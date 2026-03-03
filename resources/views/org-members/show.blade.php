@extends('layouts.app')

@section('title', $member->user->name)

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
        
        $routePrefix = $isAdmin ? 'admin.' : ($isManager ? 'manager.' : 'owner.');
    @endphp

    <!-- Хедер -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" 
                 style="width: 50px; height: 50px; color: white; font-size: 1.5rem;">
                {{ strtoupper(substr($member->user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="h3 mb-1">{{ $member->user->name }}</h1>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-info">Сотрудник</span>
                    @if($member->user->is_active)
                        <span class="badge bg-success">Активен</span>
                    @else
                        <span class="badge bg-danger">Неактивен</span>
                    @endif
                    @if($member->user->phone)
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-telephone"></i> {{ $member->user->phone }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
            @if($isAdmin || $isManager || $isOwner)
                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                   class="btn btn-primary">
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

    <!-- Статистика отчетов -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text fs-2 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-1">Всего отчетов</h6>
                            <h3 class="mb-0">{{ $totalReports }}</h3>
                            <small class="text-muted">{{ $completedReports }} завершено</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle fs-2 text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">Завершено</h6>
                            <h3 class="mb-0">{{ $completedReports }}</h3>
                            <small class="text-muted">{{ $inProgressReports }} в работе</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-check fs-2 text-warning me-3"></i>
                        <div>
                            <h6 class="mb-1">За этот месяц</h6>
                            <h3 class="mb-0">{{ $thisMonthReports }}</h3>
                            <small class="text-muted">с {{ now()->format('d.m') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-stars fs-2 text-info me-3"></i>
                        <div>
                            <h6 class="mb-1">Доступно отчетов</h6>
                            <h3 class="mb-0">{{ $totalAllAvailable }}</h3>
                            <small class="text-muted">из {{ $totalAll }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Левая колонка -->
        <div class="col-lg-4">
            <!-- Личная информация -->
            <div class="card mb-4">
                <div class="card-header py-2 bg-light">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Личная информация</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px; color: white; font-size: 1.5rem;">
                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $member->user->name }}</h5>
                            <p class="text-muted mb-0 small">{{ $member->user->email }}</p>
                        </div>
                    </div>
                    
                    <div class="row g-2 small">
                        <div class="col-5 text-muted">Телефон:</div>
                        <div class="col-7">{{ $member->user->phone ?? '—' }}</div>
                        
                        <div class="col-5 text-muted">Регистрация:</div>
                        <div class="col-7">{{ $member->user->created_at->format('d.m.Y') }}</div>
                        
                        <div class="col-5 text-muted">Организация:</div>
                        <div class="col-7">
                            <span class="badge bg-secondary">{{ $organization->name }}</span>
                        </div>
                        
                        @if($member->boss && $member->boss->user)
                        <div class="col-5 text-muted">Начальник:</div>
                        <div class="col-7">{{ $member->boss->user->name }}</div>
                        @endif
                        
                        @if($member->manager && $member->manager->user)
                        <div class="col-5 text-muted">Менеджер:</div>
                        <div class="col-7">{{ $member->manager->user->name }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Подписки сотрудника -->
            @if(isset($subscriptions))
            <div class="card mb-4">
                <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-stars text-info me-2"></i>Подписки</h6>
                    @if($subscriptions->count() > 0)
                        <span class="badge bg-info">{{ $subscriptions->count() }}</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    @if($subscriptions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($subscriptions as $subscription)
                                @php
                                    $remainingDays = $subscription->getRemainingDays();
                                    $statusClass = $subscription->status === 'active' ? 'success' : 
                                                  ($subscription->status === 'expired' ? 'danger' : 'warning');
                                    
                                    $subscriptionLimits = $personalLimits->where('subscription_id', $subscription->id);
                                    $totalLimits = $subscriptionLimits->sum('quantity');
                                @endphp
                                <div class="list-group-item px-0 py-2 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-{{ $statusClass }} me-2">
                                                {{ $subscription->getStatusTextAttribute() }}
                                            </span>
                                            @if($subscription->ends_at)
                                                <small>до {{ $subscription->ends_at->format('d.m.Y') }}</small>
                                            @else
                                                <small>бессрочно</small>
                                            @endif
                                        </div>
                                        @if($totalLimits > 0)
                                            <span class="badge bg-info">{{ $totalLimits }} шт.</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small text-center mb-0">
                            <i class="bi bi-stars me-1"></i> Нет подписок
                        </p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Статистика по типам отчетов -->
            @if($reportsByType->count() > 0)
            <div class="card mb-4">
                <div class="card-header py-2 bg-light">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>По типам отчетов</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($reportsByType as $typeId => $stats)
                        @php
                            $percentage = $stats['count'] > 0 ? round(($stats['completed'] / $stats['count']) * 100) : 0;
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>{{ $reportTypes[$typeId] ?? 'Тип #' . $typeId }}</span>
                                <span class="text-muted">{{ $stats['completed'] }}/{{ $stats['count'] }}</span>
                            </div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'info') }}" 
                                     style="width: {{ $percentage }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Действия -->
            @if($isAdmin || $isManager || $isOwner)
                <div class="card">
                    <div class="card-header py-2 bg-light">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Действия</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i> Редактировать
                            </a>

                            <form action="{{ route($routePrefix . 'org-members.toggle-status', [$organization->id, $member->id]) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-sm btn-{{ $member->is_active ? 'warning' : 'success' }} w-100">
                                    <i class="bi bi-toggle-{{ $member->is_active ? 'off' : 'on' }}"></i>
                                    {{ $member->is_active ? 'Деактивировать' : 'Активировать' }}
                                </button>
                            </form>

                            @if($isOwner)
                                <button type="button" class="btn btn-sm btn-warning delegate-btn"
                                        data-employee-id="{{ $member->user->id }}"
                                        data-employee-name="{{ $member->user->name }}">
                                    <i class="bi bi-share"></i> Делегировать
                                </button>
                            @endif

                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Правая колонка: Таблицы лимитов -->
        <div class="col-lg-8">
            <!-- Сводка по лимитам -->
            @if($personalLimits->count() > 0 || $delegatedLimits->count() > 0)
            <div class="row g-3 mb-4">
                @if($personalLimits->count() > 0)
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><i class="bi bi-person-check text-success"></i> Собственные</h6>
                                    <div class="small">
                                        <span class="text-muted">Доступно:</span>
                                        <strong class="text-success">{{ $totalPersonalAvailable }}</strong> из {{ $totalPersonal }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0">{{ $totalPersonalUsed }}</h4>
                                    <small class="text-muted">использовано</small>
                                </div>
                            </div>
                            @if($totalPersonal > 0)
                                @php $personalPercentage = round(($totalPersonalUsed / $totalPersonal) * 100); @endphp
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-{{ $personalPercentage > 80 ? 'danger' : ($personalPercentage > 50 ? 'warning' : 'success') }}" 
                                         style="width: {{ $personalPercentage }}%">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($delegatedLimits->count() > 0)
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><i class="bi bi-share text-warning"></i> Делегированные</h6>
                                    <div class="small">
                                        <span class="text-muted">Доступно:</span>
                                        <strong class="text-success">{{ $totalDelegatedAvailable }}</strong> из {{ $totalDelegated }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0">{{ $totalDelegatedUsed }}</h4>
                                    <small class="text-muted">использовано</small>
                                </div>
                            </div>
                            @if($totalDelegated > 0)
                                @php $delegatedPercentage = round(($totalDelegatedUsed / $totalDelegated) * 100); @endphp
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-{{ $delegatedPercentage > 80 ? 'danger' : ($delegatedPercentage > 50 ? 'warning' : 'success') }}" 
                                         style="width: {{ $delegatedPercentage }}%">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Табы для лимитов -->
            <div class="card">
                <div class="card-header p-0 bg-light">
                    <ul class="nav nav-tabs" id="limitsTab" role="tablist">
                        @if($personalLimits->count() > 0)
                            <li class="nav-item">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" 
                                        data-bs-target="#personal" type="button" role="tab">
                                    <i class="bi bi-person-check me-1"></i>
                                    Собственные
                                    <span class="badge bg-success ms-1">{{ $personalLimits->count() }}</span>
                                </button>
                            </li>
                        @endif
                        @if($delegatedLimits->count() > 0)
                            <li class="nav-item">
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
                
                <div class="card-body p-3">
                    <div class="tab-content">
                        <!-- Собственные лимиты -->
                        @if($personalLimits->count() > 0)
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
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
                                                @php $available = $limit->getAvailableQuantity(); @endphp
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-info">#{{ $limit->subscription_id }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold small">{{ $limit->reportType->name ?? 'Без типа' }}</span>
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
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
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
                                                                     style="width: 22px; height: 22px; color: white; font-size: 0.7rem;">
                                                                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                                                                </div>
                                                                <small>{{ $owner->name }}</small>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-semibold small">{{ $delegated->limit->reportType->name ?? 'Без типа' }}</span>
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
                            <div class="text-center py-4">
                                <i class="bi bi-clipboard-x text-muted fs-1 mb-3 d-block"></i>
                                <p class="text-muted mb-0">У сотрудника нет отчетов</p>
                                @if($isOwner)
                                    <small class="text-muted">Вы можете делегировать отчеты этому сотруднику</small>
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
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    color: #495057;
    background-color: #f8f9fa;
    margin-right: 2px;
}

.nav-tabs .nav-link.active {
    color: #0d6efd !important;
    font-weight: 500 !important;
    background-color: #ffffff !important;
    border-bottom: 2px solid #0d6efd !important;
}

.nav-tabs {
    padding-left: 0.5rem;
    padding-top: 0.25rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .nav-tabs .nav-link {
        padding: 0.4rem 0.6rem;
        font-size: 0.85rem;
    }
}
</style>
@endsection