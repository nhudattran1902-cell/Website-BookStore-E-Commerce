<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->hasRole('admin')) {
            if ((int) session()->get('admin_2fa_verified_user') !== (int) $user->id) {
                return redirect()->route('admin.2fa.challenge');
            }
        }

        return $next($request);
    }
}
