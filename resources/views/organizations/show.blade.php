@extends('layouts.app')

@section('title', $organization->name)

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
        $routePrefix = $isAdmin ? 'admin.' : 'manager.';
        
        // Проверяем права на делегирование
        $canDelegateAny = false;
        $currentUserId = Auth::id();
        $ownerId = $organization->owner->user_id ?? null;
        
        if ($ownerId) {
            if ($currentUserId == $ownerId) {
                // Сам владелец может делегировать
                $canDelegateAny = true;
            } elseif ($isAdmin) {
                // Админ может делегировать
                $canDelegateAny = true;
            } elseif ($isManager) {
                // Менеджер может делегировать, если он является менеджером этой организации
                $canDelegateAny = $organization->manager && $organization->manager->id == $currentUserId;
            }
        }
    @endphp

    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" 
                 style="width: 50px; height: 50px; color: white;">
                <i class="bi bi-building fs-4"></i>
            </div>
            <div>
                <h1 class="h3 mb-0">{{ $organization->name }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @if($isAdmin)
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Панель админа</a></li>
                        @elseif($isManager)
                            <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Панель менеджера</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($organization->name, 20) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($isAdmin || $isManager)
                <a href="{{ route($routePrefix . 'organization.edit', $organization->id) }}" 
                   class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Редактировать
                </a>
            @endif
            <a href="{{ $isAdmin ? route('admin.dashboard') : route('manager.dashboard') }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Назад
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

    <!-- Статистика организации -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="display-4 text-primary mb-2">{{ $organization->members->count() }}</div>
                    <div class="text-muted">Сотрудников</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="display-4 text-success mb-2">{{ $organization->members->where('is_active', true)->count() }}</div>
                    <div class="text-muted">Активных</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    @if($organization->subscription_ends_at)
                        <div class="{{ $organization->subscription_ends_at->isPast() ? 'text-danger' : 'text-success' }} display-4 mb-2">
                            {{ $organization->subscription_ends_at->format('d.m.Y') }}
                        </div>
                        <div class="text-muted">Подписка до</div>
                    @else
                        <div class="display-4 text-success mb-2">∞</div>
                        <div class="text-muted">Подписка</div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    @if($organization->status === 'active')
                        <span class="badge bg-success fs-5 px-3 py-2">Активна</span>
                    @elseif($organization->status === 'suspended')
                        <span class="badge bg-warning fs-5 px-3 py-2">Приостановлена</span>
                    @elseif($organization->status === 'expired')
                        <span class="badge bg-danger fs-5 px-3 py-2">Истекла</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-8">
            <!-- Карточка информации об организации -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Информация об организации
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">{{ $organization->name }}</h5>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-hash text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">ID:</span>
                                    <span class="ms-2 fw-bold">#{{ $organization->id }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Создана:</span>
                                    <span class="ms-2">{{ $organization->created_at->format('d.m.Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar-check text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Подписка до:</span>
                                    <div class="ms-2">
                                        @if($organization->subscription_ends_at)
                                            @if($organization->subscription_ends_at->isPast())
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                                </span>
                                            @elseif($organization->subscription_ends_at->diffInDays(now()) < 7)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Бессрочно</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- ИСПРАВЛЕНО: Отображение менеджера -->
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-badge text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Менеджер:</span>
                                    <div class="ms-2">
                                        @if($organization->manager)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($organization->manager->name, 0, 1)) }}
                                                </div>
                                                <div>{{ $organization->manager->name }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Владелец организации -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-success me-2"></i>
                        Владелец организации
                    </h5>
                    @if($isAdmin && $organization->owner && $organization->owner->user)
                        <a href="{{ route($routePrefix . 'organization.edit', $organization->id) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i> Редактировать
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($organization->owner && $organization->owner->user)
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px; color: white; font-size: 2rem;">
                                    {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h4 class="mb-2">{{ $organization->owner->user->name }}</h4>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-envelope text-muted me-2"></i>
                                    <span>{{ $organization->owner->user->email }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-calendar text-muted me-2"></i>
                                    <span>Зарегистрирован: {{ $organization->owner->user->created_at->format('d.m.Y') }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-success">Владелец</span>
                                    @if($organization->owner->user->is_active)
                                        <span class="badge bg-info">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Неактивен</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-person-x display-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-3">Владелец не назначен</h5>
                            @if($isAdmin)
                                <a href="{{ route('admin.organization.edit', $organization->id) }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i> Назначить владельца
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Лимиты владельца -->
            @if(isset($ownerLimits) && count($ownerLimits) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-speedometer text-info me-2"></i>
                            Отчеты владельца
                            <small class="text-muted ms-2">({{ now()->format('d.m.Y') }})</small>
                        </h5>
                        <div class="d-flex gap-2">
                            @if($canDelegateAny && isset($availableEmployees) && $availableEmployees->count() > 0 && count(array_filter($ownerLimits, fn($l) => $l['available_amount'] > 0)) > 0)
                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#delegateModal">
                                    <i class="bi bi-share"></i> Делегировать
                                </button>
                            @endif
                            <span class="badge bg-secondary">
                                {{ count($ownerLimits) }} тип(ов)
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Тип отчета</th>
                                    <th>Дата действия</th>
                                    <th>Выделено</th>
                                    <th>Использовано</th>
                                    <th>Делегировано</th>
                                    <th>Доступно</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ownerLimits as $limit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ $limit['report_type_name'] }}</strong>
                                            @if($limit['only_api'])
                                                <span class="badge bg-warning ms-2">API</span>
                                            @else
                                                <span class="badge bg-primary ms-2">UI</span>
                                            @endif
                                        </div>
                                        @if($limit['description'])
                                            <small class="text-muted d-block mt-1">{{ $limit['description'] }}</small>
                                        @endif
                                    </td>
                                    <td>{{ date('d.m.Y', strtotime($limit['date_created'])) }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $limit['total_allocated'] }} шт.</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $limit['used_quantity'] }} шт.</span>
                                    </td>
                                    <td>
                                        @if($limit['delegated_amount'] > 0)
                                            <span class="badge bg-warning">{{ $limit['delegated_amount'] }} шт.</span>
                                        @else
                                            <span class="text-muted">0 шт.</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $limit['available_amount'] > 0 ? 'success' : 'danger' }}">
                                            {{ $limit['available_amount'] }} шт.
                                        </span>
                                    </td>
                                    <td>
                                        @if($limit['available_amount'] <= 0)
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
            </div>
            @endif
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Действия -->
            @if($isAdmin)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-gear text-warning me-2"></i>
                        Действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($organization->owner && $organization->owner->user)
                            <a href="{{ route($routePrefix . 'organization.edit', $organization->id) }}" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i> Редактировать организацию
                            </a>
                        @endif

                        <form action="{{ route('admin.organization.toggle-status', $organization->id) }}" 
                              method="POST" class="d-grid">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-outline-{{ $organization->status == 'active' ? 'warning' : 'success' }}">
                                <i class="bi bi-toggle-{{ $organization->status == 'active' ? 'off' : 'on' }} me-1"></i>
                                {{ $organization->status == 'active' ? 'Деактивировать' : 'Активировать' }}
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger" 
                                onclick="confirmDelete({{ $organization->id }}, '{{ $organization->name }}')">
                            <i class="bi bi-trash me-1"></i> Удалить организацию
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- ИСПРАВЛЕНО: Ответственный менеджер -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        Ответственный менеджер
                    </h5>
                </div>
                <div class="card-body">
                    @if($organization->manager)
                        <div class="text-center">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 60px; height: 60px; color: white; font-size: 1.5rem;">
                                {{ strtoupper(substr($organization->manager->name, 0, 1)) }}
                            </div>
                            <h6 class="mb-1">{{ $organization->manager->name }}</h6>
                            <p class="text-muted small mb-3">{{ $organization->manager->email }}</p>
                            
                            @if($organization->manager->id === Auth::id())
                                <span class="badge bg-success">Это вы</span>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-person-slash text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-0">Менеджер не назначен</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning text-success me-2"></i>
                        Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                        </a>
                        
                        @if($organization->members->count() > 0)
                            <a href="{{ route('reports.create') }}" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-plus me-1"></i> Создать отчет
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Делегированные лимиты -->
    @if(isset($delegatedLimits) && $delegatedLimits->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-share text-warning me-2"></i>
                Делегированные отчеты
                <span class="badge bg-warning ms-2">{{ $delegatedLimits->count() }}</span>
            </h5>
            <small class="text-muted">
                Всего делегировано: {{ $delegatedLimits->sum('quantity') }} шт.
            </small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Сотрудник</th>
                            <th>Тип отчета</th>
                            <th>Дата лимита</th>
                            <th>Делегировано/Использовано</th>
                            <th>Дата делегирования</th>
                            <th>Статус</th>
                            @if($canDelegateAny)
                            <th>Действия</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delegatedLimits as $delegated)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px; color: white; font-size: 0.8rem;">
                                        {{ strtoupper(substr($delegated->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div>{{ $delegated->user->name }}</div>
                                        <small class="text-muted">{{ $delegated->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $delegated->limit->reportType->name ?? 'Не указан' }}</strong>
                            </td>
                            <td>{{ $delegated->limit->date_created->format('d.m.Y') }}</td>
                            <td>
                                <div>
                                    <span class="badge bg-warning mb-1">{{ $delegated->quantity }} шт.</span>
                                    @if($delegated->used_quantity > 0)
                                        <br>
                                        <small class="text-muted">использовано: {{ $delegated->used_quantity }} шт.</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $delegated->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($delegated->is_active)
                                    @php
                                        $availableDelegated = $delegated->quantity - $delegated->used_quantity;
                                    @endphp
                                    @if($availableDelegated <= 0)
                                        <span class="badge bg-danger">Исчерпан</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                            @if($canDelegateAny)
                            <td>
                                <form action="{{ route('delegated-limits.destroy', $delegated) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to_organization" value="{{ $organization->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Возвратить лимит? Лимит вернется владельцу.')"
                                            title="Возвратить лимит">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
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
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="bi bi-people text-primary me-2"></i>
                    Сотрудники организации
                    <span class="badge bg-primary ms-2">{{ $organization->members->count() }}</span>
                </h5>
                <small class="text-muted">Все сотрудники вашей организации</small>
            </div>
            @if($isAdmin || $isManager)
                <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                </a>
            @endif
        </div>
        <div class="card-body">
            @if($organization->members->count() > 0)
                <div class="row">
                    @foreach($organization->members as $member)
                    @php
                        $memberDelegated = isset($delegatedLimits) ? $delegatedLimits->where('user_id', $member->user->id) : collect();
                        $memberTotalDelegated = $memberDelegated->sum('quantity');
                        $memberTotalUsed = $memberDelegated->sum('used_quantity');
                        $memberTotalAvailable = $memberTotalDelegated - $memberTotalUsed;
                        $hasDelegated = $memberTotalDelegated > 0;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card border h-100">
                            <div class="card-body p-3">
                                <!-- Заголовок сотрудника -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; color: white; font-size: 1rem;">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold">{{ $member->user->name }}</h6>
                                        <small class="text-muted d-block">{{ $member->user->email }}</small>
                                        <div class="d-flex gap-1 mt-1">
                                            @if($member->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-danger">Неактивен</span>
                                            @endif
                                            @if($hasDelegated)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-share"></i> {{ $memberDelegated->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Статистика по лимитам -->
                                @if($hasDelegated)
                                    <div class="mb-3 border-top pt-2">
                                        <small class="text-muted d-block mb-2">Статистика по лимитам:</small>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Делегировано:</small>
                                            <span class="badge bg-warning bg-opacity-25 text-dark">{{ $memberTotalDelegated }} шт.</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Использовано:</small>
                                            <span class="badge bg-info bg-opacity-25 text-dark">{{ $memberTotalUsed }} шт.</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small>Доступно:</small>
                                            <span class="badge bg-{{ $memberTotalAvailable > 0 ? 'success' : 'danger' }} bg-opacity-25 text-dark">
                                                {{ $memberTotalAvailable }} шт.
                                            </span>
                                        </div>
                                        
                                        <!-- Виды лимитов -->
                                        @if($memberDelegated->count() > 0)
                                            <small class="text-muted d-block mb-1">Виды лимитов:</small>
                                            <div class="mt-1">
                                                @foreach($memberDelegated->take(2) as $delegated)
                                                    @php
                                                        $delegatedAvailable = $delegated->quantity - $delegated->used_quantity;
                                                        $percentage = $delegated->quantity > 0 ? round(($delegatedAvailable / $delegated->quantity) * 100) : 0;
                                                    @endphp
                                                    <div class="mb-2">
                                                        <small class="d-block text-truncate" title="{{ $delegated->limit->reportType->name ?? 'Лимит' }}">
                                                            {{ $delegated->limit->reportType->name ?? 'Лимит' }}
                                                        </small>
                                                        <div class="progress" style="height: 4px;">
                                                            <div class="progress-bar bg-{{ $percentage > 20 ? 'success' : ($percentage > 0 ? 'warning' : 'danger') }}" 
                                                                 style="width: {{ $percentage }}%">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <small class="text-muted">{{ $delegatedAvailable }} шт.</small>
                                                            <small class="text-muted">из {{ $delegated->quantity }} шт.</small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @if($memberDelegated->count() > 2)
                                                    <div class="text-center">
                                                        <small class="text-muted">
                                                            + еще {{ $memberDelegated->count() - 2 }} видов лимитов
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="border-top pt-3 text-center">
                                        <i class="bi bi-share fs-4 text-muted mb-2 d-block"></i>
                                        <small class="text-muted">Нет делегированных лимитов</small>
                                    </div>
                                @endif
                                
                                <!-- Кнопки действий -->
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route($routePrefix . 'org-members.show', [$organization->id, $member->id]) }}" 
                                               class="btn btn-sm btn-outline-info" title="Просмотр">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($isAdmin || $isManager)
                                                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                                                   class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if($canDelegateAny && isset($ownerLimits) && count($ownerLimits) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-warning delegate-btn"
                                                    data-employee-id="{{ $member->user->id }}"
                                                    data-employee-name="{{ $member->user->name }}"
                                                    data-owner-id="{{ $organization->owner->user_id ?? '' }}"
                                                    title="Делегировать лимит">
                                                <i class="bi bi-share"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-people display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Сотрудников пока нет</h4>
                    <p class="text-muted mb-4">Добавьте первого сотрудника в вашу организацию</p>
                    @if($isAdmin || $isManager)
                        <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно делегирования -->
@if($canDelegateAny && isset($ownerLimits) && count($ownerLimits) > 0 && isset($availableEmployees) && $availableEmployees->count() > 0)
<div class="modal fade" id="delegateModal" tabindex="-1" aria-labelledby="delegateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('delegated-limits.store') }}" method="POST" id="delegateForm">
                @csrf
                <input type="hidden" name="redirect_to_organization" value="{{ $organization->id }}">
                @if($ownerId && $ownerId != Auth::id())
                    <input type="hidden" name="owner_id" value="{{ $ownerId }}">
                @endif
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="delegateModalLabel">
                        <i class="bi bi-share"></i> Делегирование лимита
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="limit_id" class="form-label">
                                    <i class="bi bi-tachometer"></i> Лимит владельца *
                                </label>
                                <select name="limit_id" id="limit_id" class="form-select" required>
                                    <option value="">Выберите лимит</option>
                                    @foreach($ownerLimits as $limit)
                                        @if($limit['available_amount'] > 0 && $limit['id'])
                                            <option value="{{ $limit['id'] }}" 
                                                    data-available="{{ $limit['available_amount'] }}"
                                                    data-name="{{ $limit['report_type_name'] }}"
                                                    data-date="{{ date('d.m.Y', strtotime($limit['date_created'])) }}">
                                                {{ $limit['report_type_name'] }} 
                                                ({{ date('d.m.Y', strtotime($limit['date_created'])) }})
                                                - доступно {{ $limit['available_amount'] }} шт.
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="card border-info mb-3" id="limitInfo" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="mb-2" id="limitName"></h6>
                                    <div class="small">
                                        <div>Дата действия: <span id="limitDate"></span></div>
                                        <div>Доступно: <span class="badge bg-success" id="limitAvailable"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="bi bi-person"></i> Сотрудник *
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
                            
                            <div class="card border-primary mb-3" id="employeeInfo" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="mb-2" id="employeeName"></h6>
                                    <div class="small" id="employeeDelegated">
                                        <i class="bi bi-info-circle"></i> Загрузка информации...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity" class="form-label">
                            <i class="bi bi-123"></i> Количество для делегирования *
                        </label>
                        <div class="input-group mb-2">
                            <input type="number" name="quantity" id="quantity" 
                                   class="form-control" 
                                   min="1" 
                                   value="1"
                                   required>
                            <span class="input-group-text">шт.</span>
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="setDelegateAmount(5)">
                                +5
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="setDelegateAmount(10)">
                                +10
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setMaxDelegateAmount()">
                                Максимум
                            </button>
                        </div>
                        <small class="text-muted">
                            Максимально можно делегировать: <span id="maxAmount">0</span> шт.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-share"></i> Делегировать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($isAdmin)
<!-- Форма для удаления -->
<form id="delete-form" method="POST" action="{{ route('admin.organization.delete', $organization->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id, name) {
    if (confirm(`Вы уверены, что хотите удалить организацию "${name}"? Это действие удалит также владельца организации и всех сотрудников.`)) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endif

@push('scripts')
<script>
    // Инициализация тултипов
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Инициализация данных для делегирования
        @if(isset($ownerLimits) && count($ownerLimits) > 0)
        let limits = {
            @foreach($ownerLimits as $limit)
                @if($limit['id'] && $limit['available_amount'] > 0)
                    {{ $limit['id'] }}: {
                        available: {{ $limit['available_amount'] }},
                        name: '{{ addslashes($limit['report_type_name']) }}',
                        date: '{{ date('d.m.Y', strtotime($limit['date_created'])) }}'
                    },
                @endif
            @endforeach
        };
        
        let employees = {
            @foreach($availableEmployees as $employee)
                {{ $employee->id }}: {
                    name: '{{ addslashes($employee->name) }}',
                    delegated: {{ isset($delegatedLimits) ? $delegatedLimits->where('user_id', $employee->id)->sum('quantity') : 0 }},
                    types: {{ isset($delegatedLimits) ? $delegatedLimits->where('user_id', $employee->id)->count() : 0 }}
                },
            @endforeach
        };
        
        // Кнопки делегирования в карточках сотрудников
        $('.delegate-btn').on('click', function() {
            const employeeId = $(this).data('employee-id');
            $('#user_id').val(employeeId).trigger('change');
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
                
                // Проверка текущего значения количества
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
            const employeeId = $(this).val();
            const employee = employees[employeeId];
            
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
            
            const limitName = limits[limitId]?.name || 'лимит';
            const employeeName = employees[userId]?.name || 'сотруднику';
            
            if (!confirm(`Делегировать ${quantity} шт. лимита "${limitName}" сотруднику ${employeeName}?`)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Инициализация при открытии модального окна
        $('#delegateModal').on('shown.bs.modal', function() {
            $('#limit_id').trigger('change');
            $('#user_id').trigger('change');
        });
        @endif
    });
</script>
@endpush
@endsection