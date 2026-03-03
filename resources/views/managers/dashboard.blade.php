@extends('layouts.app')

@section('title', 'Панель менеджера')
@section('page-icon', 'bi-speedometer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-speedometer text-primary me-2"></i>
        Панель менеджера
    </h5>
    <div>
        <a href="{{ route('manager.organization.create') }}" class="btn btn-success">
            <i class="bi bi-building-add me-2"></i>
            Создать организацию
        </a>
    </div>
</div>

<!-- Информация о менеджере -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    Ваш профиль
                </h6>
            </div>
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; color: white;">
                    {{ strtoupper(substr($manager->name, 0, 1)) }}
                </div>
                <h5 class="mb-1">{{ $manager->name }}</h5>
                <p class="text-muted mb-3">{{ $manager->email }}</p>
                
                <div class="mb-3">
                    <span class="badge bg-primary">Менеджер</span>
                    @if($manager->is_active)
                        <span class="badge bg-success">Активен</span>
                    @else
                        <span class="badge bg-danger">Неактивен</span>
                    @endif
                </div>
                
                @if($admin)
                    <div class="text-muted small mt-4 pt-3 border-top">
                        <div class="mb-1">Ваш администратор:</div>
                        <div class="fw-bold">{{ $admin->name }}</div>
                        <div class="small">{{ $admin->email }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <div class="display-4 text-primary mb-2">{{ $stats['total_organizations'] }}</div>
                        <div class="text-muted">Всего организаций</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <div class="display-4 text-success mb-2">{{ $stats['active_organizations'] }}</div>
                        <div class="text-muted">Активные</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <div class="display-4 text-warning mb-2">{{ $stats['pending_organizations'] }}</div>
                        <div class="text-muted">Ожидают</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Подписки менеджера -->
@if(isset($subscriptions) && $subscriptions->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
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
                    @endphp
                    <tr>
                        <td>#{{ $subscription->id }}</td>
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

<!-- Отчеты по подпискам менеджера -->
@if(isset($groupedLimits) && count($groupedLimits) > 0)
    @foreach($groupedLimits as $group)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-stars text-info me-2"></i>
                        Подписка #{{ $group['subscription']->id }}
                        @if($group['subscription']->status === 'active')
                            <span class="badge bg-success ms-2">Активна</span>
                        @elseif($group['subscription']->status === 'expired')
                            <span class="badge bg-danger ms-2">Истекла</span>
                        @elseif($group['subscription']->status === 'pending')
                            <span class="badge bg-warning ms-2">Ожидает</span>
                        @elseif($group['subscription']->status === 'suspended')
                            <span class="badge bg-warning ms-2">Приостановлена</span>
                        @elseif($group['subscription']->status === 'cancelled')
                            <span class="badge bg-secondary ms-2">Отменена</span>
                        @else
                            <span class="badge bg-secondary ms-2">{{ $group['subscription']->getStatusTextAttribute() }}</span>
                        @endif
                    </h6>
                    <div class="text-muted small">
                        @if($group['subscription']->starts_at)
                            <span class="me-3"><i class="bi bi-calendar-plus me-1"></i>С {{ $group['subscription']->starts_at->format('d.m.Y') }}</span>
                        @endif
                        @if($group['subscription']->ends_at)
                            <span><i class="bi bi-calendar-x me-1"></i>до {{ $group['subscription']->ends_at->format('d.m.Y') }}</span>
                            @if($group['subscription']->getRemainingDays())
                                <span class="badge bg-{{ $group['subscription']->getRemainingDays() <= 7 ? 'warning' : 'info' }} ms-2">
                                    осталось {{ $group['subscription']->getRemainingDays() }} дн.
                                </span>
                            @endif
                        @else
                            <span class="text-muted">(бессрочная)</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Статистика по подписке -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block">Всего отчетов</small>
                            <span class="fw-bold fs-5">{{ $group['total_quantity'] }} шт.</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block">Использовано</small>
                            <span class="fw-bold fs-5 text-warning">{{ $group['total_used'] }} шт.</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block">Доступно</small>
                            <span class="fw-bold fs-5 text-{{ $group['total_available'] > 0 ? 'success' : 'danger' }}">
                                {{ $group['total_available'] }} шт.
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block">Типов отчетов</small>
                            <span class="fw-bold fs-5 text-info">{{ count($group['limits']) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Тип отчета</th>
                                <th>Описание</th>
                                <th>Доступ через</th>
                                <th>Всего</th>
                                <th>Использовано</th>
                                <th>Доступно</th>
                                <th>Статус</th>
                                <th>Прогресс</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['limits'] as $limit)
                            <tr>
                                <td>
                                    <strong>{{ $limit['report_type_name'] }}</strong>
                                </td>
                                <td>
                                    @if($limit['description'])
                                        <small class="text-muted">{{ Str::limit($limit['description'], 50) }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($limit['only_api'])
                                        <span class="badge bg-warning" title="Только через API">
                                            <i class="bi bi-plug"></i> API
                                        </span>
                                    @else
                                        <span class="badge bg-primary" title="Доступен в интерфейсе">
                                            <i class="bi bi-window"></i> UI
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $limit['quantity'] }} шт.</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $limit['used_quantity'] }} шт.</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $limit['available_quantity'] > 0 ? 'success' : 'danger' }}">
                                        {{ $limit['available_quantity'] }} шт.
                                    </span>
                                </td>
                                <td>
                                    @if($limit['is_exhausted'])
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Исчерпан
                                        </span>
                                    @elseif(!$limit['has_limit'])
                                        <span class="badge bg-secondary">Не настроен</span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Активен
                                        </span>
                                    @endif
                                </td>
                                <td style="min-width: 150px;">
                                    @if($limit['quantity'] > 0)
                                        @php
                                            $percentage = round(($limit['used_quantity'] / $limit['quantity']) * 100);
                                            $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                            ($percentage > 80 ? 'bg-danger' : 
                                                            ($percentage > 50 ? 'bg-warning' : 'bg-success'));
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar {{ $progressClass }}" 
                                                     style="width: {{ $percentage }}%"
                                                     role="progressbar"
                                                     aria-valuenow="{{ $percentage }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $percentage }}%</small>
                                        </div>
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
    
    <!-- Общая сводка по всем подпискам -->
    @php
        $totalAllQuantity = collect($groupedLimits)->sum('total_quantity');
        $totalAllUsed = collect($groupedLimits)->sum('total_used');
        $totalAllAvailable = collect($groupedLimits)->sum('total_available');
        $totalAllPercentage = $totalAllQuantity > 0 ? round(($totalAllUsed / $totalAllQuantity) * 100) : 0;
        $totalAllSubscriptions = count($groupedLimits);
    @endphp
    
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex flex-wrap align-items-center gap-4">
                        <div>
                            <span class="text-muted">Подписок:</span>
                            <span class="fw-bold ms-2">{{ $totalAllSubscriptions }}</span>
                        </div>
                        <div>
                            <span class="text-muted">Всего отчетов:</span>
                            <span class="fw-bold ms-2">{{ $totalAllQuantity }} шт.</span>
                        </div>
                        <div>
                            <span class="text-muted">Использовано:</span>
                            <span class="fw-bold text-warning ms-2">{{ $totalAllUsed }} шт.</span>
                        </div>
                        <div>
                            <span class="text-muted">Доступно:</span>
                            <span class="fw-bold text-{{ $totalAllAvailable > 0 ? 'success' : 'danger' }} ms-2">
                                {{ $totalAllAvailable }} шт.
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted">Общий прогресс:</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-{{ $totalAllPercentage > 80 ? 'danger' : ($totalAllPercentage > 50 ? 'warning' : 'success') }}" 
                                 style="width: {{ $totalAllPercentage }}%">
                            </div>
                        </div>
                        <small class="text-muted">{{ $totalAllPercentage }}%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0">
                <i class="bi bi-speedometer text-info me-2"></i>
                Ваши отчеты
            </h6>
        </div>
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-graph-up display-1 text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">У вас нет отчетов</h4>
            <p class="text-muted mb-4">Создайте подписку и добавьте отчеты</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('subscriptions.create') }}" class="btn btn-info">
                    <i class="bi bi-stars me-1"></i> Создать подписку
                </a>
                <a href="{{ route('limits.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Создать отчет
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Все организации менеджера -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0">
                <i class="bi bi-buildings text-success me-2"></i>
                Ваши организации
                <span class="badge bg-success ms-2">{{ $organizations->count() }}</span>
            </h6>
            <small class="text-muted">Последние созданные организации</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manager.organizations.list') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-list-ul me-1"></i> Все организации
            </a>
            <a href="{{ route('manager.organization.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-building-add me-1"></i> Создать
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($organizations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Организация / ИНН</th>
                            <th>Владелец</th>
                            <th width="100">Статус</th>
                            <th width="120" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organizations as $organization)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ Str::limit($organization->name, 30) }}</div>
                                        @if($organization->inn)
                                            <small class="text-muted">ИНН: {{ $organization->inn }}</small>
                                        @endif
                                        <small class="text-muted d-block">{{ $organization->created_at->format('d.m.Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($organization->owner && $organization->owner->user)
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-2" 
                                            style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="small fw-bold">{{ $organization->owner->user->name }}</div>
                                            <small class="text-muted">Владелец</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-danger">Не назначен</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusConfig = [
                                        'active' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Активна'],
                                        'inactive' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Неактивна'],
                                        'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Ожидает'],
                                        'suspended' => ['class' => 'warning', 'icon' => 'pause-circle', 'text' => 'Приостановлена'],
                                        'expired' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Истекла'],
                                    ];
                                    $config = $statusConfig[$organization->status] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => $organization->status];
                                @endphp
                                <span class="badge bg-{{ $config['class'] }}">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                    {{ $config['text'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('manager.organization.show', $organization->id) }}" 
                                       class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Просмотреть организацию"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('manager.organization.edit', $organization->id) }}" 
                                       class="btn btn-sm btn-outline-warning rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Редактировать организацию"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('limits.create') }}?user_id={{ $organization->owner?->user_id ?? '' }}" 
                                       class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Создать отчет для владельца"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($organizations->count() >= 5)
                <div class="text-center mt-3">
                    <a href="{{ route('manager.organizations.list') }}" class="btn btn-outline-success">
                        <i class="bi bi-list-ul me-1"></i> Показать все организации ({{ $organizations->count() }})
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-buildings display-1 text-muted"></i>
                </div>
                <h4 class="text-muted mb-3">Организаций пока нет</h4>
                <p class="text-muted mb-4">Создайте свою первую организацию</p>
                <a href="{{ route('manager.organization.create') }}" class="btn btn-success">
                    <i class="bi bi-building-add me-1"></i> Создать организацию
                </a>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Инициализация тултипов
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush