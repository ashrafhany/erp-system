<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->requireCrudPermissions('departments');
    }

    public function index()
    {
        $departments = Department::orderBy('name')->paginate(15);
        $counts = Employee::selectRaw('department, COUNT(*) as total')->groupBy('department')->pluck('total', 'department');
        return view('departments.index', compact('departments', 'counts'));
    }

    public function create()
    {
        return view('departments.form', ['department' => new Department()]);
    }

    public function store(Request $request)
    {
        Department::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:departments,name']]));
        return redirect()->route('departments.index')->with('success', 'تمت إضافة القسم.');
    }

    public function edit(Department $department)
    {
        return view('departments.form', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)]]);
        DB::transaction(function () use ($department, $data) {
            Employee::where('department', $department->name)->update(['department' => $data['name']]);
            $department->update($data);
        });
        return redirect()->route('departments.index')->with('success', 'تم تعديل القسم.');
    }

    public function destroy(Department $department)
    {
        if (Employee::where('department', $department->name)->exists()) {
            return back()->with('error', 'لا يمكن حذف قسم مرتبط بموظفين.');
        }
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'تم حذف القسم.');
    }
}
