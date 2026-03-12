<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Organization;
use App\Models\Limit;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class SubscriptionController extends Controller
{

    /**
     * Список всех подписок
     */
    public function index(Request $request)
    {
        $user = Auth::user();   
        
        // Только админ и менеджер могут видеть список подписок
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $query = Subscription::with(['user', 'user.orgOwnerProfile', 'user.orgMemberProfile', 'user.orgOwnerProfile.organization', 'user.orgMemberProfile.organization']);
        
        // Получаем список организаций для фильтра
        if ($user->isAdmin()) {
            $organizations = Organization::orderBy('name')->get();
        } else {
            // Для менеджера - только его организации
            $organizations = Organization::where('manager_id', $user->id)
                ->orderBy('name')
                ->get();
        }
        
        // Получаем список всех менеджеров для фильтра (только для админа)
        if ($user->isAdmin()) {
            $managers = User::where('role', 'manager')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } else {
            $managers = collect(); // Менеджер не видит фильтр по менеджеру
        }
        
        // === НОВЫЙ ФИЛЬТР ПО МЕНЕДЖЕРУ ===
        if ($request->filled('manager_id') && $user->isAdmin()) {
            $managerId = $request->manager_id;
            
            // Получаем ID организаций, которые курирует этот менеджер
            $managerOrganizationIds = Organization::where('manager_id', $managerId)
                ->pluck('id');
            
            if ($managerOrganizationIds->isNotEmpty()) {
                // Получаем ID пользователей, связанных с этими организациями
                $managerUserIds = User::whereHas('orgOwnerProfile', function($q) use ($managerOrganizationIds) {
                        $q->whereIn('organization_id', $managerOrganizationIds);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($managerOrganizationIds) {
                        $q->whereIn('organization_id', $managerOrganizationIds);
                    })
                    ->pluck('id');
                
                $query->whereIn('user_id', $managerUserIds);
            } else {
                // Если у менеджера нет организаций, возвращаем пустой результат
                $subscriptions = collect();
                $users = collect();
                $stats = [
                    'total' => 0,
                    'active' => 0,
                    'expired' => 0,
                    'expiring_soon' => 0,
                ];
                
                return view('subscriptions.index', compact('subscriptions', 'users', 'stats', 'organizations', 'managers'));
            }
        }
        
        // Фильтр по организации
        if ($request->filled('organization_id')) {
            // Получаем ID пользователей, связанных с этой организацией
            $orgUserIds = User::whereHas('orgOwnerProfile', function($q) use ($request) {
                    $q->where('organization_id', $request->organization_id);
                })
                ->orWhereHas('orgMemberProfile', function($q) use ($request) {
                    $q->where('organization_id', $request->organization_id);
                })
                ->pluck('id');
                
            $query->whereIn('user_id', $orgUserIds);
        }
        
        // Фильтр по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // ЕДИНОЕ ПОЛЕ: Дней до/после окончания
        if ($request->filled('days_to_end') && $request->days_to_end !== '') {
            $days = (int) $request->days_to_end;
            $today = now()->startOfDay();
            
            if ($days >= 0) {
                // Положительные значения: ищем подписки, которым осталось от 0 до N дней до истечения
                $query->whereNotNull('ends_at')
                    ->whereDate('ends_at', '>=', $today->toDateString())
                    ->whereDate('ends_at', '<=', $today->copy()->addDays($days)->toDateString());
            } else {
                // Отрицательные значения: ищем подписки, которые истекли от 0 до N дней назад
                $absDays = abs($days);
                $query->whereNotNull('ends_at')
                    ->whereDate('ends_at', '>=', $today->copy()->subDays($absDays)->toDateString())
                    ->whereDate('ends_at', '<=', $today->toDateString())
                    ->where(function($q) {
                        $q->where('status', 'expired')
                        ->orWhere(function($subQ) {
                            $subQ->where('status', 'active')
                                    ->where('ends_at', '<', now());
                        });
                    });
            }
        }
        
        // Для менеджера - дополнительная фильтрация по его организациям
        if ($user->isManager()) {
            $organizationIds = Organization::where('manager_id', $user->id)->pluck('id');
            
            if ($organizationIds->isNotEmpty()) {
                $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->pluck('id');
                    
                $query->whereIn('user_id', $userIds);
            } else {
                // Если нет организаций, возвращаем пустой результат
                $subscriptions = collect();
                $users = collect();
                $stats = [
                    'total' => 0,
                    'active' => 0,
                    'expired' => 0,
                    'expiring_soon' => 0,
                ];
                
                return view('subscriptions.index', compact('subscriptions', 'users', 'stats', 'organizations', 'managers'));
            }
        }
        
        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Получаем список пользователей для фильтра
        if ($user->isAdmin()) {
            $users = User::whereIn('role', ['org_owner', 'org_member', 'manager'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        } else {
            // Для менеджера - только пользователи его организаций
            $organizationIds = Organization::where('manager_id', $user->id)->pluck('id');
            
            $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                })
                ->orWhereHas('orgMemberProfile', function($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                })
                ->pluck('id');
                
            $users = User::whereIn('id', $userIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }
        
        // Статистика (с учетом прав доступа)
        $stats = [
            'total' => $query->count(),
            'active' => (clone $query)->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
                })->count(),
            'expired' => (clone $query)->where(function($q) {
                    $q->where('status', 'expired')
                    ->orWhere(function($subQ) {
                        $subQ->where('status', 'active')
                            ->whereNotNull('ends_at')
                            ->where('ends_at', '<', now());
                    });
                })->count(),
            'expiring_soon' => (clone $query)->where('status', 'active')
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', now()->addDays(7))
                ->where('ends_at', '>', now())
                ->count(),
        ];
        
        return view('subscriptions.index', compact('subscriptions', 'users', 'stats', 'organizations', 'managers'));
    }
    
    /**
     * Просмотр подписки
     */
    public function show(Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Для менеджера проверяем доступ
        if ($user->isManager()) {
            $targetUser = $subscription->user;
            $organizationIds = Organization::where('manager_id', $user->id)->pluck('id');
            
            $hasAccess = false;
            
            if ($targetUser->isOrgOwner()) {
                $ownerProfile = $targetUser->orgOwnerProfile;
                $hasAccess = $ownerProfile && in_array($ownerProfile->organization_id, $organizationIds->toArray());
            } elseif ($targetUser->isOrgMember()) {
                $memberProfile = $targetUser->orgMemberProfile;
                $hasAccess = $memberProfile && in_array($memberProfile->organization_id, $organizationIds->toArray());
            }
            
            if (!$hasAccess) {
                abort(403, 'Доступ к этой подписке запрещен');
            }
        }
        
        $subscription->load(['user', 'user.orgOwnerProfile', 'user.orgMemberProfile']);
        
        // Получаем лимиты, связанные с этой подпиской
        $limits = $subscription->limits()->with(['reportType'])->orderBy('date_created', 'desc')->get();
        
        return view('subscriptions.show', compact('subscription', 'limits'));
    }

    /**
     * Удалить подписку (только для админа)
     */
    public function destroy(Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Только администратор может удалять подписки');
        }
        
        // Проверяем, есть ли лимиты, связанные с этой подпиской
        $limitsCount = $subscription->limits()->count();
        
        if ($limitsCount > 0) {
            return redirect()->back()
                ->with('error', "Нельзя удалить подписку, так как с ней связано {$limitsCount} лимитов. Сначала удалите лимиты.");
        }
        
        try {
            $subscription->delete();
            
            // Если есть redirect в запросе, возвращаемся туда
            if (request()->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, request()->redirect_to_organization)
                    ->with('success', 'Подписка успешно удалена');
            }
            
            return redirect()->route('subscriptions.index')
                ->with('success', 'Подписка успешно удалена');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }

    /**
     * Продлить подписку
     */
    public function extend(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1|max:365',
            'redirect_to_organization' => 'nullable|integer|exists:organizations,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $subscription->extend($request->days);
            
            // Если есть redirect_to_organization, возвращаемся к организации
            if ($request->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, $request->redirect_to_organization)
                    ->with('success', "Подписка продлена на {$request->days} дней");
            }
            
            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', "Подписка продлена на {$request->days} дней");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Активировать подписку
     */
    public function activate(Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            $subscription->activate();
            
            // Если есть redirect в запросе, возвращаемся туда
            if (request()->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, request()->redirect_to_organization)
                    ->with('success', 'Подписка активирована');
            }
            
            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Подписка активирована');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Приостановить подписку
     */
    public function suspend(Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            $subscription->suspend();
            
            // Если есть redirect в запросе, возвращаемся туда
            if (request()->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, request()->redirect_to_organization)
                    ->with('success', 'Подписка приостановлена');
            }
            
            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Подписка приостановлена');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Отменить подписку
     */
    public function cancel(Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            $subscription->cancel();
            
            // Если есть redirect в запросе, возвращаемся туда
            if (request()->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, request()->redirect_to_organization)
                    ->with('success', 'Подписка отменена');
            }
            
            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Подписка отменена');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Получить статистику по подпискам (API)
     */
    public function stats()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
                })->count(),
            'expired' => Subscription::where(function($q) {
                    $q->where('status', 'expired')
                      ->orWhere(function($subQ) {
                          $subQ->where('status', 'active')
                               ->whereNotNull('ends_at')
                               ->where('ends_at', '<', now());
                      });
                })->count(),
            'expiring_soon' => Subscription::where('status', 'active')
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', now()->addDays(7))
                ->where('ends_at', '>', now())
                ->count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Проверить статус подписки пользователя (API)
     */
    public function checkUserSubscription(User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        $activeSubscription = $user->activeSubscription();
        
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'has_active_subscription' => $activeSubscription !== null,
            'subscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'name' => $activeSubscription->name, // ДОБАВЛЕНО
                'starts_at' => $activeSubscription->starts_at?->format('Y-m-d'),
                'ends_at' => $activeSubscription->ends_at?->format('Y-m-d'),
                'status' => $activeSubscription->status,
                'remaining_days' => $activeSubscription->getRemainingDays(),
                'is_expiring_soon' => $activeSubscription->isExpiringSoon(),
            ] : null,
        ]);
    }

    /**
     * Получить подписки для владельца организации (для использования в шаблоне)
     */
    public function getOwnerSubscriptions($ownerId)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            return collect();
        }
        
        return Subscription::where('user_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить подписки пользователя (API)
     */
    public function getUserSubscriptions(User $user)
    {
        $currentUser = Auth::user();
        
        // Проверка доступа
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        try {
            // Получаем все подписки пользователя
            $subscriptions = Subscription::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($subscription) {
                    // Безопасно получаем лимиты, если связь существует
                    $limits = $subscription->limits ?? collect();
                    
                    return [
                        'id' => $subscription->id,
                        'name' => $subscription->name, // ДОБАВЛЕНО
                        'starts_at' => $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : null,
                        'ends_at' => $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : null,
                        'status' => $subscription->status,
                        'status_text' => $subscription->getStatusTextAttribute(),
                        'remaining_days' => $subscription->getRemainingDays(),
                        'is_expiring_soon' => $subscription->isExpiringSoon(),
                        'limits_count' => $limits->count(),
                        'total_limits_quantity' => $limits->sum('quantity'),
                        'total_limits_used' => $limits->sum('used_quantity'),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'subscriptions' => $subscriptions,
                'total' => $subscriptions->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Форма редактирования подписки (название и лимиты)
     */
    public function edit(Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Для менеджера проверяем доступ
        if ($user->isManager()) {
            $this->checkManagerAccess($user, $subscription);
        }

        // Загружаем существующие лимиты
        $limits = $subscription->limits()->with('reportType')->get();
        
        // Загружаем все типы отчетов
        $reportTypes = ReportType::orderBy('name')->get();
        
        // Общая сумма лимитов в подписке
        $totalLimits = $limits->sum('quantity');
        
        // Создаем структуру для передачи в представление
        $limitsData = [];
        foreach ($reportTypes as $reportType) {
            $existingLimit = $limits->firstWhere('report_type_id', $reportType->id);
            $limitsData[$reportType->id] = [
                'name' => $reportType->name,
                'only_api' => $reportType->only_api,
                'current_quantity' => $existingLimit ? $existingLimit->quantity : 0,
                'used_quantity' => $existingLimit ? $existingLimit->used_quantity : 0,
                'available' => $existingLimit ? $existingLimit->getAvailableQuantity() : 0,
                'limit_id' => $existingLimit ? $existingLimit->id : null,
            ];
        }

        return view('subscriptions.edit', compact('subscription', 'limitsData', 'totalLimits'));
    }

    /**
     * Обновление подписки (название и лимиты)
     */
    public function update(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Для менеджера проверяем доступ
        if ($user->isManager()) {
            $this->checkManagerAccess($user, $subscription);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'limits' => 'required|array',
            'limits.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Получаем существующие лимиты подписки
        $existingLimits = $subscription->limits()->get()->keyBy('report_type_id');
        
        // Собираем новые значения лимитов из формы
        $newLimits = [];
        $totalNew = 0;
        
        foreach ($request->limits as $reportTypeId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity > 0) {
                $newLimits[(int) $reportTypeId] = $quantity;
                $totalNew += $quantity;
            }
        }

        // Получаем общую сумму существующих лимитов
        $totalExisting = $existingLimits->sum('quantity');

        // Проверяем, что общая сумма не изменилась
        if (abs($totalNew - $totalExisting) > 0.01) {
            return redirect()->back()
                ->with('error', "Общая сумма лимитов должна остаться неизменной: {$totalExisting}. Получено: {$totalNew}")
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Обновляем название подписки, если оно изменилось
            if ($request->has('name') && $request->name !== $subscription->name) {
                $subscription->update(['name' => $request->name]);
            }

            // Обрабатываем каждый тип отчета
            foreach ($newLimits as $reportTypeId => $newQuantity) {
                if (isset($existingLimits[$reportTypeId])) {
                    // Обновляем существующий лимит
                    $limit = $existingLimits[$reportTypeId];
                    if ($limit->quantity != $newQuantity) {
                        // Проверяем, что новое количество не меньше использованного
                        if ($newQuantity < $limit->used_quantity) {
                            throw new \Exception("Нельзя уменьшить лимит для типа отчета '{$limit->reportType->name}' ниже использованного количества ({$limit->used_quantity})");
                        }
                        $limit->update(['quantity' => $newQuantity]);
                    }
                } else {
                    // Создаем новый лимит
                    Limit::create([
                        'subscription_id' => $subscription->id,
                        'report_type_id' => $reportTypeId,
                        'quantity' => $newQuantity,
                        'used_quantity' => 0,
                        'date_created' => now()->format('Y-m-d'),
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Удаляем лимиты, которых больше нет в форме (которые стали 0)
            foreach ($existingLimits as $reportTypeId => $limit) {
                if (!isset($newLimits[$reportTypeId]) && $limit->used_quantity == 0) {
                    // Можно удалить только если лимит не использован
                    $limit->delete();
                } elseif (!isset($newLimits[$reportTypeId]) && $limit->used_quantity > 0) {
                    throw new \Exception("Нельзя удалить лимит для типа отчета '{$limit->reportType->name}', так как он уже использован ({$limit->used_quantity} шт.)");
                }
            }

            DB::commit();

            // Если есть redirect_to_organization в запросе
            if ($request->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, $request->redirect_to_organization)
                    ->with('success', 'Подписка успешно обновлена');
            }

            return redirect()->route('subscriptions.index')
                ->with('success', 'Подписка успешно обновлена');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Проверка доступа менеджера к подписке
     */
    private function checkManagerAccess($user, $subscription)
    {
        $targetUser = $subscription->user;
        $organizationIds = Organization::where('manager_id', $user->id)->pluck('id')->toArray();
        
        $hasAccess = false;
        
        if ($targetUser->isOrgOwner()) {
            $ownerProfile = $targetUser->orgOwnerProfile;
            $hasAccess = $ownerProfile && in_array($ownerProfile->organization_id, $organizationIds);
        } elseif ($targetUser->isOrgMember()) {
            $memberProfile = $targetUser->orgMemberProfile;
            $hasAccess = $memberProfile && in_array($memberProfile->organization_id, $organizationIds);
        }
        
        if (!$hasAccess) {
            abort(403, 'Доступ к этой подписке запрещен');
        }
    }
}