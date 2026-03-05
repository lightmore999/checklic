<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LimitSubscriptionLog;
use App\Models\User;
use App\Models\Limit;
use App\Models\DelegatedLimit;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LimitSubscriptionLogController extends Controller
{
    /**
     * Конструктор с проверкой прав доступа
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Доступ запрещен');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список логов
     */
    public function index(Request $request)
    {
        $query = LimitSubscriptionLog::with('user')
                    ->latest();

        // Фильтр по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Фильтр по типу сущности
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        // Фильтр по действию
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Фильтр по дате
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Фильтр по ID сущности
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        // Фильтр по изменениям количества
        if ($request->filled('has_quantity_change')) {
            $query->whereNotNull('quantity_change');
        }

        $logs = $query->paginate(50)->withQueryString();

        // Для фильтров
        $users = User::orderBy('name')->get();
        $entityTypes = [
            'limit' => 'Лимиты',
            'delegated_limit' => 'Делегированные лимиты',
            'subscription' => 'Подписки',
        ];
        $actions = [
            'create' => 'Создание',
            'update' => 'Изменение',
            'delete' => 'Удаление',
            'activate' => 'Активация',
            'suspend' => 'Приостановка',
            'cancel' => 'Отмена',
            'extend' => 'Продление',
            'use_quantity' => 'Использование',
            'return_quantity' => 'Возврат',
            'delegate' => 'Делегирование',
        ];

        return view('admin.limit-logs.index', compact('logs', 'users', 'entityTypes', 'actions'));
    }

    /**
     * Показывает детальную информацию о логе
     */
    public function show(LimitSubscriptionLog $log)
    {
        $log->load('user');

        // Получаем информацию о сущности, если она еще существует
        $entity = null;
        
        if ($log->entity_type === 'limit') {
            $entity = Limit::with(['subscription.user'])->find($log->entity_id);
        } elseif ($log->entity_type === 'delegated_limit') {
            $entity = DelegatedLimit::with(['user', 'limit.subscription'])->find($log->entity_id);
        } elseif ($log->entity_type === 'subscription') {
            $entity = Subscription::with('user')->find($log->entity_id);
        }

        return view('admin.limit-logs.show', compact('log', 'entity'));
    }

    /**
     * Экспорт логов в CSV
     */
    public function export(Request $request)
    {
        $query = LimitSubscriptionLog::with('user');

        // Применяем фильтры
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
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

        $logs = $query->latest()->get();

        $filename = 'limit_logs_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['ID', 'Дата', 'Пользователь', 'Тип', 'ID сущности', 'Действие', 'Изменение количества', 'IP'];

        $callback = function() use ($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                $quantityDesc = '';
                if ($log->quantity_change !== null) {
                    $sign = $log->quantity_change > 0 ? '+' : '';
                    $quantityDesc = $sign . $log->quantity_change . ' (было: ' . $log->old_quantity . ', стало: ' . $log->new_quantity . ')';
                }

                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('d.m.Y H:i:s'),
                    $log->user ? $log->user->name . ' (' . $log->user->email . ')' : 'Система',
                    $this->getEntityTypeName($log->entity_type),
                    $log->entity_id,
                    $this->getActionName($log->action),
                    $quantityDesc,
                    $log->ip_address ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Очистка старых логов
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $count = LimitSubscriptionLog::where('created_at', '<', now()->subDays($request->days))
                    ->delete();

        return redirect()->route('admin.limit-logs.index')
            ->with('success', "Удалено {$count} записей старше {$request->days} дней");
    }

    /**
     * Получить статистику по логам
     */
    public function statistics()
    {
        $byAction = LimitSubscriptionLog::select('action', DB::raw('count(*) as total'))
            ->groupBy('action')
            ->pluck('total', 'action');

        $byEntity = LimitSubscriptionLog::select('entity_type', DB::raw('count(*) as total'))
            ->groupBy('entity_type')
            ->pluck('total', 'entity_type');

        $stats = [
            'total' => LimitSubscriptionLog::count(),
            'by_action' => $byAction,
            'by_entity' => $byEntity,
            'today' => LimitSubscriptionLog::whereDate('created_at', today())->count(),
            'week' => LimitSubscriptionLog::where('created_at', '>=', now()->subDays(7))->count(),
            'month' => LimitSubscriptionLog::where('created_at', '>=', now()->subDays(30))->count(),
            'quantity_changes' => LimitSubscriptionLog::whereNotNull('quantity_change')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Получить название типа сущности
     */
    private function getEntityTypeName($type)
    {
        $types = [
            'limit' => 'Лимит',
            'delegated_limit' => 'Делегированный лимит',
            'subscription' => 'Подписка',
        ];
        
        return $types[$type] ?? $type;
    }

    /**
     * Получить название действия
     */
    private function getActionName($action)
    {
        $actions = [
            'create' => 'Создание',
            'update' => 'Изменение',
            'delete' => 'Удаление',
            'activate' => 'Активация',
            'suspend' => 'Приостановка',
            'cancel' => 'Отмена',
            'extend' => 'Продление',
            'use_quantity' => 'Использование',
            'return_quantity' => 'Возврат',
            'delegate' => 'Делегирование',н
        ];
        
        return $actions[$action] ?? $action;
    }
}