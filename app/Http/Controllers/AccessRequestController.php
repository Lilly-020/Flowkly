<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AccessRequestController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/RequestAccess');
    }

    public function store()
    {
        //
    }
}