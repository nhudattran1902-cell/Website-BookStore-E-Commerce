<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! $user->hasRole('admin') && ! $user->hasAnyRole(...$roles))) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
