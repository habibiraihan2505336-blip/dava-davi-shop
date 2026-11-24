<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplikasi berjalan di belakang Cloudflare Tunnel (HTTPS diputus di
        // Cloudflare), jadi header proxy dipercaya supaya tautan dan gambar
        // yang dibuat Laravel memakai https, bukan http.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO);

        // Tamu yang membuka /admin diarahkan ke halaman masuk pengelola,
        // bukan ke rute "login" bawaan yang tidak dipakai di aplikasi ini.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('admin.login');
            }

            if ($request->is('akun', 'akun/*')) {
                return route('pelanggan.login');
            }

            return route('shop.home');
        });

        // Pengguna yang sudah masuk dan membuka halaman masuk diarahkan ke
        // halaman yang sesuai, bukan ke beranda toko.
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('admin.dashboard');
            }

            if ($request->is('masuk', 'masuk/*')) {
                return route('akun.index');
            }

            return route('shop.home');
        });

        // Notifikasi pembayaran datang dari server Midtrans, bukan dari form
        // aplikasi, jadi tidak membawa token CSRF.
        $middleware->validateCsrfTokens(except: ['pembayaran/notifikasi']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
