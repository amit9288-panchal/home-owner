<?php

use App\Http\Controllers\Api\v1\CsvUploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth.token')->group(function () {
    Route::post('/upload', [CsvUploadController::class, 'upload']);
    Route::get('/people', [CsvUploadController::class, 'index']);
});

