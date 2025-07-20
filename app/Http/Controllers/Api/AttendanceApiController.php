<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceApiController extends BaseApiController
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with('employee');

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            // Default to today's attendance
            $query->whereDate('date', today());
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Month filter
        if ($request->filled('month')) {
            $query->where('date', 'like', $request->month . '%');
        }

        $perPage = $request->get('per_page', 15);
        $attendance = $query->latest()->paginate($perPage);

        return $this->successResponse($attendance, 'Attendance records retrieved successfully');
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request): JsonResponse
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
            return $this->validationError($validator->errors());
        }

        // Check for existing attendance record
        $existingAttendance = Attendance::where('employee_id', $request->employee_id)
                                      ->whereDate('date', $request->date)
                                      ->first();

        if ($existingAttendance) {
            return $this->errorResponse('Attendance record already exists for this employee on this date', 409);
        }

        $attendance = Attendance::create($request->all());

        // Calculate total hours if both check_in and check_out are provided
        if ($request->check_in && $request->check_out) {
            $attendance->calculateTotalHours();
        }

        $attendance->load('employee');

        return $this->successResponse($attendance, 'Attendance record created successfully', 201);
    }

    /**
     * Display the specified attendance record
     */
    public function show(Attendance $attendance): JsonResponse
    {
        $attendance->load('employee');

        return $this->successResponse($attendance, 'Attendance record retrieved successfully');
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|exists:employees,id',
            'date' => 'sometimes|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'sometimes|in:present,absent,late,half_day',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // Check for duplicate if employee_id or date is being updated
        if ($request->has('employee_id') || $request->has('date')) {
            $employeeId = $request->get('employee_id', $attendance->employee_id);
            $date = $request->get('date', $attendance->date);

            $existingAttendance = Attendance::where('employee_id', $employeeId)
                                          ->whereDate('date', $date)
                                          ->where('id', '!=', $attendance->id)
                                          ->first();

            if ($existingAttendance) {
                return $this->errorResponse('Another attendance record exists for this employee on this date', 409);
            }
        }

        $attendance->update($request->all());

        // Recalculate total hours if check_in or check_out is updated
        if ($request->has('check_in') || $request->has('check_out')) {
            if ($attendance->check_in && $attendance->check_out) {
                $attendance->calculateTotalHours();
            }
        }

        $attendance->load('employee');

        return $this->successResponse($attendance, 'Attendance record updated successfully');
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();

        return $this->successResponse(null, 'Attendance record deleted successfully');
    }

    /**
     * Employee check-in
     */
    public function checkIn(Employee $employee): JsonResponse
    {
        $today = today();

        // Check if already checked in today
        $attendance = Attendance::where('employee_id', $employee->id)
                               ->whereDate('date', $today)
                               ->first();

        if ($attendance && $attendance->check_in) {
            return $this->errorResponse('Employee has already checked in today', 409);
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

        $attendance->load('employee');

        return $this->successResponse($attendance, 'Employee checked in successfully');
    }

    /**
     * Employee check-out
     */
    public function checkOut(Employee $employee): JsonResponse
    {
        $today = today();

        $attendance = Attendance::where('employee_id', $employee->id)
                               ->whereDate('date', $today)
                               ->first();

        if (!$attendance || !$attendance->check_in) {
            return $this->errorResponse('Employee must check in first', 400);
        }

        if ($attendance->check_out) {
            return $this->errorResponse('Employee has already checked out today', 409);
        }

        $attendance->update([
            'check_out' => now()->format('H:i:s')
        ]);

        $attendance->calculateTotalHours();
        $attendance->load('employee');

        return $this->successResponse($attendance, 'Employee checked out successfully');
    }

    /**
     * Daily attendance report
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $date = $request->get('date', today());

        $attendance = Attendance::with('employee')
                               ->whereDate('date', $date)
                               ->get();

        $totalEmployees = Employee::where('status', 'active')->count();
        $presentCount = $attendance->where('status', 'present')->count();
        $absentCount = $attendance->where('status', 'absent')->count();
        $lateCount = $attendance->where('status', 'late')->count();
        $halfDayCount = $attendance->where('status', 'half_day')->count();

        $report = [
            'date' => $date,
            'total_employees' => $totalEmployees,
            'summary' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'half_day' => $halfDayCount,
                'attendance_rate' => $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 2) : 0
            ],
            'attendance_records' => $attendance
        ];

        return $this->successResponse($report, 'Daily attendance report generated successfully');
    }

    /**
     * Monthly attendance report
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));

        $attendance = Attendance::with('employee')
                               ->where('date', 'like', $month . '%')
                               ->get();

        $employeeStats = [];
        $totalEmployees = Employee::where('status', 'active')->count();

        foreach ($attendance->groupBy('employee_id') as $employeeId => $records) {
            $employee = $records->first()->employee;
            $employeeStats[] = [
                'employee' => $employee,
                'total_days' => $records->count(),
                'present_days' => $records->where('status', 'present')->count(),
                'absent_days' => $records->where('status', 'absent')->count(),
                'late_days' => $records->where('status', 'late')->count(),
                'half_days' => $records->where('status', 'half_day')->count(),
                'total_hours' => $records->sum('total_hours'),
                'attendance_rate' => $records->count() > 0 ?
                    round(($records->where('status', 'present')->count() / $records->count()) * 100, 2) : 0
            ];
        }

        $report = [
            'month' => $month,
            'total_employees' => $totalEmployees,
            'employee_statistics' => $employeeStats,
            'overall_summary' => [
                'total_records' => $attendance->count(),
                'present_records' => $attendance->where('status', 'present')->count(),
                'absent_records' => $attendance->where('status', 'absent')->count(),
                'late_records' => $attendance->where('status', 'late')->count(),
                'half_day_records' => $attendance->where('status', 'half_day')->count()
            ]
        ];

        return $this->successResponse($report, 'Monthly attendance report generated successfully');
    }
}
