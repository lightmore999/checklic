@extends('layouts.app')

@section('title', 'Просмотр менеджера')
@section('page-icon', 'bi-person-badge')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-person-badge text-primary me-2"></i>
        Профиль менеджера
    </h5>
    <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
        <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-outline-success btn-sm ms-2">
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
        
        <!-- Организации менеджера -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-buildings text-success me-2"></i>
                    Организации менеджера
                    @php
                        $managerOrganizations = $manager->managerProfile ? $manager->managerProfile->organizations : collect();
                    @endphp
                    <span class="badge bg-success ms-2">{{ $managerOrganizations->count() }}</span>
                </h6>
                <a href="{{ route('admin.organization.create') }}?manager_id={{ $manager->managerProfile->id ?? '' }}" 
                   class="btn btn-success btn-sm">
                    <i class="bi bi-plus"></i> Создать организацию
                </a>
            </div>
            <div class="card-body">
                @if($managerOrganizations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Название организации</th>
                                    <th>Владелец</th>
                                    <th>Статус</th>
                                    <th>Подписка до</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($managerOrganizations as $organization)
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
                                                    <small class="text-muted">Владелец</small>
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
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.organization.show', $organization->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="Просмотреть">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
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
                        <p class="text-muted mb-4">Этот менеджер еще не создал ни одной организации</p>
                        <a href="{{ route('admin.organization.create') }}?manager_id={{ $manager->managerProfile->id ?? '' }}" 
                           class="btn btn-success">
                            <i class="bi bi-building-add"></i> Создать организацию
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Действия и статистика -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>
                    Действия
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('admin.managers.toggle-status', $manager->id) }}">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-{{ $manager->is_active ? 'warning' : 'success' }} w-100 mb-2">
                            <i class="bi bi-{{ $manager->is_active ? 'ban' : 'check' }} me-1"></i>
                            {{ $manager->is_active ? 'Деактивировать' : 'Активировать' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.managers.delete', $manager->id) }}"
                        onsubmit="return confirm('Удалить менеджера {{ $manager->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Удалить менеджера
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart text-info me-2"></i>
                    Статистика
                </h6>
            </div>
            <div class="card-body">
                @php
                    $orgCount = $managerOrganizations->count();
                    $activeOrgs = $managerOrganizations->where('status', 'active')->count();
                    $pendingOrgs = $managerOrganizations->where('status', 'pending')->count();
                @endphp
                
                <div class="text-center py-3 border-bottom">
                    <div class="display-6 text-primary mb-2">{{ $orgCount }}</div>
                    <div class="text-muted">Организаций</div>
                </div>
                <div class="text-center py-3 border-bottom">
                    <div class="display-6 text-success mb-2">{{ $activeOrgs }}</div>
                    <div class="text-muted">Активных</div>
                </div>
                <div class="text-center py-3">
                    <div class="display-6 text-warning mb-2">{{ $pendingOrgs }}</div>
                    <div class="text-muted">Ожидающих</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection