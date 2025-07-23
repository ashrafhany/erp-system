<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Customer;
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
        $query = Invoice::with('customer');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
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
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        DB::beginTransaction();

        // Generate invoice number
        $lastInvoice = Invoice::latest()->first();
        $invoiceNumber = 'INV-' . str_pad(($lastInvoice ? $lastInvoice->id + 1 : 1), 6, '0', STR_PAD_LEFT);

        // Create invoice
        $invoiceData = $request->except('items');
        $invoiceData['invoice_number'] = $invoiceNumber;
        $invoiceData['status'] = 'pending';
        $invoiceData['subtotal'] = 0;
        $invoiceData['tax_amount'] = 0;
        $invoiceData['total_amount'] = 0;
        $invoiceData['paid_amount'] = 0;

        $invoice = Invoice::create($invoiceData);

        // Create invoice items and calculate totals
        $subtotal = 0;
        $totalTax = 0;

        foreach ($request->items as $itemData) {
            $lineTotal = $itemData['quantity'] * $itemData['unit_price'];
            $itemTaxRate = $itemData['tax_rate'] ?? $request->tax_rate ?? 0;
            $itemTax = $lineTotal * ($itemTaxRate / 100);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'line_total' => $lineTotal,
                'tax_rate' => $itemTaxRate,
                'tax_amount' => $itemTax
            ]);

            $subtotal += $lineTotal;
            $totalTax += $itemTax;
        }

        // Update invoice totals
        $discountAmount = $request->discount_amount ?? 0;
        $totalAmount = $subtotal + $totalTax - $discountAmount;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount
        ]);

        DB::commit();

        $invoice->load(['customer', 'items']);

        return $this->successResponse($invoice, 'Invoice created successfully', 201);
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['customer', 'items', 'payments']);

        return $this->successResponse($invoice, 'Invoice retrieved successfully');
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|exists:customers,id',
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:invoice_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        if ($invoice->status === 'paid' && $request->has('items')) {
            return $this->errorResponse('Cannot modify items of a paid invoice', 409);
        }

        $invoice->update($request->except('items'));
        $invoice->load(['customer', 'items', 'payments']);

        return $this->successResponse($invoice, 'Invoice updated successfully');
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return $this->errorResponse('Cannot delete a paid invoice', 409);
        }

        if ($invoice->payments()->count() > 0) {
            return $this->errorResponse('Cannot delete invoice with payments', 409);
        }

        $invoice->delete();

        return $this->successResponse(null, 'Invoice deleted successfully');
    }

    /**
     * Add items to invoice
     */
    public function addItems(Invoice $invoice, Request $request): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return $this->errorResponse('Cannot add items to a paid invoice', 409);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        DB::beginTransaction();

        $subtotalIncrease = 0;
        $taxIncrease = 0;

        foreach ($request->items as $itemData) {
            $lineTotal = $itemData['quantity'] * $itemData['unit_price'];
            $itemTaxRate = $itemData['tax_rate'] ?? $invoice->tax_rate ?? 0;
            $itemTax = $lineTotal * ($itemTaxRate / 100);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'line_total' => $lineTotal,
                'tax_rate' => $itemTaxRate,
                'tax_amount' => $itemTax
            ]);

            $subtotalIncrease += $lineTotal;
            $taxIncrease += $itemTax;
        }

        // Update invoice totals
        $invoice->update([
            'subtotal' => $invoice->subtotal + $subtotalIncrease,
            'tax_amount' => $invoice->tax_amount + $taxIncrease,
            'total_amount' => $invoice->total_amount + $subtotalIncrease + $taxIncrease
        ]);

        DB::commit();

        $invoice->load(['customer', 'items', 'payments']);

        return $this->successResponse($invoice, 'Items added to invoice successfully');
    }

    /**
     * Record payment for invoice
     */
    public function addPayment(Invoice $invoice, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $remainingAmount = $invoice->total_amount - $invoice->paid_amount;

        if ($request->amount > $remainingAmount) {
            return $this->errorResponse('Payment amount cannot exceed remaining balance', 400);
        }

        DB::beginTransaction();

        // Create payment record
        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes
        ]);

        // Update invoice paid amount and status
        $newPaidAmount = $invoice->paid_amount + $request->amount;
        $newStatus = $newPaidAmount >= $invoice->total_amount ? 'paid' : 'pending';

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $newStatus
        ]);

        DB::commit();

        $invoice->load(['customer', 'items', 'payments']);

        return $this->successResponse($invoice, 'Payment recorded successfully');
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePdf(Invoice $invoice): JsonResponse
    {
        // This is a placeholder for PDF generation
        // You would typically use a package like DomPDF or similar

        $invoice->load(['customer', 'items', 'payments']);

        $pdfData = [
            'invoice' => $invoice,
            'pdf_url' => route('api.invoices.pdf', $invoice->id), // Placeholder URL
            'generated_at' => now()
        ];

        return $this->successResponse($pdfData, 'PDF generation completed');
    }
}
