<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RefundController as ApiRefundController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\RefundActionController;
use App\Http\Controllers\Api\AuthController;

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

Route::prefix('admin')
->middleware(['auth:sanctum',])
->group(function () {

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
| Authentication
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);

});
});