<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin kullanıcı yönetimi controller
 */
class UserController extends Controller
{
    /**
     * Kullanıcı listesini gösterir
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Role filtresi
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Durum filtresi
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Arama
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Kullanıcı durumunu aktif/pasif yapar
     */
    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Kullanıcı {$status} yapıldı!");
    }

    /**
     * Kullanıcıyı siler (soft delete)
     */
    public function destroy(User $user)
    {
        // Admin kendini silemez
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendi hesabınızı silemezsiniz!');
        }

        $user->delete();

        return back()->with('success', 'Kullanıcı silindi!');
    }

    /**
     * Yeni kullanıcı oluşturma formu
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Yeni kullanıcı kaydeder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,seller,user',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(), // Admin tarafından oluşturulan kullanıcılar otomatik doğrulanmış sayılır
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı oluşturuldu!');
    }

    /**
     * Kullanıcı düzenleme formu
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Kullanıcıyı günceller
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,seller,user',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        // Eğer şifre girilmişse güncelle
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı güncellendi!');
    }
}
