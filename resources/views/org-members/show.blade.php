@extends('layouts.app')

@section('title', $member->user->name)

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
        
        // Определяем префикс маршрута
        if ($isAdmin) {
            $routePrefix = 'admin.';
        } elseif ($isManager) {
            $routePrefix = 'manager.';
        } else {
            $routePrefix = 'owner.';
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-badge text-primary"></i> {{ $member->user->name }}
        </h1>
        <div>
            @if($isAdmin || $isManager)
                <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к организации
                </a>
            @else
                <a href="{{ route('owner.dashboard') }}" 
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад в панель
                </a>
            @endif
            
            @if($isAdmin || $isManager || $isOwner)
                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                   class="btn btn-primary ms-2">
                    <i class="bi bi-pencil"></i> Редактировать
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Левая колонка: Личная информация -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; color: white; font-size: 28px;">
                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                    </div>
                    <h4>{{ $member->user->name }}</h4>
                    <p class="text-muted">{{ $member->user->email }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-info">Сотрудник</span>
                        @if($member->user->is_active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-danger">Неактивен</span>
                        @endif
                    </div>
                    
                    <p class="text-muted small">
                        <i class="bi bi-calendar"></i> Зарегистрирован: {{ $member->user->created_at->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <!-- Статистика всех лимитов -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer text-primary me-2"></i>
                        Статистика лимитов
                    </h5>
                    @if(isset($totalAll) && $totalAll > 0)
                        <span class="badge bg-primary">{{ $delegatedLimits->count() + $personalLimits->count() }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if(isset($totalAll) && $totalAll > 0)
                        <!-- Общая статистика -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Всего лимитов:</small>
                                <span class="badge bg-primary">{{ $totalAll }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Использовано:</small>
                                <span class="badge bg-info">{{ $totalAllUsed }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small>Доступно всего:</small>
                                <span class="badge bg-{{ $totalAllAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $totalAllAvailable }} шт.
                                </span>
                            </div>
                            
                            <!-- Прогресс бар общий -->
                            <div class="progress mb-2" style="height: 8px;">
                                @php
                                    $allPercentage = $totalAll > 0 ? round(($totalAllUsed / $totalAll) * 100) : 0;
                                @endphp
                                <div class="progress-bar bg-{{ $allPercentage > 80 ? 'danger' : ($allPercentage > 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $allPercentage }}%">
                                </div>
                            </div>
                            <small class="text-muted d-block text-center">
                                Использовано {{ $allPercentage }}% от всех лимитов
                            </small>
                        </div>

                        <!-- Разделение на собственные и делегированные -->
                        <div class="row mb-3">
                            <!-- Собственные лимиты -->
                            <div class="col-6">
                                <div class="border p-2 rounded bg-light">
                                    <h6 class="text-center mb-2">
                                        <i class="bi bi-person-check text-success"></i>
                                        <small>Собственные</small>
                                    </h6>
                                    <div class="text-center mb-1">
                                        <span class="badge bg-{{ $totalPersonalAvailable > 0 ? 'success' : 'danger' }}">
                                            {{ $totalPersonalAvailable }}
                                        </span>
                                        <small class="d-block text-muted">доступно</small>
                                    </div>
                                    <div class="text-center">
                                        <span class="badge bg-secondary">{{ $totalPersonal }}</span>
                                        <small class="d-block text-muted">всего</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Делегированные лимиты -->
                            <div class="col-6">
                                <div class="border p-2 rounded bg-light">
                                    <h6 class="text-center mb-2">
                                        <i class="bi bi-share text-warning"></i>
                                        <small>Делегированные</small>
                                    </h6>
                                    <div class="text-center mb-1">
                                        <span class="badge bg-{{ $totalDelegatedAvailable > 0 ? 'success' : 'danger' }}">
                                            {{ $totalDelegatedAvailable }}
                                        </span>
                                        <small class="d-block text-muted">доступно</small>
                                    </div>
                                    <div class="text-center">
                                        <span class="badge bg-warning">{{ $totalDelegated }}</span>
                                        <small class="d-block text-muted">всего</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Типы лимитов -->
                        @if(!empty($delegatedLimitsByType) || !empty($personalLimitsByType))
                            <div class="border-top pt-3">
                                <small class="text-muted d-block mb-2">По типам отчетов:</small>
                                @php
                                    // Объединяем типы
                                    $allTypes = array_unique(
                                        array_merge(
                                            array_keys($personalLimitsByType),
                                            array_keys($delegatedLimitsByType)
                                        )
                                    );
                                @endphp
                                
                                @foreach($allTypes as $typeName)
                                    <div class="mb-2">
                                        <small class="text-truncate d-block mb-1" style="max-width: 120px;" title="{{ $typeName }}">
                                            {{ $typeName }}
                                        </small>
                                        
                                        <!-- Собственные лимиты по этому типу -->
                                        @if(isset($personalLimitsByType[$typeName]))
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small><i class="bi bi-person text-success me-1"></i> Собственные:</small>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $personalLimitsByType[$typeName]['available'] }}/{{ $personalLimitsByType[$typeName]['total'] }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <!-- Делегированные лимиты по этому типу -->
                                        @if(isset($delegatedLimitsByType[$typeName]))
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small><i class="bi bi-share text-warning me-1"></i> Делегированные:</small>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $delegatedLimitsByType[$typeName]['available'] }}/{{ $delegatedLimitsByType[$typeName]['delegated'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-2">
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-speedometer text-muted fs-1 mb-3 d-block"></i>
                            <p class="text-muted mb-0">У сотрудника нет лимитов</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Организация -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Организация</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 50px; height: 50px; color: white;">
                            <i class="bi bi-building"></i>
                        </div>
                        <h6>{{ $organization->name }}</h6>
                        @if($organization->status === 'active')
                            <span class="badge bg-success">Активна</span>
                        @elseif($organization->status === 'suspended')
                            <span class="badge bg-warning">Приостановлена</span>
                        @else
                            <span class="badge bg-danger">Истекла</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Действия для админа/менеджера/владельца -->
            @if($isAdmin || $isManager || $isOwner)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Действия</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Редактировать данные
                            </a>

                            <form action="{{ route($routePrefix . 'org-members.toggle-status', [$organization->id, $member->id]) }}" 
                                  method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-outline-{{ $member->is_active ? 'warning' : 'success' }} w-100">
                                    <i class="bi bi-toggle-{{ $member->is_active ? 'off' : 'on' }}"></i>
                                    {{ $member->is_active ? 'Деактивировать' : 'Активировать' }}
                                </button>
                            </form>

                            @if($isOwner)
                                <button type="button" class="btn btn-outline-warning delegate-btn"
                                        data-employee-id="{{ $member->user->id }}"
                                        data-employee-name="{{ $member->user->name }}">
                                    <i class="bi bi-share"></i> Делегировать лимит
                                </button>
                            @endif

                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Удалить сотрудника
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Правая колонка: Рабочая информация -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Рабочая информация</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Статус сотрудника:</th>
                                    <td>
                                        @if($member->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Добавлен:</th>
                                    <td>{{ $member->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                @if($member->boss && $member->boss->user)
                                <tr>
                                    <th>Начальник:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                {{ strtoupper(substr($member->boss->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div>{{ $member->boss->user->name }}</div>
                                                <small class="text-muted">{{ $member->boss->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Менеджер:</th>
                                    <td>
                                        @if($member->manager && $member->manager->user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($member->manager->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div>{{ $member->manager->user->name }}</div>
                                                    <small class="text-muted">{{ $member->manager->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Последнее обновление:</th>
                                    <td>{{ $member->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Организация:</th>
                                    <td>
                                        <span class="badge bg-secondary">{{ $organization->name }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Табы для лимитов -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="limitsTab" role="tablist">
                        @if($personalLimits->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" 
                                        data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person-check me-1"></i>
                                    Собственные лимиты
                                    <span class="badge bg-success ms-1">{{ $personalLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                        @if($delegatedLimits->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $personalLimits->count() == 0 ? 'active' : '' }}" 
                                        id="delegated-tab" data-bs-toggle="tab" 
                                        data-bs-target="#delegated" type="button" role="tab">
                                    <i class="bi bi-share me-1"></i>
                                    Делегированные лимиты
                                    <span class="badge bg-warning ms-1">{{ $delegatedLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="limitsTabContent">
                        <!-- Собственные лимиты -->
                        @if($personalLimits->count() > 0)
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Тип отчета</th>
                                                <th>Общий лимит</th>
                                                <th>Использовано</th>
                                                <th>Доступно</th>
                                                <th>Дата создания</th>
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
                                                    <strong>{{ $limit->reportType->name ?? 'Без типа' }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $limit->quantity }} шт.</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $limit->used_quantity }} шт.</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
                                                        {{ $available }} шт.
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $limit->date_created->format('d.m.Y') }}<br>
                                                    <small class="text-muted">{{ $limit->created_at->format('H:i') }}</small>
                                                </td>
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
                            <div class="tab-pane fade {{ $personalLimits->count() == 0 ? 'show active' : '' }}" 
                                 id="delegated" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Тип отчета</th>
                                                <th>От кого</th>
                                                <th>Делегировано</th>
                                                <th>Использовано</th>
                                                <th>Доступно</th>
                                                <th>Дата делегирования</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($delegatedLimits as $delegated)
                                            @php
                                                $available = $delegated->quantity - $delegated->used_quantity;
                                                $originalUser = $delegated->limit->user ?? null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $delegated->limit->reportType->name ?? 'Без типа' }}</strong>
                                                </td>
                                                <td>
                                                    @if($originalUser)
                                                        <small>
                                                            <i class="bi bi-person-up"></i>
                                                            {{ $originalUser->name }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">Неизвестно</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">{{ $delegated->quantity }} шт.</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $delegated->used_quantity }} шт.</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
                                                        {{ $available }} шт.
                                                    </span>
                                                </td>
                                                <td>{{ $delegated->created_at->format('d.m.Y H:i') }}</td>
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

                        <!-- Нет лимитов -->
                        @if($personalLimits->count() == 0 && $delegatedLimits->count() == 0)
                            <div class="text-center py-3">
                                <i class="bi bi-clipboard-x text-muted fs-1 mb-3 d-block"></i>
                                <p class="text-muted mb-0">У сотрудника нет доступных лимитов</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Карточки статистики -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h2 class="text-primary mb-0">{{ $totalReports }}</h2>
                            <small class="text-muted">Всего отчетов</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h2 class="text-success mb-0">{{ $thisMonthReports }}</h2>
                            <small class="text-muted">За этот месяц</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h2 class="text-warning mb-0">{{ $inProgressReports }}</h2>
                            <small class="text-muted">В работе</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h2 class="text-info mb-0">{{ $completedReports }}</h2>
                            <small class="text-muted">Завершено</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детальная статистика по типам отчетов -->
            @if($reportsByType->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart text-primary me-2"></i>
                        Статистика по типам отчетов
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Тип отчета</th>
                                    <th>Всего</th>
                                    <th>Завершено</th>
                                    <th>В работе</th>
                                    <th>Процент завершения</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportsByType as $typeId => $stats)
                                @php
                                    $percentage = $stats['count'] > 0 ? round(($stats['completed'] / $stats['count']) * 100) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $reportTypes[$typeId] ?? 'Тип #' . $typeId }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $stats['count'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $stats['completed'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $stats['pending'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'info') }}" 
                                                    style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted" style="min-width: 40px;">
                                                {{ $percentage }}%
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td><strong>Итого:</strong></td>
                                    <td><strong>{{ $totalReports }}</strong></td>
                                    <td><strong>{{ $completedReports }}</strong></td>
                                    <td><strong>{{ $inProgressReports }}</strong></td>
                                    <td>
                                        @php
                                            $totalPercentage = $totalReports > 0 ? round(($completedReports / $totalReports) * 100) : 0;
                                        @endphp
                                        <strong>{{ $totalPercentage }}%</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i>
                У сотрудника пока нет созданных отчетов
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно делегирования (если есть доступные лимиты) -->
@if($isOwner)
<div class="modal fade" id="delegateModal" tabindex="-1" aria-labelledby="delegateModalLabel" aria-hidden="true">
    <!-- Содержимое модального окна для делегирования -->
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('owner.org-members.delegate-limit', [$organization->id, $member->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="delegateModalLabel">
                        <i class="bi bi-share me-2"></i>
                        Делегировать лимит сотруднику
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Выберите тип лимита:</label>
                        <select name="limit_type" class="form-select" id="limitTypeSelect">
                            <option value="">Выберите тип отчета</option>
                            <!-- Можно добавить опции через JS или PHP -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Количество:</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Дополнительная информация:</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="При необходимости добавьте комментарий..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Делегировать</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($isAdmin || $isManager || $isOwner)
<!-- Форма для удаления -->
<form id="delete-form" method="POST" 
      action="{{ route($routePrefix . 'org-members.delete', [$organization->id, $member->id]) }}" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete() {
    const memberName = "{{ $member->user->name }}";
    if (confirm(`Вы уверены, что хотите удалить сотрудника "${memberName}"? Это действие удалит его учетную запись и все связанные данные.`)) {
        document.getElementById('delete-form').submit();
    }
}

// Инициализация табов
document.addEventListener('DOMContentLoaded', function() {
    const triggerTabList = [].slice.call(document.querySelectorAll('#limitsTab button'))
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl)
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
    
    // Обработка кнопки делегирования
    const delegateBtn = document.querySelector('.delegate-btn');
    if (delegateBtn) {
        delegateBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('delegateModal'));
            modal.show();
        });
    }
});
</script>
@endif
@endsection