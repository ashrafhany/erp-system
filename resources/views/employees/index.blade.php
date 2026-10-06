@extends('layouts.app')

@section('title', 'إدارة الموظفين')
@section('page-title', 'إدارة الموظفين')

@section('page-actions')
    @can('employees.create')
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إضافة موظف جديد
    </a>
    @endcan
@endsection

@section('content')
<!-- فلاتر البحث -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">البحث</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="الاسم أو رقم الموظف أو البريد">
                </div>
                <div class="col-md-3">
                    <label for="department" class="form-label">القسم</label>
                    <select class="form-select" id="department" name="department">
                        <option value="">كل الأقسام</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">الحالة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>منتهي الخدمة</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh me-2"></i>إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users me-2"></i>
            قائمة الموظفين
        </h6>
        <small class="text-muted">{{ $employees->total() }} موظف</small>
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
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar avatar-sm avatar-gradient">{{ $employee->initials }}</span>
                                    <div>
                                        <div class="fw-bold">{{ $employee->full_name }}</div>
                                        <small class="text-muted">{{ $employee->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->department }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>{{ number_format($employee->basic_salary, 2) }} ج.م</td>
                            <td><x-status-badge type="employee" :status="$employee->status" /></td>
                            <td>{{ $employee->hire_date->format('Y-m-d') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" title="عرض" aria-label="عرض">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    @can('employees.update')
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" title="تعديل" aria-label="تعديل">
                                        <i class="far fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('employees.delete')
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                data-confirm="هل أنت متأكد من حذف الموظف {{ $employee->full_name }}؟"
                                                data-bs-toggle="tooltip" title="حذف" aria-label="حذف">
                                            <i class="far fa-trash-can"></i>
                                        </button>
                                    </form>
                                    @endcan
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
        @elseif(request()->anyFilled(['search', 'department', 'status']))
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-magnifying-glass"></i></div>
                <h5>لا توجد نتائج مطابقة</h5>
                <p class="text-muted">جرّب كلمات بحث أو فلاتر مختلفة</p>
                <a href="{{ route('employees.index') }}" class="btn btn-soft">إعادة تعيين الفلاتر</a>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                <h5>لا يوجد موظفين مسجلين</h5>
                <p class="text-muted">ابدأ بإضافة موظف جديد لإدارة فريق العمل</p>
                @can('employees.create')
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة موظف جديد
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
