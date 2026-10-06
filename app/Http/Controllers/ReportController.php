<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view');
        $this->middleware('permission:payroll.view')->only('payroll');
        $this->middleware('permission:attendance.view')->only('attendance');
        $this->middleware('permission:invoices.view')->only(['receivables', 'sales']);
    }

    public function index()
    {
        return view('reports.index');
    }

    public function payroll(Request $request)
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:draft,approved,paid'],
        ]);
        $month = $filters['month'] ?? now()->format('Y-m');

        $records = PayrollRecord::with('employee')
            ->where('payroll_month', $month)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->get()
            ->sortBy(fn ($record) => $record->employee->full_name)
            ->values();

        $totals = [];
        foreach (['basic_salary', 'overtime_amount', 'allowances', 'gross_salary', 'deductions', 'tax_amount', 'net_salary'] as $column) {
            $totals[$column] = $records->sum($column);
        }

        return view('reports.payroll', compact('records', 'totals', 'month'));
    }

    public function attendance(Request $request)
    {
        $filters = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $filters['month'] ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();

        $stats = Attendance::whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->selectRaw("employee_id,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                COALESCE(SUM(total_hours), 0) as hours")
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $employees = Employee::where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get();

        return view('reports.attendance', compact('employees', 'stats', 'month'));
    }

    public function receivables()
    {
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['sent', 'overdue'])
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->orderBy('due_date')
            ->get();

        $isOverdue = fn ($invoice) => $invoice->status === 'overdue' || $invoice->due_date->lt(today());
        $remaining = fn ($invoice) => $invoice->total_amount - $invoice->paid_amount;

        $groups = $invoices->groupBy('customer_id')->map(fn ($items) => [
            'customer' => $items->first()->customer,
            'invoices' => $items,
            'remaining' => $items->sum($remaining),
            'overdue' => $items->filter($isOverdue)->sum($remaining),
        ])->sortByDesc('remaining')->values();

        return view('reports.receivables', [
            'groups' => $groups,
            'totalRemaining' => $groups->sum('remaining'),
            'totalOverdue' => $groups->sum('overdue'),
        ]);
    }

    public function sales(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();

        $invoices = Invoice::with('customer')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->orderBy('invoice_date')
            ->get();

        $totals = [
            'count' => $invoices->count(),
            'subtotal' => $invoices->sum('subtotal'),
            'discount' => $invoices->sum('discount_amount'),
            'tax' => $invoices->sum('tax_amount'),
            'total' => $invoices->sum('total_amount'),
            'paid' => $invoices->sum('paid_amount'),
        ];
        $totals['remaining'] = $totals['total'] - $totals['paid'];

        return view('reports.sales', compact('invoices', 'totals', 'from', 'to'));
    }
}
