<?php

use App\Http\Controllers\Passenger\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    return view('admin.index');
});

Route::get('/frontend', function () {
    return view('frontend.index');
});

Route::get('/portal', [PortalController::class, 'show']);
Route::post('/portal/{refund}/upload', [PortalController::class, 'upload'])->name('passenger.upload');
Route::get('/portal/{refund}/receipt', [PortalController::class, 'receipt'])->name('passenger.receipt');
