<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        $allowed = ['login', 'logout', 'api']; // allow API (external) if needed

        if (in_array($path, $allowed, true) || strpos($path, 'login/') === 0 || strpos($path, 'logout/') === 0) {
            return $next($request);
        }

        if (!$request->session()->get('logged_in')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
