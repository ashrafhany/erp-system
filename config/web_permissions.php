<?php

 $groups = [
    'لوحة المراقبة' => ['dashboard.view' => 'عرض لوحة المراقبة'],
    'الموظفون' => ['employees.view' => 'عرض', 'employees.create' => 'إضافة', 'employees.update' => 'تعديل', 'employees.delete' => 'حذف'],
    'الأقسام' => ['departments.view' => 'عرض', 'departments.create' => 'إضافة', 'departments.update' => 'تعديل', 'departments.delete' => 'حذف'],
    'الحضور' => ['attendance.view' => 'عرض', 'attendance.create' => 'إضافة', 'attendance.update' => 'تعديل', 'attendance.delete' => 'حذف', 'attendance.checkin' => 'تسجيل حضور', 'attendance.checkout' => 'تسجيل انصراف'],
    'الرواتب' => ['payroll.view' => 'عرض', 'payroll.create' => 'إضافة', 'payroll.update' => 'تعديل', 'payroll.delete' => 'حذف', 'payroll.generate' => 'توليد', 'payroll.approve' => 'اعتماد', 'payroll.pay' => 'تسجيل دفع'],
    'العملاء' => ['customers.view' => 'عرض', 'customers.create' => 'إضافة', 'customers.update' => 'تعديل', 'customers.delete' => 'حذف'],
    'الفواتير' => ['invoices.view' => 'عرض', 'invoices.create' => 'إضافة', 'invoices.update' => 'تعديل', 'invoices.delete' => 'حذف', 'invoices.send' => 'إرسال', 'invoices.payment' => 'تسجيل دفعة', 'invoices.reconcile' => 'تصحيح دفعة قديمة'],
    'المستخدمون' => ['users.view' => 'عرض', 'users.create' => 'إضافة', 'users.update' => 'تعديل', 'users.delete' => 'حذف'],
    'الأدوار والصلاحيات' => ['roles.view' => 'عرض', 'roles.create' => 'إضافة', 'roles.update' => 'تعديل', 'roles.delete' => 'حذف'],
];

// The helper is the source of truth: only registered CRUDs appear in roles and menu.
$registered = array_keys(getMenuData());
$groups = array_filter($groups, function ($permissions) use ($registered) {
    $first = array_key_first($permissions);
    return in_array(explode('.', $first)[0], $registered, true);
});

foreach ($registered as $resource) {
    $alreadyDefined = collect($groups)->contains(fn ($permissions) => str_starts_with(array_key_first($permissions), $resource . '.'));
    if (! $alreadyDefined) {
        $groups[$resource] = [
            "$resource.view" => 'عرض',
            "$resource.create" => 'إضافة',
            "$resource.update" => 'تعديل',
            "$resource.delete" => 'حذف',
        ];
    }
}

return $groups;
