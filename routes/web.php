<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/callback', [GoogleController::class, 'callback'])->name('callback');
Route::get('/api/events', [GoogleController::class, 'apiEvents']);
Route::get('/api/login', [GoogleController::class, 'login']);
Route::post('/api/logout', [GoogleController::class, 'logout']);
