<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->latest()
                ->get(['id', 'name', 'email', 'role', 'created_at']),
        ]);
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $password = Str::password(12);

        $user->update(['password' => $password]);

        return back()->with('generatedPassword', [
            'email' => $user->email,
            'password' => $password,
        ]);
    }
}
