<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InvoiceApiController extends BaseApiController
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Invoice::with('customer');

            // Customer filter
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Date range filter
            if ($request->filled('from_date')) {
                $query->where('invoice_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->where('invoice_date', '<=', $request->to_date);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($customerQuery) use ($search) {
                          $customerQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('company_name', 'like', "%{$search}%");
                      });
                });
            }

            $perPage = $request->get('per_page', 15);
            $invoices = $query->latest()->paginate($perPage);

            return $this->successResponse($invoices, 'Invoices retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve invoices: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after:invoice_date',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            DB::beginTransaction();

            try {
                // Generate invoice number
                $lastInvoice = Invoice::latest()->first();
                $invoiceNumber = 'INV-' . str_pad(($lastInvoice ? $lastInvoice->id + 1 : 1), 6, '0', STR_PAD_LEFT);

                // Create invoice
                $invoiceData = $request->except('items');
                $invoiceData['invoice_number'] = $invoiceNumber;
                $invoiceData['status'] = 'draft';

                $invoice = Invoice::create($invoiceData);

                // Create invoice items and calculate totals
                $subtotal = 0;
                foreach ($request->items as $itemData) {
                    $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                    $itemData['invoice_id'] = $invoice->id;
                    $itemData['total_price'] = $totalPrice;

                    InvoiceItem::create($itemData);
                    $subtotal += $totalPrice;
                }

                // Calculate final amounts
                $discountAmount = $request->discount_amount ?? 0;
                $taxRate = $request->tax_rate ?? 0;
                $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
                $totalAmount = $subtotal - $discountAmount + $taxAmount;

                // Update invoice with calculated amounts
                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount
                ]);

                $invoice->load(['customer', 'items']);

                DB::commit();

                return $this->successResponse($invoice, 'Invoice created successfully', 201);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        try {
            $invoice->load(['customer', 'items']);

            return $this->successResponse($invoice, 'Invoice retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            // Prevent updating paid invoices
            if ($invoice->status === 'paid') {
                return $this->errorResponse('Cannot update paid invoice', 409);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'sometimes|exists:customers,id',
                'invoice_date' => 'sometimes|date',
                'due_date' => 'sometimes|date|after:invoice_date',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $invoice->update($request->all());

            // Recalculate totals if tax_rate or discount_amount changed
            if ($request->has('tax_rate') || $request->has('discount_amount')) {
                $this->recalculateInvoiceTotals($invoice);
            }

            $invoice->load(['customer', 'items']);

            return $this->successResponse($invoice, 'Invoice updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            // Prevent deleting paid invoices
            if ($invoice->status === 'paid') {
                return $this->errorResponse('Cannot delete paid invoice', 409);
            }

            $invoice->delete();

            return $this->successResponse(null, 'Invoice deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add item to invoice
     */
    public function addItem(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            // Prevent adding items to paid invoices
            if ($invoice->status === 'paid') {
                return $this->errorResponse('Cannot add items to paid invoice', 409);
            }

            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'quantity' => 'required|numeric|min:1',
                'unit_price' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $itemData = $request->all();
            $itemData['invoice_id'] = $invoice->id;
            $itemData['total_price'] = $itemData['quantity'] * $itemData['unit_price'];

            $item = InvoiceItem::create($itemData);

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            $invoice->load(['customer', 'items']);

            return $this->successResponse($invoice, 'Item added to invoice successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add item to invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove item from invoice
     */
    public function removeItem(InvoiceItem $item): JsonResponse
    {
        try {
            $invoice = $item->invoice;

            // Prevent removing items from paid invoices
            if ($invoice->status === 'paid') {
                return $this->errorResponse('Cannot remove items from paid invoice', 409);
            }

            $item->delete();

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            $invoice->load(['customer', 'items']);

            return $this->successResponse($invoice, 'Item removed from invoice successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove item from invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Send invoice to customer
     */
    public function send(Invoice $invoice): JsonResponse
    {
        try {
            if ($invoice->status === 'paid') {
                return $this->errorResponse('Invoice is already paid', 409);
            }

            if ($invoice->items()->count() === 0) {
                return $this->errorResponse('Cannot send invoice without items', 400);
            }

            $invoice->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            $invoice->load(['customer', 'items']);

            return $this->successResponse($invoice, 'Invoice sent successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_method' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $paymentAmount = $request->amount;
            $currentPaidAmount = $invoice->paid_amount ?? 0;
            $remainingAmount = $invoice->total_amount - $currentPaidAmount;

            if ($paymentAmount > $remainingAmount) {
                return $this->errorResponse('Payment amount exceeds remaining balance', 400);
            }

            $newPaidAmount = $currentPaidAmount + $paymentAmount;
            $status = $newPaidAmount >= $invoice->total_amount ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
                'payment_date' => $newPaidAmount >= $invoice->total_amount ? $request->payment_date : $invoice->payment_date
            ]);

            // Here you could also create a separate payment record table for detailed payment history

            $invoice->load(['customer', 'items']);

            $message = $status === 'paid' ? 'Payment recorded - Invoice fully paid' : 'Partial payment recorded';

            return $this->successResponse($invoice, $message);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to record payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePdf(Invoice $invoice): JsonResponse
    {
        try {
            // This is a placeholder - you would implement actual PDF generation here
            // using libraries like DomPDF, TCPDF, or similar

            $invoice->load(['customer', 'items']);

            // For now, return invoice data that can be used to generate PDF on frontend
            return $this->successResponse([
                'invoice' => $invoice,
                'pdf_url' => null, // Would contain actual PDF URL
                'message' => 'PDF generation endpoint - implement PDF library integration'
            ], 'Invoice data prepared for PDF generation');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate PDF: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Recalculate invoice totals
     */
    private function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $subtotal = $invoice->items()->sum('total_price');
        $discountAmount = $invoice->discount_amount ?? 0;
        $taxRate = $invoice->tax_rate ?? 0;
        $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);
    }
}
