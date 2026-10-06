<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->requireCrudPermissions('invoices');
        $this->middleware('permission:invoices.view')->only('print');
        $this->middleware('permission:invoices.send')->only('send');
        $this->middleware('permission:invoices.payment')->only('recordPayment');
        $this->middleware('permission:invoices.reconcile')->only('reconcilePayment');
        $this->middleware('permission:invoices.update')->only('cancel');
    }

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
            if ($request->status === 'overdue') {
                $query->overdue();
            } elseif ($request->status === 'sent') {
                $query->where('status', 'sent')->whereDate('due_date', '>=', today());
            } else {
                $query->where('status', $request->status);
            }
        }

        // فلترة بالتاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoiceStats = [
            'total' => (clone $query)->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'pending' => (clone $query)->where('status', 'sent')->whereDate('due_date', '>=', today())->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'inconsistent' => (clone $query)->where(function ($q) {
                $q->whereColumn('paid_amount', '>', 'total_amount')
                    ->orWhere(fn ($paid) => $paid->where('status', 'paid')->whereColumn('paid_amount', '<', 'total_amount'))
                    ->orWhere(fn ($draft) => $draft->where('status', 'draft')->where('paid_amount', '>', 0));
            })->count(),
        ];
        $invoices = $query->latest()->paginate(15);
        $customers = Customer::where('status', 'active')->get();

        return view('invoices.index', compact('invoices', 'customers', 'invoiceStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->get();
        $duplicate = $request->filled('duplicate')
            ? Invoice::with('items')->findOrFail($request->integer('duplicate'))
            : null;
        $initialItems = old('items', $duplicate?->items?->map(fn ($item) => $item->only(['description', 'quantity', 'unit_price']))->values()->all() ?? []);
        return view('invoices.create', compact('customers', 'duplicate', 'initialItems'));
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
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        DB::beginTransaction();

        try {
            $invoice = Invoice::create([
                'invoice_number' => 'TMP-'.str()->uuid(),
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

            $invoice->update(['invoice_number' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT)]);
            foreach ($validator->validated()['items'] as $item) {
                $invoice->items()->create($item);
            }
            $invoice->calculateTotal();
            if ($invoice->total_amount <= 0) {
                throw ValidationException::withMessages(['discount_amount' => 'يجب أن يكون إجمالي الفاتورة أكبر من صفر.']);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                           ->with('success', 'تم إنشاء الفاتورة بنجاح');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
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
        $invoice->load(['customer', 'items', 'payments']);
        $paymentAdjustments = DB::table('invoice_payment_adjustments')->where('invoice_id', $invoice->id)->orderByDesc('id')->get();
        return view('invoices.show', compact('invoice', 'paymentAdjustments'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        $customers = Customer::where('status', 'active')->orWhere('id', $invoice->customer_id)->get();
        $financialLocked = $invoice->status === 'cancelled' || $invoice->payments()->exists();
        return view('invoices.edit', compact('invoice', 'customers', 'financialLocked'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $financialLocked = $invoice->status === 'cancelled' || $invoice->payments()->exists();
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ];
        if (! $financialLocked) {
            $rules += [
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01'
            ];
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $items = $financialLocked ? [] : $validator->validated()['items'];
        $submittedIds = [];
        if (! $financialLocked) {
            $subtotal = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
            $newTotal = $subtotal + ($request->tax_amount ?? 0) - ($request->discount_amount ?? 0);
            if ($newTotal <= 0 || ($newTotal < $invoice->paid_amount && $newTotal < $invoice->total_amount)) {
                return back()->withErrors(['discount_amount' => 'يجب أن يكون إجمالي الفاتورة موجبًا ولا يقل عن المبلغ المدفوع.'])->withInput();
            }
            $existingIds = $invoice->items()->pluck('id')->all();
            $submittedIds = collect($items)->pluck('id')->filter()->all();
            if (count($submittedIds) !== count(array_unique($submittedIds)) || array_diff($submittedIds, $existingIds)) {
                return back()->withErrors(['items' => 'أحد بنود الفاتورة غير صحيح.'])->withInput();
            }
        }

        DB::transaction(function () use ($invoice, $request, $items, $submittedIds, $financialLocked) {
        $invoice->update([
            'customer_id' => $request->customer_id,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'notes' => $request->notes
        ] + ($financialLocked ? [] : ['tax_amount' => $request->tax_amount ?? 0, 'discount_amount' => $request->discount_amount ?? 0]));

        if (! $financialLocked) {
            $invoice->items()->whereNotIn('id', $submittedIds)->delete();
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $invoice->items()->findOrFail($item['id'])->update(collect($item)->except('id')->all());
                } else {
                    $invoice->items()->create($item);
                }
            }
            $invoice->calculateTotal();
        }
        });

        return redirect()->route('invoices.index')
                       ->with('success', 'تم تحديث الفاتورة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->paid_amount > 0 || $invoice->status !== 'draft') {
            return redirect()->back()
                           ->with('error', 'يمكن حذف المسودات التي لم تسجل لها دفعات فقط.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
                       ->with('success', 'تم حذف الفاتورة بنجاح');
    }

    /**
     * إرسال الفاتورة
     */
    public function send(Invoice $invoice)
    {
        abort_unless($invoice->status === 'draft', 403);
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
        abort_unless(in_array($invoice->status, ['sent', 'overdue'], true), 403);
        if ($invoice->remaining_amount <= 0) {
            return back()->with('error', 'لا يوجد مبلغ مستحق لهذه الفاتورة. راجع بيانات الدفع المسجلة أولاً.');
        }
        $validator = Validator::make($request->all(), [
            'payment_amount' => 'required|numeric|min:0.01|max:' . $invoice->remaining_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,card,other'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator);
        }

        DB::transaction(function () use ($invoice, $request) {
        $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
        if ($request->payment_amount > $invoice->remaining_amount) {
            throw ValidationException::withMessages(['payment_amount' => 'قيمة الدفعة أكبر من المتبقي.']);
        }
        $invoice->payments()->create([
            'amount' => $request->payment_amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
        ]);
        $newPaidAmount = $invoice->paid_amount + $request->payment_amount;

        $status = $invoice->due_date->lt(today()) ? 'overdue' : 'sent';
        if ($newPaidAmount >= $invoice->total_amount) {
            $status = 'paid';
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status
        ]);
        });

        return redirect()->back()
                       ->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function reconcilePayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->total_amount <= 0, 422, 'إجمالي الفاتورة غير صالح للتصحيح.');
        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0', 'max:'.$invoice->total_amount],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        DB::transaction(function () use ($invoice, $request, $data) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            DB::table('invoice_payment_adjustments')->insert([
                'invoice_id' => $invoice->id,
                'user_id' => $request->user()->id,
                'old_amount' => $invoice->paid_amount,
                'new_amount' => $data['paid_amount'],
                'reason' => $data['reason'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($invoice->status === 'cancelled') {
                $status = 'cancelled';
            } elseif ($data['paid_amount'] >= $invoice->total_amount) {
                $status = 'paid';
            } elseif ($data['paid_amount'] > 0 || $invoice->status !== 'draft') {
                $status = 'sent';
            } else {
                $status = 'draft';
            }
            if ($status === 'sent' && $invoice->due_date->lt(today())) {
                $status = 'overdue';
            }
            $invoice->update(['paid_amount' => $data['paid_amount'], 'status' => $status]);
        });

        return back()->with('success', 'تم تصحيح المبلغ المدفوع وحفظ سبب التصحيح.');
    }

    public function cancel(Invoice $invoice)
    {
        abort_unless($invoice->status === 'draft' && $invoice->paid_amount == 0, 403);
        $invoice->update(['status' => 'cancelled']);
        return back()->with('success', 'تم إلغاء الفاتورة.');
    }
}
