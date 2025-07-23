@extends('layouts.app')

@section('title', 'إنشاء فاتورة جديدة')
@section('page-title', 'إنشاء فاتورة جديدة')

@section('page-actions')
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        العودة للفواتير
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    بيانات الفاتورة
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">العميل <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror"
                                    id="customer_id" name="customer_id" required>
                                <option value="">اختر العميل</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="invoice_date" class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                                   id="invoice_date" name="invoice_date"
                                   value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="due_date" class="form-label">تاريخ الاستحقاق <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                   id="due_date" name="due_date"
                                   value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">حالة الفاتورة</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status">
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>مرسلة</option>
                                <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- عناصر الفاتورة -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">عناصر الفاتورة</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-item">
                                <i class="fas fa-plus me-1"></i>
                                إضافة عنصر
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">الوصف</th>
                                        <th style="width: 15%">الكمية</th>
                                        <th style="width: 15%">السعر</th>
                                        <th style="width: 15%">الإجمالي</th>
                                        <th style="width: 10%">إجراءات</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                    <!-- سيتم إضافة العناصر هنا بـ JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- الملاحظات والإجماليات -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="4"
                                          placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">المجموع الفرعي:</div>
                                        <div class="col-6 text-end">
                                            <span id="subtotal">0.00</span> ر.س
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-8">
                                            <label for="discount_amount" class="form-label mb-0">الخصم:</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm @error('discount_amount') is-invalid @enderror"
                                                   id="discount_amount" name="discount_amount"
                                                   value="{{ old('discount_amount', 0) }}"
                                                   onchange="calculateTotal()">
                                            @error('discount_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4 text-end">
                                            <span id="discount_display">0.00</span> ر.س
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-8">
                                            <label for="tax_amount" class="form-label mb-0">الضريبة:</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm @error('tax_amount') is-invalid @enderror"
                                                   id="tax_amount" name="tax_amount"
                                                   value="{{ old('tax_amount', 0) }}"
                                                   onchange="calculateTotal()">
                                            @error('tax_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4 text-end">
                                            <span id="tax_display">0.00</span> ر.س
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-6"><strong>المجموع النهائي:</strong></div>
                                        <div class="col-6 text-end">
                                            <strong><span id="total">0.00</span> ر.س</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-2"></i>
                                حفظ الفاتورة
                            </button>
                            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- معلومات العميل -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    معلومات العميل
                </h6>
            </div>
            <div class="card-body" id="customer-info">
                <p class="text-muted">اختر عميل لعرض معلوماته</p>
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
                        أضف عناصر الفاتورة بالضغط على "إضافة عنصر"
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        سيتم حساب المجموع تلقائياً
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        يمكن تعديل الضريبة والخصم
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        سيتم إنشاء رقم الفاتورة تلقائياً
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.item-row {
    background-color: #fdfdfd;
}

.item-row:hover {
    background-color: #f8f9fa;
}

#customer-info {
    min-height: 150px;
}

.invoice-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;

    // إضافة عنصر جديد
    document.getElementById('add-item').addEventListener('click', function() {
        addInvoiceItem();
    });

    // تغيير العميل
    document.getElementById('customer_id').addEventListener('change', function() {
        loadCustomerInfo(this.value);
    });

    // إضافة عنصر أولي
    addInvoiceItem();

    function addInvoiceItem() {
        const tbody = document.getElementById('items-tbody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>
                <input type="text" class="form-control" name="items[${itemIndex}][description]"
                       placeholder="وصف العنصر" required>
            </td>
            <td>
                <input type="number" class="form-control" name="items[${itemIndex}][quantity]"
                       min="1" value="1" onchange="calculateRowTotal(this)" required>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][unit_price]"
                       placeholder="0.00" onchange="calculateRowTotal(this)" required>
            </td>
            <td>
                <span class="row-total">0.00</span> ر.س
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        itemIndex++;
    }

    function removeItem(button) {
        if (document.querySelectorAll('.item-row').length > 1) {
            button.closest('tr').remove();
            calculateTotal();
        } else {
            alert('يجب أن تحتوي الفاتورة على عنصر واحد على الأقل');
        }
    }

    // حساب مجموع الصف
    window.calculateRowTotal = function(input) {
        const row = input.closest('tr');
        const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
        const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
        const total = quantity * unitPrice;

        row.querySelector('.row-total').textContent = total.toFixed(2);
        calculateTotal();
    }

    // حساب المجموع الكلي
    window.calculateTotal = function() {
        let subtotal = 0;

        // حساب المجموع الفرعي
        document.querySelectorAll('.row-total').forEach(function(element) {
            subtotal += parseFloat(element.textContent) || 0;
        });

        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;

        const total = subtotal - discount + tax;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('discount_display').textContent = discount.toFixed(2);
        document.getElementById('tax_display').textContent = tax.toFixed(2);
        document.getElementById('total').textContent = total.toFixed(2);
    }

    // تحميل معلومات العميل
    function loadCustomerInfo(customerId) {
        const customerInfo = document.getElementById('customer-info');

        if (!customerId) {
            customerInfo.innerHTML = '<p class="text-muted">اختر عميل لعرض معلوماته</p>';
            return;
        }

        // هنا يمكن إضافة AJAX call لجلب معلومات العميل
        // لكن الآن سنستخدم البيانات الموجودة في الـ select
        const customerSelect = document.getElementById('customer_id');
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];
        const customerName = selectedOption.text;

        customerInfo.innerHTML = `
            <div class="customer-details">
                <h6 class="text-primary">${customerName}</h6>
                <p class="text-muted mb-1">
                    <i class="fas fa-user me-2"></i>
                    ${customerName}
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    تم اختيار العميل بنجاح
                </p>
            </div>
        `;
    }

    // إعداد validation للنموذج
    document.getElementById('invoice-form').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.item-row');
        let hasValidItems = false;

        items.forEach(function(row) {
            const description = row.querySelector('input[name*="[description]"]').value.trim();
            const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;

            if (description && quantity > 0 && unitPrice > 0) {
                hasValidItems = true;
            }
        });

        if (!hasValidItems) {
            e.preventDefault();
            alert('يجب إضافة عنصر واحد صحيح على الأقل للفاتورة');
            return false;
        }
    });

    // جعل الدوال متاحة عالمياً
    window.removeItem = removeItem;
});
</script>
@endpush
