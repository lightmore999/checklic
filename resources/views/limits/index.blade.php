@extends('layouts.app')

@section('title', 'Управление отчетами')
@section('page-icon', 'bi-file-text')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h3 class="card-title h5 mb-0">
                        <i class="bi bi-file-text text-primary me-2"></i>
                        Отчеты
                    </h3>
                </div>
                
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" class="mb-4" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Организация</label>
                                <select name="organization_id" class="form-select select2-organization">
                                    <option value="">Все организации</option>
                                    @foreach($organizations ?? [] as $org)
                                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Пользователь</label>
                                <select name="user_id" class="form-select select2-user" data-placeholder="Поиск пользователя...">
                                    <option value="">Все пользователи</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }}) - {{ $user->getRoleDisplayName() ?? 'Нет роли' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Можно ввести имя или email для поиска</small>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Тип отчета</label>
                                <select name="report_type_id" class="form-select">
                                    <option value="">Все типы</option>
                                    @foreach($reportTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Дата</label>
                                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="">Все</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                                    <option value="exhausted" {{ request('status') == 'exhausted' ? 'selected' : '' }}>Исчерпанные</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mt-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-info">
                                        <i class="bi bi-funnel me-1"></i> Применить фильтры
                                    </button>
                                    <a href="{{ route('limits.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Активные фильтры -->
                    @if(request()->anyFilled(['organization_id', 'user_id', 'report_type_id', 'date', 'status']))
                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <i class="bi bi-funnel me-1"></i>
                                <span>Активные фильтры:</span>
                                @if(request('organization_id'))
                                    @php 
                                        $org = isset($organizations) ? $organizations->firstWhere('id', request('organization_id')) : null; 
                                    @endphp
                                    <span class="badge bg-info text-white">Организация: {{ $org->name ?? 'ID: ' . request('organization_id') }}</span>
                                @endif
                                @if(request('user_id'))
                                    @php 
                                        $usr = $users->firstWhere('id', request('user_id')); 
                                    @endphp
                                    <span class="badge bg-info text-white">Пользователь: {{ $usr->name ?? 'ID: ' . request('user_id') }}</span>
                                @endif
                                @if(request('report_type_id'))
                                    @php 
                                        $type = $reportTypes->firstWhere('id', request('report_type_id')); 
                                    @endphp
                                    <span class="badge bg-info text-white">Тип отчета: {{ $type->name ?? 'ID: ' . request('report_type_id') }}</span>
                                @endif
                                @if(request('date'))
                                    <span class="badge bg-info text-white">Дата: {{ \Carbon\Carbon::parse(request('date'))->format('d.m.Y') }}</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge bg-info text-white">Статус: {{ request('status') == 'active' ? 'Активные' : 'Исчерпанные' }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Таблица лимитов -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Пользователь</th>
                                    <th>Организация</th>
                                    <th>Подписка</th>
                                    <th>Создатель</th>
                                    <th>Тип отчета</th>
                                    <th>Кол-во</th>
                                    <th>Использовано</th>
                                    <th>Доступно</th>
                                    <th>Делегировано</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Создан</th>
                                    <th class="text-center">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($limits as $limit)
                                    @php
                                        // Получаем пользователя через подписку
                                        $user = $limit->subscription->user ?? null;
                                        $subscription = $limit->subscription;
                                        
                                        // Определяем организацию пользователя
                                        $userOrg = null;
                                        if ($user) {
                                            if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                                $userOrg = $user->orgOwnerProfile->organization;
                                            } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                                $userOrg = $user->orgMemberProfile->organization;
                                            }
                                        }
                                        
                                        // Статус подписки
                                        $subscriptionStatus = $subscription ? $subscription->status : null;
                                        $subscriptionStatusClass = $subscriptionStatus === 'active' ? 'success' : 
                                                                  ($subscriptionStatus === 'expired' ? 'danger' : 
                                                                  ($subscriptionStatus === 'pending' ? 'warning' : 'secondary'));
                                        $subscriptionStatusText = $subscription ? $subscription->getStatusTextAttribute() : 'Нет подписки';
                                        
                                        // Определяем маршрут к профилю пользователя
                                        $userProfileRoute = null;
                                        if ($user) {
                                            if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                                $userProfileRoute = route('admin.organization.show', $user->orgOwnerProfile->organization_id);
                                            } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                                $userProfileRoute = route('admin.org-members.show', [
                                                    $user->orgMemberProfile->organization_id,
                                                    $user->orgMemberProfile->id
                                                ]);
                                            } elseif ($user->isManager()) {
                                                $userProfileRoute = route('admin.managers.show', $user->id);
                                            }
                                        }
                                        
                                        // Определяем маршрут к профилю создателя
                                        $creator = $limit->creator;
                                        $creatorProfileRoute = null;
                                        if ($creator) {
                                            if ($creator->isOrgOwner() && $creator->orgOwnerProfile) {
                                                $creatorProfileRoute = route('admin.organization.show', $creator->orgOwnerProfile->organization_id);
                                            } elseif ($creator->isOrgMember() && $creator->orgMemberProfile) {
                                                $creatorProfileRoute = route('admin.org-members.show', [
                                                    $creator->orgMemberProfile->organization_id,
                                                    $creator->orgMemberProfile->id
                                                ]);
                                            } elseif ($creator->isManager()) {
                                                $creatorProfileRoute = route('admin.managers.show', $creator->id);
                                            } elseif ($creator->isAdmin()) {
                                                $creatorProfileRoute = route('admin.dashboard');
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">#{{ $limit->id }}</td>
                                        <td>
                                            @if($user)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-{{ $user->getRoleColor() ?? 'secondary' }} d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px; color: white; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        @if($userProfileRoute)
                                                            <a href="{{ $userProfileRoute }}" class="fw-bold text-decoration-none">
                                                                {{ $user->name }}
                                                            </a>
                                                        @else
                                                            <div class="fw-bold">{{ $user->name }}</div>
                                                        @endif
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                        <div>
                                                            <span class="badge bg-{{ $user->getRoleColor() ?? 'secondary' }}" style="font-size: 0.7rem;">
                                                                {{ $user->getRoleDisplayName() ?? 'Нет роли' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">Пользователь удален</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($userOrg)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                        <i class="bi bi-building"></i>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('admin.organization.show', $userOrg->id) }}" class="text-decoration-none">
                                                            {{ $userOrg->name }}
                                                        </a>
                                                        @if($user && $user->isOrgOwner())
                                                            <small class="badge bg-primary">Руководитель</small>
                                                        @elseif($user && $user->isOrgMember())
                                                            <small class="badge bg-info">Сотрудник</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @elseif($user && $user->isManager())
                                                <span class="badge bg-secondary">Менеджер</span>
                                            @else
                                                <span class="text-muted fst-italic">Не указана</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subscription)
                                                <div>
                                                    <span class="badge bg-{{ $subscriptionStatusClass }} mb-1">
                                                        {{ $subscriptionStatusText }}
                                                    </span>
                                                    @if($subscription->ends_at)
                                                        <div><small>до {{ $subscription->ends_at->format('d.m.Y') }}</small></div>
                                                        @if($subscription->isExpiringSoon())
                                                            <span class="badge bg-warning mt-1">скоро</span>
                                                        @endif
                                                    @else
                                                        <div><small class="text-muted">бессрочно</small></div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">Нет подписки</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($creator)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                        {{ strtoupper(substr($creator->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        @if($creatorProfileRoute)
                                                            <a href="{{ $creatorProfileRoute }}" class="text-decoration-none">
                                                                {{ $creator->name }}
                                                            </a>
                                                        @else
                                                            <div>{{ $creator->name }}</div>
                                                        @endif
                                                        <small class="text-muted">{{ $creator->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="bi bi-robot"></i> Система
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-text text-info me-1"></i>
                                                <strong>{{ $limit->reportType->name ?? 'Не указан' }}</strong>
                                            </div>
                                            @if($limit->reportType && $limit->reportType->only_api)
                                                <span class="badge bg-warning mt-1" style="font-size: 0.7rem;">только API</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $limit->quantity }} шт.
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $limit->used_quantity }} шт.
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $limit->getAvailableQuantity() > 0 ? 'success' : 'danger' }}">
                                                {{ $limit->getAvailableQuantity() }} шт.
                                            </span>
                                        </td>
                                        <td>
                                            @if($limit->delegatedVersions && $limit->delegatedVersions->count() > 0)
                                                <div class="delegated-info">
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse" 
                                                            data-bs-target="#delegated-{{ $limit->id }}">
                                                        <i class="bi bi-share"></i> 
                                                        {{ $limit->delegatedVersions->count() }}
                                                    </button>
                                                    <div class="mt-2 small">
                                                        <div>Всего: <strong>{{ $limit->delegatedVersions->sum('quantity') }}</strong> шт.</div>
                                                        <div>Использовано: <strong>{{ $limit->delegatedVersions->sum('used_quantity') }}</strong> шт.</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $limit->date_created ? $limit->date_created->format('d.m.Y') : '—' }}</td>
                                        <td>
                                            @if($limit->isExhausted())
                                                <span class="badge bg-danger">Исчерпан</span>
                                            @else
                                                <span class="badge bg-success">Активен</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $limit->created_at ? $limit->created_at->format('d.m.Y') : '' }}<br>
                                            <small class="text-muted">{{ $limit->created_at ? $limit->created_at->format('H:i') : '' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                @if(auth()->user()->isAdmin())
                                                    <form action="{{ route('limits.destroy', $limit) }}" method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Вы уверены, что хотите удалить этот отчет?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Удалить">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($user && $userProfileRoute)
                                                    <a href="{{ $userProfileRoute }}" class="btn btn-sm btn-info" title="Профиль пользователя">
                                                        <i class="bi bi-person"></i>
                                                    </a>
                                                @endif
                                                @if((auth()->user()->isAdmin() || auth()->user()->isManager()) && $limit->getAvailableQuantity() > 0 && $subscription)
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            data-bs-toggle="modal" data-bs-target="#delegateModal{{ $limit->id }}" 
                                                            title="Делегировать">
                                                        <i class="bi bi-share"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Детали делегированных лимитов (скрытый блок) -->
                                    @if($limit->delegatedVersions && $limit->delegatedVersions->count() > 0)
                                        <tr class="collapse" id="delegated-{{ $limit->id }}">
                                            <td colspan="14" class="p-0">
                                                <div class="p-3 bg-light border-top border-bottom">
                                                    <h6 class="mb-3">
                                                        <i class="bi bi-share me-2"></i>
                                                        Делегированные отчеты:
                                                    </h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0 bg-white">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Пользователь</th>
                                                                    <th>Организация</th>
                                                                    <th>Количество</th>
                                                                    <th>Использовано</th>
                                                                    <th>Доступно</th>
                                                                    <th>Статус</th>
                                                                    <th>Дата создания</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($limit->delegatedVersions as $delegated)
                                                                    @php
                                                                        $delUser = $delegated->user;
                                                                        $delUserOrg = null;
                                                                        if ($delUser) {
                                                                            if ($delUser->isOrgOwner() && $delUser->orgOwnerProfile) {
                                                                                $delUserOrg = $delUser->orgOwnerProfile->organization;
                                                                            } elseif ($delUser->isOrgMember() && $delUser->orgMemberProfile) {
                                                                                $delUserOrg = $delUser->orgMemberProfile->organization;
                                                                            }
                                                                        }
                                                                        
                                                                        // Определяем маршрут к профилю делегированного пользователя
                                                                        $delUserProfileRoute = null;
                                                                        if ($delUser) {
                                                                            if ($delUser->isOrgOwner() && $delUser->orgOwnerProfile) {
                                                                                $delUserProfileRoute = route('admin.organization.show', $delUser->orgOwnerProfile->organization_id);
                                                                            } elseif ($delUser->isOrgMember() && $delUser->orgMemberProfile) {
                                                                                $delUserProfileRoute = route('admin.org-members.show', [
                                                                                    $delUser->orgMemberProfile->organization_id,
                                                                                    $delUser->orgMemberProfile->id
                                                                                ]);
                                                                            } elseif ($delUser->isManager()) {
                                                                                $delUserProfileRoute = route('admin.managers.show', $delUser->id);
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <tr>
                                                                        <td>
                                                                            @if($delUser)
                                                                                <div class="d-flex align-items-center">
                                                                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                                                         style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                                                        {{ strtoupper(substr($delUser->name, 0, 1)) }}
                                                                                    </div>
                                                                                    <div>
                                                                                        @if($delUserProfileRoute)
                                                                                            <a href="{{ $delUserProfileRoute }}" class="text-decoration-none">
                                                                                                {{ $delUser->name }}
                                                                                            </a>
                                                                                        @else
                                                                                            <div>{{ $delUser->name }}</div>
                                                                                        @endif
                                                                                        <small class="text-muted">{{ $delUser->email }}</small>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <span class="text-muted fst-italic">Не указан</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if($delUserOrg)
                                                                                <a href="{{ route('admin.organization.show', $delUserOrg->id) }}" class="text-decoration-none">
                                                                                    {{ $delUserOrg->name }}
                                                                                </a>
                                                                            @elseif($delUser && $delUser->isManager())
                                                                                <span class="badge bg-secondary">Менеджер</span>
                                                                            @else
                                                                                <span class="text-muted fst-italic">Не указана</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $delegated->quantity }} шт.</td>
                                                                        <td>{{ $delegated->used_quantity }} шт.</td>
                                                                        <td>
                                                                            <span class="badge bg-{{ $delegated->getAvailableQuantity() > 0 ? 'success' : 'danger' }}">
                                                                                {{ $delegated->getAvailableQuantity() }} шт.
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            @if($delegated->isExhausted())
                                                                                <span class="badge bg-danger">Исчерпан</span>
                                                                            @elseif($delegated->isActive())
                                                                                <span class="badge bg-success">Активен</span>
                                                                            @else
                                                                                <span class="badge bg-secondary">Неактивен</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $delegated->created_at ? $delegated->created_at->format('d.m.Y H:i') : '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center py-5">
                                            <i class="bi bi-file-text display-1 text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">Отчеты не найдены</p>
                                            @if(request()->anyFilled(['organization_id', 'user_id', 'report_type_id', 'date', 'status']))
                                                <p class="text-muted mt-2">Попробуйте изменить параметры фильтрации</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Информация о пагинации -->
                    @if($limits->total() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="text-muted mb-0">
                                Показано {{ $limits->firstItem() ?? 0 }} - {{ $limits->lastItem() ?? 0 }} 
                                из {{ $limits->total() }} отчетов
                            </p>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $limits->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для делегирования -->
@foreach($limits as $limit)
    @php
        $subscription = $limit->subscription;
        $user = $subscription->user ?? null;
    @endphp
    @if((auth()->user()->isAdmin() || auth()->user()->isManager()) && $limit->getAvailableQuantity() > 0 && $subscription)
        <div class="modal fade" id="delegateModal{{ $limit->id }}" tabindex="-1" aria-labelledby="delegateModalLabel{{ $limit->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('limits.delegate', $limit) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="delegateModalLabel{{ $limit->id }}">
                                <i class="bi bi-share me-2"></i>
                                Делегировать отчет #{{ $limit->id }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-file-text me-2"></i>
                                    <strong>Отчет:</strong> {{ $limit->reportType->name ?? 'Не указан' }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Доступно для делегирования:</strong> 
                                    <span class="badge bg-success ms-2">{{ $limit->getAvailableQuantity() }} шт.</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Пользователь <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select select2-delegate" 
                                        data-exclude-user-id="{{ $user ? $user->id : '' }}" required>
                                    <option value="">Поиск пользователя...</option>
                                </select>
                                <small class="text-muted">Выберите сотрудника для делегирования</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Количество <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" 
                                       min="1" max="{{ $limit->getAvailableQuantity() }}" 
                                       value="1" required>
                                <small class="text-muted">
                                    Максимум: {{ $limit->getAvailableQuantity() }} шт.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-share me-1"></i>Делегировать
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .badge {
        font-size: 85%;
    }
    .btn-sm {
        line-height: 1;
    }
    .btn-sm i {
        font-size: 1rem;
    }
    .delegated-info .btn-sm {
        padding: 0.25rem 0.5rem;
    }
    .collapse:not(.show) {
        display: none;
    }
    .collapse.show {
        display: table-row;
    }
    /* Стили для ссылок */
    a.text-decoration-none:hover {
        text-decoration: underline !important;
    }
    .table a {
        color: #0d6efd;
    }
    .table a:hover {
        color: #0a58ca;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script>
    $(document).ready(function() {
        // Инициализация Select2 для пользователей
        $('.select2-user').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Поиск пользователя...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route("users.search") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term || '',
                        organization_id: $('select[name="organization_id"]').val()
                    };
                },
                processResults: function(data) {
                    let results = [{
                        id: '',
                        text: 'Все пользователи'
                    }];
                    
                    results = results.concat(data);
                    
                    return {
                        results: results
                    };
                },
                cache: true
            }
        });

        // Инициализация Select2 для организаций
        $('.select2-organization').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Выберите организацию',
            allowClear: true,
            width: '100%'
        });

        // При изменении организации - обновляем список пользователей
        $('select[name="organization_id"]').on('change', function() {
            $('.select2-user').val(null).trigger('change');
            $('#filterForm').submit();
        });

        // Автоматическая отправка формы при изменении полей
        $('select[name="report_type_id"], select[name="status"], input[name="date"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Отправка формы при изменении пользователя
        $('.select2-user').on('change', function() {
            $('#filterForm').submit();
        });

        // Инициализация Select2 для делегирования
        $('.select2-delegate').each(function() {
            let excludeUserId = $(this).data('exclude-user-id');
            let modal = $(this).closest('.modal');
            
            $(this).select2({
                theme: 'default',
                language: 'ru',
                placeholder: 'Поиск пользователя...',
                allowClear: true,
                width: '100%',
                dropdownParent: modal,
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("users.search") }}',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            search: params.term || '',
                            organization_id: $('select[name="organization_id"]').val(),
                            exclude_user_id: excludeUserId
                        };
                    },
                    processResults: function(data) {
                        // Исключаем текущего пользователя
                        let filtered = data.filter(function(user) {
                            return user.id != excludeUserId;
                        });
                        
                        return {
                            results: filtered
                        };
                    },
                    cache: true
                }
            });
        });

        // Инициализация Bootstrap Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Инициализация Bootstrap Collapse для кнопок делегирования
        var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
        collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, {
                toggle: false
            });
        });
    });
</script>
@endpush

@endsection