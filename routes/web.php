<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebLoginController::class, 'create'])->name('login');
    Route::post('/login', [WebLoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebLoginController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('attendance', AttendanceController::class);
    Route::resource('payroll', PayrollController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('invoices', InvoiceController::class);

    Route::post('attendance/checkin/{employee}', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('attendance/checkout/{employee}', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::post('payroll/generate/{employee}', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
    Route::post('payroll/approve/{payroll}', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('payroll/pay/{payroll}', [PayrollController::class, 'markPaid'])->name('payroll.pay');
    Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem'])->name('invoices.items.add');
    Route::delete('invoices/items/{item}', [InvoiceController::class, 'removeItem'])->name('invoices.items.remove');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('invoices/{invoice}/reconcile', [InvoiceController::class, 'reconcilePayment'])->name('invoices.reconcile');

    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
});
