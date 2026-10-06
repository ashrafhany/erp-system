@extends('layouts.app')

@section('title', 'إدارة الرواتب')
@section('page-title', 'إدارة الرواتب')

@section('page-actions')
    @can('payroll.create')
    <a href="{{ route('payroll.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إنشاء راتب جديد
    </a>
    @endcan
@endsection

@section('content')
<!-- فلاتر البحث -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payroll.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="month" class="form-label">الشهر</label>
                    <input type="month" class="form-control" id="month" name="month"
                           value="{{ request('month', now()->format('Y-m')) }}">
                </div>
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">الموظف</label>
                    <select class="form-select" id="employee_id" name="employee_id">
                        <option value="">جميع الموظفين</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                    {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">الحالة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh me-2"></i>إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- بطاقات الإحصائيات -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="إجمالي السجلات" :value="$payrolls->total()" icon="fas fa-money-bill-wave" color="info" />
    </div>
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="مدفوع" :value="$statusCounts['paid'] ?? 0" icon="fas fa-circle-check" color="success" />
    </div>
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="معتمد" :value="$statusCounts['approved'] ?? 0" icon="fas fa-stamp" color="warning" />
    </div>
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="مسودة" :value="$statusCounts['draft'] ?? 0" icon="far fa-file-lines" color="secondary" />
    </div>
</div>

<!-- قائمة الرواتب -->
<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-money-bill-wave me-2"></i>
            سجلات الرواتب
        </h6>
    </div>
    <div class="card-body">
        @if($payrolls->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الموظف</th>
                            <th>الشهر</th>
                            <th>الراتب الأساسي</th>
                            <th>الساعات الإضافية</th>
                            <th>البدلات</th>
                            <th>الخصومات</th>
                            <th>الراتب الصافي</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $payroll)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar avatar-sm avatar-gradient">{{ $payroll->employee->initials }}</span>
                                    <div>
                                        <div class="fw-bold">{{ $payroll->employee->full_name }}</div>
                                        <small class="text-muted">{{ $payroll->employee->employee_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $payroll->formatted_month }}</td>
                            <td>{{ number_format($payroll->basic_salary, 2) }} ج.م</td>
                            <td>
                                @if($payroll->overtime_hours > 0)
                                    {{ $payroll->overtime_hours }} ساعة
                                    <br><small class="text-muted">{{ number_format($payroll->overtime_amount, 2) }} ج.م</small>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($payroll->allowances > 0)
                                    {{ number_format($payroll->allowances, 2) }} ج.م
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($payroll->deductions > 0)
                                    {{ number_format($payroll->deductions, 2) }} ج.م
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">{{ number_format($payroll->net_salary, 2) }} ج.م</span>
                            </td>
                            <td><x-status-badge type="payroll" :status="$payroll->status" /></td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" title="عرض" aria-label="عرض">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    @if($payroll->status != 'paid')
                                        @can('payroll.update')
                                        <a href="{{ route('payroll.edit', $payroll) }}" class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip" title="تعديل" aria-label="تعديل">
                                            <i class="far fa-pen-to-square"></i>
                                        </a>
                                        @endcan
                                        @if($payroll->status == 'draft')
                                            @can('payroll.approve')
                                            <form action="{{ route('payroll.approve', $payroll) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                        data-confirm="تأكيد اعتماد راتب {{ $payroll->employee->full_name }}؟" data-confirm-ok="اعتماد"
                                                        data-bs-toggle="tooltip" title="اعتماد" aria-label="اعتماد">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        @endif
                                        @can('payroll.delete')
                                        <form action="{{ route('payroll.destroy', $payroll) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-confirm="هل أنت متأكد من حذف سجل راتب {{ $payroll->employee->full_name }}؟"
                                                    data-bs-toggle="tooltip" title="حذف" aria-label="حذف">
                                                <i class="far fa-trash-can"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- الترقيم -->
            <div class="d-flex justify-content-center mt-4">
                {{ $payrolls->withQueryString()->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-money-bill-wave"></i></div>
                <h5>لا توجد سجلات رواتب</h5>
                <p class="text-muted">ابدأ بإنشاء سجل راتب جديد</p>
                @can('payroll.create')
                <a href="{{ route('payroll.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إنشاء راتب جديد
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
