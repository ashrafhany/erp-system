@extends('layouts.app')

@section('title', 'تفاصيل العميل')
@section('page-title', 'تفاصيل العميل')

@section('page-actions')
    <div class="btn-group" role="group">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            العودة للعملاء
        </a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>
            تعديل
        </a>
        <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>
            إنشاء فاتورة جديدة
        </a>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-print me-2"></i>
                طباعة/تصدير
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>طباعة
                </a></li>
                <li><a class="dropdown-item" href="{{ route('customers.export.pdf', $customer) }}">
                    <i class="fas fa-file-pdf me-2"></i>تصدير PDF
                </a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- معلومات العميل -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        {{ $customer->name }}
                    </h5>
                    <span class="badge badge-{{ $customer->status === 'active' ? 'success' : 'secondary' }} fs-6">
                        @if($customer->status === 'active')
                            <i class="fas fa-check-circle me-1"></i> نشط
                        @else
                            <i class="fas fa-pause-circle me-1"></i> غير نشط
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- المعلومات الأساسية -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">رمز العميل:</label>
                            <p class="fw-bold">{{ $customer->customer_code }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">اسم الشركة:</label>
                            <p class="fw-bold">{{ $customer->company_name ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">البريد الإلكتروني:</label>
                            <p class="fw-bold">
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">رقم الهاتف:</label>
                            <p class="fw-bold">
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">الرقم الضريبي:</label>
                            <p class="fw-bold">{{ $customer->tax_number ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label class="text-muted">حد الائتمان:</label>
                            <p class="fw-bold">
                                @if($customer->credit_limit)
                                    {{ number_format($customer->credit_limit, 2) }} ر.س
                                @else
                                    غير محدود
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if($customer->address)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="info-item">
                            <label class="text-muted">العنوان:</label>
                            <p class="fw-bold">{{ $customer->address }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($customer->notes)
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-sticky-note me-2"></i>
                            ملاحظات
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0">{{ $customer->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- فواتير العميل -->
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        آخر الفواتير ({{ $customer->invoices->count() }} من أصل {{ $totalInvoices }})
                    </h6>
                    <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                        عرض جميع الفواتير
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($customer->invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>التاريخ</th>
                                    <th>المبلغ الإجمالي</th>
                                    <th>المبلغ المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2) }} ر.س</td>
                                    <td>{{ number_format($invoice->paid_amount, 2) }} ر.س</td>
                                    <td>
                                        <span class="text-{{ $invoice->remaining_amount > 0 ? 'danger' : 'success' }}">
                                            {{ number_format($invoice->remaining_amount, 2) }} ر.س
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : ($invoice->status === 'sent' ? 'info' : 'secondary')) }}">
                                            @switch($invoice->status)
                                                @case('draft')
                                                    مسودة
                                                    @break
                                                @case('sent')
                                                    مرسلة
                                                    @break
                                                @case('partial')
                                                    مدفوعة جزئياً
                                                    @break
                                                @case('paid')
                                                    مدفوعة
                                                    @break
                                                @case('overdue')
                                                    متأخرة
                                                    @break
                                                @default
                                                    غير معروف
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-primary" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">لا توجد فواتير لهذا العميل</h6>
                        <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-2"></i>
                            إنشاء أول فاتورة
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- الجانب الأيمن -->
    <div class="col-lg-4">
        <!-- ملخص مالي -->
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <h6 class="card-title mb-0 text-center">
                    <i class="fas fa-chart-pie me-2"></i>
                    الملخص المالي
                </h6>
            </div>
            <div class="card-body">
                <div class="financial-summary">
                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">إجمالي الفواتير:</span>
                            <span class="fw-bold fs-5">{{ $totalInvoices }}</span>
                        </div>
                    </div>

                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">إجمالي المبالغ:</span>
                            <span class="fw-bold text-primary">{{ number_format($totalAmount, 2) }} ر.س</span>
                        </div>
                    </div>

                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">المبالغ المدفوعة:</span>
                            <span class="fw-bold text-success">{{ number_format($paidAmount, 2) }} ر.س</span>
                        </div>
                    </div>

                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">المبالغ المستحقة:</span>
                            <span class="fw-bold text-danger">{{ number_format($outstandingAmount, 2) }} ر.س</span>
                        </div>
                    </div>

                    @if($customer->credit_limit)
                    <div class="summary-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">الائتمان المتاح:</span>
                            <span class="fw-bold text-{{ ($customer->credit_limit - $outstandingAmount) >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($customer->credit_limit - $outstandingAmount, 2) }} ر.س
                            </span>
                        </div>
                        <div class="progress mt-2" style="height: 8px;">
                            @php
                                $usedPercentage = $customer->credit_limit > 0 ? ($outstandingAmount / $customer->credit_limit) * 100 : 0;
                                $usedPercentage = min(100, $usedPercentage);
                            @endphp
                            <div class="progress-bar bg-{{ $usedPercentage > 80 ? 'danger' : ($usedPercentage > 60 ? 'warning' : 'success') }}"
                                 style="width: {{ $usedPercentage }}%"></div>
                        </div>
                        <small class="text-muted">استخدام الائتمان: {{ number_format($usedPercentage, 1) }}%</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- معلومات سريعة -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات سريعة
                </h6>
            </div>
            <div class="card-body">
                <div class="quick-info">
                    <div class="info-row mb-2">
                        <i class="fas fa-calendar-plus text-primary me-2"></i>
                        <span class="text-muted">تاريخ الإضافة:</span>
                        <span class="fw-bold">{{ $customer->created_at->format('d/m/Y') }}</span>
                    </div>

                    <div class="info-row mb-2">
                        <i class="fas fa-calendar-edit text-warning me-2"></i>
                        <span class="text-muted">آخر تحديث:</span>
                        <span class="fw-bold">{{ $customer->updated_at->format('d/m/Y') }}</span>
                    </div>

                    @if($customer->invoices->count() > 0)
                    <div class="info-row mb-2">
                        <i class="fas fa-file-invoice text-success me-2"></i>
                        <span class="text-muted">آخر فاتورة:</span>
                        <span class="fw-bold">{{ $customer->invoices->first()->created_at->format('d/m/Y') }}</span>
                    </div>
                    @endif

                    <div class="info-row">
                        <i class="fas fa-{{ $customer->status === 'active' ? 'check-circle text-success' : 'pause-circle text-secondary' }} me-2"></i>
                        <span class="text-muted">الحالة:</span>
                        <span class="fw-bold">{{ $customer->status === 'active' ? 'نشط' : 'غير نشط' }}</span>
                    </div>
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
                <div class="d-grid gap-2">
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-2"></i>
                        إنشاء فاتورة جديدة
                    </a>

                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-2"></i>
                        تعديل بيانات العميل
                    </a>

                    @if($customer->email)
                    <a href="mailto:{{ $customer->email }}" class="btn btn-info btn-sm">
                        <i class="fas fa-envelope me-2"></i>
                        إرسال بريد إلكتروني
                    </a>
                    @endif

                    @if($customer->phone)
                    <a href="tel:{{ $customer->phone }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-phone me-2"></i>
                        اتصال هاتفي
                    </a>
                    @endif

                    <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-invoice me-2"></i>
                        عرض جميع الفواتير
                    </a>
                </div>
            </div>
        </div>

        <!-- إحصائيات -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    إحصائيات
                </h6>
            </div>
            <div class="card-body">
                @php
                    $stats = [
                        'thisMonth' => $customer->invoices()->whereMonth('invoice_date', now()->month)->sum('total_amount'),
                        'lastMonth' => $customer->invoices()->whereMonth('invoice_date', now()->subMonth()->month)->sum('total_amount'),
                        'avgInvoice' => $totalInvoices > 0 ? $totalAmount / $totalInvoices : 0,
                        'paymentRate' => $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0
                    ];
                @endphp

                <div class="stats-grid">
                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-primary text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-calendar-month"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($stats['thisMonth'], 0) }} ر.س</h6>
                        <small class="text-muted">فواتير هذا الشهر</small>
                    </div>

                    <div class="stat-item text-center mb-3">
                        <div class="stat-icon bg-info text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($stats['avgInvoice'], 0) }} ر.س</h6>
                        <small class="text-muted">متوسط قيمة الفاتورة</small>
                    </div>

                    <div class="stat-item text-center">
                        <div class="stat-icon bg-success text-white rounded-circle mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px;">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h6 class="mb-0">{{ number_format($stats['paymentRate'], 1) }}%</h6>
                        <small class="text-muted">معدل السداد</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.875em;
    padding: 0.5em 0.75em;
}

.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    display: block;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.summary-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-item:last-child {
    border-bottom: none;
}

.info-row {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
}

.info-row span {
    margin-left: 0.5rem;
}

.stat-icon {
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

@media print {
    .btn-group,
    .card:nth-child(n+2) {
        display: none !important;
    }

    .col-lg-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
