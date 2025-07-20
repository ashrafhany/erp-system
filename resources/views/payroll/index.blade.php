@extends('layouts.app')

@section('title', 'إدارة الرواتب')
@section('page-title', 'إدارة الرواتب')

@section('page-actions')
    <a href="{{ route('payroll.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إنشاء راتب جديد
    </a>
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
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $payrolls->total() }}</h5>
                        <small>إجمالي السجلات</small>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $payrolls->where('status', 'paid')->count() }}</h5>
                        <small>مدفوع</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $payrolls->where('status', 'approved')->count() }}</h5>
                        <small>معتمد</small>
                    </div>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $payrolls->where('status', 'draft')->count() }}</h5>
                        <small>مسودة</small>
                    </div>
                    <i class="fas fa-edit fa-2x"></i>
                </div>
            </div>
        </div>
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
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 35px; height: 35px;">
                                            <span class="text-white fw-bold small">
                                                {{ substr($payroll->employee->first_name, 0, 1) }}{{ substr($payroll->employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
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
                            <td>
                                @if($payroll->status == 'draft')
                                    <span class="badge bg-secondary">مسودة</span>
                                @elseif($payroll->status == 'approved')
                                    <span class="badge bg-warning">معتمد</span>
                                @else
                                    <span class="badge bg-success">مدفوع</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('payroll.show', $payroll) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($payroll->status != 'paid')
                                        <a href="{{ route('payroll.edit', $payroll) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($payroll->status == 'draft')
                                            <form action="{{ route('payroll.approve', $payroll) }}"
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                        onclick="return confirm('تأكيد اعتماد الراتب؟')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('payroll.destroy', $payroll) }}"
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
            <div class="text-center py-5">
                <i class="fas fa-money-bill-wave fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">لا توجد سجلات رواتب</h5>
                <p class="text-muted">ابدأ بإنشاء سجل راتب جديد</p>
                <a href="{{ route('payroll.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إنشاء راتب جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
