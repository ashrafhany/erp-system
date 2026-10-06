<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
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
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/inventory', [ProductController::class, 'adjustInventory'])->name('products.inventory');

    Route::post('attendance/checkin/{employee}', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('attendance/checkout/{employee}', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('payroll/{payroll}/print', [PayrollController::class, 'print'])->name('payroll.print');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('payroll/generate/{employee}', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
    Route::post('payroll/approve/{payroll}', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('payroll/pay/{payroll}', [PayrollController::class, 'markPaid'])->name('payroll.pay');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('invoices/{invoice}/reconcile', [InvoiceController::class, 'reconcilePayment'])->name('invoices.reconcile');

    Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('payroll', 'payroll')->name('payroll');
        Route::get('attendance', 'attendance')->name('attendance');
        Route::get('receivables', 'receivables')->name('receivables');
        Route::get('sales', 'sales')->name('sales');
    });

    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
});
