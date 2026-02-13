<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use App\Models\Organization;
use App\Models\ReportType;
use App\Models\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    /**
     * Форма создания менеджера (для админа)
     */
    public function create()
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('managers.create', compact('admin'));
    }
    
    /**
     * Сохранение нового менеджера (для админа)
     */
    public function store(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        DB::beginTransaction();
        
        try {
            // 1. Создаем пользователя с ролью manager
            $managerUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'manager',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            // 2. Создаем профиль менеджера с ссылкой на админа
            Manager::create([
                'user_id' => $managerUser->id,
                'admin_id' => $admin->id,
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Менеджер успешно создан');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Ошибка при создании менеджера: ' . $e->getMessage());
        }
    }
    
    /**
     * Просмотр профиля менеджера (для админа)
     */
    public function show($id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что менеджер принадлежит этому админу
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->whereHas('managerProfile', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->with('managerProfile.admin')
            ->firstOrFail();
        
        // Получаем организации этого менеджера
        $organizations = Organization::where('manager_id', $manager->id)
            ->with(['owner.user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // === ДОБАВЛЕНО: ЛИМИТЫ МЕНЕДЖЕРА ===
        $limits = [];
        
        // Получаем ВСЕ типы отчетов
        $reportTypes = ReportType::all();
        
        foreach ($reportTypes as $reportType) {
            // ПОЛУЧАЕМ ПОСЛЕДНИЙ ЛИМИТ МЕНЕДЖЕРА
            $limit = Limit::where('user_id', $manager->id)
                ->where('report_type_id', $reportType->id)
                ->orderBy('date_created', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Логика отображения:
            // 1. Если only_api = false, ВСЕГДА показываем
            // 2. Если only_api = true, показываем ТОЛЬКО если есть лимит
            if (!$reportType->only_api || ($reportType->only_api && $limit !== null)) {
                $quantity = $limit ? $limit->quantity : 0;
                $used_quantity = $limit ? $limit->used_quantity : 0;
                $available_quantity = $limit ? ($limit->quantity - $limit->used_quantity) : 0;
                
                $limits[] = [
                    'report_type_id' => $reportType->id,
                    'report_type_name' => $reportType->name,
                    'description' => $reportType->description,
                    'only_api' => $reportType->only_api,
                    'quantity' => $quantity,
                    'used_quantity' => $used_quantity,
                    'available_quantity' => $available_quantity,
                    'is_exhausted' => $limit ? ($limit->quantity - $limit->used_quantity <= 0) : true,
                    'has_limit' => $limit !== null,
                    'date_created' => $limit ? $limit->date_created->format('d.m.Y') : null,
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
        
        return view('managers.show', compact('admin', 'manager', 'organizations', 'limits'));
    }

    /**
     * Форма редактирования менеджера (для админа)
     */
    public function edit($id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что менеджер принадлежит этому админу
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->whereHas('managerProfile', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        return view('managers.edit', compact('admin', 'manager'));
    }

    /**
     * Обновление данных менеджера (для админа)
     */
    public function update(Request $request, $id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что менеджер принадлежит этому админу
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->whereHas('managerProfile', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        // Правила валидации
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $manager->id,
        ];
        
        // Если меняем пароль
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        
        $validated = $request->validate($rules);
        
        // Обновляем данные
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }
        
        $manager->update($updateData);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Данные менеджера обновлены');
    }
    
    /**
     * Активация/деактивация менеджера (для админа)
     */
    public function toggleStatus(Request $request, $id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что менеджер принадлежит этому админу
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->whereHas('managerProfile', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        // Нельзя изменить статус самому себе
        if ($manager->id === $admin->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Нельзя изменить статус самому себе'], 403);
            }
            return back()->with('error', 'Нельзя изменить статус самому себе');
        }
        
        $manager->is_active = !$manager->is_active;
        $manager->save();
        
        $action = $manager->is_active ? 'активирован' : 'деактивирован';
        $message = "Менеджер {$manager->name} успешно {$action}";
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $manager->is_active,
                'status_badge' => $manager->is_active 
                    ? '<span class="badge bg-success">Активен</span>' 
                    : '<span class="badge bg-danger">Неактивен</span>',
                'button_title' => $manager->is_active ? 'Деактивировать' : 'Активировать',
                'button_class' => $manager->is_active ? 'btn-warning' : 'btn-success',
                'button_icon' => $manager->is_active ? 'toggle-on' : 'toggle-off',
            ]);
        }
        
        return back()->with('success', $message);
    }

    /**
     * Удаление менеджера (для админа)
     */
    public function destroy($id)
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что менеджер принадлежит этому админу
        $manager = User::where('id', $id)
            ->where('role', 'manager')
            ->whereHas('managerProfile', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->firstOrFail();
        
        // Нельзя удалить самого себя
        if ($manager->id === $admin->id) {
            return back()->with('error', 'Нельзя удалить самого себя');
        }
        
        // ИСПРАВЛЕНО: Проверяем, есть ли у менеджера организации
        $hasOrganizations = Organization::where('manager_id', $manager->id)->exists();
        
        if ($hasOrganizations) {
            return back()->with('error', 'Нельзя удалить менеджера, у которого есть организации. Сначала передайте организации другому менеджеру.');
        }
        
        DB::beginTransaction();
        
        try {
            // Удаляем профиль менеджера
            $manager->managerProfile()->delete();
            
            // Удаляем пользователя
            $manager->delete();
            
            DB::commit();
            
            return redirect()->route('admin.dashboard')
                ->with('success', "Менеджер {$manager->name} удален");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Ошибка при удалении менеджера: ' . $e->getMessage());
        }
    }
    
    /**
     * Панель менеджера (для самого менеджера)
     */
    public function dashboard()
    {
        $manager = Auth::user();
        
        if (!$manager->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        // === СТАТИСТИКА ===
        $stats = [
            'total_organizations' => Organization::where('manager_id', $manager->id)->count(),
            
            'active_organizations' => Organization::where('manager_id', $manager->id)
                ->where('status', 'active')
                ->count(),
            
            'pending_organizations' => Organization::where('manager_id', $manager->id)
                ->where('status', 'pending')
                ->count(),
        ];
        
        // === ЛИМИТЫ МЕНЕДЖЕРА ===
        $limits = [];
        
        // Получаем ВСЕ типы отчетов
        $reportTypes = ReportType::all();
        
        foreach ($reportTypes as $reportType) {
            // ПОЛУЧАЕМ ПОСЛЕДНИЙ ЛИМИТ МЕНЕДЖЕРА
            $limit = Limit::where('user_id', $manager->id)
                ->where('report_type_id', $reportType->id)
                ->orderBy('date_created', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Логика отображения:
            // 1. Если only_api = false, ВСЕГДА показываем
            // 2. Если only_api = true, показываем ТОЛЬКО если есть лимит
            if (!$reportType->only_api || ($reportType->only_api && $limit !== null)) {
                $quantity = $limit ? $limit->quantity : 0;
                $used_quantity = $limit ? $limit->used_quantity : 0;
                $available_quantity = $limit ? ($limit->quantity - $limit->used_quantity) : 0;
                
                $limits[] = [
                    'report_type_id' => $reportType->id,
                    'report_type_name' => $reportType->name,
                    'description' => $reportType->description,
                    'only_api' => $reportType->only_api,
                    'quantity' => $quantity,
                    'used_quantity' => $used_quantity,
                    'available_quantity' => $available_quantity,
                    'is_exhausted' => $limit ? ($limit->quantity - $limit->used_quantity <= 0) : true,
                    'has_limit' => $limit !== null,
                    'date_created' => $limit ? $limit->date_created->format('d.m.Y') : null,
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
        
        // ИСПРАВЛЕНО: Получаем организации менеджера
        $organizations = Organization::where('manager_id', $manager->id)
            ->with(['owner.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Получаем админа менеджера
        $admin = $manager->managerProfile->admin ?? null;
        
        return view('managers.dashboard', compact(
            'manager',
            'stats',
            'organizations',
            'admin',
            'limits'
        ));
    }
    
    /**
     * Показать профиль менеджера (для самого менеджера)
     */
    public function profile()
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $admin = $user->managerProfile->admin ?? null;
        
        // ИСПРАВЛЕНО: Получаем статистику организаций менеджера
        $organizationsCount = Organization::where('manager_id', $user->id)->count();
        $activeOrganizationsCount = Organization::where('manager_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        return view('managers.profile', compact('user', 'admin', 'organizationsCount', 'activeOrganizationsCount'));
    }
    
    /**
     * Показать форму редактирования профиля менеджера (для самого менеджера)
     */
    public function editProfile()
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('managers.edit-profile', compact('user'));
    }
    
    /**
     * Обновить профиль менеджера (для самого менеджера)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $validated['password'] = Hash::make($request->password);
        }
        
        $user->update($validated);
        
        return redirect()->route('manager.profile')
            ->with('success', 'Профиль успешно обновлен');
    }
    
    /**
     * Список всех менеджеров админа (для админа)
     */
    public function index()
    {
        $admin = Auth::user();
        
        if (!$admin->isAdmin()) {
            abort(403, 'Доступ запрещен');
        }
        
        // ИСПРАВЛЕНО: Получаем менеджеров этого админа
        $managers = User::where('role', 'manager')
            ->whereHas('managerProfile', function($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })
            ->with('managerProfile')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Добавляем статистику по организациям для каждого менеджера
        foreach ($managers as $manager) {
            $manager->organizations_count = Organization::where('manager_id', $manager->id)->count();
            $manager->active_organizations_count = Organization::where('manager_id', $manager->id)
                ->where('status', 'active')
                ->count();
        }
        
        return view('managers.index', compact('admin', 'managers'));
    }
}