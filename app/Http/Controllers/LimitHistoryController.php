<?php

namespace App\Http\Controllers;

use App\Models\LimitSubscriptionLog;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Organization;
use App\Models\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LimitHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Показать историю лимитов
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Базовый запрос только для лимитов (не подписок)
        $query = LimitSubscriptionLog::with(['user'])
            ->whereIn('entity_type', ['limit', 'delegated_limit']);

        // Фильтрация по правам доступа
        $this->applyAccessFilter($query, $user);

        // Применяем фильтры из запроса
        $this->applyRequestFilters($query, $request, $user);

        // Получаем статистику ДО пагинации
        $stats = $this->getStatistics($query, $request);

        // Применяем сортировку и пагинацию ПОСЛЕ статистики
        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Для фильтров
        $users = $this->getAccessibleUsers($user, $request);
        $subscriptions = $this->getAccessibleSubscriptions($user);
        $organizations = $this->getAccessibleOrganizations($user); // НОВЫЙ МЕТОД
        $reportTypes = \App\Models\ReportType::orderBy('name')->get();
        
        $actions = [
            'create' => 'Создание',
            'use_quantity' => 'Использование',
            'return_quantity' => 'Возврат',
            'delegate' => 'Делегирование',
            'update' => 'Изменение',
            'delete' => 'Удаление',
        ];

        return view('limits.history', compact('logs', 'users', 'subscriptions', 'organizations', 'reportTypes', 'actions', 'stats'));
    }

    /**
     * Применить фильтр по правам доступа
     */
    private function applyAccessFilter($query, $user)
    {
        if ($user->isAdmin()) {
            // Админ видит всё
            return;
        }

        // Получаем ID подписок и лимитов, доступных пользователю
        $accessibleData = $this->getAccessibleData($user);
        
        $query->where(function($q) use ($accessibleData, $user) {
            // Лимиты, доступные пользователю
            if (!empty($accessibleData['limit_ids'])) {
                $q->orWhere(function($subQ) use ($accessibleData) {
                    $subQ->whereIn('entity_id', $accessibleData['limit_ids'])
                         ->where('entity_type', 'limit');
                });
            }
              
            // Делегированные лимиты, где пользователь является целевым
            if (!empty($accessibleData['delegated_limit_ids'])) {
                $q->orWhere(function($subQ) use ($accessibleData) {
                    $subQ->whereIn('entity_id', $accessibleData['delegated_limit_ids'])
                         ->where('entity_type', 'delegated_limit');
                });
            }
            
            // Действия, совершенные самим пользователем
            $q->orWhere('user_id', $user->id);
        });
    }

    /**
     * Получить доступные данные для пользователя
     */
    private function getAccessibleData($user)
    {
        $result = [
            'limit_ids' => [],
            'delegated_limit_ids' => []
        ];

        if ($user->isManager()) {
            // Менеджер - организации, которые он курирует
            $organizationIds = Organization::where('manager_id', $user->id)->pluck('id');
            
            $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                })
                ->orWhereHas('orgMemberProfile', function($q) use ($organizationIds) {
                    $q->whereIn('organization_id', $organizationIds);
                })
                ->pluck('id');
            
            $subscriptionIds = Subscription::whereIn('user_id', $userIds)->pluck('id');
            $result['limit_ids'] = Limit::whereIn('subscription_id', $subscriptionIds)->pluck('id')->toArray();
            
        } elseif ($user->isOrgOwner()) {
            // Владелец - своя организация и сотрудники
            $organization = $user->orgOwnerProfile->organization;
            $memberIds = \App\Models\OrgMemberProfile::where('organization_id', $organization->id)
                ->pluck('user_id');
            
            $allUserIds = $memberIds->push($user->id);
            $subscriptionIds = Subscription::whereIn('user_id', $allUserIds)->pluck('id');
            $result['limit_ids'] = Limit::whereIn('subscription_id', $subscriptionIds)->pluck('id')->toArray();
            
            // Делегированные лимиты сотрудников
            $result['delegated_limit_ids'] = \App\Models\DelegatedLimit::whereIn('user_id', $memberIds)
                ->pluck('id')
                ->toArray();
                
        } elseif ($user->isOrgMember()) {
            // Сотрудник - только свои лимиты
            $subscriptionIds = Subscription::where('user_id', $user->id)->pluck('id');
            $result['limit_ids'] = Limit::whereIn('subscription_id', $subscriptionIds)->pluck('id')->toArray();
            
            // Свои делегированные лимиты
            $result['delegated_limit_ids'] = \App\Models\DelegatedLimit::where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        return $result;
    }

    /**
     * Применить фильтры из запроса
     */
    private function applyRequestFilters($query, $request, $user)
    {
        // Фильтр по организации
        if ($request->filled('organization_id')) {
            $organization = Organization::find($request->organization_id);
            if ($organization) {
                // Получаем пользователей этой организации
                $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organization) {
                        $q->where('organization_id', $organization->id);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organization) {
                        $q->where('organization_id', $organization->id);
                    })
                    ->pluck('id');
                
                if ($userIds->isNotEmpty()) {
                    $subscriptionIds = Subscription::whereIn('user_id', $userIds)->pluck('id');
                    $limitIds = Limit::whereIn('subscription_id', $subscriptionIds)->pluck('id');
                    
                    $query->whereIn('entity_id', $limitIds)
                          ->where('entity_type', 'limit');
                }
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('subscription_id')) {
            $limitIds = Limit::where('subscription_id', $request->subscription_id)->pluck('id');
            if ($limitIds->isNotEmpty()) {
                $query->whereIn('entity_id', $limitIds)
                      ->where('entity_type', 'limit');
            }
        }

        if ($request->filled('report_type_id')) {
            $limitIds = Limit::where('report_type_id', $request->report_type_id)->pluck('id');
            if ($limitIds->isNotEmpty()) {
                $query->whereIn('entity_id', $limitIds)
                      ->where('entity_type', 'limit');
            }
        }
    }

    /**
     * Получить статистику за период (без сортировки и пагинации)
     */
    private function getStatistics($query, $request)
    {
        // Клонируем запрос без пагинации и сортировки
        $statsQuery = clone $query;
        
        $stats = [
            'total_operations' => $statsQuery->count(),
            'total_quantity_change' => $statsQuery->sum('quantity_change') ?? 0,
            'total_created' => (clone $query)->where('action', 'create')->sum('new_quantity') ?? 0,
            'by_action' => [],
            'daily' => [],
        ];

        // Статистика по типам операций
        $byAction = (clone $query)
            ->select('action', 
                DB::raw('count(*) as count'), 
                DB::raw('COALESCE(sum(quantity_change), 0) as total_quantity'),
                DB::raw('COALESCE(sum(new_quantity), 0) as total_created')
            )
            ->groupBy('action')
            ->get();

        foreach ($byAction as $item) {
            $totalQuantity = $item->total_quantity;
            // Для create используем new_quantity вместо quantity_change
            if ($item->action === 'create') {
                $totalQuantity = $item->total_created;
            }
            
            $stats['by_action'][$item->action] = [
                'count' => $item->count,
                'total_quantity' => $totalQuantity,
            ];
        }

        // Статистика по дням (для графика)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $daily = (clone $query)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count'),
                    DB::raw('COALESCE(sum(quantity_change), 0) as total_quantity'),
                    DB::raw('COALESCE(sum(case when action = \'create\' then new_quantity else 0 end), 0) as created'),
                    DB::raw('COALESCE(sum(case when action = \'return_quantity\' then quantity_change else 0 end), 0) as returned'),
                    DB::raw('COALESCE(sum(case when action = \'use_quantity\' then quantity_change else 0 end), 0) as used')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $stats['daily'] = $daily;
        }

        return $stats;
    }

    /**
     * Получить пользователей, доступных текущему пользователю
     */
    private function getAccessibleUsers($user, $request = null)
    {
        // Если есть фильтр по организации, получаем пользователей этой организации
        if ($request && $request->filled('organization_id')) {
            $organization = Organization::find($request->organization_id);
            if ($organization) {
                $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organization) {
                        $q->where('organization_id', $organization->id);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organization) {
                        $q->where('organization_id', $organization->id);
                    })
                    ->pluck('id');
                
                return User::whereIn('id', $userIds)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email', 'role']);
            }
        }

        if ($user->isAdmin()) {
            return User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        }

        $data = $this->getAccessibleData($user);
        $userIds = [];
        
        // Пользователи, которым принадлежат лимиты
        if (!empty($data['limit_ids'])) {
            $limitUserIds = Limit::whereIn('id', $data['limit_ids'])
                ->with('subscription.user')
                ->get()
                ->pluck('subscription.user.id')
                ->filter()
                ->toArray();
            $userIds = array_merge($userIds, $limitUserIds);
        }
        
        // Пользователи, которым делегированы лимиты
        if (!empty($data['delegated_limit_ids'])) {
            $delegatedUserIds = \App\Models\DelegatedLimit::whereIn('id', $data['delegated_limit_ids'])
                ->pluck('user_id')
                ->toArray();
            $userIds = array_merge($userIds, $delegatedUserIds);
        }
        
        // Добавляем самого пользователя
        $userIds[] = $user->id;
        
        return User::whereIn('id', array_unique($userIds))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    /**
     * Получить организации, доступные текущему пользователю
     */
    private function getAccessibleOrganizations($user)
    {
        if ($user->isAdmin()) {
            return Organization::orderBy('name')->get();
        }

        if ($user->isManager()) {
            return Organization::where('manager_id', $user->id)
                ->orderBy('name')
                ->get();
        }

        if ($user->isOrgOwner()) {
            $organization = $user->orgOwnerProfile->organization;
            return $organization ? collect([$organization]) : collect();
        }

        if ($user->isOrgMember()) {
            $organization = $user->orgMemberProfile->organization;
            return $organization ? collect([$organization]) : collect();
        }

        return collect();
    }

    /**
     * Получить подписки, доступные текущему пользователю
     */
    private function getAccessibleSubscriptions($user)
    {
        if ($user->isAdmin()) {
            return Subscription::with('user')->orderBy('created_at', 'desc')->get();
        }

        $data = $this->getAccessibleData($user);
        
        if (empty($data['limit_ids'])) {
            return collect();
        }

        $subscriptionIds = Limit::whereIn('id', $data['limit_ids'])
            ->pluck('subscription_id')
            ->unique();

        return Subscription::whereIn('id', $subscriptionIds)
            ->with('user')
            ->get();
    }

    /**
     * Экспорт в CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $query = LimitSubscriptionLog::with(['user'])
            ->whereIn('entity_type', ['limit', 'delegated_limit']);

        $this->applyAccessFilter($query, $user);
        $this->applyRequestFilters($query, $request, $user);

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'limit_history_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['Дата', 'Операция', 'Кто', 'Кому', 'Подписка', 'Тип отчета', 'Количество', 'Баланс до', 'Баланс после'];

        $callback = function() use ($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                $targetUser = $log->targetUser();
                $subscription = $log->subscription();
                $reportType = $log->reportType();
                
                fputcsv($file, [
                    $log->created_at->format('d.m.Y H:i:s'),
                    $log->action_name,
                    $log->user?->name ?? 'Система',
                    $targetUser?->name ?? '—',
                    $subscription?->name ?? '—',
                    $reportType?->name ?? '—',
                    $log->quantity_change,
                    $log->old_quantity,
                    $log->new_quantity,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API для поиска пользователей (для Select2)
     */
    public function searchUsers(Request $request)
    {
        $user = Auth::user();
        
        $query = User::query();
        
        // Фильтр по организации
        if ($request->filled('organization_id')) {
            $organization = Organization::find($request->organization_id);
            if ($organization) {
                $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organization) {
                        $q->where('organization_id', $organization->id);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organization) {
                        $q->where('organization_id', $organization->id);
                    })
                    ->pluck('id');
                
                $query->whereIn('id', $userIds);
            }
        } else {
            // Если нет фильтра по организации, применяем права доступа
            $accessibleData = $this->getAccessibleData($user);
            $accessibleUserIds = [];
            
            if (!empty($accessibleData['limit_ids'])) {
                $limitUserIds = Limit::whereIn('id', $accessibleData['limit_ids'])
                    ->with('subscription.user')
                    ->get()
                    ->pluck('subscription.user.id')
                    ->filter()
                    ->toArray();
                $accessibleUserIds = array_merge($accessibleUserIds, $limitUserIds);
            }
            
            if (!empty($accessibleData['delegated_limit_ids'])) {
                $delegatedUserIds = \App\Models\DelegatedLimit::whereIn('id', $accessibleData['delegated_limit_ids'])
                    ->pluck('user_id')
                    ->toArray();
                $accessibleUserIds = array_merge($accessibleUserIds, $delegatedUserIds);
            }
            
            $accessibleUserIds[] = $user->id;
            $query->whereIn('id', array_unique($accessibleUserIds));
        }

        // Поиск по имени или email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $users = $query->limit(20)->get(['id', 'name', 'email', 'role']);

        $formattedUsers = $users->map(function($user) {
            return [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->email . ') - ' . $user->getRoleDisplayName(),
            ];
        });

        return response()->json($formattedUsers);
    }
}