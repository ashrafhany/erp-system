@extends('layouts.app')

@section('title', 'التقارير')
@section('page-title', 'التقارير')

@section('content')
@php
    $reports = [
        ['route' => 'reports.payroll', 'icon' => 'fa-money-bill-wave', 'title' => 'كشف الرواتب الشهري', 'desc' => 'رواتب الموظفين لشهر محدد مع الإجماليات.', 'perm' => 'payroll.view'],
        ['route' => 'reports.attendance', 'icon' => 'fa-clock', 'title' => 'تقرير الحضور الشهري', 'desc' => 'أيام الحضور والتأخير والغياب وساعات العمل لكل موظف.', 'perm' => 'attendance.view'],
        ['route' => 'reports.receivables', 'icon' => 'fa-hand-holding-usd', 'title' => 'مديونيات العملاء', 'desc' => 'الفواتير غير المسددة بالكامل مجمعة حسب العميل.', 'perm' => 'invoices.view'],
        ['route' => 'reports.sales', 'icon' => 'fa-chart-line', 'title' => 'ملخص المبيعات', 'desc' => 'إجمالي الفواتير والمدفوع والمتبقي خلال فترة.', 'perm' => 'invoices.view'],
    ];
@endphp
<div class="row g-4">
    @foreach($reports as $report)
        @can($report['perm'])
        <div class="col-md-6">
            <a href="{{ route($report['route']) }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="text-primary"><i class="fas {{ $report['icon'] }} me-2"></i>{{ $report['title'] }}</h5>
                        <p class="text-muted mb-0">{{ $report['desc'] }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endcan
    @endforeach
</div>
@endsection
