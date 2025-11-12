<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Satıcı yetkisi kontrolü yapan middleware
 * Sadece seller rolüne sahip kullanıcıların satıcı paneline erişmesini sağlar
 */
class SellerMiddleware
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

        // Kullanıcı satıcı değilse ana sayfaya yönlendir
        if (!auth()->user()->isSeller()) {
            return redirect()->route('home')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return $next($request);
    }
}
