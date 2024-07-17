<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;

Route::get('/login', [GoogleController::class, 'login']);
Route::get('/callback', [GoogleController::class, 'callback']);
Route::post('/logout', [GoogleController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/events', [GoogleController::class, 'apiEvents']);

