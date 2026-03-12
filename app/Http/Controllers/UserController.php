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
        $roles = $request->get('roles'); // Добавляем фильтр по ролям
        
        $query = User::where('is_active', true);
        
        // Фильтр по ролям (если передан)
        if ($roles && is_array($roles) && count($roles) > 0) {
            $query->whereIn('role', $roles);
        }
        
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
        
        // Загружаем пользователей с профилями для получения информации об организации
        $users = $query->with(['orgOwnerProfile.organization', 'orgMemberProfile.organization'])
            ->limit(20)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
        
        $formatted = $users->map(function($user) use ($forDelegation) {
            // Определяем организацию пользователя
            $organizationName = null;
            $organizationId = null;
            
            if ($user->isOrgOwner() && $user->orgOwnerProfile && $user->orgOwnerProfile->organization) {
                $organizationName = $user->orgOwnerProfile->organization->name;
                $organizationId = $user->orgOwnerProfile->organization_id;
            } elseif ($user->isOrgMember() && $user->orgMemberProfile && $user->orgMemberProfile->organization) {
                $organizationName = $user->orgMemberProfile->organization->name;
                $organizationId = $user->orgMemberProfile->organization_id;
            }
            
            // Базовая информация о пользователе
            $result = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_display' => $this->getRoleDisplayName($user->role),
                'text' => $user->name . ' (' . $user->email . ')',
                'organization' => $organizationName,
                'organization_id' => $organizationId,
                'has_active_subscriptions' => $user->hasActiveSubscription(),
            ];
            
            // Добавляем профили для дополнительной информации
            if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                $result['org_owner_profile'] = [
                    'organization_name' => $organizationName,
                    'organization_id' => $organizationId
                ];
            } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                $result['org_member_profile'] = [
                    'organization_name' => $organizationName,
                    'organization_id' => $organizationId
                ];
            }
            
            // Для делегирования добавляем больше информации в text
            if ($forDelegation) {
                $roleText = $this->getRoleDisplayName($user->role);
                $orgText = $organizationName ? " - {$organizationName}" : '';
                $result['text'] = $user->name . ' (' . $user->email . ') - ' . $roleText . $orgText;
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
            'org_member' => 'Сотрудник организации',
        ];
        
        return $roles[$role] ?? $role;
    }
}