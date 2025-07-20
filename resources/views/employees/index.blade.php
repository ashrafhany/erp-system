@extends('layouts.app')

@section('title', 'إدارة الموظفين')
@section('page-title', 'إدارة الموظفين')

@section('page-actions')
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إضافة موظف جديد
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users me-2"></i>
            قائمة الموظفين
        </h6>
    </div>
    <div class="card-body">
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رقم الموظف</th>
                            <th>الاسم</th>
                            <th>القسم</th>
                            <th>المنصب</th>
                            <th>الراتب الأساسي</th>
                            <th>الحالة</th>
                            <th>تاريخ التوظيف</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold">
                                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $employee->full_name }}</div>
                                        <small class="text-muted">{{ $employee->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->department }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>{{ number_format($employee->basic_salary, 2) }} ج.م</td>
                            <td>
                                @if($employee->status == 'active')
                                    <span class="badge bg-success">نشط</span>
                                @elseif($employee->status == 'inactive')
                                    <span class="badge bg-warning">غير نشط</span>
                                @else
                                    <span class="badge bg-danger">منتهي الخدمة</span>
                                @endif
                            </td>
                            <td>{{ $employee->hire_date->format('Y-m-d') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('employees.show', $employee) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee) }}"
                                          method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا الموظف؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- الترقيم -->
            <div class="d-flex justify-content-center mt-4">
                {{ $employees->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">لا يوجد موظفين مسجلين</h5>
                <p class="text-muted">ابدأ بإضافة موظف جديد لإدارة فريق العمل</p>
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة موظف جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
