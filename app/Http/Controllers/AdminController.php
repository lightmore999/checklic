<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Manager;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'pending_organizations' => Organization::where('status', 'pending')->count(),
        ];
        
        // === ЛИМИТЫ АДМИНИСТРАТОРА (через подписки) ===
        $limits = [];
        
        // Получаем ВСЕ типы отчетов
        $reportTypes = \App\Models\ReportType::all();
        
        // Получаем подписки администратора
        $subscriptions = Subscription::where('user_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Для каждой подписки получаем лимиты
        $groupedLimits = [];
        foreach ($subscriptions as $subscription) {
            // Получаем лимиты для этой подписки
            $subscriptionLimits = \App\Models\Limit::where('subscription_id', $subscription->id)
                ->with('reportType')
                ->orderBy('date_created', 'desc')
                ->get();
            
            $limitsData = [];
            foreach ($subscriptionLimits as $limit) {
                if ($limit->reportType) {
                    $limitsData[] = [
                        'id' => $limit->id,
                        'report_type_id' => $limit->report_type_id,
                        'report_type_name' => $limit->reportType->name,
                        'description' => $limit->reportType->description,
                        'only_api' => $limit->reportType->only_api,
                        'quantity' => $limit->quantity,
                        'used_quantity' => $limit->used_quantity,
                        'available_quantity' => $limit->getAvailableQuantity(),
                        'is_exhausted' => $limit->isExhausted(),
                        'has_limit' => true,
                        'date_created' => $limit->date_created->format('d.m.Y'),
                    ];
                }
            }
            
            if (count($limitsData) > 0) {
                $groupedLimits[] = [
                    'subscription' => $subscription,
                    'limits' => $limitsData,
                    'total_quantity' => collect($limitsData)->sum('quantity'),
                    'total_used' => collect($limitsData)->sum('used_quantity'),
                    'total_available' => collect($limitsData)->sum('available_quantity'),
                ];
            }
        }
        
        // Формируем плоский список лимитов для отображения в таблице
        foreach ($groupedLimits as $group) {
            foreach ($group['limits'] as $limit) {
                $limits[] = $limit;
            }
        }
        
        // Добавляем типы отчетов без лимитов (для интерфейсных)
        foreach ($reportTypes as $reportType) {
            $exists = false;
            foreach ($limits as $limit) {
                if ($limit['report_type_id'] == $reportType->id) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists && !$reportType->only_api) {
                $limits[] = [
                    'report_type_id' => $reportType->id,
                    'report_type_name' => $reportType->name,
                    'description' => $reportType->description,
                    'only_api' => $reportType->only_api,
                    'quantity' => 0,
                    'used_quantity' => 0,
                    'available_quantity' => 0,
                    'is_exhausted' => true,
                    'has_limit' => false,
                    'date_created' => null,
                ];
            }
        }
        
        // Сортируем лимиты: сначала интерфейсные, потом API, потом по имени
        usort($limits, function($a, $b) {
            if ($a['only_api'] !== $b['only_api']) {
                return $a['only_api'] ? 1 : -1;
            }
            return strcmp($a['report_type_name'], $b['report_type_name']);
        });
        
        // === ВСЕ МЕНЕДЖЕРЫ ===
        $managers = User::where('role', 'manager')
            ->with('managerProfile')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // === ВСЕ ОРГАНИЗАЦИИ ===
        // Загружаем организации с владельцем и менеджером
        $organizations = Organization::with(['owner.user', 'manager'])
            ->orderBy('created_at', 'desc')
            ->get();

        $user = $admin;
        
        // Получаем статистику по подпискам
        $subscriptionsCount = $subscriptions->count();
        $activeSubscriptionsCount = $subscriptions->where('status', 'active')->count();
            
        return view('admin.dashboard', compact(
            'user', 
            'stats', 
            'managers',
            'organizations',
            'limits',
            'groupedLimits',
            'subscriptions',
            'subscriptionsCount',
            'activeSubscriptionsCount'
        ));
    }

    /**
     * Показать форму редактирования профиля администратора
     */
    public function editProfile()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Обновить профиль администратора
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Профиль успешно обновлен');
    }

    /**
     * Показать форму изменения пароля
     */
    public function showChangePasswordForm()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('admin.profile.change-password', compact('user'));
    }

    /**
     * Изменить пароль
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'Текущий пароль не совпадает',
            'new_password.min' => 'Пароль должен содержать минимум 8 символов',
            'new_password.confirmed' => 'Пароли не совпадают',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Пароль успешно изменен');
    }
}