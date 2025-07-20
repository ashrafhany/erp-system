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
        try {
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

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve customers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_code' => 'required|string|unique:customers,customer_code',
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'required|email|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'credit_limit' => 'nullable|numeric|min:0',
                'status' => 'required|in:active,inactive',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $customer = Customer::create($request->all());

            return $this->successResponse($customer, 'Customer created successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer): JsonResponse
    {
        try {
            $customer->load(['invoices' => function($query) {
                $query->latest()->take(10);
            }]);

            // Add some customer statistics
            $customerStats = [
                'total_invoices' => $customer->invoices()->count(),
                'total_invoice_amount' => $customer->invoices()->sum('total_amount'),
                'paid_amount' => $customer->invoices()->sum('paid_amount'),
                'pending_amount' => $customer->invoices()->sum('total_amount') - $customer->invoices()->sum('paid_amount'),
                'overdue_invoices' => $customer->invoices()->where('status', 'overdue')->count()
            ];

            $customerData = $customer->toArray();
            $customerData['statistics'] = $customerStats;

            return $this->successResponse($customerData, 'Customer retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_code' => 'sometimes|string|unique:customers,customer_code,' . $customer->id,
                'name' => 'sometimes|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'sometimes|email|unique:customers,email,' . $customer->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'credit_limit' => 'nullable|numeric|min:0',
                'status' => 'sometimes|in:active,inactive',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $customer->update($request->all());

            return $this->successResponse($customer, 'Customer updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            // Check if customer has any invoices
            if ($customer->invoices()->count() > 0) {
                return $this->errorResponse('Cannot delete customer with existing invoices', 409);
            }

            $customer->delete();

            return $this->successResponse(null, 'Customer deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get customer invoices
     */
    public function invoices(Request $request, Customer $customer): JsonResponse
    {
        try {
            $query = $customer->invoices();

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

            $perPage = $request->get('per_page', 15);
            $invoices = $query->latest()->paginate($perPage);

            return $this->successResponse($invoices, 'Customer invoices retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve customer invoices: ' . $e->getMessage(), 500);
        }
    }
}
