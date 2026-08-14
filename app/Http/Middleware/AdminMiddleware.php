<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Educational Comment: If no admin user exists in the database, redirect to the initial setup screen!
        if (User::where('is_admin', true)->doesntExist()) {
            return redirect()->route('admin.setup');
        }

        // 2. If the user is unauthenticated, redirect to login
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // 3. If the logged in user is not an admin, return 403 Forbidden
        if (! auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
