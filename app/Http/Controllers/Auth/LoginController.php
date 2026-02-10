<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Обработка входа
     */
    public function login(Request $request)
    {
        // Валидация
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Попытка входа
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            
            // Редирект по роли
            $user = Auth::user();
            
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            if ($user->isManager()) {
                return redirect()->route('manager.dashboard');
            }
            
            if ($user->isOrgOwner()) {
                return redirect()->route('owner.dashboard');
            }
            
            if ($user->isOrgMember()) {
                // ИСПРАВЛЕНО: правильный маршрут для сотрудников
                return redirect()->route('member.profile');
            }
            
            return redirect()->route('admin.dashboard');
        }
        
        // Если не удалось войти
        return back()->withErrors([
            'email' => 'Неверный email или пароль',
        ])->onlyInput('email');
    }
    
    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}