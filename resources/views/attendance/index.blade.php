@extends('layouts.app')

@section('title', 'إدارة الحضور والانصراف')
@section('page-title', 'إدارة الحضور والانصراف')

@section('page-actions')
    @can('attendance.create')
    <a href="{{ route('attendance.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        تسجيل حضور جديد
    </a>
    @endcan
@endsection

@section('content')
<!-- فلاتر البحث -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">التاريخ</label>
                    <input type="date" class="form-control" id="date" name="date"
                           value="{{ request('date', today()->format('Y-m-d')) }}">
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
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh me-2"></i>إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- بطاقات سريعة للحضور -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="حاضر" :value="$statusCounts['present'] ?? 0" icon="fas fa-circle-check" color="success" />
    </div>
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="غائب" :value="$statusCounts['absent'] ?? 0" icon="fas fa-circle-xmark" color="danger" />
    </div>
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="متأخر" :value="$statusCounts['late'] ?? 0" icon="fas fa-clock" color="warning" />
    </div>
    <div class="col-xl-3 col-sm-6">
        <x-stat-card label="نصف يوم" :value="$statusCounts['half_day'] ?? 0" icon="fas fa-user-clock" color="info" />
    </div>
</div>

<!-- قائمة الحضور -->
<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-clock me-2"></i>
            سجلات الحضور
        </h6>
    </div>
    <div class="card-body">
        @if($attendances->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الموظف</th>
                            <th>التاريخ</th>
                            <th>وقت الدخول</th>
                            <th>وقت الخروج</th>
                            <th>إجمالي الساعات</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar avatar-sm avatar-gradient">{{ $attendance->employee->initials }}</span>
                                    <div>
                                        <div class="fw-bold">{{ $attendance->employee->full_name }}</div>
                                        <small class="text-muted">{{ $attendance->employee->employee_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $attendance->date->format('Y-m-d') }}</td>
                            <td>
                                @if($attendance->check_in)
                                    <span class="badge bg-success">{{ $attendance->formatted_check_in }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->check_out)
                                    <span class="badge bg-danger">{{ $attendance->formatted_check_out }}</span>
                                @else
                                    @if($attendance->check_in)
                                        <button class="btn btn-sm btn-outline-warning"
                                                onclick="checkOut({{ $attendance->employee->id }})">
                                            تسجيل خروج
                                        </button>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($attendance->total_hours)
                                    {{ $attendance->total_hours }} ساعة
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td><x-status-badge type="attendance" :status="$attendance->status" /></td>
                            <td>
                                <div class="table-actions">
                                    @if(!$attendance->check_in && auth()->user()->can('attendance.checkin'))
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="checkIn({{ $attendance->employee->id }})"
                                                data-bs-toggle="tooltip" title="تسجيل دخول" aria-label="تسجيل دخول">
                                            <i class="fas fa-right-to-bracket"></i>
                                        </button>
                                    @endif
                                    @can('attendance.update')
                                    <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" title="تعديل" aria-label="تعديل">
                                        <i class="far fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('attendance.delete')
                                    <form action="{{ route('attendance.destroy', $attendance) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                data-confirm="هل أنت متأكد من حذف سجل حضور {{ $attendance->employee->full_name }}؟"
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
                {{ $attendances->withQueryString()->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-clock"></i></div>
                <h5>لا توجد سجلات حضور</h5>
                <p class="text-muted">ابدأ بتسجيل حضور الموظفين</p>
                @can('attendance.create')
                <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    تسجيل حضور جديد
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function submitAttendance(action, employeeId, message) {
    window.confirmAction({ message, title: 'تأكيد التسجيل', okText: 'تأكيد' }).then(confirmed => {
        if (!confirmed) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/attendance/${action}/${employeeId}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    });
}

function checkIn(employeeId) {
    submitAttendance('checkin', employeeId, 'تأكيد تسجيل دخول الموظف؟');
}

function checkOut(employeeId) {
    submitAttendance('checkout', employeeId, 'تأكيد تسجيل خروج الموظف؟');
}
</script>
@endpush
