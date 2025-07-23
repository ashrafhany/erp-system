@extends('layouts.app')

@section('title', 'تعديل سجل الراتب')
@section('page-title', 'تعديل سجل الراتب')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-secondary">
            <i class="fas fa-eye me-2"></i>
            عرض السجل
        </a>
        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للرواتب
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        @if($payroll->status === 'approved')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>تنبيه:</strong> هذا السجل معتمد بالفعل. قد تحتاج إلى إذن خاص لتعديله.
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل سجل راتب: {{ $payroll->employee->name }}
                    </h5>
                    <span class="badge badge-{{ $payroll->status === 'approved' ? 'success' : ($payroll->status === 'pending' ? 'warning' : 'secondary') }}">
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
                <form action="{{ route('payroll.update', $payroll) }}" method="POST" id="payroll-form">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror"
                                    id="employee_id" name="employee_id" required
                                    {{ $payroll->status === 'approved' ? 'disabled' : '' }}>
                                <option value="">اختر الموظف</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                            data-salary="{{ $employee->salary }}"
                                            {{ old('employee_id', $payroll->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} - {{ $employee->department }}
                                    </option>
                                @endforeach
                            </select>
                            @if($payroll->status === 'approved')
                                <input type="hidden" name="employee_id" value="{{ $payroll->employee_id }}">
                            @endif
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="payroll_month" class="form-label">شهر الراتب <span class="text-danger">*</span></label>
                            <input type="month" class="form-control @error('payroll_month') is-invalid @enderror"
                                   id="payroll_month" name="payroll_month"
                                   value="{{ old('payroll_month', \Carbon\Carbon::parse($payroll->payroll_month)->format('Y-m')) }}"
                                   required {{ $payroll->status === 'approved' ? 'readonly' : '' }}>
                            @error('payroll_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="basic_salary" class="form-label">الراتب الأساسي <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('basic_salary') is-invalid @enderror"
                                       id="basic_salary" name="basic_salary"
                                       value="{{ old('basic_salary', $payroll->basic_salary) }}"
                                       placeholder="0.00" required onchange="calculateSalary()">
                                <span class="input-group-text">ر.س</span>
                                @error('basic_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="overtime_hours" class="form-label">ساعات العمل الإضافي</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0"
                                       class="form-control @error('overtime_hours') is-invalid @enderror"
                                       id="overtime_hours" name="overtime_hours"
                                       value="{{ old('overtime_hours', $payroll->overtime_hours) }}"
                                       placeholder="0" onchange="calculateSalary()">
                                <span class="input-group-text">ساعة</span>
                                @error('overtime_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="overtime_rate" class="form-label">معدل الساعة الإضافية</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('overtime_rate') is-invalid @enderror"
                                       id="overtime_rate" name="overtime_rate"
                                       value="{{ old('overtime_rate', $payroll->overtime_rate) }}"
                                       placeholder="0.00" onchange="calculateSalary()">
                                <span class="input-group-text">ر.س/ساعة</span>
                                @error('overtime_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="allowances" class="form-label">البدلات والمكافآت</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('allowances') is-invalid @enderror"
                                       id="allowances" name="allowances"
                                       value="{{ old('allowances', $payroll->allowances) }}"
                                       placeholder="0.00" onchange="calculateSalary()">
                                <span class="input-group-text">ر.س</span>
                                @error('allowances')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="deductions" class="form-label">الخصومات</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('deductions') is-invalid @enderror"
                                       id="deductions" name="deductions"
                                       value="{{ old('deductions', $payroll->deductions) }}"
                                       placeholder="0.00" onchange="calculateSalary()">
                                <span class="input-group-text">ر.س</span>
                                @error('deductions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tax_amount" class="form-label">الضرائب</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('tax_amount') is-invalid @enderror"
                                       id="tax_amount" name="tax_amount"
                                       value="{{ old('tax_amount', $payroll->tax_amount) }}"
                                       placeholder="0.00" onchange="calculateSalary()">
                                <span class="input-group-text">ر.س</span>
                                @error('tax_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="أي ملاحظات إضافية...">{{ old('notes', $payroll->notes) }}</textarea>
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
                                            <li>صافي الراتب الحالي: <span class="text-primary">{{ number_format($payroll->net_salary, 2) }} ر.س</span></li>
                                            <li>إجمالي الدخل: {{ number_format($payroll->gross_salary, 2) }} ر.س</li>
                                            <li>إجمالي الخصومات: {{ number_format($payroll->deductions + $payroll->tax_amount, 2) }} ر.س</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>القيم الجديدة:</strong>
                                        <ul class="list-unstyled mb-0 mt-2">
                                            <li>صافي الراتب الجديد: <span class="text-success" id="new-net-salary">{{ number_format($payroll->net_salary, 2) }} ر.س</span></li>
                                            <li>إجمالي الدخل: <span id="new-gross-salary">{{ number_format($payroll->gross_salary, 2) }} ر.س</span></li>
                                            <li>إجمالي الخصومات: <span id="new-deductions">{{ number_format($payroll->deductions + $payroll->tax_amount, 2) }} ر.س</span></li>
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
                            <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                إلغاء
                            </a>
                            @if($payroll->status === 'pending')
                            <button type="button" class="btn btn-danger" onclick="deletePayroll()">
                                <i class="fas fa-trash me-2"></i>
                                حذف السجل
                            </button>
                            @endif
                        </div>
                    </div>
                </form>

                @if($payroll->status === 'pending')
                <!-- نموذج حذف السجل -->
                <form action="{{ route('payroll.destroy', $payroll) }}" method="POST" id="delete-form" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- ملخص الراتب المحدث -->
    <div class="col-lg-4">
        <div class="card sticky-top">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    ملخص الراتب المحدث
                </h6>
            </div>
            <div class="card-body">
                <div id="salary-summary">
                    <div class="row mb-2">
                        <div class="col-7">الراتب الأساسي:</div>
                        <div class="col-5 text-end">
                            <span id="display-basic">{{ number_format($payroll->basic_salary, 2) }}</span> ر.س
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-7">أجر العمل الإضافي:</div>
                        <div class="col-5 text-end">
                            <span id="display-overtime">{{ number_format($payroll->overtime_hours * $payroll->overtime_rate, 2) }}</span> ر.س
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-7">البدلات:</div>
                        <div class="col-5 text-end">
                            <span id="display-allowances">{{ number_format($payroll->allowances, 2) }}</span> ر.س
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-7">إجمالي الدخل:</div>
                        <div class="col-5 text-end">
                            <strong><span id="display-gross">{{ number_format($payroll->gross_salary, 2) }}</span> ر.س</strong>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-7">الخصومات:</div>
                        <div class="col-5 text-end text-danger">
                            <span id="display-deductions">{{ number_format($payroll->deductions, 2) }}</span> ر.س
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-7">الضرائب:</div>
                        <div class="col-5 text-end text-danger">
                            <span id="display-tax">{{ number_format($payroll->tax_amount, 2) }}</span> ر.س
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-7"><strong>صافي الراتب:</strong></div>
                        <div class="col-5 text-end">
                            <strong class="text-success">
                                <span id="display-net">{{ number_format($payroll->net_salary, 2) }}</span> ر.س
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- مقارنة سريعة -->
                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="mb-3">الفرق عن القيم الأصلية:</h6>
                    <div class="row">
                        <div class="col-8">تغيير صافي الراتب:</div>
                        <div class="col-4 text-end">
                            <span id="salary-difference" class="fw-bold">0.00 ر.س</span>
                        </div>
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
                    <h6 class="text-primary">{{ $payroll->employee->name }}</h6>
                    <p class="text-muted mb-1">
                        <i class="fas fa-building me-2"></i>
                        {{ $payroll->employee->department ?? 'غير محدد' }}
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        الراتب الأساسي: {{ number_format($payroll->employee->salary ?? 0, 2) }} ر.س
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        شهر الراتب: {{ \Carbon\Carbon::parse($payroll->payroll_month)->format('F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- تحذيرات -->
        @if($payroll->status === 'approved')
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تنبيه هام
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-0">هذا السجل معتمد بالفعل. أي تعديل قد يؤثر على:</p>
                <ul class="mt-2 mb-0">
                    <li>تقارير الرواتب</li>
                    <li>الحسابات المالية</li>
                    <li>سجل الموظف</li>
                </ul>
            </div>
        </div>
        @endif
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

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.employee-details h6 {
    margin-bottom: 0.5rem;
}

.employee-details p {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const originalNetSalary = {{ $payroll->net_salary }};

    // حساب الراتب عند تحميل الصفحة
    calculateSalary();

    // حساب الراتب
    window.calculateSalary = function() {
        const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
        const overtimeHours = parseFloat(document.getElementById('overtime_hours').value) || 0;
        const overtimeRate = parseFloat(document.getElementById('overtime_rate').value) || 0;
        const allowances = parseFloat(document.getElementById('allowances').value) || 0;
        const deductions = parseFloat(document.getElementById('deductions').value) || 0;
        const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;

        const overtimeAmount = overtimeHours * overtimeRate;
        const grossSalary = basicSalary + overtimeAmount + allowances;
        const netSalary = grossSalary - deductions - taxAmount;

        // تحديث العرض
        document.getElementById('display-basic').textContent = basicSalary.toFixed(2);
        document.getElementById('display-overtime').textContent = overtimeAmount.toFixed(2);
        document.getElementById('display-allowances').textContent = allowances.toFixed(2);
        document.getElementById('display-gross').textContent = grossSalary.toFixed(2);
        document.getElementById('display-deductions').textContent = deductions.toFixed(2);
        document.getElementById('display-tax').textContent = taxAmount.toFixed(2);
        document.getElementById('display-net').textContent = netSalary.toFixed(2);

        // تحديث المقارنة
        document.getElementById('new-net-salary').textContent = netSalary.toFixed(2) + ' ر.س';
        document.getElementById('new-gross-salary').textContent = grossSalary.toFixed(2) + ' ر.س';
        document.getElementById('new-deductions').textContent = (deductions + taxAmount).toFixed(2) + ' ر.س';

        // حساب الفرق
        const difference = netSalary - originalNetSalary;
        const differenceElement = document.getElementById('salary-difference');
        differenceElement.textContent = (difference >= 0 ? '+' : '') + difference.toFixed(2) + ' ر.س';

        // تغيير لون الفرق
        if (difference > 0) {
            differenceElement.className = 'fw-bold text-success';
        } else if (difference < 0) {
            differenceElement.className = 'fw-bold text-danger';
        } else {
            differenceElement.className = 'fw-bold text-muted';
        }

        // تغيير لون صافي الراتب حسب القيمة
        const netElement = document.getElementById('display-net').parentElement;
        if (netSalary < 0) {
            netElement.classList.remove('text-success');
            netElement.classList.add('text-danger');
        } else {
            netElement.classList.remove('text-danger');
            netElement.classList.add('text-success');
        }
    }

    // التحقق من صحة النموذج
    document.getElementById('payroll-form').addEventListener('submit', function(e) {
        const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
        const newNetSalary = parseFloat(document.getElementById('display-net').textContent) || 0;

        if (basicSalary <= 0) {
            e.preventDefault();
            alert('يرجى إدخال راتب أساسي صحيح');
            return false;
        }

        // تأكيد التعديل إذا كان هناك فرق كبير
        const difference = Math.abs(newNetSalary - originalNetSalary);
        if (difference > 1000) {
            if (!confirm(`هناك فرق كبير في صافي الراتب (${difference.toFixed(2)} ر.س). هل أنت متأكد من المتابعة؟`)) {
                e.preventDefault();
                return false;
            }
        }
    });

    // حذف السجل
    window.deletePayroll = function() {
        if (confirm('هل أنت متأكد من حذف هذا السجل؟ لا يمكن التراجع عن هذا الإجراء.')) {
            document.getElementById('delete-form').submit();
        }
    }
});
</script>
@endpush
