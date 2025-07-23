@extends('layouts.app')

@section('title', 'تعديل الفاتورة #' . $invoice->invoice_number)
@section('page-title', 'تعديل الفاتورة #' . $invoice->invoice_number)

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للفاتورة
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-2"></i>
            جميع الفواتير
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    تعديل بيانات الفاتورة
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoice-form">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">العميل <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror"
                                    id="customer_id" name="customer_id" required>
                                <option value="">اختر العميل</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
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
                                   value="{{ old('invoice_date', $invoice->invoice_date) }}" required>
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
                                   value="{{ old('due_date', $invoice->due_date) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">حالة الفاتورة</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status">
                                <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>مرسلة</option>
                                <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                                <option value="overdue" {{ old('status', $invoice->status) == 'overdue' ? 'selected' : '' }}>متأخرة</option>
                                <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
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
                                    @foreach($invoice->items as $index => $item)
                                        <tr class="item-row">
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                <input type="text" class="form-control"
                                                       name="items[{{ $index }}][description]"
                                                       value="{{ $item->description }}"
                                                       placeholder="وصف العنصر" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control"
                                                       name="items[{{ $index }}][quantity]"
                                                       value="{{ $item->quantity }}"
                                                       min="1" onchange="calculateRowTotal(this)" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control"
                                                       name="items[{{ $index }}][unit_price]"
                                                       value="{{ $item->unit_price }}"
                                                       placeholder="0.00" onchange="calculateRowTotal(this)" required>
                                            </td>
                                            <td>
                                                <span class="row-total">{{ number_format($item->total_price, 2) }}</span> ر.س
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
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
                                          placeholder="أي ملاحظات إضافية...">{{ old('notes', $invoice->notes) }}</textarea>
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
                                            <span id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span> ر.س
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-8">
                                            <label for="discount_amount" class="form-label mb-0">الخصم:</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm @error('discount_amount') is-invalid @enderror"
                                                   id="discount_amount" name="discount_amount"
                                                   value="{{ old('discount_amount', $invoice->discount_amount) }}"
                                                   onchange="calculateTotal()">
                                            @error('discount_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4 text-end">
                                            <span id="discount_display">{{ number_format($invoice->discount_amount, 2) }}</span> ر.س
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-8">
                                            <label for="tax_amount" class="form-label mb-0">الضريبة:</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm @error('tax_amount') is-invalid @enderror"
                                                   id="tax_amount" name="tax_amount"
                                                   value="{{ old('tax_amount', $invoice->tax_amount) }}"
                                                   onchange="calculateTotal()">
                                            @error('tax_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-4 text-end">
                                            <span id="tax_display">{{ number_format($invoice->tax_amount, 2) }}</span> ر.س
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-6"><strong>المجموع النهائي:</strong></div>
                                        <div class="col-6 text-end">
                                            <strong><span id="total">{{ number_format($invoice->total_amount, 2) }}</span> ر.س</strong>
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
                                حفظ التعديلات
                            </button>
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">
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
                <!-- سيتم تحديث المحتوى بـ JavaScript -->
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    معلومات الفاتورة
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p class="mb-2">
                        <i class="fas fa-hashtag me-2"></i>
                        <strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-calendar me-2"></i>
                        <strong>تاريخ الإنشاء:</strong> {{ $invoice->created_at->format('Y-m-d H:i') }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-edit me-2"></i>
                        <strong>آخر تحديث:</strong> {{ $invoice->updated_at->format('Y-m-d H:i') }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        <strong>المبلغ المدفوع:</strong> {{ number_format($invoice->paid_amount, 2) }} ر.س
                    </p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    تنبيهات
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    سيتم حفظ التعديلات على الفاتورة الحالية. تأكد من صحة البيانات قبل الحفظ.
                </div>

                @if($invoice->status == 'paid')
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-2"></i>
                        هذه الفاتورة مدفوعة. قد تحتاج لإذن خاص لتعديلها.
                    </div>
                @endif
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = {{ $invoice->items->count() }};

    // إضافة عنصر جديد
    document.getElementById('add-item').addEventListener('click', function() {
        addInvoiceItem();
    });

    // تغيير العميل
    document.getElementById('customer_id').addEventListener('change', function() {
        loadCustomerInfo(this.value);
    });

    // تحميل معلومات العميل الحالي
    loadCustomerInfo(document.getElementById('customer_id').value);

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

    // جعل الدوال متاحة عالمياً
    window.removeItem = removeItem;
});
</script>
@endpush
