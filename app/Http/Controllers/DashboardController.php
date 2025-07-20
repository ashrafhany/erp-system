<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Attendance;
use App\Models\PayrollRecord;

class DashboardController extends Controller
{
    public function index()
    {
        // إحصائيات عامة
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalCustomers = Customer::where('status', 'active')->count();
        $totalInvoices = Invoice::count();
        $pendingInvoices = Invoice::whereIn('status', ['draft', 'sent'])->count();

        // إحصائيات الفواتير
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $outstandingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total_amount')
                           - Invoice::whereIn('status', ['sent', 'overdue'])->sum('paid_amount');

        // إحصائيات الحضور لليوم
        $todayAttendance = Attendance::whereDate('date', today())->count();
        $presentToday = Attendance::whereDate('date', today())
                                ->where('status', 'present')
                                ->count();

        // الفواتير المتأخرة
        $overdueInvoices = Invoice::where('due_date', '<', today())
                                ->whereIn('status', ['sent', 'overdue'])
                                ->count();

        // آخر الفواتير
        $recentInvoices = Invoice::with('customer')
                               ->latest()
                               ->take(5)
                               ->get();

        // الموظفون الجدد هذا الشهر
        $newEmployees = Employee::whereMonth('hire_date', now()->month)
                              ->whereYear('hire_date', now()->year)
                              ->count();

        return view('dashboard', compact(
            'totalEmployees',
            'totalCustomers',
            'totalInvoices',
            'pendingInvoices',
            'totalRevenue',
            'outstandingAmount',
            'todayAttendance',
            'presentToday',
            'overdueInvoices',
            'recentInvoices',
            'newEmployees'
        ));
    }
}
