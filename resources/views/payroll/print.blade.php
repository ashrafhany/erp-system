@extends('layouts.print')

@section('title', 'كشف راتب ' . $payroll->employee->full_name . ' - ' . $payroll->payroll_month)
@section('back-url', route('payroll.show', $payroll))

@php
    $statusLabels = ['draft' => 'مسودة', 'approved' => 'معتمد', 'paid' => 'مدفوع'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="mb-1">{{ config('app.name', 'نظام ERP') }}</h2>
        <div class="text-muted">كشف راتب</div>
    </div>
    <div class="text-end">
        <div><strong>الشهر:</strong> {{ $payroll->payroll_month }}</div>
        <div><strong>الحالة:</strong> {{ $statusLabels[$payroll->status] ?? $payroll->status }}</div>
        @if($payroll->payment_date)<div><strong>تاريخ الدفع:</strong> {{ $payroll->payment_date->format('Y-m-d') }}</div>@endif
    </div>
</div>

<div class="mb-4">
    <div><strong>الموظف:</strong> {{ $payroll->employee->full_name }}</div>
    <div><strong>رقم الموظف:</strong> {{ $payroll->employee->employee_id }}</div>
    <div><strong>القسم:</strong> {{ $payroll->employee->department }}</div>
    <div><strong>الوظيفة:</strong> {{ $payroll->employee->position }}</div>
</div>

<table class="table table-bordered">
    <tbody>
        <tr><td>الراتب الأساسي</td><td class="text-end">{{ number_format($payroll->basic_salary, 2) }}</td></tr>
        <tr><td>العمل الإضافي ({{ $payroll->overtime_hours }} ساعة × {{ number_format($payroll->overtime_rate, 2) }})</td><td class="text-end">{{ number_format($payroll->overtime_amount, 2) }}</td></tr>
        <tr><td>البدلات</td><td class="text-end">{{ number_format($payroll->allowances, 2) }}</td></tr>
        <tr class="table-light fw-bold"><td>إجمالي الدخل</td><td class="text-end">{{ number_format($payroll->gross_salary, 2) }}</td></tr>
        <tr><td>الخصومات</td><td class="text-end">-{{ number_format($payroll->deductions, 2) }}</td></tr>
        <tr><td>الضرائب</td><td class="text-end">-{{ number_format($payroll->tax_amount, 2) }}</td></tr>
        <tr class="table-light fw-bold fs-5"><td>صافي الراتب</td><td class="text-end">{{ number_format($payroll->net_salary, 2) }} ج.م</td></tr>
    </tbody>
</table>

@if($payroll->notes)
<div class="mt-3"><strong>ملاحظات:</strong> {{ $payroll->notes }}</div>
@endif

<div class="row mt-5 pt-4">
    <div class="col-6 text-center">توقيع المحاسب<br><br>__________________</div>
    <div class="col-6 text-center">توقيع الموظف<br><br>__________________</div>
</div>
@endsection
