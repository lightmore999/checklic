<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use App\Models\Organization;
use App\Models\OrgOwnerProfile;
use App\Models\Limit;
use App\Models\ReportType;
use App\Models\DelegatedLimit; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * Список организаций (для админа)
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organizations = Organization::whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with(['manager.user', 'owner.user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $view = 'admin.organizations.index';
        } else {
            // Для менеджера
            $organizations = Organization::whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['owner.user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $view = 'manager.organizations.index';
        }
        
        return view($view, compact('user', 'organizations'));
    }
    
    /**
     * Форма создания организации с владельцем
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            // Получаем менеджеров текущего админа
            $managers = Manager::where('admin_id', $user->id)
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
                
            // Для админа передаем менеджеров
            return view('organizations.create', compact('user', 'managers'));
        } else {
            // Для менеджера получаем его менеджерскую запись
            $managerRecord = Manager::where('user_id', $user->id)->first();
            
            if (!$managerRecord) {
                abort(403, 'Менеджер не найден в системе');
            }
            
            // Для менеджера не нужно выбирать менеджера - он сам менеджер
            $managers = []; // Пустой массив
            
            // Передаем manager_id в представление
            return view('organizations.create', compact('user', 'managers', 'managerRecord'));
        }
    }
    
    /**
     * Сохранение организации с владельцем (объединенный метод)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Валидация - исправлены названия полей
        $validationRules = [
            'organization.name' => 'required|string|max:255|unique:organizations,name',
            'organization.subscription_ends_at' => 'nullable|date|after:today',
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
        ];
        
        // Разные статусы для админа и менеджера
        if ($user->isAdmin()) {
            $validationRules['organization.status'] = 'required|in:active,suspended,expire';
            $validationRules['manager_id'] = [
                'nullable',
                'integer',
                Rule::exists('managers', 'id')->where('admin_id', $user->id)
            ];
        } else {
            // Для менеджера
            $validationRules['organization.status'] = 'required|in:active,inactive';
            
            // Менеджер может создавать организации только от своего имени
            $validationRules['manager_id'] = [
                'nullable',
                'integer',
                Rule::exists('managers', 'id')->where('user_id', $user->id)
            ];
        }
        
        $validated = $request->validate($validationRules);
        
        DB::beginTransaction();
        
        try {
            // 1. Определяем ID менеджера
            $managerId = $validated['manager_id'] ?? null;
            $managerUserId = null;
            
            if ($managerId) {
                // Если указан менеджер
                $manager = Manager::findOrFail($managerId);
                $managerUserId = $manager->user_id;
                
                // Проверяем права
                if ($user->isAdmin() && $manager->admin_id != $user->id) {
                    abort(403, 'Доступ запрещен');
                }
                if ($user->isManager() && $manager->user_id != $user->id) {
                    abort(403, 'Доступ запрещен');
                }
            } else {
                // Если менеджер не указан
                if ($user->isAdmin()) {
                    // Админ становится менеджером
                    $adminManager = Manager::where('user_id', $user->id)->first();
                    
                    if (!$adminManager) {
                        $adminManager = Manager::create([
                            'user_id' => $user->id,
                            'admin_id' => $user->id,
                        ]);
                    }
                    
                    $managerId = $adminManager->id;
                    $managerUserId = $user->id;
                } else {
                    // Менеджер - используем его менеджерскую запись
                    $manager = Manager::where('user_id', $user->id)->firstOrFail();
                    $managerId = $manager->id;
                    $managerUserId = $user->id;
                }
            }
            
            // 2. Создаем пользователя для владельца организации - исправлены ключи
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
                'manager_id' => $managerId,
                'subscription_ends_at' => $validated['organization']['subscription_ends_at'] ?? null,
                'status' => $validated['organization']['status'],
            ]);
            
            // 4. Создаем профиль владельца организации
            OrgOwnerProfile::create([
                'user_id' => $ownerUser->id,
                'organization_id' => $organization->id,
                'manager_id' => $managerUserId,
            ]);
            
            DB::commit();
            
            // Редирект в зависимости от роли
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Организация и владелец успешно созданы');
            } else {
                return redirect()->route('manager.dashboard')
                    ->with('success', 'Организация и владелец успешно созданы');
            }
                    
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании: ' . $e->getMessage());
        }
    }
    
    /**
     * Просмотр организации и владельца (общий для админа и менеджера)
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with(['manager.user', 'owner.user', 'members.user'])
                ->firstOrFail();
        } else {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['manager.user', 'owner.user', 'members.user'])
                ->firstOrFail();
        }
        
        // === ЛИМИТЫ ВЛАДЕЛЬЦА ОРГАНИЗАЦИИ ===
        $ownerLimits = [];
        $delegatedLimits = collect();
        $availableEmployees = collect();
        
        if ($organization->owner && $organization->owner->user) {
            $owner = $organization->owner->user;
            $today = now()->format('Y-m-d');
            
            // Получаем обычные лимиты владельца
            $limits = Limit::where('user_id', $owner->id)
                ->with(['reportType', 'delegatedVersions.user'])
                ->orderBy('date_created', 'desc')
                ->get();
            
            // Получаем делегированные лимиты владельца
            $delegatedLimits = DelegatedLimit::whereHas('limit', function($q) use ($owner) {
                    $q->where('user_id', $owner->id);
                })
                ->with(['user', 'limit.reportType'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Формируем данные для отображения
            foreach ($limits as $limit) {
                if ($limit->reportType && (!$limit->reportType->only_api || ($limit->reportType->only_api && $limit->quantity > 0))) {
                    
                    // Сумма делегированных лимитов для этого лимита
                    $delegatedAmount = $delegatedLimits->where('limit_id', $limit->id)->sum('quantity');
                    
                    // Общее выделенное = текущий остаток + использовано + делегировано
                    $totalAllocated = $limit->quantity + ($limit->used_quantity ?? 0) + $delegatedAmount;
                    
                    $ownerLimits[] = [
                        'id' => $limit->id,
                        'report_type_id' => $limit->report_type_id,
                        'report_type_name' => $limit->reportType->name ?? 'Не указан',
                        'description' => $limit->reportType->description ?? null,
                        'only_api' => $limit->reportType->only_api ?? false,
                        'quantity' => $limit->quantity,
                        'used_quantity' => $limit->used_quantity ?? 0,
                        'total_allocated' => $totalAllocated,
                        'delegated_amount' => $delegatedAmount,
                        'available_amount' => $limit->quantity,
                        'is_exhausted' => $limit->quantity <= 0,
                        'has_limit' => true,
                        'date_created' => $limit->date_created,
                    ];
                }
            }
            
            // Получаем доступных для делегирования сотрудников
            $availableEmployees = User::whereHas('orgMemberProfile', function($q) use ($organization, $owner) {
                    $q->where('organization_id', $organization->id)
                        ->where('boss_id', $owner->id)
                        ->where('is_active', true);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        return view('organizations.show', compact(
            'user', 
            'organization', 
            'routePrefix',
            'ownerLimits',
            'delegatedLimits',
            'availableEmployees'
        ));
    }
    
    /**
     * Форма редактирования организации и владельца (объединенная)
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with('owner.user')
                ->firstOrFail();
                
            // Получаем менеджеров текущего админа
            $managers = Manager::where('admin_id', $user->id)
                ->with('user')
                ->whereHas('user', function($query) {
                    $query->where('is_active', true);
                })
                ->get()
                ->map(function($manager) {
                    return [
                        'id' => $manager->id,
                        'name' => $manager->user->name,
                    ];
                });
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('owner.user')
                ->firstOrFail();
                
            $managers = [];
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        return view('organizations.edit', compact('user', 'organization', 'managers', 'routePrefix'));
    }
    
    /**
     * Обновление организации и владельца (объединенный метод)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Определяем префикс маршрута для редиректа
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with('owner.user')
                ->firstOrFail();
        } else {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('owner.user')
                ->firstOrFail();
        }
        
        $owner = $organization->owner;
        
        // Правила валидации
        $validationRules = [
            'organization.name' => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            'organization.subscription_ends_at' => 'nullable|date|after:today',
            'organization.status' => 'required|in:active,suspended,expired',
        ];
        
        // Если админ, добавляем правило для менеджера
        if ($user->isAdmin()) {
            $validationRules['organization.manager_id'] = [
                'nullable',
                'integer',
                Rule::exists('managers', 'id')->where('admin_id', $user->id)
            ];
        }
        
        // Если есть владелец, добавляем правила для редактирования
        if ($owner && $owner->user) {
            $validationRules['owner.name'] = 'required|string|max:255';
            $validationRules['owner.email'] = 'required|email|unique:users,email,' . $owner->user_id;
            $validationRules['owner.password'] = 'nullable|string|min:8|confirmed';
        }
        // Если нет владельца, добавляем правила для создания
        else {
            $validationRules['owner.name'] = 'required|string|max:255';
            $validationRules['owner.email'] = 'required|email|unique:users,email';
            $validationRules['owner.password'] = 'required|string|min:8|confirmed';
        }
        
        $validated = $request->validate($validationRules);
        
        DB::beginTransaction();
        
        try {
            // 1. Обновляем данные организации
            $organizationData = [
                'name' => $validated['organization']['name'],
                'subscription_ends_at' => $validated['organization']['subscription_ends_at'] ?? null,
                'status' => $validated['organization']['status'],
            ];
            
            if ($user->isAdmin() && isset($validated['organization']['manager_id'])) {
                $organizationData['manager_id'] = $validated['organization']['manager_id'];
            }
            
            $organization->update($organizationData);
            
            // 2. Работаем с владельцем
            if ($owner && $owner->user) {
                // Обновляем существующего владельца
                $ownerUserData = [
                    'name' => $validated['owner']['name'],
                    'email' => $validated['owner']['email'],
                ];
                
                // Если указан новый пароль
                if (!empty($validated['owner']['password'])) {
                    $ownerUserData['password'] = Hash::make($validated['owner']['password']);
                }
                
                $owner->user->update($ownerUserData);
            } else {
                // Создаем нового владельца
                $managerUserId = null;
                if ($organization->manager) {
                    $managerUserId = $organization->manager->user_id;
                }
                
                $ownerUser = User::create([
                    'name' => $validated['owner']['name'],
                    'email' => $validated['owner']['email'],
                    'password' => Hash::make($validated['owner']['password']),
                    'role' => 'org_owner',
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);
                
                OrgOwnerProfile::create([
                    'user_id' => $ownerUser->id,
                    'organization_id' => $organization->id,
                    'manager_id' => $managerUserId,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route($routePrefix . 'organization.show', $organization->id)
                ->with('success', 'Организация и владелец успешно обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при обновлении: ' . $e->getMessage());
        }
    }
    
    /**
     * Изменение статуса организации (активна/неактивна)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();
        }
        
        // Определяем доступные статусы в зависимости от роли
        if ($user->isAdmin()) {
            // Админ может менять между active/suspended/expired
            if ($organization->status === 'active') {
                $organization->status = 'suspended';
                $statusText = 'приостановлена';
            } else {
                $organization->status = 'active';
                $statusText = 'активирована';
            }
        } else {
            // Менеджер может менять только между active/inactive
            if ($organization->status === 'active') {
                $organization->status = 'inactive';
                $statusText = 'деактивирована';
            } else {
                $organization->status = 'active';
                $statusText = 'активирована';
            }
        }
        
        $organization->save();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Организация {$statusText}",
                'status' => $organization->status,
                'status_badge' => $organization->getStatusBadge(), // добавить метод в модель
            ]);
        }
        
        return back()->with('success', "Организация {$statusText}");
    }
    
    /**
     * Продление подписки организации
     */
    public function extendSubscription(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();
        }
        
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);
        
        // Метод extendSubscription должен быть определен в модели Organization
        if (method_exists($organization, 'extendSubscription')) {
            $organization->extendSubscription($validated['days']);
        } else {
            // Если метода нет, реализуем здесь
            if (!$organization->subscription_ends_at) {
                $organization->subscription_ends_at = now()->addDays($validated['days']);
            } else {
                $organization->subscription_ends_at = $organization->subscription_ends_at->addDays($validated['days']);
            }
            $organization->save();
        }
        
        // Редирект в зависимости от роли
        if ($user->isAdmin()) {
            return redirect()->route('admin.organization.show', $organization->id)
                ->with('success', "Подписка продлена на {$validated['days']} дней");
        } else {
            return redirect()->route('manager.organization.show', $organization->id)
                ->with('success', "Подписка продлена на {$validated['days']} дней");
        }
    }
    
    /**
     * Удаление организации и владельца
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with('owner.user')
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('owner.user')
                ->firstOrFail();
        }
        
        DB::beginTransaction();
        
        try {
            // Удаляем владельца
            if ($organization->owner) {
                $ownerUser = $organization->owner->user;
                $organization->owner->delete();
                $ownerUser->delete();
            }
            
            // Удаляем организацию
            $organization->delete();
            
            DB::commit();
            
            // Редирект в зависимости от роли
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Организация и владелец успешно удалены');
            } else {
                return redirect()->route('manager.dashboard')
                    ->with('success', 'Организация и владелец успешно удалены');
            }
                    
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Произошла ошибка при удалении: ' . $e->getMessage());
        }
    }
    
    /**
     * Просмотр организаций менеджера (для самого менеджера)
     */
    public function managerIndex()
    {
        $manager = Auth::user();
        
        if (!$manager->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $organizations = Organization::whereHas('manager', function($query) use ($manager) {
                $query->where('user_id', $manager->id);
            })
            ->with(['owner.user', 'members.user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('organizations.index-manager', compact('manager', 'organizations'));
    }
    
    /**
     * Просмотр организации менеджером
     */
    public function managerShow($id)
    {
        return $this->show($id); // Используем общий метод
    }
    
    /**
     * Просмотр организаций владельцем (для самого владельца)
     */
    public function ownerDashboard()
    {
        $user = Auth::user();
        
        // Проверяем, что пользователь - владелец организации
        if (!$user->isOrgOwner()) {
            abort(403, 'Только владельцы организаций могут просматривать эту страницу');
        }
        
        // Получаем профиль владельца и его организацию
        $ownerProfile = $user->orgOwnerProfile;
        
        if (!$ownerProfile) {
            abort(404, 'Профиль владельца не найден');
        }
        
        $organization = $ownerProfile->organization;
        
        if (!$organization) {
            abort(404, 'Организация не найдена');
        }
        
        // Получаем сотрудников организации
        $members = $organization->members()
            ->with('user')
            ->where('boss_id', $user->id) 
            ->paginate(10);
        
        $membersCount = $organization->members()
            ->where('boss_id', $user->id)
            ->count();
        
        $activeMembersCount = $organization->members()
            ->where('boss_id', $user->id)
            ->where('is_active', true)
            ->count();
        
        // Получаем лимиты владельца
        $ownerLimits = Limit::where('user_id', $user->id)
            ->with('reportType')
            ->orderBy('date_created', 'desc')
            ->get();
        
        // Получаем делегированные лимиты владельца
        $delegatedLimits = DelegatedLimit::whereHas('limit', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['user', 'limit.reportType'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Получаем доступных для делегирования сотрудников
        $availableEmployees = User::whereHas('orgMemberProfile', function($q) use ($organization, $user) {
                $q->where('organization_id', $organization->id)
                    ->where('boss_id', $user->id)
                    ->where('is_active', true);
            })
            ->where('is_active', true)
            ->get();
        
        return view('org-owner.dashboard', compact(
            'user', 
            'organization', 
            'members', 
            'membersCount', 
            'activeMembersCount',
            'ownerLimits',
            'delegatedLimits',
            'availableEmployees'
        ));
    }
    
    /**
     * Просмотр организаций менеджером (для самого менеджера)
     */
    public function managerEdit($id)
    {
        return $this->edit($id); // Используем общий метод
    }
    
    /**
     * Обновление организации менеджером
     */
    public function managerUpdate(Request $request, $id)
    {
        return $this->update($request, $id); // Используем общий метод
    }

}