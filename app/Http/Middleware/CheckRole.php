<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)  // Здесь ...$roles
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Проверяем, есть ли у пользователя хотя бы одна из требуемых ролей
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            abort(403, 'Доступ запрещен. Требуемая роль: ' . implode(', ', $roles));
        }
        
        return $next($request);
    }
}