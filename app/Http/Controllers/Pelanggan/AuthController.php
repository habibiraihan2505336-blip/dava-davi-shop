<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('pelanggan')->check()) {
            return redirect()->route('akun.index');
        }

        return view('pelanggan.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak sah.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $kunci = 'masuk-pelanggan:'.Str::lower($data['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.',
            ]);
        }

        if (! Auth::guard('pelanggan')->attempt($data, $request->boolean('ingat'))) {
            RateLimiter::hit($kunci, 60);

            throw ValidationException::withMessages(['email' => 'Email atau kata sandi tidak cocok.']);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('akun.index'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('pelanggan')->check()) {
            return redirect()->route('akun.index');
        }

        return view('pelanggan.daftar');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')],
            'no_hp' => ['required', 'string', 'regex:/^[0-9+() -]{10,20}$/'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak sah.',
            'email.unique' => 'Email ini sudah terdaftar. Coba masuk.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP hanya boleh angka, spasi, tanda +, tanda hubung, atau tanda kurung, 10 sampai 20 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Ulangi kata sandi dengan isian yang sama.',
        ]);

        $customer = Customer::create($data);
        Auth::guard('pelanggan')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('akun.index')->with('success', 'Akun dibuat. Selamat datang, '.$customer->nama.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('pelanggan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.home')->with('success', 'Anda sudah keluar dari akun.');
    }
}
