<?php

use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::get('/solicitar-acesso', function () {
        return redirect()->route('login', ['solicitar-acesso' => 1]);
    })->name('access-requests.show');

    Route::post('/solicitar-acesso', [AccessRequestController::class, 'store'])
        ->name('access-requests.store');

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
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
    });
});