@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-circle text-primary"></i> Мой профиль
        </h1>
        <a href="{{ route('member.profile.edit') }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; color: white; font-size: 28px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-info">Сотрудник</span>
                        @if($user->is_active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-danger">Неактивен</span>
                        @endif
                    </div>
                    
                    <p class="text-muted small">
                        <i class="bi bi-calendar"></i> Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <!-- ===== НОВЫЙ БЛОК: МОИ ПОДПИСКИ ===== -->
            @if(isset($subscriptions))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-info me-2"></i>
                        Мои подписки
                        @if($subscriptions->count() > 0)
                            <span class="badge bg-info float-end">{{ $subscriptions->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($subscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Дата начала</th>
                                        <th>Дата окончания</th>
                                        <th>Статус</th>
                                        <th>Осталось дней</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscriptions as $subscription)
                                    @php
                                        $remainingDays = $subscription->getRemainingDays();
                                        $statusClass = $subscription->status === 'active' ? 'success' : 
                                                      ($subscription->status === 'expired' ? 'danger' : 
                                                      ($subscription->status === 'pending' ? 'warning' : 'secondary'));
                                    @endphp
                                    <tr>
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
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-stars text-muted fs-1 mb-2 d-block"></i>
                            <p class="text-muted mb-0">У вас нет подписок</p>
                            <small class="text-muted">Подписки выдаются через администратора</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- ИСПРАВЛЕНО: Статистика отчетов -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart text-primary me-2"></i>
                        Статистика отчетов
                    </h5>
                </div>
                <div class="card-body">
                    @if($personalLimits->count() > 0 || $delegatedLimits->count() > 0)
                        <!-- Собственные отчеты -->
                        @if($personalLimits->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-person-check me-2"></i>
                                Собственные отчеты
                                <span class="badge bg-success float-end">{{ $personalLimits->count() }}</span>
                            </h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Всего выделено:</small>
                                <span class="badge bg-primary">{{ $totalPersonal }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Использовано:</small>
                                <span class="badge bg-info">{{ $totalPersonalUsed }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small>Доступно:</small>
                                <span class="badge bg-{{ $totalPersonalAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $totalPersonalAvailable }} шт.
                                </span>
                            </div>
                            
                            @if($totalPersonal > 0)
                            <div class="progress mb-1" style="height: 6px;">
                                @php
                                    $personalPercentage = round(($totalPersonalUsed / $totalPersonal) * 100);
                                @endphp
                                <div class="progress-bar bg-{{ $personalPercentage > 80 ? 'danger' : ($personalPercentage > 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $personalPercentage }}%">
                                </div>
                            </div>
                            <small class="text-muted d-block text-center">
                                использовано {{ $personalPercentage }}%
                            </small>
                            @endif
                        </div>
                        @endif
                        
                        <!-- Делегированные отчеты -->
                        @if($delegatedLimits->count() > 0)
                        <div class="{{ $personalLimits->count() > 0 ? 'border-top pt-3' : '' }}">
                            <h6 class="text-warning mb-3">
                                <i class="bi bi-share me-2"></i>
                                Делегированные отчеты
                                <span class="badge bg-warning float-end">{{ $delegatedLimits->count() }}</span>
                            </h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Всего делегировано:</small>
                                <span class="badge bg-warning">{{ $totalDelegated }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Использовано:</small>
                                <span class="badge bg-info">{{ $totalDelegatedUsed }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small>Доступно:</small>
                                <span class="badge bg-{{ $totalDelegatedAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $totalDelegatedAvailable }} шт.
                                </span>
                            </div>
                            
                            @if($totalDelegated > 0)
                            <div class="progress mb-1" style="height: 6px;">
                                @php
                                    $delegatedPercentage = round(($totalDelegatedUsed / $totalDelegated) * 100);
                                @endphp
                                <div class="progress-bar bg-{{ $delegatedPercentage > 80 ? 'danger' : ($delegatedPercentage > 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $delegatedPercentage }}%">
                                </div>
                            </div>
                            <small class="text-muted d-block text-center">
                                использовано {{ $delegatedPercentage }}%
                            </small>
                            @endif
                        </div>
                        @endif
                        
                        <!-- Общая статистика (если есть и те и другие) -->
                        @if($personalLimits->count() > 0 && $delegatedLimits->count() > 0)
                        <div class="border-top pt-3 mt-3">
                            <h6 class="mb-3">
                                <i class="bi bi-bar-chart me-2"></i>
                                Общая статистика
                            </h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Всего отчетов:</small>
                                <span class="badge bg-primary">{{ $totalAll }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Всего использовано:</small>
                                <span class="badge bg-info">{{ $totalAllUsed }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small>Всего доступно:</small>
                                <span class="badge bg-{{ $totalAllAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $totalAllAvailable }} шт.
                                </span>
                            </div>
                        </div>
                        @endif
                        
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x text-muted fs-1 mb-3 d-block"></i>
                            <p class="text-muted mb-0">У вас нет отчетов</p>
                            <small class="text-muted">Обратитесь к руководителю для получения отчетов</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Типы отчетов -->
            @if(!empty($personalLimitsByType) || !empty($delegatedLimitsByType))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-grid text-info me-2"></i>
                            По типам отчетов
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            // Объединяем типы
                            $allTypes = array_unique(
                                array_merge(
                                    array_keys($personalLimitsByType),
                                    array_keys($delegatedLimitsByType)
                                )
                            );
                        @endphp
                        
                        @if(!empty($allTypes))
                            @foreach($allTypes as $typeName)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">{{ $typeName }}</small>
                                    
                                    <!-- Собственные лимиты по этому типу -->
                                    @if(isset($personalLimitsByType[$typeName]))
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small><i class="bi bi-person text-success"></i> Собственные:</small>
                                            <span class="badge bg-light text-dark border">
                                                {{ $personalLimitsByType[$typeName]['available'] }}/{{ $personalLimitsByType[$typeName]['total'] }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <!-- Делегированные лимиты по этому типу -->
                                    @if(isset($delegatedLimitsByType[$typeName]))
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small><i class="bi bi-share text-warning"></i> Делегированные:</small>
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
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Правая колонка -->
        <div class="col-lg-8">
            <!-- Рабочая информация -->
            @if($memberProfile && $organization)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Рабочая информация</h5>
                    <span class="badge bg-primary">{{ $organization->name }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%;">Организация:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; color: white; font-size: 0.8rem;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $organization->name }}</strong>
                                                <div class="mt-1">
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
                                    </td>
                                </tr>
                                <tr>
                                    <th>Дата добавления:</th>
                                    <td>{{ $memberProfile->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%;">Начальник:</th>
                                    <td>
                                        @if($memberProfile->boss && $memberProfile->boss->user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($memberProfile->boss->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div>{{ $memberProfile->boss->user->name }}</div>
                                                    <small class="text-muted">{{ $memberProfile->boss->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Статус:</th>
                                    <td>
                                        @if($memberProfile->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Информация о подписке -->
                    @if($organization->subscription_ends_at)
                        <div class="alert alert-light mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-clock-history me-2"></i>
                                    Подписка организации до: 
                                    <strong>{{ $organization->subscription_ends_at->format('d.m.Y') }}</strong>
                                </div>
                                @if($organization->isSubscriptionExpiringSoon())
                                    <span class="badge bg-warning">
                                        Истекает через {{ $organization->getRemainingSubscriptionDays() }} дн.
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Табы для лимитов -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="limitsTab" role="tablist">
                        @if($personalLimits->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" 
                                        data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person-check me-1"></i>
                                    Собственные отчеты
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
                                    Делегированные отчеты
                                    <span class="badge bg-warning ms-1">{{ $delegatedLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                        @if($personalLimits->count() == 0 && $delegatedLimits->count() == 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" disabled>
                                    <i class="bi bi-clipboard-x me-1"></i>
                                    Нет отчетов
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
                                                <th>Всего</th>
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
                                                    {{ $limit->date_created->format('d.m.Y') }}
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
                                                // ИСПРАВЛЕНО: получаем владельца через subscription->user
                                                $originalUser = $delegated->limit->subscription->user ?? null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $delegated->limit->reportType->name ?? 'Без типа' }}</strong>
                                                </td>
                                                <td>
                                                    @if($originalUser)
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-1" 
                                                                 style="width: 24px; height: 24px; color: white; font-size: 0.7rem;">
                                                                {{ strtoupper(substr($originalUser->name, 0, 1)) }}
                                                            </div>
                                                            <small>{{ $originalUser->name }}</small>
                                                        </div>
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

                        <!-- Нет лимитов -->
                        @if($personalLimits->count() == 0 && $delegatedLimits->count() == 0)
                            <div class="text-center py-5">
                                <i class="bi bi-clipboard-x text-muted fs-1 mb-3 d-block"></i>
                                <p class="text-muted mb-2">У вас нет доступных отчетов</p>
                                <small class="text-muted">Обратитесь к руководителю для получения отчетов</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра подписки (опционально) -->
<div class="modal fade" id="viewSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
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

@push('scripts')
<script>
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
                    user_name: '{{ $user->name }}',
                    user_email: '{{ $user->email }}'
                },
            @endforeach
        @endif
    };

    // Функция для просмотра подписки
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

    // Активация табов
    document.addEventListener('DOMContentLoaded', function() {
        const triggerTabList = [].slice.call(document.querySelectorAll('#limitsTab button'))
        triggerTabList.forEach(function (triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl)
            
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    })
</script>
@endpush

<style>
/* Исправление для активных табов */
.nav-tabs .nav-link.active {
    color: #0d6efd !important;
    font-weight: 500 !important;
    background-color: #e7f1ff !important;
    border-color: #dee2e6 #dee2e6 #fff !important;
}
</style>
@endsection