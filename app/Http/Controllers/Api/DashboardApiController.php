<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollRecord;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardApiController extends BaseApiController
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $totalEmployees = Employee::count();
            $activeEmployees = Employee::where('status', 'active')->count();
            $totalCustomers = Customer::count();
            $totalInvoices = Invoice::count();

            $stats = [
                'employees' => [
                    'total' => $totalEmployees,
                    'active' => $activeEmployees,
                    'inactive' => $totalEmployees - $activeEmployees
                ],
                'customers' => [
                    'total' => $totalCustomers
                ],
                'invoices' => [
                    'total' => $totalInvoices
                ]
            ];

            return $this->successResponse($stats, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get monthly overview
     */
    public function monthlyOverview(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));

        // Monthly attendance stats
        $monthlyAttendance = Attendance::where('date', 'like', $month . '%')
                                     ->selectRaw('status, COUNT(*) as count')
                                     ->groupBy('status')
                                     ->pluck('count', 'status')
                                     ->toArray();

        // Monthly payroll
        $monthlyPayroll = PayrollRecord::where('payroll_month', 'like', $month . '%')
                                     ->selectRaw('SUM(basic_salary) as basic, SUM(overtime_amount) as overtime, SUM(allowances) as allowances, SUM(deductions) as deductions, SUM(net_salary) as net')
                                     ->first();

        // Monthly invoices
        $monthlyInvoices = Invoice::whereMonth('created_at', Carbon::parse($month)->month)
                                 ->whereYear('created_at', Carbon::parse($month)->year)
                                 ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
                                 ->groupBy('status')
                                 ->get();

        $overview = [
            'month' => $month,
            'attendance' => [
                'present' => $monthlyAttendance['present'] ?? 0,
                'absent' => $monthlyAttendance['absent'] ?? 0,
                'late' => $monthlyAttendance['late'] ?? 0,
                'half_day' => $monthlyAttendance['half_day'] ?? 0
            ],
            'payroll' => [
                'basic_salary' => $monthlyPayroll->basic ?? 0,
                'overtime' => $monthlyPayroll->overtime ?? 0,
                'allowances' => $monthlyPayroll->allowances ?? 0,
                'deductions' => $monthlyPayroll->deductions ?? 0,
                'net_salary' => $monthlyPayroll->net ?? 0
            ],
            'invoices' => $monthlyInvoices
        ];

        return $this->successResponse($overview, 'Monthly overview retrieved successfully');
    }
}
