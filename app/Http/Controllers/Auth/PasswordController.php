<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.change-password', [
            'isRequired' => $request->user()->password_changed_at === null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Kata sandi lama tidak sesuai.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $user = $request->user();
        if (Hash::check($validated['password'], (string) $user->password)) {
            return back()->withErrors(['password' => 'Kata sandi baru harus berbeda dari kata sandi lama.']);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Kata sandi berhasil diubah.');
    }
}