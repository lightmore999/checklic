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
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Базовый запрос
        if ($user->isAdmin()) {
            // Для админа - все организации менеджеров этого админа
            $query = Organization::whereHas('manager', function($query) use ($user) {
                    $query->whereHas('managerProfile', function($subQuery) use ($user) {
                        $subQuery->where('admin_id', $user->id);
                    });
                })
                ->with(['manager', 'owner.user']);
        } else {
            // Для менеджера - только свои организации
            $query = Organization::where('manager_id', $user->id)
                ->with(['owner.user']);
        }
        
        // ПРИМЕНЯЕМ ФИЛЬТРЫ
        
        // 1. Поиск по названию организации
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', "%{$searchTerm}%");
            });
        }
        
        // 2. Фильтр по оставшимся дням подписки
        if ($request->filled('subscription_days')) {
            $days = (int) $request->subscription_days;
            $targetDate = now()->addDays($days);
            
            $query->where(function($q) use ($targetDate, $days) {
                // Организации, у которых подписка истекает через $days дней или меньше
                $q->whereNotNull('subscription_ends_at')
                ->where('subscription_ends_at', '<=', $targetDate);
                
                // Если $days = 0, показываем уже истекшие
                if ($days == 0) {
                    $q->orWhere('subscription_ends_at', '<', now());
                }
            });
        }
        
        // 3. Поиск по владельцу (имя или email)
        if ($request->filled('owner_search')) {
            $ownerSearch = $request->owner_search;
            $query->whereHas('owner.user', function($q) use ($ownerSearch) {
                $q->where(function($subQ) use ($ownerSearch) {
                    $subQ->where('name', 'ILIKE', "%{$ownerSearch}%")
                        ->orWhere('email', 'ILIKE', "%{$ownerSearch}%");
                });
            });
        }
        
        // 4. Фильтр по менеджеру
        if ($request->filled('manager_id')) {
            $managerId = $request->manager_id;
            
            if ($user->isAdmin()) {
                // Для админа - проверяем что менеджер принадлежит ему
                $query->whereHas('manager', function($q) use ($managerId, $user) {
                    $q->where('id', $managerId)
                    ->whereHas('managerProfile', function($subQ) use ($user) {
                        $subQ->where('admin_id', $user->id);
                    });
                });
            } else {
                // Для менеджера - только если это он сам
                if ($managerId == $user->id) {
                    $query->where('manager_id', $user->id);
                }
            }
        }
        
        // 5. Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // 6. Сортировка
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Разрешенные поля для сортировки
        $allowedSorts = ['created_at', 'name', 'status', 'subscription_ends_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Получаем организации с пагинацией
        $organizations = $query->paginate(15)->withQueryString();
        
        // Получаем список менеджеров для фильтра
        if ($user->isAdmin()) {
            $managers = User::where('role', 'manager')
                ->whereHas('managerProfile', function($q) use ($user) {
                    $q->where('admin_id', $user->id);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } else {
            $managers = collect([$user]); // Только текущий менеджер
        }
        
        // Определяем шаблон в зависимости от роли
        $view = $user->isAdmin() ? 'organizations.index' : 'manager.organizations.index';
        
        return view($view, compact('user', 'organizations', 'managers'));
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
            // Для админа - получаем менеджеров (пользователей с ролью manager, созданных этим админом)
            $managers = User::where('role', 'manager')
                ->whereHas('managerProfile', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
                
            return view('organizations.create', compact('user', 'managers'));
        } else {
            // Для менеджера не нужно выбирать менеджера - он сам менеджер
            return view('organizations.create', compact('user'));
        }
    }
    
    /**
     * Сохранение организации с владельцем
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Базовые правила валидации
        $validationRules = [
            'organization.name' => 'required|string|max:255|unique:organizations,name',
            'organization.subscription_ends_at' => 'nullable|date|after:today',
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
        ];
        
        // Статус в зависимости от роли
        if ($user->isAdmin()) {
            $validationRules['organization.status'] = 'required|in:active,suspended,expired';
            
            // ИСПРАВЛЕНО: Заменяем Rule::exists на кастомное правило
            $validationRules['manager_id'] = [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        // Проверяем, что пользователь существует, имеет роль manager
                        // и принадлежит этому админу
                        $exists = User::where('id', $value)
                            ->where('role', 'manager')
                            ->whereHas('managerProfile', function($query) use ($user) {
                                $query->where('admin_id', $user->id);
                            })
                            ->exists();
                        
                        if (!$exists) {
                            $fail('Выбранный менеджер не найден или не принадлежит вам');
                        }
                    }
                }
            ];
        } else {
            // Для менеджера - только active/inactive
            $validationRules['organization.status'] = 'required|in:active,inactive';
            // manager_id не нужен - менеджером будет текущий пользователь
        }
        
        $validated = $request->validate($validationRules);
        
        DB::beginTransaction();
        
        try {
            // 1. Определяем ID менеджера (user_id)
            if ($user->isAdmin()) {
                // Админ выбрал менеджера или NULL
                $managerUserId = $validated['manager_id'] ?? null;
            } else {
                // Менеджер - текущий пользователь
                $managerUserId = $user->id;
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
                'manager_id' => $managerUserId,
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
     * Просмотр организации и владельца
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        if ($user->isAdmin()) {
            // Для админа - организации менеджеров этого админа
            $organization = Organization::where('id', $id)
                ->whereHas('manager', function($query) use ($user) {
                    $query->whereHas('managerProfile', function($subQuery) use ($user) {
                        $subQuery->where('admin_id', $user->id);
                    });
                })
                ->with(['manager', 'owner.user', 'members.user'])
                ->firstOrFail();
        } else {
            // Для менеджера - только свои организации
            $organization = Organization::where('id', $id)
                ->where('manager_id', $user->id)
                ->with(['manager', 'owner.user', 'members.user'])
                ->firstOrFail();
        }
        
        // === ЛИМИТЫ ВЛАДЕЛЬЦА ОРГАНИЗАЦИИ ===
        $ownerLimits = [];
        $delegatedLimits = collect();
        $availableEmployees = collect();
        
        if ($organization->owner && $organization->owner->user) {
            $owner = $organization->owner->user;
            
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
                    
                    $delegatedAmount = $delegatedLimits->where('limit_id', $limit->id)->sum('quantity');
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
     * Форма редактирования организации и владельца
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
                    $query->whereHas('managerProfile', function($subQuery) use ($user) {
                        $subQuery->where('admin_id', $user->id);
                    });
                })
                ->with('owner.user')
                ->firstOrFail();
                
            // Получаем менеджеров (пользователей с ролью manager) этого админа
            $managers = User::where('role', 'manager')
                ->whereHas('managerProfile', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } else {
            // Для менеджера - только свои организации
            $organization = Organization::where('id', $id)
                ->where('manager_id', $user->id)
                ->with('owner.user')
                ->firstOrFail();
                
            $managers = [];
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        return view('organizations.edit', compact('user', 'organization', 'managers', 'routePrefix'));
    }
    
    /**
     * Обновление организации и владельца
     */
    public function update(Request $request, $id)
    {   
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $routePrefix = $user->isAdmin() ? 'admin.' : 'manager.';
        
        $organization = Organization::with('owner.user', 'manager')->findOrFail($id);
        
        // Проверка прав доступа
        if ($user->isAdmin()) {
            // Для админа - проверяем, что менеджер организации принадлежит этому админу
            if ($organization->manager) {
                $managerExists = Manager::where('user_id', $organization->manager->id)
                    ->where('admin_id', $user->id)
                    ->exists();
                    
                if (!$managerExists) {
                    abort(403, 'У вас нет прав на редактирование этой организации');
                }
            }
        } else if ($user->isManager()) {
            // Менеджер может редактировать только свои организации
            if (!$organization->manager || $organization->manager->id != $user->id) {
                abort(403, 'У вас нет прав на редактирование этой организации');
            }
        }
        
        $owner = $organization->owner;
        
        // Правила валидации
        $validationRules = [
            'organization.name' => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            'organization.subscription_ends_at' => 'nullable|date',
            'organization.status' => 'required|in:active,suspended,expired',
        ];
        
        if ($user->isAdmin()) {
            $validationRules['organization.manager_id'] = [
                'nullable',
                'integer',
                // ИСПРАВЛЕНО: Убрана ошибка с "has"
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $exists = User::where('id', $value)
                            ->where('role', 'manager')
                            ->whereHas('managerProfile', function($query) use ($user) {
                                $query->where('admin_id', $user->id);
                            })
                            ->exists();
                        
                        if (!$exists) {
                            $fail('Выбранный менеджер не найден или не принадлежит вам');
                        }
                    }
                }
            ];
        }
        
        if ($owner && $owner->user) {
            $validationRules['owner.name'] = 'required|string|max:255';
            $validationRules['owner.email'] = 'required|email|unique:users,email,' . $owner->user_id;
            $validationRules['owner.password'] = 'nullable|string|min:8|confirmed';
        } else {
            $validationRules['owner.name'] = 'required|string|max:255';
            $validationRules['owner.email'] = 'required|email|unique:users,email';
            $validationRules['owner.password'] = 'required|string|min:8|confirmed';
        }
        
        $validated = $request->validate($validationRules);
        
        DB::beginTransaction();
        
        try {
            $organizationData = [
                'name' => $validated['organization']['name'],
                'subscription_ends_at' => $validated['organization']['subscription_ends_at'] ?? null,
                'status' => $validated['organization']['status'],
            ];
            
            // Обновляем менеджера (только для админа)
            if ($user->isAdmin() && array_key_exists('manager_id', $validated['organization'])) {
                $organizationData['manager_id'] = $validated['organization']['manager_id'] ?: null;
            }
            
            $organization->update($organizationData);
            
            // Обновляем владельца
            if ($owner && $owner->user) {
                $ownerUserData = [
                    'name' => $validated['owner']['name'],
                    'email' => $validated['owner']['email'],
                ];
                
                if (!empty($validated['owner']['password'])) {
                    $ownerUserData['password'] = Hash::make($validated['owner']['password']);
                }
                
                $owner->user->update($ownerUserData);
            } else {
                // Создаем нового владельца (если его не было)
                $managerUserId = $organization->manager ? $organization->manager->id : null;
                
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
     * Изменение статуса организации
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
                    $query->whereHas('managerProfile', function($subQuery) use ($user) {
                        $subQuery->where('admin_id', $user->id);
                    });
                })
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->where('manager_id', $user->id)
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
                    $query->whereHas('managerProfile', function($subQuery) use ($user) {
                        $subQuery->where('admin_id', $user->id);
                    });
                })
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->where('manager_id', $user->id)
                ->firstOrFail();
        }
        
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);
        
        // Продлеваем подписку
        if (!$organization->subscription_ends_at) {
            $organization->subscription_ends_at = now()->addDays($validated['days']);
        } else {
            $organization->subscription_ends_at = $organization->subscription_ends_at->addDays($validated['days']);
        }
        $organization->save();
        
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
                    $query->whereHas('managerProfile', function($subQuery) use ($user) {
                        $subQuery->where('admin_id', $user->id);
                    });
                })
                ->with('owner.user')
                ->firstOrFail();
        } else {
            // Для менеджера
            $organization = Organization::where('id', $id)
                ->where('manager_id', $user->id)
                ->with('owner.user')
                ->firstOrFail();
        }
        
        DB::beginTransaction();
        
        try {
            // Удаляем владельца
            if ($organization->owner) {
                $ownerUser = $organization->owner->user;
                $organization->owner->delete();
                if ($ownerUser) {
                    $ownerUser->delete();
                }
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
        
        $organizations = Organization::where('manager_id', $manager->id)
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
        return $this->show($id);
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
        
        // Получаем менеджера организации
        $manager = null;
        if ($organization->manager) {
            $manager = $organization->manager;
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
        
        // Получаем лимиты владельца (БЕЗ map и вычисления used_quantity)
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
            'manager',
            'members', 
            'membersCount', 
            'activeMembersCount',
            'ownerLimits',
            'delegatedLimits',
            'availableEmployees'
        ));
    }
    
    /**
     * Редактирование организации менеджером
     */
    public function managerEdit($id)
    {
        return $this->edit($id);
    }
    
    /**
     * Обновление организации менеджером
     */
    public function managerUpdate(Request $request, $id)
    {
        return $this->update($request, $id);
    }

}