@extends('layouts.app')

@section('title', 'Панель владельца организации - ' . $organization->name)
@section('page-icon', 'bi-buildings')

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
        
        // Проверяем права на делегирование (владелец всегда может делегировать)
        $canDelegateAny = true;
        $currentUserId = Auth::id();
        $ownerId = $organization->owner->user_id ?? null;
        
        // Статистика по сотрудникам для новых полей
        $currentEmployeesCount = $organization->members->count();
        $availableEmployeeSlots = $organization->max_employees ? max(0, $organization->max_employees - $currentEmployeesCount) : null;
    @endphp

    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3" 
                 style="width: 50px; height: 50px; color: white;">
                <i class="bi bi-building fs-4"></i>
            </div>
            <div>
                <h1 class="h3 mb-0">{{ $organization->name }}</h1>
                @if($organization->our_organization)
                    <div class="d-flex align-items-center mt-1">
                        <span class="badge bg-info me-2">Наша организация:</span>
                        <span class="fw-semibold">{{ $organization->our_organization }}</span>
                    </div>
                @endif
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Панель владельца</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($organization->name, 20) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div>
            <span class="badge bg-success fs-6">Владелец организации</span>
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

    <!-- Информация о владельце и организации -->
    <div class="row mb-4">
        <!-- Профиль владельца -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-person-badge text-success me-2"></i>
                        Ваш профиль
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 70px; height: 70px; font-size: 1.8rem; color: white;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <h5>{{ Auth::user()->name }}</h5>
                    <p class="text-muted">{{ Auth::user()->email }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-success">Владелец</span>
                        @if(Auth::user()->is_active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-danger">Неактивен</span>
                        @endif
                    </div>
                    
                    @if($organization->max_employees)
                        <div class="small text-muted mt-2">
                            <i class="bi bi-people me-1"></i>
                            Лимит сотрудников: {{ $organization->max_employees }} чел.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Информация об организации -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <i class="bi bi-building text-success me-2"></i>
                                Организация: <strong class="ms-1">{{ $organization->name }}</strong>
                            </h6>
                            @if($organization->our_organization)
                                <div class="mt-1 small">
                                    <span class="badge bg-info me-1">Наша организация:</span>
                                    <span class="fw-semibold">{{ $organization->our_organization }}</span>
                                </div>
                            @endif
                        </div>
                        @if($organization->inn)
                            <span class="badge bg-info">ИНН: {{ $organization->inn }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Статус:</strong>
                                @switch($organization->status)
                                    @case('active')
                                        <span class="badge bg-success ms-2">Активна</span>
                                        @break
                                    @case('inactive')
                                        <span class="badge bg-danger ms-2">Неактивна</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning ms-2">Ожидает</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge bg-warning ms-2">Приостановлена</span>
                                        @break
                                    @case('expired')
                                        <span class="badge bg-danger ms-2">Истекла</span>
                                        @break
                                @endswitch
                            </div>
                            
                            <div class="mb-3">
                                <strong>Сотрудников:</strong>
                                <span class="ms-2">{{ $membersCount }} / {{ $activeMembersCount }} активных</span>
                                @if($organization->max_employees)
                                    <span class="badge bg-info ms-2" title="Максимальное количество сотрудников">
                                        лимит: {{ $organization->max_employees }}
                                    </span>
                                    <div class="mt-2" style="max-width: 200px;">
                                        <div class="progress" style="height: 6px;">
                                            @php
                                                $employeePercentage = $organization->max_employees > 0 
                                                    ? min(100, round(($currentEmployeesCount / $organization->max_employees) * 100)) 
                                                    : 0;
                                            @endphp
                                            <div class="progress-bar bg-{{ $employeePercentage >= 90 ? 'danger' : ($employeePercentage >= 70 ? 'warning' : 'success') }}" 
                                                 style="width: {{ $employeePercentage }}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $employeePercentage }}% от лимита</small>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <strong>Создана:</strong>
                                <span class="ms-2">{{ $organization->created_at->format('d.m.Y') }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>ИНН:</strong>
                                <span class="ms-2">{{ $organization->inn ?? 'Не указан' }}</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Макс. сотрудников:</strong>
                                <span class="ms-2">
                                    @if($organization->max_employees)
                                        {{ $organization->max_employees }} чел.
                                    @else
                                        <span class="text-muted">Не ограничено</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Свободных мест:</strong>
                                <span class="ms-2">
                                    @if($organization->max_employees)
                                        @php
                                            $availableSlots = max(0, $organization->max_employees - $currentEmployeesCount);
                                        @endphp
                                        <span class="badge bg-{{ $availableSlots > 0 ? 'success' : 'danger' }}">
                                            {{ $availableSlots }} из {{ $organization->max_employees }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">∞</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div> 
                    
                    @if($organization->max_employees && $currentEmployeesCount >= $organization->max_employees)
                        <div class="alert alert-warning mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Достигнут лимит сотрудников ({{ $currentEmployeesCount }}/{{ $organization->max_employees }}). 
                            Для добавления новых сотрудников обратитесь к менеджеру.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Информация о менеджере -->
    @if($manager)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2">
                    <h6 class="mb-0">
                        <i class="bi bi-person-workspace text-primary me-1"></i>
                        Ваш менеджер: <strong>{{ $manager->name }}</strong>
                    </h6>
                </div>
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-envelope text-secondary"></i>
                                    <a href="mailto:{{ $manager->email }}" class="text-decoration-none ms-1">
                                        {{ $manager->email }}
                                    </a>
                                </div>
                                
                                @php
                                    $managerPhone = $manager->managerProfile->phone ?? $manager->phone ?? null;
                                @endphp
                                @if($managerPhone)
                                <div>
                                    <i class="bi bi-telephone text-secondary ms-3"></i>
                                    <a href="tel:{{ $managerPhone }}" class="text-decoration-none ms-1">
                                        {{ $managerPhone }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @php
                            $managerTelegram = $manager->managerProfile->telegram ?? $manager->telegram ?? null;
                        @endphp
                        @if($managerTelegram)
                        <div class="col-md-4 text-md-end mt-2 mt-md-0">
                            <a href="https://t.me/{{ $managerTelegram }}" 
                               class="badge bg-info text-decoration-none px-3 py-2" 
                               target="_blank">
                                <i class="bi bi-telegram"></i> Telegram
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info py-2 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Менеджер не назначен. 
                <a href="mailto:support@example.com" class="alert-link">Написать в поддержку</a>
            </div>
        </div>
    </div>
    @endif

    <!-- Блок с подписками -->
    @if(isset($subscriptions) && $subscriptions->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-stars text-info me-2"></i>
                Ваши подписки
                <span class="badge bg-info ms-2">{{ $subscriptions->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
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
        </div>
    </div>
    @endif

    <!-- Отчеты по подпискам -->
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
                            <h6 class="mb-0">
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
                            </h6>
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
                                    <th>Действия</th>
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
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-stars text-info me-2"></i>
                    Отчеты по подпискам
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-file-text display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">В подписках пока нет отчетов</h4>
                    <p class="text-muted mb-4">Обратитесь к менеджеру для получения отчетов</p>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-stars text-info me-2"></i>
                    Отчеты
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-speedometer display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">У вас нет подписок</h4>
                    <p class="text-muted mb-4">Для получения отчетов необходимо создать подписку</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Делегированные лимиты -->
    @if(isset($delegatedLimits) && $delegatedLimits->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-share text-warning me-2"></i>
                Делегированные отчеты
                <span class="badge bg-warning ms-2">{{ $delegatedLimits->count() }}</span>
            </h6>
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
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">
                    <i class="bi bi-people text-primary me-2"></i>
                    Сотрудники организации
                    <span class="badge bg-primary ms-2">{{ $currentEmployeesCount }}</span>
                    @if($organization->max_employees)
                        <span class="badge bg-info ms-2">лимит: {{ $organization->max_employees }}</span>
                    @endif
                </h6>
                <small class="text-muted">Все сотрудники организации</small>
            </div>
            @if($canAddMoreEmployees)
                <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                </a>
            @elseif($organization->max_employees)
                <button class="btn btn-secondary" disabled 
                        title="Достигнут лимит сотрудников ({{ $currentEmployeesCount }}/{{ $organization->max_employees }})">
                    <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                </button>
            @endif
        </div>
        <div class="card-body">
            @if($members->count() > 0)
                <div class="row">
                    @foreach($members as $member)
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
                                            <a href="{{ route('owner.org-members.show', [$organization->id, $member->id]) }}" 
                                               class="btn btn-sm btn-info" title="Просмотр">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('owner.org-members.edit', [$organization->id, $member->id]) }}" 
                                               class="btn btn-sm btn-secondary" title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($members->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $members->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-people fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Сотрудников пока нет</h5>
                    <p class="text-muted mb-4">
                        Добавьте первого сотрудника в вашу организацию
                        @if($organization->max_employees)
                            <br><small class="text-info">Лимит сотрудников: {{ $organization->max_employees }} чел.</small>
                        @endif
                    </p>
                    @if($canAddMoreEmployees)
                        <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Добавить сотрудника
                        </a>
                    @elseif($organization->max_employees)
                        <button class="btn btn-secondary" disabled>
                            <i class="bi bi-person-plus me-1"></i> Лимит сотрудников исчерпан
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно делегирования -->
@if(isset($availableEmployees) && $availableEmployees->count() > 0)
<div class="modal fade" id="delegateModal" tabindex="-1" aria-labelledby="delegateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('delegated-limits.store') }}" method="POST" id="delegateForm">
                @csrf
                <input type="hidden" name="redirect_to_organization" value="{{ $organization->id }}">
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
                                    <i class="bi bi-tachometer"></i> Отчет *
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

<!-- Модальное окно просмотра подписки -->
<div class="modal fade" id="viewSubscriptionModal" tabindex="-1" aria-labelledby="viewSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewSubscriptionModalLabel">
                    <i class="bi bi-eye me-2"></i>
                    Детали подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSubscriptionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
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
    .table td {
        vertical-align: middle;
    }
    .bg-opacity-25 {
        --bs-bg-opacity: 0.25;
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
    
    // Кнопки делегирования в таблице
    $('.delegate-btn').on('click', function() {
        const limitId = $(this).data('limit-id');
        const limitName = $(this).data('limit-name');
        const limitAvailable = $(this).data('limit-available');
        const limitDate = $(this).data('limit-date');
        
        // Устанавливаем значение в селект
        $('#limit_id').val(limitId).trigger('change');
        
        // Обновляем информацию о лимите
        $('#limitInfo').show();
        $('#limitName').text(limitName);
        $('#limitDate').text(limitDate);
        $('#limitAvailable').text(limitAvailable + ' шт.');
        $('#maxAmount').text(limitAvailable);
        $('#quantity').attr('max', limitAvailable);
        
        // Открываем модальное окно
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
    
    // Инициализация при открытии модального окна
    $('#delegateModal').on('shown.bs.modal', function() {
        if (!$('#limit_id').val()) {
            $('#limitInfo').hide();
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