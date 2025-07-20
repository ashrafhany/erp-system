@extends('layouts.app')

@section('title', 'إدارة الحضور والانصراف')
@section('page-title', 'إدارة الحضور والانصراف')

@section('page-actions')
    <a href="{{ route('attendance.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        تسجيل حضور جديد
    </a>
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
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $attendances->where('status', 'present')->count() }}</h5>
                        <small>حاضر اليوم</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $attendances->where('status', 'absent')->count() }}</h5>
                        <small>غائب اليوم</small>
                    </div>
                    <i class="fas fa-times-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $attendances->where('status', 'late')->count() }}</h5>
                        <small>متأخر اليوم</small>
                    </div>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $attendances->where('status', 'half_day')->count() }}</h5>
                        <small>نصف يوم</small>
                    </div>
                    <i class="fas fa-user-clock fa-2x"></i>
                </div>
            </div>
        </div>
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
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 35px; height: 35px;">
                                            <span class="text-white fw-bold small">
                                                {{ substr($attendance->employee->first_name, 0, 1) }}{{ substr($attendance->employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
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
                            <td>
                                <div class="btn-group" role="group">
                                    @if(!$attendance->check_in)
                                        <button class="btn btn-sm btn-outline-success"
                                                onclick="checkIn({{ $attendance->employee->id }})">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('attendance.edit', $attendance) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('attendance.destroy', $attendance) }}"
                                          method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
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
                {{ $attendances->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">لا توجد سجلات حضور</h5>
                <p class="text-muted">ابدأ بتسجيل حضور الموظفين</p>
                <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    تسجيل حضور جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkIn(employeeId) {
    if (confirm('تأكيد تسجيل دخول الموظف؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/attendance/checkin/${employeeId}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}

function checkOut(employeeId) {
    if (confirm('تأكيد تسجيل خروج الموظف؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/attendance/checkout/${employeeId}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
