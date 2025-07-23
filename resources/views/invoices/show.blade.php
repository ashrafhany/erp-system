@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة #' . $invoice->invoice_number)
@section('page-title', 'تفاصيل الفاتورة #' . $invoice->invoice_number)

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للفواتير
        </a>
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>
            تعديل
        </a>
        <button type="button" class="btn btn-info" onclick="printInvoice()">
            <i class="fas fa-print me-2"></i>
            طباعة
        </button>
        <button type="button" class="btn btn-success" onclick="downloadPDF()">
            <i class="fas fa-download me-2"></i>
            تحميل PDF
        </button>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- بيانات الفاتورة -->
        <div class="card">
            <div class="card-body" id="invoice-content">
                <!-- رأس الفاتورة -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h3 class="text-primary">فاتورة</h3>
                        <p class="mb-1"><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</p>
                        <p class="mb-1"><strong>تاريخ الفاتورة:</strong> {{ $invoice->invoice_date }}</p>
                        <p class="mb-0"><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="badge fs-6
                            @if($invoice->status == 'paid') bg-success
                            @elseif($invoice->status == 'sent') bg-info
                            @elseif($invoice->status == 'overdue') bg-danger
                            @elseif($invoice->status == 'cancelled') bg-secondary
                            @else bg-warning
                            @endif">
                            @switch($invoice->status)
                                @case('paid') مدفوعة @break
                                @case('sent') مرسلة @break
                                @case('overdue') متأخرة @break
                                @case('cancelled') ملغاة @break
                                @default مسودة
                            @endswitch
                        </span>
                    </div>
                </div>

                <hr>

                <!-- بيانات العميل -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">معلومات الشركة</h6>
                        <p class="mb-1"><strong>نظام ERP</strong></p>
                        <p class="mb-1">الرياض، المملكة العربية السعودية</p>
                        <p class="mb-0">هاتف: +966 11 1234567</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">فاتورة إلى</h6>
                        <p class="mb-1"><strong>{{ $invoice->customer->name }}</strong></p>
                        @if($invoice->customer->address)
                            <p class="mb-1">{{ $invoice->customer->address }}</p>
                        @endif
                        @if($invoice->customer->phone)
                            <p class="mb-1">هاتف: {{ $invoice->customer->phone }}</p>
                        @endif
                        @if($invoice->customer->email)
                            <p class="mb-0">بريد إلكتروني: {{ $invoice->customer->email }}</p>
                        @endif
                    </div>
                </div>

                <!-- عناصر الفاتورة -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الوصف</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-end">السعر</th>
                                <th class="text-end">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }} ر.س</td>
                                    <td class="text-end">{{ number_format($item->total_price, 2) }} ر.س</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>المجموع الفرعي:</strong></td>
                                <td class="text-end"><strong>{{ number_format($invoice->subtotal, 2) }} ر.س</strong></td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">الخصم:</td>
                                    <td class="text-end">{{ number_format($invoice->discount_amount, 2) }} ر.س</td>
                                </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">الضريبة:</td>
                                    <td class="text-end">{{ number_format($invoice->tax_amount, 2) }} ر.س</td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>المجموع النهائي:</strong></td>
                                <td class="text-end"><strong>{{ number_format($invoice->total_amount, 2) }} ر.س</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- الملاحظات -->
                @if($invoice->notes)
                    <div class="mb-4">
                        <h6 class="text-muted">ملاحظات</h6>
                        <p class="mb-0">{{ $invoice->notes }}</p>
                    </div>
                @endif

                <!-- شروط الدفع -->
                <div class="border-top pt-3">
                    <h6 class="text-muted">شروط الدفع</h6>
                    <p class="small text-muted mb-0">
                        يرجى سداد المبلغ في موعد أقصاه تاريخ الاستحقاق المحدد.
                        في حالة التأخير عن السداد، سيتم تطبيق فوائد تأخير بمعدل 2% شهرياً.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- معلومات إضافية -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات الفاتورة
                </h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6">تاريخ الإنشاء:</div>
                    <div class="col-6 text-end">{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6">آخر تحديث:</div>
                    <div class="col-6 text-end">{{ $invoice->updated_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6">عدد العناصر:</div>
                    <div class="col-6 text-end">{{ $invoice->items->count() }}</div>
                </div>
                <div class="row mb-0">
                    <div class="col-6">المبلغ المدفوع:</div>
                    <div class="col-6 text-end">{{ number_format($invoice->paid_amount, 2) }} ر.س</div>
                </div>
            </div>
        </div>

        <!-- الإجراءات السريعة -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    إجراءات سريعة
                </h6>
            </div>
            <div class="card-body">
                @if($invoice->status == 'draft')
                    <button type="button" class="btn btn-info btn-sm w-100 mb-2" onclick="markAsSent()">
                        <i class="fas fa-paper-plane me-2"></i>
                        تحديد كمرسلة
                    </button>
                @endif

                @if($invoice->status != 'paid' && $invoice->status != 'cancelled')
                    <button type="button" class="btn btn-success btn-sm w-100 mb-2" onclick="markAsPaid()">
                        <i class="fas fa-check-circle me-2"></i>
                        تسجيل دفعة
                    </button>
                @endif

                @if($invoice->status != 'cancelled')
                    <button type="button" class="btn btn-danger btn-sm w-100 mb-2" onclick="cancelInvoice()">
                        <i class="fas fa-times-circle me-2"></i>
                        إلغاء الفاتورة
                    </button>
                @endif

                <hr class="my-3">

                <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="sendByEmail()">
                    <i class="fas fa-envelope me-2"></i>
                    إرسال بالبريد الإلكتروني
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="duplicateInvoice()">
                    <i class="fas fa-copy me-2"></i>
                    تكرار الفاتورة
                </button>
            </div>
        </div>

        <!-- سجل المدفوعات -->
        @if($invoice->payments && $invoice->payments->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        سجل المدفوعات
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($invoice->payments as $payment)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="small">{{ $payment->payment_date }}</div>
                                <div class="text-muted small">{{ $payment->payment_method }}</div>
                            </div>
                            <div class="text-success fw-bold">
                                {{ number_format($payment->amount, 2) }} ر.س
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-2">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .card-header, .page-actions, nav, footer {
        display: none !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .col-lg-4 {
        display: none !important;
    }

    .col-lg-8 {
        width: 100% !important;
    }

    body {
        background: white !important;
    }
}

.invoice-status {
    font-size: 1.2rem;
    padding: 0.5rem 1rem;
}
</style>
@endpush

@push('scripts')
<script>
function printInvoice() {
    window.print();
}

function downloadPDF() {
    window.location.href = '/invoices/{{ $invoice->id }}/pdf';
}

function markAsSent() {
    if (confirm('هل تريد تحديد هذه الفاتورة كمرسلة؟')) {
        // إضافة AJAX call هنا
        updateInvoiceStatus('sent');
    }
}

function markAsPaid() {
    const amount = prompt('أدخل المبلغ المدفوع:', '{{ $invoice->total_amount }}');
    if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
        // إضافة AJAX call هنا
        recordPayment(parseFloat(amount));
    }
}

function cancelInvoice() {
    if (confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟ لا يمكن التراجع عن هذا الإجراء.')) {
        // إضافة AJAX call هنا
        updateInvoiceStatus('cancelled');
    }
}

function sendByEmail() {
    alert('سيتم تفعيل هذه الميزة قريباً');
}

function duplicateInvoice() {
    if (confirm('هل تريد إنشاء فاتورة جديدة بنفس البيانات؟')) {
        window.location.href = '/invoices/create?duplicate={{ $invoice->id }}';
    }
}

function updateInvoiceStatus(status) {
    // هنا يمكن إضافة AJAX call لتحديث حالة الفاتورة
    // مؤقتاً سنقوم بإعادة تحميل الصفحة
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/invoices/{{ $invoice->id }}/status';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';

    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PATCH';

    const statusField = document.createElement('input');
    statusField.type = 'hidden';
    statusField.name = 'status';
    statusField.value = status;

    form.appendChild(csrfToken);
    form.appendChild(methodField);
    form.appendChild(statusField);

    document.body.appendChild(form);
    form.submit();
}

function recordPayment(amount) {
    // هنا يمكن إضافة AJAX call لتسجيل دفعة
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/invoices/{{ $invoice->id }}/payment';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';

    const amountField = document.createElement('input');
    amountField.type = 'hidden';
    amountField.name = 'amount';
    amountField.value = amount;

    const dateField = document.createElement('input');
    dateField.type = 'hidden';
    dateField.name = 'payment_date';
    dateField.value = new Date().toISOString().split('T')[0];

    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = 'payment_method';
    methodField.value = 'cash';

    form.appendChild(csrfToken);
    form.appendChild(amountField);
    form.appendChild(dateField);
    form.appendChild(methodField);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
