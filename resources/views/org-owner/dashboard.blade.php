@extends('layouts.app')

@section('title', 'Панель владельца организации')
@section('page-icon', 'bi-buildings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-buildings text-success"></i> Панель владельца организации
    </h2>
    <div>
        <span class="badge bg-success fs-6">Владелец</span>
    </div>
</div>

<!-- Информация о владельце -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
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
                    <span class="badge bg-success">Владелец организации</span>
                    @if(Auth::user()->is_active)
                        <span class="badge bg-success">Активен</span>
                    @else
                        <span class="badge bg-danger">Неактивен</span>
                    @endif
                </div>
                
                @if($organization->manager)
                    <div class="text-muted small mt-3">
                        <div class="mb-1">Ваш менеджер:</div>
                        <div class="fw-bold">{{ $organization->manager->user->name ?? 'Не назначен' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Информация об организации -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-building text-success me-2"></i>
                    Ваша организация
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h3 class="text-primary">{{ $organization->name }}</h3>
                        <p class="text-muted mb-0">ID: {{ $organization->id }}</p>
                        <p class="text-muted">Создана: {{ $organization->created_at->format('d.m.Y') }}</p>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Статус:</span>
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
                            @endswitch
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Подписка до:</span>
                            @if($organization->subscription_ends_at)
                                <span class="{{ $organization->isExpired() ? 'text-danger' : 'text-success' }}">
                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                </span>
                            @else
                                <span class="text-muted">Бессрочно</span>
                            @endif
                        </div>
                        
                        @if($organization->subscription_ends_at && !$organization->isExpired())
                            <div class="d-flex justify-content-between mb-2">
                                <span>Осталось дней:</span>
                                <span class="text-primary fw-bold">
                                    {{ $organization->getRemainingSubscriptionDays() }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                
                @if($organization->isSubscriptionExpiringSoon())
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Подписка истекает через {{ $organization->getRemainingSubscriptionDays() }} дней
                    </div>
                @endif
                
                @if($organization->isExpired())
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-x-circle me-2"></i>
                        Подписка истекла! Обратитесь к менеджеру
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="display-4 text-primary mb-2">{{ $membersCount ?? 0 }}</div>
                <div class="text-muted">Сотрудников</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="display-4 text-success mb-2">{{ $activeMembersCount ?? 0 }}</div>
                <div class="text-muted">Активных</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="display-4 text-info mb-2">{{ $reportsCount ?? 0 }}</div>
                <div class="text-muted">Отчетов</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="display-4 text-warning mb-2">{{ $licensesCount ?? 0 }}</div>
                <div class="text-muted">Лицензий</div>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-lightning text-warning me-2"></i>
                    Быстрые действия
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                            Добавить сотрудника
                        </a>
                    </div>
                    
                    <div class="col-md-3">
                        <a href="{{ route('reports.create') }}" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-file-earmark-plus fs-4 d-block mb-2"></i>
                            Создать отчет
                        </a>
                    </div>
                    
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-people fs-4 d-block mb-2"></i>
                            Управление доступом
                        </a>
                    </div>
                    
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-graph-up fs-4 d-block mb-2"></i>
                            Статистика
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Последние сотрудники -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-people text-primary me-2"></i>
            Ваши сотрудники
            @if($membersCount > 0)
                <span class="badge bg-primary ms-2">{{ $membersCount }}</span>
            @endif
        </h6>
        <a href="#" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-list-ul"></i> Все сотрудники
        </a>
    </div>
    <div class="card-body">
        @if($membersCount > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Должность</th>
                            <th>Статус</th>
                            <th>Дата добавления</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px; color: white; font-size: 0.8rem;">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </div>
                                    <div>{{ $member->user->name }}</div>
                                </div>
                            </td>
                            <td>{{ $member->user->email }}</td>
                            <td>
                                <span class="badge bg-secondary">Сотрудник</span>
                            </td>
                            <td>
                                @if($member->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-danger">Неактивен</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at->format('d.m.Y') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-info" title="Просмотреть">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Сотрудников пока нет</h5>
                <p class="text-muted mb-4">Добавьте первого сотрудника в вашу организацию</p>
                <a href="#" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Добавить сотрудника
                </a>
            </div>
        @endif
    </div>
</div>
@endsection