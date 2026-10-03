<?php

use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/requests', [TicketController::class, 'index'])
        ->name('requests.index');
});

Route::middleware(['auth', 'role:user,ti'])->group(function () {
    Route::get('/requests/create', [TicketController::class, 'create'])
        ->name('requests.create');

    Route::post('/requests', [TicketController::class, 'store'])
        ->name('requests.store');
});

Route::middleware('auth')->group(function () {
    // Authorization (TI can always manage; the owning user only while
    // the ticket is still "Aberto") is enforced by TicketPolicy.
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])
        ->name('tickets.update');

    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
        ->name('tickets.destroy');

    Route::get('/settings', [SettingsController::class, 'show'])
        ->name('settings');

    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password');
});

Route::middleware(['auth', 'role:ti'])->group(function () {
    Route::get('/solicitacoes', [AccessRequestController::class, 'index'])
        ->name('access-requests.index');

    Route::post('/solicitacoes/{accessRequest}/aceitar', [AccessRequestController::class, 'accept'])
        ->name('access-requests.accept');

    Route::post('/solicitacoes/{accessRequest}/recusar', [AccessRequestController::class, 'decline'])
        ->name('access-requests.decline');

    Route::get('/kanban', [KanbanController::class, 'index'])
        ->name('kanban');

    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
        ->name('tickets.status');

    Route::get('/usuarios', [UserController::class, 'index'])
        ->name('users.index');

    Route::post('/usuarios/{user}/redefinir-senha', [UserController::class, 'resetPassword'])
        ->name('users.reset-password');
});
