@extends('layouts.app')

@section('title', 'ملخص المبيعات')
@section('page-title', 'ملخص المبيعات')
@include('reports._print')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">التقارير</a>
    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>طباعة</button>
@endsection

@section('content')
<div class="card mb-4 no-print"><div class="card-body">
    <form method="GET" class="row g-3">
        <div class="col-md-3"><label class="form-label" for="from">من</label><input type="date" id="from" name="from" class="form-control" value="{{ $from }}"></div>
        <div class="col-md-3"><label class="form-label" for="to">إلى</label><input type="date" id="to" name="to" class="form-control" value="{{ $to }}"></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">عرض</button></div>
    </form>
</div></div>

<p class="text-muted">الفترة: {{ $from }} إلى {{ $to }} (لا تشمل المسودات والملغاة)</p>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">عدد الفواتير</div><h4 class="mb-0">{{ $totals['count'] }}</h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">إجمالي الفواتير</div><h4 class="mb-0">{{ number_format($totals['total'], 2) }}</h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">المدفوع</div><h4 class="mb-0 text-success">{{ number_format($totals['paid'], 2) }}</h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">المتبقي</div><h4 class="mb-0 text-danger">{{ number_format($totals['remaining'], 2) }}</h4></div></div></div>
</div>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light"><tr><th>الفاتورة</th><th>التاريخ</th><th>العميل</th><th>المجموع الفرعي</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th><th>المدفوع</th></tr></thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td>{{ $invoice->customer->name }}</td>
                    <td>{{ number_format($invoice->subtotal, 2) }}</td>
                    <td>{{ number_format($invoice->discount_amount, 2) }}</td>
                    <td>{{ number_format($invoice->tax_amount, 2) }}</td>
                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                    <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">لا توجد فواتير في هذه الفترة.</td></tr>
                @endforelse
            </tbody>
            @if($invoices->isNotEmpty())
            <tfoot class="table-light fw-bold"><tr>
                <td colspan="3">الإجمالي</td>
                <td>{{ number_format($totals['subtotal'], 2) }}</td>
                <td>{{ number_format($totals['discount'], 2) }}</td>
                <td>{{ number_format($totals['tax'], 2) }}</td>
                <td>{{ number_format($totals['total'], 2) }}</td>
                <td>{{ number_format($totals['paid'], 2) }}</td>
            </tr></tfoot>
            @endif
        </table>
    </div>
</div></div>
@endsection
