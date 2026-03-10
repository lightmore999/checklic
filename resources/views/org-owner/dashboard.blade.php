@extends('layouts.app')

@section('title', 'Панель владельца организации - ' . $organization->name)
@section('page-icon', 'bi-buildings')

@section('content')
<div class="container-fluid py-4">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
        
        // Проверяем права на делегирование (владелец всегда может делегировать)
        $canDelegateAny = true;
        $currentUserId = Auth::id();
        $ownerId = $organization->owner->user_id ?? null;
        
        // Статистика по сотрудникам
        $currentEmployeesCount = $organization->members->count();
        $availableEmployeeSlots = $organization->max_employees ? max(0, $organization->max_employees - $currentEmployeesCount) : null;
        
        // Процент заполнения лимита сотрудников
        $employeePercentage = $organization->max_employees > 0 
            ? min(100, round(($currentEmployeesCount / $organization->max_employees) * 100)) 
            : 0;
    @endphp

    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-success text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #43a047 0%, #1e7e34 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <!-- Аватар организации -->
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ strtoupper(substr($organization->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $organization->name }}</h1>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-white text-success px-3 py-2">
                                        <i class="bi bi-person-badge me-1"></i>Владелец организации
                                    </span>
                                    @if($organization->our_organization)
                                        <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                            <i class="bi bi-star-fill me-1"></i>{{ $organization->our_organization }}
                                        </span>
                                    @endif
                                    @if($organization->inn)
                                        <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                            <i class="bi bi-card-text me-1"></i>ИНН: {{ $organization->inn }}
                                        </span>
                                    @endif
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}" class="text-white opacity-75">Панель владельца</a></li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">{{ Str::limit($organization->name, 30) }}</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-white text-success fs-6 px-4 py-3">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ ucfirst($organization->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Успешно!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Ошибка!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Статистика в карточках -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Всего сотрудников</h6>
                            <h3 class="mb-0 fw-bold">{{ $currentEmployeesCount }}</h3>
                            <small class="text-muted">в организации</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Активных</h6>
                            <h3 class="mb-0 fw-bold">{{ $activeMembersCount }}</h3>
                            <small class="text-muted">из {{ $currentEmployeesCount }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Лимит</h6>
                            <h3 class="mb-0 fw-bold">{{ $organization->max_employees ?? '∞' }}</h3>
                            <small class="text-muted">максимум</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Свободно мест</h6>
                            <h3 class="mb-0 fw-bold">{{ $availableEmployeeSlots ?? '∞' }}</h3>
                            <small class="text-muted">можно добавить</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Информация о владельце и менеджере -->
    <div class="row g-4 mb-4">
        <!-- Профиль владельца -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-success me-2"></i>
                        Ваш профиль
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px; font-size: 1.8rem; font-weight: 500; color: #198754;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                            <p class="text-muted mb-0 small">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-telephone text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Телефон</small>
                                    <span class="fw-semibold">{{ Auth::user()->phone ?? 'Не указан' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-calendar text-warning"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Регистрация</small>
                                    <span class="fw-semibold">{{ Auth::user()->created_at->format('d.m.Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Статус:</span>
                            @if(Auth::user()->is_active)
                                <span class="badge bg-success">Активен</span>
                            @else
                                <span class="badge bg-danger">Неактивен</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Менеджер -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-workspace text-primary me-2"></i>
                        Ваш менеджер
                    </h5>
                </div>
                <div class="card-body pt-3">
                    @if($manager)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                                 style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 500; color: #0d6efd;">
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $manager->name }}</h6>
                                <p class="text-muted small mb-2">{{ $manager->email }}</p>
                                
                                @php
                                    $managerPhone = $manager->managerProfile->phone ?? $manager->phone ?? null;
                                    $managerTelegram = $manager->managerProfile->telegram ?? $manager->telegram ?? null;
                                @endphp
                                
                                @if($managerPhone)
                                    <p class="small mb-1">
                                        <i class="bi bi-telephone text-primary me-2"></i>
                                        <a href="tel:{{ $managerPhone }}" class="text-decoration-none">{{ $managerPhone }}</a>
                                    </p>
                                @endif
                                
                                @if($managerTelegram)
                                    <p class="small mb-0">
                                        <i class="bi bi-telegram text-info me-2"></i>
                                        <a href="https://t.me/{{ $managerTelegram }}" class="text-decoration-none" target="_blank">
                                            @ {{ $managerTelegram }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 48px; height: 48px; color: #6c757d;">
                                <i class="bi bi-question"></i>
                            </div>
                            <p class="text-muted mb-2">Менеджер не назначен</p>
                            <a href="mailto:support@example.com" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-envelope me-1"></i>Написать в поддержку
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Лимит сотрудников -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-people text-info me-2"></i>
                        Лимит сотрудников
                    </h5>
                </div>
                <div class="card-body pt-3">
                    @if($organization->max_employees)
                        <div class="text-center mb-3">
                            <div class="display-4 text-info mb-2">{{ $currentEmployeesCount }} / {{ $organization->max_employees }}</div>
                            <div class="text-muted">текущее / максимум</div>
                        </div>
                        
                        <!-- Прогресс-бар -->
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-{{ $employeePercentage >= 90 ? 'danger' : ($employeePercentage >= 70 ? 'warning' : 'success') }}" 
                                 style="width: {{ $employeePercentage }}%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-center">
                            <div>
                                <span class="badge bg-success">{{ $activeMembersCount }}</span>
                                <small class="d-block text-muted">активных</small>
                            </div>
                            <div>
                                <span class="badge bg-danger">{{ $currentEmployeesCount - $activeMembersCount }}</span>
                                <small class="d-block text-muted">неактивных</small>
                            </div>
                            <div>
                                <span class="badge bg-info">{{ $availableEmployeeSlots ?? '∞' }}</span>
                                <small class="d-block text-muted">свободно</small>
                            </div>
                        </div>
                        
                        @if($currentEmployeesCount >= $organization->max_employees)
                            <div class="alert alert-warning mt-3 mb-0 py-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Достигнут лимит сотрудников
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <div class="display-4 text-success mb-2">∞</div>
                            <div class="text-muted">Лимит не ограничен</div>
                            <div class="mt-3">
                                <span class="badge bg-success">{{ $currentEmployeesCount }}</span>
                                <small class="d-block text-muted">текущее количество</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Подписки владельца -->
    @if(isset($subscriptions) && $subscriptions->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-warning me-2"></i>
                        Ваши подписки
                    </h5>
                    <span class="badge bg-warning">{{ $subscriptions->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($subscriptions as $subscription)
                            @php
                                $remainingDays = $subscription->getRemainingDays();
                                $statusClass = $subscription->status === 'active' ? 'success' : 
                                              ($subscription->status === 'expired' ? 'danger' : 
                                              ($subscription->status === 'pending' ? 'warning' : 'secondary'));
                                
                                // Считаем количество отчетов в этой подписке
                                $limitsCount = 0;
                                if (isset($groupedLimits)) {
                                    foreach($groupedLimits as $group) {
                                        if ($group['subscription']->id == $subscription->id) {
                                            $limitsCount = count($group['limits']);
                                            break;
                                        }
                                    }
                                }
                                $subscriptionName = $subscription->name ?? 'Подписка #' . $subscription->id;
                            @endphp
                            
                            <div class="col-md-6 col-lg-4">
                                <div class="card border h-100">
                                    <div class="card-header bg-transparent border-0 pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                                {{ $subscription->getStatusTextAttribute() }}
                                            </span>
                                            <span class="text-muted small">#{{ $subscription->id }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <h6 class="fw-bold mb-2">{{ Str::limit($subscriptionName, 30) }}</h6>
                                        
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-muted">
                                                <i class="bi bi-calendar-plus me-1"></i>{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-calendar-x me-1"></i>{{ $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : '∞' }}
                                            </span>
                                        </div>
                                        
                                        @if($limitsCount > 0)
                                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                                <span class="small text-muted">Отчетов в подписке:</span>
                                                <span class="badge bg-info">{{ $limitsCount }} шт.</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" 
                                                    onclick="viewSubscription({{ $subscription->id }})">
                                                <i class="bi bi-eye me-1"></i>Подробнее
                                            </button>
                                        @else
                                            <div class="text-center py-3">
                                                <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi bi-file-text text-secondary"></i>
                                                </div>
                                                <p class="text-muted small mb-0">Нет отчетов</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Отчеты по подпискам (группировка) -->
    @if(isset($groupedLimits) && count($groupedLimits) > 0)
        @foreach($groupedLimits as $group)
            @php
                $subscription = $group['subscription'];
                $limits = $group['limits'];
                $totalQuantity = $group['total_quantity'];
                $totalUsed = $group['total_used'];
                $totalAvailable = $group['total_available'];
                
                // Получаем делегированные лимиты для этой подписки
                $subscriptionDelegated = isset($delegatedLimits) ? $delegatedLimits->filter(function($delegated) use ($subscription) {
                    return $delegated->limit->subscription_id == $subscription->id;
                }) : collect();
                $totalDelegatedForSub = $subscriptionDelegated->sum('quantity');
                
                $subscriptionName = $group['subscription']->name ?? 'Подписка #' . $group['subscription']->id;
                $statusClass = $subscription->status === 'active' ? 'success' : 
                              ($subscription->status === 'expired' ? 'danger' : 
                              ($subscription->status === 'pending' ? 'warning' : 'secondary'));
            @endphp
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="mb-1">
                                <i class="bi bi-stars text-info me-2"></i>
                                {{ $subscriptionName }}
                                <span class="badge bg-{{ $statusClass }} ms-2">{{ $subscription->getStatusTextAttribute() }}</span>
                            </h5>
                            <div class="text-muted small">
                                @if($subscription->starts_at)
                                    <span class="me-3"><i class="bi bi-calendar-plus me-1"></i>С {{ $subscription->starts_at->format('d.m.Y') }}</span>
                                @endif
                                @if($subscription->ends_at)
                                    <span><i class="bi bi-calendar-x me-1"></i>до {{ $subscription->ends_at->format('d.m.Y') }}</span>
                                    @if($subscription->getRemainingDays() && $subscription->isActive())
                                        <span class="badge bg-{{ $subscription->getRemainingDays() <= 7 ? 'warning' : 'info' }} ms-2">
                                            осталось {{ $subscription->getRemainingDays() }} дн.
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">(бессрочная)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body pt-3">
                    <!-- Статистика по подписке -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Всего отчетов</small>
                                <span class="fw-bold fs-4">{{ $totalQuantity }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Использовано</small>
                                <span class="fw-bold fs-4 text-warning">{{ $totalUsed }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Доступно</small>
                                <span class="fw-bold fs-4 text-{{ $totalAvailable > 0 ? 'success' : 'danger' }}">{{ $totalAvailable }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block">Делегировано</small>
                                <span class="fw-bold fs-4 text-warning">{{ $totalDelegatedForSub }}</span>
                                <small class="text-muted d-block">шт.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Таблица отчетов -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Тип отчета</th>
                                    <th class="text-center">Выделено</th>
                                    <th class="text-center">Использовано</th>
                                    <th class="text-center">Делегировано</th>
                                    <th class="text-center">Доступно</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($limits as $limit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                {{ strtoupper(substr($limit['report_type_name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-semibold">{{ $limit['report_type_name'] }}</span>
                                                @if($limit['only_api'])
                                                    <span class="badge bg-warning ms-2">API</span>
                                                @else
                                                    <span class="badge bg-primary ms-2">UI</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold">{{ $limit['quantity'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                            {{ $limit['used_quantity'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($limit['delegated_amount'] > 0)
                                            <span class="badge bg-warning">{{ $limit['delegated_amount'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $limit['available_amount'] > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $limit['available_amount'] > 0 ? 'success' : 'danger' }} px-3 py-2">
                                            {{ $limit['available_amount'] }}
                                        </span>
                                    </td>
                                    <td>{{ date('d.m.Y', strtotime($limit['date_created'])) }}</td>
                                    <td>
                                        @if($limit['available_amount'] <= 0)
                                            <span class="badge bg-danger">Исчерпан</span>
                                        @else
                                            <span class="badge bg-success">Активен</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($limit['available_amount'] > 0 && isset($availableEmployees) && $availableEmployees->count() > 0)
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning delegate-btn"
                                                    data-limit-id="{{ $limit['id'] }}"
                                                    data-limit-name="{{ $limit['report_type_name'] }}"
                                                    data-limit-available="{{ $limit['available_amount'] }}"
                                                    data-limit-date="{{ date('d.m.Y', strtotime($limit['date_created'])) }}"
                                                    data-subscription-id="{{ $subscription->id }}"
                                                    title="Делегировать лимит">
                                                <i class="bi bi-share"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @elseif(isset($subscriptions) && $subscriptions->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5 class="mb-0">
                    <i class="bi bi-stars text-info me-2"></i>
                    Отчеты по подпискам
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-file-text fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">В подписках пока нет отчетов</h5>
                    <p class="text-muted mb-0">Обратитесь к менеджеру для получения отчетов</p>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5 class="mb-0">
                    <i class="bi bi-stars text-info me-2"></i>
                    Отчеты
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-speedometer2 fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">У вас нет подписок</h5>
                    <p class="text-muted mb-0">Для получения отчетов необходимо создать подписку</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Делегированные лимиты -->
    @if(isset($delegatedLimits) && $delegatedLimits->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-share text-warning me-2"></i>
                Делегированные отчеты
            </h5>
            <span class="badge bg-warning">{{ $delegatedLimits->count() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Сотрудник</th>
                            <th>Тип отчета</th>
                            <th>Подписка</th>
                            <th class="text-center">Делегировано</th>
                            <th class="text-center">Использовано</th>
                            <th class="text-center">Доступно</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delegatedLimits as $delegated)
                        @php
                            $available = $delegated->quantity - $delegated->used_quantity;
                            $subscription = $delegated->limit->subscription ?? null;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px; font-size: 0.9rem; color: #198754;">
                                        {{ strtoupper(substr($delegated->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-semibold small">{{ $delegated->user->name }}</span>
                                        <small class="text-muted d-block">{{ $delegated->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $delegated->limit->reportType->name ?? 'Не указан' }}</span>
                            </td>
                            <td>
                                @if($subscription)
                                    <span class="badge bg-info">#{{ $subscription->id }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $delegated->quantity }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                    {{ $delegated->used_quantity }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $available > 0 ? 'success' : 'danger' }} px-3 py-2">
                                    {{ $available }}
                                </span>
                            </td>
                            <td>{{ $delegated->created_at->format('d.m.Y') }}</td>
                            <td>
                                @if($delegated->is_active)
                                    @if($available <= 0)
                                        <span class="badge bg-danger">Исчерпан</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('delegated-limits.destroy', $delegated) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to_organization" value="{{ $organization->id }}">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Вернуть лимит?')"
                                            title="Вернуть лимит">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Сотрудники организации -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-people text-primary me-2"></i>
                    Сотрудники организации
                </h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary">{{ $currentEmployeesCount }} всего</span>
                    <span class="badge bg-success">{{ $activeMembersCount }} активно</span>
                    @if($organization->max_employees)
                        <span class="badge bg-info">лимит: {{ $organization->max_employees }}</span>
                    @endif
                </div>
            </div>
            @if($canAddMoreEmployees)
                <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Добавить сотрудника
                </a>
            @elseif($organization->max_employees)
                <button class="btn btn-secondary" disabled 
                        title="Достигнут лимит сотрудников ({{ $currentEmployeesCount }}/{{ $organization->max_employees }})">
                    <i class="bi bi-person-plus me-2"></i>Добавить сотрудника
                </button>
            @endif
        </div>
        
        <div class="card-body">
            @if($members->count() > 0)
                <div class="row g-3">
                    @foreach($members as $member)
                    @php
                        $memberDelegated = isset($delegatedLimits) ? $delegatedLimits->where('user_id', $member->user->id) : collect();
                        $memberTotalDelegated = $memberDelegated->sum('quantity');
                        $memberTotalUsed = $memberDelegated->sum('used_quantity');
                        $memberTotalAvailable = $memberTotalDelegated - $memberTotalUsed;
                        $hasDelegated = $memberTotalDelegated > 0;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card border h-100">
                            <div class="card-body p-3">
                                <!-- Заголовок сотрудника -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                                         style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 500; color: #0dcaf0;">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">{{ $member->user->name }}</h6>
                                        <small class="text-muted d-block">{{ $member->user->email }}</small>
                                        <div class="d-flex gap-1 mt-1">
                                            @if($member->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-danger">Неактивен</span>
                                            @endif
                                            @if($hasDelegated)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-share me-1"></i>{{ $memberDelegated->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                @if($hasDelegated)
                                    <div class="mb-3 pt-2 border-top">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Делегировано:</span>
                                            <span class="fw-semibold">{{ $memberTotalDelegated }} шт.</span>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Использовано:</span>
                                            <span class="fw-semibold text-warning">{{ $memberTotalUsed }} шт.</span>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <span class="text-muted">Доступно:</span>
                                            <span class="fw-semibold text-{{ $memberTotalAvailable > 0 ? 'success' : 'danger' }}">
                                                {{ $memberTotalAvailable }} шт.
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-2 border-top">
                                        <i class="bi bi-share text-muted me-1"></i>
                                        <small class="text-muted">Нет делегированных отчетов</small>
                                    </div>
                                @endif
                                
                                <!-- Кнопки действий -->
                                <div class="d-flex gap-1 pt-2 border-top">
                                    <a href="{{ route('owner.org-members.show', [$organization->id, $member->id]) }}" 
                                       class="btn btn-sm btn-outline-info" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('owner.org-members.edit', [$organization->id, $member->id]) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if(method_exists($members, 'hasPages') && $members->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $members->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-people fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">Сотрудников пока нет</h5>
                    <p class="text-muted mb-4">
                        Добавьте первого сотрудника в вашу организацию
                        @if($organization->max_employees)
                            <br><small class="text-info">Лимит сотрудников: {{ $organization->max_employees }} чел.</small>
                        @endif
                    </p>
                    @if($canAddMoreEmployees)
                        <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Добавить сотрудника
                        </a>
                    @elseif($organization->max_employees)
                        <button class="btn btn-secondary" disabled>
                            <i class="bi bi-person-plus me-2"></i>Лимит сотрудников исчерпан
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно делегирования -->
@if(isset($availableEmployees) && $availableEmployees->count() > 0)
<div class="modal fade" id="delegateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('delegated-limits.store') }}" method="POST" id="delegateForm">
                @csrf
                <input type="hidden" name="redirect_to_organization" value="{{ $organization->id }}">
                <div class="modal-header bg-warning text-white border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-share me-2"></i>
                        Делегирование отчета
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="limit_id" class="form-label fw-semibold">
                                    <i class="bi bi-tachometer me-2"></i>Отчет *
                                </label>
                                <select name="limit_id" id="limit_id" class="form-select" required>
                                    <option value="">Выберите отчет</option>
                                    @foreach($groupedLimits as $group)
                                        @foreach($group['limits'] as $limit)
                                            @if($limit['available_amount'] > 0)
                                                <option value="{{ $limit['id'] }}" 
                                                        data-available="{{ $limit['available_amount'] }}"
                                                        data-name="{{ $limit['report_type_name'] }}"
                                                        data-date="{{ date('d.m.Y', strtotime($limit['date_created'])) }}">
                                                    {{ $limit['report_type_name'] }} 
                                                    (Подписка #{{ $group['subscription']->id }})
                                                    - доступно {{ $limit['available_amount'] }} шт.
                                                </option>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="card border-info bg-light" id="limitInfo" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="mb-2" id="limitName"></h6>
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Дата действия:</span>
                                            <span id="limitDate" class="fw-semibold"></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Доступно:</span>
                                            <span class="badge bg-success" id="limitAvailable"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-semibold">
                                    <i class="bi bi-person me-2"></i>Сотрудник *
                                </label>
                                <select name="user_id" id="user_id" class="form-select" required>
                                    <option value="">Выберите сотрудника</option>
                                    @foreach($availableEmployees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }} ({{ $employee->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="card border-primary bg-light" id="employeeInfo" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="mb-2" id="employeeName"></h6>
                                    <div class="small" id="employeeDelegated">
                                        <i class="bi bi-info-circle"></i> Загрузка информации...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="quantity" class="form-label fw-semibold">
                            <i class="bi bi-123 me-2"></i>Количество для делегирования *
                        </label>
                        <div class="input-group mb-2">
                            <input type="number" name="quantity" id="quantity" 
                                   class="form-control" 
                                   min="1" 
                                   value="1"
                                   required>
                            <span class="input-group-text">шт.</span>
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDelegateAmount(5)">+5</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDelegateAmount(10)">+10</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setMaxDelegateAmount()">Максимум</button>
                        </div>
                        <small class="text-muted">
                            Максимально: <span id="maxAmount" class="fw-bold">0</span> шт.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-share me-2"></i>Делегировать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Модальное окно просмотра подписки -->
<div class="modal fade" id="viewSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    Детали подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewSubscriptionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge.fs-6 {
        font-size: 0.9rem;
        padding: 0.5rem 0.8rem;
    }
    
    /* Гарантируем идеальные круги */
    .rounded-circle {
        border-radius: 50% !important;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    
    .card {
        border-radius: 1rem;
        transition: all 0.2s;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #43a047 0%, #1e7e34 100%);
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .progress {
        border-radius: 10px;
        background-color: #e9ecef;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Данные для делегирования
    let limits = {
        @if(isset($groupedLimits))
            @foreach($groupedLimits as $group)
                @foreach($group['limits'] as $limit)
                    @if($limit['available_amount'] > 0)
                        {{ $limit['id'] }}: {
                            available: {{ $limit['available_amount'] }},
                            name: '{{ addslashes($limit['report_type_name']) }}',
                            date: '{{ date('d.m.Y', strtotime($limit['date_created'])) }}'
                        },
                    @endif
                @endforeach
            @endforeach
        @endif
    };
    
    // Данные сотрудников
    let employees = {
        @if(isset($availableEmployees))
            @foreach($availableEmployees as $employee)
                {{ $employee->id }}: {
                    name: '{{ addslashes($employee->name) }}',
                    delegated: {{ isset($delegatedLimits) ? $delegatedLimits->where('user_id', $employee->id)->sum('quantity') : 0 }},
                    types: {{ isset($delegatedLimits) ? $delegatedLimits->where('user_id', $employee->id)->count() : 0 }}
                },
            @endforeach
        @endif
    };
    
    // Кнопки делегирования
    $('.delegate-btn').on('click', function() {
        const limitId = $(this).data('limit-id');
        const limitName = $(this).data('limit-name');
        const limitAvailable = $(this).data('limit-available');
        const limitDate = $(this).data('limit-date');
        
        $('#limit_id').val(limitId).trigger('change');
        
        $('#limitInfo').show();
        $('#limitName').text(limitName);
        $('#limitDate').text(limitDate);
        $('#limitAvailable').text(limitAvailable + ' шт.');
        $('#maxAmount').text(limitAvailable);
        $('#quantity').attr('max', limitAvailable);
        
        $('#delegateModal').modal('show');
    });
    
    // Обновление информации при выборе лимита
    $('#limit_id').on('change', function() {
        const limitId = $(this).val();
        const limit = limits[limitId];
        
        if (limit && limit.available > 0) {
            $('#limitInfo').show();
            $('#limitName').text(limit.name);
            $('#limitDate').text(limit.date);
            $('#limitAvailable').text(limit.available + ' шт.');
            $('#maxAmount').text(limit.available);
            $('#quantity').attr('max', limit.available);
            
            const current = parseInt($('#quantity').val()) || 1;
            if (current > limit.available) {
                $('#quantity').val(Math.min(1, limit.available));
            }
        } else {
            $('#limitInfo').hide();
            $('#maxAmount').text('0');
            $('#quantity').attr('max', 0);
        }
    });
    
    // Обновление информации при выборе сотрудника
    $('#user_id').on('change', function() {
        const userId = $(this).val();
        const employee = employees[userId];
        
        if (employee) {
            $('#employeeInfo').show();
            $('#employeeName').text(employee.name);
            
            let delegatedInfo = '';
            if (employee.delegated > 0) {
                delegatedInfo = `Уже делегировано: <span class="badge bg-warning">${employee.delegated} шт.</span> (${employee.types} видов)`;
            } else {
                delegatedInfo = '<span class="text-muted">Нет делегированных лимитов</span>';
            }
            
            $('#employeeDelegated').html(delegatedInfo);
        } else {
            $('#employeeInfo').hide();
        }
    });
    
    // Функции для управления количеством
    window.setDelegateAmount = function(amount) {
        const current = parseInt($('#quantity').val()) || 1;
        const max = parseInt($('#quantity').attr('max')) || 0;
        let newValue = current + amount;
        
        if (newValue < 1) newValue = 1;
        if (newValue > max) newValue = max;
        
        $('#quantity').val(newValue);
    };
    
    window.setMaxDelegateAmount = function() {
        const max = parseInt($('#quantity').attr('max')) || 0;
        if (max > 0) {
            $('#quantity').val(max);
        }
    };
    
    // Валидация формы делегирования
    $('#delegateForm').on('submit', function(e) {
        const limitId = $('#limit_id').val();
        const userId = $('#user_id').val();
        const quantity = parseInt($('#quantity').val()) || 0;
        const max = parseInt($('#quantity').attr('max')) || 0;
        
        if (!limitId || !userId) {
            e.preventDefault();
            alert('Пожалуйста, выберите лимит и сотрудника');
            return false;
        }
        
        if (quantity <= 0) {
            e.preventDefault();
            alert('Количество должно быть больше 0');
            return false;
        }
        
        if (quantity > max) {
            e.preventDefault();
            alert('Нельзя делегировать больше, чем доступно');
            return false;
        }
        
        const limitText = $('#limit_id option:selected').text().split('-')[0].trim();
        const employeeText = $('#user_id option:selected').text().split('(')[0].trim();
        
        if (!confirm(`Делегировать ${quantity} шт. лимита "${limitText}" сотруднику ${employeeText}?`)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Сброс формы при закрытии
    $('#delegateModal').on('hidden.bs.modal', function() {
        $('#limit_id').val('');
        $('#user_id').val('');
        $('#quantity').val(1);
        $('#limitInfo').hide();
        $('#employeeInfo').hide();
    });
</script>
@endpush