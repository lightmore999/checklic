@extends('layouts.app')

@section('title', 'Управление отчетами')
@section('page-icon', 'bi-file-text')

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
                                <i class="bi bi-file-text"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Управление отчетами</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-primary px-3 py-2">
                                        <i class="bi bi-file-text me-1"></i>Всего: {{ $limits->total() ?? 0 }}
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'manager.dashboard') }}" 
                                               class="text-white opacity-75">
                                                Панель {{ Auth::user()->isAdmin() ? 'админа' : 'менеджера' }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Отчеты
                                        </li>
                                    </ol>
                                </nav>
                            </div>
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
            </h5>
        </div>
        <div class="card-body pt-3">
            <form method="GET" class="mb-0" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Организация</label>
                        <select name="organization_id" class="form-select select2-organization">
                            <option value="">Все организации</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Пользователь</label>
                        <select name="user_id" class="form-select select2-user" data-placeholder="Поиск пользователя...">
                            <option value="">Все пользователи</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }}) - {{ $user->getRoleDisplayName() ?? 'Нет роли' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Можно ввести имя или email для поиска</small>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Тип отчета</label>
                        <select name="report_type_id" class="form-select">
                            <option value="">Все типы</option>
                            @foreach($reportTypes as $type)
                                <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Дата</label>
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Статус</label>
                        <select name="status" class="form-select">
                            <option value="">Все</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                            <option value="exhausted" {{ request('status') == 'exhausted' ? 'selected' : '' }}>Исчерпанные</option>
                        </select>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Применить фильтры
                            </button>
                            <a href="{{ route('limits.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Активные фильтры -->
            @if(request()->anyFilled(['organization_id', 'user_id', 'report_type_id', 'date', 'status']))
                <div class="alert alert-info py-2 mt-3">
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
                        @if(request('report_type_id'))
                            @php 
                                $type = $reportTypes->firstWhere('id', request('report_type_id')); 
                            @endphp
                            <span class="badge bg-info text-white">Тип отчета: {{ $type->name ?? 'ID: ' . request('report_type_id') }}</span>
                        @endif
                        @if(request('date'))
                            <span class="badge bg-info text-white">Дата: {{ \Carbon\Carbon::parse(request('date'))->format('d.m.Y') }}</span>
                        @endif
                        @if(request('status'))
                            <span class="badge bg-info text-white">Статус: {{ request('status') == 'active' ? 'Активные' : 'Исчерпанные' }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Таблица лимитов -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="min-width: 200px;">Пользователь</th>
                            <th style="min-width: 180px;">Организация</th>
                            <th style="min-width: 150px;">Подписка</th>
                            <th style="min-width: 150px;">Создатель</th>
                            <th style="min-width: 150px;">Тип отчета</th>
                            <th style="width: 80px;">Кол-во</th>
                            <th style="width: 80px;">Исп.</th>
                            <th style="width: 80px;">Дост.</th>
                            <th style="width: 80px;">Делег.</th>
                            <th style="width: 100px;">Дата</th>
                            <th style="width: 100px;">Статус</th>
                            <th style="width: 120px;">Создан</th>
                            <th style="width: 100px;" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($limits as $limit)
                            @php
                                // Получаем пользователя через подписку
                                $user = $limit->subscription->user ?? null;
                                $subscription = $limit->subscription;
                                
                                // Определяем организацию пользователя
                                $userOrg = null;
                                if ($user) {
                                    if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                        $userOrg = $user->orgOwnerProfile->organization;
                                    } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                        $userOrg = $user->orgMemberProfile->organization;
                                    }
                                }
                                
                                // Статус подписки
                                $subscriptionStatus = $subscription ? $subscription->status : null;
                                $subscriptionStatusClass = $subscriptionStatus === 'active' ? 'success' : 
                                                          ($subscriptionStatus === 'expired' ? 'danger' : 
                                                          ($subscriptionStatus === 'pending' ? 'warning' : 'secondary'));
                                $subscriptionStatusText = $subscription ? $subscription->getStatusTextAttribute() : 'Нет подписки';
                                
                                // Определяем маршрут к профилю пользователя
                                $userProfileRoute = null;
                                if ($user) {
                                    if ($user->isOrgOwner() && $user->orgOwnerProfile) {
                                        $userProfileRoute = route('admin.organization.show', $user->orgOwnerProfile->organization_id);
                                    } elseif ($user->isOrgMember() && $user->orgMemberProfile) {
                                        $userProfileRoute = route('admin.org-members.show', [
                                            $user->orgMemberProfile->organization_id,
                                            $user->orgMemberProfile->id
                                        ]);
                                    } elseif ($user->isManager()) {
                                        $userProfileRoute = route('admin.managers.show', $user->id);
                                    }
                                }
                                
                                // Определяем маршрут к профилю создателя
                                $creator = $limit->creator;
                                $creatorProfileRoute = null;
                                if ($creator) {
                                    if ($creator->isOrgOwner() && $creator->orgOwnerProfile) {
                                        $creatorProfileRoute = route('admin.organization.show', $creator->orgOwnerProfile->organization_id);
                                    } elseif ($creator->isOrgMember() && $creator->orgMemberProfile) {
                                        $creatorProfileRoute = route('admin.org-members.show', [
                                            $creator->orgMemberProfile->organization_id,
                                            $creator->orgMemberProfile->id
                                        ]);
                                    } elseif ($creator->isManager()) {
                                        $creatorProfileRoute = route('admin.managers.show', $creator->id);
                                    } elseif ($creator->isAdmin()) {
                                        $creatorProfileRoute = route('admin.dashboard');
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">#{{ $limit->id }}</span>
                                </td>
                                <td>
                                    @if($user)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-{{ $user->getRoleColor() ?? 'secondary' }} bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 0.9rem; color: {{ $user->getRoleColor() === 'success' ? '#198754' : ($user->getRoleColor() === 'danger' ? ' #fd7e14' : '#0d6efd') }};">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                @if($userProfileRoute)
                                                    <a href="{{ $userProfileRoute }}" class="fw-semibold text-decoration-none">
                                                        {{ $user->name }}
                                                    </a>
                                                @else
                                                    <span class="fw-semibold">{{ $user->name }}</span>
                                                @endif
                                                <small class="text-muted d-block">{{ $user->email }}</small>
                                                <span class="badge bg-{{ $user->getRoleColor() ?? 'secondary' }}" style="font-size: 0.65rem;">
                                                    {{ $user->getRoleDisplayName() ?? 'Нет роли' }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic small">Пользователь удален</span>
                                    @endif
                                </td>
                                <td>
                                    @if($userOrg)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; font-size: 0.8rem; color: #198754;">
                                                {{ strtoupper(substr($userOrg->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.organization.show', $userOrg->id) }}" class="text-decoration-none">
                                                    {{ Str::limit($userOrg->name, 20) }}
                                                </a>
                                                @if($user && $user->isOrgOwner())
                                                    <span class="badge bg-primary ms-1">Руководитель</span>
                                                @elseif($user && $user->isOrgMember())
                                                    <span class="badge bg-info ms-1">Сотрудник</span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($user && $user->isManager())
                                        <span class="badge bg-secondary">Менеджер</span>
                                    @else
                                        <span class="text-muted fst-italic small">Не указана</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subscription)
                                        <div>
                                            <span class="badge bg-{{ $subscriptionStatusClass }} mb-1">
                                                {{ $subscriptionStatusText }}
                                            </span>
                                            @if($subscription->ends_at)
                                                <div><small class="text-muted">до {{ $subscription->ends_at->format('d.m.Y') }}</small></div>
                                                @if($subscription->isExpiringSoon())
                                                    <span class="badge bg-warning mt-1">скоро</span>
                                                @endif
                                            @else
                                                <div><small class="text-muted">бессрочно</small></div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic small">Нет подписки</span>
                                    @endif
                                </td>
                                <td>
                                    @if($creator)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px; font-size: 0.8rem; color: #6c757d;">
                                                {{ strtoupper(substr($creator->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                @if($creatorProfileRoute)
                                                    <a href="{{ $creatorProfileRoute }}" class="text-decoration-none">
                                                        {{ Str::limit($creator->name, 15) }}
                                                    </a>
                                                @else
                                                    <span>{{ Str::limit($creator->name, 15) }}</span>
                                                @endif
                                                <small class="text-muted d-block">{{ $creator->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">
                                            <i class="bi bi-robot"></i> Система
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px; font-size: 0.8rem; color: #0dcaf0;">
                                            {{ strtoupper(substr($limit->reportType->name ?? 'О', 0, 1)) }}
                                        </div>
                                        <strong>{{ $limit->reportType->name ?? 'Не указан' }}</strong>
                                    </div>
                                    @if($limit->reportType && $limit->reportType->only_api)
                                        <span class="badge bg-warning mt-1">только API</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $limit->quantity }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                        {{ $limit->used_quantity }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $limit->getAvailableQuantity() > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $limit->getAvailableQuantity() > 0 ? 'success' : 'danger' }} px-3 py-2">
                                        {{ $limit->getAvailableQuantity() }}
                                    </span>
                                </td>
                                <td>
                                    @if($limit->delegatedVersions && $limit->delegatedVersions->count() > 0)
                                        <div class="delegated-info">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" 
                                                    data-bs-target="#delegated-{{ $limit->id }}">
                                                <i class="bi bi-share"></i> {{ $limit->delegatedVersions->count() }}
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                             style="width: 24px; height: 24px;">
                                            <i class="bi bi-calendar text-primary small"></i>
                                        </div>
                                        <span class="small">{{ $limit->date_created ? $limit->date_created->format('d.m.Y') : '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($limit->isExhausted())
                                        <span class="badge bg-danger">Исчерпан</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                             style="width: 24px; height: 24px;">
                                            <i class="bi bi-clock text-primary small"></i>
                                        </div>
                                        <div>
                                            <span class="small">{{ $limit->created_at ? $limit->created_at->format('d.m.Y') : '' }}</span>
                                            <small class="text-muted d-block">{{ $limit->created_at ? $limit->created_at->format('H:i') : '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(auth()->user()->isAdmin())
                                            <form action="{{ route('limits.destroy', $limit) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этот отчет?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"
                                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                        title="Удалить">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($user && $userProfileRoute)
                                            <a href="{{ $userProfileRoute }}" class="btn btn-sm btn-outline-info rounded-circle"
                                               style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                               title="Профиль пользователя">
                                                <i class="bi bi-person"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Детали делегированных лимитов (скрытый блок) -->
                            @if($limit->delegatedVersions && $limit->delegatedVersions->count() > 0)
                                <tr class="collapse" id="delegated-{{ $limit->id }}">
                                    <td colspan="14" class="p-0">
                                        <div class="p-3 bg-light border-top border-bottom">
                                            <h6 class="mb-3">
                                                <i class="bi bi-share me-2"></i>
                                                Делегированные отчеты
                                                <span class="badge bg-info ms-2">{{ $limit->delegatedVersions->count() }}</span>
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0 bg-white">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Пользователь</th>
                                                            <th>Организация</th>
                                                            <th class="text-center">Количество</th>
                                                            <th class="text-center">Использовано</th>
                                                            <th class="text-center">Доступно</th>
                                                            <th>Статус</th>
                                                            <th>Дата создания</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($limit->delegatedVersions as $delegated)
                                                            @php
                                                                $delUser = $delegated->user;
                                                                $delUserOrg = null;
                                                                if ($delUser) {
                                                                    if ($delUser->isOrgOwner() && $delUser->orgOwnerProfile) {
                                                                        $delUserOrg = $delUser->orgOwnerProfile->organization;
                                                                    } elseif ($delUser->isOrgMember() && $delUser->orgMemberProfile) {
                                                                        $delUserOrg = $delUser->orgMemberProfile->organization;
                                                                    }
                                                                }
                                                                
                                                                // Определяем маршрут к профилю делегированного пользователя
                                                                $delUserProfileRoute = null;
                                                                if ($delUser) {
                                                                    if ($delUser->isOrgOwner() && $delUser->orgOwnerProfile) {
                                                                        $delUserProfileRoute = route('admin.organization.show', $delUser->orgOwnerProfile->organization_id);
                                                                    } elseif ($delUser->isOrgMember() && $delUser->orgMemberProfile) {
                                                                        $delUserProfileRoute = route('admin.org-members.show', [
                                                                            $delUser->orgMemberProfile->organization_id,
                                                                            $delUser->orgMemberProfile->id
                                                                        ]);
                                                                    } elseif ($delUser->isManager()) {
                                                                        $delUserProfileRoute = route('admin.managers.show', $delUser->id);
                                                                    }
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    @if($delUser)
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                                                 style="width: 28px; height: 28px; font-size: 0.8rem; color: #0dcaf0;">
                                                                                {{ strtoupper(substr($delUser->name, 0, 1)) }}
                                                                            </div>
                                                                            <div>
                                                                                @if($delUserProfileRoute)
                                                                                    <a href="{{ $delUserProfileRoute }}" class="text-decoration-none">
                                                                                        {{ Str::limit($delUser->name, 15) }}
                                                                                    </a>
                                                                                @else
                                                                                    <span>{{ Str::limit($delUser->name, 15) }}</span>
                                                                                @endif
                                                                                <small class="text-muted d-block">{{ $delUser->email }}</small>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-muted fst-italic small">Не указан</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($delUserOrg)
                                                                        <a href="{{ route('admin.organization.show', $delUserOrg->id) }}" class="text-decoration-none">
                                                                            {{ Str::limit($delUserOrg->name, 20) }}
                                                                        </a>
                                                                    @elseif($delUser && $delUser->isManager())
                                                                        <span class="badge bg-secondary">Менеджер</span>
                                                                    @else
                                                                        <span class="text-muted fst-italic small">Не указана</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center fw-bold">{{ $delegated->quantity }}</td>
                                                                <td class="text-center">
                                                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                                                        {{ $delegated->used_quantity }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge bg-{{ $delegated->getAvailableQuantity() > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $delegated->getAvailableQuantity() > 0 ? 'success' : 'danger' }} px-3 py-2">
                                                                        {{ $delegated->getAvailableQuantity() }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if($delegated->isExhausted())
                                                                        <span class="badge bg-danger">Исчерпан</span>
                                                                    @elseif($delegated->isActive())
                                                                        <span class="badge bg-success">Активен</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">Неактивен</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $delegated->created_at ? $delegated->created_at->format('d.m.Y H:i') : '' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-5">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 80px; height: 80px;">
                                        <i class="bi bi-file-text fs-1 text-secondary"></i>
                                    </div>
                                    <h5 class="text-muted mb-3">Отчеты не найдены</h5>
                                    @if(request()->anyFilled(['organization_id', 'user_id', 'report_type_id', 'date', 'status']))
                                        <p class="text-muted">Попробуйте изменить параметры фильтрации</p>
                                        <a href="{{ route('limits.index') }}" class="btn btn-secondary mt-2">
                                            <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить фильтры
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Информация о пагинации -->
            @if($limits->total() > 0)
                <div class="d-flex justify-content-between align-items-center mt-4 px-4 pb-4">
                    <div class="text-muted small">
                        Показано {{ $limits->firstItem() ?? 0 }} - {{ $limits->lastItem() ?? 0 }} 
                        из {{ $limits->total() }} отчетов
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $limits->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальные окна для делегирования (закомментированы, оставлены как есть) -->
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ru.js"></script>
<script>
    $(document).ready(function() {
        // Инициализация Select2 для пользователей в фильтрах
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
                        organization_id: $('select[name="organization_id"]').val()
                    };
                },
                processResults: function(data) {
                    let results = [{
                        id: '',
                        text: 'Все пользователи'
                    }];
                    
                    results = results.concat(data);
                    
                    return {
                        results: results
                    };
                },
                cache: true
            }
        });

        // Инициализация Select2 для организаций
        $('.select2-organization').select2({
            theme: 'default',
            language: 'ru',
            placeholder: 'Выберите организацию',
            allowClear: true,
            width: '100%'
        });

        // При изменении организации - обновляем список пользователей
        $('select[name="organization_id"]').on('change', function() {
            $('.select2-user').val(null).trigger('change');
            $('#filterForm').submit();
        });

        // Автоматическая отправка формы при изменении полей
        $('select[name="report_type_id"], select[name="status"], input[name="date"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Отправка формы при изменении пользователя
        $('.select2-user').on('change', function() {
            $('#filterForm').submit();
        });

        // Инициализация Bootstrap Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Инициализация Bootstrap Collapse для кнопок делегирования
        var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
        collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, {
                toggle: false
            });
        });
    });
</script>
@endpush
