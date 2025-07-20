<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EmployeeApiController extends BaseApiController
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::query();

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            // Department filter
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 15);
            $employees = $query->latest()->paginate($perPage);

            return $this->successResponse($employees, 'Employees retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve employees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|string|unique:employees,employee_id',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:employees,email',
                'phone' => 'nullable|string|max:20',
                'department' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'basic_salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
                'status' => 'required|in:active,inactive,terminated',
                'address' => 'nullable|string',
                'national_id' => 'nullable|string|max:20|unique:employees,national_id'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $employee = Employee::create($request->all());

            return $this->successResponse($employee, 'Employee created successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee): JsonResponse
    {
        try {
            $employee->load(['attendances' => function($query) {
                $query->latest()->take(10);
            }, 'payrollRecords' => function($query) {
                $query->latest()->take(5);
            }]);

            return $this->successResponse($employee, 'Employee retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'sometimes|string|unique:employees,employee_id,' . $employee->id,
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:employees,email,' . $employee->id,
                'phone' => 'nullable|string|max:20',
                'department' => 'sometimes|string|max:255',
                'position' => 'sometimes|string|max:255',
                'basic_salary' => 'sometimes|numeric|min:0',
                'hire_date' => 'sometimes|date',
                'status' => 'sometimes|in:active,inactive,terminated',
                'address' => 'nullable|string',
                'national_id' => 'nullable|string|max:20|unique:employees,national_id,' . $employee->id
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $employee->update($request->all());

            return $this->successResponse($employee, 'Employee updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            $employee->delete();

            return $this->successResponse(null, 'Employee deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get employee attendance records
     */
    public function attendance(Request $request, Employee $employee): JsonResponse
    {
        try {
            $query = $employee->attendances();

            if ($request->filled('month')) {
                $query->where('date', 'like', $request->month . '%');
            }

            $perPage = $request->get('per_page', 15);
            $attendance = $query->latest()->paginate($perPage);

            return $this->successResponse($attendance, 'Employee attendance retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve employee attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get employee payroll records
     */
    public function payroll(Request $request, Employee $employee): JsonResponse
    {
        try {
            $query = $employee->payrollRecords();

            if ($request->filled('year')) {
                $query->where('payroll_month', 'like', $request->year . '%');
            }

            $perPage = $request->get('per_page', 15);
            $payroll = $query->latest()->paginate($perPage);

            return $this->successResponse($payroll, 'Employee payroll retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve employee payroll: ' . $e->getMessage(), 500);
        }
    }
}
