<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index']);

Route::resource('admin/users', UserController::class);
Route::post('admin/users/{user}/warn', [UserController::class, 'warn'])->name('users.warn');
Route::post('admin/users/{user}/block', [UserController::class, 'block'])->name('users.block');
Route::post('admin/users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');
