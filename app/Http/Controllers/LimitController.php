<?php

namespace App\Http\Controllers;

use App\Models\Limit;
use App\Models\User;
use App\Models\ReportType;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LimitController extends Controller
{
    /**
     * Список всех лимитов
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Начинаем запрос с подгрузкой связей
        $query = Limit::with([
            'user.orgOwnerProfile', 
            'user.orgMemberProfile',
            'creator', // <-- ДОБАВЬТЕ ЭТО
            'creator.orgOwnerProfile',
            'creator.orgMemberProfile',
            'reportType',
            'delegatedVersions.user.orgOwnerProfile',
            'delegatedVersions.user.orgMemberProfile'
        ])
            ->orderBy('date_created', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Если пользователь - менеджер, показываем только лимиты его организаций
        if ($user->isManager()) {
            $organizationIds = Organization::whereHas('manager', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->pluck('id');
            
            if ($organizationIds->isNotEmpty()) {
                // Получаем ID пользователей из организаций менеджера
                $userIds = User::whereHas('orgOwnerProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->pluck('id');
                    
                $query->whereIn('user_id', $userIds);
            }
        }
        
        // Фильтрация по организации
        if ($request->filled('organization_id')) {
            $orgUserIds = User::whereHas('orgOwnerProfile', function($q) use ($request) {
                    $q->where('organization_id', $request->organization_id);
                })
                ->orWhereHas('orgMemberProfile', function($q) use ($request) {
                    $q->where('organization_id', $request->organization_id);
                })
                ->pluck('id');
                
            $query->whereIn('user_id', $orgUserIds);
        }
        
        // Фильтрация по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Фильтрация по типу отчета
        if ($request->filled('report_type_id')) {
            $query->where('report_type_id', $request->report_type_id);
        }
        
        // Фильтрация по дате
        if ($request->filled('date')) {
            $query->where('date_created', $request->date);
        }
        
        // Фильтрация по статусу
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->whereRaw('quantity > used_quantity');
            } elseif ($request->status == 'exhausted') {
                $query->whereRaw('quantity <= used_quantity');
            }
        }
        
        $limits = $query->paginate(20);
        
        // Данные для фильтров
        $users = $this->getAvailableUsers($user);
        $reportTypes = ReportType::all();
        
        // Получаем организации для фильтра
        if ($user->isAdmin()) {
            $organizations = Organization::orderBy('name')->get();
        } elseif ($user->isManager()) {
            $organizations = Organization::whereHas('manager', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orderBy('name')->get();
        } else {
            $organizations = collect();
        }
        
        return view('limits.index', compact('limits', 'users', 'reportTypes', 'organizations'));
    }

    
    /**
     * Форма создания лимита
     */
    public function create()
    {
        $user = Auth::user();
        $users = $this->getAvailableUsers($user);
        $reportTypes = ReportType::all();
        
        return view('limits.create', compact('users', 'reportTypes'));
    }
    
    /**
     * Сохранить лимит
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Валидация
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'quantity' => 'required|integer|min:0',
            'date_created' => 'required|date',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Проверяем доступ к пользователю
        if (!$this->checkUserAvailability($user->id, $request->user_id)) {
            return redirect()->back()
                ->with('error', 'Вы не можете выдавать лимиты этому пользователю')
                ->withInput();
        }
        
        // Создаем или обновляем лимит
        try {
            $limit = Limit::createOrUpdateLimit(
                $request->user_id,
                $request->report_type_id,
                $request->quantity,
                $request->date_created
            );
            
            // Явно сохраняем создателя
            $limit->created_by = auth()->id();
            $limit->save();
            
            return redirect()->route('limits.index')
                ->with('success', 'Лимит успешно создан');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Форма редактирования лимита (только для админа)
     */
    public function edit(Limit $limit)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Только администратор может редактировать лимиты');
        }
        
        $users = User::whereIn('role', ['org_owner', 'org_member'])->get();
        $reportTypes = ReportType::all();
        
        return view('limits.edit', compact('limit', 'users', 'reportTypes'));
    }
    
    /**
     * Обновить лимит (только для админа)
     */
    public function update(Request $request, Limit $limit)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Только администратор может обновлять лимиты');
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'quantity' => 'required|integer|min:0',
            'date_created' => 'required|date',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $limit->update([
                'user_id' => $request->user_id,
                'report_type_id' => $request->report_type_id,
                'quantity' => $request->quantity,
                'date_created' => $request->date_created,
            ]);
            
            return redirect()->route('limits.index')
                ->with('success', 'Лимит успешно обновлен');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Удалить лимит (только для админа)
     */
    public function destroy(Limit $limit)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Только администратор может удалять лимиты');
        }
        
        try {
            $limit->delete();
            
            return redirect()->route('limits.index')
                ->with('success', 'Лимит успешно удален');
                
        } catch (\Exception $e) {
            return redirect()->route('limits.index')
                ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }
    
    /**
     * Массовое создание лимитов
     */
    public function bulkCreate()
    {
        $user = Auth::user();
        $users = $this->getAvailableUsers($user);
        $reportTypes = ReportType::all();
        
        return view('limits.bulk-create', compact('users', 'reportTypes'));
    }
    
    /**
     * Сохранить массовые лимиты
     */
    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'quantity' => 'required|integer|min:0',
            'date_created' => 'required|date',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Для менеджера проверяем всех пользователей
        if ($user->isManager()) {
            foreach ($request->user_ids as $userId) {
                if (!$this->checkUserAvailability($user->id, $userId)) {
                    return redirect()->back()
                        ->with('error', 'Вы не можете выдавать лимиты одному из выбранных пользователей')
                        ->withInput();
                }
            }
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($request->user_ids as $userId) {
            try {
                $limit = Limit::createOrUpdateLimit(
                    $userId,
                    $request->report_type_id,
                    $request->quantity,
                    $request->date_created
                );
                
                $limit->created_by = auth()->id();
                $limit->save();
                
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }
        
        $message = "Успешно создано: $successCount лимитов";
        if ($errorCount > 0) {
            $message .= ", ошибок: $errorCount";
        }
        
        return redirect()->route('limits.index')
            ->with('success', $message);
    }
    
    /**
     * Лимиты конкретного пользователя
     */
    public function userLimits(User $targetUser, Request $request)
    {
        $user = Auth::user();
        
        // Проверка доступности пользователя для менеджера
        if ($user->isManager()) {
            $isAvailable = $this->checkUserAvailability($user->id, $targetUser->id);
            if (!$isAvailable) {
                abort(403, 'Доступ к лимитам этого пользователя запрещен');
            }
        }
        
        $query = Limit::where('user_id', $targetUser->id)
            ->with(['reportType'])
            ->orderBy('date_created', 'desc');
            
        if ($request->filled('report_type_id')) {
            $query->where('report_type_id', $request->report_type_id);
        }
        
        if ($request->filled('date')) {
            $query->where('date_created', $request->date);
        }
        
        $limits = $query->paginate(20);
        $reportTypes = ReportType::all();
        
        return view('limits.user-limits', compact('limits', 'targetUser', 'reportTypes'));
    }
    
    /**
     * API: Проверить лимит
     */
    public function checkLimit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'required_amount' => 'integer|min:1|max:1000',
            'date' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Неверные параметры',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $hasLimit = Limit::checkLimit(
            $request->user_id,
            $request->report_type_id,
            $request->required_amount ?? 1,
            $request->date
        );
        
        return response()->json([
            'success' => true,
            'has_limit' => $hasLimit,
            'message' => $hasLimit ? 'Лимит доступен' : 'Лимит недоступен'
        ]);
    }
    
    /**
     * API: Уменьшить лимит
     */
    public function decrementLimit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'amount' => 'integer|min:1|max:1000',
            'date' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Неверные параметры',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $limit = Limit::getUserLimit(
            $request->user_id,
            $request->report_type_id,
            $request->date
        );
        
        if (!$limit) {
            return response()->json([
                'success' => false,
                'message' => 'Лимит не найден'
            ], 404);
        }
        
        $result = $limit->decrementLimit($request->amount ?? 1);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Лимит уменьшен' : 'Недостаточно лимита',
            'remaining' => $limit->quantity
        ]);
    }
    
    /**
     * API: Увеличить лимит
     */
    public function incrementLimit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'amount' => 'integer|min:1|max:1000',
            'date' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Неверные параметры',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $limit = Limit::getUserLimit(
            $request->user_id,
            $request->report_type_id,
            $request->date
        );
        
        if (!$limit) {
            // Создаем новый лимит
            $limit = Limit::createOrUpdateLimit(
                $request->user_id,
                $request->report_type_id,
                $request->amount ?? 1,
                $request->date
            );
        } else {
            $limit->incrementLimit($request->amount ?? 1);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Лимит увеличен',
            'quantity' => $limit->quantity
        ]);
    }
    
    /**
     * Получить список доступных пользователей
     */
    private function getAvailableUsers(User $currentUser)
    {
        if ($currentUser->isAdmin()) {
            // Админ может выдавать лимиты всем: менеджерам, владельцам, сотрудникам и СЕБЕ
            return User::whereIn('role', ['manager', 'org_owner', 'org_member'])
                ->orWhere('id', $currentUser->id) // Добавляем самого админа
                ->where('is_active', true)
                ->orderByRaw("
                    CASE 
                        WHEN role = 'manager' THEN 1
                        WHEN role = 'org_owner' THEN 2
                        WHEN role = 'org_member' THEN 3
                        ELSE 4
                    END
                ")
                ->orderBy('name')
                ->get();
        }
        
        if ($currentUser->isManager()) {
            // Получаем организации менеджера через связь с Manager
            $organizations = Organization::whereHas('manager', function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();
            
            if ($organizations->isEmpty()) {
                return collect();
            }
            
            $organizationIds = $organizations->pluck('id')->toArray();
            
            // Ищем владельцев и сотрудников этих организаций
            return User::where(function($query) use ($organizationIds) {
                    $query->whereHas('orgOwnerProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    })
                    ->orWhereHas('orgMemberProfile', function($q) use ($organizationIds) {
                        $q->whereIn('organization_id', $organizationIds);
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        return collect();
    }
    
    /**
     * Проверить доступность пользователя для менеджера
     */
    private function checkUserAvailability($currentUserId, $targetUserId)
    {
        $currentUser = User::find($currentUserId);
        $targetUser = User::find($targetUserId);
        
        if (!$currentUser || !$targetUser) {
            return false;
        }
        
        // Админ может выдавать лимиты кому угодно (включая себя и менеджеров)
        if ($currentUser->isAdmin()) {
            // Проверяем только что пользователь активен
            return $targetUser->is_active;
        }
        
        // Менеджер может выдавать лимиты только владельцам и сотрудникам своих организаций
        if ($currentUser->isManager()) {
            if (!$targetUser->isOrgOwner() && !$targetUser->isOrgMember()) {
                return false;
            }
            
            // Получаем организации менеджера через связь с Manager
            $organizations = Organization::whereHas('manager', function($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            })->get();
            
            if ($organizations->isEmpty()) {
                return false;
            }
            
            $organizationIds = $organizations->pluck('id')->toArray();
            
            // Проверяем, связан ли пользователь с организациями менеджера
            if ($targetUser->isOrgOwner()) {
                $ownerProfile = $targetUser->orgOwnerProfile;
                return $ownerProfile && in_array($ownerProfile->organization_id, $organizationIds);
            }
            
            if ($targetUser->isOrgMember()) {
                $memberProfile = $targetUser->orgMemberProfile;
                return $memberProfile && in_array($memberProfile->organization_id, $organizationIds);
            }
            
            return false;
        }
        
        return false;
    }

    public function delegate(Request $request, Limit $limit)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1|max:' . $limit->getAvailableQuantity(),
        ]);

        // Проверяем доступность лимита
        if ($limit->getAvailableQuantity() < $request->quantity) {
            return back()->with('error', 'Недостаточно доступного лимита для делегирования');
        }

        // Проверяем, не делегируем ли самому себе
        if ($limit->user_id == $request->user_id) {
            return back()->with('error', 'Нельзя делегировать лимит самому себе');
        }

        // Используем лимит из основного пула
        $limit->useQuantity($request->quantity);

        // Создаем или обновляем делегированный лимит
        DelegatedLimit::createOrUpdateDelegatedLimit(
            $request->user_id,
            $limit->id,
            $request->quantity
        );

        return back()->with('success', 'Лимит успешно делегирован');
    }
}