<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Kullanıcı kayıt işlemlerini yöneten controller
 */
class RegisterController extends Controller
{
    /**
     * Kayıt sayfasını gösterir
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Yeni kullanıcı kaydı oluşturur
     */
    public function register(Request $request)
    {
        // Validasyon
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:customer,seller'],
        ]);

        // Kullanıcı oluştur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->role === 'customer' ? true : false, // Müşteriler otomatik aktif, satıcılar admin onayı bekler
        ]);

        // Email doğrulama olayını tetikle
        event(new Registered($user));

        // Kullanıcıyı giriş yaptır
        auth()->login($user);

        // Email doğrulama sayfasına yönlendir
        return redirect()->route('verification.notice')
            ->with('success', 'Kayıt başarılı! Lütfen email adresinizi doğrulayın.');
    }
}
