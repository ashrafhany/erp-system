@extends('layouts.app')

@section('title', 'تسجيل حضور جديد')
@section('page-title', 'تسجيل حضور جديد')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clock me-2"></i>
                    تسجيل حضور موظف
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror"
                                    id="employee_id" name="employee_id" required>
                                <option value="">اختر الموظف</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                            {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->full_name }} ({{ $employee->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                   id="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="check_in" class="form-label">وقت الدخول</label>
                            <input type="time" class="form-control @error('check_in') is-invalid @enderror"
                                   id="check_in" name="check_in" value="{{ old('check_in') }}">
                            @error('check_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="check_out" class="form-label">وقت الخروج</label>
                            <input type="time" class="form-control @error('check_out') is-invalid @enderror"
                                   id="check_out" name="check_out" value="{{ old('check_out') }}">
                            @error('check_out')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">حالة الحضور <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>حاضر</option>
                            <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>غائب</option>
                            <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>متأخر</option>
                            <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>نصف يوم</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            تسجيل الحضور
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// تعيين الوقت الحالي عند تحديد الحالة كحاضر
document.getElementById('status').addEventListener('change', function() {
    const checkInField = document.getElementById('check_in');
    const checkOutField = document.getElementById('check_out');

    if (this.value === 'present' && !checkInField.value) {
        const now = new Date();
        const timeString = now.toTimeString().slice(0, 5);
        checkInField.value = timeString;
    } else if (this.value === 'absent') {
        checkInField.value = '';
        checkOutField.value = '';
    }
});

// التحقق من أن وقت الخروج بعد وقت الدخول
document.getElementById('check_out').addEventListener('change', function() {
    const checkIn = document.getElementById('check_in').value;
    const checkOut = this.value;

    if (checkIn && checkOut && checkOut <= checkIn) {
        alert('وقت الخروج يجب أن يكون بعد وقت الدخول');
        this.value = '';
    }
});
</script>
@endpush
