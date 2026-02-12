<?php

namespace App\Http\Controllers;

use App\Models\DelegatedLimit;
use App\Models\Limit;
use App\Models\User;
use App\Models\ReportType;
use App\Models\OrgMemberProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DelegatedLimitController extends Controller
{
    /**
     * Список всех делегированных лимитов
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Начинаем запрос с подгрузкой связей
        $query = DelegatedLimit::with(['user', 'limit.reportType', 'limit.user'])
            ->orderBy('created_at', 'desc');
        
        // Фильтрация по типу пользователя
        if ($user->isAdmin() || $user->isManager()) {
            // Админ и менеджер видят все делегированные лимиты
            // (можно добавить фильтрацию для менеджера по его организациям)
        } elseif ($user->isOrgOwner()) {
            // Владелец видит лимиты, которые он делегировал своим сотрудникам
            $query->whereHas('limit', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->isOrgMember()) {
            // Сотрудник видит только свои делегированные лимиты
            $query->where('user_id', $user->id);
        }
        
        // Фильтрация по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Фильтрация по владельцу лимита
        if ($request->filled('owner_id')) {
            $query->whereHas('limit', function($q) use ($request) {
                $q->where('user_id', $request->owner_id);
            });
        }
        
        // Фильтрация по типу отчета
        if ($request->filled('report_type_id')) {
            $query->whereHas('limit', function($q) use ($request) {
                $q->where('report_type_id', $request->report_type_id);
            });
        }
        
        // Фильтрация по статусу
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true)->where('quantity', '>', 0);
            } elseif ($request->status == 'exhausted') {
                $query->where('quantity', '<=', 0);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $delegatedLimits = $query->paginate(20);
        
        // Данные для фильтров
        $users = $this->getAvailableUsers($user);
        $reportTypes = ReportType::all();
        
        return view('delegated-limits.index', compact('delegatedLimits', 'users', 'reportTypes'));
    }
    
    /**
     * Форма делегирования лимита
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Получаем лимиты, которые можно делегировать
        $limits = $this->getAvailableLimits($user);
        
        if ($limits->isEmpty()) {
            return redirect()->route('delegated-limits.index')
                ->with('warning', 'У вас нет доступных лимитов для делегирования');
        }
        
        // Получаем пользователей, которым можно делегировать
        $availableUsers = $this->getAvailableDelegationUsers($user);
        
        if ($availableUsers->isEmpty()) {
            return redirect()->route('delegated-limits.index')
                ->with('warning', 'Нет доступных пользователей для делегирования лимитов');
        }
        
        // Если передан limit_id в запросе, выбираем этот лимит по умолчанию
        $selectedLimitId = $request->get('limit_id');
        
        return view('delegated-limits.create', compact('limits', 'availableUsers', 'selectedLimitId'));
    }
    
    /**
     * Сохранить делегированный лимит
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'limit_id' => 'required|exists:limits,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'owner_id' => 'nullable|exists:users,id', // Добавлено для делегирования от имени владельца
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Получаем лимит
        $limit = Limit::findOrFail($request->limit_id);
        
        // Определяем ID владельца лимита (того, чей лимит используется)
        $ownerId = $limit->user_id;
        
        // Проверяем права на делегирование
        $canDelegate = false;
        
        if ($user->id == $ownerId) {
            // Сам владелец может делегировать
            $canDelegate = true;
        } elseif ($user->isAdmin()) {
            // Админ может делегировать любой лимит
            $canDelegate = true;
        } elseif ($user->isManager()) {
            // Менеджер может делегировать, если он является менеджером организации владельца
            $owner = User::find($ownerId);
            if ($owner && $owner->orgOwnerProfile) {
                $organizationId = $owner->orgOwnerProfile->organization_id;
                $canDelegate = Organization::where('id', $organizationId)
                    ->whereHas('manager', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->exists();
            }
        }
        
        if (!$canDelegate) {
            return redirect()->back()
                ->with('error', 'У вас нет прав для делегирования этого лимита')
                ->withInput();
        }
        
        // Проверяем, что сотрудник доступен для делегирования
        $targetUser = User::find($request->user_id);
        if (!$targetUser || !$targetUser->isOrgMember() || !$targetUser->is_active) {
            return redirect()->back()
                ->with('error', 'Некорректный сотрудник для делегирования')
                ->withInput();
        }
        
        // Проверяем, что сотрудник относится к той же организации, что и владелец лимита
        $owner = User::find($ownerId);
        if ($owner && $owner->orgOwnerProfile) {
            $organizationId = $owner->orgOwnerProfile->organization_id;
            
            $isValidEmployee = OrgMemberProfile::where('user_id', $targetUser->id)
                ->where('organization_id', $organizationId)
                ->where('boss_id', $ownerId)
                ->where('is_active', true)
                ->exists();
            
            if (!$isValidEmployee) {
                return redirect()->back()
                    ->with('error', 'Этот сотрудник не относится к организации владельца лимита')
                    ->withInput();
            }
        }
        
        // Проверяем, что у владельца достаточно лимита
        if ($limit->quantity < $request->quantity) {
            return redirect()->back()
                ->with('error', 'Недостаточно лимита для делегирования. Доступно: ' . $limit->quantity)
                ->withInput();
        }
        
        try {
            // Создаем делегированный лимит
            $delegatedLimit = DelegatedLimit::createOrUpdateDelegatedLimit(
                $request->user_id,
                $request->limit_id,
                $request->quantity
            );
            
            // Уменьшаем оригинальный лимит
            $limit->decrementLimit($request->quantity);
            
            // Редирект в зависимости от переданного параметра
            if ($request->has('redirect_to_organization')) {
                $organizationId = $request->redirect_to_organization;
                
                if ($user->isAdmin()) {
                    return redirect()->route('admin.organization.show', $organizationId)
                        ->with('success', 'Лимит успешно делегирован');
                } elseif ($user->isManager()) {
                    return redirect()->route('manager.organization.show', $organizationId)
                        ->with('success', 'Лимит успешно делегирован');
                }
            }
            
            // Редирект по умолчанию
            if ($user->isOrgOwner()) {
                return redirect()->route('owner.dashboard')
                    ->with('success', 'Лимит успешно делегирован');
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Лимит успешно делегирован');
            } elseif ($user->isManager()) {
                return redirect()->route('manager.dashboard')
                    ->with('success', 'Лимит успешно делегирован');
            }
            
            return redirect()->back()->with('success', 'Лимит успешно делегирован');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Просмотр делегированного лимита
     */
    public function show(DelegatedLimit $delegatedLimit)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        if (!$this->canViewDelegatedLimit($user->id, $delegatedLimit)) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('delegated-limits.show', compact('delegatedLimit'));
    }
    
    /**
     * Форма редактирования делегированного лимита
     */
    public function edit(DelegatedLimit $delegatedLimit)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        if (!$this->canEditDelegatedLimit($user->id, $delegatedLimit)) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('delegated-limits.edit', compact('delegatedLimit'));
    }
    
    /**
     * Обновить делегированный лимит
     */
    public function update(Request $request, DelegatedLimit $delegatedLimit)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        if (!$this->canEditDelegatedLimit($user->id, $delegatedLimit)) {
            abort(403, 'Доступ запрещен');
        }
        
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $delegatedLimit->update([
                'quantity' => $request->quantity,
                'is_active' => $request->has('is_active'),
            ]);
            
            // Редирект для админа (только админ может редактировать)
            return redirect()->route('admin.dashboard')
                ->with('success', 'Делегированный лимит успешно обновлен');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Удалить делегированный лимит (возврат лимита владельцу)
     */
    public function destroy(DelegatedLimit $delegatedLimit)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        $ownerId = $delegatedLimit->limit->user_id;
        $canDelete = false;
        
        if ($user->id == $ownerId) {
            // Владелец может удалять свои делегированные лимиты
            $canDelete = true;
        } elseif ($user->isAdmin()) {
            // Админ может удалять любые делегированные лимиты
            $canDelete = true;
        } elseif ($user->isManager()) {
            // Менеджер может удалять делегированные лимиты в своих организациях
            $owner = User::find($ownerId);
            if ($owner && $owner->orgOwnerProfile) {
                $organizationId = $owner->orgOwnerProfile->organization_id;
                $canDelete = Organization::where('id', $organizationId)
                    ->whereHas('manager', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->exists();
            }
        }
        
        if (!$canDelete) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            // Возвращаем лимит владельцу
            $delegatedLimit->limit->incrementLimit($delegatedLimit->quantity);
            
            // Удаляем делегированный лимит
            $delegatedLimit->delete();
            
            // Редирект в зависимости от переданного параметра
            if (request()->has('redirect_to_organization')) {
                $organizationId = request()->redirect_to_organization;
                
                if ($user->isAdmin()) {
                    return redirect()->route('admin.organization.show', $organizationId)
                        ->with('success', 'Делегированный лимит удален. Лимит возвращен владельцу.');
                } elseif ($user->isManager()) {
                    return redirect()->route('manager.organization.show', $organizationId)
                        ->with('success', 'Делегированный лимит удален. Лимит возвращен владельцу.');
                }
            }
            
            // Редирект по умолчанию
            if ($user->isOrgOwner()) {
                return redirect()->route('owner.dashboard')
                    ->with('success', 'Делегированный лимит удален. Лимит возвращен владельцу.');
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Делегированный лимит удален. Лимит возвращен владельцу.');
            } elseif ($user->isManager()) {
                return redirect()->route('manager.dashboard')
                    ->with('success', 'Делегированный лимит удален. Лимит возвращен владельцу.');
            }
            
            return redirect()->back()
                ->with('success', 'Делегированный лимит удален. Лимит возвращен владельцу.');
            
        } catch (\Exception $e) {
            if ($user->isOrgOwner()) {
                return redirect()->route('owner.dashboard')
                    ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
            } elseif ($user->isManager()) {
                return redirect()->route('manager.dashboard')
                    ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
            }
            
            return redirect()->back()
                ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }
    
    /**
     * Получить лимиты, доступные для делегирования
     */
    private function getAvailableLimits(User $currentUser)
    {
        if ($currentUser->isAdmin() || $currentUser->isManager()) {
            // Админ и менеджер могут делегировать любые лимиты владельцев организаций
            return Limit::whereHas('user', function($query) {
                    $query->whereIn('role', ['org_owner']);
                })
                ->where('quantity', '>', 0)
                ->with(['user', 'reportType'])
                ->orderBy('date_created', 'desc')
                ->get();
        }
        
        if ($currentUser->isOrgOwner()) {
            // Владелец может делегировать только свои лимиты
            return Limit::where('user_id', $currentUser->id)
                ->where('quantity', '>', 0)
                ->with(['reportType'])
                ->orderBy('date_created', 'desc')
                ->get();
        }
        
        return collect();
    }
    
    /**
     * Получить пользователей, которым можно делегировать лимиты
     */
    private function getAvailableDelegationUsers(User $currentUser)
    {
        if ($currentUser->isAdmin()) {
            // Админ может делегировать любому сотруднику
            return User::where('role', 'org_member')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        if ($currentUser->isManager()) {
            // Менеджер может делегировать сотрудникам своих организаций
            return User::where(function($query) use ($currentUser) {
                    $query->whereHas('orgMemberProfile.organization', function($q) use ($currentUser) {
                        $q->whereHas('manager', function($managerQuery) use ($currentUser) {
                            $managerQuery->where('user_id', $currentUser->id);
                        });
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        if ($currentUser->isOrgOwner()) {
            // Владелец может делегировать только своим сотрудникам
            $organizationId = $currentUser->orgOwnerProfile->organization_id ?? null;
            
            if (!$organizationId) {
                return collect();
            }
            
            return User::whereHas('orgMemberProfile', function($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId)
                        ->where('boss_id', Auth::id());
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        return collect();
    }
    
    /**
     * Получить пользователей для фильтра (общий список)
     */
    private function getAvailableUsers(User $currentUser)
    {
        if ($currentUser->isAdmin()) {
            return User::whereIn('role', ['org_owner', 'org_member', 'manager'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        if ($currentUser->isManager()) {
            return User::where(function($query) use ($currentUser) {
                    $query->whereHas('orgOwnerProfile.organization', function($q) use ($currentUser) {
                        $q->whereHas('manager', function($managerQuery) use ($currentUser) {
                            $managerQuery->where('user_id', $currentUser->id);
                        });
                    })
                    ->orWhereHas('orgMemberProfile.organization', function($q) use ($currentUser) {
                        $q->whereHas('manager', function($managerQuery) use ($currentUser) {
                            $managerQuery->where('user_id', $currentUser->id);
                        });
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        if ($currentUser->isOrgOwner()) {
            $organizationId = $currentUser->orgOwnerProfile->organization_id ?? null;
            
            if (!$organizationId) {
                return collect();
            }
            
            // Владелец видит себя и своих сотрудников
            return User::where(function($query) use ($organizationId, $currentUser) {
                    $query->where('id', $currentUser->id)
                        ->orWhereHas('orgMemberProfile', function($q) use ($organizationId, $currentUser) {
                            $q->where('organization_id', $organizationId)
                                ->where('boss_id', $currentUser->id);
                        });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        return collect();
    }
    
    /**
     * Проверить, можно ли делегировать лимит
     */
    private function canDelegateLimit($userId, $limitId)
    {
        $user = User::find($userId);
        $limit = Limit::with('user')->find($limitId);
        
        if (!$user || !$limit) {
            return false;
        }
        
        // Админ может делегировать любые лимиты владельцев
        if ($user->isAdmin()) {
            return $limit->user->isOrgOwner();
        }
        
        // Менеджер может делегировать лимиты владельцев своих организаций
        if ($user->isManager()) {
            if (!$limit->user->isOrgOwner()) {
                return false;
            }
            
            // Проверяем, что владелец лимита из организации менеджера
            $ownerOrganizationId = $limit->user->orgOwnerProfile->organization_id ?? null;
            if (!$ownerOrganizationId) {
                return false;
            }
            
            return Organization::where('id', $ownerOrganizationId)
                ->whereHas('manager', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->exists();
        }
        
        // Владелец может делегировать только свои лимиты
        if ($user->isOrgOwner()) {
            return $limit->user_id == $userId;
        }
        
        return false;
    }
    
    /**
     * Проверить, можно ли делегировать пользователю
     */
    private function canDelegateToUser($delegatorId, $targetUserId)
    {
        $delegator = User::find($delegatorId);
        $targetUser = User::find($targetUserId);
        
        if (!$delegator || !$targetUser) {
            return false;
        }
        
        // Нельзя делегировать самому себе
        if ($delegatorId == $targetUserId) {
            return false;
        }
        
        // Админ может делегировать только сотрудникам
        if ($delegator->isAdmin()) {
            return $targetUser->isOrgMember() && $targetUser->is_active;
        }
        
        // Менеджер может делегировать сотрудникам своих организаций
        if ($delegator->isManager()) {
            if (!$targetUser->isOrgMember() || !$targetUser->is_active) {
                return false;
            }
            
            // Проверяем, что сотрудник из организации менеджера
            $memberOrganizationId = $targetUser->orgMemberProfile->organization_id ?? null;
            if (!$memberOrganizationId) {
                return false;
            }
            
            return Organization::where('id', $memberOrganizationId)
                ->whereHas('manager', function($query) use ($delegatorId) {
                    $query->where('user_id', $delegatorId);
                })
                ->exists();
        }
        
        // Владелец может делегировать только своим сотрудникам
        if ($delegator->isOrgOwner()) {
            if (!$targetUser->isOrgMember() || !$targetUser->is_active) {
                return false;
            }
            
            $memberProfile = $targetUser->orgMemberProfile;
            return $memberProfile && 
                   $memberProfile->organization_id == $delegator->orgOwnerProfile->organization_id &&
                   $memberProfile->boss_id == $delegatorId;
        }
        
        return false;
    }
    
    /**
     * Проверить, можно ли просматривать делегированный лимит
     */
    private function canViewDelegatedLimit($userId, DelegatedLimit $delegatedLimit)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Админ может просматривать все
        if ($user->isAdmin()) {
            return true;
        }
        
        // Менеджер может просматривать делегированные лимиты в своих организациях
        if ($user->isManager()) {
            $ownerId = $delegatedLimit->limit->user_id ?? null;
            if (!$ownerId) {
                return false;
            }
            
            $owner = User::find($ownerId);
            if (!$owner || !$owner->isOrgOwner()) {
                return false;
            }
            
            $ownerOrganizationId = $owner->orgOwnerProfile->organization_id ?? null;
            if (!$ownerOrganizationId) {
                return false;
            }
            
            return Organization::where('id', $ownerOrganizationId)
                ->whereHas('manager', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->exists();
        }
        
        // Владелец может просматривать делегированные им лимиты
        if ($user->isOrgOwner()) {
            return $delegatedLimit->limit->user_id == $userId;
        }
        
        // Сотрудник может просматривать только свои делегированные лимиты
        if ($user->isOrgMember()) {
            return $delegatedLimit->user_id == $userId;
        }
        
        return false;
    }
    
    /**
     * Проверить, можно ли редактировать делегированный лимит
     */
    private function canEditDelegatedLimit($userId, DelegatedLimit $delegatedLimit)
    {
        // Только админ может редактировать делегированные лимиты
        $user = User::find($userId);
        return $user && $user->isAdmin();
    }
    
    /**
     * Проверить, можно ли удалить делегированный лимит
     */
    private function canDeleteDelegatedLimit($userId, DelegatedLimit $delegatedLimit)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Админ может удалять любые делегированные лимиты
        if ($user->isAdmin()) {
            return true;
        }
        
        // Владелец может удалять (возвращать) только свои делегированные лимиты
        if ($user->isOrgOwner()) {
            return $delegatedLimit->limit->user_id == $userId;
        }
        
        // Менеджер может удалять делегированные лимиты в своих организациях
        if ($user->isManager()) {
            $ownerId = $delegatedLimit->limit->user_id ?? null;
            if (!$ownerId) {
                return false;
            }
            
            $owner = User::find($ownerId);
            if (!$owner || !$owner->isOrgOwner()) {
                return false;
            }
            
            $ownerOrganizationId = $owner->orgOwnerProfile->organization_id ?? null;
            if (!$ownerOrganizationId) {
                return false;
            }
            
            return Organization::where('id', $ownerOrganizationId)
                ->whereHas('manager', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->exists();
        }
        
        return false;
    }
}