@extends('layouts.app')

@section('title', 'Панель владельца организации - ' . $organization->name)
@section('page-icon', 'bi-buildings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-buildings text-success"></i> {{ $organization->name }}
    </h2>
    <div>
        <span class="badge bg-success fs-6">Владелец организации</span>
    </div>
</div>

<!-- Информация о владельце и организации -->
<div class="row mb-4">
    <!-- Профиль владельца -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-person-badge text-primary me-2"></i>
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
                    @if($organization->our_organization)
                    <div class="mb-3">
                        <strong>Наша организация:</strong>
                        <span class="ms-2">{{ $organization->our_organization }}</span>
                    </div>
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
                                                ? min(100, round(($membersCount / $organization->max_employees) * 100)) 
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
                                        $availableSlots = max(0, $organization->max_employees - $membersCount);
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
                
                @if($organization->max_employees && $membersCount >= $organization->max_employees)
                    <div class="alert alert-warning mt-2 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Достигнут лимит сотрудников ({{ $membersCount }}/{{ $organization->max_employees }}). 
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
@if(isset($subscriptions))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-stars text-info me-2"></i>
            Ваши подписки
            @if($subscriptions->count() > 0)
                <span class="badge bg-info ms-2">{{ $subscriptions->count() }}</span>
            @endif
        </h6>
    </div>
    <div class="card-body">
        @if($subscriptions->count() > 0)
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
                <p class="text-muted mb-3">У вас пока нет подписок</p>
                @if($manager)
                    <p class="text-muted small">Обратитесь к менеджеру для получения подписки</p>
                @endif
            </div>
        @endif
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
            $subscriptionDelegated = $delegatedLimits->filter(function($delegated) use ($subscription) {
                return $delegated->limit->subscription_id == $subscription->id;
            });
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
                                    @if($delegated->user->phone)
                                        <p class="mb-2">
                                            <i class="bi bi-telephone text-primary"></i> 
                                            <a href="tel:{{ $delegated->user->phone }}" class="text-decoration-none">
                                                {{ $delegated->user->phone }}
                                            </a>
                                        </p>
                                    @else
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-telephone-x"></i> Телефон не указан
                                        </p>
                                    @endif
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
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Возвратить лимит?')"
                                        title="Возвратить лимит">
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
        <h6 class="mb-0">
            <i class="bi bi-people text-primary me-2"></i>
            Сотрудники организации
            @if($membersCount > 0)
                <span class="badge bg-primary ms-2">{{ $membersCount }}</span>
            @endif
        </h6>
        <div class="d-flex gap-2 align-items-center">
            @if($organization->max_employees)
                <span class="badge bg-info">
                    Лимит: {{ $organization->max_employees }} чел.
                </span>
                @if($organization->max_employees > $membersCount)
                    <span class="badge bg-success">
                        Свободно: {{ $organization->max_employees - $membersCount }}
                    </span>
                @else
                    <span class="badge bg-danger">
                        Лимит исчерпан
                    </span>
                @endif
            @endif
            
            @if($organization && $organization->owner && $organization->owner->user_id == Auth::id())
                @if(!$organization->max_employees || $organization->max_employees > $membersCount)
                    <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </a>
                @else
                    <button class="btn btn-secondary btn-sm" disabled 
                            title="Достигнут лимит сотрудников">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </button>
                @endif
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($membersCount > 0)
            <div class="row">
                @foreach($members as $member)
                @php
                    $memberDelegated = $delegatedLimits->where('user_id', $member->user->id);
                    $hasDelegated = $memberDelegated->count() > 0;
                @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border h-100">
                        <div class="card-body p-3">
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
                </p>
                @if($organization && $organization->owner && $organization->owner->user_id == Auth::id())
                    <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

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

@push('scripts')
<script>
    $(document).ready(function() {
        // Данные подписок для модального окна просмотра
        let subscriptions = {
            @if(isset($subscriptions))
                @foreach($subscriptions as $sub)
                    {{ $sub->id }}: {
                        starts_at: '{{ $sub->starts_at ? $sub->starts_at->format('Y-m-d') : '' }}',
                        ends_at: '{{ $sub->ends_at ? $sub->ends_at->format('Y-m-d') : '' }}',
                        status: '{{ $sub->status }}',
                        status_text: '{{ $sub->getStatusTextAttribute() }}',
                        status_class: '{{ $sub->status === "active" ? "success" : ($sub->status === "expired" ? "danger" : ($sub->status === "pending" ? "warning" : "secondary")) }}',
                        remaining_days: {{ $sub->getRemainingDays() ?? 'null' }},
                        user_name: '{{ Auth::user()->name }}',
                        user_email: '{{ Auth::user()->email }}'
                    },
                @endforeach
            @endif
        };
        
        // Функция для открытия модалки просмотра подписки
        window.viewSubscription = function(id) {
            const sub = subscriptions[id];
            if (!sub) return;
            
            let html = `
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 70px; height: 70px; color: white; font-size: 1.8rem;">
                        ${sub.user_name ? sub.user_name.charAt(0).toUpperCase() : '?'}
                    </div>
                    <h5>${sub.user_name || 'Неизвестно'}</h5>
                    <p class="text-muted small">${sub.user_email || ''}</p>
                </div>
                <table class="table table-sm">
                    <tr>
                        <th>Дата начала:</th>
                        <td>${sub.starts_at ? sub.starts_at : '—'}</td>
                    </tr>
                    <tr>
                        <th>Дата окончания:</th>
                        <td>${sub.ends_at ? sub.ends_at : 'Бессрочно'}</td>
                    </tr>
                    <tr>
                        <th>Статус:</th>
                        <td><span class="badge bg-${sub.status_class}">${sub.status_text}</span></td>
                    </tr>
                    <tr>
                        <th>Осталось дней:</th>
                        <td>${sub.remaining_days !== null ? sub.remaining_days + ' дн.' : '∞'}</td>
                    </tr>
                </table>
            `;
            
            document.getElementById('viewSubscriptionContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewSubscriptionModal')).show();
        };
    });
</script>
@endpush