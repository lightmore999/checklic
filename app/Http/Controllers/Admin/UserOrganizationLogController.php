<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserOrganizationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserOrganizationLogController extends Controller
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
        $query = UserOrganizationLog::with('user')
                    ->latest();

        // Фильтр по пользователю (кто совершил действие)
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

        // Поиск по ID сущности
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        // Поиск по тексту (в new_data или old_data) - опционально
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('new_data', 'like', "%{$search}%")
                  ->orWhere('old_data', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // Для фильтров
        $users = User::orderBy('name')->get();
        $entityTypes = [
            'user' => 'Пользователи',
            'organization' => 'Организации',
            'manager' => 'Менеджеры',
            'org_owner' => 'Владельцы организаций',
            'org_member' => 'Сотрудники',
        ];
        $actions = [
            'create' => 'Создание',
            'update' => 'Изменение',
            'delete' => 'Удаление',
            'restore' => 'Восстановление',
            'login' => 'Вход',
            'logout' => 'Выход',
            'status_change' => 'Изменение статуса',
            'force_delete' => 'Полное удаление',
        ];

        return view('admin.logs.index', compact('logs', 'users', 'entityTypes', 'actions'));
    }

    /**
     * Показывает детальную информацию о логе
     */
    public function show(UserOrganizationLog $log)
    {
        $log->load('user');

        // Получаем информацию о сущности, если она еще существует
        $entity = null;
        
        switch ($log->entity_type) {
            case 'user':
                $entity = User::find($log->entity_id);
                break;
            case 'organization':
                $entity = \App\Models\Organization::find($log->entity_id);
                break;
            case 'manager':
                $entity = \App\Models\Manager::find($log->entity_id);
                break;
            case 'org_owner':
                $entity = \App\Models\OrgOwnerProfile::with('user', 'organization')->find($log->entity_id);
                break;
            case 'org_member':
                $entity = \App\Models\OrgMemberProfile::with('user', 'organization')->find($log->entity_id);
                break;
        }

        return view('admin.logs.show', compact('log', 'entity'));
    }

    /**
     * Экспорт логов в CSV
     */
    public function export(Request $request)
    {
        $query = UserOrganizationLog::with('user');

        // Применяем те же фильтры, что и в index
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
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        $logs = $query->latest()->get();

        $filename = 'logs_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['ID', 'Дата', 'Пользователь', 'Тип сущности', 'ID сущности', 'Действие', 'IP', 'User Agent'];

        $callback = function() use ($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('d.m.Y H:i:s'),
                    $log->user ? $log->user->name . ' (' . $log->user->email . ')' : 'Система',
                    $log->entity_type_name,
                    $log->entity_id,
                    $log->action_name,
                    $log->ip_address ?? '',
                    $log->user_agent ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Очистка старых логов (только для админов)
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $count = UserOrganizationLog::where('created_at', '<', now()->subDays($request->days))
                    ->delete();

        return redirect()->route('admin.logs.index')
            ->with('success', "Удалено {$count} записей старше {$request->days} дней");
    }

    /**
     * Получить статистику по логам
     */
    public function statistics()
    {
        $stats = [
            'total' => UserOrganizationLog::count(),
            'by_action' => UserOrganizationLog::select('action', DB::raw('count(*) as total'))
                ->groupBy('action')
                ->pluck('total', 'action'),
            'by_entity' => UserOrganizationLog::select('entity_type', DB::raw('count(*) as total'))
                ->groupBy('entity_type')
                ->pluck('total', 'entity_type'),
            'today' => UserOrganizationLog::whereDate('created_at', today())->count(),
            'week' => UserOrganizationLog::where('created_at', '>=', now()->subDays(7))->count(),
            'month' => UserOrganizationLog::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json($stats);
    }
}