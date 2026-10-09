<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isStaffMember() || ! $user->hasPermission($permission)) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
