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
        $organizationId = $request->get('organization_id');
        $forDelegation = $request->get('for_delegation', false); // Добавляем флаг для делегирования
        
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
            ->get(['id', 'name', 'email', 'role']); // Добавляем поле role
        
        $formatted = $users->map(function($user) use ($forDelegation) {
            $result = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // Добавляем роль в результат
                'text' => $user->name . ' (' . $user->email . ')'
            ];
            
            // Для делегирования добавляем больше информации
            if ($forDelegation) {
                $result['text'] = $user->name . ' (' . $user->email . ') - ' . $this->getRoleDisplayName($user->role);
            }
            
            return $result;
        });
        
        return response()->json($formatted);
    }
    
    /**
     * Получить отображаемое название роли
     */
    private function getRoleDisplayName($role)
    {
        $roles = [
            'admin' => 'Администратор',
            'manager' => 'Менеджер',
            'org_owner' => 'Владелец организации',
            'org_member' => 'Сотрудник',
        ];
        
        return $roles[$role] ?? $role;
    }
}