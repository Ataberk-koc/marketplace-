<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kullanıcı hesabının aktif olup olmadığını kontrol eden middleware
 * is_active = true olan kullanıcıların sistemi kullanmasını sağlar
 */
class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kullanıcı giriş yapmışsa ve hesabı aktif değilse
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Hesabınız aktif değil. Lütfen yönetici ile iletişime geçin.');
        }

        return $next($request);
    }
}
