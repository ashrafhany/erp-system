<?php

if (! function_exists('getMenuData')) {
    /** Add or comment out a CRUD here to control both the sidebar and role form. */
    function getMenuData(): array
    {
        return [
            'dashboard' => 'fas fa-tachometer-alt',
            'employees' => 'fas fa-users',
            'departments' => 'fas fa-sitemap',
            'attendance' => 'fas fa-clock',
            'payroll' => 'fas fa-money-bill-wave',
            'customers' => 'fas fa-user-tie',
            'invoices' => 'fas fa-file-invoice',
            'products' => 'fas fa-boxes',
            'reports' => 'fas fa-chart-bar',
            'users' => 'fas fa-user-cog',
            'roles' => 'fas fa-user-shield',
        ];
    }
}
