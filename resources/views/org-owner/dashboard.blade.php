@extends('layouts.app')

@section('title', 'Панель владельца организации - ' . $organization->name)
@section('page-icon', 'bi-buildings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-buildings text-success"></i> {{ $organization->name }}
    </h2>
    <div>
        <span class="badge bg-success fs-6">Владелец организации</span>
    </div>
</div>

<!-- Информация о владельце и организации -->
<div class="row mb-4">
    <!-- Профиль владельца -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
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
                    <span class="badge bg-success">Владелец</span>
                    @if(Auth::user()->is_active)
                        <span class="badge bg-success">Активен</span>
                    @else
                        <span class="badge bg-danger">Неактивен</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Информация об организации -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-building text-success me-2"></i>
                    Организация: <strong class="ms-1">{{ $organization->name }}</strong>
                </h6>
                @if($organization->inn)
                    <span class="badge bg-info">ИНН: {{ $organization->inn }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Статус:</strong>
                            @switch($organization->status)
                                @case('active')
                                    <span class="badge bg-success ms-2">Активна</span>
                                    @break
                                @case('inactive')
                                    <span class="badge bg-danger ms-2">Неактивна</span>
                                    @break
                                @case('pending')
                                    <span class="badge bg-warning ms-2">Ожидает</span>
                                    @break
                                @case('suspended')
                                    <span class="badge bg-warning ms-2">Приостановлена</span>
                                    @break
                                @case('expired')
                                    <span class="badge bg-danger ms-2">Истекла</span>
                                    @break
                            @endswitch
                        </div>
                        
                        <div class="mb-3">
                            <strong>Сотрудников:</strong>
                            <span class="ms-2">{{ $membersCount }} / {{ $activeMembersCount }} активных</span>
                            @if($organization->max_employees)
                                <span class="badge bg-info ms-2" title="Максимальное количество сотрудников">
                                    лимит: {{ $organization->max_employees }}
                                </span>
                                <div class="mt-2" style="max-width: 200px;">
                                    <div class="progress" style="height: 6px;">
                                        @php
                                            $employeePercentage = $organization->max_employees > 0 
                                                ? min(100, round(($membersCount / $organization->max_employees) * 100)) 
                                                : 0;
                                        @endphp
                                        <div class="progress-bar bg-{{ $employeePercentage >= 90 ? 'danger' : ($employeePercentage >= 70 ? 'warning' : 'success') }}" 
                                             style="width: {{ $employeePercentage }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $employeePercentage }}% от лимита</small>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <strong>Создана:</strong>
                            <span class="ms-2">{{ $organization->created_at->format('d.m.Y') }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>ИНН:</strong>
                            <span class="ms-2">{{ $organization->inn ?? 'Не указан' }}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Подписка до:</strong>
                            @if($organization->subscription_ends_at)
                                <span class="ms-2 {{ $organization->isExpired() ? 'text-danger' : 'text-success' }}">
                                    {{ $organization->subscription_ends_at->format('d.m.Y') }}
                                </span>
                            @else
                                <span class="ms-2 text-muted">Бессрочно</span>
                            @endif
                        </div>
                        
                        @if($organization->subscription_ends_at && !$organization->isExpired())
                        <div class="mb-3">
                            <strong>Осталось дней:</strong>
                            <span class="ms-2 text-primary fw-bold">
                                {{ $organization->getRemainingSubscriptionDays() }}
                            </span>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <strong>Макс. сотрудников:</strong>
                            <span class="ms-2">
                                @if($organization->max_employees)
                                    {{ $organization->max_employees }} чел.
                                @else
                                    <span class="text-muted">Не ограничено</span>
                                @endif
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Свободных мест:</strong>
                            <span class="ms-2">
                                @if($organization->max_employees)
                                    @php
                                        $availableSlots = max(0, $organization->max_employees - $membersCount);
                                    @endphp
                                    <span class="badge bg-{{ $availableSlots > 0 ? 'success' : 'danger' }}">
                                        {{ $availableSlots }} из {{ $organization->max_employees }}
                                    </span>
                                @else
                                    <span class="badge bg-success">∞</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                
                @if($organization->isSubscriptionExpiringSoon())
                    <div class="alert alert-warning mt-2 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Подписка истекает через {{ $organization->getRemainingSubscriptionDays() }} дней
                    </div>
                @endif
                
                @if($organization->isExpired())
                    <div class="alert alert-danger mt-2 mb-0">
                        <i class="bi bi-x-circle me-2"></i>
                        Подписка истекла! Обратитесь к менеджеру
                    </div>
                @endif
                
                @if($organization->max_employees && $membersCount >= $organization->max_employees)
                    <div class="alert alert-warning mt-2 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Достигнут лимит сотрудников ({{ $membersCount }}/{{ $organization->max_employees }}). 
                        Для добавления новых сотрудников обратитесь к менеджеру.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Информация о менеджере -->
@if($manager)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2">
                <h6 class="mb-0">
                    <i class="bi bi-person-workspace text-primary me-1"></i>
                    Ваш менеджер: <strong>{{ $manager->name }}</strong>
                </h6>
            </div>
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-envelope text-secondary"></i>
                                <a href="mailto:{{ $manager->email }}" class="text-decoration-none ms-1">
                                    {{ $manager->email }}
                                </a>
                            </div>
                            
                            @php
                                $managerPhone = $manager->managerProfile->phone ?? $manager->phone ?? null;
                            @endphp
                            @if($managerPhone)
                            <div>
                                <i class="bi bi-telephone text-secondary ms-3"></i>
                                <a href="tel:{{ $managerPhone }}" class="text-decoration-none ms-1">
                                    {{ $managerPhone }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    @php
                        $managerTelegram = $manager->managerProfile->telegram ?? $manager->telegram ?? null;
                    @endphp
                    @if($managerTelegram)
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="https://t.me/{{ $managerTelegram }}" 
                           class="badge bg-info text-decoration-none px-3 py-2" 
                           target="_blank">
                            <i class="bi bi-telegram"></i> Telegram
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info py-2 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            Менеджер не назначен. 
            <a href="mailto:support@example.com" class="alert-link">Написать в поддержку</a>
        </div>
    </div>
</div>
@endif

<!-- Лимиты владельца -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-speedometer text-info me-2"></i>
            Ваши отчеты
            @if($ownerLimits->count() > 0)
                <span class="badge bg-info ms-2">{{ $ownerLimits->count() }}</span>
            @endif
        </h6>
        <div class="d-flex gap-2">
            @if($availableEmployees->count() > 0 && $ownerLimits->where('quantity', '>', 0)->count() > 0)
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#delegateModal">
                    <i class="bi bi-share"></i> Делегировать
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($ownerLimits->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Тип отчета</th>
                            <th>Дата действия</th>
                            <th>Выделено</th>
                            <th>Использовано</th>
                            <th>Делегировано</th>
                            <th>Доступно</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ownerLimits as $limit)
                        @php
                            $usedAmount = $limit->used_quantity ?? 0;
                            $delegatedAmount = $delegatedLimits->where('limit_id', $limit->id)->sum('quantity');
                            $totalAllocated = $limit->quantity + $usedAmount + $delegatedAmount;
                            $availableAmount = $limit->quantity;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $limit->reportType->name ?? 'Не указан' }}</strong>
                            </td>
                            <td>{{ $limit->date_created->format('d.m.Y') }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $totalAllocated }} шт.</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $usedAmount }} шт.</span>
                            </td>
                            <td>
                                @if($delegatedAmount > 0)
                                    <span class="badge bg-warning">{{ $delegatedAmount }} шт.</span>
                                @else
                                    <span class="text-muted">0 шт.</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $availableAmount > 0 ? 'success' : 'danger' }}">
                                    {{ $availableAmount }} шт.
                                </span>
                            </td>
                            <td>
                                @if($availableAmount <= 0)
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
        @else
            <div class="text-center py-4">
                <i class="bi bi-speedometer fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Отчеты не назначены</h5>
                <p class="text-muted mb-3">Обратитесь к менеджеру для получения отчетов</p>
                @if($manager)
                <a href="mailto:{{ $manager->email }}" class="btn btn-primary">
                    <i class="bi bi-envelope"></i> Связаться с менеджером
                </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Делегированные лимиты -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-share text-warning me-2"></i>
            Делегированные отчеты
            @if($delegatedLimits->count() > 0)
                <span class="badge bg-warning ms-2">{{ $delegatedLimits->count() }}</span>
            @endif
        </h6>
        @if($delegatedLimits->count() > 0)
            <small class="text-muted">
                Всего делегировано: {{ $delegatedLimits->sum('quantity') }} шт.
            </small>
        @endif
    </div>
    <div class="card-body">
        @if($delegatedLimits->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Сотрудник</th>
                            <th>Тип отчета</th>
                            <th>Дата отчета</th>
                            <th>Делегировано/Использовано</th>
                            <th>Дата делегирования</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delegatedLimits as $delegated)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px; color: white; font-size: 0.8rem;">
                                        {{ strtoupper(substr($delegated->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div>{{ $delegated->user->name }}</div>
                                        <small class="text-muted">{{ $delegated->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $delegated->limit->reportType->name ?? 'Не указан' }}</strong>
                            </td>
                            <td>{{ $delegated->limit->date_created->format('d.m.Y') }}</td>
                            <td>
                                <div>
                                    <span class="badge bg-warning mb-1">{{ $delegated->quantity }} шт.</span>
                                    @if($delegated->used_quantity > 0)
                                        <br>
                                        <small class="text-muted">использовано: {{ $delegated->used_quantity }} шт.</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $delegated->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($delegated->is_active)
                                    @if($delegated->isExhausted())
                                        <span class="badge bg-danger">Исчерпан</span>
                                    @else
                                        <span class="badge bg-success">Активен</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('delegated-limits.destroy', $delegated) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btnы-danger" 
                                            onclick="return confirm('Возвратить лимит? Лимит вернется вам.')"
                                            title="Возвратить лимит">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-share fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Делегированных отчетов нет</h5>
                <p class="text-muted mb-3">
                    @if($ownerLimits->where('quantity', '>', 0)->count() == 0)
                        У вас нет доступных отчетов для делегирования
                    @elseif($availableEmployees->count() == 0)
                        У вас нет активных сотрудников
                    @else
                        Начните делегировать отчеты своим сотрудникам
                    @endif
                </p>
                @if($ownerLimits->where('quantity', '>', 0)->count() > 0 && $availableEmployees->count() > 0)
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#delegateModal">
                        <i class="bi bi-share"></i> Делегировать отчет
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Сотрудники организации -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-people text-primary me-2"></i>
            Сотрудники организации
            @if($membersCount > 0)
                <span class="badge bg-primary ms-2">{{ $membersCount }}</span>
            @endif
        </h6>
        <div class="d-flex gap-2 align-items-center">
            @if($organization->max_employees)
                <span class="badge bg-info">
                    Лимит: {{ $organization->max_employees }} чел.
                </span>
                @if($organization->max_employees > $membersCount)
                    <span class="badge bg-success">
                        Свободно: {{ $organization->max_employees - $membersCount }}
                    </span>
                @else
                    <span class="badge bg-danger">
                        Лимит исчерпан
                    </span>
                @endif
            @endif
            
            @if($organization && $organization->owner && $organization->owner->user_id == Auth::id())
                @if(!$organization->max_employees || $organization->max_employees > $membersCount)
                    <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </a>
                @else
                    <button class="btn btn-secondary btn-sm" disabled 
                            title="Достигнут лимит сотрудников ({{ $membersCount }}/{{ $organization->max_employees }})">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </button>
                @endif
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($membersCount > 0)
            <div class="row">
                @foreach($members as $member)
                @php
                    $memberDelegated = $delegatedLimits->where('user_id', $member->user->id);
                    $memberTotalDelegated = $memberDelegated->sum('quantity');
                    $memberTotalUsed = $memberDelegated->sum('used_quantity');
                    $memberTotalAvailable = $memberTotalDelegated - $memberTotalUsed;
                    $hasDelegated = $memberTotalDelegated > 0;
                @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px; color: white; font-size: 1rem;">
                                    {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold">{{ $member->user->name }}</h6>
                                    <small class="text-muted d-block">{{ $member->user->email }}</small>
                                    <div class="d-flex gap-1 mt-1">
                                        @if($member->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                        @if($hasDelegated)
                                            <span class="badge bg-warning">
                                                <i class="bi bi-share"></i> {{ $memberDelegated->count() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($hasDelegated)
                                <div class="mb-3 border-top pt-2">
                                    <small class="text-muted d-block mb-2">Статистика по отчетам:</small>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Делегировано:</small>
                                        <span class="badge bg-warning bg-opacity-25 text-dark">{{ $memberTotalDelegated }} шт.</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Использовано:</small>
                                        <span class="badge bg-info bg-opacity-25 text-dark">{{ $memberTotalUsed }} шт.</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small>Доступно:</small>
                                        <span class="badge bg-{{ $memberTotalAvailable > 0 ? 'success' : 'danger' }} bg-opacity-25 text-dark">
                                            {{ $memberTotalAvailable }} шт.
                                        </span>
                                    </div>
                                    
                                    @if($memberDelegated->count() > 0)
                                        <small class="text-muted d-block mb-1">Виды отчетов:</small>
                                        <div class="mt-1">
                                            @foreach($memberDelegated->take(2) as $delegated)
                                                @php
                                                    $delegatedAvailable = $delegated->quantity - $delegated->used_quantity;
                                                    $percentage = $delegated->quantity > 0 ? round(($delegatedAvailable / $delegated->quantity) * 100) : 0;
                                                @endphp
                                                <div class="mb-2">
                                                    <small class="d-block text-truncate" title="{{ $delegated->limit->reportType->name ?? 'Отчет' }}">
                                                        {{ $delegated->limit->reportType->name ?? 'Отчет' }}
                                                    </small>
                                                    <div class="progress" style="height: 4px;">
                                                        <div class="progress-bar bg-{{ $percentage > 20 ? 'success' : ($percentage > 0 ? 'warning' : 'danger') }}" 
                                                             style="width: {{ $percentage }}%">
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <small class="text-muted">{{ $delegatedAvailable }} шт.</small>
                                                        <small class="text-muted">из {{ $delegated->quantity }} шт.</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($memberDelegated->count() > 2)
                                                <div class="text-center">
                                                    <small class="text-muted">
                                                        + еще {{ $memberDelegated->count() - 2 }} видов отчетов
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="border-top pt-3 text-center">
                                    <i class="bi bi-share fs-4 text-muted mb-2 d-block"></i>
                                    <small class="text-muted">Нет делегированных отчетов</small>
                                </div>
                            @endif
                            
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('owner.org-members.show', [$organization->id, $member->id]) }}" 
                                           class="btn btn-sm btn-info" title="Просмотр">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('owner.org-members.edit', [$organization->id, $member->id]) }}" 
                                           class="btn btn-sm btn-secondary" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                    @if($organization && $organization->owner && $organization->owner->user_id == Auth::id())
                                        <button type="button" class="btn btn-sm btn-warning delegate-btn"
                                                data-employee-id="{{ $member->user->id }}"
                                                data-employee-name="{{ $member->user->name }}"
                                                title="Делегировать лимит"
                                                {{ !$organization->canAddMoreEmployees() && $ownerLimits->where('quantity', '>', 0)->count() == 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-share"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($members->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $members->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">Сотрудников пока нет</h5>
                <p class="text-muted mb-4">
                    Добавьте первого сотрудника в вашу организацию
                    @if($organization->max_employees)
                        <br><small class="text-info">Лимит сотрудников: {{ $organization->max_employees }} чел.</small>
                    @endif
                </p>
                @if($organization && $organization->owner && $organization->owner->user_id == Auth::id())
                    @if(!$organization->max_employees || $organization->max_employees > 0)
                        <a href="{{ route('owner.org-members.create', $organization->id) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Добавить сотрудника
                        </a>
                    @else
                        <button class="btn btn-secondary disabled">
                            <i class="bi bi-person-plus"></i> Лимит сотрудников исчерпан
                        </button>
                    @endif
                @else
                    <button class="btn btn-primary disabled">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </button>
                    <p class="text-muted mt-2">Только владелец может добавлять сотрудников</p>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно делегирования -->
<div class="modal fade" id="delegateModal" tabindex="-1" aria-labelledby="delegateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('delegated-limits.store') }}" method="POST" id="delegateForm">
                @csrf
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="delegateModalLabel">
                        <i class="bi bi-share"></i> Делегирование отчета
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="limit_id" class="form-label">
                                    <i class="bi bi-tachometer"></i> Ваш отчет *
                                </label>
                                <select name="limit_id" id="limit_id" class="form-select" required>
                                    <option value="">Выберите отчет</option>
                                    @foreach($ownerLimits as $limit)
                                        @if($limit->getAvailableQuantity() > 0)
                                            <option value="{{ $limit->id }}" 
                                                    data-available="{{ $limit->getAvailableQuantity() }}"
                                                    data-name="{{ $limit->reportType->name ?? 'Без типа' }}"
                                                    data-date="{{ $limit->date_created->format('d.m.Y') }}">
                                                {{ $limit->reportType->name ?? 'Без типа' }} 
                                                ({{ $limit->date_created->format('d.m.Y') }})
                                                - доступно {{ $limit->getAvailableQuantity() }} шт.
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="card border-info mb-3" id="limitInfo" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="mb-2" id="limitName"></h6>
                                    <div class="small">
                                        <div>Дата действия: <span id="limitDate"></span></div>
                                        <div>Доступно: <span class="badge bg-success" id="limitAvailable"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="bi bi-person"></i> Сотрудник *
                                </label>
                                <select name="user_id" id="user_id" class="form-select" required>
                                    <option value="">Выберите сотрудника</option>
                                    @foreach($availableEmployees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }} ({{ $employee->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="card border-primary mb-3" id="employeeInfo" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="mb-2" id="employeeName"></h6>
                                    <div class="small" id="employeeDelegated">
                                        <i class="bi bi-info-circle"></i> Загрузка информации...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity" class="form-label">
                            <i class="bi bi-123"></i> Количество для делегирования *
                        </label>
                        <div class="input-group mb-2">
                            <input type="number" name="quantity" id="quantity" 
                                   class="form-control" 
                                   min="1" 
                                   value="1"
                                   required>
                            <span class="input-group-text">шт.</span>
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-primary me-2" onclick="setDelegateAmount(5)">
                                +5
                            </button>
                            <button type="button" class="btn btn-sm btn-primary me-2" onclick="setDelegateAmount(10)">
                                +10
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setMaxDelegateAmount()">
                                Максимум
                            </button>
                        </div>
                        <small class="text-muted">
                            Максимально можно делегировать: <span id="maxAmount">0</span> шт.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-share"></i> Делегировать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        
        let employees = {
            @foreach($availableEmployees as $employee)
                {{ $employee->id }}: {
                    name: '{{ $employee->name }}',
                    delegated: {{ $delegatedLimits->where('user_id', $employee->id)->sum('quantity') }},
                    types: {{ $delegatedLimits->where('user_id', $employee->id)->count() }}
                },
            @endforeach
        };
        
        // Кнопки делегирования в карточках сотрудников
        $('.delegate-btn[data-employee-id]').on('click', function() {
            const employeeId = $(this).data('employee-id');
            $('#user_id').val(employeeId).trigger('change');
            $('#delegateModal').modal('show');
        });
        
        // Обновление информации при выборе лимита
        $('#limit_id').on('change', function() {
            const limitId = $(this).val();
            const limit = limits[limitId];
            
            if (limit && limit.available > 0) {
                $('#limitInfo').show();
                $('#limitName').text(limit.name);
                $('#limitDate').text(limit.date);
                $('#limitAvailable').text(limit.available + ' шт.');
                $('#maxAmount').text(limit.available);
                $('#quantity').attr('max', limit.available);
                
                const current = parseInt($('#quantity').val()) || 1;
                if (current > limit.available) {
                    $('#quantity').val(Math.min(1, limit.available));
                }
            } else {
                $('#limitInfo').hide();
                $('#maxAmount').text('0');
                $('#quantity').attr('max', 0);
            }
        });
        
        // Обновление информации при выборе сотрудника
        $('#user_id').on('change', function() {
            const employeeId = $(this).val();
            const employee = employees[employeeId];
            
            if (employee) {
                $('#employeeInfo').show();
                $('#employeeName').text(employee.name);
                
                let delegatedInfo = '';
                if (employee.delegated > 0) {
                    delegatedInfo = `Уже делегировано: <span class="badge bg-warning">${employee.delegated} шт.</span> (${employee.types} видов)`;
                } else {
                    delegatedInfo = '<span class="text-muted">Нет делегированных лимитов</span>';
                }
                
                $('#employeeDelegated').html(delegatedInfo);
            } else {
                $('#employeeInfo').hide();
            }
        });
        
        // Функции для управления количеством
        window.setDelegateAmount = function(amount) {
            const current = parseInt($('#quantity').val()) || 1;
            const max = parseInt($('#quantity').attr('max')) || 0;
            let newValue = current + amount;
            
            if (newValue < 1) newValue = 1;
            if (newValue > max) newValue = max;
            
            $('#quantity').val(newValue);
        };
        
        window.setMaxDelegateAmount = function() {
            const max = parseInt($('#quantity').attr('max')) || 0;
            if (max > 0) {
                $('#quantity').val(max);
            }
        };
        
        // Валидация формы делегирования
        $('#delegateForm').on('submit', function(e) {
            const limitId = $('#limit_id').val();
            const userId = $('#user_id').val();
            const quantity = parseInt($('#quantity').val()) || 0;
            const max = parseInt($('#quantity').attr('max')) || 0;
            
            if (!limitId || !userId) {
                e.preventDefault();
                alert('Пожалуйста, выберите лимит и сотрудника');
                return false;
            }
            
            if (quantity <= 0) {
                e.preventDefault();
                alert('Количество должно быть больше 0');
                return false;
            }
            
            if (quantity > max) {
                e.preventDefault();
                alert('Нельзя делегировать больше, чем доступно');
                return false;
            }
            
            const limitName = limits[limitId]?.name || 'лимит';
            const employeeName = employees[userId]?.name || 'сотруднику';
            
            if (!confirm(`Делегировать ${quantity} шт. лимита "${limitName}" сотруднику ${employeeName}?`)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Инициализация при открытии модального окна
        $('#delegateModal').on('shown.bs.modal', function() {
            $('#limit_id').trigger('change');
            $('#user_id').trigger('change');
        });
    });
</script>
@endpush