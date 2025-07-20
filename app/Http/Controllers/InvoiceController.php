<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('customer');

        // فلترة بالعميل
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // فلترة بالحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة بالتاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(15);
        $customers = Customer::where('status', 'active')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('invoices.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        DB::beginTransaction();

        try {
            // إنشاء رقم فاتورة تلقائي
            $lastInvoice = Invoice::latest('id')->first();
            $invoiceNumber = 'INV-' . str_pad(($lastInvoice ? $lastInvoice->id + 1 : 1), 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'status' => 'draft',
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('invoices.edit', $invoice)
                           ->with('success', 'تم إنشاء الفاتورة بنجاح. يمكنك الآن إضافة العناصر');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة')
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        $customers = Customer::where('status', 'active')->get();
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $invoice->update([
            'customer_id' => $request->customer_id,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'tax_amount' => $request->tax_amount ?? 0,
            'discount_amount' => $request->discount_amount ?? 0,
            'notes' => $request->notes
        ]);

        $invoice->calculateTotal();

        return redirect()->route('invoices.index')
                       ->with('success', 'تم تحديث الفاتورة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status == 'paid') {
            return redirect()->back()
                           ->with('error', 'لا يمكن حذف فاتورة مدفوعة');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
                       ->with('success', 'تم حذف الفاتورة بنجاح');
    }

    /**
     * إضافة عنصر للفاتورة
     */
    public function addItem(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'total_price' => $request->quantity * $request->unit_price
        ]);

        return redirect()->back()
                       ->with('success', 'تم إضافة العنصر بنجاح');
    }

    /**
     * حذف عنصر من الفاتورة
     */
    public function removeItem(InvoiceItem $item)
    {
        $item->delete();

        return redirect()->back()
                       ->with('success', 'تم حذف العنصر بنجاح');
    }

    /**
     * إرسال الفاتورة
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->items()->count() == 0) {
            return redirect()->back()
                           ->with('error', 'لا يمكن إرسال فاتورة فارغة');
        }

        $invoice->update(['status' => 'sent']);

        return redirect()->back()
                       ->with('success', 'تم إرسال الفاتورة بنجاح');
    }

    /**
     * تسجيل دفعة
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:0.01|max:' . $invoice->remaining_amount
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator);
        }

        $newPaidAmount = $invoice->paid_amount + $request->payment_amount;

        $status = 'sent';
        if ($newPaidAmount >= $invoice->total_amount) {
            $status = 'paid';
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status
        ]);

        return redirect()->back()
                       ->with('success', 'تم تسجيل الدفعة بنجاح');
    }
}
