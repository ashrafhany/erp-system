<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PayrollApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\AuthApiController;

// Authentication API (Public Routes)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthApiController::class, 'register']);
    Route::post('login', [AuthApiController::class, 'login']);
});

// Protected Authentication Routes
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout', [AuthApiController::class, 'logout']);
    Route::post('logout-all', [AuthApiController::class, 'logoutAll']);
    Route::post('refresh', [AuthApiController::class, 'refresh']);
    Route::get('user', [AuthApiController::class, 'user']);
    Route::put('change-password', [AuthApiController::class, 'changePassword']);
    Route::put('update-profile', [AuthApiController::class, 'updateProfile']);
    Route::get('tokens', [AuthApiController::class, 'tokens']);
    Route::delete('tokens/{tokenId}', [AuthApiController::class, 'revokeToken']);
});

// API Version 1 (Protected Routes)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Test endpoint
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working', 'timestamp' => now(), 'user' => auth()->user()]);
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

// Public API Routes (No Authentication Required)
Route::prefix('v1/public')->group(function () {
    // Public test endpoint
    Route::get('/test', function () {
        return response()->json(['message' => 'Public API is working', 'timestamp' => now()]);
    });

    // Public company info or any other public endpoints
    Route::get('/company-info', function () {
        return response()->json([
            'company_name' => 'ERP System',
            'version' => '1.0.0',
            'api_version' => 'v1'
        ]);
    });
});
