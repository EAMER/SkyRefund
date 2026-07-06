<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RefundController;

Route::post('/v1/refunds', [RefundController::class, 'store']);