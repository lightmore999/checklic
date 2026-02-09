<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use App\Models\Organization;
use App\Models\OrgOwnerProfile;
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
        
        // Определяем префикс маршрута
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        if ($user->isAdmin()) {
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with(['manager.user', 'owner.user', 'members.user'])
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['manager.user', 'owner.user', 'members.user'])
                ->firstOrFail();
        }
        
        // === ЛИМИТЫ ВЛАДЕЛЬЦА ОРГАНИЗАЦИИ ===
        $ownerLimits = [];
        
        if ($organization->owner && $organization->owner->user) {
            $owner = $organization->owner->user;
            
            // Проверяем, существует ли модель ReportType
            if (class_exists('App\Models\ReportType')) {
                // Получаем ВСЕ типы отчетов
                $reportTypes = \App\Models\ReportType::all();
                
                foreach ($reportTypes as $reportType) {
                    // Получаем лимит владельца для этого типа отчета на сегодня
                    $today = now()->format('Y-m-d');
                    $limit = \App\Models\Limit::where('user_id', $owner->id)
                        ->where('report_type_id', $reportType->id)
                        ->where('date_created', $today)
                        ->first();
                    
                    // Логика отображения:
                    // 1. Если only_api = false, ВСЕГДА показываем (даже если лимит = 0)
                    // 2. Если only_api = true, показываем ТОЛЬКО если есть лимит
                    if (!$reportType->only_api || ($reportType->only_api && $limit !== null)) {
                        $quantity = $limit ? $limit->quantity : 0;
                        
                        $ownerLimits[] = [
                            'report_type_id' => $reportType->id,
                            'report_type_name' => $reportType->name,
                            'description' => $reportType->description,
                            'only_api' => $reportType->only_api,
                            'quantity' => $quantity,
                            'is_exhausted' => $quantity <= 0,
                            'has_limit' => $limit !== null,
                        ];
                    }
                }
                
                // Сортируем лимиты
                usort($ownerLimits, function($a, $b) {
                    if ($a['only_api'] !== $b['only_api']) {
                        return $a['only_api'] ? 1 : -1;
                    }
                    return strcmp($a['report_type_name'], $b['report_type_name']);
                });
            }
        }
        
        return view('organizations.show', compact(
            'user', 
            'organization', 
            'routePrefix',
            'ownerLimits'
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
        $owner = Auth::user();
        
        if (!$owner->isOrgOwner()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Получаем организацию владельца
        $organization = $owner->orgOwnerProfile->organization;
        
        // Получаем сотрудников организации
        $members = $organization->members()->with('user')->orderBy('created_at', 'desc')->take(5)->get();
        
        // Статистика
        $data = [
            'organization' => $organization,
            'members' => $members,
            'membersCount' => $organization->members()->count(),
            'activeMembersCount' => $organization->members()->where('is_active', true)->count(),
            'reportsCount' => 0,
            'licensesCount' => 0,
        ];
        
        return view('org-owner.dashboard', $data);
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