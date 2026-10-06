@extends('layouts.app')

@section('title', 'كشف الرواتب الشهري')
@section('page-title', 'كشف الرواتب - ' . $month)
@include('reports._print')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">التقارير</a>
    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>طباعة</button>
@endsection

@section('content')
@php $statusLabels = ['draft' => 'مسودة', 'approved' => 'معتمد', 'paid' => 'مدفوع']; @endphp
<div class="card mb-4 no-print"><div class="card-body">
    <form method="GET" class="row g-3">
        <div class="col-md-3"><label class="form-label" for="month">الشهر</label><input type="month" id="month" name="month" class="form-control" value="{{ $month }}"></div>
        <div class="col-md-3"><label class="form-label" for="status">الحالة</label>
            <select id="status" name="status" class="form-select">
                <option value="">الكل</option>
                @foreach($statusLabels as $value => $label)<option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">عرض</button></div>
    </form>
</div></div>

<div class="card"><div class="card-body">
    @if($records->isEmpty())
        <p class="text-muted text-center my-4">لا توجد سجلات رواتب لهذا الشهر.</p>
    @else
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr><th>الموظف</th><th>القسم</th><th>الأساسي</th><th>إضافي</th><th>بدلات</th><th>الإجمالي</th><th>خصومات</th><th>ضرائب</th><th>الصافي</th><th>الحالة</th></tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->employee->full_name }}</td>
                    <td>{{ $record->employee->department }}</td>
                    <td>{{ number_format($record->basic_salary, 2) }}</td>
                    <td>{{ number_format($record->overtime_amount, 2) }}</td>
                    <td>{{ number_format($record->allowances, 2) }}</td>
                    <td>{{ number_format($record->gross_salary, 2) }}</td>
                    <td>{{ number_format($record->deductions, 2) }}</td>
                    <td>{{ number_format($record->tax_amount, 2) }}</td>
                    <td class="fw-bold">{{ number_format($record->net_salary, 2) }}</td>
                    <td>{{ $statusLabels[$record->status] ?? $record->status }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2">الإجمالي ({{ $records->count() }} موظف)</td>
                    <td>{{ number_format($totals['basic_salary'], 2) }}</td>
                    <td>{{ number_format($totals['overtime_amount'], 2) }}</td>
                    <td>{{ number_format($totals['allowances'], 2) }}</td>
                    <td>{{ number_format($totals['gross_salary'], 2) }}</td>
                    <td>{{ number_format($totals['deductions'], 2) }}</td>
                    <td>{{ number_format($totals['tax_amount'], 2) }}</td>
                    <td>{{ number_format($totals['net_salary'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div></div>
@endsection
