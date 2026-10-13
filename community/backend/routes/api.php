<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PostLikeController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::get('/me', 'me');
});

Route::controller(PostController::class)->group(function () {
    Route::get('/posts', 'index');
    Route::get('/posts/{post}', 'show');
    Route::post('/posts', 'store');
    Route::match(['put', 'patch'], '/posts/{post}', 'update');
    Route::delete('/posts/{post}', 'destroy');
});

Route::controller(CommentController::class)->group(function () {
    Route::get('/posts/{post}/comments', 'index');
    Route::post('/posts/{post}/comments', 'store');
    Route::match(['put', 'patch'], '/comments/{comment}', 'update');
    Route::delete('/comments/{comment}', 'destroy');
});

Route::controller(PostLikeController::class)->group(function () {
    Route::post('/posts/{post}/like', 'toggle');
});

Route::controller(AttachmentController::class)->group(function () {
    Route::post('/attachments/upload', 'upload');
});
