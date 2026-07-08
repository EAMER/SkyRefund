<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RefundController as ApiRefundController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RefundActionController;

/*
|--------------------------------------------------------------------------
| Passenger API
|--------------------------------------------------------------------------
*/

Route::post('/v1/refunds', [ApiRefundController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | View Refunds
    |--------------------------------------------------------------------------
    */

    Route::get('/refunds', [AdminRefundController::class, 'index']);
    Route::get('/refunds/{refund}', [AdminRefundController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Workflow Actions
    |--------------------------------------------------------------------------
    */

    Route::patch('/refunds/{refund}/assign', [RefundActionController::class, 'assign']);

    Route::patch('/refunds/{refund}/review', [RefundActionController::class, 'review']);

    Route::patch('/refunds/{refund}/approve', [RefundActionController::class, 'approve']);

    Route::patch('/refunds/{refund}/reject', [RefundActionController::class, 'reject']);

    Route::patch('/refunds/{refund}/request-documents', [RefundActionController::class, 'requestDocuments']);

    Route::patch('/refunds/{refund}/documents-received', [RefundActionController::class, 'documentsReceived']);

    Route::patch('/refunds/{refund}/mark-paid', [RefundActionController::class, 'markPaid']);

    /*
    |--------------------------------------------------------------------------
    | Admin Management
    |--------------------------------------------------------------------------
    */

    Route::patch('/refunds/{refund}/priority', [RefundActionController::class, 'updatePriority']);

    Route::patch('/refunds/{refund}/department', [RefundActionController::class, 'updateDepartment']);

    Route::patch('/refunds/{refund}/notes', [RefundActionController::class, 'updateNotes']);
});