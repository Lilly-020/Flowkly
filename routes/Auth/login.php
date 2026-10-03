<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::get('/login/usuario', function () {
        return Inertia::render('Auth/LoginForm', ['type' => 'usuario']);
    })->name('login.usuario');

    Route::get('/login/ti', function () {
        return Inertia::render('Auth/LoginForm', ['type' => 'ti']);
    })->name('login.ti');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::middleware('role:ti')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
    });
});