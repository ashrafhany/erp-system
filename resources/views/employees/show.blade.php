@extends('layouts.app')

@section('title', 'تفاصيل الموظف - ' . $employee->full_name)
@section('page-title', 'تفاصيل الموظف')

@section('page-actions')
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>
        تعديل البيانات
    </a>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        العودة للقائمة
    </a>
@endsection

@section('content')
<div class="row">
    <!-- بيانات الموظف -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user me-2"></i>
                    البيانات الشخصية
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="avatar mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto"
                         style="width: 80px; height: 80px;">
                        <span class="text-white fw-bold h4">
                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                        </span>
                    </div>
                </div>

                <h5 class="fw-bold">{{ $employee->full_name }}</h5>
                <p class="text-muted">{{ $employee->position }}</p>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="fw-bold">{{ $employee->employee_id }}</h6>
                            <small class="text-muted">رقم الموظف</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="fw-bold">{{ $employee->department }}</h6>
                        <small class="text-muted">القسم</small>
                    </div>
                </div>

                <hr>

                <div class="text-start">
                    <p><i class="fas fa-envelope me-2 text-primary"></i> {{ $employee->email }}</p>
                    @if($employee->phone)
                        <p><i class="fas fa-phone me-2 text-primary"></i> {{ $employee->phone }}</p>
                    @endif
                    @if($employee->address)
                        <p><i class="fas fa-map-marker-alt me-2 text-primary"></i> {{ $employee->address }}</p>
                    @endif
                    <p><i class="fas fa-calendar me-2 text-primary"></i> تاريخ التوظيف: {{ $employee->hire_date->format('Y-m-d') }}</p>
                    <p><i class="fas fa-money-bill-wave me-2 text-primary"></i> الراتب الأساسي: {{ number_format($employee->basic_salary, 2) }} ج.م</p>
                </div>

                <div class="mt-3">
                    @if($employee->status == 'active')
                        <span class="badge bg-success">نشط</span>
                    @elseif($employee->status == 'inactive')
                        <span class="badge bg-warning">غير نشط</span>
                    @else
                        <span class="badge bg-danger">منتهي الخدمة</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- إجراءات سريعة -->
        <div class="card mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h6>
            </div>
            <div class="card-body">
                @php
                    $todayAttendance = $employee->getTodayAttendance();
                @endphp

                @if(!$todayAttendance || !$todayAttendance->check_in)
                    <form action="{{ route('attendance.checkin', $employee) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            تسجيل دخول
                        </button>
                    </form>
                @elseif(!$todayAttendance->check_out)
                    <form action="{{ route('attendance.checkout', $employee) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            تسجيل خروج
                        </button>
                    </form>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-check-circle me-2"></i>
                        تم تسجيل الحضور لليوم
                    </div>
                @endif

                <form action="{{ route('payroll.generate', $employee) }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ now()->format('Y-m') }}">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>
                        إنشاء راتب الشهر الحالي
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- الحضور والرواتب -->
    <div class="col-lg-8">
        <!-- آخر سجلات الحضور -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clock me-2"></i>
                    آخر سجلات الحضور
                </h6>
                <a href="{{ route('attendance.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-primary">
                    عرض الكل
                </a>
            </div>
            <div class="card-body">
                @if($employee->attendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>وقت الدخول</th>
                                    <th>وقت الخروج</th>
                                    <th>إجمالي الساعات</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('Y-m-d') }}</td>
                                    <td>
                                        @if($attendance->check_in)
                                            {{ $attendance->formatted_check_in }}
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->check_out)
                                            {{ $attendance->formatted_check_out }}
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->total_hours)
                                            {{ $attendance->total_hours }} ساعة
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->status == 'present')
                                            <span class="badge bg-success">حاضر</span>
                                        @elseif($attendance->status == 'absent')
                                            <span class="badge bg-danger">غائب</span>
                                        @elseif($attendance->status == 'late')
                                            <span class="badge bg-warning">متأخر</span>
                                        @else
                                            <span class="badge bg-info">نصف يوم</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <p class="text-muted">لا توجد سجلات حضور</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- آخر سجلات الرواتب -->
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    آخر سجلات الرواتب
                </h6>
                <a href="{{ route('payroll.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-primary">
                    عرض الكل
                </a>
            </div>
            <div class="card-body">
                @if($employee->payrollRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>الشهر</th>
                                    <th>الراتب الأساسي</th>
                                    <th>الساعات الإضافية</th>
                                    <th>البدلات</th>
                                    <th>الخصومات</th>
                                    <th>الراتب الصافي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->payrollRecords as $payroll)
                                <tr>
                                    <td>{{ $payroll->formatted_month }}</td>
                                    <td>{{ number_format($payroll->basic_salary, 2) }} ج.م</td>
                                    <td>
                                        @if($payroll->overtime_amount > 0)
                                            {{ number_format($payroll->overtime_amount, 2) }} ج.م
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
                                    <td class="fw-bold">{{ number_format($payroll->net_salary, 2) }} ج.م</td>
                                    <td>
                                        @if($payroll->status == 'draft')
                                            <span class="badge bg-secondary">مسودة</span>
                                        @elseif($payroll->status == 'approved')
                                            <span class="badge bg-warning">معتمد</span>
                                        @else
                                            <span class="badge bg-success">مدفوع</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-money-bill-wave fa-2x text-muted mb-2"></i>
                        <p class="text-muted">لا توجد سجلات رواتب</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
