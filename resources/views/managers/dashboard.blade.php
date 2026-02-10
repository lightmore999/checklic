@extends('layouts.app')

@section('title', 'Панель менеджера')
@section('page-icon', 'bi-speedometer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-speedometer text-primary me-2"></i>
        Панель менеджера
    </h5>
    <div class="d-flex gap-2">
        <!-- ДОБАВЛЕНА КНОПКА УПРАВЛЕНИЯ ЛИМИТАМИ -->
        <a href="{{ route('limits.index') }}" class="btn btn-info">
            <i class="bi bi-graph-up"></i> Управление лимитами
        </a>
        <a href="{{ route('manager.organization.create') }}" class="btn btn-success">
            <i class="bi bi-building-add"></i> Создать организацию
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
                
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('manager.profile') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person"></i> Мой профиль
                    </a>
                    <a href="{{ route('manager.profile.edit') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil"></i> Редактировать профиль
                    </a>
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
        
        <!-- Быстрые действия -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <a href="{{ route('manager.organization.create') }}" class="btn btn-outline-success w-100">
                                    <i class="bi bi-building-add me-1"></i> Новая организация
                                </a>
                            </div>
                            <div class="col-md-3">
                                <!-- ДОБАВЛЕНА КНОПКА ДЛЯ МЕНЕДЖЕРА -->
                                <a href="{{ route('limits.create') }}" class="btn btn-outline-info w-100">
                                    <i class="bi bi-plus-circle me-1"></i> Создать лимит
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('reports.create') }}" class="btn btn-outline-info w-100">
                                    <i class="bi bi-file-earmark-plus me-1"></i> Создать отчет
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('manager.organizations.list') }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-list-ul me-1"></i> Все организации
                                </a>
                            </div>
                        </div>
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
            Ваши лимиты
            <span class="badge bg-secondary ms-2">{{ now()->format('d.m.Y') }}</span>
        </h6>
        <div class="small text-muted">
            <i class="bi bi-info-circle"></i> Обновлено: {{ now()->format('H:i') }}
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($limits as $limit)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-{{ $limit['is_exhausted'] ? 'danger' : ($limit['only_api'] ? 'warning' : 'primary') }} shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0 text-truncate" title="{{ $limit['report_type_name'] }}">
                                <i class="bi bi-{{ $limit['only_api'] ? 'plug' : 'window' }} me-1"></i>
                                {{ $limit['report_type_name'] }}
                            </h6>
                            @if($limit['only_api'])
                                <span class="badge bg-warning" title="Только через API">API</span>
                            @else
                                <span class="badge bg-primary" title="Доступен в интерфейсе">UI</span>
                            @endif
                        </div>
                        
                        @if($limit['description'])
                            <p class="small text-muted mb-2">{{ Str::limit($limit['description'], 60) }}</p>
                        @endif
                        
                        <div class="text-center my-3">
                            <div class="display-6 {{ $limit['is_exhausted'] ? 'text-danger' : ($limit['quantity'] > 0 ? 'text-success' : 'text-secondary') }}">
                                {{ $limit['quantity'] }}
                            </div>
                            <small class="text-muted">осталось запросов</small>
                        </div>
                        
                        <div class="progress mb-2" style="height: 6px;">
                            @php
                                $percentage = $limit['quantity'] > 0 ? min(100, ($limit['quantity'] / 100) * 100) : 0;
                                $progressClass = $limit['is_exhausted'] ? 'bg-danger' : 
                                                ($limit['quantity'] > 50 ? 'bg-success' : 
                                                ($limit['quantity'] > 10 ? 'bg-warning' : 'bg-danger'));
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" 
                                 style="width: {{ $percentage }}%"
                                 role="progressbar"
                                 aria-valuenow="{{ $limit['quantity'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center small">
                            <div>
                                @if($limit['has_limit'])
                                    <span class="text-success" title="Индивидуальный лимит установлен">
                                        <i class="bi bi-check-circle-fill"></i> Установлен
                                    </span>
                                @else
                                    <span class="text-muted" title="Лимит не настроен">
                                        <i class="bi bi-dash-circle"></i> Не настроен
                                    </span>
                                @endif
                            </div>
                            
                            @if($limit['is_exhausted'])
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Исчерпан
                                </span>
                            @elseif($limit['quantity'] == 0)
                                <span class="badge bg-secondary">Нет лимита</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Сводка по лимитам -->
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    @php
                        $interfaceCount = count(array_filter($limits, fn($l) => !$l['only_api']));
                        $apiCount = count(array_filter($limits, fn($l) => $l['only_api']));
                        $exhaustedCount = count(array_filter($limits, fn($l) => $l['is_exhausted']));
                        $hasLimitCount = count(array_filter($limits, fn($l) => $l['has_limit']));
                    @endphp
                    <i class="bi bi-info-circle"></i>
                    Всего: {{ count($limits) }} | 
                    UI: {{ $interfaceCount }} | 
                    API: {{ $apiCount }} | 
                    Исчерпано: <span class="{{ $exhaustedCount > 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ $exhaustedCount }}</span>
                </div>
                
                <!-- ДОБАВЛЕНА ССЫЛКА ДЛЯ МЕНЕДЖЕРА -->
                <div>
                    <a href="{{ route('limits.index') }}" class="btn btn-sm btn-info">
                        <i class="bi bi-graph-up me-1"></i> Управление лимитами
                    </a>
                </div>
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
            Лимиты
        </h6>
    </div>
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="bi bi-graph-up display-1 text-muted"></i>
        </div>
        <h4 class="text-muted mb-3">Лимиты не настроены</h4>
        <p class="text-muted mb-4">Настройте лимиты для ваших организаций</p>
        <a href="{{ route('limits.create') }}" class="btn btn-info">
            <i class="bi bi-plus-circle me-1"></i> Создать лимит
        </a>
        <a href="{{ route('limits.bulk-create') }}" class="btn btn-success ms-2">
            <i class="bi bi-layer-group me-1"></i> Массовое создание
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
            <!-- ДОБАВЛЕНА КНОПКА МАССОВОГО СОЗДАНИЯ ЛИМИТОВ -->
            <a href="{{ route('limits.bulk-create') }}" class="btn btn-info">
                <i class="bi bi-layer-group"></i> Массовые лимиты
            </a>
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
                                    
                                    <!-- ДОБАВЛЕНА КНОПКА СОЗДАНИЯ ЛИМИТА ДЛЯ ОРГАНИЗАЦИИ -->
                                    <a href="{{ route('limits.create') }}?user_id={{ $organization->owner?->user_id ?? '' }}" 
                                       class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center"
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