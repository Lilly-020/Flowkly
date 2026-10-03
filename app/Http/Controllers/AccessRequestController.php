<?php

namespace App\Http\Controllers;

use App\Enums\AccessRequestStatus;
use App\Enums\UserRole;
use App\Mail\AccessRequestApproved;
use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AccessRequestController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('AccessRequests/Index', [
            'requests' => AccessRequest::query()
                ->latest()
                ->with('reviewer:id,name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if (User::where('email', $data['email'])->exists()) {
            return back()->withErrors([
                'email' => 'Já existe uma conta com este e-mail.',
            ]);
        }

        $hasPendingRequest = AccessRequest::query()
            ->where('email', $data['email'])
            ->where('status', AccessRequestStatus::Pending)
            ->exists();

        if ($hasPendingRequest) {
            return back()->withErrors([
                'email' => 'Já existe uma solicitação em análise para este e-mail.',
            ]);
        }

        AccessRequest::create($data);

        return back()->with('success', 'Solicitação enviada com sucesso!');
    }

    public function accept(AccessRequest $accessRequest): RedirectResponse
    {
        if ($accessRequest->status !== AccessRequestStatus::Pending) {
            return back()->with('error', 'Esta solicitação já foi analisada.');
        }

        if (User::where('email', $accessRequest->email)->exists()) {
            return back()->with('error', 'Já existe uma conta com este e-mail.');
        }

        $password = Str::password(12);

        $user = User::create([
            'name' => $accessRequest->name,
            'email' => $accessRequest->email,
            'password' => $password,
            'role' => UserRole::User,
        ]);

        $accessRequest->forceFill([
            'status' => AccessRequestStatus::Accepted,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ])->save();

        Mail::to($user->email)->send(new AccessRequestApproved($user, $password));

        return back()->with('success', "Acesso liberado para {$user->name}.");
    }

    public function decline(AccessRequest $accessRequest): RedirectResponse
    {
        if ($accessRequest->status !== AccessRequestStatus::Pending) {
            return back()->with('error', 'Esta solicitação já foi analisada.');
        }

        $accessRequest->forceFill([
            'status' => AccessRequestStatus::Declined,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ])->save();

        return back()->with('success', 'Solicitação recusada.');
    }
}
