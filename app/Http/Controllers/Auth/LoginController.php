<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kullanıcı giriş işlemlerini yöneten controller
 */
class LoginController extends Controller
{
    /**
     * Login sayfasını gösterir
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Kullanıcı girişi yapar
     */
    public function login(Request $request)
    {
        // Validasyon
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Giriş denemesi
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Hesap aktif değilse çıkış yaptır
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Hesabınız henüz aktif değil. Lütfen yönetici onayını bekleyin.',
                ]);
            }

            // Email doğrulanmamışsa doğrulama sayfasına yönlendir
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // Role göre yönlendirme
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->isSeller()) {
                return redirect()->intended(route('seller.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'Giriş bilgileri hatalı.',
        ])->onlyInput('email');
    }

    /**
     * Kullanıcı çıkışı yapar
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
