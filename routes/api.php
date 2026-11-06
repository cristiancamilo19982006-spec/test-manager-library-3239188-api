<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\MemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('books', BookController::class)->only(['index', 'store', 'show']);
    Route::apiResource('members', MemberController::class)->only(['index', 'store', 'show']);
    Route::apiResource('loans', LoanController::class)->only(['index', 'store', 'show']);
});