@extends('layouts.app')

@section('title', 'Управление подписками')
@section('page-icon', 'bi-stars')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-info text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-stars"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Управление подписками</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-info px-3 py-2">
                                        <i class="bi bi-stars me-1"></i>Всего: {{ $subscriptions->total() ?? 0 }}
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'manager.dashboard') }}" 
                                               class="text-white opacity-75">
                                                Панель {{ Auth::user()->isAdmin() ? 'админа' : 'менеджера' }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Подписки
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('subscriptions.create') }}" class="btn btn-light">
                                <i class="bi bi-plus-lg me-2"></i>Создать подписку
                            </a>
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

    <!-- Статистика -->
    @if(isset($stats))
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Всего подписок</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
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
                            <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
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
                            <h6 class="text-muted mb-1">Скоро истекают</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['expiring_soon'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Истекшие</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['expired'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Карточка с фильтрами -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>
                Фильтры
            </h5>
        </div>
        <div class="card-body pt-3">
            <form method="GET" action="{{ route('subscriptions.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Фильтр по менеджеру (НОВЫЙ) -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Менеджер</label>
                        <select name="manager_id" id="manager_id" class="form-select select2-manager">
                            <option value="">Все менеджеры</option>
                            @if(isset($managers) && $managers->count() > 0)
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} ({{ $manager->email }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted">Показать подписки организаций менеджера</small>
                    </div>
                    
                    <!-- Фильтр по организации -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Организация</label>
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
                        <label class="form-label small fw-semibold">Пользователь</label>
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
                        <label for="status" class="form-label small fw-semibold">Статус</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активна</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Отменена</option>
                        </select>
                    </div>
                    
                    <!-- ЕДИНОЕ ПОЛЕ: Дней до/после окончания -->
                    <div class="col-md-2">
                        <label for="days_to_end" class="form-label small fw-semibold">
                            Дней до/после окончания
                            <i class="bi bi-question-circle text-muted ms-1" data-bs-toggle="tooltip" 
                               title="Положительные: осталось дней до истечения (включая меньшие значения). Отрицательные: дней после истечения (включая меньшие по модулю). Например: 5 - истекают через 5 дней или меньше, -5 - истекли 5 дней назад или раньше"></i>
                        </label>
                        <input type="number" name="days_to_end" id="days_to_end" 
                               class="form-control" value="{{ request('days_to_end') }}" 
                               placeholder="Например: 5 или -5">
                        <small class="text-muted">
                            @if(request('days_to_end') !== null && request('days_to_end') !== '')
                                @php
                                    $days = (int)request('days_to_end');
                                @endphp
                                @if($days > 0)
                                    Показываем подписки, которым осталось ≤ {{ $days }} дней
                                @elseif($days < 0)
                                    Показываем подписки, которые истекли ≥ {{ abs($days) }} дней назад
                                @else
                                    Показываем подписки, истекающие сегодня
                                @endif
                            @else
                                Положительные: ≤ N дней до конца, Отрицательные: ≥ N дней после конца
                            @endif
                        </small>
                    </div>
                    
                    <!-- Кнопки действий -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Применить фильтры
                            </button>
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Активные фильтры -->
            @if(request()->anyFilled(['organization_id', 'user_id', 'manager_id', 'status', 'days_to_end']))
                <div class="alert alert-info py-2 mt-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <i class="bi bi-funnel-fill me-1"></i>
                        <span class="fw-semibold">Активные фильтры:</span>
                        
                        @if(request('manager_id'))
                            @php 
                                $mgr = isset($managers) ? $managers->firstWhere('id', request('manager_id')) : null; 
                            @endphp
                            <span class="badge bg-info text-white">Менеджер: {{ $mgr->name ?? 'ID: ' . request('manager_id') }}</span>
                        @endif
                        
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
                        
                        @if(request('days_to_end') !== null && request('days_to_end') !== '')
                            @php
                                $days = (int)request('days_to_end');
                                if ($days > 0) {
                                    $text = "Осталось ≤ {$days} дней до истечения";
                                } elseif ($days < 0) {
                                    $text = "Истекло ≥ " . abs($days) . " дней назад";
                                } else {
                                    $text = "Истекают сегодня";
                                }
                            @endphp
                            <span class="badge bg-info text-white">{{ $text }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

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
                            
                            $subscriptionName = $subscription->name ?? 'Подписка #' . $subscription->id;
                            
                            // Определяем менеджера организации (для отображения)
                            $manager = $userOrg ? $userOrg->manager : null;
                        @endphp
                        
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="heading{{ $subscription->id }}">
                                <button class="accordion-button collapsed px-4 py-3" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $subscription->id }}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse{{ $subscription->id }}">
                                    <div class="row w-100 align-items-center g-2">
                                        <!-- ID -->
                                        <div class="col-lg-1 col-md-1">
                                            <span class="badge bg-secondary">#{{ $subscription->id }}</span>
                                        </div>
                                        
                                        <!-- Название подписки -->
                                        <div class="col-lg-2 col-md-2">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; font-size: 0.8rem; color: #0dcaf0;">
                                                    <i class="bi bi-tag"></i>
                                                </div>
                                                <span class="fw-semibold small text-truncate" style="max-width: 120px;" title="{{ $subscriptionName }}">
                                                    {{ Str::limit($subscriptionName, 15) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Пользователь -->
                                        <div class="col-lg-2 col-md-2">
                                            @if($user)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-{{ $user->getRoleColor() }} bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; font-size: 0.8rem; color: {{ $user->getRoleColor() === 'success' ? '#198754' : ($user->getRoleColor() === 'danger' ? '#fd7e14' : '#0d6efd') }};">
                                                        {{ $userInitial }}
                                                    </div>
                                                    <div class="text-truncate" style="max-width: 100px;">
                                                        <span class="small fw-semibold">{{ Str::limit($user->name, 12) }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">Пользователь удален</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Организация -->
                                        <div class="col-lg-2 col-md-2">
                                            @if($userOrg)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; font-size: 0.8rem; color: #198754;">
                                                        {{ strtoupper(substr($userOrg->name, 0, 1)) }}
                                                    </div>
                                                    <span class="small text-truncate" style="max-width: 100px;">{{ Str::limit($userOrg->name, 12) }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Менеджер (НОВЫЙ) -->
                                        <div class="col-lg-1 col-md-1">
                                            @if($manager)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-1" 
                                                         style="width: 24px; height: 24px; font-size: 0.7rem; color: #0d6efd;">
                                                        {{ strtoupper(substr($manager->name, 0, 1)) }}
                                                    </div>
                                                    <span class="small text-truncate d-none d-lg-inline" style="max-width: 60px;" title="{{ $manager->name }}">
                                                        {{ Str::limit($manager->name, 8) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Статус и даты -->
                                        <div class="col-lg-2 col-md-2">
                                            <span class="badge bg-{{ $statusClass }}">{{ $subscription->getStatusTextAttribute() }}</span>
                                            @if($subscription->ends_at)
                                                @if($remainingDays !== null)
                                                    <small class="d-block text-{{ $remainingDays <= 7 && $subscription->isActive() ? 'warning' : 'muted' }}">
                                                        @if($remainingDays > 0)
                                                            осталось {{ $remainingDays }} дн.
                                                        @elseif($remainingDays === 0)
                                                            истекает сегодня
                                                        @else
                                                            просрочено на {{ abs($remainingDays) }} дн.
                                                        @endif
                                                    </small>
                                                @endif
                                            @else
                                                <small class="d-block text-muted">бессрочно</small>
                                            @endif
                                        </div>
                                        
                                        <!-- Сводка по лимитам -->
                                        <div class="col-lg-2 col-md-2">
                                            @if($hasLimits)
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-1">{{ $totalLimits }}</span>
                                                    <div class="progress flex-grow-1" style="height: 6px;">
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
                                        <div class="col-lg-1 col-md-1 text-end">
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
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-tag text-info"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Название</small>
                                                    <span class="fw-semibold">{{ $subscriptionName }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-calendar-plus text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Дата начала</small>
                                                    <span class="fw-semibold">{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-calendar-x text-warning"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Дата окончания</small>
                                                    <span class="fw-semibold">{{ $subscription->ends_at ? $subscription->ends_at->format('d.m.Y') : 'Бессрочно' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-clock text-primary"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Создана</small>
                                                    <span class="fw-semibold">{{ $subscription->created_at->format('d.m.Y H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <a href="{{ route('subscriptions.edit', $subscription) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Редактировать
                                        </a>
                                    </div>

                                    <!-- БЛОК СО ССЫЛКАМИ НА ПОЛЬЗОВАТЕЛЯ И ОРГАНИЗАЦИЮ -->
                                    <div class="row g-3 mb-3">
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
                                                        
                                                        <!-- Информация о менеджере организации -->
                                                        @if($manager)
                                                        <div class="mt-2 pt-2 border-top">
                                                            <small class="text-muted d-block mb-1">Менеджер</small>
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                                     style="width: 28px; height: 28px; font-size: 0.8rem; color: #0d6efd;">
                                                                    {{ strtoupper(substr($manager->name, 0, 1)) }}
                                                                </div>
                                                                <a href="{{ route('admin.managers.show', $manager->id) }}" class="text-decoration-none">
                                                                    {{ $manager->name }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                        @endif
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
                                                <table class="table table-sm table-hover bg-white">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Тип отчета</th>
                                                            <th class="text-center">Всего</th>
                                                            <th class="text-center">Использовано</th>
                                                            <th class="text-center">Доступно</th>
                                                            <th class="text-center">Прогресс</th>
                                                            <th class="text-center">Дата</th>
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
                                                                        <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                                             style="width: 28px; height: 28px; font-size: 0.8rem; color: #0dcaf0;">
                                                                            {{ strtoupper(substr($limit->reportType->name ?? 'О', 0, 1)) }}
                                                                        </div>
                                                                        <strong>{{ $limit->reportType->name ?? 'Неизвестный тип' }}</strong>
                                                                        @if($limit->reportType && $limit->reportType->only_api)
                                                                            <span class="badge bg-warning ms-2">API</span>
                                                                        @endif
                                                                    </div>
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
                                                                <td>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <div class="progress flex-grow-1" style="height: 6px;">
                                                                            <div class="progress-bar bg-{{ $progressClass }}" 
                                                                                style="width: {{ $percentage }}%">
                                                                            </div>
                                                                        </div>
                                                                        <small class="text-muted">{{ $percentage }}%</small>
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
                                                <i class="bi bi-plus-lg me-1"></i> Добавить лимиты
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Пагинация -->
                @if($subscriptions->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4 px-4 pb-4">
                        <div class="text-muted small">
                            Показано {{ $subscriptions->firstItem() ?? 0 }} - {{ $subscriptions->lastItem() ?? 0 }} 
                            из {{ $subscriptions->total() }} подписок
                        </div>
                        <div>
                            {{ $subscriptions->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-stars fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">Подписки не найдены</h5>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['organization_id', 'user_id', 'manager_id', 'status', 'days_to_end']))
                            Попробуйте изменить параметры фильтрации
                        @else
                            Создайте первую подписку
                        @endif
                    </p>
                    @if(!request()->anyFilled(['organization_id', 'user_id', 'manager_id', 'status', 'days_to_end']))
                        <a href="{{ route('subscriptions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i> Создать подписку
                        </a>
                    @else
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i> Сбросить фильтры
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно удаления подписки -->
<div class="modal fade" id="deleteSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-trash me-2"></i>
                    Удаление подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                    </div>
                    <p>Вы уверены, что хотите удалить эту подписку?</p>
                    <p class="text-danger"><small>Это действие также удалит все лимиты, связанные с этой подпиской.</small></p>
                </div>
            </div>
            <div class="modal-footer border-0">
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Редактирование подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-center py-3">Функция редактирования в разработке</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ТОЧНО КАК ВО ВСЕХ СТРАНИЦАХ */

    /* Круги всегда идеальные */
    .rounded-circle {
        border-radius: 50% !important;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Карточки - скругление 1rem (16px) */
    .card {
        border-radius: 1rem;
        transition: all 0.2s;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Бейджи - сильно скругленные 30px */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        border-radius: 30px;
        padding: 0.35em 0.65em;
    }

    /* Заголовки карточек */
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem 1.25rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 600;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* Градиент для заголовка */
    .bg-gradient-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
    }

    /* Таблицы */
    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
    }

    /* Для иконок в кружках */
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    /* Для текста в кружках с буквами */
    .rounded-circle.bg-primary, 
    .rounded-circle.bg-success, 
    .rounded-circle.bg-info,
    .rounded-circle.bg-warning,
    .rounded-circle.bg-danger {
        font-weight: 500;
    }

    /* Для форм */
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Для алертов */
    .alert-info {
        background-color: #cff4fc;
        border-color: #b6effb;
        color: #055160;
        border-radius: 0.75rem;
    }

    .alert-success {
        border-radius: 0.75rem;
    }

    .alert-danger {
        border-radius: 0.75rem;
    }

    /* Модальное окно */
    .modal-content {
        border-radius: 1rem;
        border: none;
    }

    .modal-header {
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    /* Select2 стили */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
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
    
    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    /* Анимация для иконки */
    .accordion-button .bi-chevron-down {
        transition: transform 0.3s;
    }
    
    .accordion-button:not(.collapsed) .bi-chevron-down {
        transform: rotate(180deg);
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
                        organization_id: $('#organization_id').val(),
                        roles: ['org_owner', 'org_member', 'manager', 'admin']
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(user) {
                            return {
                                id: user.id,
                                text: user.name + ' (' + user.email + ')',
                                name: user.name,
                                email: user.email,
                                role: user.role_display,
                                role_code: user.role,
                                organization: user.organization || 'Нет организации',
                                has_active: user.has_active_subscriptions || false
                            };
                        })
                    };
                },
                cache: true
            },
            templateResult: formatUser,
            templateSelection: formatUserSelection
        });

        // Инициализация Select2 для менеджеров
        $('.select2-manager').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Выберите менеджера',
            allowClear: true,
            width: '100%'
        });

        // Компактный формат отображения пользователя в списке
        function formatUser(user) {
            if (user.loading) return user.text;
            
            let roleBadge = '';
            if (user.role_code === 'org_owner') {
                roleBadge = '<span class="badge bg-success ms-1">Владелец</span>';
            } else if (user.role_code === 'org_member') {
                roleBadge = '<span class="badge bg-info ms-1">Сотрудник</span>';
            } else if (user.role_code === 'manager') {
                roleBadge = '<span class="badge bg-primary ms-1">Менеджер</span>';
            } else if (user.role_code === 'admin') {
                roleBadge = '<span class="badge bg-danger ms-1">Админ</span>';
            }
            
            let status = user.has_active ? '<span class="badge bg-warning ms-2"><i class="bi bi-check-circle-fill"></i> есть подписка</span>' : '';
            let organizationDisplay = user.organization !== 'Нет организации' ? user.organization : '';
            
            return $(`
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>${user.name}</strong> ${roleBadge}<br>
                        <small class="text-muted"><i class="bi bi-envelope"></i> ${user.email}</small>
                        ${organizationDisplay ? `<small class="text-muted ms-2"><i class="bi bi-building"></i> ${organizationDisplay}</small>` : ''}
                    </div>
                    <div>${status}</div>
                </div>
            `);
        }

        function formatUserSelection(user) {
            return user.name || user.text;
        }

        // При изменении организации - обновляем список пользователей
        $('#organization_id').on('change', function() {
            $('.select2-user').val(null).trigger('change');
            $('#filterForm').submit();
        });

        // При изменении менеджера - отправляем форму
        $('#manager_id').on('change', function() {
            $('#filterForm').submit();
        });

        // Автоматическая отправка формы при изменении полей
        $('select[name="status"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Отправка формы при изменении пользователя
        $('.select2-user').on('change', function() {
            $('#filterForm').submit();
        });

        // Debounce для поля days_to_end
        let daysTimer;
        $('input[name="days_to_end"]').on('input', function() {
            clearTimeout(daysTimer);
            daysTimer = setTimeout(() => {
                $('#filterForm').submit();
            }, 500);
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