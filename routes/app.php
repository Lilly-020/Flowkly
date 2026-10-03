<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('/requests', function () {
        return Inertia::render('Requests/Index');
    })->name('requests.index');

    Route::get('/requests/create', function () {
        return Inertia::render('Requests/Create');
    })->name('requests.create');

    Route::get('/kanban', function () {
        return Inertia::render('Kanban');
    })->name('kanban');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');
});
