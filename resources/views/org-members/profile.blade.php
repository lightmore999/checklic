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
        <!-- Левая колонка -->
        <div class="col-lg-4">
            <!-- Личная информация -->
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

            <!-- Мои подписки -->
            @if(isset($subscriptions))
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-stars text-info me-2"></i>
                        Мои подписки
                    </h5>
                    @if($subscriptions->count() > 0)
                        <span class="badge bg-info">{{ $subscriptions->count() }}</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($subscriptions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($subscriptions as $subscription)
                                @php
                                    $remainingDays = $subscription->getRemainingDays();
                                    $statusClass = $subscription->status === 'active' ? 'success' : 
                                                  ($subscription->status === 'expired' ? 'danger' : 
                                                  ($subscription->status === 'pending' ? 'warning' : 'secondary'));
                                    
                                    // Получаем лимиты для этой подписки
                                    $subscriptionLimits = $personalLimits->where('subscription_id', $subscription->id);
                                    $totalLimits = $subscriptionLimits->sum('quantity');
                                    $totalUsed = $subscriptionLimits->sum('used_quantity');
                                @endphp
                                <div class="list-group-item px-3 py-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-{{ $statusClass }} me-2">
                                                {{ $subscription->getStatusTextAttribute() }}
                                            </span>
                                            <small class="text-muted">#{{ $subscription->id }}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewSubscription({{ $subscription->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="small mb-2">
                                        @if($subscription->starts_at)
                                            <span class="me-3"><i class="bi bi-calendar-plus"></i> {{ $subscription->starts_at->format('d.m.Y') }}</span>
                                        @endif
                                        @if($subscription->ends_at)
                                            <span><i class="bi bi-calendar-x"></i> {{ $subscription->ends_at->format('d.m.Y') }}</span>
                                        @else
                                            <span class="text-muted">бессрочно</span>
                                        @endif
                                    </div>
                                    
                                    @if($totalLimits > 0)
                                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                                            <span class="text-muted">Отчеты:</span>
                                            <span>
                                                <span class="badge bg-primary">{{ $totalLimits }} шт.</span>
                                                <span class="badge bg-info">{{ $totalUsed }} исп.</span>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            @php $percentage = $totalLimits > 0 ? round(($totalUsed / $totalLimits) * 100) : 0; @endphp
                                            <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success') }}" 
                                                 style="width: {{ $percentage }}%">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-stars text-muted fs-1 mb-2 d-block"></i>
                            <p class="text-muted mb-0">У вас нет подписок</p>
                            <small class="text-muted">Подписки выдаются через администратора</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Статистика отчетов (компактная) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart text-primary me-2"></i>
                        Статистика отчетов
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-muted">Всего</div>
                                <div class="h4 mb-0">{{ $totalAll }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-muted">Использовано</div>
                                <div class="h4 mb-0">{{ $totalAllUsed }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-muted">Доступно</div>
                                <div class="h4 mb-0 text-success">{{ $totalAllAvailable }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-muted">Создано отчетов</div>
                                <div class="h4 mb-0">{{ $totalReports }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика по типам отчетов -->
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
                            $allTypes = array_unique(array_merge(
                                array_keys($personalLimitsByType),
                                array_keys($delegatedLimitsByType)
                            ));
                        @endphp
                        
                        @foreach($allTypes as $typeName)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">{{ $typeName }}</small>
                                
                                @if(isset($personalLimitsByType[$typeName]))
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small><i class="bi bi-person-check text-success"></i> Собственные:</small>
                                        <span class="badge bg-light text-dark border">
                                            {{ $personalLimitsByType[$typeName]['available'] }}/{{ $personalLimitsByType[$typeName]['total'] }}
                                        </span>
                                    </div>
                                @endif
                                
                                @if(isset($delegatedLimitsByType[$typeName]))
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small><i class="bi bi-share text-warning"></i> Делегированные:</small>
                                        <span class="badge bg-light text-dark border">
                                            {{ $delegatedLimitsByType[$typeName]['available'] }}/{{ $delegatedLimitsByType[$typeName]['delegated'] }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            @if(!$loop->last)<hr class="my-2">@endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Правая колонка -->
        <div class="col-lg-8">
            <!-- Рабочая информация -->
            @if(isset($memberProfile) && $memberProfile && isset($organization))
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
                                                 style="width: 32px; height: 32px; color: white;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $organization->name }}</strong>
                                                @if($organization->our_organization)
                                                    <div><small class="text-muted">{{ $organization->our_organization }}</small></div>
                                                @endif
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
                                            <div>{{ $memberProfile->boss->user->name }}</div>
                                            <small class="text-muted">{{ $memberProfile->boss->user->email }}</small>
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
                </div>
            </div>
            @endif

            <!-- Табы для лимитов -->
            <div class="card">
                <div class="card-header p-0 bg-light">
                    <ul class="nav nav-tabs" id="limitsTab" role="tablist">
                        @if($personalLimits->count() > 0)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" 
                                        data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person-check me-1"></i>
                                    Собственные
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
                                    Делегированные
                                    <span class="badge bg-warning ms-1">{{ $delegatedLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Собственные лимиты -->
                        @if($personalLimits->count() > 0)
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Подписка</th>
                                                <th>Тип отчета</th>
                                                <th class="text-center">Всего</th>
                                                <th class="text-center">Исп.</th>
                                                <th class="text-center">Дост.</th>
                                                <th>Дата</th>
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
                                                        <span class="badge bg-info">#{{ $limit->subscription_id }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $limit->reportType->name ?? 'Без типа' }}</span>
                                                    </td>
                                                    <td class="text-center">{{ $limit->quantity }}</td>
                                                    <td class="text-center">{{ $limit->used_quantity }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
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
                            <div class="tab-pane fade {{ $personalLimits->count() == 0 ? 'show active' : '' }}" 
                                 id="delegated" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>От кого</th>
                                                <th>Тип отчета</th>
                                                <th class="text-center">Всего</th>
                                                <th class="text-center">Исп.</th>
                                                <th class="text-center">Дост.</th>
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
                                                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-1" 
                                                                     style="width: 24px; height: 24px; color: white; font-size: 0.7rem;">
                                                                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                                                                </div>
                                                                <small>{{ $owner->name }}</small>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $delegated->limit->reportType->name ?? 'Без типа' }}</span>
                                                    </td>
                                                    <td class="text-center">{{ $delegated->quantity }}</td>
                                                    <td class="text-center">{{ $delegated->used_quantity }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
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

<!-- Модальное окно просмотра подписки -->
<div class="modal fade" id="viewSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    Детали подписки
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewSubscriptionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
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
    // Данные подписок для модального окна
    let subscriptionsData = {
        @if(isset($subscriptions))
            @foreach($subscriptions as $sub)
                {{ $sub->id }}: {
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
                <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 70px; height: 70px; color: white; font-size: 1.8rem;">
                    ${sub.user_name.charAt(0).toUpperCase()}
                </div>
                <h5>${sub.user_name}</h5>
                <p class="text-muted small">${sub.user_email}</p>
            </div>
            <table class="table table-sm">
                <tr><th>Дата начала:</th><td>${sub.starts_at}</td></tr>
                <tr><th>Дата окончания:</th><td>${sub.ends_at}</td></tr>
                <tr><th>Статус:</th><td><span class="badge bg-${sub.status_class}">${sub.status_text}</span></td></tr>
                <tr><th>Осталось дней:</th><td>${sub.remaining_days !== null ? sub.remaining_days + ' дн.' : '∞'}</td></tr>
            </table>
        `;
        
        document.getElementById('viewSubscriptionContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('viewSubscriptionModal')).show();
    }

    // Активация табов
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#limitsTab button').forEach(triggerEl => {
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault();
                new bootstrap.Tab(this).show();
            });
        });
    });
</script>
@endpush

<style>
.nav-tabs .nav-link.active {
    color: #0d6efd !important;
    font-weight: 500 !important;
    background-color: #e7f1ff !important;
    border-color: #dee2e6 #dee2e6 #fff !important;
}
</style>
@endsection