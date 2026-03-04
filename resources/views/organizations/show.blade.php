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
        
        // Статистика по сотрудникам для новых полей
        $currentEmployeesCount = $organization->members->count();
        $availableEmployeeSlots = $organization->max_employees ? max(0, $organization->max_employees - $currentEmployeesCount) : null;
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
                <!-- ДОБАВЛЕНО: Отображение "Наша организация" -->
                @if($organization->our_organization)
                    <div class="d-flex align-items-center mt-1">
                        <span class="badge bg-info me-2">Наша организация:</span>
                        <span class="fw-semibold">{{ $organization->our_organization }}</span>
                    </div>
                @endif
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
               class="btn btn-secondary">
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
                    <div class="display-4 text-primary mb-2">{{ $currentEmployeesCount }}</div>
                    <div class="text-muted">Сотрудников</div>
                    @if($organization->max_employees)
                        <small class="text-muted">из {{ $organization->max_employees }}</small>
                    @endif
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
                                <!-- ДОБАВЛЕНО: Отображение "Наша организация" в деталях -->
                                @if($organization->our_organization)
                                <div class="mb-3 p-3 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building fs-4 text-info me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Наша организация</small>
                                            <span class="fw-bold">{{ $organization->our_organization }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
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
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-file-text text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">ИНН:</span>
                                    <span class="ms-2">{{ $organization->inn ?? 'Не указан' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">                                
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-people text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Лимит сотрудников:</span>
                                    <div class="ms-2">
                                        @if($organization->max_employees)
                                            <span class="badge bg-info">{{ $organization->max_employees }} чел.</span>
                                            <small class="text-muted ms-1">({{ $currentEmployeesCount }} / {{ $organization->max_employees }})</small>
                                        @else
                                            <span class="badge bg-secondary">Не ограничен</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-badge text-muted me-2" style="width: 20px;"></i>
                                    <span class="text-muted">Свободных мест:</span>
                                    <div class="ms-2">
                                        @if($organization->max_employees)
                                            @php
                                                $freeSlots = max(0, $organization->max_employees - $currentEmployeesCount);
                                            @endphp
                                            <span class="badge bg-{{ $freeSlots > 0 ? 'success' : 'danger' }}">
                                                {{ $freeSlots }} из {{ $organization->max_employees }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">∞</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Менеджер -->
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
                    
                    @if($organization->max_employees && $currentEmployeesCount >= $organization->max_employees)
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Достигнут лимит сотрудников ({{ $currentEmployeesCount }}/{{ $organization->max_employees }}).
                            Для добавления новых сотрудников необходимо увеличить лимит.
                        </div>
                    @endif
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
                           class="btn btn-primary btn-sm">
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
                                @if($organization->owner->user->phone)
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-telephone text-muted me-2"></i>
                                    <span>{{ $organization->owner->user->phone }}</span>
                                </div>
                                @endif
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

            <!-- Подписки владельца -->
            @if($organization->owner && $organization->owner->user)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-info me-2"></i>
                        Подписки владельца
                        @if(isset($subscriptions) && $subscriptions->count() > 0)
                            <span class="badge bg-info ms-2">{{ $subscriptions->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($subscriptions) && $subscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Дата начала</th>
                                        <th>Дата окончания</th>
                                        <th>Статус</th>
                                        <th>Осталось дней</th>
                                        <th>Отчетов</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                    <tr>
                                        <td>#{{ $subscription->id }}</td>
                                        <td>
                                            <span class="fw-semibold small">
                                                <i class="bi bi-tag text-info me-1"></i>
                                                {{ Str::limit($subscriptionName, 25) }}
                                            </span>
                                        </td>
                                        <td>{{ $subscription->starts_at ? $subscription->starts_at->format('d.m.Y') : '—' }}</td>
                                        <td>
                                            @if($subscription->ends_at)
                                                {{ $subscription->ends_at->format('d.m.Y') }}
                                                @if($subscription->isExpiringSoon() && $subscription->isActive())
                                                    <span class="badge bg-warning ms-1">скоро</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Бессрочно</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusClass }}">
                                                {{ $subscription->getStatusTextAttribute() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($remainingDays !== null)
                                                @if($subscription->isActive())
                                                    <span class="badge bg-{{ $remainingDays <= 7 ? 'warning' : 'success' }}">
                                                        {{ $remainingDays }} дн.
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            @else
                                                <span class="text-muted">∞</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $limitsCount }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-stars fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-3">У владельца пока нет подписок</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Отчеты по подпискам -->
            @if(isset($groupedLimits) && count($groupedLimits) > 0)
                @foreach($groupedLimits as $subscriptionId => $group)
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
                        
                        // Функция для склонения слов
                        $limitsCount = count($limits);
                        $wordForms = ['отчет', 'отчета', 'отчетов'];
                        $wordIndex = ($limitsCount % 100 > 4 && $limitsCount % 100 < 20) ? 2 : [2, 0, 1, 1, 1, 2][min($limitsCount % 10, 5)];
                        $wordForm = $wordForms[$wordIndex];
                        $subscriptionName = $group['subscription']->name ?? 'Подписка #' . $group['subscription']->id;
                    @endphp
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-stars text-info me-2"></i>
                                        <span class="me-2">{{ $subscriptionName }}</span>
                                        @if($subscription->status === 'active')
                                            <span class="badge bg-success ms-2">Активна</span>
                                        @elseif($subscription->status === 'expired')
                                            <span class="badge bg-danger ms-2">Истекла</span>
                                        @elseif($subscription->status === 'pending')
                                            <span class="badge bg-warning ms-2">Ожидает</span>
                                        @elseif($subscription->status === 'suspended')
                                            <span class="badge bg-warning ms-2">Приостановлена</span>
                                        @elseif($subscription->status === 'cancelled')
                                            <span class="badge bg-secondary ms-2">Отменена</span>
                                        @else
                                            <span class="badge bg-secondary ms-2">{{ $subscription->getStatusTextAttribute() }}</span>
                                        @endif
                                    </h5>
                                    <div class="text-muted small mt-1">
                                        @if($subscription->starts_at)
                                            <span class="me-3"><i class="bi bi-calendar-plus me-1"></i>С {{ $subscription->starts_at->format('d.m.Y') }}</span>
                                        @endif
                                        @if($subscription->ends_at)
                                            <span><i class="bi bi-calendar-x me-1"></i>до {{ $subscription->ends_at->format('d.m.Y') }}</span>
                                            @if($subscription->getRemainingDays())
                                                <span class="badge bg-{{ $subscription->getRemainingDays() <= 7 ? 'warning' : 'info' }} ms-2">
                                                    осталось {{ $subscription->getRemainingDays() }} дн.
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">(бессрочная)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-secondary">
                                        {{ $limitsCount }} {{ $wordForm }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Статистика по подписке -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Всего отчетов</small>
                                        <span class="fw-bold fs-5">{{ $totalQuantity }} шт.</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Использовано</small>
                                        <span class="fw-bold fs-5 text-warning">{{ $totalUsed }} шт.</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Доступно</small>
                                        <span class="fw-bold fs-5 text-{{ $totalAvailable > 0 ? 'success' : 'danger' }}">
                                            {{ $totalAvailable }} шт.
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <small class="text-muted d-block">Делегировано</small>
                                        <span class="fw-bold fs-5 text-warning">{{ $totalDelegatedForSub }} шт.</span>
                                    </div>
                                </div>
                            </div>
                            
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
                                        @foreach($limits as $limit)
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
                                            </td>
                                            <td>{{ date('d.m.Y', strtotime($limit['date_created'])) }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $limit['quantity'] }} шт.</span>
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
                @endforeach
            @elseif(isset($subscriptions) && $subscriptions->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-stars text-info me-2"></i>
                            Отчеты по подпискам
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-file-text display-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">В подписках пока нет отчетов</h4>
                            <p class="text-muted mb-4">Добавьте отчеты в подписки</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-stars text-info me-2"></i>
                            Отчеты владельца
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-speedometer display-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">У владельца нет отчетов</h4>
                            <p class="text-muted mb-4">Для создания отчетов необходимо создать подписку и добавить отчеты</p>
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
                               class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i> Редактировать организацию
                            </a>
                        @endif

                        <form action="{{ route('admin.organization.toggle-status', $organization->id) }}" 
                              method="POST" class="d-grid">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-{{ $organization->status == 'active' ? 'warning' : 'success' }}">
                                <i class="bi bi-toggle-{{ $organization->status == 'active' ? 'off' : 'on' }} me-1"></i>
                                {{ $organization->status == 'active' ? 'Деактивировать' : 'Активировать' }}
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete({{ $organization->id }}, '{{ $organization->name }}')">
                            <i class="bi bi-trash me-1"></i> Удалить организацию
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Ответственный менеджер -->
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

            <!-- Информация о лимитах сотрудников -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-people text-info me-2"></i>
                        Лимит сотрудников
                    </h5>
                </div>
                <div class="card-body">
                    @if($organization->max_employees)
                        <div class="text-center mb-3">
                            <div class="display-4 text-info mb-2">{{ $currentEmployeesCount }} / {{ $organization->max_employees }}</div>
                            <div class="text-muted">текущее / максимум</div>
                        </div>
                        
                        <!-- Прогресс-бар -->
                        @php
                            $employeePercentage = $organization->max_employees > 0 
                                ? min(100, round(($currentEmployeesCount / $organization->max_employees) * 100)) 
                                : 0;
                        @endphp
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-{{ $employeePercentage >= 90 ? 'danger' : ($employeePercentage >= 70 ? 'warning' : 'success') }}" 
                                 style="width: {{ $employeePercentage }}%">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-center">
                            <div>
                                <span class="badge bg-success">{{ $organization->members->where('is_active', true)->count() }}</span>
                                <small class="d-block text-muted">активных</small>
                            </div>
                            <div>
                                <span class="badge bg-danger">{{ $organization->members->where('is_active', false)->count() }}</span>
                                <small class="d-block text-muted">неактивных</small>
                            </div>
                            <div>
                                <span class="badge bg-info">{{ $availableEmployeeSlots ?? '∞' }}</span>
                                <small class="d-block text-muted">свободно</small>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
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
                        @if(!$organization->max_employees || $currentEmployeesCount < $organization->max_employees)
                            <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" 
                               class="btn btn-primary">
                                <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                            </a>
                        @else
                            <button class="btn btn-secondary" disabled 
                                    title="Достигнут лимит сотрудников ({{ $currentEmployeesCount }}/{{ $organization->max_employees }})">
                                <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                            </button>
                        @endif
                        
                        @if($organization->members->count() > 0)
                            <a href="{{ route('reports.create') }}" class="btn btn-success">
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
                            <th>Подписка</th>
                            <th>Делегировано</th>
                            <th>Использовано</th>
                            <th>Доступно</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            @if($canDelegateAny)
                            <th>Действия</th>
                            @endif
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
                            <td>
                                @if($subscription)
                                    <span class="badge bg-info">#{{ $subscription->id }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $delegated->quantity }} шт.</td>
                            <td>{{ $delegated->used_quantity }} шт.</td>
                            <td>
                                <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
                                    {{ $available }} шт.
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
                            @if($canDelegateAny)
                            <td>
                                <form action="{{ route('delegated-limits.destroy', $delegated) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to_organization" value="{{ $organization->id }}">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Возвратить лимит?')"
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
                    <span class="badge bg-primary ms-2">{{ $currentEmployeesCount }}</span>
                    @if($organization->max_employees)
                        <span class="badge bg-info ms-2">лимит: {{ $organization->max_employees }}</span>
                    @endif
                </h5>
                <small class="text-muted">Все сотрудники организации</small>
            </div>
            @if($isAdmin || $isManager)
                @if(!$organization->max_employees || $currentEmployeesCount < $organization->max_employees)
                    <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                    </a>
                @else
                    <button class="btn btn-secondary" disabled 
                            title="Достигнут лимит сотрудников ({{ $currentEmployeesCount }}/{{ $organization->max_employees }})">
                        <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                    </button>
                @endif
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
                                        @if($member->user->phone)
                                                <p class="mb-2">
                                                    <i class="bi bi-telephone text-primary"></i> 
                                                    <a href="tel:{{ $member->user->phone }}" class="text-decoration-none">
                                                        {{ $member->user->phone }}
                                                    </a>
                                                </p>
                                            @else
                                                <p class="text-muted small mb-2">
                                                    <i class="bi bi-telephone-x"></i> Телефон не указан
                                                </p>
                                            @endif
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
                                
                                @if($hasDelegated)
                                    <div class="mb-3 border-top pt-2">
                                        <small class="text-muted d-block mb-2">Статистика по отчетам:</small>
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
                                    </div>
                                @else
                                    <div class="border-top pt-3 text-center">
                                        <i class="bi bi-share fs-4 text-muted mb-2 d-block"></i>
                                        <small class="text-muted">Нет делегированных отчетов</small>
                                    </div>
                                @endif
                                
                                <!-- Кнопки действий -->
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex gap-1">
                                            <a href="{{ route($routePrefix . 'org-members.show', [$organization->id, $member->id]) }}" 
                                               class="btn btn-sm btn-info" title="Просмотр">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($isAdmin || $isManager)
                                                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                                                   class="btn btn-sm btn-secondary" title="Редактировать">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        </div>
                                        
                                        <!-- Кнопка делегирования -->
                                        @if($canDelegateAny && isset($groupedLimits) && count($groupedLimits) > 0)
                                            <button type="button" class="btn btn-sm btn-warning delegate-btn"
                                                    data-employee-id="{{ $member->user->id }}"
                                                    data-employee-name="{{ $member->user->name }}"
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
                    <p class="text-muted mb-4">
                        Добавьте первого сотрудника в организацию
                        @if($organization->max_employees)
                            <br><small class="text-info">Лимит сотрудников: {{ $organization->max_employees }} чел.</small>
                        @endif
                    </p>
                    @if($isAdmin || $isManager)
                        @if(!$organization->max_employees || $currentEmployeesCount < $organization->max_employees)
                            <a href="{{ route($routePrefix . 'org-members.create', $organization->id) }}" class="btn btn-primary">
                                <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                            </a>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="bi bi-person-plus me-1"></i> Лимит сотрудников исчерпан
                            </button>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@if($isAdmin)
<!-- Форма для удаления организации -->
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

<!-- Модальное окно делегирования -->
@if($canDelegateAny && isset($groupedLimits) && count($groupedLimits) > 0 && isset($availableEmployees) && $availableEmployees->count() > 0)
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
                        <i class="bi bi-share"></i> Делегирование отчета
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="limit_id" class="form-label">
                                    <i class="bi bi-tachometer"></i> Отчет владельца *
                                </label>
                                <select name="limit_id" id="limit_id" class="form-select" required>
                                    <option value="">Выберите отчет</option>
                                    @foreach($groupedLimits as $group)
                                        @foreach($group['limits'] as $limit)
                                            @if($limit['available_amount'] > 0)
                                                <option value="{{ $limit['id'] }}" 
                                                        data-available="{{ $limit['available_amount'] }}"
                                                        data-name="{{ $limit['report_type_name'] }}"
                                                        data-subscription-id="{{ $group['subscription']->id }}"
                                                        data-date="{{ date('d.m.Y', strtotime($limit['date_created'])) }}">
                                                    {{ $limit['report_type_name'] }} 
                                                    (Подписка #{{ $group['subscription']->id }})
                                                    - {{ date('d.m.Y', strtotime($limit['date_created'])) }}
                                                    - доступно {{ $limit['available_amount'] }} шт.
                                                </option>
                                            @endif
                                        @endforeach
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
                            <button type="button" class="btn btn-sm btn-primary me-2" onclick="setDelegateAmount(5)">
                                +5
                            </button>
                            <button type="button" class="btn btn-sm btn-primary me-2" onclick="setDelegateAmount(10)">
                                +10
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setMaxDelegateAmount()">
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
</script>
@endpush
@endsection