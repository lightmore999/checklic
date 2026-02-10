<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrgMemberProfile;
use App\Models\Limit;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\DelegatedLimit; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrgMemberController extends Controller
{
    /**
     * Профиль сотрудника (самого себя)
     */
    public function profile()
    {
        $user = Auth::user();
        $memberProfile = $user->orgMemberProfile;
        
        if (!$memberProfile) {
            return view('org-members.profile', compact('user'));
        }
        
        $organization = $memberProfile->organization;
        
        // Получаем делегированные лимиты сотрудника
        $delegatedLimits = DelegatedLimit::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['limit.reportType'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Общая статистика по лимитам
        $totalDelegated = $delegatedLimits->sum('quantity');
        $totalUsed = $delegatedLimits->sum('used_quantity');
        $totalAvailable = $totalDelegated - $totalUsed;
        
        // Статистика по типам отчетов
        $limitsByType = [];
        foreach ($delegatedLimits as $delegated) {
            $reportTypeName = $delegated->limit->reportType->name ?? 'Без типа';
            if (!isset($limitsByType[$reportTypeName])) {
                $limitsByType[$reportTypeName] = [
                    'delegated' => 0,
                    'used' => 0,
                    'available' => 0,
                    'count' => 0
                ];
            }
            
            $limitsByType[$reportTypeName]['delegated'] += $delegated->quantity;
            $limitsByType[$reportTypeName]['used'] += $delegated->used_quantity;
            $limitsByType[$reportTypeName]['available'] += ($delegated->quantity - $delegated->used_quantity);
            $limitsByType[$reportTypeName]['count']++;
        }
        
        return view('org-members.profile', compact(
            'user',
            'memberProfile',
            'organization',
            'delegatedLimits',
            'totalDelegated',
            'totalUsed',
            'totalAvailable',
            'limitsByType'
        ));
    }
    
    /**
     * Форма редактирования профиля сотрудника (самого себя)
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('org-members.edit-profile', compact('user'));
    }
    
    /**
     * Обновление профиля сотрудника (самого себя)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
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
        
        return redirect()->route('member.profile')
            ->with('success', 'Профиль успешно обновлен');
    }
    
    /**
     * Форма создания сотрудника (для админа/менеджера/владельца)
     */
    public function create($organizationId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        // Определяем префикс маршрута
        $routePrefix = $this->getRoutePrefix($user);
        
        return view('org-members.create', compact('user', 'organization', 'routePrefix'));
    }
    
    /**
     * Сохранение сотрудника (для админа/менеджера/владельца)
     */
    public function store(Request $request, $organizationId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        $validated = $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Создаем пользователя для сотрудника
            $memberUser = User::create([
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
                'password' => Hash::make($validated['user']['password']),
                'role' => 'org_member',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            // Автоматически определяем начальника (владелец организации)
            $bossId = $organization->owner->user_id ?? null;
            
            // Создаем профиль сотрудника
            OrgMemberProfile::create([
                'user_id' => $memberUser->id,
                'organization_id' => $organization->id,
                'boss_id' => $bossId,
                'manager_id' => $organization->manager->user_id,
                'is_active' => true,
            ]);
            
            DB::commit();
            
            // Определяем префикс маршрута для редиректа
            $routePrefix = $this->getRoutePrefix($user);
            
            // Редирект в зависимости от роли
            if ($user->isOrgOwner()) {
                return redirect()->route('owner.dashboard')
                    ->with('success', 'Сотрудник успешно добавлен в организацию');
            }
            
            return redirect()->route($routePrefix . 'organization.show', $organization->id)
                ->with('success', 'Сотрудник успешно добавлен в организацию');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании сотрудника: ' . $e->getMessage());
        }
    }
    
    /**
     * Просмотр профиля сотрудника (для админа/менеджера/владельца)
     */
    public function show($organizationId, $memberId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        // Получаем сотрудника с проверкой, что он принадлежит организации
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with(['user', 'boss', 'manager'])
            ->firstOrFail();
        
        // Получаем делегированные лимиты сотрудника
        $delegatedLimits = DelegatedLimit::where('user_id', $member->user_id)
            ->where('is_active', true)
            ->with(['limit.reportType'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Статистика по лимитам
        $totalDelegated = $delegatedLimits->sum('quantity');
        $totalUsed = $delegatedLimits->sum('used_quantity');
        $totalAvailable = $totalDelegated - $totalUsed;
        
        // Группировка по типам отчетов
        $limitsByType = [];
        foreach ($delegatedLimits as $delegated) {
            $reportTypeName = $delegated->limit->reportType->name ?? 'Без типа';
            if (!isset($limitsByType[$reportTypeName])) {
                $limitsByType[$reportTypeName] = [
                    'delegated' => 0,
                    'used' => 0,
                    'available' => 0,
                    'count' => 0
                ];
            }
            
            $limitsByType[$reportTypeName]['delegated'] += $delegated->quantity;
            $limitsByType[$reportTypeName]['used'] += $delegated->used_quantity;
            $limitsByType[$reportTypeName]['available'] += ($delegated->quantity - $delegated->used_quantity);
            $limitsByType[$reportTypeName]['count']++;
        }
        
        // ДОБАВЛЯЕМ СТАТИСТИКУ ПО ОТЧЕТАМ
        $reports = Report::where('user_id', $member->user_id)->get();
        
        $totalReports = $reports->count();
        $thisMonthReports = $reports->where('created_at', '>=', now()->startOfMonth())->count();
        $inProgressReports = $reports->whereIn('status', ['pending', 'processing'])->count();
        $completedReports = $reports->where('status', 'completed')->count();
        
        // Статистика по типам отчетов
        $reportsByType = $reports->groupBy('report_type_id')->map(function($group) {
            return [
                'count' => $group->count(),
                'completed' => $group->where('status', 'completed')->count(),
                'pending' => $group->whereIn('status', ['pending', 'processing'])->count()
            ];
        });
        
        // Получаем названия типов отчетов
        $reportTypes = ReportType::whereIn('id', $reportsByType->keys())->pluck('name', 'id');
        
        // Определяем префикс маршрута для редиректов в представлениях
        $routePrefix = $this->getRoutePrefix($user);
        
        return view('org-members.show', compact(
            'user', 
            'organization', 
            'member', 
            'routePrefix',
            'delegatedLimits',
            'totalDelegated',
            'totalUsed',
            'totalAvailable',
            'limitsByType',
            'totalReports',
            'thisMonthReports',
            'inProgressReports',
            'completedReports',
            'reportsByType',
            'reportTypes'
        ));
    }
    
    /**
     * Форма редактирования сотрудника (для админа/менеджера/владельца)
     */
    public function edit($organizationId, $memberId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        // Определяем префикс маршрута
        $routePrefix = $this->getRoutePrefix($user);
        
        return view('org-members.edit', compact('user', 'organization', 'member', 'routePrefix'));
    }
    
    /**
     * Обновление сотрудника (для админа/менеджера/владельца)
     */
    public function update(Request $request, $organizationId, $memberId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        $validated = $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email,' . $member->user_id,
            'is_active' => 'boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Обновляем пользователя
            $member->user->update([
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
            ]);
            
            // Обновляем профиль сотрудника
            $member->update([
                'is_active' => $validated['is_active'] ?? $member->is_active,
            ]);
            
            DB::commit();
            
            $routePrefix = $this->getRoutePrefix($user);
            
            return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
                ->with('success', 'Данные сотрудника обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при обновлении: ' . $e->getMessage());
        }
    }
    
    /**
     * Изменение пароля сотрудника (для админа/менеджера/владельца)
     */
    public function changePassword(Request $request, $organizationId, $memberId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $member->user->update([
            'password' => Hash::make($validated['password']),
        ]);
        
        $routePrefix = $this->getRoutePrefix($user);
        
        return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
            ->with('success', 'Пароль сотрудника изменен');
    }
    
    /**
     * Изменение статуса сотрудника (для админа/менеджера/владельца)
     */
    public function toggleStatus(Request $request, $organizationId, $memberId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->firstOrFail();
        
        $member->is_active = !$member->is_active;
        $member->save();
        
        $status = $member->is_active ? 'активирован' : 'деактивирован';
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Сотрудник {$status}",
                'is_active' => $member->is_active,
            ]);
        }
        
        $routePrefix = $this->getRoutePrefix($user);
        
        return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
            ->with('success', "Сотрудник {$status}");
    }
    
    /**
     * Удаление сотрудника (для админа/менеджера/владельца)
     */
    public function destroy($organizationId, $memberId)
    {
        $user = Auth::user();
        
        // Проверяем доступ к организации
        $organization = $this->getOrganizationWithAccess($user, $organizationId);
        
        $member = OrgMemberProfile::where('id', $memberId)
            ->where('organization_id', $organizationId)
            ->with('user')
            ->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            $memberUser = $member->user;
            $member->delete();
            $memberUser->delete();
            
            DB::commit();
            
            $routePrefix = $this->getRoutePrefix($user);
            
            return redirect()->route($routePrefix . 'organization.show', $organization->id)
                ->with('success', 'Сотрудник успешно удален');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            $routePrefix = $this->getRoutePrefix($user);
            
            return redirect()->route($routePrefix . 'org-members.show', [$organization->id, $member->id])
                ->with('error', 'Произошла ошибка при удалении: ' . $e->getMessage());
        }
    }
    
    /**
     * Получить организацию с проверкой доступа пользователя
     */
    private function getOrganizationWithAccess($user, $organizationId)
    {
        if ($user->isAdmin()) {
            return Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('admin_id', $user->id);
                })
                ->with(['owner.user', 'manager.user'])
                ->firstOrFail();
        } elseif ($user->isManager()) {
            return Organization::where('id', $organizationId)
                ->whereHas('manager', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['owner.user', 'manager.user'])
                ->firstOrFail();
        } else {
            // Для владельца организации
            return Organization::where('id', $organizationId)
                ->whereHas('owner', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['owner.user', 'manager.user'])
                ->firstOrFail();
        }
    }
    
    /**
     * Получить префикс маршрута на основе роли пользователя
     */
    private function getRoutePrefix($user)
    {
        return match($user->role) {
            'admin' => 'admin.',
            'manager' => 'manager.',
            'org_owner' => 'owner.',
            default => 'admin.'
        };
    }
}