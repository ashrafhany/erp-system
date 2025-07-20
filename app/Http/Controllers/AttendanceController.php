<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // فلترة بالتاريخ
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            // افتراضياً عرض حضور اليوم
            $query->whereDate('date', today());
        }

        // فلترة بالموظف
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $attendances = $query->latest()->paginate(15);
        $employees = Employee::where('status', 'active')->get();

        return view('attendance.index', compact('attendances', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        return view('attendance.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,half_day',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // التحقق من عدم وجود سجل حضور لنفس الموظف في نفس اليوم
        $existingAttendance = Attendance::where('employee_id', $request->employee_id)
                                      ->whereDate('date', $request->date)
                                      ->first();

        if ($existingAttendance) {
            return redirect()->back()
                           ->withErrors(['date' => 'يوجد سجل حضور لهذا الموظف في هذا التاريخ'])
                           ->withInput();
        }

        $attendance = Attendance::create($request->all());

        // حساب ساعات العمل إذا تم إدخال وقت الدخول والخروج
        if ($request->check_in && $request->check_out) {
            $attendance->calculateTotalHours();
        }

        return redirect()->route('attendance.index')
                       ->with('success', 'تم تسجيل الحضور بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load('employee');
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $employees = Employee::where('status', 'active')->get();
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,half_day',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // التحقق من عدم وجود سجل آخر لنفس الموظف في نفس اليوم
        $existingAttendance = Attendance::where('employee_id', $request->employee_id)
                                      ->whereDate('date', $request->date)
                                      ->where('id', '!=', $attendance->id)
                                      ->first();

        if ($existingAttendance) {
            return redirect()->back()
                           ->withErrors(['date' => 'يوجد سجل حضور آخر لهذا الموظف في هذا التاريخ'])
                           ->withInput();
        }

        $attendance->update($request->all());

        // حساب ساعات العمل
        if ($request->check_in && $request->check_out) {
            $attendance->calculateTotalHours();
        }

        return redirect()->route('attendance.index')
                       ->with('success', 'تم تحديث سجل الحضور بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('attendance.index')
                       ->with('success', 'تم حذف سجل الحضور بنجاح');
    }

    /**
     * تسجيل دخول الموظف
     */
    public function checkIn(Employee $employee)
    {
        $today = today();

        // التحقق من وجود سجل حضور لليوم
        $attendance = Attendance::where('employee_id', $employee->id)
                               ->whereDate('date', $today)
                               ->first();

        if ($attendance && $attendance->check_in) {
            return redirect()->back()
                           ->with('error', 'تم تسجيل دخول هذا الموظف مسبقاً اليوم');
        }

        if (!$attendance) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => now()->format('H:i:s'),
                'status' => 'present'
            ]);
        } else {
            $attendance->update([
                'check_in' => now()->format('H:i:s'),
                'status' => 'present'
            ]);
        }

        return redirect()->back()
                       ->with('success', 'تم تسجيل دخول ' . $employee->full_name . ' بنجاح');
    }

    /**
     * تسجيل خروج الموظف
     */
    public function checkOut(Employee $employee)
    {
        $today = today();

        $attendance = Attendance::where('employee_id', $employee->id)
                               ->whereDate('date', $today)
                               ->first();

        if (!$attendance || !$attendance->check_in) {
            return redirect()->back()
                           ->with('error', 'يجب تسجيل دخول الموظف أولاً');
        }

        if ($attendance->check_out) {
            return redirect()->back()
                           ->with('error', 'تم تسجيل خروج هذا الموظف مسبقاً');
        }

        $attendance->update([
            'check_out' => now()->format('H:i:s')
        ]);

        $attendance->calculateTotalHours();

        return redirect()->back()
                       ->with('success', 'تم تسجيل خروج ' . $employee->full_name . ' بنجاح');
    }
}
