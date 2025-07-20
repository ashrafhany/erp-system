<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PayrollApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\InvoiceApiController;

// API Version 1
Route::prefix('v1')->group(function () {

    // Dashboard API
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // Employee Management API
    Route::apiResource('employees', EmployeeApiController::class);
    Route::get('employees/{employee}/attendance', [EmployeeApiController::class, 'attendance']);
    Route::get('employees/{employee}/payroll', [EmployeeApiController::class, 'payroll']);

    // Attendance Management API
    Route::apiResource('attendance', AttendanceApiController::class);
    Route::post('attendance/checkin/{employee}', [AttendanceApiController::class, 'checkIn']);
    Route::post('attendance/checkout/{employee}', [AttendanceApiController::class, 'checkOut']);
    Route::get('attendance/report/daily', [AttendanceApiController::class, 'dailyReport']);
    Route::get('attendance/report/monthly', [AttendanceApiController::class, 'monthlyReport']);

    // Payroll Management API
    Route::apiResource('payroll', PayrollApiController::class);
    Route::post('payroll/generate/{employee}', [PayrollApiController::class, 'generatePayroll']);
    Route::post('payroll/approve/{payroll}', [PayrollApiController::class, 'approve']);
    Route::get('payroll/report/monthly', [PayrollApiController::class, 'monthlyReport']);

    // Customer Management API
    Route::apiResource('customers', CustomerApiController::class);
    Route::get('customers/{customer}/invoices', [CustomerApiController::class, 'invoices']);

    // Invoice Management API
    Route::apiResource('invoices', InvoiceApiController::class);
    Route::post('invoices/{invoice}/items', [InvoiceApiController::class, 'addItem']);
    Route::delete('invoices/items/{item}', [InvoiceApiController::class, 'removeItem']);
    Route::post('invoices/{invoice}/send', [InvoiceApiController::class, 'send']);
    Route::post('invoices/{invoice}/payment', [InvoiceApiController::class, 'recordPayment']);
    Route::get('invoices/{invoice}/pdf', [InvoiceApiController::class, 'generatePdf']);

});

// Authentication routes (if needed)
Route::post('/auth/login', function (Request $request) {
    // Authentication logic here
    return response()->json(['message' => 'Login endpoint - implement as needed']);
});

Route::post('/auth/logout', function (Request $request) {
    // Logout logic here
    return response()->json(['message' => 'Logout successful']);
})->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
