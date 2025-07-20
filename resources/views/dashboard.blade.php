@extends('layouts.app')

@section('title', 'لوحة المراقبة - نظام ERP')
@section('page-title', 'لوحة المراقبة')

@section('content')
<div class="row">
    <!-- بطاقات الإحصائيات -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-primary border-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            إجمالي الموظفين
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalEmployees }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-success border-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            إجمالي العملاء
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCustomers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-info border-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            إجمالي الإيرادات
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRevenue, 2) }} ج.م</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-warning border-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            المبالغ المعلقة
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($outstandingAmount, 2) }} ج.م</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- الحضور اليوم -->
    <div class="col-xl-6 col-lg-6">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clock me-2"></i>
                    الحضور اليوم
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-success">{{ $presentToday }}</h4>
                            <small class="text-muted">حاضر</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $totalEmployees - $presentToday }}</h4>
                        <small class="text-muted">غائب</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الفواتير -->
    <div class="col-xl-6 col-lg-6">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice me-2"></i>
                    حالة الفواتير
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h5 class="text-info">{{ $totalInvoices }}</h5>
                        <small class="text-muted">الإجمالي</small>
                    </div>
                    <div class="col-4">
                        <div class="border-start border-end">
                            <h5 class="text-warning">{{ $pendingInvoices }}</h5>
                            <small class="text-muted">معلقة</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <h5 class="text-danger">{{ $overdueInvoices }}</h5>
                        <small class="text-muted">متأخرة</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- آخر الفواتير -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    آخر الفواتير
                </h6>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">
                    عرض الكل
                </a>
            </div>
            <div class="card-body">
                @if($recentInvoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentInvoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                                    <td>
                                        @if($invoice->status == 'draft')
                                            <span class="badge bg-secondary">مسودة</span>
                                        @elseif($invoice->status == 'sent')
                                            <span class="badge bg-primary">مرسلة</span>
                                        @elseif($invoice->status == 'paid')
                                            <span class="badge bg-success">مدفوعة</span>
                                        @elseif($invoice->status == 'overdue')
                                            <span class="badge bg-danger">متأخرة</span>
                                        @else
                                            <span class="badge bg-warning">ملغاة</span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد فواتير حتى الآن</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- إحصائيات إضافية -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>
                    إحصائيات سريعة
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">الموظفون الجدد هذا الشهر</small>
                    <div class="d-flex justify-content-between">
                        <span>{{ $newEmployees }}</span>
                        <i class="fas fa-user-plus text-success"></i>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">الحضور اليوم</small>
                    <div class="d-flex justify-content-between">
                        <span>{{ $presentToday }}/{{ $totalEmployees }}</span>
                        <i class="fas fa-clock text-info"></i>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <small class="text-muted">الفواتير المعلقة</small>
                    <div class="d-flex justify-content-between">
                        <span>{{ $pendingInvoices }}</span>
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
