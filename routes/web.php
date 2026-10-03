<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

require __DIR__ . '/Auth/login.php';
require __DIR__ . '/app.php';