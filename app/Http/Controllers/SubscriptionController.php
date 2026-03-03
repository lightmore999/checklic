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
        
        $query = Subscription::with(['user', 'user.orgOwnerProfile', 'user.orgMemberProfile']);
        
        // Получаем список организаций для фильтра
        if ($user->isAdmin()) {
            $organizations = Organization::orderBy('name')->get();
        } else {
            // Для менеджера - только его организации
            $organizations = Organization::where('manager_id', $user->id)
                ->orderBy('name')
                ->get();
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
        
        // Фильтр по истекающим
        if ($request->filled('expiring_soon')) {
            $days = (int) $request->expiring_soon;
            $query->where('status', 'active')
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', now()->addDays($days))
                ->where('ends_at', '>', now());
        }
        
        // Фильтр по истекшим
        if ($request->filled('expired')) {
            $query->where(function($q) {
                $q->where('status', 'expired')
                ->orWhere(function($subQ) {
                    $subQ->where('status', 'active')
                        ->whereNotNull('ends_at')
                        ->where('ends_at', '<', now());
                });
            });
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
                $stats = [];
                
                return view('subscriptions.index', compact('subscriptions', 'users', 'stats', 'organizations'));
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
            'total' => $query->count(), // Используем отфильтрованный запрос для подсчета
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
        
        return view('subscriptions.index', compact('subscriptions', 'users', 'stats', 'organizations'));
    }

    /**
     * Форма создания подписки
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Получаем все типы отчетов
        $reportTypes = ReportType::orderBy('name')->get();
        
        // Получаем организации для фильтра
        if ($user->isAdmin()) {
            $organizations = Organization::orderBy('name')->get();
        } elseif ($user->isManager()) {
            $organizations = Organization::where('manager_id', $user->id)
                ->orderBy('name')
                ->get();
        } else {
            $organizations = collect();
        }
        
        return view('subscriptions.create', compact('user', 'reportTypes', 'organizations'));
    }

    /**
     * Сохранить новую подписку
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'status' => 'required|in:active,suspended,expired,cancelled,pending',
            'redirect_to_organization' => 'nullable|integer|exists:organizations,id',
            'report_types' => 'nullable|array',
            'report_types.*' => 'exists:report_types,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // ИСПРАВЛЕНО: убираем проверку на наличие активной подписки
        // Теперь можно создавать несколько активных подписок
        
        DB::beginTransaction();
        
        try {
            // 1. Создаем подписку
            $subscription = Subscription::create([
                'user_id' => $request->user_id,
                'starts_at' => $request->starts_at ? Carbon::parse($request->starts_at) : now(),
                'ends_at' => $request->ends_at ? Carbon::parse($request->ends_at) : null,
                'status' => $request->status,
            ]);
            
            // 2. Создаем лимиты
            $createdLimits = [];
            if ($request->has('report_types') && is_array($request->report_types) && count($request->report_types) > 0) {
                foreach ($request->report_types as $reportTypeId) {
                    $quantity = isset($request->quantities[$reportTypeId]) 
                        ? (int)$request->quantities[$reportTypeId] 
                        : 0;
                    
                    if ($quantity > 0) {
                        $limit = Limit::create([
                            'subscription_id' => $subscription->id,
                            'report_type_id' => $reportTypeId,
                            'quantity' => $quantity,
                            'used_quantity' => 0,
                            'date_created' => now()->format('Y-m-d'),
                            'created_by' => auth()->id(),
                        ]);
                        
                        $createdLimits[] = $limit;
                    }
                }
            }
            
            DB::commit();
            
            // Убираем dd() после отладки
            // dd([
            //     'subscription' => $subscription->toArray(),
            //     'limits_created_count' => count($createdLimits),
            //     'limits' => array_map(function($limit) {
            //         return $limit->toArray();
            //     }, $createdLimits),
            //     'report_types_from_form' => $request->report_types,
            //     'quantities_from_form' => $request->quantities,
            // ]);
            
            $message = 'Подписка успешно создана';
            if (count($createdLimits) > 0) {
                $message .= ' с ' . count($createdLimits) . ' лимитами';
            }
            
            // Если есть redirect_to_organization, возвращаемся к организации
            if ($request->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, $request->redirect_to_organization)
                    ->with('success', $message);
            }
            
            return redirect()->route('subscriptions.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
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
     * Форма редактирования подписки
     */
    public function edit(Subscription $subscription)
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
        
        // Получаем доступных пользователей
        if ($user->isAdmin()) {
            $users = User::whereIn('role', ['org_owner', 'org_member', 'manager'])
                ->where('is_active', true)
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
                ->where('is_active', true)
                ->pluck('id');
                
            $users = User::whereIn('id', $userIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }
        
        return view('subscriptions.edit', compact('subscription', 'users'));
    }

    /**
     * Обновить подписку
     */
    public function update(Request $request, Subscription $subscription)
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
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'status' => 'required|in:active,suspended,expired,cancelled,pending',
            'redirect_to_organization' => 'nullable|integer|exists:organizations,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Если меняем статус на active, проверяем нет ли другой активной подписки
        if ($request->status == 'active' && $subscription->status != 'active') {
            $targetUser = User::find($request->user_id);
            $activeSubscription = $targetUser->activeSubscription();
            
            if ($activeSubscription && $activeSubscription->id != $subscription->id) {
                return redirect()->back()
                    ->with('error', 'У пользователя уже есть другая активная подписка')
                    ->withInput();
            }
        }
        
        try {
            $subscription->update([
                'user_id' => $request->user_id,
                'starts_at' => $request->starts_at ? Carbon::parse($request->starts_at) : $subscription->starts_at,
                'ends_at' => $request->ends_at ? Carbon::parse($request->ends_at) : null,
                'status' => $request->status,
            ]);
            
            // Если есть redirect_to_organization, возвращаемся к организации
            if ($request->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, $request->redirect_to_organization)
                    ->with('success', 'Подписка успешно обновлена');
            }
            
            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', 'Подписка успешно обновлена');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
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
}