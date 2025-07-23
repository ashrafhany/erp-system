@extends('layouts.app')

@section('title', 'تعديل العميل')
@section('page-title', 'تعديل العميل')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">
            <i class="fas fa-eye me-2"></i>
            عرض العميل
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للعملاء
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
                        تعديل بيانات العميل: {{ $customer->name }}
                    </h5>
                    <span class="badge badge-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                        @if($customer->status === 'active')
                            <i class="fas fa-check-circle me-1"></i> نشط
                        @else
                            <i class="fas fa-pause-circle me-1"></i> غير نشط
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer) }}" method="POST" id="customer-form">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_code" class="form-label">رمز العميل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_code') is-invalid @enderror"
                                   id="customer_code" name="customer_code"
                                   value="{{ old('customer_code', $customer->customer_code) }}"
                                   placeholder="مثال: CUST001" required>
                            @error('customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">رمز فريد للعميل لا يمكن تكراره</div>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $customer->name) }}"
                                   placeholder="الاسم الكامل للعميل" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">اسم الشركة</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name" name="company_name"
                                   value="{{ old('company_name', $customer->company_name) }}"
                                   placeholder="اسم الشركة أو المؤسسة">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email', $customer->email) }}"
                                   placeholder="example@domain.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">رقم الهاتف</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone"
                                   value="{{ old('phone', $customer->phone) }}"
                                   placeholder="+966 50 123 4567">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tax_number" class="form-label">الرقم الضريبي</label>
                            <input type="text" class="form-control @error('tax_number') is-invalid @enderror"
                                   id="tax_number" name="tax_number"
                                   value="{{ old('tax_number', $customer->tax_number) }}"
                                   placeholder="الرقم الضريبي للشركة">
                            @error('tax_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">حالة العميل <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="">اختر الحالة</option>
                                <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>
                                    نشط
                                </option>
                                <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>
                                    غير نشط
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="credit_limit" class="form-label">حد الائتمان</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('credit_limit') is-invalid @enderror"
                                       id="credit_limit" name="credit_limit"
                                       value="{{ old('credit_limit', $customer->credit_limit) }}"
                                       placeholder="0.00">
                                <span class="input-group-text">ر.س</span>
                                @error('credit_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">اتركه فارغاً للحد غير المحدود</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="address" class="form-label">العنوان</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3"
                                      placeholder="العنوان الكامل للعميل...">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="أي ملاحظات أو تفاصيل إضافية...">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- مقارنة التغييرات -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ملخص التغييرات
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>البيانات الحالية:</strong>
                                        <ul class="list-unstyled mb-0 mt-2">
                                            <li>الاسم: {{ $customer->name }}</li>
                                            <li>رمز العميل: {{ $customer->customer_code }}</li>
                                            <li>الحالة: {{ $customer->status === 'active' ? 'نشط' : 'غير نشط' }}</li>
                                            <li>البريد: {{ $customer->email ?? 'غير محدد' }}</li>
                                            <li>الهاتف: {{ $customer->phone ?? 'غير محدد' }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>البيانات الجديدة:</strong>
                                        <ul class="list-unstyled mb-0 mt-2" id="new-values">
                                            <li>الاسم: <span id="new-name">{{ $customer->name }}</span></li>
                                            <li>رمز العميل: <span id="new-code">{{ $customer->customer_code }}</span></li>
                                            <li>الحالة: <span id="new-status">{{ $customer->status === 'active' ? 'نشط' : 'غير نشط' }}</span></li>
                                            <li>البريد: <span id="new-email">{{ $customer->email ?? 'غير محدد' }}</span></li>
                                            <li>الهاتف: <span id="new-phone">{{ $customer->phone ?? 'غير محدد' }}</span></li>
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
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>
                                إلغاء
                            </a>
                            @if($customer->invoices()->count() === 0)
                                <button type="button" class="btn btn-danger" onclick="deleteCustomer()">
                                    <i class="fas fa-trash me-2"></i>
                                    حذف العميل
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

                @if($customer->invoices()->count() === 0)
                <!-- نموذج حذف العميل -->
                <form action="{{ route('customers.destroy', $customer) }}" method="POST" id="delete-form" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- الجانب الأيمن -->
    <div class="col-lg-4">
        <!-- ملخص العميل -->
        <div class="card sticky-top">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user-tie me-2"></i>
                    ملخص العميل
                </h6>
            </div>
            <div class="card-body">
                <div id="customer-summary">
                    <div class="summary-item mb-3">
                        <label class="text-muted">اسم العميل:</label>
                        <p class="fw-bold" id="summary-name">{{ $customer->name }}</p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">رمز العميل:</label>
                        <p class="fw-bold" id="summary-code">{{ $customer->customer_code }}</p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">الشركة:</label>
                        <p class="fw-bold" id="summary-company">{{ $customer->company_name ?? 'غير محدد' }}</p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">الحالة:</label>
                        <p class="fw-bold" id="summary-status">
                            <span class="badge badge-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $customer->status === 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">البريد الإلكتروني:</label>
                        <p class="fw-bold" id="summary-email">{{ $customer->email ?? 'غير محدد' }}</p>
                    </div>

                    <div class="summary-item mb-3">
                        <label class="text-muted">رقم الهاتف:</label>
                        <p class="fw-bold" id="summary-phone">{{ $customer->phone ?? 'غير محدد' }}</p>
                    </div>

                    <div class="summary-item">
                        <label class="text-muted">حد الائتمان:</label>
                        <p class="fw-bold" id="summary-credit">
                            {{ $customer->credit_limit ? number_format($customer->credit_limit, 2) . ' ر.س' : 'غير محدود' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصائيات العميل -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    إحصائيات العميل
                </h6>
            </div>
            <div class="card-body">
                @php
                    $totalInvoices = $customer->invoices()->count();
                    $totalAmount = $customer->invoices()->sum('total_amount');
                    $paidAmount = $customer->invoices()->sum('paid_amount');
                    $outstandingAmount = $totalAmount - $paidAmount;
                @endphp

                <div class="stats-grid">
                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h6 class="mb-0">{{ $totalInvoices }}</h6>
                        <small class="text-muted">إجمالي الفواتير</small>
                    </div>

                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-success text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($totalAmount, 0) }}</h6>
                        <small class="text-muted">إجمالي المبالغ (ر.س)</small>
                    </div>

                    <div class="stat-item text-center">
                        <div class="stat-icon bg-warning text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($outstandingAmount, 0) }}</h6>
                        <small class="text-muted">المبالغ المستحقة (ر.س)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- إرشادات -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    إرشادات التعديل
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        رمز العميل يجب أن يكون فريد ولا يمكن تكراره
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        البريد الإلكتروني مطلوب لإرسال الفواتير
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        حد الائتمان يحدد المبلغ الأقصى للديون
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        العملاء غير النشطين لا يظهرون في إنشاء الفواتير
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        لا يمكن حذف العميل إذا كان له فواتير
                    </li>
                </ul>
            </div>
        </div>

        @if($customer->invoices()->count() > 0)
        <!-- تحذير الفواتير -->
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تنبيه هام
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-0">لا يمكن حذف هذا العميل لأن له {{ $totalInvoices }} فاتورة مسجلة في النظام.</p>
                <p class="mt-2 mb-0">إذا كنت تريد إخفاء العميل، يمكنك تغيير حالته إلى "غير نشط".</p>
            </div>
        </div>
        @endif

        <!-- آخر التحديثات -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    آخر التحديثات
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline-simple">
                    <div class="timeline-item mb-3">
                        <div class="timeline-content">
                            <h6 class="timeline-title">إنشاء العميل</h6>
                            <p class="timeline-text text-muted mb-1">تم إنشاء ملف العميل في النظام</p>
                            <small class="timeline-time text-muted">{{ $customer->created_at->format('d/m/Y h:i A') }}</small>
                        </div>
                    </div>

                    @if($customer->updated_at != $customer->created_at)
                    <div class="timeline-item mb-3">
                        <div class="timeline-content">
                            <h6 class="timeline-title">آخر تحديث</h6>
                            <p class="timeline-text text-muted mb-1">تم تحديث بيانات العميل</p>
                            <small class="timeline-time text-muted">{{ $customer->updated_at->format('d/m/Y h:i A') }}</small>
                        </div>
                    </div>
                    @endif

                    @if($customer->invoices()->count() > 0)
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h6 class="timeline-title">آخر فاتورة</h6>
                            <p class="timeline-text text-muted mb-1">تم إنشاء فاتورة جديدة للعميل</p>
                            <small class="timeline-time text-muted">{{ $customer->invoices()->latest()->first()->created_at->format('d/m/Y h:i A') }}</small>
                        </div>
                    </div>
                    @endif
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

.stat-icon {
    font-size: 0.875rem;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.timeline-simple .timeline-item {
    position: relative;
    padding-left: 1.5rem;
}

.timeline-simple .timeline-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.25rem;
    width: 8px;
    height: 8px;
    background: #007bff;
    border-radius: 50%;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
}

.timeline-time {
    font-size: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحديث الملخص عند تغيير أي قيمة
    const formInputs = document.querySelectorAll('#customer-form input, #customer-form select, #customer-form textarea');

    formInputs.forEach(input => {
        input.addEventListener('input', updateSummary);
        input.addEventListener('change', updateSummary);
    });

    function updateSummary() {
        // تحديث الاسم
        const nameInput = document.getElementById('name');
        const summaryName = document.getElementById('summary-name');
        const newName = document.getElementById('new-name');
        if (nameInput.value) {
            summaryName.textContent = nameInput.value;
            newName.textContent = nameInput.value;
        }

        // تحديث رمز العميل
        const codeInput = document.getElementById('customer_code');
        const summaryCode = document.getElementById('summary-code');
        const newCode = document.getElementById('new-code');
        if (codeInput.value) {
            summaryCode.textContent = codeInput.value;
            newCode.textContent = codeInput.value;
        }

        // تحديث الشركة
        const companyInput = document.getElementById('company_name');
        const summaryCompany = document.getElementById('summary-company');
        summaryCompany.textContent = companyInput.value || 'غير محدد';

        // تحديث الحالة
        const statusSelect = document.getElementById('status');
        const summaryStatus = document.getElementById('summary-status');
        const newStatus = document.getElementById('new-status');

        if (statusSelect.value) {
            const statusText = statusSelect.value === 'active' ? 'نشط' : 'غير نشط';
            const badgeClass = statusSelect.value === 'active' ? 'badge-success' : 'badge-secondary';

            summaryStatus.innerHTML = `<span class="badge ${badgeClass}">${statusText}</span>`;
            newStatus.textContent = statusText;
        }

        // تحديث البريد الإلكتروني
        const emailInput = document.getElementById('email');
        const summaryEmail = document.getElementById('summary-email');
        const newEmail = document.getElementById('new-email');
        const emailValue = emailInput.value || 'غير محدد';
        summaryEmail.textContent = emailValue;
        newEmail.textContent = emailValue;

        // تحديث الهاتف
        const phoneInput = document.getElementById('phone');
        const summaryPhone = document.getElementById('summary-phone');
        const newPhone = document.getElementById('new-phone');
        const phoneValue = phoneInput.value || 'غير محدد';
        summaryPhone.textContent = phoneValue;
        newPhone.textContent = phoneValue;

        // تحديث حد الائتمان
        const creditInput = document.getElementById('credit_limit');
        const summaryCredit = document.getElementById('summary-credit');
        const creditValue = creditInput.value ? number_format(parseFloat(creditInput.value), 2) + ' ر.س' : 'غير محدود';
        summaryCredit.textContent = creditValue;
    }

    function number_format(number, decimals) {
        return number.toLocaleString('ar-SA', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // التحقق من صحة النموذج
    document.getElementById('customer-form').addEventListener('submit', function(e) {
        const customerCode = document.getElementById('customer_code').value;
        const name = document.getElementById('name').value;
        const status = document.getElementById('status').value;

        if (!customerCode || !name || !status) {
            e.preventDefault();
            alert('يرجى ملء جميع الحقول المطلوبة');
            return false;
        }

        // التحقق من صحة البريد الإلكتروني
        const email = document.getElementById('email').value;
        if (email && !isValidEmail(email)) {
            e.preventDefault();
            alert('يرجى إدخال بريد إلكتروني صحيح');
            return false;
        }
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // حذف العميل
    window.deleteCustomer = function() {
        if (confirm('هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن هذا الإجراء.')) {
            document.getElementById('delete-form').submit();
        }
    }
});
</script>
@endpush
