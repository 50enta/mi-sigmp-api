<?php

use App\Http\Controllers\Session\sessionController;
use Illuminate\Support\Facades\Route;

route::post('login', sessionController::class . '@login');

route::post('passwordRequest', sessionController::class . '@requestPassword');

route::post('checkToken', sessionController::class . '@checkToken')->middleware('auth:sanctum');

route::post('logout', sessionController::class . '@logout')->middleware('auth:sanctum');