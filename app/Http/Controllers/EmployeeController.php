<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::latest()->paginate(10);
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|unique:employees',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'department' => 'required|string',
            'position' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        Employee::create($request->all());

        return redirect()->route('employees.index')
                       ->with('success', 'تم إضافة الموظف بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['attendances' => function($query) {
            $query->latest()->take(10);
        }, 'payrollRecords' => function($query) {
            $query->latest()->take(6);
        }]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|unique:employees,employee_id,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'department' => 'required|string',
            'position' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $employee->update($request->all());

        return redirect()->route('employees.index')
                       ->with('success', 'تم تحديث بيانات الموظف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
                       ->with('success', 'تم حذف الموظف بنجاح');
    }
}
