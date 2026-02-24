<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Organization;
use App\Models\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
        
        // Для менеджера - только подписки пользователей его организаций
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
                
                return view('subscriptions.index', compact('subscriptions', 'users', 'stats'));
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
        
        // Статистика
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
        
        return view('subscriptions.index', compact('subscriptions', 'users', 'stats'));
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
        
        return view('subscriptions.create', compact('users'));
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
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Проверяем, нет ли уже активной подписки у пользователя
        $targetUser = User::find($request->user_id);
        $activeSubscription = $targetUser->activeSubscription();
        
        if ($activeSubscription && $request->status == 'active') {
            return redirect()->back()
                ->with('error', 'У пользователя уже есть активная подписка')
                ->withInput();
        }
        
        try {
            $subscription = Subscription::create([
                'user_id' => $request->user_id,
                'starts_at' => $request->starts_at ? Carbon::parse($request->starts_at) : now(),
                'ends_at' => $request->ends_at ? Carbon::parse($request->ends_at) : null,
                'status' => $request->status,
            ]);
            
            // Если есть redirect_to_organization, возвращаемся к организации
            if ($request->filled('redirect_to_organization')) {
                $route = $user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show';
                return redirect()->route($route, $request->redirect_to_organization)
                    ->with('success', 'Подписка успешно создана');
            }
            
            return redirect()->route('subscriptions.index')
                ->with('success', 'Подписка успешно создана');
                
        } catch (\Exception $e) {
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
}