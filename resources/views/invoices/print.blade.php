@extends('layouts.print')

@section('title', 'فاتورة ' . $invoice->invoice_number)
@section('back-url', route('invoices.show', $invoice))

@php
    $statusLabels = ['draft' => 'مسودة', 'sent' => 'مرسلة', 'paid' => 'مدفوعة', 'overdue' => 'متأخرة', 'cancelled' => 'ملغاة'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="mb-1">{{ config('app.name', 'نظام ERP') }}</h2>
        <div class="text-muted">فاتورة</div>
    </div>
    <div class="text-end">
        <div><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</div>
        <div><strong>التاريخ:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</div>
        <div><strong>الاستحقاق:</strong> {{ $invoice->due_date->format('Y-m-d') }}</div>
        <div><strong>الحالة:</strong> {{ $statusLabels[$invoice->status] ?? $invoice->status }}</div>
    </div>
</div>

<div class="mb-4">
    <div class="text-muted">فاتورة إلى</div>
    <div class="fw-bold">{{ $invoice->customer->name }}</div>
    @if($invoice->customer->company_name)<div>{{ $invoice->customer->company_name }}</div>@endif
    @if($invoice->customer->address)<div>{{ $invoice->customer->address }}</div>@endif
    @if($invoice->customer->phone)<div>هاتف: {{ $invoice->customer->phone }}</div>@endif
    @if($invoice->customer->tax_number)<div>الرقم الضريبي: {{ $invoice->customer->tax_number }}</div>@endif
</div>

<table class="table table-bordered">
    <thead class="table-light">
        <tr><th>#</th><th>الوصف</th><th class="text-center">الكمية</th><th class="text-end">السعر</th><th class="text-end">الإجمالي</th></tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->description }}</td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
            <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><td colspan="4" class="text-end">المجموع الفرعي</td><td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
        @if($invoice->discount_amount > 0)
        <tr><td colspan="4" class="text-end">الخصم</td><td class="text-end">-{{ number_format($invoice->discount_amount, 2) }}</td></tr>
        @endif
        @if($invoice->tax_amount > 0)
        <tr><td colspan="4" class="text-end">الضريبة</td><td class="text-end">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
        @endif
        <tr class="fw-bold"><td colspan="4" class="text-end">الإجمالي</td><td class="text-end">{{ number_format($invoice->total_amount, 2) }} ج.م</td></tr>
        <tr><td colspan="4" class="text-end">المدفوع</td><td class="text-end">{{ number_format($invoice->paid_amount, 2) }}</td></tr>
        <tr class="fw-bold"><td colspan="4" class="text-end">المتبقي</td><td class="text-end">{{ number_format(max($invoice->remaining_amount, 0), 2) }} ج.م</td></tr>
    </tfoot>
</table>

@if($invoice->notes)
<div class="mt-3"><strong>ملاحظات:</strong> {{ $invoice->notes }}</div>
@endif
@endsection
