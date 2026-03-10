@extends('layouts.app')

@section('title', $member->user->name)

@section('content')
<div class="container-fluid py-4">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
        
        $routePrefix = $isAdmin ? 'admin.' : ($isManager ? 'manager.' : 'owner.');
        
        // Статистика для прогресс-баров
        $personalPercentage = $totalPersonal > 0 ? round(($totalPersonalUsed / $totalPersonal) * 100) : 0;
        $delegatedPercentage = $totalDelegated > 0 ? round(($totalDelegatedUsed / $totalDelegated) * 100) : 0;
        
        // Получаем начальника (boss) - это владелец организации
        $bossUser = $member->boss ? $member->boss->user : null;
        
        // Получаем менеджера организации
        $managerUser = $organization->manager;
    @endphp

    <!-- Хедер с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <!-- Аватар с первой буквой -->
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ strtoupper(substr($member->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $member->user->name }}</h1>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-white text-primary px-3 py-2">{{ $member->user->email }}</span>
                                    <span class="badge bg-info px-3 py-2">Сотрудник</span>
                                    @if($member->user->is_active)
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Активен
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-circle-fill me-1"></i>Неактивен
                                        </span>
                                    @endif
                                    @if($member->user->phone)
                                        <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                            <i class="bi bi-telephone me-1"></i>{{ $member->user->phone }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mb-0 opacity-75">
                                    <i class="bi bi-building me-2"></i>{{ $organization->name }}
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-calendar3 me-2"></i>Зарегистрирован: {{ $member->user->created_at->format('d.m.Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
                               class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                            @if($isAdmin || $isManager || $isOwner)
                                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-pencil-square me-2"></i>Редактировать
                                </a>
                            @endif
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

    <!-- Статистика в карточках -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Всего отчетов</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalReports }}</h3>
                            <small class="text-muted">{{ $completedReports }} завершено</small>
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
                            <h6 class="text-muted mb-1">Завершено</h6>
                            <h3 class="mb-0 fw-bold">{{ $completedReports }}</h3>
                            <small class="text-muted">{{ $inProgressReports }} в работе</small>
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
                            <h6 class="text-muted mb-1">За этот месяц</h6>
                            <h3 class="mb-0 fw-bold">{{ $thisMonthReports }}</h3>
                            <small class="text-muted">с {{ now()->format('d.m') }}</small>
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
                            <h6 class="text-muted mb-1">Доступно</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalAllAvailable }}</h3>
                            <small class="text-muted">из {{ $totalAll }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Информация об организации и руководстве -->
    <div class="row g-4 mb-4">
        <!-- Организация -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-building text-primary me-2"></i>
                        Организация
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 500; color: #0d6efd;">
                            {{ strtoupper(substr($organization->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">{{ $organization->name }}</h6>
                            @if($organization->our_organization)
                                <div class="mb-2">
                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                        <i class="bi bi-star-fill me-1"></i>
                                        {{ $organization->our_organization }}
                                    </span>
                                </div>
                            @endif
                            @if($organization->inn)
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-card-text me-2"></i>ИНН: {{ $organization->inn }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Начальник (Владелец организации) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-success me-2"></i>
                        Начальник
                    </h5>
                </div>
                <div class="card-body pt-3">
                    @php
                        // Получаем начальника через связь boss (это User) и затем его профиль владельца
                        $bossUser = null;
                        $bossProfile = null;
                        
                        if ($member && $member->boss) {
                            $bossUser = $member->boss; // Это уже User из связи belongsTo
                            $bossProfile = $bossUser->orgOwnerProfile; // Профиль владельца
                        }
                    @endphp
                    
                    @if($bossUser)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                                style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 500; color: #198754; flex-shrink: 0;">
                                {{ strtoupper(substr($bossUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $bossUser->name }}</h6>
                                <p class="text-muted small mb-2">{{ $bossUser->email }}</p>
                                @if($bossUser->phone)
                                    <p class="text-muted small mb-1">
                                        <i class="bi bi-telephone me-2"></i>{{ $bossUser->phone }}
                                    </p>
                                @endif
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-star-fill me-1"></i>Владелец
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                style="width: 48px; height: 48px; color: #6c757d;">
                                <i class="bi bi-question"></i>
                            </div>
                            <p class="text-muted mb-0">Начальник не назначен</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Менеджер организации -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-workspace text-info me-2"></i>
                        Менеджер
                    </h5>
                </div>
                <div class="card-body pt-3">
                    @if($managerUser)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                                 style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 500; color: #0dcaf0;">
                                {{ strtoupper(substr($managerUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $managerUser->name }}</h6>
                                <p class="text-muted small mb-2">{{ $managerUser->email }}</p>
                                @if($managerUser->phone)
                                    <p class="text-muted small mb-1">
                                        <i class="bi bi-telephone me-2"></i>{{ $managerUser->phone }}
                                    </p>
                                @endif
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-person-badge me-1"></i>Менеджер
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 48px; height: 48px; color: #6c757d;">
                                <i class="bi bi-question"></i>
                            </div>
                            <p class="text-muted mb-0">Менеджер не назначен</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Левая колонка -->
        <div class="col-lg-4">
            <!-- Личная информация -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Личная информация
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px; font-size: 1.8rem; font-weight: 500; color: #0d6efd;">
                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $member->user->name }}</h5>
                            <p class="text-muted mb-0 small">{{ $member->user->email }}</p>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-telephone text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Телефон</small>
                                    <span class="fw-semibold">{{ $member->user->phone ?? 'Не указан' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-calendar text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Регистрация</small>
                                    <span class="fw-semibold">{{ $member->user->created_at->format('d.m.Y') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-building text-info"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Организация</small>
                                    <span class="fw-semibold">{{ $organization->name }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($bossUser)
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-badge text-warning"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Начальник</small>
                                    <span class="fw-semibold">{{ $bossUser->name }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($managerUser)
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-workspace text-danger"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Менеджер</small>
                                    <span class="fw-semibold">{{ $managerUser->name }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Подписки сотрудника -->
            @if(isset($subscriptions) && $subscriptions->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-stars text-warning me-2"></i>
                            Подписки
                        </h5>
                        <span class="badge bg-warning">{{ $subscriptions->count() }}</span>
                    </div>
                    <div class="card-body pt-3">
                        <div class="list-group list-group-flush">
                            @foreach($subscriptions as $subscription)
                                @php
                                    $remainingDays = $subscription->getRemainingDays();
                                    $statusClass = $subscription->status === 'active' ? 'success' : 
                                                ($subscription->status === 'expired' ? 'danger' : 'warning');
                                    
                                    $subscriptionLimits = $personalLimits->where('subscription_id', $subscription->id);
                                    $totalLimits = $subscriptionLimits->sum('quantity');
                                    $subscriptionName = $subscription->name ?? 'Подписка #' . $subscription->id;
                                @endphp
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-{{ $statusClass }} bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-tag text-{{ $statusClass }}"></i>
                                            </div>
                                            <div>
                                                <span class="badge bg-{{ $statusClass }} me-2">
                                                    {{ $subscription->getStatusTextAttribute() }}
                                                </span>
                                                <span class="fw-semibold">{{ Str::limit($subscriptionName, 25) }}</span>
                                            </div>
                                        </div>
                                        @if($totalLimits > 0)
                                            <span class="badge bg-info">{{ $totalLimits }} шт.</span>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted ps-5">
                                        <span>
                                            <i class="bi bi-calendar-plus me-1"></i>
                                            {{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}
                                        </span>
                                        <span>
                                            <i class="bi bi-calendar-x me-1"></i>
                                            {{ $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : '∞' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Статистика по типам отчетов -->
            @if($reportsByType->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart text-info me-2"></i>
                        По типам отчетов
                    </h5>
                </div>
                <div class="card-body pt-3">
                    @foreach($reportsByType as $typeId => $stats)
                        @php
                            $percentage = $stats['count'] > 0 ? round(($stats['completed'] / $stats['count']) * 100) : 0;
                            $typeName = $reportTypes[$typeId] ?? 'Тип #' . $typeId;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                         style="width: 28px; height: 28px; font-size: 0.8rem; color: #0d6efd;">
                                        {{ strtoupper(substr($typeName, 0, 1)) }}
                                    </div>
                                    <span class="small fw-semibold">{{ $typeName }}</span>
                                </div>
                                <span class="small text-muted">{{ $stats['completed'] }}/{{ $stats['count'] }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'info') }}" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Действия -->
            @if($isAdmin || $isManager || $isOwner)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">
                            <i class="bi bi-gear text-secondary me-2"></i>
                            Действия
                        </h5>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                               class="btn btn-primary">
                                <i class="bi bi-pencil-square me-2"></i>Редактировать сотрудника
                            </a>

                            <form action="{{ route($routePrefix . 'org-members.toggle-status', [$organization->id, $member->id]) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-{{ $member->is_active ? 'warning' : 'success' }} w-100">
                                    <i class="bi bi-toggle-{{ $member->is_active ? 'off' : 'on' }} me-2"></i>
                                    {{ $member->is_active ? 'Деактивировать' : 'Активировать' }}
                                </button>
                            </form>

                            @if($isOwner)
                                <button type="button" class="btn btn-warning delegate-btn"
                                        data-employee-id="{{ $member->user->id }}"
                                        data-employee-name="{{ $member->user->name }}">
                                    <i class="bi bi-share me-2"></i>Делегировать отчеты
                                </button>
                            @endif

                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="bi bi-trash me-2"></i>Удалить сотрудника
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Правая колонка -->
        <div class="col-lg-8">
            <!-- Сводка по лимитам (2 карточки) -->
            <div class="row g-4 mb-4">
                @if($personalLimits->count() > 0)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-check text-success fs-5"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold">Собственные отчеты</h6>
                                </div>
                                <span class="badge bg-success">{{ $totalPersonalAvailable }}/{{ $totalPersonal }}</span>
                            </div>
                            
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-{{ $personalPercentage >= 80 ? 'danger' : ($personalPercentage >= 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $personalPercentage }}%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Использовано: {{ $totalPersonalUsed }}</span>
                                <span class="text-muted">Доступно: {{ $totalPersonalAvailable }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($delegatedLimits->count() > 0)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-share text-warning fs-5"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold">Делегированные</h6>
                                </div>
                                <span class="badge bg-warning">{{ $totalDelegatedAvailable }}/{{ $totalDelegated }}</span>
                            </div>
                            
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-{{ $delegatedPercentage >= 80 ? 'danger' : ($delegatedPercentage >= 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $delegatedPercentage }}%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Использовано: {{ $totalDelegatedUsed }}</span>
                                <span class="text-muted">Доступно: {{ $totalDelegatedAvailable }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Табы для лимитов -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <ul class="nav nav-tabs card-header-tabs" id="limitsTab" role="tablist">
                        @if($personalLimits->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" 
                                        data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person-check me-2"></i>
                                    Собственные
                                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $personalLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                        @if($delegatedLimits->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $personalLimits->count() == 0 ? 'active' : '' }}" 
                                        id="delegated-tab" data-bs-toggle="tab" 
                                        data-bs-target="#delegated" type="button" role="tab">
                                    <i class="bi bi-share me-2"></i>
                                    Делегированные
                                    <span class="badge bg-warning bg-opacity-10 text-warning ms-2">{{ $delegatedLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>
                
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Собственные лимиты -->
                        @if($personalLimits->count() > 0)
                            <div class="tab-pane fade show active p-3" id="personal" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Подписка</th>
                                                <th>Тип отчета</th>
                                                <th class="text-center">Всего</th>
                                                <th class="text-center">Использовано</th>
                                                <th class="text-center">Доступно</th>
                                                <th>Дата</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($personalLimits as $limit)
                                                @php 
                                                    $available = $limit->getAvailableQuantity();
                                                    $subscription = $limit->subscription;
                                                    $subscriptionName = $subscription ? ($subscription->name ?? 'Подписка #' . $subscription->id) : 'Неизвестно';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                                 style="width: 28px; height: 28px;">
                                                                <i class="bi bi-tag text-info small"></i>
                                                            </div>
                                                            <span class="small" title="{{ $subscriptionName }}">
                                                                {{ Str::limit($subscriptionName, 20) }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $limit->reportType->name ?? 'Без типа' }}</span>
                                                    </td>
                                                    <td class="text-center fw-bold">{{ $limit->quantity }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                                            {{ $limit->used_quantity }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $available > 0 ? 'success' : 'danger' }} px-3 py-2">
                                                            {{ $available }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $limit->date_created->format('d.m.Y') }}</td>
                                                    <td>
                                                        @if($limit->isExhausted())
                                                            <span class="badge bg-danger">Исчерпан</span>
                                                        @else
                                                            <span class="badge bg-success">Активен</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Делегированные лимиты -->
                        @if($delegatedLimits->count() > 0)
                            <div class="tab-pane fade p-3 {{ $personalLimits->count() == 0 ? 'show active' : '' }}" 
                                 id="delegated" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>От кого</th>
                                                <th>Тип отчета</th>
                                                <th class="text-center">Всего</th>
                                                <th class="text-center">Использовано</th>
                                                <th class="text-center">Доступно</th>
                                                <th>Дата</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($delegatedLimits as $delegated)
                                                @php
                                                    $available = $delegated->quantity - $delegated->used_quantity;
                                                    $owner = $delegated->limit->subscription->user ?? null;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        @if($owner)
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                                     style="width: 28px; height: 28px; font-size: 0.8rem; color: #198754;">
                                                                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <span class="fw-semibold small">{{ $owner->name }}</span>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $delegated->limit->reportType->name ?? 'Без типа' }}</span>
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
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if($personalLimits->count() == 0 && $delegatedLimits->count() == 0)
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-clipboard-x fs-1 text-secondary"></i>
                                </div>
                                <h5 class="text-muted mb-3">У сотрудника нет отчетов</h5>
                                @if($isOwner)
                                    <p class="text-muted mb-0">Вы можете делегировать отчеты этому сотруднику</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Форма для удаления -->
@if($isAdmin || $isManager || $isOwner)
<form id="delete-form" method="POST" 
      action="{{ route($routePrefix . 'org-members.delete', [$organization->id, $member->id]) }}" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete() {
    const memberName = "{{ $member->user->name }}";
    if (confirm(`Вы уверены, что хотите удалить сотрудника "${memberName}"?`)) {
        document.getElementById('delete-form').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#limitsTab button').forEach(triggerEl => {
        triggerEl.addEventListener('click', function(event) {
            event.preventDefault();
            new bootstrap.Tab(this).show();
        });
    });
});
</script>
@endif

<style>
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    background: transparent;
}

.nav-tabs .nav-link:hover {
    border: none;
    color: #0d6efd;
    background: transparent;
}

.nav-tabs .nav-link.active {
    color: #0d6efd !important;
    background: transparent !important;
    border: none !important;
    border-bottom: 3px solid #0d6efd !important;
    font-weight: 600;
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

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
    border-radius: 30px;
}

.card {
    border-radius: 1rem;
    transition: all 0.2s;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table th {
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
}

@media (max-width: 768px) {
    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}
</style>
@endsection