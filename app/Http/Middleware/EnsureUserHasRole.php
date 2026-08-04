<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $roles  Pipe-separated list of allowed roles, e.g. "moderator|admin"
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        $allowed = explode('|', $roles);

        if (! $user || ! in_array($user->role, $allowed, true)) {
            abort(403, "Vous n'avez pas accès à cette section.");
        }

        return $next($request);
    }
}
