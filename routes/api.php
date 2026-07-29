<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\RefundController as ApiRefundController;
use App\Http\Controllers\Api\AirlineController;
use App\Http\Controllers\Admin\RefundAttachmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RefundActionController;
use App\Http\Controllers\Admin\ReportController;


/*
|--------------------------------------------------------------------------
| API Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/health',
        [HealthController::class, 'index']
    );


    Route::get(
        '/airlines',
        [AirlineController::class, 'index']
    );

    
    /*
    |--------------------------------------------------------------------------
    | Passenger Refund Submission
    |--------------------------------------------------------------------------
    */


    Route::post(
        '/refunds',
        [ApiRefundController::class, 'store']
    );



    /*
    |--------------------------------------------------------------------------
    | Admin Authentication
    |--------------------------------------------------------------------------
    */


    Route::prefix('admin')->group(function () {


        Route::post(
            '/login',
            [AuthController::class, 'login']
        );



        /*
        |--------------------------------------------------------------------------
        | Protected Admin Routes
        |--------------------------------------------------------------------------
        */


        Route::middleware([
            'auth:sanctum',
            'tenant'
        ])->group(function () {



            Route::get(
                '/me',
                [AuthController::class, 'me']
            );


            Route::post(
                '/logout',
                [AuthController::class, 'logout']
            );



            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */


            Route::get(
                '/dashboard',
                [DashboardController::class, 'index']
            );



            /*
            |--------------------------------------------------------------------------
            | Refund Management
            |--------------------------------------------------------------------------
            */


            Route::get(
                '/refunds',
                [AdminRefundController::class, 'index']
            );


            Route::get(
                '/refunds/{refund}',
                [AdminRefundController::class, 'show']
            );
            
            Route::get(
                '/refunds/{refund}/attachments/{attachment}',
                [RefundAttachmentController::class, 'show']
);
            Route::get('/users', [UserManagementController::class, 'index']);
            Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole']);
            Route::patch('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive']);
            Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword']);
            Route::get('/audit-log', [UserManagementController::class, 'auditLog']);

            /*
            |--------------------------------------------------------------------------
            | Workflow Actions
            |--------------------------------------------------------------------------
            */


            Route::patch(
                '/refunds/{refund}/assign',
                [RefundActionController::class, 'assign']
            )
            ->middleware('role:SUPER_ADMIN');


            Route::patch(
                '/refunds/{refund}/approve',
                [RefundActionController::class, 'approve']
            );

            Route::patch
                ('/refunds/{refund}/tickets/{ticket}/amount', 
                [RefundActionController::class, 'updateTicketAmount']);

            Route::patch(
                '/refunds/{refund}/return',
                [RefundActionController::class, 'returnBack']
            );


            Route::patch(
                '/refunds/{refund}/reject',
                [RefundActionController::class, 'reject']
            );


            Route::patch(
                '/refunds/{refund}/cancel',
                [RefundActionController::class, 'cancel']
            );


            Route::patch(
                '/refunds/{refund}/complete',
                [RefundActionController::class, 'complete']
            );



            /*
            |--------------------------------------------------------------------------
            | Refund Administration
            |--------------------------------------------------------------------------
            */


            Route::patch(
                '/refunds/{refund}/priority',
                [RefundActionController::class, 'updatePriority']
            );


            Route::patch(
                '/refunds/{refund}/department',
                [RefundActionController::class, 'updateDepartment']
            );


            Route::patch(
                '/refunds/{refund}/notes',
                [RefundActionController::class, 'updateNotes']
            );



            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */


            Route::get(
                '/reports/export',
                [ReportController::class, 'export']
            );


        });

    });

});