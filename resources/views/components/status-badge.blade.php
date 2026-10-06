@props(['status', 'type'])

@php
    // الحالات المعروفة لكل نوع: [النص، اللون]
    $active = ['active' => ['نشط', 'success'], 'inactive' => ['غير نشط', 'secondary']];
    $map = [
        'employee' => ['active' => ['نشط', 'success'], 'inactive' => ['غير نشط', 'warning'], 'terminated' => ['منتهي الخدمة', 'danger']],
        'customer' => $active,
        'product' => $active,
        'attendance' => ['present' => ['حاضر', 'success'], 'absent' => ['غائب', 'danger'], 'late' => ['متأخر', 'warning'], 'half_day' => ['نصف يوم', 'info']],
        'payroll' => ['draft' => ['مسودة', 'secondary'], 'approved' => ['معتمد', 'warning'], 'paid' => ['مدفوع', 'success']],
        'invoice' => ['draft' => ['مسودة', 'secondary'], 'sent' => ['مرسلة', 'primary'], 'paid' => ['مدفوعة', 'success'], 'overdue' => ['متأخرة', 'danger'], 'cancelled' => ['ملغاة', 'dark']],
    ];
    [$label, $color] = $map[$type][$status] ?? [$status, 'secondary'];
@endphp

<span {{ $attributes->merge(['class' => "badge badge-dot bg-{$color}"]) }}>{{ $label }}</span>
