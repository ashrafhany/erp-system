@extends('layouts.app')

@section('title', 'إدارة الفواتير')
@section('page-title', 'إدارة الفواتير')

@section('page-actions')
    @can('invoices.create')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إنشاء فاتورة جديدة
    </a>
    @endcan
@endsection

@section('content')
<!-- فلاتر البحث -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="customer_id" class="form-label">العميل</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">جميع العملاء</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                    {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">الحالة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>مرسلة</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>متأخرة</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" id="date_from" name="date_from"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" id="date_to" name="date_to"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh me-2"></i>إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- بطاقات الإحصائيات -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $invoiceStats['total'] }}</h5>
                        <small>إجمالي الفواتير</small>
                    </div>
                    <i class="fas fa-file-invoice fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $invoiceStats['paid'] }}</h5>
                        <small>مدفوعة</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $invoiceStats['pending'] }}</h5>
                        <small>معلقة</small>
                    </div>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $invoiceStats['overdue'] }}</h5>
                        <small>متأخرة</small>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if($invoiceStats['inconsistent'] > 0)
    <div class="alert alert-warning">يوجد {{ $invoiceStats['inconsistent'] }} فواتير ببيانات دفع غير متسقة؛ راجع المبلغ المدفوع وحالة الفاتورة.</div>
@endif

<!-- قائمة الفواتير -->
<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-file-invoice me-2"></i>
            قائمة الفواتير
        </h6>
    </div>
    <div class="card-body">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>تاريخ الفاتورة</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>المبلغ الإجمالي</th>
                            <th>المبلغ المدفوع</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $invoice->customer->name }}</div>
                                    @if($invoice->customer->company_name)
                                        <small class="text-muted">{{ $invoice->customer->company_name }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="{{ $invoice->due_date < now() && $invoice->status != 'paid' ? 'text-danger' : '' }}">
                                    {{ $invoice->due_date->format('Y-m-d') }}
                                </span>
                            </td>
                            <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }} ج.م</td>
                            <td>
                                @if($invoice->status == 'draft')
                                    <span class="badge bg-secondary">مسودة</span>
                                @elseif($invoice->status == 'sent' && $invoice->due_date->lt(today()) && $invoice->paid_amount < $invoice->total_amount)
                                    <span class="badge bg-danger">متأخرة</span>
                                @elseif($invoice->status == 'sent')
                                    <span class="badge bg-primary">{{ $invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount ? 'مدفوعة جزئيًا' : 'مرسلة' }}</span>
                                @elseif($invoice->status == 'paid')
                                    <span class="badge bg-success">مدفوعة</span>
                                @elseif($invoice->status == 'overdue')
                                    <span class="badge bg-danger">{{ $invoice->paid_amount > 0 ? 'متأخرة ومدفوعة جزئيًا' : 'متأخرة' }}</span>
                                @else
                                    <span class="badge bg-warning">ملغاة</span>
                                @endif
                                @if($invoice->paid_amount > $invoice->total_amount || ($invoice->status === 'draft' && $invoice->paid_amount > 0) || ($invoice->status === 'paid' && $invoice->paid_amount < $invoice->total_amount))
                                    <span class="badge bg-warning text-dark">تحتاج مراجعة</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('invoices.update')
                                        <a href="{{ route('invoices.edit', $invoice) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @if($invoice->status == 'draft')
                                        @can('invoices.delete')
                                        <form action="{{ route('invoices.destroy', $invoice) }}"
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- الترقيم -->
            <div class="d-flex justify-content-center mt-4">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">لا توجد فواتير</h5>
                <p class="text-muted">ابدأ بإنشاء فاتورة جديدة</p>
                @can('invoices.create')
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إنشاء فاتورة جديدة
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
