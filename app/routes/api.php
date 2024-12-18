<?php

use App\Http\Controllers\HookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('payments/schedule', [HookController::class, 'schedule']);

Route::post('payments/sum', [HookController::class, 'sum']);
