@extends('layouts.app')

@section('title', 'تقرير الحضور الشهري')
@section('page-title', 'تقرير الحضور - ' . $month)
@include('reports._print')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">التقارير</a>
    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>طباعة</button>
@endsection

@section('content')
<div class="card mb-4 no-print"><div class="card-body">
    <form method="GET" class="row g-3">
        <div class="col-md-3"><label class="form-label" for="month">الشهر</label><input type="month" id="month" name="month" class="form-control" value="{{ $month }}"></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">عرض</button></div>
    </form>
</div></div>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr><th>الموظف</th><th>القسم</th><th>حضور</th><th>تأخير</th><th>نصف يوم</th><th>غياب</th><th>ساعات العمل</th></tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                @php $row = $stats->get($employee->id); @endphp
                <tr>
                    <td>{{ $employee->full_name }}</td>
                    <td>{{ $employee->department }}</td>
                    <td>{{ (int) ($row->present_days ?? 0) }}</td>
                    <td>{{ (int) ($row->late_days ?? 0) }}</td>
                    <td>{{ (int) ($row->half_days ?? 0) }}</td>
                    <td>{{ (int) ($row->absent_days ?? 0) }}</td>
                    <td>{{ number_format($row->hours ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">لا يوجد موظفون نشطون.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div></div>
@endsection
