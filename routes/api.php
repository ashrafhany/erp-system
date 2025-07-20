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

    // Test endpoint
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working', 'timestamp' => now()]);
    });

    // Dashboard API
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
    Route::get('/dashboard/monthly-overview', [DashboardApiController::class, 'monthlyOverview']);

    // Employee Management API
    Route::apiResource('employees', EmployeeApiController::class);
    Route::get('employees/{employee}/attendance', [EmployeeApiController::class, 'attendance']);
    Route::get('employees/{employee}/payroll', [EmployeeApiController::class, 'payroll']);

    // Attendance Management API
    Route::apiResource('attendance', AttendanceApiController::class);
    Route::post('attendance/employees/{employee}/checkin', [AttendanceApiController::class, 'checkIn']);
    Route::post('attendance/employees/{employee}/checkout', [AttendanceApiController::class, 'checkOut']);
    Route::get('attendance/reports/daily', [AttendanceApiController::class, 'dailyReport']);
    Route::get('attendance/reports/monthly', [AttendanceApiController::class, 'monthlyReport']);

    // Payroll Management API
    Route::apiResource('payroll', PayrollApiController::class);
    Route::post('payroll/employees/{employee}/generate', [PayrollApiController::class, 'generateAutomatic']);
    Route::post('payroll/{payroll}/approve', [PayrollApiController::class, 'approve']);
    Route::post('payroll/{payroll}/mark-paid', [PayrollApiController::class, 'markPaid']);

    // Customer Management API
    Route::apiResource('customers', CustomerApiController::class);
    Route::get('customers/{customer}/invoices', [CustomerApiController::class, 'invoices']);
    Route::get('customers/{customer}/balance', [CustomerApiController::class, 'balance']);

    // Invoice Management API
    Route::apiResource('invoices', InvoiceApiController::class);
    Route::post('invoices/{invoice}/items', [InvoiceApiController::class, 'addItems']);
    Route::post('invoices/{invoice}/payments', [InvoiceApiController::class, 'addPayment']);
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
