<?php

use Illuminate\Support\Facades\Route;
use Rosalana\Tracker\Http\Controllers\TrackerController;

Route::group('tracker', function () {
    Route::post('send-captured', [TrackerController::class, 'send'])->name('tracker.send-captured');
    Route::post('flush-captured', [TrackerController::class, 'flush'])->name('tracker.flush-captured');
});