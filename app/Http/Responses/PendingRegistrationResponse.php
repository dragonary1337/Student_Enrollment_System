<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse;
use Symfony\Component\HttpFoundation\Response;

class PendingRegistrationResponse implements RegisterResponse
{
    public function toResponse($request): Response
    {
        Auth::guard(config('fortify.guard'))->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => 'Your account is awaiting administrator approval.'], 202);
        }

        return redirect()->route('login')->with('status', 'Registration submitted. Sign-in will be available after administrator approval.');
    }
}
