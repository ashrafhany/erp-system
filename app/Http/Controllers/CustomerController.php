<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // البحث بالاسم أو رمز العميل
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // فلترة بالحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(10);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_code' => 'required|unique:customers',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        Customer::create($request->all());

        return redirect()->route('customers.index')
                       ->with('success', 'تم إضافة العميل بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['invoices' => function($query) {
            $query->latest()->take(10);
        }]);

        $totalInvoices = $customer->invoices()->count();
        $totalAmount = $customer->invoices()->sum('total_amount');
        $paidAmount = $customer->invoices()->sum('paid_amount');
        $outstandingAmount = $customer->getTotalOutstandingAmount();

        return view('customers.show', compact(
            'customer',
            'totalInvoices',
            'totalAmount',
            'paidAmount',
            'outstandingAmount'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'customer_code' => 'required|unique:customers,customer_code,' . $customer->id,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $customer->update($request->all());

        return redirect()->route('customers.index')
                       ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // التحقق من وجود فواتير مرتبطة بالعميل
        if ($customer->invoices()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'لا يمكن حذف العميل لوجود فواتير مرتبطة به');
        }

        $customer->delete();

        return redirect()->route('customers.index')
                       ->with('success', 'تم حذف العميل بنجاح');
    }
}
