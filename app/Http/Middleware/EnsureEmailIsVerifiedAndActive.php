<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Email doğrulaması ve hesap aktifliği kontrolü yapan middleware
 * Hem email doğrulanmış hem de hesabı aktif olan kullanıcıların işlem yapmasını sağlar
 */
class EnsureEmailIsVerifiedAndActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kullanıcı giriş yapmamışsa login sayfasına yönlendir
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Lütfen giriş yapınız.');
        }

        $user = auth()->user();

        // Email doğrulanmamışsa doğrulama sayfasına yönlendir
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('warning', 'Lütfen email adresinizi doğrulayın.');
        }

        // Hesap aktif değilse çıkış yaptır
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Hesabınız henüz aktif değil. Lütfen yönetici onayını bekleyin.');
        }

        return $next($request);
    }
}
