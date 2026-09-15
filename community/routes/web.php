<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('main');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.submit');

    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->name('register.submit');

    Route::post('/logout', 'logout')->name('logout');
});

Route::resource('posts', PostController::class);

Route::post('/posts/{post}/like', [PostLikeController::class, 'store'])
    ->name('posts.like');

Route::controller(CommentController::class)->group(function () {
    Route::post('/posts/{post}/comments', 'store')
        ->name('comments.store');

    Route::put('/posts/{post}/comments/{comment}', 'update')
        ->scopeBindings()
        ->name('comments.update');

    Route::delete('/posts/{post}/comments/{comment}', 'destroy')
        // scopeBindings()는 중첩된 Route Model Binding에서 부모 모델과 자식 모델의 관계까지 확인하도록 하는 기능
        // 이 comment가 이 post에 속한 댓글인가?
        ->scopeBindings()
        ->name('comments.destroy');
});
