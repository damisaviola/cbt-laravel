<?php

use App\Http\Controllers\Admin\StudentManagerController;
use App\Http\Controllers\Api\ExamApiController;
use Illuminate\Support\Facades\Route;

// External API inbound sync for School Information System (SIS)
Route::post('/v1/students/sync', [StudentManagerController::class, 'apiSync'])->middleware('throttle:60,1');

// Authenticated exam engine endpoints (using web session)
Route::middleware(['web', 'auth', 'throttle:180,1'])->group(function () {
    Route::post('/exam/{attempt}/sync', [ExamApiController::class, 'sync']);
    Route::post('/exam/{attempt}/strike', [ExamApiController::class, 'recordStrike']);
    Route::get('/exam/{attempt}/status', [ExamApiController::class, 'status']);
    Route::post('/exam/{attempt}/unlock', [ExamApiController::class, 'unlock']);
});
