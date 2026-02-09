<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrgMemberProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrgMemberController extends Controller
{
    /**
     * Профиль сотрудника (самого себя)
     */
    public function profile()
    {
        $user = Auth::user();
        
        if (!$user->isOrgMember()) {
            abort(403, 'Доступ запрещен');
        }
        
        $memberProfile = $user->orgMemberProfile;
        $organization = $memberProfile->organization;
        
        return view('org-members.profile', compact('user', 'memberProfile', 'organization'));
    }
    
    /**
     * Форма редактирования профиля сотрудника (самого себя)
     */
    public function editProfile()
    {
        $user = Auth::user();
        
        if (!$user->isOrgMember()) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('org-members.edit-profile', compact('user'));
    }
    
    /**
     * Обновление профиля сотрудника (самого себя)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isOrgMember()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $validated['password'] = Hash::make($request->password);
        }
        
        $user->update($validated);
        
        return redirect()->route('member.profile')
            ->with('success', 'Профиль успешно обновлен');
    }
    
    /**
     * Форма создания сотрудника (для админа/менеджера)
     */
    public function create($organizationId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();
        }
        
        // Получаем всех возможных начальников (владельца + менеджеров)
        $potentialBosses = collect();
        
        // Владелец организации
        if ($organization->owner && $organization->owner->user) {
            $potentialBosses->push([
                'id' => $organization->owner->user_id,
                'name' => $organization->owner->user->name . ' (Владелец)',
            ]);
        }
        
        // Ответственный менеджер
        if ($organization->manager && $organization->manager->user) {
            $potentialBosses->push([
                'id' => $organization->manager->user_id,
                'name' => $organization->manager->user->name . ' (Менеджер)',
            ]);
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        return view('org-members.create', compact('user', 'organization', 'potentialBosses', 'routePrefix'));
    }
    
    /**
     * Сохранение сотрудника (для админа/менеджера)
     */
    public function store(Request $request, $organizationId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with('owner')
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('owner')
                ->firstOrFail();
        }
        
        // Валидация
        $validated = $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'boss_id' => 'nullable|exists:users,id',
        ]);
        
        // Проверяем, что boss_id принадлежит этой организации
        if ($validated['boss_id']) {
            // Проверяем, что выбранный начальник - это либо владелец, либо менеджер
            $validBoss = false;
            
            if ($organization->owner && $organization->owner->user_id == $validated['boss_id']) {
                $validBoss = true;
            }
            
            if ($organization->manager && $organization->manager->user_id == $validated['boss_id']) {
                $validBoss = true;
            }
            
            if (!$validBoss) {
                return back()
                    ->withInput()
                    ->with('error', 'Выбранный начальник не принадлежит этой организации');
            }
        }
        
        DB::beginTransaction();
        
        try {
            // 1. Создаем пользователя для сотрудника
            $memberUser = User::create([
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
                'password' => Hash::make($validated['user']['password']),
                'role' => 'org_member',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            // 2. Создаем профиль сотрудника
            $memberProfile = OrgMemberProfile::create([
                'user_id' => $memberUser->id,
                'organization_id' => $organization->id,
                'boss_id' => $validated['boss_id'] ?? ($organization->owner ? $organization->owner->user_id : null),
                'manager_id' => $organization->manager->user_id,
                'position' => $validated['position'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
            ]);
            
            DB::commit();
            
            // Определяем префикс маршрута для редиректа
            $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
            
            return redirect()->route($routePrefix . 'organization.show', $organization->id)
                ->with('success', 'Сотрудник успешно добавлен в организацию');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании сотрудника: ' . $e->getMessage());
        }
    }
    
    /**
     * Просмотр профиля сотрудника (для админа/менеджера/владельца)
     */
    public function show($organizationId, $memberId)
    {
        $currentUser = Auth::user();
        
        // Проверяем доступ
        if (!$currentUser->isAdmin() && !$currentUser->isManager() && !$currentUser->isOrgOwner()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Получаем организацию с проверкой прав
        if ($currentUser->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($currentUser) {
                    $query->where('admin_id', $currentUser->id);
                })
                ->firstOrFail();
        } elseif ($currentUser->isManager()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id);
                })
                ->firstOrFail();
        } else {
            // Для владельца организации
            $organization = Organization::where('id', $organizationId)
                ->whereHas('owner', function($query) use ($currentUser) {
                    $query->where('user_id', $currentUser->id);
                })
                ->firstOrFail();
        }
        
        // Получаем сотрудника
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with(['user', 'boss', 'manager'])
            ->firstOrFail();
        
        // Определяем префикс маршрута
        if ($currentUser->isAdmin()) {
            $routePrefix = 'admin.';
        } elseif ($currentUser->isManager()) {
            $routePrefix = 'manager.';
        } else {
            $routePrefix = 'owner.';
        }
        
        return view('org-members.show', compact('currentUser', 'organization', 'member', 'routePrefix'));
    }
    
    /**
     * Форма редактирования сотрудника (для админа/менеджера)
     */
    public function edit($organizationId, $memberId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with(['owner', 'manager'])
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['owner', 'manager'])
                ->firstOrFail();
        }
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        // Получаем всех возможных начальников
        $potentialBosses = collect();
        
        if ($organization->owner && $organization->owner->user) {
            $potentialBosses->push([
                'id' => $organization->owner->user_id,
                'name' => $organization->owner->user->name . ' (Владелец)',
            ]);
        }
        
        if ($organization->manager && $organization->manager->user) {
            $potentialBosses->push([
                'id' => $organization->manager->user_id,
                'name' => $organization->manager->user->name . ' (Менеджер)',
            ]);
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        return view('org-members.edit', compact('user', 'organization', 'member', 'potentialBosses', 'routePrefix'));
    }
    
    /**
     * Обновление сотрудника (для админа/менеджера)
     */
    public function update(Request $request, $organizationId, $memberId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with(['owner', 'manager'])
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['owner', 'manager'])
                ->firstOrFail();
        }
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        // Валидация
        $validated = $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email,' . $member->user_id,
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'boss_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);
        
        // Проверяем boss_id
        if ($validated['boss_id']) {
            $validBoss = false;
            
            if ($organization->owner && $organization->owner->user_id == $validated['boss_id']) {
                $validBoss = true;
            }
            
            if ($organization->manager && $organization->manager->user_id == $validated['boss_id']) {
                $validBoss = true;
            }
            
            if (!$validBoss) {
                return back()
                    ->withInput()
                    ->with('error', 'Выбранный начальник не принадлежит этой организации');
            }
        }
        
        DB::beginTransaction();
        
        try {
            // Обновляем пользователя
            $member->user->update([
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
            ]);
            
            // Обновляем профиль сотрудника
            $member->update([
                'position' => $validated['position'] ?? $member->position,
                'phone' => $validated['phone'] ?? $member->phone,
                'boss_id' => $validated['boss_id'] ?? $member->boss_id,
                'is_active' => $validated['is_active'] ?? $member->is_active,
            ]);
            
            DB::commit();
            
            $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
            
            return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
                ->with('success', 'Данные сотрудника обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при обновлении: ' . $e->getMessage());
        }
    }
    
    /**
     * Изменение пароля сотрудника (для админа/менеджера)
     */
    public function changePassword(Request $request, $organizationId, $memberId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем доступ к организации
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->firstOrFail();
        } else {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();
        }
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $member->user->update([
            'password' => Hash::make($validated['password']),
        ]);
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
            ->with('success', 'Пароль сотрудника изменен');
    }
    
    /**
     * Изменение статуса сотрудника (для админа/менеджера)
     */
    public function toggleStatus(Request $request, $organizationId, $memberId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->firstOrFail();
        } else {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();
        }
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $member->is_active = !$member->is_active;
        $member->save();
        
        $status = $member->is_active ? 'активирован' : 'деактивирован';
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Сотрудник {$status}",
                'is_active' => $member->is_active,
            ]);
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
            ->with('success', "Сотрудник {$status}");
    }
    
    /**
     * Удаление сотрудника (для админа/менеджера)
     */
    public function destroy($organizationId, $memberId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->firstOrFail();
        } else {
            $organization = Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();
        }
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        $memberUser = $member->user;
        
        DB::beginTransaction();
        
        try {
            $member->delete();
            $memberUser->delete();
            
            DB::commit();
            
            $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
            
            return redirect()->route($routePrefix . 'organization.show', $organization->id)
                ->with('success', 'Сотрудник успешно удален');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
            
            return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
                ->with('error', 'Произошла ошибка при удалении: ' . $e->getMessage());
        }
    }
}