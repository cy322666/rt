<?php

use App\Http\Controllers\DocController;
use App\Http\Controllers\HookController;
use Illuminate\Support\Facades\Route;

Route::get('docs/get',  [DocController::class => 'get']);

Route::get('docs/push', [DocController::class => 'push']);
