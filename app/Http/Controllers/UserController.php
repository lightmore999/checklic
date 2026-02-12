<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $organizationId = $request->get('organization_id'); // Получаем ID организации
        
        $query = User::where('is_active', true);
        
        // Фильтр по организации
        if ($organizationId) {
            $query->where(function($q) use ($organizationId) {
                $q->whereHas('orgOwnerProfile', function($subQ) use ($organizationId) {
                    $subQ->where('organization_id', $organizationId);
                })->orWhereHas('orgMemberProfile', function($subQ) use ($organizationId) {
                    $subQ->where('organization_id', $organizationId);
                });
            });
        }
        
        // Поиск по имени или email
        if ($search && strlen($search) > 0) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        $users = $query->limit(20)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        
        $formatted = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'text' => $user->name . ' (' . $user->email . ')'
            ];
        });
        
        return response()->json($formatted);
    }
}