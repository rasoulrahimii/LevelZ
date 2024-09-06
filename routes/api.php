<?php

declare(strict_types=1);

use App\Http\Controllers\RegisterMobileController;
use App\Http\Controllers\RegisterOnDeviceController;
use App\Http\Controllers\RegisterVerifyController;
use App\Http\Controllers\SetPinController;
use Illuminate\Support\Facades\Route;

// maximum of 60 requests per minute
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/users/register/mobile', RegisterMobileController::class)->name('users.register.mobile');
    Route::post('/users/register/verify', RegisterVerifyController::class)->name('users.register.verify');
    Route::post('/users/register/device', RegisterOnDeviceController::class)->name('users.register.device');
    Route::post('/users/set-pin', SetPinController::class)->name('users.set.pin');
});
