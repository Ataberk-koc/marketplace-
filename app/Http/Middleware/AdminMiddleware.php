<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin yetkisi kontrolü yapan middleware
 * Sadece admin rolüne sahip kullanıcıların admin paneline erişmesini sağlar
 */
class AdminMiddleware
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

        // Kullanıcı admin değilse ana sayfaya yönlendir
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        return $next($request);
    }
}
