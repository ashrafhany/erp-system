<?php

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "عدد المنتجات: " . \App\Models\Product::count() . "\n";
echo "عدد الموظفين: " . \App\Models\Employee::count() . "\n";
echo "عدد العملاء: " . \App\Models\Customer::count() . "\n";
echo "عدد الفواتير: " . \App\Models\Invoice::count() . "\n";
echo "عدد سجلات المخزون: " . \App\Models\Inventory::count() . "\n";
