<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['usuario', 'ti'])],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $type = $data['type'] === 'ti' ? UserRole::Ti : UserRole::User;

        if (! Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'E-mail ou senha inválidos.',
            ]);
        }

        if (Auth::user()->role !== $type) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Esta conta não tem acesso a este portal.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($type->homeRouteName()));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
