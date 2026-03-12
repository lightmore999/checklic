@extends('layouts.app')

@section('title', 'Логи действий пользователей и организаций')
@section('page-icon', 'bi-journal-text')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white shadow-lg" 
                 style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Логи действий</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-primary px-3 py-2">
                                        <i class="bi bi-database me-1"></i>Всего: {{ $logs->total() }}
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}" class="text-white opacity-75">
                                                Панель админа
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Логи действий
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.logs.export', request()->query()) }}" class="btn btn-light">
                                <i class="bi bi-download me-2"></i>Экспорт в CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row g-4 mb-4" id="statistics-container">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">Всего записей</h6>
                            <h3 class="mb-0 fw-bold" id="stat-total">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">За сегодня</h6>
                            <h3 class="mb-0 fw-bold" id="stat-today">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">За неделю</h6>
                            <h3 class="mb-0 fw-bold" id="stat-week">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 me-3" 
                             style="width: 48px; height: 48px;"></div>
                        <div>
                            <h6 class="text-muted mb-1">За месяц</h6>
                            <h3 class="mb-0 fw-bold" id="stat-month">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>
                Фильтры
                <button class="btn btn-sm btn-link float-end text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filters">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filters">
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('admin.logs.index') }}" id="filterForm" class="row g-3">
                    <!-- Фильтр по организации -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Организация</label>
                        <select name="organization_id" id="organization_id" class="form-select select2-organization">
                            <option value="">Все организации</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} @if($org->inn) (ИНН: {{ $org->inn }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по пользователю с поиском -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Пользователь</label>
                        <select name="user_id" id="user_id" class="form-select select2-user" data-placeholder="Поиск пользователя...">
                            <option value="">Все пользователи</option>
                            @foreach($users as $userOption)
                                <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                                    {{ $userOption->name }} ({{ $userOption->email }}) - {{ $userOption->getRoleDisplayName() }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Можно ввести имя или email для поиска</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Тип сущности</label>
                        <select name="entity_type" class="form-select">
                            <option value="">Все типы</option>
                            @foreach($entityTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('entity_type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Действие</label>
                        <select name="action" class="form-select">
                            <option value="">Все действия</option>
                            @foreach($actions as $value => $label)
                                <option value="{{ $value }}" {{ request('action') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">ID сущности</label>
                        <input type="number" name="entity_id" class="form-control" value="{{ request('entity_id') }}" placeholder="ID">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Поиск по тексту</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Поиск...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Дата с</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Дата по</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Применить фильтры
                            </button>
                            <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request()->anyFilled(['organization_id', 'user_id', 'entity_type', 'action', 'entity_id', 'search', 'date_from', 'date_to']))
        <div class="alert alert-info py-2 mb-4">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <i class="bi bi-funnel-fill me-1"></i>
                <span class="fw-semibold">Активные фильтры:</span>
                
                @if(request('organization_id'))
                    @php 
                        $org = isset($organizations) ? $organizations->firstWhere('id', request('organization_id')) : null; 
                    @endphp
                    <span class="badge bg-info text-white">Организация: {{ $org->name ?? 'ID: ' . request('organization_id') }}</span>
                @endif
                
                @if(request('user_id'))
                    @php 
                        $usr = $users->firstWhere('id', request('user_id')); 
                    @endphp
                    <span class="badge bg-info text-white">Пользователь: {{ $usr->name ?? 'ID: ' . request('user_id') }}</span>
                @endif
                
                @if(request('entity_type'))
                    <span class="badge bg-info text-white">Тип: {{ $entityTypes[request('entity_type')] ?? request('entity_type') }}</span>
                @endif
                
                @if(request('action'))
                    <span class="badge bg-info text-white">Действие: {{ $actions[request('action')] ?? request('action') }}</span>
                @endif
                
                @if(request('entity_id'))
                    <span class="badge bg-info text-white">ID сущности: {{ request('entity_id') }}</span>
                @endif
                
                @if(request('search'))
                    <span class="badge bg-info text-white">Поиск: {{ request('search') }}</span>
                @endif
                
                @if(request('date_from') && request('date_to'))
                    <span class="badge bg-info text-white">Период: {{ request('date_from') }} - {{ request('date_to') }}</span>
                @elseif(request('date_from'))
                    <span class="badge bg-info text-white">С: {{ request('date_from') }}</span>
                @elseif(request('date_to'))
                    <span class="badge bg-info text-white">По: {{ request('date_to') }}</span>
                @endif
            </div>
        </div>
    @endif

    <!-- Таблица логов -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table text-primary me-2"></i>
                Список действий
            </h5>
            <span class="badge bg-primary">{{ $logs->total() }} всего</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="min-width: 150px;">Дата</th>
                            <th style="min-width: 200px;">Пользователь</th>
                            <th style="min-width: 120px;">Тип</th>
                            <th style="min-width: 200px;">Сущность</th>
                            <th style="min-width: 100px;">Действие</th>
                            <th style="min-width: 120px;">IP</th>
                            <th style="width: 80px;" class="text-center">Детали</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                // Получаем информацию о сущности в зависимости от типа
                                $entityInfo = null;
                                $entityRoute = null;
                                $entityId = $log->entity_id;
                                
                                if ($log->entity_type === 'user') {
                                    $entityUser = \App\Models\User::find($entityId);
                                    if ($entityUser) {
                                        $entityInfo = $entityUser->name . ' (' . $entityUser->email . ')';
                                        // Для пользователей нет отдельного маршрута, используем фильтр
                                        $entityRoute = '#';
                                    }
                                } 
                                elseif ($log->entity_type === 'organization') {
                                    $entityOrg = \App\Models\Organization::find($entityId);
                                    if ($entityOrg) {
                                        $entityInfo = $entityOrg->name;
                                        if ($entityOrg->inn) {
                                            $entityInfo .= ' (ИНН: ' . $entityOrg->inn . ')';
                                        }
                                        $entityRoute = route('admin.organization.show', $entityId);
                                    }
                                } 
                                elseif ($log->entity_type === 'manager') {
                                    $entityManager = \App\Models\Manager::with('user')->find($entityId);
                                    if ($entityManager && $entityManager->user) {
                                        $entityInfo = $entityManager->user->name . ' (Менеджер)';
                                        $entityRoute = route('admin.managers.show', $entityId);
                                    }
                                } 
                                elseif ($log->entity_type === 'org_owner') {
                                    $entityOwner = \App\Models\OrgOwnerProfile::with('user', 'organization')->find($entityId);
                                    if ($entityOwner && $entityOwner->user) {
                                        $entityInfo = $entityOwner->user->name . ' (Владелец)';
                                        if ($entityOwner->organization) {
                                            $entityInfo .= ' - ' . $entityOwner->organization->name;
                                            $entityRoute = route('admin.organization.show', $entityOwner->organization->id);
                                        }
                                    }
                                } 
                                elseif ($log->entity_type === 'org_member') {
                                    $entityMember = \App\Models\OrgMemberProfile::with('user', 'organization')->find($entityId);
                                    if ($entityMember && $entityMember->user) {
                                        $entityInfo = $entityMember->user->name . ' (Сотрудник)';
                                        if ($entityMember->organization) {
                                            $entityInfo .= ' - ' . $entityMember->organization->name;
                                            $entityRoute = route('admin.org-members.show', [
                                                $entityMember->organization_id, 
                                                $entityId
                                            ]);
                                        }
                                    }
                                }
                                
                                $badgeClass = match($log->action) {
                                    'create' => 'success',
                                    'update' => 'primary',
                                    'delete', 'force_delete' => 'danger',
                                    'restore' => 'warning',
                                    'login', 'logout' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">#{{ $log->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px;">
                                            <i class="bi bi-clock text-primary small"></i>
                                        </div>
                                        <div>
                                            <span class="small">{{ $log->created_at->format('d.m.Y') }}</span>
                                            <small class="text-muted d-block">{{ $log->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-{{ $log->user->getRoleColor() ?? 'secondary' }} bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: {{ $log->user->getRoleColor() === 'success' ? '#198754' : ($log->user->getRoleColor() === 'danger' ? '' : '#0d6efd') }};">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="#" onclick="event.preventDefault(); document.getElementById('filter-user-{{ $log->user_id }}').submit();" class="fw-semibold text-decoration-none">
                                                    {{ $log->user->name }}
                                                </a>
                                                <small class="text-muted d-block">{{ $log->user->email }}</small>
                                            </div>
                                        </div>
                                        <form id="filter-user-{{ $log->user_id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                            <input type="hidden" name="user_id" value="{{ $log->user_id }}">
                                        </form>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; color: #6c757d;">
                                                <i class="bi bi-robot"></i>
                                            </div>
                                            <span class="text-muted">Система</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->entity_type_name }}</span>
                                </td>
                                <td>
                                    @if($entityInfo)
                                        @if($entityRoute && $entityRoute !== '#')
                                            <a href="{{ $entityRoute }}" class="text-decoration-none" target="_blank">
                                                <div class="d-flex flex-column">
                                                    <strong>{{ Str::limit($entityInfo, 40) }}</strong>
                                                    <small class="text-muted">ID: {{ $log->entity_id }}</small>
                                                </div>
                                            </a>
                                        @else
                                            <a href="#" onclick="event.preventDefault(); document.getElementById('filter-entity-{{ $log->id }}').submit();" class="text-decoration-none">
                                                <div class="d-flex flex-column">
                                                    <strong>{{ Str::limit($entityInfo, 40) }}</strong>
                                                    <small class="text-muted">ID: {{ $log->entity_id }}</small>
                                                </div>
                                            </a>
                                        @endif
                                        <form id="filter-entity-{{ $log->id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                            <input type="hidden" name="entity_type" value="{{ $log->entity_type }}">
                                            <input type="hidden" name="entity_id" value="{{ $log->entity_id }}">
                                        </form>
                                    @else
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('filter-entity-{{ $log->id }}').submit();" class="text-decoration-none">
                                            #{{ $log->entity_id }}
                                        </a>
                                        <form id="filter-entity-{{ $log->id }}" method="GET" action="{{ route('admin.logs.index') }}" class="d-none">
                                            <input type="hidden" name="entity_type" value="{{ $log->entity_type }}">
                                            <input type="hidden" name="entity_id" value="{{ $log->entity_id }}">
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $badgeClass }} px-3 py-2">{{ $log->action_name }}</span>
                                </td>
                                <td>
                                    @if($log->ip_address)
                                        <code class="bg-light p-1 rounded">{{ $log->ip_address }}</code>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-sm btn-outline-primary rounded-circle"
                                       style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                       title="Просмотр деталей">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 80px; height: 80px;">
                                        <i class="bi bi-inbox fs-1 text-secondary"></i>
                                    </div>
                                    <h5 class="text-muted mb-3">Логи не найдены</h5>
                                    <p class="text-muted mb-0">Попробуйте изменить параметры фильтрации</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            @if($logs->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 px-4 pb-4">
                    <div class="text-muted small">
                        Показано {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} 
                        из {{ $logs->total() }} записей
                    </div>
                    <div>
                        {{ $logs->withQueryString()->links() }}
                    </div>
                </div>
            @elseif($logs->total() > 0 && !$logs->hasPages())
                <div class="text-center py-3 border-top">
                    <div class="text-muted small">
                        Всего {{ $logs->total() }} записей
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Форма очистки старых логов -->
    <div class="card border-0 shadow-sm border-warning">
        <div class="card-header bg-warning text-white border-0">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Очистка логов
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.logs.clean') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-auto">
                    <label class="form-label fw-semibold">Удалить записи старше</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" name="days" class="form-control" value="30" min="1" max="365" style="width: 80px;" required>
                        <span>дней</span>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Вы уверены? Это действие нельзя отменить.')">
                        <i class="bi bi-trash me-2"></i>Очистить
                    </button>
                </div>
            </form>
            <small class="text-muted d-block mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Рекомендуется хранить логи не более 90 дней для экономии места в БД.
            </small>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ТОЧНО КАК ВО ВСЕХ СТРАНИЦАХ */

    /* Круги всегда идеальные */
    .rounded-circle {
        border-radius: 50% !important;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Карточки - скругление 1rem (16px) */
    .card {
        border-radius: 1rem;
        transition: all 0.2s;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Бейджи - сильно скругленные 30px */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        border-radius: 30px;
        padding: 0.35em 0.65em;
    }

    /* Заголовки карточек */
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem 1.25rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 600;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* Градиент для заголовка */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    }

    /* Таблицы */
    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
    }

    /* Для иконок в кружках */
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    /* Для текста в кружках с буквами */
    .rounded-circle.bg-primary, 
    .rounded-circle.bg-success, 
    .rounded-circle.bg-info,
    .rounded-circle.bg-warning,
    .rounded-circle.bg-danger {
        font-weight: 500;
    }

    /* Для форм */
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Для алертов */
    .alert-info {
        background-color: #cff4fc;
        border-color: #b6effb;
        color: #055160;
        border-radius: 0.75rem;
    }

    /* Select2 стили */
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    
    /* Стили для кнопок действий */
    .btn-sm.rounded-circle {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Стили для ссылок */
    a.text-decoration-none:hover {
        text-decoration: underline !important;
    }
    
    .table a {
        color: #0d6efd;
    }
    
    .table a:hover {
        color: #0a58ca;
    }
    
    /* Для IP адреса */
    code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация Select2 для организаций
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2-organization').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Выберите организацию',
            allowClear: true,
            width: '100%'
        });
        
        // Инициализация Select2 для пользователей с поиском
        $('.select2-user').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Поиск пользователя...',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route("users.search") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term || '',
                        organization_id: $('#organization_id').val()
                    };
                },
                processResults: function(data) {
                    let results = [{
                        id: '',
                        text: 'Все пользователи'
                    }];
                    
                    if (Array.isArray(data)) {
                        results = results.concat(data);
                    }
                    
                    return {
                        results: results
                    };
                },
                cache: true
            }
        });
    }

    // Загрузка статистики
    fetch('{{ route("admin.logs.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total;
            document.getElementById('stat-today').textContent = data.today;
            document.getElementById('stat-week').textContent = data.week;
            document.getElementById('stat-month').textContent = data.month;
        })
        .catch(error => console.error('Error loading statistics:', error));
        
    // При изменении организации - обновляем список пользователей
    $('#organization_id').on('change', function() {
        $('.select2-user').val(null).trigger('change');
        $('#filterForm').submit();
    });

    // Отправка формы при изменении пользователя
    $('.select2-user').on('change', function() {
        $('#filterForm').submit();
    });

    // Автоматическая отправка формы при изменении select-фильтров
    $('select[name="entity_type"], select[name="action"]').on('change', function() {
        $('#filterForm').submit();
    });

    // Отправка формы при изменении дат (с задержкой)
    let dateTimer;
    $('input[type="date"]').on('change', function() {
        clearTimeout(dateTimer);
        dateTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
    
    // Отправка формы при изменении ID сущности (с задержкой)
    let entityIdTimer;
    $('input[name="entity_id"]').on('input', function() {
        clearTimeout(entityIdTimer);
        entityIdTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
    
    // Отправка формы при поиске по тексту (с задержкой)
    let searchTimer;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
});
</script>
@endpush
