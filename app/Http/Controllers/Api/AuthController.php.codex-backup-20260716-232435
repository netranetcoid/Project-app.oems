<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Mobile mengirim identity/client; email/device_name lama tetap
        // diterima agar integrasi bertahap tidak memutus klien lama.
        $request->validate([
            'identity' => 'nullable|string',
            'email' => 'nullable|email',
            'password' => 'required',
            'client' => 'nullable|string|max:80',
            'device_name' => 'nullable|string|max:80',
        ]);

        $identity = trim((string) ($request->input('identity') ?: $request->input('email')));
        $user = User::query()
            ->where(function ($query) use ($identity): void {
                $query->where('email', $identity)->orWhere('username', $identity);
            })
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)
            || ! $user->isActiveUser() || $user->isLockedUser()) {
            throw ValidationException::withMessages([
                'identity' => ['Data login salah atau akun tidak aktif.'],
            ]);
        }

        $client = (string) ($request->input('client') ?: $request->input('device_name') ?: 'ovallhr_mobile');
        $access = $user->createToken($client, ['mobile'], now()->addMinutes(15));
        $refresh = $user->createToken($client . '_refresh', ['mobile:refresh'], now()->addDays(30));

        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $access->plainTextToken,
                'refresh_token' => $refresh->plainTextToken,
                'expires_in' => 900,
                'user' => $user,
            ],
        ]);
    }

    public function refresh(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        abort_unless($token instanceof PersonalAccessToken && $token->can('mobile:refresh'), 401);

        $user = $request->user();
        $client = str_replace('_refresh', '', (string) $token->name) ?: 'ovallhr_mobile';
        $token->delete();
        $access = $user->createToken($client, ['mobile'], now()->addMinutes(15));
        $refresh = $user->createToken($client . '_refresh', ['mobile:refresh'], now()->addDays(30));

        return response()->json(['data' => [
            'access_token' => $access->plainTextToken,
            'refresh_token' => $refresh->plainTextToken,
            'expires_in' => 900,
        ]]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}
