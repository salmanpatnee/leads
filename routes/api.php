<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::post('/leads', LeadController::class)
    ->middleware(['site.api_key', 'throttle:leads']);
