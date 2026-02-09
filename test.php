<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use App\Models\Organization;
use App\Models\OrgOwnerProfile;
use App\Models\OrgMemberProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Главная панель администратора
     * Показывает всех пользователей с акцентом на менеджеров
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // === СТАТИСТИКА ===
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'managers_count' => User::where('role', 'manager')->count(),
            'managers_active' => User::where('role', 'manager')->where('is_active', true)->count(),
            'total_organizations' => Organization::whereHas('manager', function($query) use ($user) {
                // Организации, чьи менеджеры принадлежат текущему админу
                $query->where('admin_id', $user->id);
            })->count(),
            'active_organizations' => Organization::where('status', 'active')
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })->count(),
            'pending_organizations' => Organization::where('status', 'pending')
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })->count(),
        ];
        
        // === ВСЕ МЕНЕДЖЕРЫ (главная таблица) ===
        $managers = User::where('role', 'manager')
            ->with('managerProfile.admin')
            ->orderBy('created_at', 'desc')
            ->get(); 
        
        // === ОРГАНИЗАЦИИ ===
        $organizations = Organization::whereHas('manager', function($query) use ($user) {
                $query->where('admin_id', $user->id);
            })
            ->with(['manager.user', 'owner.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'user', 
            'stats', 
            'managers',
            'organizations'
        ));
    }
    
    /**
     * Форма создания менеджера
     */
    public function createManager()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('admin.managers.create', compact('user'));
    }
    
    /**
     * Сохранение нового менеджера
     */
    public function storeManager(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // 1. Создаем пользователя с ролью manager
        $managerUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'manager',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        
        // 2. Создаем профиль менеджера с ссылкой на админа
        Manager::create([
            'user_id' => $managerUser->id,
            'admin_id' => $admin->id,
        ]);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Менеджер успешно создан');
    }
    
    /**
     * Просмотр профиля менеджера (пока заглушка)
     */
    public function showManager($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $manager = User::with('managerProfile.admin')
            ->where('id', $id)
            ->where('role', 'manager')
            ->firstOrFail();
        
        // Пока просто показываем информацию
        return view('admin.managers.show', compact('user', 'manager'));
    }
    
    /**
     * Активация/деактивация пользователя
     */
    public function toggleUserStatus(Request $request, $id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $user = User::findOrFail($id);
        
        // Нельзя изменить статус самому себе
        if ($user->id === $admin->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Нельзя изменить статус самому себе'], 403);
            }
            return back()->with('error', 'Нельзя изменить статус самому себе');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        $action = $user->is_active ? 'активирован' : 'деактивирован';
        $message = "Пользователь {$user->name} успешно {$action}";
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $user->is_active,
                'status_badge' => $user->is_active 
                    ? '<span class="badge bg-success">Активен</span>' 
                    : '<span class="badge bg-danger">Неактивен</span>',
                'button_title' => $user->is_active ? 'Деактивировать' : 'Активировать',
                'button_class' => $user->is_active ? 'btn-warning' : 'btn-success',
                'button_icon' => $user->is_active ? 'toggle-on' : 'toggle-off',
            ]);
        }
        
        return back()->with('success', $message);
    }

    public function editManager($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->firstOrFail();
        
        return view('admin.managers.edit', compact('user', 'manager'));
    }

    /**
     * Обновление данных менеджера
     */
    public function updateManager(Request $request, $id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->firstOrFail();
        
        // Правила валидации
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $manager->id,
        ];
        
        // Если меняем пароль
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        
        $validated = $request->validate($rules);
        
        // Обновляем данные
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }
        
        $manager->update($updateData);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Данные менеджера обновлены');
    }
    
    /**
     * Удаление пользователя (осторожно!)
     */
    public function deleteUser($id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $user = User::findOrFail($id);
        
        // Нельзя удалить самого себя
        if ($user->id === $admin->id) {
            return back()->with('error', 'Нельзя удалить самого себя');
        }
        
        // Нельзя удалить админов (для безопасности)
        if ($user->isAdmin()) {
            return back()->with('error', 'Нельзя удалить администратора');
        }
        
        $user->delete();
        
        return back()->with('success', "Пользователь {$user->name} удален");
    }
    /**
     * Форма создания организации с главой организации
     */
    public function createOrganization()
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Получаем менеджеров текущего админа
        $managers = Manager::where('admin_id', $admin->id)
            ->with('user')
            ->whereHas('user', function($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->map(function($manager) {
                return [
                    'id' => $manager->id,
                    'user_id' => $manager->user_id,
                    'name' => $manager->user->name,
                    'email' => $manager->user->email
                ];
            });
        
        return view('admin.organizations.create', compact('admin', 'managers'));
    }
    
    /**
     * Сохранение организации с главой организации
     */
     public function storeOrganization(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Валидация
        $validated = $request->validate([
            'organization.name' => 'required|string|max:255|unique:organizations,name',
            'organization.subscription_ends_at' => 'nullable|date|after:today',
            'organization.status' => 'required|in:active,inactive,pending',
            
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
            
            'manager_id' => [
                'nullable',
                'integer',
                Rule::exists('managers', 'id')->where('admin_id', $admin->id)
            ],
        ]);
        
        DB::beginTransaction();
        
        try {
            // 1. Определяем ID менеджера
            $managerId = $validated['manager_id'] ?? null;
            $managerUserId = null;
            
            if ($managerId) {
                // Если указан менеджер, берем его user_id
                $manager = Manager::findOrFail($managerId);
                $managerUserId = $manager->user_id;
            } else {
                // Если менеджер не указан, текущий админ становится менеджером
                // Но сначала нужно проверить, есть ли у админа запись в таблице managers
                $adminManager = Manager::where('user_id', $admin->id)->first();
                
                if (!$adminManager) {
                    $adminManager = Manager::create([
                        'user_id' => $admin->id,
                        'admin_id' => $admin->id,
                    ]);
                }
                
                $managerId = $adminManager->id;
                $managerUserId = $admin->id;
            }
            
            // 2. Создаем пользователя для владельца организации
            $ownerUser = User::create([
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
                'password' => Hash::make($validated['user']['password']),
                'role' => 'org_owner',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            // 3. Создаем организацию
            $organization = Organization::create([
                'name' => $validated['organization']['name'],
                'manager_id' => $managerId, // id из таблицы managers
                'subscription_ends_at' => $validated['organization']['subscription_ends_at'] ?? null,
                'status' => $validated['organization']['status'],
            ]);
            
            // 4. Создаем профиль владельца организации
            $ownerProfile = OrgOwnerProfile::create([
                'user_id' => $ownerUser->id,
                'organization_id' => $organization->id,
                'manager_id' => $managerUserId, // user_id менеджера
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Организация и владелец успешно созданы');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании: ' . $e->getMessage());
        }
    }
    
    /**
     * Список организаций
     */
    public function organizationsList()
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Получаем все организации через менеджеров текущего админа
        $organizations = Organization::whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->with(['manager.user', 'owner.user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.organizations.list', compact('admin', 'organizations'));
    }
    
    /**
     * Просмотр организации
     */
    public function showOrganization($id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $organization = Organization::where('id', $id)
            ->whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->with([
                'manager.user', 
                'owner.user',
                'members.user' // Добавляем сотрудников
            ])
            ->firstOrFail();
        
        return view('admin.organizations.show', compact('admin', 'organization'));
    }

    /**
     * Форма добавления сотрудника в организацию
     */
    public function createOrgMember($organizationId)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что организация принадлежит менеджеру текущего админа
        $organization = Organization::where('id', $organizationId)
            ->whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        return view('admin.org-members.create', compact('admin', 'organization'));
    }

    /**
     * Сохранение сотрудника организации
     */
    public function storeOrgMember(Request $request, $organizationId)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что организация принадлежит менеджеру текущего админа
        $organization = Organization::where('id', $organizationId)
            ->whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        // Валидация
        $validated = $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
        ]);
        
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
            
            // 2. Получаем владельца организации
            $owner = $organization->owner;
            
            // 3. Создаем профиль сотрудника
            OrgMemberProfile::create([
                'user_id' => $memberUser->id,
                'organization_id' => $organization->id,
                'boss_id' => $owner ? $owner->user_id : null,
                'manager_id' => $organization->manager->user_id,
                'is_active' => true,
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.organization.show', $organization->id)
                ->with('success', 'Сотрудник успешно добавлен в организацию');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании сотрудника: ' . $e->getMessage());
        }
    }

    /**
     * Просмотр профиля сотрудника
     */
    public function showOrgMember($organizationId, $memberId)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что организация принадлежит менеджеру текущего админа
        $organization = Organization::where('id', $organizationId)
            ->whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        // Получаем сотрудника
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with(['user', 'boss', 'manager'])
            ->firstOrFail();
        
        return view('admin.org-members.show', compact('admin', 'organization', 'member'));
    }

    public function toggleOrgMemberStatus(Request $request, $organizationId, $memberId)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что организация принадлежит менеджеру текущего админа
        $organization = Organization::where('id', $organizationId)
            ->whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $member->is_active = !$member->is_active;
        $member->save();
        
        return redirect()->route('admin.org-members.show', [$organization->id, $member->id])
            ->with('success', 'Статус сотрудника изменен');
    }

    /**
     * Удаление сотрудника
     */
    public function deleteOrgMember(Request $request, $organizationId, $memberId)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что организация принадлежит менеджеру текущего админа
        $organization = Organization::where('id', $organizationId)
            ->whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $user = $member->user;
        
        DB::beginTransaction();
        
        try {
            $member->delete();
            $user->delete();
            
            DB::commit();
            
            return redirect()->route('admin.organization.show', $organization->id)
                ->with('success', 'Сотрудник успешно удален');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.org-members.show', [$organization->id, $member->id])
                ->with('error', 'Произошла ошибка при удалении: ' . $e->getMessage());
        }
    }
    
}