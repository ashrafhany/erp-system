@extends('layouts.app')

@section('title', 'تعديل سجل الحضور')
@section('page-title', 'تعديل سجل الحضور')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-secondary">
            <i class="fas fa-eye me-2"></i>
            عرض السجل
        </a>
        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للحضور
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل سجل حضور: {{ $attendance->employee->name }}
                    </h5>
                    <span class="badge badge-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : ($attendance->status === 'half_day' ? 'info' : 'danger')) }}">
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
                <form action="{{ route('attendance.update', $attendance) }}" method="POST" id="attendance-form">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror"
                                    id="employee_id" name="employee_id" required>
                                <option value="">اختر الموظف</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                            data-department="{{ $employee->department }}"
                                            {{ old('employee_id', $attendance->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} - {{ $employee->department }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date" class="form-label">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                   id="date" name="date"
                                   value="{{ old('date', $attendance->date) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="check_in" class="form-label">وقت الدخول</label>
                            <input type="time" class="form-control @error('check_in') is-invalid @enderror"
                                   id="check_in" name="check_in"
                                   value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}"
                                   onchange="calculateHours()">
                            @error('check_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">اتركه فارغاً إذا لم يحضر الموظف</div>
                        </div>
                        <div class="col-md-6">
                            <label for="check_out" class="form-label">وقت الخروج</label>
                            <input type="time" class="form-control @error('check_out') is-invalid @enderror"
                                   id="check_out" name="check_out"
                                   value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}"
                                   onchange="calculateHours()">
                            @error('check_out')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">اتركه فارغاً إذا لم ينصرف الموظف بعد</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">حالة الحضور <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="">اختر الحالة</option>
                                <option value="present" {{ old('status', $attendance->status) === 'present' ? 'selected' : '' }}>
                                    <i class="fas fa-check-circle"></i> حاضر
                                </option>
                                <option value="absent" {{ old('status', $attendance->status) === 'absent' ? 'selected' : '' }}>
                                    <i class="fas fa-times-circle"></i> غائب
                                </option>
                                <option value="late" {{ old('status', $attendance->status) === 'late' ? 'selected' : '' }}>
                                    <i class="fas fa-clock"></i> متأخر
                                </option>
                                <option value="half_day" {{ old('status', $attendance->status) === 'half_day' ? 'selected' : '' }}>
                                    <i class="fas fa-clock-o"></i> نصف يوم
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="calculated_hours" class="form-label">ساعات العمل المحسوبة</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="calculated_hours" readonly
                                       value="{{ $attendance->total_hours ? number_format($attendance->total_hours, 2) : '0.00' }}">
                                <span class="input-group-text">ساعة</span>
                            </div>
                            <div class="form-text">سيتم الحساب تلقائياً عند إدخال أوقات الدخول والخروج</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="أي ملاحظات أو تفاصيل إضافية...">{{ old('notes', $attendance->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- مقارنة القيم -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    مقارنة القيم
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>القيم الحالية:</strong>
                                        <ul class="list-unstyled mb-0 mt-2">
                                            <li>الحالة: <span class="badge badge-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                                                {{ $attendance->status === 'present' ? 'حاضر' : ($attendance->status === 'late' ? 'متأخر' : ($attendance->status === 'half_day' ? 'نصف يوم' : 'غائب')) }}
                                            </span></li>
                                            <li>وقت الدخول: {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'غير محدد' }}</li>
                                            <li>وقت الخروج: {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'غير محدد' }}</li>
                                            <li>ساعات العمل: {{ $attendance->total_hours ? number_format($attendance->total_hours, 2) : '0.00' }} ساعة</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>القيم الجديدة:</strong>
                                        <ul class="list-unstyled mb-0 mt-2" id="new-values">
                                            <li>الحالة: <span id="new-status-display">{{ $attendance->status === 'present' ? 'حاضر' : ($attendance->status === 'late' ? 'متأخر' : ($attendance->status === 'half_day' ? 'نصف يوم' : 'غائب')) }}</span></li>
                                            <li>وقت الدخول: <span id="new-checkin-display">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'غير محدد' }}</span></li>
                                            <li>وقت الخروج: <span id="new-checkout-display">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'غير محدد' }}</span></li>
                                            <li>ساعات العمل: <span id="new-hours-display">{{ $attendance->total_hours ? number_format($attendance->total_hours, 2) : '0.00' }}</span> ساعة</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-2"></i>
                                حفظ التعديلات
                            </button>
                            <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                إلغاء
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteAttendance()">
                                <i class="fas fa-trash me-2"></i>
                                حذف السجل
                            </button>
                        </div>
                    </div>
                </form>

                <!-- نموذج حذف السجل -->
                <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" id="delete-form" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    <!-- الجانب الأيمن -->
    <div class="col-lg-4">
        <!-- ملخص التعديلات -->
        <div class="card sticky-top">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    ملخص التعديلات
                </h6>
            </div>
            <div class="card-body">
                <div id="attendance-summary">
                    <div class="summary-item mb-3">
                        <label class="text-muted">الموظف المختار:</label>
                        <p class="fw-bold" id="selected-employee">{{ $attendance->employee->name }}</p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">التاريخ:</label>
                        <p class="fw-bold" id="selected-date">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">الحالة:</label>
                        <p class="fw-bold" id="selected-status">
                            <span class="badge badge-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                                {{ $attendance->status === 'present' ? 'حاضر' : ($attendance->status === 'late' ? 'متأخر' : ($attendance->status === 'half_day' ? 'نصف يوم' : 'غائب')) }}
                            </span>
                        </p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">أوقات العمل:</label>
                        <div class="time-summary bg-light p-2 rounded">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <small class="text-muted">دخول</small>
                                    <div class="fw-bold" id="summary-checkin">
                                        {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <small class="text-muted">خروج</small>
                                    <div class="fw-bold" id="summary-checkout">
                                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '--:--' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-item">
                        <label class="text-muted">إجمالي ساعات العمل:</label>
                        <h5 class="text-primary" id="summary-hours">{{ $attendance->total_hours ? number_format($attendance->total_hours, 2) : '0.00' }} ساعة</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- معلومات الموظف -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    معلومات الموظف
                </h6>
            </div>
            <div class="card-body" id="employee-info">
                <div class="employee-details">
                    <h6 class="text-primary">{{ $attendance->employee->name }}</h6>
                    <p class="text-muted mb-1">
                        <i class="fas fa-building me-2"></i>
                        {{ $attendance->employee->department ?? 'غير محدد' }}
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-envelope me-2"></i>
                        {{ $attendance->employee->email ?? 'غير محدد' }}
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        الراتب: {{ number_format($attendance->employee->salary ?? 0, 2) }} ر.س
                    </p>
                </div>
            </div>
        </div>

        <!-- إرشادات -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    إرشادات
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        ساعات العمل تُحسب تلقائياً عند إدخال أوقات الدخول والخروج
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        تأكد من صحة التاريخ والأوقات قبل الحفظ
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        لا يمكن أن يكون وقت الخروج قبل وقت الدخول
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        في حالة الغياب، اتركي أوقات الدخول والخروج فارغة
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        يمكن إضافة ملاحظات لتوضيح أي ظروف خاصة
                    </li>
                </ul>
            </div>
        </div>

        <!-- إحصائيات سريعة -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    إحصائيات الموظف هذا الشهر
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
                            AVG(total_hours) as avg_hours
                        ')
                        ->first();
                @endphp

                <div class="monthly-stats">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="stat-box bg-success bg-opacity-10 p-2 rounded">
                                <h6 class="text-success mb-0">{{ $monthlyStats->present_days ?? 0 }}</h6>
                                <small class="text-muted">حضور</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="stat-box bg-danger bg-opacity-10 p-2 rounded">
                                <h6 class="text-danger mb-0">{{ $monthlyStats->absent_days ?? 0 }}</h6>
                                <small class="text-muted">غياب</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box bg-warning bg-opacity-10 p-2 rounded">
                                <h6 class="text-warning mb-0">{{ $monthlyStats->late_days ?? 0 }}</h6>
                                <small class="text-muted">تأخير</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box bg-info bg-opacity-10 p-2 rounded">
                                <h6 class="text-info mb-0">{{ number_format($monthlyStats->avg_hours ?? 0, 1) }}</h6>
                                <small class="text-muted">متوسط ساعات</small>
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
.sticky-top {
    top: 1rem;
}

.badge {
    font-size: 0.875em;
    padding: 0.5em 0.75em;
}

.summary-item {
    margin-bottom: 1rem;
}

.summary-item label {
    display: block;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.time-summary {
    border: 1px solid #dee2e6;
}

.employee-details h6 {
    margin-bottom: 0.5rem;
}

.employee-details p {
    font-size: 0.875rem;
}

.stat-box {
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: scale(1.05);
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحديث معلومات الموظف عند تغيير الاختيار
    document.getElementById('employee_id').addEventListener('change', function() {
        updateEmployeeInfo(this);
    });

    // تحديث العرض عند تغيير أي قيمة
    document.getElementById('status').addEventListener('change', updateDisplay);
    document.getElementById('date').addEventListener('change', updateDisplay);

    // حساب ساعات العمل
    window.calculateHours = function() {
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;

        if (checkIn && checkOut) {
            const startTime = new Date('2000-01-01 ' + checkIn);
            const endTime = new Date('2000-01-01 ' + checkOut);

            if (endTime > startTime) {
                const diffMs = endTime - startTime;
                const diffHours = diffMs / (1000 * 60 * 60);

                document.getElementById('calculated_hours').value = diffHours.toFixed(2);
                document.getElementById('summary-hours').textContent = diffHours.toFixed(2) + ' ساعة';
            } else {
                document.getElementById('calculated_hours').value = '0.00';
                document.getElementById('summary-hours').textContent = '0.00 ساعة';
            }
        } else {
            document.getElementById('calculated_hours').value = '0.00';
            document.getElementById('summary-hours').textContent = '0.00 ساعة';
        }

        updateDisplay();
    }

    function updateEmployeeInfo(select) {
        const selectedOption = select.options[select.selectedIndex];
        const employeeName = selectedOption.text;
        const department = selectedOption.dataset.department || 'غير محدد';

        if (select.value) {
            document.getElementById('selected-employee').textContent = employeeName;
        } else {
            document.getElementById('selected-employee').textContent = 'لم يتم الاختيار';
        }
    }

    function updateDisplay() {
        // تحديث التاريخ
        const dateInput = document.getElementById('date');
        if (dateInput.value) {
            const date = new Date(dateInput.value);
            document.getElementById('selected-date').textContent = date.toLocaleDateString('ar-EG');
        }

        // تحديث الحالة
        const statusSelect = document.getElementById('status');
        const statusDisplay = document.getElementById('selected-status');
        const newStatusDisplay = document.getElementById('new-status-display');

        if (statusSelect.value) {
            let statusText = '';
            let badgeClass = '';

            switch(statusSelect.value) {
                case 'present':
                    statusText = 'حاضر';
                    badgeClass = 'badge-success';
                    break;
                case 'absent':
                    statusText = 'غائب';
                    badgeClass = 'badge-danger';
                    break;
                case 'late':
                    statusText = 'متأخر';
                    badgeClass = 'badge-warning';
                    break;
                case 'half_day':
                    statusText = 'نصف يوم';
                    badgeClass = 'badge-info';
                    break;
            }

            statusDisplay.innerHTML = `<span class="badge ${badgeClass}">${statusText}</span>`;
            newStatusDisplay.textContent = statusText;
        }

        // تحديث الأوقات
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;

        document.getElementById('summary-checkin').textContent = checkIn || '--:--';
        document.getElementById('summary-checkout').textContent = checkOut || '--:--';

        document.getElementById('new-checkin-display').textContent = checkIn ? formatTime(checkIn) : 'غير محدد';
        document.getElementById('new-checkout-display').textContent = checkOut ? formatTime(checkOut) : 'غير محدد';

        // تحديث ساعات العمل
        const hours = document.getElementById('calculated_hours').value;
        document.getElementById('new-hours-display').textContent = hours;
    }

    function formatTime(timeString) {
        const time = new Date('2000-01-01 ' + timeString);
        return time.toLocaleTimeString('ar-EG', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    // التحقق من صحة النموذج
    document.getElementById('attendance-form').addEventListener('submit', function(e) {
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        const status = document.getElementById('status').value;

        // التحقق من أن وقت الخروج بعد وقت الدخول
        if (checkIn && checkOut) {
            const startTime = new Date('2000-01-01 ' + checkIn);
            const endTime = new Date('2000-01-01 ' + checkOut);

            if (endTime <= startTime) {
                e.preventDefault();
                alert('وقت الخروج يجب أن يكون بعد وقت الدخول');
                return false;
            }
        }

        // التحقق من منطقية البيانات
        if (status === 'absent' && (checkIn || checkOut)) {
            if (!confirm('تم تحديد الحالة كغائب ولكن تم إدخال أوقات الحضور. هل تريد المتابعة؟')) {
                e.preventDefault();
                return false;
            }
        }

        if (status === 'present' && (!checkIn || !checkOut)) {
            if (!confirm('تم تحديد الحالة كحاضر ولكن لم يتم إدخال أوقات الحضور كاملة. هل تريد المتابعة؟')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // حذف السجل
    window.deleteAttendance = function() {
        if (confirm('هل أنت متأكد من حذف هذا السجل؟ لا يمكن التراجع عن هذا الإجراء.')) {
            document.getElementById('delete-form').submit();
        }
    }

    // تحديث العرض عند تحميل الصفحة
    calculateHours();
    updateDisplay();
});
</script>
@endpush
