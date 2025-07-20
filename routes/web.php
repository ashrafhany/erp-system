<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;

// الصفحة الرئيسية - لوحة المراقبة
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// مسارات إدارة الموظفين
Route::resource('employees', EmployeeController::class);

// مسارات إدارة الحضور
Route::resource('attendance', AttendanceController::class);
Route::post('attendance/checkin/{employee}', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
Route::post('attendance/checkout/{employee}', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

// مسارات إدارة الرواتب
Route::resource('payroll', PayrollController::class);
Route::post('payroll/generate/{employee}', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
Route::post('payroll/approve/{payroll}', [PayrollController::class, 'approve'])->name('payroll.approve');

// مسارات إدارة العملاء
Route::resource('customers', CustomerController::class);

// مسارات إدارة الفواتير
Route::resource('invoices', InvoiceController::class);
Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem'])->name('invoices.items.add');
Route::delete('invoices/items/{item}', [InvoiceController::class, 'removeItem'])->name('invoices.items.remove');
Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
