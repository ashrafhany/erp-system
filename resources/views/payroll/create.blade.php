@extends('layouts.app')

@section('title', 'إضافة سجل راتب جديد')
@section('page-title', 'إضافة سجل راتب جديد')

@section('page-actions')
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        العودة للرواتب
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    بيانات سجل الراتب
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payroll.store') }}" method="POST" id="payroll-form">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror"
                                    id="employee_id" name="employee_id" required>
                                <option value="">اختر الموظف</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                            data-salary="{{ $employee->salary }}"
                                            {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} - {{ $employee->department }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="payroll_month" class="form-label">شهر الراتب <span class="text-danger">*</span></label>
                            <input type="month" class="form-control @error('payroll_month') is-invalid @enderror"
                                   id="payroll_month" name="payroll_month"
                                   value="{{ old('payroll_month', date('Y-m')) }}" required>
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
                                       value="{{ old('basic_salary') }}"
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
                                       value="{{ old('overtime_hours', 0) }}"
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
                                       value="{{ old('overtime_rate', 0) }}"
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
                                       value="{{ old('allowances', 0) }}"
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
                                       value="{{ old('deductions', 0) }}"
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
                                       value="{{ old('tax_amount', 0) }}"
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
                                      placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-2"></i>
                                حفظ سجل الراتب
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ملخص الراتب -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    ملخص الراتب
                </h6>
            </div>
            <div class="card-body">
                <div id="salary-summary">
                    <div class="row mb-2">
                        <div class="col-7">الراتب الأساسي:</div>
                        <div class="col-5 text-end">
                            <span id="display-basic">0.00</span> ر.س
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-7">أجر العمل الإضافي:</div>
                        <div class="col-5 text-end">
                            <span id="display-overtime">0.00</span> ر.س
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-7">البدلات:</div>
                        <div class="col-5 text-end">
                            <span id="display-allowances">0.00</span> ر.س
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-7">إجمالي الدخل:</div>
                        <div class="col-5 text-end">
                            <strong><span id="display-gross">0.00</span> ر.س</strong>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-7">الخصومات:</div>
                        <div class="col-5 text-end text-danger">
                            <span id="display-deductions">0.00</span> ر.س
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-7">الضرائب:</div>
                        <div class="col-5 text-end text-danger">
                            <span id="display-tax">0.00</span> ر.س
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-7"><strong>صافي الراتب:</strong></div>
                        <div class="col-5 text-end">
                            <strong class="text-success">
                                <span id="display-net">0.00</span> ر.س
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    معلومات الموظف
                </h6>
            </div>
            <div class="card-body" id="employee-info">
                <p class="text-muted">اختر موظف لعرض معلوماته</p>
            </div>
        </div>

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
                        سيتم حساب صافي الراتب تلقائياً
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        أجر العمل الإضافي = الساعات × المعدل
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        تحقق من عدم وجود سجل مسبق لنفس الشهر
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        يمكن تعديل البيانات لاحقاً
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.salary-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

#employee-info {
    min-height: 120px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تغيير الموظف
    document.getElementById('employee_id').addEventListener('change', function() {
        loadEmployeeInfo(this);
        loadEmployeeSalary(this);
    });

    // حساب الراتب عند تحميل الصفحة
    calculateSalary();

    function loadEmployeeInfo(select) {
        const employeeInfo = document.getElementById('employee-info');

        if (!select.value) {
            employeeInfo.innerHTML = '<p class="text-muted">اختر موظف لعرض معلوماته</p>';
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const employeeName = selectedOption.text;
        const employeeSalary = selectedOption.dataset.salary || '0';

        employeeInfo.innerHTML = `
            <div class="employee-details">
                <h6 class="text-primary">${employeeName}</h6>
                <p class="text-muted mb-1">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    الراتب الأساسي: ${parseFloat(employeeSalary).toFixed(2)} ر.س
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    تم اختيار الموظف بنجاح
                </p>
            </div>
        `;
    }

    function loadEmployeeSalary(select) {
        const selectedOption = select.options[select.selectedIndex];
        const employeeSalary = selectedOption.dataset.salary || '0';

        if (employeeSalary && !document.getElementById('basic_salary').value) {
            document.getElementById('basic_salary').value = parseFloat(employeeSalary).toFixed(2);
            calculateSalary();
        }
    }

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
        const employeeId = document.getElementById('employee_id').value;
        const month = document.getElementById('payroll_month').value;
        const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;

        if (!employeeId) {
            e.preventDefault();
            alert('يرجى اختيار الموظف');
            return false;
        }

        if (!month) {
            e.preventDefault();
            alert('يرجى تحديد شهر الراتب');
            return false;
        }

        if (basicSalary <= 0) {
            e.preventDefault();
            alert('يرجى إدخال راتب أساسي صحيح');
            return false;
        }
    });
});
</script>
@endpush
