@extends('layouts.app')

@section('title', 'تفاصيل سجل الراتب')
@section('page-title', 'تفاصيل سجل الراتب')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للرواتب
        </a>
        @if($payroll->status === 'pending')
            <a href="{{ route('payroll.edit', $payroll) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>
                تعديل
            </a>
            <form action="{{ route('payroll.approve', $payroll) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success" onclick="return confirm('هل أنت متأكد من اعتماد هذا السجل؟')">
                    <i class="fas fa-check me-2"></i>
                    اعتماد
                </button>
            </form>
        @endif
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-print me-2"></i>
                طباعة/تصدير
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </a></li>
                <li><a class="dropdown-item" href="{{ route('payroll.export.pdf', $payroll) }}">
                    <i class="fas fa-file-pdf me-2"></i>تصدير PDF
                </a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- معلومات الراتب الأساسية -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        سجل راتب: {{ $payroll->employee->name }}
                    </h5>
                    <span class="badge badge-{{ $payroll->status === 'approved' ? 'success' : ($payroll->status === 'pending' ? 'warning' : 'secondary') }} fs-6">
                        @if($payroll->status === 'approved')
                            <i class="fas fa-check-circle me-1"></i> معتمد
                        @elseif($payroll->status === 'pending')
                            <i class="fas fa-clock me-1"></i> في الانتظار
                        @else
                            <i class="fas fa-times-circle me-1"></i> مرفوض
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">الموظف:</label>
                            <p class="fw-bold">{{ $payroll->employee->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">القسم:</label>
                            <p class="fw-bold">{{ $payroll->employee->department ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">شهر الراتب:</label>
                            <p class="fw-bold">{{ \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">تاريخ الإنشاء:</label>
                            <p class="fw-bold">{{ $payroll->created_at->format('d/m/Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- تفاصيل الراتب -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            تفاصيل الراتب
                        </h6>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">الراتب الأساسي:</span>
                                <span class="fw-bold">{{ number_format($payroll->basic_salary, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">ساعات العمل الإضافي:</span>
                                <span class="fw-bold">{{ $payroll->overtime_hours }} ساعة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">معدل الساعة الإضافية:</span>
                                <span class="fw-bold">{{ number_format($payroll->overtime_rate, 2) }} ر.س/ساعة</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">إجمالي أجر العمل الإضافي:</span>
                                <span class="fw-bold text-success">{{ number_format($payroll->overtime_hours * $payroll->overtime_rate, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">البدلات والمكافآت:</span>
                                <span class="fw-bold text-success">{{ number_format($payroll->allowances, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">الخصومات:</span>
                                <span class="fw-bold text-danger">{{ number_format($payroll->deductions, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">الضرائب:</span>
                                <span class="fw-bold text-danger">{{ number_format($payroll->tax_amount, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salary-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">إجمالي الدخل:</span>
                                <span class="fw-bold">{{ number_format($payroll->gross_salary, 2) }} ر.س</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($payroll->notes)
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-sticky-note me-2"></i>
                            ملاحظات
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0">{{ $payroll->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- سجل التعديلات (إذا وُجدت) -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    سجل التعديلات
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">إنشاء السجل</h6>
                            <p class="timeline-text text-muted">
                                تم إنشاء سجل الراتب بواسطة النظام
                            </p>
                            <small class="timeline-time text-muted">
                                {{ $payroll->created_at->format('d/m/Y h:i A') }}
                            </small>
                        </div>
                    </div>

                    @if($payroll->updated_at != $payroll->created_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">تحديث السجل</h6>
                            <p class="timeline-text text-muted">
                                تم تحديث بيانات السجل
                            </p>
                            <small class="timeline-time text-muted">
                                {{ $payroll->updated_at->format('d/m/Y h:i A') }}
                            </small>
                        </div>
                    </div>
                    @endif

                    @if($payroll->status === 'approved')
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">اعتماد السجل</h6>
                            <p class="timeline-text text-muted">
                                تم اعتماد سجل الراتب
                            </p>
                            <small class="timeline-time text-muted">
                                {{ $payroll->updated_at->format('d/m/Y h:i A') }}
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ملخص الراتب -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
                <h6 class="card-title mb-0 text-center">
                    <i class="fas fa-calculator me-2"></i>
                    ملخص الراتب النهائي
                </h6>
            </div>
            <div class="card-body">
                <div class="salary-summary">
                    <!-- الإيرادات -->
                    <div class="mb-3">
                        <h6 class="text-success border-bottom border-success pb-1">
                            <i class="fas fa-plus-circle me-2"></i>الإيرادات
                        </h6>
                        <div class="row mb-2">
                            <div class="col-8">الراتب الأساسي:</div>
                            <div class="col-4 text-end">{{ number_format($payroll->basic_salary, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">أجر العمل الإضافي:</div>
                            <div class="col-4 text-end">{{ number_format($payroll->overtime_hours * $payroll->overtime_rate, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">البدلات:</div>
                            <div class="col-4 text-end">{{ number_format($payroll->allowances, 2) }}</div>
                        </div>
                        <div class="row mb-2 border-top pt-2">
                            <div class="col-8"><strong>إجمالي الإيرادات:</strong></div>
                            <div class="col-4 text-end">
                                <strong class="text-success">{{ number_format($payroll->gross_salary, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- الخصومات -->
                    <div class="mb-3">
                        <h6 class="text-danger border-bottom border-danger pb-1">
                            <i class="fas fa-minus-circle me-2"></i>الخصومات
                        </h6>
                        <div class="row mb-2">
                            <div class="col-8">الخصومات:</div>
                            <div class="col-4 text-end text-danger">{{ number_format($payroll->deductions, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-8">الضرائب:</div>
                            <div class="col-4 text-end text-danger">{{ number_format($payroll->tax_amount, 2) }}</div>
                        </div>
                        <div class="row mb-2 border-top pt-2">
                            <div class="col-8"><strong>إجمالي الخصومات:</strong></div>
                            <div class="col-4 text-end">
                                <strong class="text-danger">{{ number_format($payroll->deductions + $payroll->tax_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- صافي الراتب -->
                    <div class="bg-primary text-white p-3 rounded">
                        <div class="row">
                            <div class="col-7">
                                <h5 class="mb-0">صافي الراتب:</h5>
                            </div>
                            <div class="col-5 text-end">
                                <h4 class="mb-0">{{ number_format($payroll->net_salary, 2) }}</h4>
                                <small>ر.س</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- معلومات الموظف -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    معلومات الموظف
                </h6>
            </div>
            <div class="card-body">
                <div class="employee-info">
                    <div class="text-center mb-3">
                        <div class="employee-avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                        <h6 class="mt-2 mb-0">{{ $payroll->employee->name }}</h6>
                        <small class="text-muted">{{ $payroll->employee->department ?? 'غير محدد' }}</small>
                    </div>

                    <div class="employee-details">
                        <div class="row mb-2">
                            <div class="col-6 text-muted">رقم الموظف:</div>
                            <div class="col-6 text-end">#{{ $payroll->employee->id }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">البريد الإلكتروني:</div>
                            <div class="col-6 text-end">{{ $payroll->employee->email ?? 'غير محدد' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">الراتب الأساسي:</div>
                            <div class="col-6 text-end">{{ number_format($payroll->employee->salary ?? 0, 2) }} ر.س</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">تاريخ التوظيف:</div>
                            <div class="col-6 text-end">{{ $payroll->employee->hire_date ? \Carbon\Carbon::parse($payroll->employee->hire_date)->format('d/m/Y') : 'غير محدد' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصائيات سريعة -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    إحصائيات سريعة
                </h6>
            </div>
            <div class="card-body">
                <div class="stats">
                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-info text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h6 class="mb-0">{{ $payroll->deductions > 0 ? number_format(($payroll->deductions / $payroll->gross_salary) * 100, 1) : 0 }}%</h6>
                        <small class="text-muted">نسبة الخصومات</small>
                    </div>

                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-warning text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="mb-0">{{ $payroll->overtime_hours }}</h6>
                        <small class="text-muted">ساعات إضافية</small>
                    </div>

                    <div class="stat-item text-center">
                        <div class="stat-icon bg-success text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($payroll->allowances, 0) }}</h6>
                        <small class="text-muted">البدلات (ر.س)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.875em;
    padding: 0.5em 0.75em;
}

.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    display: block;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.salary-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -1.625rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.timeline-time {
    font-size: 0.75rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.employee-avatar {
    font-size: 1.5rem;
}

.stat-icon {
    font-size: 0.875rem;
}

@media print {
    .btn-group,
    .card:last-child,
    .timeline {
        display: none !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
