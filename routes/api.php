<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RefundController as ApiRefundController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RefundActionController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Passenger API
|--------------------------------------------------------------------------
*/

Route::post('/v1/refunds', [ApiRefundController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // Public
    Route::post('/login', [AuthController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Current User
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | Refunds
        |--------------------------------------------------------------------------
        */

        Route::get('/refunds', [AdminRefundController::class, 'index']);
        Route::get('/refunds/{refund}', [AdminRefundController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | Workflow
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/refunds/{refund}/assign',
            [RefundActionController::class, 'assign']
            )->middleware('role:SUPER_ADMIN');

        Route::patch('/refunds/{refund}/approve', [RefundActionController::class, 'approve']);

        Route::patch('/refunds/{refund}/return', [RefundActionController::class, 'returnBack']);

        Route::patch('/refunds/{refund}/reject', [RefundActionController::class, 'reject']);

        Route::patch('/refunds/{refund}/cancel', [RefundActionController::class, 'cancel']);

        Route::patch('/refunds/{refund}/complete', [RefundActionController::class, 'complete']);

        /*
        |--------------------------------------------------------------------------
        | Admin Management
        |--------------------------------------------------------------------------
        */

        Route::patch('/refunds/{refund}/priority', [RefundActionController::class, 'updatePriority']);

        Route::patch('/refunds/{refund}/department', [RefundActionController::class, 'updateDepartment']);

        Route::patch('/refunds/{refund}/notes', [RefundActionController::class, 'updateNotes']);

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get('/reports/export', [ReportController::class, 'export']);
    });
});