<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('main');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('auth.login');
    Route::post('/login', 'login');

    Route::get('/register', 'showRegister')->name('auth.register');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout')->name('auth.logout');
});

Route::resource('posts', PostController::class);
