@extends('layouts.app')

@section('title', 'مديونيات العملاء')
@section('page-title', 'مديونيات العملاء')
@include('reports._print')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">التقارير</a>
    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>طباعة</button>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card"><div class="card-body"><div class="text-muted">إجمالي المستحق</div><h4 class="mb-0">{{ number_format($totalRemaining, 2) }} ج.م</h4></div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-body"><div class="text-muted">منها متأخر</div><h4 class="mb-0 text-danger">{{ number_format($totalOverdue, 2) }} ج.م</h4></div></div></div>
</div>

@forelse($groups as $group)
<div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between mb-2">
        <h6 class="mb-0">{{ $group['customer']->name }} <small class="text-muted">{{ $group['customer']->customer_code }}</small></h6>
        <strong>{{ number_format($group['remaining'], 2) }} ج.م</strong>
    </div>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>الفاتورة</th><th>الاستحقاق</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th></tr></thead>
        <tbody>
            @foreach($group['invoices'] as $invoice)
            <tr class="{{ $invoice->status === 'overdue' || $invoice->due_date->lt(today()) ? 'table-danger' : '' }}">
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                <td>{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div></div>
@empty
<div class="card"><div class="card-body text-center text-muted my-4">لا توجد مديونيات مستحقة.</div></div>
@endforelse
@endsection
