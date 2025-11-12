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
}
