<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Главная панель администратора
     */
    public function dashboard(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // === СТАТИСТИКА ===
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'managers_count' => User::where('role', 'manager')->count(),
            'managers_active' => User::where('role', 'manager')->where('is_active', true)->count(),
            'total_organizations' => Organization::whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })->count(),
            'active_organizations' => Organization::where('status', 'active')
                ->whereHas('manager', function($query) use ($admin) {
                    $query->where('admin_id', $admin->id);
                })->count(),
            'pending_organizations' => Organization::where('status', 'pending')
                ->whereHas('manager', function($query) use ($admin) {
                    $query->where('admin_id', $admin->id);
                })->count(),
        ];
        
        // === ЛИМИТЫ АДМИНИСТРАТОРА ===
        $limits = [];
        
        // Получаем ВСЕ типы отчетов
        $reportTypes = \App\Models\ReportType::all();
        
        foreach ($reportTypes as $reportType) {
            // Получаем ПОСЛЕДНИЙ лимит админа для этого типа отчета (без привязки к дате)
            $limit = \App\Models\Limit::where('user_id', $admin->id)
                ->where('report_type_id', $reportType->id)
                ->orderBy('date_created', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Логика отображения:
            // 1. Если only_api = false, ВСЕГДА показываем (даже если лимит = 0 или не установлен)
            // 2. Если only_api = true, показываем ТОЛЬКО если есть лимит
            if (!$reportType->only_api || ($reportType->only_api && $limit !== null)) {
                $quantity = $limit ? $limit->quantity : 0;
                
                $limits[] = [
                    'report_type_id' => $reportType->id,
                    'report_type_name' => $reportType->name,
                    'description' => $reportType->description,
                    'only_api' => $reportType->only_api,
                    'quantity' => $quantity,
                    'used_quantity' => $limit ? $limit->used_quantity : 0, // Добавляем использованное количество
                    'available_quantity' => $limit ? ($limit->quantity - $limit->used_quantity) : 0, // Доступное количество
                    'is_exhausted' => $limit ? ($limit->quantity - $limit->used_quantity <= 0) : true,
                    'has_limit' => $limit !== null, // Был ли установлен лимит
                    'date_created' => $limit ? $limit->date_created->format('d.m.Y') : null, // Дата создания лимита
                ];
            }
        }
        
        // Сортируем лимиты: сначала интерфейсные, потом API, потом по имени
        usort($limits, function($a, $b) {
            // Сначала сортируем по типу: интерфейсные (only_api=false) идут первыми
            if ($a['only_api'] !== $b['only_api']) {
                return $a['only_api'] ? 1 : -1;
            }
            // Потом по названию
            return strcmp($a['report_type_name'], $b['report_type_name']);
        });
        
        // === ВСЕ МЕНЕДЖЕРЫ ===
        $managers = User::where('role', 'manager')
            ->with('managerProfile')
            ->orderBy('created_at', 'desc')
            ->get(); 
        
        $organizations = Organization::whereHas('manager', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->with(['manager.user', 'owner.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $user = $admin;
            
        return view('admin.dashboard', compact(
            'user', 
            'stats', 
            'managers',
            'organizations',
            'limits'
        ));
    }
}