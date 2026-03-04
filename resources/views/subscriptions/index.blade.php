@extends('layouts.app')

@section('title', 'Управление подписками')
@section('page-icon', 'bi-stars')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-stars text-info me-2"></i>
            Управление подписками
            @if(isset($subscriptions) && $subscriptions->total() > 0)
                <span class="badge bg-info ms-2">{{ $subscriptions->total() }}</span>
            @endif
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('subscriptions.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-2"></i>
                Создать подписку
            </a>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Статистика -->
    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-primary mb-2">{{ $stats['total'] }}</div>
                    <div class="text-muted">Всего подписок</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-success mb-2">{{ $stats['active'] }}</div>
                    <div class="text-muted">Активных</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-warning mb-2">{{ $stats['expiring_soon'] }}</div>
                    <div class="text-muted">Скоро истекают</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="display-5 text-danger mb-2">{{ $stats['expired'] }}</div>
                    <div class="text-muted">Истекшие</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Форма фильтров -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Фильтры
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('subscriptions.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Фильтр по организации -->
                    <div class="col-md-3">
                        <label class="form-label">Организация</label>
                        <select name="organization_id" id="organization_id" class="form-select select2-organization">
                            <option value="">Все организации</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} @if($org->inn) (ИНН: {{ $org->inn }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Фильтр по пользователю с поиском -->
                    <div class="col-md-3">
                        <label class="form-label">Пользователь</label>
                        <select name="user_id" id="user_id" class="form-select select2-user" data-placeholder="Поиск пользователя...">
                            <option value="">Все пользователи</option>
                            @foreach($users as $userOption)
                                <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                                    {{ $userOption->name }} ({{ $userOption->email }}) - {{ $userOption->getRoleDisplayName() }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Можно ввести имя или email для поиска</small>
                    </div>
                    
                    <!-- Фильтр по статусу -->
                    <div class="col-md-2">
                        <label for="status" class="form-label">Статус</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Все статусы</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активна</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Отменена</option>
                        </select>
                    </div>
                    
                    <!-- Фильтр по истекающим -->
                    <div class="col-md-2">
                        <label for="expiring_soon" class="form-label">Скоро истекают</label>
                        <select name="expiring_soon" id="expiring_soon" class="form-select" onchange="this.form.submit()">
                            <option value="">Не выбрано</option>
                            <option value="7" {{ request('expiring_soon') == '7' ? 'selected' : '' }}>Менее 7 дней</option>
                            <option value="14" {{ request('expiring_soon') == '14' ? 'selected' : '' }}>Менее 14 дней</option>
                            <option value="30" {{ request('expiring_soon') == '30' ? 'selected' : '' }}>Менее 30 дней</option>
                        </select>
                    </div>
                    
                    <!-- Фильтр по истекшим -->
                    <div class="col-md-2">
                        <label for="expired" class="form-label">Истекшие</label>
                        <select name="expired" id="expired" class="form-select" onchange="this.form.submit()">
                            <option value="">Не выбрано</option>
                            <option value="1" {{ request('expired') == '1' ? 'selected' : '' }}>Показать истекшие</option>
                        </select>
                    </div>
                    
                    <!-- Кнопки действий -->
                    <div class="col-12 mt-3">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-funnel me-1"></i> Применить фильтры
                            </button>
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['organization_id', 'user_id', 'status', 'expiring_soon', 'expired']))
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
                
                @if(request('status'))
                    <span class="badge bg-info text-white">Статус: 
                        @switch(request('status'))
                            @case('active') Активна @break
                            @case('pending') Ожидает @break
                            @case('suspended') Приостановлена @break
                            @case('expired') Истекла @break
                            @case('cancelled') Отменена @break
                            @default {{ request('status') }}
                        @endswitch
                    </span>
                @endif
                
                @if(request('expiring_soon'))
                    <span class="badge bg-info text-white">Истекают менее чем через {{ request('expiring_soon') }} дней</span>
                @endif
                
                @if(request('expired'))
                    <span class="badge bg-info text-white">Только истекшие</span>
                @endif
            </div>
        </div>
    @endif

    <!-- Таблица подписок -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if(isset($subscriptions) && $subscriptions->count() > 0)
                <div class="accordion" id="subscriptionsAccordion">
                    @foreach($subscriptions as $subscription)
                        @php
                            $remainingDays = $subscription->getRemainingDays();
                            $statusClass = $subscription->status === 'active' ? 'success' : 
                                        ($subscription->status === 'expired' ? 'danger' : 
                                        ($subscription->status === 'pending' ? 'warning' : 
                                        ($subscription->status === 'cancelled' ? 'secondary' : 'info')));
                            
                            $user = $subscription->user;
                            $userRoleDisplay = $user ? $user->getRoleDisplayName() : 'Неизвестно';
                            $userInitial = $user ? strtoupper(substr($user->name, 0, 1)) : '?';
                            
                            // Определяем организацию пользователя
                            $userOrg = null;
                            if ($user) {
                                if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                    $userOrg = $user->orgOwnerProfile->organization;
                                } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                    $userOrg = $user->orgMemberProfile->organization;
                                }
                            }
                            
                            // Определяем маршрут к профилю пользователя в зависимости от роли
                            $userProfileRoute = null;
                            if ($user) {
                                if ($user->isAdmin()) {
                                    $userProfileRoute = route('admin.dashboard');
                                } elseif ($user->isManager()) {
                                    $userProfileRoute = route('admin.managers.show', $user->id);
                                } elseif ($user->isOrgOwner()) {
                                    $userProfileRoute = $userOrg ? route('admin.organization.show', $userOrg->id ?? 0) : null;
                                } elseif ($user->isOrgMember()) {
                                    $userProfileRoute = $userOrg ? route('admin.org-members.show', [$userOrg->id, $user->orgMemberProfile->id]) : null;
                                }
                            }
                            
                            // Получаем лимиты для этой подписки
                            $limits = $subscription->limits()->with('reportType')->get();
                            $hasLimits = $limits->count() > 0;
                            $totalLimits = $limits->sum('quantity');
                            $totalUsed = $limits->sum('used_quantity');
                            $totalAvailable = $totalLimits - $totalUsed;
                            
                            // ДОБАВЛЕНО: получаем название подписки
                            $subscriptionName = $subscription->name ?? 'Подписка #' . $subscription->id;
                        @endphp
                        
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="heading{{ $subscription->id }}">
                                <button class="accordion-button collapsed px-4 py-3" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $subscription->id }}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse{{ $subscription->id }}">
                                    <div class="row w-100 align-items-center">
                                        <!-- ID -->
                                        <div class="col-md-1">
                                            <span class="fw-bold">#{{ $subscription->id }}</span>
                                        </div>
                                        
                                        <!-- ДОБАВЛЕНО: Название подписки -->
                                        <div class="col-md-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-tag text-info me-1"></i>
                                                <span class="fw-semibold small text-truncate" style="max-width: 150px;" title="{{ $subscriptionName }}">
                                                    {{ $subscriptionName }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Пользователь (только имя, без ссылки) -->
                                        <div class="col-md-2">
                                            @if($user)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-{{ $user->getRoleColor() }} d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; color: white; font-size: 0.7rem; flex-shrink: 0;">
                                                        {{ $userInitial }}
                                                    </div>
                                                    <div class="text-truncate" style="max-width: calc(100% - 36px);">
                                                        <span class="small fw-semibold">{{ $user->name }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">Пользователь удален</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Организация (только название, без ссылки) -->
                                        <div class="col-md-2">
                                            @if($userOrg)
                                                <div class="text-truncate" style="max-width: 150px;">
                                                    <i class="bi bi-building text-muted me-1"></i>
                                                    <span class="small">{{ Str::limit($userOrg->name, 20) }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Статус и даты (компактно) -->
                                        <div class="col-md-2">
                                            <span class="badge bg-{{ $statusClass }}">{{ $subscription->getStatusTextAttribute() }}</span>
                                            @if($subscription->ends_at)
                                                <small class="d-block text-muted">
                                                    до {{ $subscription->ends_at->format('d.m.Y') }}
                                                    @if($remainingDays !== null && $subscription->isActive())
                                                        <span class="badge bg-{{ $remainingDays <= 7 ? 'warning' : 'success' }} ms-1">
                                                            {{ $remainingDays }} дн.
                                                        </span>
                                                    @endif
                                                </small>
                                            @else
                                                <small class="d-block text-muted">бессрочно</small>
                                            @endif
                                        </div>
                                        
                                        <!-- Сводка по лимитам -->
                                        <div class="col-md-2">
                                            @if($hasLimits)
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <span class="badge bg-primary">{{ $totalLimits }} шт.</span>
                                                        <span class="badge bg-info">{{ $totalUsed }} исп.</span>
                                                    </div>
                                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                        @php $percentage = $totalLimits > 0 ? round(($totalUsed / $totalLimits) * 100) : 0; @endphp
                                                        <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success') }}" 
                                                             style="width: {{ $percentage }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="text-muted">доступно {{ $totalAvailable }}</small>
                                            @else
                                                <span class="badge bg-secondary">Нет лимитов</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Индикатор раскрытия -->
                                        <div class="col-md-1 text-end">
                                            <i class="bi bi-chevron-down text-muted"></i>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            
                            <div id="collapse{{ $subscription->id }}" 
                                 class="accordion-collapse collapse" 
                                 aria-labelledby="heading{{ $subscription->id }}" 
                                 data-bs-parent="#subscriptionsAccordion">
                                <div class="accordion-body p-4 bg-light">
                                    <!-- Детальная информация о подписке -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Название</small>
                                            <span class="fw-semibold">{{ $subscriptionName }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Дата начала</small>
                                            <span class="fw-semibold">{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Дата окончания</small>
                                            <span class="fw-semibold">{{ $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : 'Бессрочно' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Создана</small>
                                            <span class="fw-semibold">{{ $subscription->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <small class="text-muted d-block mb-2">Действия</small>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editSubscription({{ $subscription->id }})"
                                                        title="Редактировать">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteSubscription({{ $subscription->id }})"
                                                        title="Удалить">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="{{ route('limits.create', ['subscription_id' => $subscription->id]) }}" 
                                                   class="btn btn-sm btn-outline-success"
                                                   title="Добавить лимиты">
                                                    <i class="bi bi-plus-lg"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- БЛОК СО ССЫЛКАМИ НА ПОЛЬЗОВАТЕЛЯ И ОРГАНИЗАЦИЮ -->
                                    <div class="row mb-3">
                                        @if($user)
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-white p-3">
                                                <small class="text-muted d-block mb-2">Пользователь</small>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-{{ $user->getRoleColor() }} d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 48px; height: 48px; color: white; font-size: 1.2rem;">
                                                        {{ $userInitial }}
                                                    </div>
                                                    <div>
                                                        @if($userProfileRoute)
                                                            <a href="{{ $userProfileRoute }}" class="fw-semibold text-decoration-none fs-5">
                                                                {{ $user->name }}
                                                            </a>
                                                        @else
                                                            <span class="fw-semibold fs-5">{{ $user->name }}</span>
                                                        @endif
                                                        <div class="mt-1">
                                                            <i class="bi bi-envelope text-muted me-1"></i>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="badge bg-{{ $user->getRoleColor() }}">{{ $userRoleDisplay }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if($userOrg)
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-white p-3">
                                                <small class="text-muted d-block mb-2">Организация</small>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 48px; height: 48px; color: white; font-size: 1.2rem;">
                                                        <i class="bi bi-building"></i>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('admin.organization.show', $userOrg->id) }}" class="fw-semibold text-decoration-none fs-5">
                                                            {{ $userOrg->name }}
                                                        </a>
                                                        @if($userOrg->our_organization)
                                                            <div class="mt-1">
                                                                <i class="bi bi-tag text-muted me-1"></i>
                                                                <small class="text-muted">{{ $userOrg->our_organization }}</small>
                                                            </div>
                                                        @endif
                                                        @if($userOrg->inn)
                                                            <div class="mt-1">
                                                                <i class="bi bi-file-text text-muted me-1"></i>
                                                                <small class="text-muted">ИНН: {{ $userOrg->inn }}</small>
                                                            </div>
                                                        @endif
                                                        <div class="mt-1">
                                                            <span class="badge bg-{{ $userOrg->status === 'active' ? 'success' : ($userOrg->status === 'suspended' ? 'warning' : 'danger') }}">
                                                                {{ $userOrg->status === 'active' ? 'Активна' : ($userOrg->status === 'suspended' ? 'Приостановлена' : 'Истекла') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Лимиты подписки -->
                                    @if($hasLimits)
                                        <div class="mt-4">
                                            <h6 class="mb-3">
                                                <i class="bi bi-file-text me-2"></i>
                                                Лимиты по типам отчетов
                                                <span class="badge bg-primary ms-2">{{ $limits->count() }}</span>
                                            </h6>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered bg-white">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Тип отчета</th>
                                                            <th class="text-center" width="100">Всего</th>
                                                            <th class="text-center" width="100">Использовано</th>
                                                            <th class="text-center" width="100">Доступно</th>
                                                            <th class="text-center" width="150">Прогресс</th>
                                                            <th class="text-center" width="100">Дата</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($limits as $limit)
                                                            @php
                                                                $available = $limit->getAvailableQuantity();
                                                                $percentage = $limit->quantity > 0 ? round(($limit->used_quantity / $limit->quantity) * 100) : 0;
                                                                $progressClass = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <strong>{{ $limit->reportType->name ?? 'Неизвестный тип' }}</strong>
                                                                        @if($limit->reportType && $limit->reportType->only_api)
                                                                            <span class="badge bg-warning ms-2">API</span>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">{{ $limit->quantity }}</td>
                                                                <td class="text-center">{{ $limit->used_quantity }}</td>
                                                                <td class="text-center">
                                                                    <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
                                                                        {{ $available }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                                            <div class="progress-bar bg-{{ $progressClass }}" 
                                                                                 style="width: {{ $percentage }}%">
                                                                            </div>
                                                                        </div>
                                                                        <small class="text-muted" style="min-width: 40px;">{{ $percentage }}%</small>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">{{ $limit->date_created->format('d.m.Y') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <td><strong>Итого:</strong></td>
                                                            <td class="text-center"><strong>{{ $totalLimits }}</strong></td>
                                                            <td class="text-center"><strong>{{ $totalUsed }}</strong></td>
                                                            <td class="text-center"><strong>{{ $totalAvailable }}</strong></td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-light text-center py-3 mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            В этой подписке пока нет лимитов
                                            <a href="{{ route('limits.create', ['subscription_id' => $subscription->id]) }}" 
                                               class="btn btn-sm btn-success ms-3">
                                                <i class="bi bi-plus-lg"></i> Добавить лимиты
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Пагинация -->
                <div class="d-flex justify-content-between align-items-center mt-4 px-4 pb-4">
                    <div>
                        <p class="text-muted mb-0">
                            Показано {{ $subscriptions->firstItem() ?? 0 }} - {{ $subscriptions->lastItem() ?? 0 }} 
                            из {{ $subscriptions->total() }} подписок
                        </p>
                    </div>
                    <div>
                        {{ $subscriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-stars display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Подписки не найдены</h4>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['organization_id', 'user_id', 'status', 'expiring_soon', 'expired']))
                            Попробуйте изменить параметры фильтрации
                        @else
                            Создайте первую подписку
                        @endif
                    </p>
                    @if(!request()->anyFilled(['organization_id', 'user_id', 'status', 'expiring_soon', 'expired']))
                        <a href="{{ route('subscriptions.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-lg me-2"></i>
                            Создать подписку
                        </a>
                    @else
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Сбросить фильтры
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно удаления подписки -->
<div class="modal fade" id="deleteSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>
                    Удаление подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту подписку?</p>
                <p class="text-danger"><strong>Внимание!</strong> Это действие также удалит все лимиты, связанные с этой подпиской.</p>
            </div>
            <div class="modal-footer">
                <form action="" method="POST" id="deleteSubscriptionForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить подписку</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования подписки (заглушка) -->
<div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Редактирование подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center py-3">Функция редактирования в разработке</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

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
    
    /* Стили для аккордеона */
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0d6efd;
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,0.125);
    }
    
    .accordion-button .row {
        transition: all 0.2s;
    }
    
    .accordion-button:not(.collapsed) .row {
        font-weight: 500;
    }
    
    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }
    
    .badge {
        font-size: 85%;
    }
    
    .table-sm th, .table-sm td {
        padding: 0.5rem 0.5rem;
    }
    
    /* Анимация для иконки */
    .accordion-button .bi-chevron-down {
        transition: transform 0.3s;
    }
    
    .accordion-button:not(.collapsed) .bi-chevron-down {
        transform: rotate(180deg);
    }
    
    /* Стили для названия подписки */
    .bi-tag {
        font-size: 0.9rem;
    }
    
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script>
    $(document).ready(function() {
        // Инициализация Select2 для организаций
        $('.select2-organization').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Выберите организацию',
            allowClear: true,
            width: '100%'
        });

        // Инициализация Select2 для пользователей с поиском
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
                        organization_id: $('#organization_id').val()
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

        // При изменении организации - обновляем список пользователей
        $('#organization_id').on('change', function() {
            $('.select2-user').val(null).trigger('change');
            $('#filterForm').submit();
        });

        // Автоматическая отправка формы при изменении полей
        $('select[name="status"], select[name="expiring_soon"], select[name="expired"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Отправка формы при изменении пользователя
        $('.select2-user').on('change', function() {
            $('#filterForm').submit();
        });

        // Инициализация тултипов
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Функция для открытия модалки удаления
    window.deleteSubscription = function(id) {
        document.getElementById('deleteSubscriptionForm').action = '/subscriptions/' + id;
        new bootstrap.Modal(document.getElementById('deleteSubscriptionModal')).show();
    };

    // Функция для открытия модалки редактирования
    window.editSubscription = function(id) {
        new bootstrap.Modal(document.getElementById('editSubscriptionModal')).show();
    };
</script>
@endpush
@endsection