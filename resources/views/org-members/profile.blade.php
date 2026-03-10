@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container-fluid py-4">
    @php
        $currentUser = Auth::user();
        $isAdmin = $currentUser->isAdmin();
        $isManager = $currentUser->isManager();
        $isOwner = $currentUser->isOrgOwner();
        
        // Определяем роль пользователя в организации
        $userRole = 'Сотрудник';
        $roleBadgeClass = 'bg-info';
        
        if ($isOwner) {
            $userRole = 'Владелец';
            $roleBadgeClass = 'bg-success';
        } elseif ($isManager) {
            $userRole = 'Менеджер';
            $roleBadgeClass = 'bg-primary';
        } elseif ($isAdmin) {
            $userRole = 'Администратор';
            $roleBadgeClass = 'bg-danger';
        }
        
        // Получаем профиль сотрудника и организацию
        $memberProfile = $user->orgMemberProfile;
        $organization = $memberProfile ? $memberProfile->organization : null;
        
        // Получаем начальника (boss) - это владелец организации
        $boss = null;
        $bossUser = null;
        if ($memberProfile && $memberProfile->boss_id) {
            $bossUser = \App\Models\User::find($memberProfile->boss_id);
            if ($bossUser) {
                $boss = $bossUser->orgOwnerProfile;
            }
        }
        
        // Получаем менеджера организации
        $managerUser = null;
        if ($organization && $organization->manager_id) {
            $managerUser = \App\Models\User::find($organization->manager_id);
        }
    @endphp

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

    <!-- Заголовок профиля -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <!-- Аватар с первой буквой имени -->
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 80px; height: 80px; font-size: 2.2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $user->name }}</h1>
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                    <span class="badge bg-white text-primary px-3 py-2">{{ $user->email }}</span>
                                    <span class="badge {{ $roleBadgeClass }} px-3 py-2">{{ $userRole }}</span>
                                    @if($user->phone)
                                        <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                            <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                                        </span>
                                    @endif
                                    @if($user->is_active)
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Активен
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-circle-fill me-1"></i>Неактивен
                                        </span>
                                    @endif
                                </div>
                                <p class="mb-0 opacity-75">
                                    <i class="bi bi-calendar3 me-2"></i>Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('member.profile.edit') }}" class="btn btn-light">
                                <i class="bi bi-pencil-square me-2"></i>Редактировать профиль
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            <h3 class="mb-0 fw-bold">{{ $totalAll ?? 0 }}</h3>
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
                            <h6 class="text-muted mb-1">Использовано</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalAllUsed ?? 0 }}</h3>
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
                            <h6 class="text-muted mb-1">Доступно</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalAllAvailable ?? 0 }}</h3>
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
                            <h6 class="text-muted mb-1">Создано отчетов</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalReports ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Информация об организации и руководстве -->
    @if($organization)
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
                            <p class="text-muted small mb-0 mt-2">
                                <i class="bi bi-calendar3 me-2"></i>Добавлен: {{ $memberProfile->created_at->format('d.m.Y') }}
                            </p>
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
                    @if($bossUser)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                                 style="width: 48px; height: 48px; font-size: 1.2rem; font-weight: 500; color: #198754;">
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
    @endif

    <!-- Подписки владельца (если пользователь - владелец) -->
    @if($isOwner && isset($subscriptions) && $subscriptions->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-warning me-2"></i>
                        Мои подписки
                        <span class="badge bg-warning ms-2">{{ $subscriptions->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($subscriptions as $subscription)
                            @php
                                $remainingDays = $subscription->getRemainingDays();
                                $statusClass = $subscription->status === 'active' ? 'success' : 
                                              ($subscription->status === 'expired' ? 'danger' : 
                                              ($subscription->status === 'pending' ? 'warning' : 'secondary'));
                                
                                $subscriptionLimits = $personalLimits->where('subscription_id', $subscription->id);
                                $totalLimits = $subscriptionLimits->sum('quantity');
                                $totalUsed = $subscriptionLimits->sum('used_quantity');
                                $available = $totalLimits - $totalUsed;
                                $percentage = $totalLimits > 0 ? round(($totalUsed / $totalLimits) * 100) : 0;
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
                                        <h6 class="fw-bold mb-2">{{ $subscription->name ?? 'Подписка #' . $subscription->id }}</h6>
                                        
                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-muted">
                                                <i class="bi bi-calendar-plus me-1"></i>{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-calendar-x me-1"></i>{{ $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : '∞' }}
                                            </span>
                                        </div>
                                        
                                        @if($totalLimits > 0)
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="small text-muted">Отчеты:</span>
                                                    <span class="small fw-bold">
                                                        {{ $totalUsed }}/{{ $totalLimits }} использовано
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success') }}" 
                                                         style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-2">
                                                    <span class="small text-muted">
                                                        <i class="bi bi-check-circle me-1"></i>Доступно: {{ $available }}
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-link text-primary p-0" 
                                                            onclick="viewSubscription({{ $subscription->id }})">
                                                        Подробнее <i class="bi bi-arrow-right"></i>
                                                    </button>
                                                </div>
                                            </div>
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

    <!-- Табы с отчетами -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="my-reports-tab" data-bs-toggle="tab" 
                                    data-bs-target="#my-reports" type="button" role="tab">
                                <i class="bi bi-person-check me-2"></i>
                                Мои отчеты
                                <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $personalLimits->count() }}</span>
                            </button>
                        </li>
                        @if($delegatedLimits->count() > 0)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="delegated-reports-tab" data-bs-toggle="tab" 
                                    data-bs-target="#delegated-reports" type="button" role="tab">
                                <i class="bi bi-share me-2"></i>
                                Делегированные
                                <span class="badge bg-warning bg-opacity-10 text-warning ms-2">{{ $delegatedLimits->count() }}</span>
                            </button>
                        </li>
                        @endif
                        @if(!empty($personalLimitsByType) || !empty($delegatedLimitsByType))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" 
                                    data-bs-target="#stats" type="button" role="tab">
                                <i class="bi bi-pie-chart me-2"></i>
                                Статистика по типам
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="reportTabsContent">
                        <!-- Мои отчеты -->
                        <div class="tab-pane fade show active" id="my-reports" role="tabpanel">
                            @if($personalLimits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Тип отчета</th>
                                                <th>Подписка</th>
                                                <th class="text-center">Всего</th>
                                                <th class="text-center">Использовано</th>
                                                <th class="text-center">Доступно</th>
                                                <th>Дата действия</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($personalLimits as $limit)
                                                @php
                                                    $available = $limit->getAvailableQuantity();
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                                {{ strtoupper(substr($limit->reportType->name ?? 'О', 0, 1)) }}
                                                            </div>
                                                            <span class="fw-semibold">{{ $limit->reportType->name ?? 'Без типа' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info bg-opacity-10 text-info">
                                                            #{{ $limit->subscription_id }}
                                                        </span>
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
                            @else
                                <div class="text-center py-5">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 80px; height: 80px;">
                                        <i class="bi bi-files fs-1 text-secondary"></i>
                                    </div>
                                    <h5 class="text-muted mb-3">У вас пока нет отчетов</h5>
                                    <p class="text-muted mb-4">Отчеты выдаются через администратора или руководителя</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Делегированные отчеты -->
                        @if($delegatedLimits->count() > 0)
                        <div class="tab-pane fade" id="delegated-reports" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
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
                                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: #198754;">
                                                                {{ strtoupper(substr($owner->name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <span class="fw-semibold small">{{ $owner->name }}</span>
                                                                <small class="text-muted d-block">{{ $owner->email }}</small>
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
                        
                        <!-- Статистика по типам -->
                        @if(!empty($personalLimitsByType) || !empty($delegatedLimitsByType))
                        <div class="tab-pane fade" id="stats" role="tabpanel">
                            <div class="row g-4">
                                @php
                                    $allTypes = array_unique(array_merge(
                                        array_keys($personalLimitsByType),
                                        array_keys($delegatedLimitsByType)
                                    ));
                                @endphp
                                
                                @foreach($allTypes as $typeName)
                                    @php
                                        $personalData = $personalLimitsByType[$typeName] ?? null;
                                        $delegatedData = $delegatedLimitsByType[$typeName] ?? null;
                                        
                                        $totalPersonal = $personalData['total'] ?? 0;
                                        $totalDelegated = $delegatedData['delegated'] ?? 0;
                                        $usedPersonal = $totalPersonal - ($personalData['available'] ?? 0);
                                        $usedDelegated = $totalDelegated - ($delegatedData['available'] ?? 0);
                                        
                                        $totalAll = $totalPersonal + $totalDelegated;
                                        $usedAll = $usedPersonal + $usedDelegated;
                                        $availableAll = ($personalData['available'] ?? 0) + ($delegatedData['available'] ?? 0);
                                        $percentage = $totalAll > 0 ? round(($usedAll / $totalAll) * 100) : 0;
                                    @endphp
                                    
                                    <div class="col-md-6">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                        {{ strtoupper(substr($typeName, 0, 1)) }}
                                                    </div>
                                                    <h6 class="fw-bold mb-0">{{ $typeName }}</h6>
                                                </div>
                                                
                                                <div class="progress mb-3" style="height: 8px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Всего:</span>
                                                    <span class="fw-bold">{{ $totalAll }} шт.</span>
                                                </div>
                                                
                                                <div class="row g-2">
                                                    @if($personalData)
                                                    <div class="col-6">
                                                        <div class="bg-light rounded p-2">
                                                            <small class="text-muted d-block">Собственные</small>
                                                            <span class="fw-bold">{{ $personalData['available'] }}/{{ $personalData['total'] }}</span>
                                                            <small class="text-muted d-block">доступно</small>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    @if($delegatedData)
                                                    <div class="col-6">
                                                        <div class="bg-light rounded p-2">
                                                            <small class="text-muted d-block">Делегированные</small>
                                                            <span class="fw-bold">{{ $delegatedData['available'] }}/{{ $delegatedData['delegated'] }}</span>
                                                            <small class="text-muted d-block">доступно</small>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра подписки -->
<div class="modal fade" id="viewSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Детали подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

@push('scripts')
<script>
    // Данные подписок для модального окна
    let subscriptionsData = {
        @if(isset($subscriptions))
            @foreach($subscriptions as $sub)
                {{ $sub->id }}: {
                    name: '{{ $sub->name ?? 'Подписка #' . $sub->id }}',
                    starts_at: '{{ $sub->starts_at ? $sub->starts_at->format('d.m.Y') : '—' }}',
                    ends_at: '{{ $sub->ends_at ? $sub->ends_at->format('d.m.Y') : 'Бессрочно' }}',
                    status: '{{ $sub->status }}',
                    status_text: '{{ $sub->getStatusTextAttribute() }}',
                    status_class: '{{ $sub->status === "active" ? "success" : ($sub->status === "expired" ? "danger" : "warning") }}',
                    remaining_days: {{ $sub->getRemainingDays() ?? 'null' }},
                    user_name: '{{ $user->name }}',
                    user_email: '{{ $user->email }}'
                },
            @endforeach
        @endif
    };

    function viewSubscription(id) {
        const sub = subscriptionsData[id];
        if (!sub) return;
        
        const html = `
            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: 500; color: white;">
                    ${sub.user_name.charAt(0).toUpperCase()}
                </div>
                <h5>${sub.user_name}</h5>
                <p class="text-muted small">${sub.user_email}</p>
            </div>
            <div class="bg-light p-3 rounded">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Название:</span>
                    <span class="fw-bold">${sub.name}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Дата начала:</span>
                    <span>${sub.starts_at}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Дата окончания:</span>
                    <span>${sub.ends_at}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Статус:</span>
                    <span class="badge bg-${sub.status_class}">${sub.status_text}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Осталось дней:</span>
                    <span class="fw-bold">${sub.remaining_days !== null ? sub.remaining_days + ' дн.' : '∞'}</span>
                </div>
            </div>
        `;
        
        document.getElementById('viewSubscriptionContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('viewSubscriptionModal')).show();
    }

    // Активация табов
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#reportTabs button').forEach(triggerEl => {
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault();
                new bootstrap.Tab(this).show();
            });
        });
    });
</script>
@endpush

<style>
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.nav-tabs .nav-link:hover {
    border: none;
    color: #0d6efd;
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
</style>
@endsection