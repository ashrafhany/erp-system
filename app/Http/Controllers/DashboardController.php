<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Attendance;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dashboard.view')->only('index');
    }

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

        // الفواتير المتأخرة
        $overdueInvoices = Invoice::overdue()->count();

        // توزيع الفواتير حسب الحالة؛ المرسلة التي تجاوزت الاستحقاق تُحسب ضمن المتأخرة
        $statusCounts = Invoice::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $sentOverdue = $overdueInvoices - ($statusCounts['overdue'] ?? 0);
        $invoiceStatus = [
            ['label' => 'مدفوعة', 'value' => (int) ($statusCounts['paid'] ?? 0), 'color' => '#10b981'],
            ['label' => 'مرسلة', 'value' => max(0, ($statusCounts['sent'] ?? 0) - $sentOverdue), 'color' => '#6366f1'],
            ['label' => 'متأخرة', 'value' => $overdueInvoices, 'color' => '#ef4444'],
            ['label' => 'مسودة', 'value' => (int) ($statusCounts['draft'] ?? 0), 'color' => '#94a3b8'],
            ['label' => 'ملغاة', 'value' => (int) ($statusCounts['cancelled'] ?? 0), 'color' => '#f59e0b'],
        ];

        // المبيعات والتحصيل لآخر 6 شهور (التجميع في PHP ليعمل على أي قاعدة بيانات)
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $from = $months->first()->toDateString();

        $sales = Invoice::whereNotIn('status', ['draft', 'cancelled'])
            ->whereDate('invoice_date', '>=', $from)
            ->get(['invoice_date', 'total_amount'])
            ->groupBy(fn ($invoice) => $invoice->invoice_date->format('Y-m'))
            ->map(fn ($group) => $group->sum('total_amount'));

        $collected = InvoicePayment::whereDate('payment_date', '>=', $from)
            ->get(['payment_date', 'amount'])
            ->groupBy(fn ($payment) => $payment->payment_date->format('Y-m'))
            ->map(fn ($group) => $group->sum('amount'));

        $salesChart = [
            'labels' => $months->map(fn ($m) => $m->locale('ar')->translatedFormat('F'))->values(),
            'sales' => $months->map(fn ($m) => round((float) ($sales[$m->format('Y-m')] ?? 0), 2))->values(),
            'collected' => $months->map(fn ($m) => round((float) ($collected[$m->format('Y-m')] ?? 0), 2))->values(),
        ];

        // نسبة تغير التحصيل عن الشهر الماضي
        [$lastMonth, $thisMonth] = array_slice($salesChart['collected']->all(), -2);
        $collectionTrend = $lastMonth > 0 ? (int) round(($thisMonth - $lastMonth) / $lastMonth * 100) : null;

        // الحضور اليوم مفصّلاً حسب الحالة
        $todayCounts = Attendance::whereDate('date', today())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $todayAttendance = (int) $todayCounts->sum();
        $presentToday = (int) ($todayCounts['present'] ?? 0);
        $attendanceToday = [
            ['label' => 'حاضر', 'value' => $presentToday, 'color' => '#10b981'],
            ['label' => 'متأخر', 'value' => (int) ($todayCounts['late'] ?? 0), 'color' => '#f59e0b'],
            ['label' => 'نصف يوم', 'value' => (int) ($todayCounts['half_day'] ?? 0), 'color' => '#0ea5e9'],
            ['label' => 'غائب', 'value' => (int) ($todayCounts['absent'] ?? 0), 'color' => '#ef4444'],
            ['label' => 'لم يُسجَّل', 'value' => max(0, $totalEmployees - $todayAttendance), 'color' => '#cbd5e1'],
        ];
        $attendedToday = $presentToday + ($todayCounts['late'] ?? 0) + ($todayCounts['half_day'] ?? 0);
        $attendanceRate = $totalEmployees > 0 ? (int) round($attendedToday / $totalEmployees * 100) : 0;

        // آخر الفواتير
        $recentInvoices = Invoice::with('customer')
                               ->latest()
                               ->take(5)
                               ->get();

        // الموظفون الجدد هذا الشهر
        $newEmployees = Employee::whereMonth('hire_date', now()->month)
                              ->whereYear('hire_date', now()->year)
                              ->count();

        // منتجات نشطة قاربت على النفاد
        $lowStockProducts = Product::where('status', 'active')
            ->withStock()
            ->lowStock(ProductController::LOW_STOCK_THRESHOLD)
            ->orderByRaw(Product::STOCK_SQL)
            ->take(5)
            ->get();

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
            'newEmployees',
            'invoiceStatus',
            'salesChart',
            'collectionTrend',
            'attendanceToday',
            'attendanceRate',
            'lowStockProducts'
        ));
    }
}
