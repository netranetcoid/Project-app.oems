<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks employee API features while an initial or reset password is still
 * active. Only the dedicated password-change and logout routes are outside
 * this middleware, so a valid token cannot bypass first-login onboarding.
 */
class EnsureMobilePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->password_changed_at === null) {
            return response()->json([
                'message' => 'Kata sandi awal wajib diganti sebelum memakai OvallHR.',
                'code' => 'PASSWORD_CHANGE_REQUIRED',
            ], 428);
        }

        return $next($request);
    }
}
