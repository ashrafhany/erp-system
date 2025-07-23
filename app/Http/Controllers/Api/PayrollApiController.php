<?php

namespace App\Http\Controllers\Api;

use App\Models\PayrollRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PayrollApiController extends BaseApiController
{
    /**
     * Display a listing of payroll records
     */
    public function index(Request $request): JsonResponse
    {
        $query = PayrollRecord::with('employee');

        // Month filter
        if ($request->filled('month')) {
            $query->where('payroll_month', 'like', $request->month . '%');
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $payroll = $query->latest()->paginate($perPage);

        return $this->successResponse($payroll, 'Payroll records retrieved successfully');
    }

    /**
     * Store a newly created payroll record
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'payroll_month' => 'required|date_format:Y-m',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,approved,paid'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // Check for existing payroll record
        $existingPayroll = PayrollRecord::where('employee_id', $request->employee_id)
                                      ->where('payroll_month', $request->payroll_month)
                                      ->first();

        if ($existingPayroll) {
            return $this->errorResponse('Payroll record already exists for this employee in this month', 409);
        }

        // Calculate overtime amount and net salary
        $data = $request->all();
        $data['overtime_amount'] = ($request->overtime_hours ?? 0) * ($request->overtime_rate ?? 0);
        $data['gross_salary'] = $data['basic_salary'] + ($data['allowances'] ?? 0) + $data['overtime_amount'];
        $data['net_salary'] = $data['gross_salary'] - ($data['deductions'] ?? 0) - ($data['tax_amount'] ?? 0);

        $payroll = PayrollRecord::create($data);
        $payroll->load('employee');

        return $this->successResponse($payroll, 'Payroll record created successfully', 201);
    }

    /**
     * Display the specified payroll record
     */
    public function show(PayrollRecord $payroll): JsonResponse
    {
        $payroll->load('employee');

        return $this->successResponse($payroll, 'Payroll record retrieved successfully');
    }

    /**
     * Update the specified payroll record
     */
    public function update(Request $request, PayrollRecord $payroll): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|exists:employees,id',
            'payroll_month' => 'sometimes|date_format:Y-m',
            'basic_salary' => 'sometimes|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:draft,approved,paid'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // Check for duplicate if employee_id or month is being updated
        if ($request->has('employee_id') || $request->has('payroll_month')) {
            $employeeId = $request->get('employee_id', $payroll->employee_id);
            $month = $request->get('payroll_month', $payroll->payroll_month);

            $existingPayroll = PayrollRecord::where('employee_id', $employeeId)
                                          ->where('payroll_month', $month)
                                          ->where('id', '!=', $payroll->id)
                                          ->first();

            if ($existingPayroll) {
                return $this->errorResponse('Another payroll record exists for this employee in this month', 409);
            }
        }

        // Update data with calculations
        $data = $request->all();
        if ($request->has('overtime_hours') || $request->has('overtime_rate')) {
            $overtimeHours = $request->get('overtime_hours', $payroll->overtime_hours);
            $overtimeRate = $request->get('overtime_rate', $payroll->overtime_rate);
            $data['overtime_amount'] = $overtimeHours * $overtimeRate;
        }

        if ($request->has('basic_salary') || $request->has('allowances') || isset($data['overtime_amount'])) {
            $basicSalary = $request->get('basic_salary', $payroll->basic_salary);
            $allowances = $request->get('allowances', $payroll->allowances);
            $overtimeAmount = $data['overtime_amount'] ?? $payroll->overtime_amount;
            $data['gross_salary'] = $basicSalary + $allowances + $overtimeAmount;
        }

        if (isset($data['gross_salary']) || $request->has('deductions') || $request->has('tax_amount')) {
            $grossSalary = $data['gross_salary'] ?? $payroll->gross_salary;
            $deductions = $request->get('deductions', $payroll->deductions);
            $taxAmount = $request->get('tax_amount', $payroll->tax_amount);
            $data['net_salary'] = $grossSalary - $deductions - $taxAmount;
        }

        $payroll->update($data);
        $payroll->load('employee');

        return $this->successResponse($payroll, 'Payroll record updated successfully');
    }

    /**
     * Remove the specified payroll record
     */
    public function destroy(PayrollRecord $payroll): JsonResponse
    {
        $payroll->delete();

        return $this->successResponse(null, 'Payroll record deleted successfully');
    }

    /**
     * Generate automatic payroll for an employee
     */
    public function generateAutomatic(Employee $employee, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payroll_month' => 'required|date_format:Y-m'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $month = $request->payroll_month;

        // Check if payroll already exists
        $existingPayroll = PayrollRecord::where('employee_id', $employee->id)
                                      ->where('payroll_month', $month)
                                      ->first();

        if ($existingPayroll) {
            return $this->errorResponse('Payroll record already exists for this employee in this month', 409);
        }

        // Calculate overtime from attendance
        $attendances = $employee->attendances()
                               ->where('date', 'like', $month . '%')
                               ->get();

        $totalHours = $attendances->sum('total_hours');
        $workingDays = $attendances->where('status', 'present')->count();
        $standardHours = $workingDays * 8; // 8 hours per day
        $overtimeHours = max(0, $totalHours - $standardHours);

        $payroll = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_month' => $month,
            'basic_salary' => $employee->basic_salary,
            'overtime_hours' => $overtimeHours,
            'overtime_rate' => 50, // Default overtime rate
            'overtime_amount' => $overtimeHours * 50,
            'allowances' => 0,
            'deductions' => 0,
            'tax_amount' => $employee->basic_salary * 0.1, // 10% tax
            'gross_salary' => $employee->basic_salary + ($overtimeHours * 50),
            'net_salary' => $employee->basic_salary + ($overtimeHours * 50) - ($employee->basic_salary * 0.1),
            'status' => 'draft'
        ]);

        $payroll->load('employee');

        return $this->successResponse($payroll, 'Automatic payroll generated successfully', 201);
    }

    /**
     * Approve payroll record
     */
    public function approve(PayrollRecord $payroll): JsonResponse
    {
        if ($payroll->status === 'approved') {
            return $this->errorResponse('Payroll record is already approved', 409);
        }

        $payroll->update(['status' => 'approved']);
        $payroll->load('employee');

        return $this->successResponse($payroll, 'Payroll record approved successfully');
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid(PayrollRecord $payroll): JsonResponse
    {
        if ($payroll->status === 'paid') {
            return $this->errorResponse('Payroll record is already marked as paid', 409);
        }

        $payroll->update([
            'status' => 'paid',
            'paid_date' => now()
        ]);
        $payroll->load('employee');

        return $this->successResponse($payroll, 'Payroll record marked as paid successfully');
    }
}
