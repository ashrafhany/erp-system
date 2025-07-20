<?php

namespace App\Http\Controllers\Api;

use App\Models\PayrollRecord;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PayrollApiController extends BaseApiController
{
    /**
     * Display a listing of payroll records
     */
    public function index(Request $request): JsonResponse
    {
        try {
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

            // Year filter
            if ($request->filled('year')) {
                $query->where('payroll_month', 'like', $request->year . '%');
            }

            $perPage = $request->get('per_page', 15);
            $payroll = $query->latest()->paginate($perPage);

            return $this->successResponse($payroll, 'Payroll records retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve payroll records: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created payroll record
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'payroll_month' => 'required|date_format:Y-m-d',
                'basic_salary' => 'required|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'overtime_rate' => 'nullable|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            // Check for existing payroll record
            $existingPayroll = PayrollRecord::where('employee_id', $request->employee_id)
                                          ->where('payroll_month', $request->payroll_month)
                                          ->first();

            if ($existingPayroll) {
                return $this->errorResponse('Payroll record already exists for this employee for this month', 409);
            }

            $payrollData = $request->all();

            // Calculate overtime amount
            $overtimeAmount = ($request->overtime_hours ?? 0) * ($request->overtime_rate ?? 0);
            $payrollData['overtime_amount'] = $overtimeAmount;

            // Calculate net salary
            $grossSalary = $request->basic_salary + $overtimeAmount + ($request->allowances ?? 0);
            $totalDeductions = ($request->deductions ?? 0) + ($request->tax_amount ?? 0);
            $payrollData['net_salary'] = $grossSalary - $totalDeductions;

            $payroll = PayrollRecord::create($payrollData);
            $payroll->load('employee');

            return $this->successResponse($payroll, 'Payroll record created successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create payroll record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified payroll record
     */
    public function show(PayrollRecord $payroll): JsonResponse
    {
        try {
            $payroll->load('employee');

            return $this->successResponse($payroll, 'Payroll record retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve payroll record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified payroll record
     */
    public function update(Request $request, PayrollRecord $payroll): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'sometimes|exists:employees,id',
                'payroll_month' => 'sometimes|date_format:Y-m-d',
                'basic_salary' => 'sometimes|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'overtime_rate' => 'nullable|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            // Check for duplicate if employee_id or payroll_month is being updated
            if ($request->has('employee_id') || $request->has('payroll_month')) {
                $employeeId = $request->get('employee_id', $payroll->employee_id);
                $payrollMonth = $request->get('payroll_month', $payroll->payroll_month);

                $existingPayroll = PayrollRecord::where('employee_id', $employeeId)
                                               ->where('payroll_month', $payrollMonth)
                                               ->where('id', '!=', $payroll->id)
                                               ->first();

                if ($existingPayroll) {
                    return $this->errorResponse('Another payroll record exists for this employee for this month', 409);
                }
            }

            $payrollData = $request->all();

            // Recalculate if salary components are updated
            if ($request->has(['basic_salary', 'overtime_hours', 'overtime_rate', 'allowances', 'deductions', 'tax_amount'])) {
                $basicSalary = $request->get('basic_salary', $payroll->basic_salary);
                $overtimeHours = $request->get('overtime_hours', $payroll->overtime_hours);
                $overtimeRate = $request->get('overtime_rate', $payroll->overtime_rate);
                $allowances = $request->get('allowances', $payroll->allowances);
                $deductions = $request->get('deductions', $payroll->deductions);
                $taxAmount = $request->get('tax_amount', $payroll->tax_amount);

                $overtimeAmount = $overtimeHours * $overtimeRate;
                $payrollData['overtime_amount'] = $overtimeAmount;

                $grossSalary = $basicSalary + $overtimeAmount + $allowances;
                $totalDeductions = $deductions + $taxAmount;
                $payrollData['net_salary'] = $grossSalary - $totalDeductions;
            }

            $payroll->update($payrollData);
            $payroll->load('employee');

            return $this->successResponse($payroll, 'Payroll record updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update payroll record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified payroll record
     */
    public function destroy(PayrollRecord $payroll): JsonResponse
    {
        try {
            $payroll->delete();

            return $this->successResponse(null, 'Payroll record deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete payroll record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate automatic payroll for employee
     */
    public function generatePayroll(Request $request, Employee $employee): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'payroll_month' => 'required|date_format:Y-m-d'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $payrollMonth = $request->payroll_month;
            $monthStart = Carbon::parse($payrollMonth)->startOfMonth();
            $monthEnd = Carbon::parse($payrollMonth)->endOfMonth();

            // Check if payroll already exists
            $existingPayroll = PayrollRecord::where('employee_id', $employee->id)
                                          ->where('payroll_month', $payrollMonth)
                                          ->first();

            if ($existingPayroll) {
                return $this->errorResponse('Payroll already exists for this employee for this month', 409);
            }

            // Get attendance data for the month
            $attendanceRecords = Attendance::where('employee_id', $employee->id)
                                         ->whereBetween('date', [$monthStart, $monthEnd])
                                         ->get();

            // Calculate overtime hours (assuming standard 8 hours per day)
            $totalHours = $attendanceRecords->sum('total_hours');
            $workingDays = $attendanceRecords->where('status', 'present')->count();
            $standardHours = $workingDays * 8;
            $overtimeHours = max(0, $totalHours - $standardHours);

            // Default overtime rate (can be configurable)
            $overtimeRate = $employee->basic_salary / 30 / 8 * 1.5; // 1.5x hourly rate
            $overtimeAmount = $overtimeHours * $overtimeRate;

            // Calculate tax (simple 10% tax rate - can be made configurable)
            $grossSalary = $employee->basic_salary + $overtimeAmount;
            $taxAmount = $grossSalary * 0.10;

            $payrollData = [
                'employee_id' => $employee->id,
                'payroll_month' => $payrollMonth,
                'basic_salary' => $employee->basic_salary,
                'overtime_hours' => $overtimeHours,
                'overtime_rate' => $overtimeRate,
                'overtime_amount' => $overtimeAmount,
                'allowances' => 0,
                'deductions' => 0,
                'tax_amount' => $taxAmount,
                'net_salary' => $grossSalary - $taxAmount,
                'status' => 'pending',
                'notes' => 'Auto-generated based on attendance records'
            ];

            $payroll = PayrollRecord::create($payrollData);
            $payroll->load('employee');

            return $this->successResponse($payroll, 'Payroll generated successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate payroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve payroll record
     */
    public function approve(PayrollRecord $payroll): JsonResponse
    {
        try {
            if ($payroll->status === 'approved') {
                return $this->errorResponse('Payroll is already approved', 409);
            }

            $payroll->update([
                'status' => 'approved',
                'approved_at' => now()
            ]);

            $payroll->load('employee');

            return $this->successResponse($payroll, 'Payroll approved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to approve payroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Monthly payroll report
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        try {
            $month = $request->get('month', now()->format('Y-m'));

            $payrollRecords = PayrollRecord::with('employee')
                                         ->where('payroll_month', 'like', $month . '%')
                                         ->get();

            $totalEmployees = Employee::where('status', 'active')->count();
            $processedEmployees = $payrollRecords->count();
            $pendingEmployees = $totalEmployees - $processedEmployees;

            $totalBasicSalary = $payrollRecords->sum('basic_salary');
            $totalOvertimeAmount = $payrollRecords->sum('overtime_amount');
            $totalAllowances = $payrollRecords->sum('allowances');
            $totalDeductions = $payrollRecords->sum('deductions');
            $totalTaxAmount = $payrollRecords->sum('tax_amount');
            $totalNetSalary = $payrollRecords->sum('net_salary');

            $statusBreakdown = [
                'pending' => $payrollRecords->where('status', 'pending')->count(),
                'approved' => $payrollRecords->where('status', 'approved')->count(),
                'paid' => $payrollRecords->where('status', 'paid')->count()
            ];

            $report = [
                'month' => $month,
                'summary' => [
                    'total_employees' => $totalEmployees,
                    'processed_employees' => $processedEmployees,
                    'pending_employees' => $pendingEmployees,
                    'completion_rate' => $totalEmployees > 0 ? round(($processedEmployees / $totalEmployees) * 100, 2) : 0
                ],
                'financial_summary' => [
                    'total_basic_salary' => $totalBasicSalary,
                    'total_overtime_amount' => $totalOvertimeAmount,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'total_tax_amount' => $totalTaxAmount,
                    'total_net_salary' => $totalNetSalary
                ],
                'status_breakdown' => $statusBreakdown,
                'payroll_records' => $payrollRecords
            ];

            return $this->successResponse($report, 'Monthly payroll report generated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate monthly report: ' . $e->getMessage(), 500);
        }
    }
}
