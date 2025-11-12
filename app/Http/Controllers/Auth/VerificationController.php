<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/**
 * Email doğrulama işlemlerini yöneten controller
 */
class VerificationController extends Controller
{
    /**
     * Email doğrulama bildirim sayfasını gösterir
     */
    public function show()
    {
        return view('auth.verify-email');
    }

    /**
     * Email doğrulama işlemini gerçekleştirir
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('info', 'Email adresiniz zaten doğrulanmış.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $user = $request->user();

        // Role göre yönlendirme
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Email adresiniz doğrulandı!');
        } elseif ($user->isSeller()) {
            // Satıcı ise hesap aktif mi kontrol et
            if (!$user->is_active) {
                return redirect()->route('home')->with('warning', 'Email adresiniz doğrulandı. Hesabınız admin onayı bekliyor.');
            }
            return redirect()->route('seller.dashboard')->with('success', 'Email adresiniz doğrulandı!');
        }

        return redirect()->route('home')->with('success', 'Email adresiniz doğrulandı!');
    }

    /**
     * Doğrulama emailini tekrar gönderir
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Doğrulama email\'i tekrar gönderildi!');
    }
}
