<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends BaseApiController
{
    /**
     * Get dashboard statistics
     */
    public function index(): JsonResponse
    {
        try {
            $today = Carbon::today();
            $currentMonth = Carbon::now()->format('Y-m');

            // Employee statistics
            $totalEmployees = Employee::count();
            $activeEmployees = Employee::where('status', 'active')->count();
            $inactiveEmployees = Employee::where('status', 'inactive')->count();

            // Attendance statistics
            $todayAttendance = Attendance::whereDate('date', $today)->count();
            $todayPresent = Attendance::whereDate('date', $today)
                                   ->where('status', 'present')
                                   ->count();
            $todayAbsent = Attendance::whereDate('date', $today)
                                  ->where('status', 'absent')
                                  ->count();
            $todayLate = Attendance::whereDate('date', $today)
                                ->where('status', 'late')
                                ->count();

            // Payroll statistics
            $currentMonthPayroll = PayrollRecord::where('payroll_month', 'like', $currentMonth . '%')->count();
            $pendingPayroll = PayrollRecord::where('status', 'pending')->count();
            $approvedPayroll = PayrollRecord::where('status', 'approved')->count();
            $totalPayrollAmount = PayrollRecord::where('payroll_month', 'like', $currentMonth . '%')
                                             ->sum('net_salary');

            // Customer statistics
            $totalCustomers = Customer::count();
            $activeCustomers = Customer::where('status', 'active')->count();

            // Invoice statistics
            $totalInvoices = Invoice::count();
            $pendingInvoices = Invoice::where('status', 'pending')->count();
            $paidInvoices = Invoice::where('status', 'paid')->count();
            $overdueInvoices = Invoice::where('status', 'overdue')
                                   ->orWhere(function($query) {
                                       $query->where('status', 'sent')
                                             ->where('due_date', '<', now());
                                   })->count();

            $totalInvoiceAmount = Invoice::sum('total_amount');
            $paidInvoiceAmount = Invoice::sum('paid_amount');
            $pendingInvoiceAmount = $totalInvoiceAmount - $paidInvoiceAmount;

            // Recent activities
            $recentAttendance = Attendance::with('employee')
                                        ->latest()
                                        ->take(5)
                                        ->get();

            $recentInvoices = Invoice::with('customer')
                                   ->latest()
                                   ->take(5)
                                   ->get();

            $recentPayroll = PayrollRecord::with('employee')
                                        ->latest()
                                        ->take(5)
                                        ->get();

            $data = [
                'employees' => [
                    'total' => $totalEmployees,
                    'active' => $activeEmployees,
                    'inactive' => $inactiveEmployees
                ],
                'attendance' => [
                    'today_total' => $todayAttendance,
                    'today_present' => $todayPresent,
                    'today_absent' => $todayAbsent,
                    'today_late' => $todayLate,
                    'attendance_rate' => $activeEmployees > 0 ? round(($todayPresent / $activeEmployees) * 100, 2) : 0
                ],
                'payroll' => [
                    'current_month_count' => $currentMonthPayroll,
                    'pending' => $pendingPayroll,
                    'approved' => $approvedPayroll,
                    'total_amount' => $totalPayrollAmount
                ],
                'customers' => [
                    'total' => $totalCustomers,
                    'active' => $activeCustomers
                ],
                'invoices' => [
                    'total' => $totalInvoices,
                    'pending' => $pendingInvoices,
                    'paid' => $paidInvoices,
                    'overdue' => $overdueInvoices,
                    'total_amount' => $totalInvoiceAmount,
                    'paid_amount' => $paidInvoiceAmount,
                    'pending_amount' => $pendingInvoiceAmount
                ],
                'recent_activities' => [
                    'attendance' => $recentAttendance,
                    'invoices' => $recentInvoices,
                    'payroll' => $recentPayroll
                ]
            ];

            return $this->successResponse($data, 'Dashboard statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve dashboard statistics: ' . $e->getMessage(), 500);
        }
    }
}
