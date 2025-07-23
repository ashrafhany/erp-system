<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends BaseApiController
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $customers = $query->latest()->paginate($perPage);

        return $this->successResponse($customers, 'Customers retrieved successfully');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_code' => 'required|string|unique:customers,customer_code|max:50',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $customer = Customer::create($request->all());

        return $this->successResponse($customer, 'Customer created successfully', 201);
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['invoices' => function($query) {
            $query->latest()->take(10);
        }]);

        return $this->successResponse($customer, 'Customer retrieved successfully');
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_code' => 'sometimes|string|unique:customers,customer_code,' . $customer->id . '|max:50',
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,' . $customer->id . '|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'sometimes|in:active,inactive',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $customer->update($request->all());

        return $this->successResponse($customer, 'Customer updated successfully');
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer): JsonResponse
    {
        // Check if customer has invoices
        if ($customer->invoices()->count() > 0) {
            return $this->errorResponse('Cannot delete customer with existing invoices', 409);
        }

        $customer->delete();

        return $this->successResponse(null, 'Customer deleted successfully');
    }

    /**
     * Get customer invoices
     */
    public function invoices(Customer $customer, Request $request): JsonResponse
    {
        $query = $customer->invoices();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $perPage = $request->get('per_page', 15);
        $invoices = $query->latest()->paginate($perPage);

        return $this->successResponse($invoices, 'Customer invoices retrieved successfully');
    }

    /**
     * Get customer balance summary
     */
    public function balance(Customer $customer): JsonResponse
    {
        $totalInvoices = $customer->invoices()->sum('total_amount');
        $totalPaid = $customer->invoices()->sum('paid_amount');
        $pendingAmount = $customer->invoices()->where('status', 'pending')->sum('total_amount');
        $overdueAmount = $customer->invoices()->where('status', 'overdue')->sum('total_amount');

        $balance = [
            'total_invoiced' => $totalInvoices,
            'total_paid' => $totalPaid,
            'outstanding_balance' => $totalInvoices - $totalPaid,
            'pending_amount' => $pendingAmount,
            'overdue_amount' => $overdueAmount,
            'credit_limit' => $customer->credit_limit,
            'available_credit' => $customer->credit_limit ? max(0, $customer->credit_limit - ($totalInvoices - $totalPaid)) : null
        ];

        return $this->successResponse($balance, 'Customer balance retrieved successfully');
    }
}
