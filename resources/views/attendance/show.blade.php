@extends('layouts.app')

@section('title', 'تفاصيل سجل الحضور')
@section('page-title', 'تفاصيل سجل الحضور')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للحضور
        </a>
        <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>
            تعديل
        </a>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-print me-2"></i>
                طباعة/تصدير
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </a></li>
                <li><a class="dropdown-item" href="{{ route('attendance.export.pdf', $attendance) }}">
                    <i class="fas fa-file-pdf me-2"></i>تصدير PDF
                </a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- معلومات سجل الحضور -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-clock me-2"></i>
                        سجل حضور: {{ $attendance->employee->name }}
                    </h5>
                    <span class="badge badge-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : ($attendance->status === 'half_day' ? 'info' : 'danger')) }} fs-6">
                        @if($attendance->status === 'present')
                            <i class="fas fa-check-circle me-1"></i> حاضر
                        @elseif($attendance->status === 'late')
                            <i class="fas fa-clock me-1"></i> متأخر
                        @elseif($attendance->status === 'half_day')
                            <i class="fas fa-clock-o me-1"></i> نصف يوم
                        @else
                            <i class="fas fa-times-circle me-1"></i> غائب
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- معلومات أساسية -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">الموظف:</label>
                            <p class="fw-bold">{{ $attendance->employee->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">القسم:</label>
                            <p class="fw-bold">{{ $attendance->employee->department ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">التاريخ:</label>
                            <p class="fw-bold">{{ \Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">يوم الأسبوع:</label>
                            <p class="fw-bold">{{ \Carbon\Carbon::parse($attendance->date)->locale('ar')->dayName }}</p>
                        </div>
                    </div>
                </div>

                <!-- أوقات الحضور -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-clock me-2"></i>
                            أوقات الحضور والانصراف
                        </h6>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="time-card bg-success bg-opacity-10 border border-success rounded p-3">
                            <div class="d-flex align-items-center">
                                <div class="time-icon bg-success text-white rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-success">وقت الدخول</h6>
                                    <h4 class="mb-0 fw-bold">
                                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'لم يتم التسجيل' }}
                                    </h4>
                                    @if($attendance->check_in)
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="time-card bg-danger bg-opacity-10 border border-danger rounded p-3">
                            <div class="d-flex align-items-center">
                                <div class="time-icon bg-danger text-white rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-danger">وقت الخروج</h6>
                                    <h4 class="mb-0 fw-bold">
                                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'لم يتم التسجيل' }}
                                    </h4>
                                    @if($attendance->check_out)
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ساعات العمل -->
                @if($attendance->check_in && $attendance->check_out)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="work-hours-card bg-primary bg-opacity-10 border border-primary rounded p-4">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center">
                                    <div class="hours-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-hourglass-half fa-lg"></i>
                                    </div>
                                    <h6 class="text-primary mb-0">إجمالي ساعات العمل</h6>
                                </div>
                                <div class="col-md-6">
                                    <h2 class="text-primary mb-0 fw-bold text-center">
                                        {{ $attendance->total_hours ? number_format($attendance->total_hours, 2) : '0.00' }} ساعة
                                    </h2>
                                    @if($attendance->total_hours)
                                        <p class="text-center text-muted mb-0">
                                            {{ floor($attendance->total_hours) }} ساعة و {{ round(($attendance->total_hours - floor($attendance->total_hours)) * 60) }} دقيقة
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-3 text-center">
                                    @php
                                        $standardHours = 8; // ساعات العمل المعتادة
                                        $workedHours = $attendance->total_hours ?? 0;
                                        $overtime = max(0, $workedHours - $standardHours);
                                    @endphp
                                    @if($overtime > 0)
                                        <div class="overtime-badge bg-warning text-dark rounded p-2">
                                            <i class="fas fa-plus-circle me-1"></i>
                                            عمل إضافي: {{ number_format($overtime, 2) }} ساعة
                                        </div>
                                    @elseif($workedHours < $standardHours && $workedHours > 0)
                                        <div class="undertime-badge bg-warning text-dark rounded p-2">
                                            <i class="fas fa-minus-circle me-1"></i>
                                            نقص: {{ number_format($standardHours - $workedHours, 2) }} ساعة
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- الملاحظات -->
                @if($attendance->notes)
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-sticky-note me-2"></i>
                            ملاحظات
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0">{{ $attendance->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- الجانب الأيمن -->
    <div class="col-lg-4">
        <!-- ملخص سريع -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    ملخص سريع
                </h6>
            </div>
            <div class="card-body">
                <div class="summary-stats">
                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }} text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-{{ $attendance->status === 'present' ? 'check' : ($attendance->status === 'late' ? 'clock' : 'times') }}"></i>
                        </div>
                        <h6 class="mb-0">{{ ucfirst($attendance->status) }}</h6>
                        <small class="text-muted">حالة الحضور</small>
                    </div>

                    @if($attendance->total_hours)
                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-info text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($attendance->total_hours, 1) }}</h6>
                        <small class="text-muted">ساعات العمل</small>
                    </div>
                    @endif

                    <div class="stat-item text-center">
                        <div class="stat-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <h6 class="mb-0">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m') }}</h6>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->date)->locale('ar')->dayName }}</small>
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
                <div class="employee-info text-center">
                    <div class="employee-avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    <h6 class="mb-1">{{ $attendance->employee->name }}</h6>
                    <small class="text-muted d-block mb-3">{{ $attendance->employee->department ?? 'غير محدد' }}</small>

                    <div class="employee-details text-start">
                        <div class="row mb-2">
                            <div class="col-6 text-muted">رقم الموظف:</div>
                            <div class="col-6 text-end">#{{ $attendance->employee->id }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">البريد الإلكتروني:</div>
                            <div class="col-6 text-end">{{ $attendance->employee->email ?? 'غير محدد' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">الراتب:</div>
                            <div class="col-6 text-end">{{ number_format($attendance->employee->salary ?? 0, 2) }} ر.س</div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-muted">الحالة:</div>
                            <div class="col-6 text-end">
                                <span class="badge badge-{{ $attendance->employee->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $attendance->employee->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الإجراءات السريعة -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(!$attendance->check_in)
                        <form action="{{ route('attendance.checkin', $attendance->employee) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                تسجيل دخول
                            </button>
                        </form>
                    @elseif(!$attendance->check_out)
                        <form action="{{ route('attendance.checkout', $attendance->employee) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                تسجيل خروج
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            تم إكمال يوم العمل
                        </div>
                    @endif

                    <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-edit me-2"></i>
                        تعديل السجل
                    </a>

                    <a href="{{ route('employees.show', $attendance->employee) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user me-2"></i>
                        ملف الموظف
                    </a>
                </div>
            </div>
        </div>

        <!-- إحصائيات الشهر -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calendar me-2"></i>
                    إحصائيات {{ \Carbon\Carbon::parse($attendance->date)->format('F Y') }}
                </h6>
            </div>
            <div class="card-body">
                @php
                    $monthlyStats = \App\Models\Attendance::where('employee_id', $attendance->employee_id)
                        ->whereYear('date', \Carbon\Carbon::parse($attendance->date)->year)
                        ->whereMonth('date', \Carbon\Carbon::parse($attendance->date)->month)
                        ->selectRaw('
                            COUNT(*) as total_days,
                            SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
                            SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
                            SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days,
                            SUM(total_hours) as total_hours
                        ')
                        ->first();
                @endphp

                <div class="monthly-stats">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-box bg-success bg-opacity-10 p-2 rounded">
                                <h6 class="text-success mb-0">{{ $monthlyStats->present_days ?? 0 }}</h6>
                                <small class="text-muted">أيام حضور</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-box bg-danger bg-opacity-10 p-2 rounded">
                                <h6 class="text-danger mb-0">{{ $monthlyStats->absent_days ?? 0 }}</h6>
                                <small class="text-muted">أيام غياب</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box bg-warning bg-opacity-10 p-2 rounded">
                                <h6 class="text-warning mb-0">{{ $monthlyStats->late_days ?? 0 }}</h6>
                                <small class="text-muted">أيام تأخير</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box bg-info bg-opacity-10 p-2 rounded">
                                <h6 class="text-info mb-0">{{ number_format($monthlyStats->total_hours ?? 0, 0) }}</h6>
                                <small class="text-muted">إجمالي ساعات</small>
                            </div>
                        </div>
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

.time-card {
    transition: all 0.3s ease;
}

.time-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.work-hours-card {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
}

.stat-icon {
    font-size: 0.875rem;
}

.employee-avatar {
    font-size: 1.5rem;
}

.stat-box {
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: scale(1.05);
}

@media print {
    .btn-group,
    .card:nth-child(n+2) {
        display: none !important;
    }

    .col-lg-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
