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
        <div class="card border-0 shadow-sm">
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

<!-- Лимиты менеджера -->
@if(count($limits) > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-speedometer text-info me-2"></i>
            Ваши отчеты
            <span class="badge bg-info ms-2">{{ count($limits) }}</span>
        </h6>
        <div class="small text-muted">
            <i class="bi bi-info-circle"></i> Обновлено: {{ now()->format('H:i') }}
        </div>
    </div>
    <div class="card-body">
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
                    @foreach($limits as $limit)
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
        
        <!-- Сводка по лимитам -->
        <div class="mt-3 pt-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <div class="small text-muted">
                        @php
                            $interfaceCount = count(array_filter($limits, fn($l) => !$l['only_api']));
                            $apiCount = count(array_filter($limits, fn($l) => $l['only_api']));
                            $exhaustedCount = count(array_filter($limits, fn($l) => $l['is_exhausted']));
                            $hasLimitCount = count(array_filter($limits, fn($l) => $l['has_limit']));
                            $totalAvailable = array_sum(array_column($limits, 'available_quantity'));
                            $totalUsed = array_sum(array_column($limits, 'used_quantity'));
                            $totalQuantity = array_sum(array_column($limits, 'quantity'));
                        @endphp
                        <i class="bi bi-info-circle"></i>
                        Всего отчетов: {{ count($limits) }} | 
                        UI: {{ $interfaceCount }} | 
                        API: {{ $apiCount }} | 
                        Исчерпано: <span class="{{ $exhaustedCount > 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ $exhaustedCount }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted text-end">
                        <i class="bi bi-pie-chart"></i>
                        Всего запросов: {{ $totalQuantity }} | 
                        Использовано: {{ $totalUsed }} | 
                        Доступно: <span class="{{ $totalAvailable > 0 ? 'text-success fw-bold' : 'text-danger' }}">{{ $totalAvailable }}</span>
                    </div>
                </div>
            </div>
            
            @if($exhaustedCount > 0)
                <div class="alert alert-danger py-2 mt-2 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <small>{{ $exhaustedCount }} отчет(ов) исчерпано. Обратитесь к администратору для пополнения.</small>
                </div>
            @endif
            
            <div class="text-end mt-3">
                <a href="{{ route('limits.index') }}" class="btn btn-sm btn-info">
                    <i class="bi bi-gear me-1"></i> Управление отчетами
                </a>
            </div>
        </div>
    </div>
</div>
@else
<!-- Если у менеджера нет лимитов -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0">
            <i class="bi bi-speedometer text-info me-2"></i>
            Отчеты
        </h6>
    </div>
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="bi bi-graph-up display-1 text-muted"></i>
        </div>
        <h4 class="text-muted mb-3">Отчеты не настроены</h4>
        <p class="text-muted mb-4">Настройте отчеты для ваших организаций</p>
        <a href="{{ route('limits.create') }}" class="btn btn-info">
            <i class="bi bi-plus-circle me-1"></i> Создать отчет
        </a>
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
            <a href="{{ route('manager.organization.create') }}" class="btn btn-success">
                <i class="bi bi-building-add"></i> Создать организацию
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($organizations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Организация</th>
                            <th>Владелец</th>
                            <th width="100">Статус</th>
                            <th width="140">Подписка до</th>
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
                                        <small class="text-muted">{{ $organization->created_at->format('d.m.Y') }}</small>
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
                                        'suspended' => ['class' => 'secondary', 'icon' => 'pause-circle', 'text' => 'Приоставлена'],
                                        'expired' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Истекла'],
                                    ];
                                    $config = $statusConfig[$organization->status] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => $organization->status];
                                @endphp
                                <span class="badge bg-{{ $config['class'] }}">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                    {{ $config['text'] }}
                                </span>
                            </td>
                            <td>
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
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('manager.organization.show', $organization->id) }}" 
                                       class="btn btn-sm btn-info rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Просмотреть организацию"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('manager.organization.edit', $organization->id) }}" 
                                       class="btn btn-sm btn-warning rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Редактировать организацию"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- ДОБАВЛЕНА КНОПКА СОЗДАНИЯ ЛИМИТА ДЛЯ ОРГАНИЗАЦИИ -->
                                    <a href="{{ route('limits.create') }}?user_id={{ $organization->owner?->user_id ?? '' }}" 
                                       class="btn btn-sm btn-success rounded-circle d-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Создать лимит для владельца"
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
            
            @if($organizations->count() > 5)
                <div class="text-center mt-3">
                    <a href="{{ route('manager.organizations.list') }}" class="btn btn-success">
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