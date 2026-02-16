@extends('layouts.app')

@section('title', 'Просмотр менеджера')
@section('page-icon', 'bi-person-badge')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-person-badge text-primary me-2"></i>
        Профиль менеджера: {{ $manager->name }}
    </h5>
    <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
        <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-success btn-sm ms-2">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
    </div>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    Основная информация
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; font-size: 2rem; color: white;">
                            {{ strtoupper(substr($manager->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Имя:</div>
                            <div class="col-md-8">{{ $manager->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Email:</div>
                            <div class="col-md-8">{{ $manager->email }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Роль:</div>
                            <div class="col-md-8">
                                <span class="badge bg-primary">Менеджер</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Статус:</div>
                            <div class="col-md-8">
                                @if($manager->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-danger">Неактивен</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Зарегистрирован:</div>
                            <div class="col-md-8">{{ $manager->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                        @if($manager->managerProfile && $manager->managerProfile->admin)
                        <div class="row">
                            <div class="col-md-4 fw-bold">Создал:</div>
                            <div class="col-md-8">
                                <span class="badge bg-dark">
                                    {{ $manager->managerProfile->admin->name }} (администратор)
                                </span>
                            </div>
                        </div>
                        @endif
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
                    Отчеты менеджера
                    <span class="badge bg-info ms-2">{{ count($limits) }}</span>
                </h6>
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
                                        <span class="badge bg-warning">API</span>
                                    @else
                                        <span class="badge bg-primary">UI</span>
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
                                        <span class="badge bg-danger">Исчерпан</span>
                                    @elseif(!$limit['has_limit'])
                                        <span class="badge bg-secondary">Не настроен</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
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
                                                     style="width: {{ $percentage }}%">
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
                    <div class="small text-muted">
                        @php
                            $exhaustedCount = count(array_filter($limits, fn($l) => $l['is_exhausted']));
                            $totalAvailable = array_sum(array_column($limits, 'available_quantity'));
                            $totalUsed = array_sum(array_column($limits, 'used_quantity'));
                            $totalQuantity = array_sum(array_column($limits, 'quantity'));
                        @endphp
                        <i class="bi bi-info-circle"></i>
                        Всего отчетов: {{ count($limits) }} | 
                        Исчерпано: <span class="{{ $exhaustedCount > 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ $exhaustedCount }}</span> |
                        Всего запросов: {{ $totalQuantity }} | 
                        Использовано: {{ $totalUsed }} | 
                        Доступно: <span class="{{ $totalAvailable > 0 ? 'text-success fw-bold' : 'text-danger' }}">{{ $totalAvailable }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Организации менеджера -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-buildings text-success me-2"></i>
                    Организации менеджера
                    <span class="badge bg-success ms-2">{{ $organizations->count() }}</span>
                </h6>
            </div>
            <div class="card-body">
                @if($organizations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Название организации</th>
                                    <th>Владелец</th>
                                    <th>Статус</th>
                                    <th>Подписка до</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($organizations as $organization)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $organization->name }}</div>
                                        <small class="text-muted">ID: {{ $organization->id }}</small>
                                    </td>
                                    <td>
                                        @if($organization->owner && $organization->owner->user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($organization->owner->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="small">{{ $organization->owner->user->name }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-danger">Не назначен</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($organization->status)
                                            @case('active')
                                                <span class="badge bg-success">Активна</span>
                                                @break
                                            @case('inactive')
                                                <span class="badge bg-danger">Неактивна</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning">Ожидает</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $organization->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($organization->subscription_ends_at)
                                            @if($organization->subscription_ends_at->isPast())
                                                <span class="text-danger small">
                                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="text-success small">
                                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Бессрочно</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-buildings fs-1 text-muted mb-3"></i>
                        <h6 class="text-muted">Организаций пока нет</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Статистика -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart text-info me-2"></i>
                    Статистика
                </h6>
            </div>
            <div class="card-body">
                @php
                    $orgCount = $organizations->count();
                    $activeOrgs = $organizations->where('status', 'active')->count();
                    $pendingOrgs = $organizations->where('status', 'pending')->count();
                    $inactiveOrgs = $organizations->where('status', 'inactive')->count();
                @endphp
                
                <div class="text-center py-3 border-bottom">
                    <div class="display-6 text-primary mb-2">{{ $orgCount }}</div>
                    <div class="text-muted">Всего организаций</div>
                </div>
                <div class="text-center py-3 border-bottom">
                    <div class="display-6 text-success mb-2">{{ $activeOrgs }}</div>
                    <div class="text-muted">Активных</div>
                </div>
                <div class="text-center py-3 border-bottom">
                    <div class="display-6 text-warning mb-2">{{ $pendingOrgs }}</div>
                    <div class="text-muted">Ожидающих</div>
                </div>
                <div class="text-center py-3">
                    <div class="display-6 text-secondary mb-2">{{ $inactiveOrgs }}</div>
                    <div class="text-muted">Неактивных</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection