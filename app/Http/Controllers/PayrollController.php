<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->requireCrudPermissions('payroll');
        $this->middleware('permission:payroll.view')->only('print');
        $this->middleware('permission:payroll.generate')->only('generatePayroll');
        $this->middleware('permission:payroll.approve')->only('approve');
        $this->middleware('permission:payroll.pay')->only('markPaid');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PayrollRecord::with('employee');

        // فلترة بالشهر
        if ($request->filled('month')) {
            $query->where('payroll_month', $request->month);
        } else {
            // افتراضياً عرض الشهر الحالي
            $query->where('payroll_month', now()->format('Y-m'));
        }

        // فلترة بالموظف
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // فلترة بالحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // العدّ من الاستعلام كاملاً وليس من الصفحة الحالية فقط
        $statusCounts = (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $payrolls = $query->latest()->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->get();

        return view('payroll.index', compact('payrolls', 'employees', 'statusCounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        return view('payroll.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // التحقق من عدم وجود سجل راتب لنفس الموظف في نفس الشهر
        $existingPayroll = PayrollRecord::where('employee_id', $request->employee_id)
                                      ->where('payroll_month', $request->payroll_month)
                                      ->first();

        if ($existingPayroll) {
            return redirect()->back()
                           ->withErrors(['payroll_month' => 'يوجد سجل راتب لهذا الموظف في هذا الشهر'])
                           ->withInput();
        }

        $payroll = PayrollRecord::create($validator->validated() + ['status' => 'draft', 'gross_salary' => 0, 'net_salary' => 0]);
        $payroll->calculateSalary();

        return redirect()->route('payroll.index')
                       ->with('success', 'تم إنشاء سجل الراتب بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollRecord $payroll)
    {
        $payroll->load('employee');
        return view('payroll.show', compact('payroll'));
    }

    public function print(PayrollRecord $payroll)
    {
        $payroll->load('employee');
        return view('payroll.print', compact('payroll'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayrollRecord $payroll)
    {
        abort_unless($payroll->status === 'draft', 403);
        $employees = Employee::where('status', 'active')->get();
        return view('payroll.edit', compact('payroll', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollRecord $payroll)
    {
        abort_unless($payroll->status === 'draft', 403);
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'payroll_month' => 'required|date_format:Y-m',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // التحقق من عدم وجود سجل آخر لنفس الموظف في نفس الشهر
        $existingPayroll = PayrollRecord::where('employee_id', $request->employee_id)
                                      ->where('payroll_month', $request->payroll_month)
                                      ->where('id', '!=', $payroll->id)
                                      ->first();

        if ($existingPayroll) {
            return redirect()->back()
                           ->withErrors(['payroll_month' => 'يوجد سجل راتب آخر لهذا الموظف في هذا الشهر'])
                           ->withInput();
        }

        $payroll->update($validator->validated());
        $payroll->calculateSalary();

        return redirect()->route('payroll.index')
                       ->with('success', 'تم تحديث سجل الراتب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollRecord $payroll)
    {
        abort_unless($payroll->status === 'draft', 403);
        $payroll->delete();

        return redirect()->route('payroll.index')
                       ->with('success', 'تم حذف سجل الراتب بنجاح');
    }

    /**
     * إنشاء راتب تلقائي للموظف
     */
    public function generatePayroll(Employee $employee, Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        // التحقق من عدم وجود سجل راتب لهذا الشهر
        $existingPayroll = PayrollRecord::where('employee_id', $employee->id)
                                      ->where('payroll_month', $month)
                                      ->first();

        if ($existingPayroll) {
            return redirect()->back()
                           ->with('error', 'يوجد سجل راتب لهذا الموظف في هذا الشهر');
        }

        // إنشاء سجل راتب جديد بالراتب الأساسي
        $payroll = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_month' => $month,
            'basic_salary' => $employee->basic_salary,
            'overtime_hours' => 0,
            'overtime_rate' => 50, // معدل افتراضي للساعة الإضافية
            'overtime_amount' => 0,
            'allowances' => 0,
            'deductions' => 0,
            'gross_salary' => $employee->basic_salary,
            'tax_amount' => 0,
            'net_salary' => $employee->basic_salary,
            'status' => 'draft'
        ]);

        return redirect()->route('payroll.edit', $payroll)
                       ->with('success', 'تم إنشاء سجل راتب للموظف ' . $employee->full_name);
    }

    /**
     * اعتماد الراتب
     */
    public function approve(PayrollRecord $payroll)
    {
        abort_unless($payroll->status === 'draft', 403);
        $payroll->update([
            'status' => 'approved',
        ]);

        return redirect()->back()
                       ->with('success', 'تم اعتماد راتب ' . $payroll->employee->full_name);
    }

    public function markPaid(Request $request, PayrollRecord $payroll)
    {
        abort_unless($payroll->status === 'approved', 403);
        $data = $request->validate(['payment_date' => ['required', 'date']]);
        $payroll->update(['status' => 'paid', 'payment_date' => $data['payment_date']]);

        return back()->with('success', 'تم تسجيل دفع راتب ' . $payroll->employee->full_name);
    }
}
