@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة #' . $invoice->invoice_number)
@section('page-title', 'تفاصيل الفاتورة #' . $invoice->invoice_number)

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للفواتير
        </a>
        @can('invoices.update')
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>
            تعديل
        </a>
        @endcan
        <button type="button" class="btn btn-info" onclick="printInvoice()">
            <i class="fas fa-print me-2"></i>
            طباعة
        </button>
        <button type="button" class="btn btn-success" onclick="printInvoice()">
            <i class="fas fa-download me-2"></i>
            حفظ PDF
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
                        <p class="mb-1"><strong>تاريخ الفاتورة:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</p>
                        <p class="mb-0"><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date->format('Y-m-d') }}</p>
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
                                @case('sent') {{ $invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount ? 'مدفوعة جزئيًا' : 'مرسلة' }} @break
                                @case('overdue') {{ $invoice->paid_amount > 0 ? 'متأخرة ومدفوعة جزئيًا' : 'متأخرة' }} @break
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
                        <p class="mb-1"><strong>{{ config('app.name', 'نظام ERP') }}</strong></p>
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
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }} ج.م</td>
                                    <td class="text-end">{{ number_format($item->total_price, 2) }} ج.م</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>المجموع الفرعي:</strong></td>
                                <td class="text-end"><strong>{{ number_format($invoice->subtotal, 2) }} ج.م</strong></td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">الخصم:</td>
                                    <td class="text-end">{{ number_format($invoice->discount_amount, 2) }} ج.م</td>
                                </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">الضريبة:</td>
                                    <td class="text-end">{{ number_format($invoice->tax_amount, 2) }} ج.م</td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>المجموع النهائي:</strong></td>
                                <td class="text-end"><strong>{{ number_format($invoice->total_amount, 2) }} ج.م</strong></td>
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
                    <div class="col-6 text-end">{{ number_format($invoice->paid_amount, 2) }} ج.م</div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">المبلغ المتبقي:</div>
                    <div class="col-6 text-end fw-bold">{{ number_format(max(0, $invoice->remaining_amount), 2) }} ج.م</div>
                </div>
                @if($invoice->paid_amount > 0 && $invoice->remaining_amount > 0)
                    <div class="badge bg-warning text-dark mt-2">مدفوعة جزئيًا</div>
                @endif
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
                <p class="small text-muted">المسودة فاتورة تحت التجهيز. بعد مراجعتها اختر «تحديد كمرسلة»، ثم «تسجيل دفعة». تتحول إلى مدفوعة تلقائيًا عند سداد كامل الإجمالي.</p>
                @if($invoice->status == 'draft' && auth()->user()->can('invoices.send'))
                    <form action="{{ route('invoices.send', $invoice) }}" method="POST">@csrf
                    <button type="submit" class="btn btn-info btn-sm w-100 mb-2" onclick="return confirm('تحديد الفاتورة كمرسلة؟')">
                        <i class="fas fa-paper-plane me-2"></i>
                        تحديد كمرسلة
                    </button>
                    </form>
                @endif

                @if(in_array($invoice->status, ['sent', 'overdue']) && $invoice->remaining_amount > 0 && auth()->user()->can('invoices.payment'))
                    <details class="mb-3">
                        <summary class="btn btn-success btn-sm w-100">تسجيل دفعة</summary>
                        <form action="{{ route('invoices.payment', $invoice) }}" method="POST" class="mt-2">
                            @csrf
                            <div class="small mb-2">المتبقي: {{ number_format($invoice->remaining_amount, 2) }} ج.م</div>
                            <label class="form-label">قيمة الدفعة</label>
                            <input class="form-control mb-2" type="number" name="payment_amount" min="0.01" max="{{ $invoice->remaining_amount }}" step="0.01" value="{{ old('payment_amount') }}" required>
                            <label class="form-label">تاريخ الدفع</label>
                            <input class="form-control mb-2" type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            <label class="form-label">طريقة الدفع</label>
                            <select class="form-select mb-2" name="payment_method" required>
                                <option value="cash">نقدًا</option><option value="bank_transfer">تحويل بنكي</option><option value="card">بطاقة</option><option value="other">أخرى</option>
                            </select>
                            <button class="btn btn-success btn-sm w-100">حفظ الدفعة</button>
                        </form>
                    </details>
                @endif

                @if($invoice->paid_amount > $invoice->total_amount || ($invoice->status === 'paid' && $invoice->paid_amount < $invoice->total_amount) || ($invoice->status === 'draft' && $invoice->paid_amount > 0))
                    <div class="alert alert-warning small">المبلغ المدفوع لا يتوافق مع إجمالي الفاتورة أو حالتها. راجع البيانات القديمة قبل تسجيل أي دفعة جديدة.</div>
                @endif
                @can('invoices.reconcile')
                    @if($invoice->total_amount > 0)
                        <details class="mb-3">
                            <summary class="btn btn-outline-warning btn-sm w-100">تصحيح المبلغ المدفوع</summary>
                            <form action="{{ route('invoices.reconcile', $invoice) }}" method="POST" class="mt-2" onsubmit="return confirm('تأكيد تصحيح المبلغ المدفوع؟')">
                                @csrf
                                <label class="form-label">المبلغ الصحيح</label>
                                <input class="form-control mb-2" type="number" name="paid_amount" min="0" max="{{ $invoice->total_amount }}" step="0.01" value="{{ min($invoice->paid_amount, $invoice->total_amount) }}" required>
                                <input class="form-control mb-2" name="reason" minlength="5" maxlength="255" placeholder="سبب التصحيح" required>
                                <button class="btn btn-outline-warning btn-sm w-100">حفظ التصحيح</button>
                            </form>
                        </details>
                    @endif
                @endcan

                @if($invoice->status === 'draft' && $invoice->paid_amount == 0 && auth()->user()->can('invoices.update'))
                    <form action="{{ route('invoices.cancel', $invoice) }}" method="POST">@csrf
                    <button type="submit" class="btn btn-danger btn-sm w-100 mb-2" onclick="return confirm('إلغاء الفاتورة؟')">
                        <i class="fas fa-times-circle me-2"></i>
                        إلغاء الفاتورة
                    </button>
                    </form>
                @endif

                <hr class="my-3">

                @can('invoices.create')
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="duplicateInvoice()">
                    <i class="fas fa-copy me-2"></i>
                    تكرار الفاتورة
                </button>
                @endcan
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
                                <div class="small">{{ $payment->payment_date->format('Y-m-d') }}</div>
                                <div class="text-muted small">{{ ['cash' => 'نقدًا', 'bank_transfer' => 'تحويل بنكي', 'card' => 'بطاقة', 'other' => 'أخرى'][$payment->payment_method] ?? $payment->payment_method }}</div>
                            </div>
                            <div class="text-success fw-bold">
                                {{ number_format($payment->amount, 2) }} ج.م
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-2">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        @if($paymentAdjustments->isNotEmpty())
            <div class="card mt-3"><div class="card-header">تصحيحات المبلغ المدفوع</div><div class="card-body small">
                @foreach($paymentAdjustments as $adjustment)
                    <div>{{ $adjustment->created_at }}: {{ number_format($adjustment->old_amount, 2) }} ← {{ number_format($adjustment->new_amount, 2) }} ج.م</div>
                    <div class="text-muted mb-2">{{ $adjustment->reason }}</div>
                @endforeach
            </div></div>
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

function duplicateInvoice() {
    if (confirm('هل تريد إنشاء فاتورة جديدة بنفس البيانات؟')) {
        window.location.href = '/invoices/create?duplicate={{ $invoice->id }}';
    }
}
</script>
@endpush
