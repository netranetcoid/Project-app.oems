<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWebPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->password_changed_at !== null) {
            return $next($request);
        }

        if ($request->routeIs('password.change', 'password.update', 'logout')) {
            return $next($request);
        }

        return redirect()->route('password.change')
            ->with('warning', 'Untuk keamanan, ganti kata sandi sementara sebelum membuka AppOEMS.');
    }
}