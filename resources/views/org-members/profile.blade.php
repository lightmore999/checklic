@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-circle text-primary"></i> Мой профиль
        </h1>
        <a href="{{ route('member.profile.edit') }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Основная информация -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; color: white; font-size: 28px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-info">Сотрудник</span>
                        @if($user->is_active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-danger">Неактивен</span>
                        @endif
                    </div>
                    
                    <p class="text-muted small">
                        <i class="bi bi-calendar"></i> Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <!-- Статистика лимитов -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer text-warning me-2"></i>
                        Мои лимиты
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($totalDelegated) && $totalDelegated > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Всего делегировано:</small>
                                <span class="badge bg-warning">{{ $totalDelegated }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small>Использовано:</small>
                                <span class="badge bg-info">{{ $totalUsed }} шт.</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small>Доступно:</small>
                                <span class="badge bg-{{ $totalAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $totalAvailable }} шт.
                                </span>
                            </div>
                            
                            <!-- Прогресс бар -->
                            <div class="progress mb-2" style="height: 8px;">
                                @php
                                    $percentage = $totalDelegated > 0 ? round(($totalUsed / $totalDelegated) * 100) : 0;
                                @endphp
                                <div class="progress-bar bg-{{ $percentage > 80 ? 'danger' : ($percentage > 50 ? 'warning' : 'success') }}" 
                                     style="width: {{ $percentage }}%">
                                </div>
                            </div>
                            <small class="text-muted d-block text-center">
                                Использовано {{ $percentage }}% от общего лимита
                            </small>
                        </div>
                        
                        <!-- Типы лимитов -->
                        @if(!empty($limitsByType))
                            <div class="border-top pt-3">
                                <small class="text-muted d-block mb-2">По типам отчетов:</small>
                                @foreach($limitsByType as $typeName => $stats)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-truncate" style="max-width: 120px;" title="{{ $typeName }}">
                                            {{ $typeName }}
                                        </small>
                                        <div>
                                            <span class="badge bg-light text-dark border" title="Делегировано: {{ $stats['delegated'] }}, Использовано: {{ $stats['used'] }}">
                                                {{ $stats['available'] }}/{{ $stats['delegated'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-speedometer text-muted fs-1 mb-3 d-block"></i>
                            <p class="text-muted mb-0">У вас нет делегированных лимитов</p>
                            <small class="text-muted">Обратитесь к вашему руководителю</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Правая колонка -->
        <div class="col-lg-8">
            <!-- Рабочая информация -->
            @if($memberProfile && $organization)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Рабочая информация</h5>
                    <span class="badge bg-primary">{{ $organization->name }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%;">Организация:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; color: white; font-size: 0.8rem;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $organization->name }}</strong>
                                                <div class="mt-1">
                                                    @if($organization->status === 'active')
                                                        <span class="badge bg-success">Активна</span>
                                                    @elseif($organization->status === 'suspended')
                                                        <span class="badge bg-warning">Приостановлена</span>
                                                    @else
                                                        <span class="badge bg-danger">Истекла</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Дата добавления:</th>
                                    <td>{{ $memberProfile->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%;">Начальник:</th>
                                    <td>
                                        @if($memberProfile->boss && $memberProfile->boss->user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($memberProfile->boss->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div>{{ $memberProfile->boss->user->name }}</div>
                                                    <small class="text-muted">{{ $memberProfile->boss->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Статус:</th>
                                    <td>
                                        @if($memberProfile->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Информация о подписке -->
                    @if($organization->subscription_ends_at)
                        <div class="alert alert-light mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-clock-history me-2"></i>
                                    Подписка организации до: 
                                    <strong>{{ $organization->subscription_ends_at->format('d.m.Y') }}</strong>
                                </div>
                                @if($organization->isSubscriptionExpiringSoon())
                                    <span class="badge bg-warning">
                                        Истекает через {{ $organization->getRemainingSubscriptionDays() }} дн.
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Делегированные лимиты -->
            @if(isset($delegatedLimits) && $delegatedLimits->count() > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check text-info me-2"></i>
                        Мои делегированные лимиты
                        <span class="badge bg-info ms-2">{{ $delegatedLimits->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Тип отчета</th>
                                    <th>Делегировано</th>
                                    <th>Использовано</th>
                                    <th>Доступно</th>
                                    <th>Дата делегирования</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delegatedLimits as $delegated)
                                @php
                                    $available = $delegated->quantity - $delegated->used_quantity;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $delegated->limit->reportType->name ?? 'Без типа' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $delegated->quantity }} шт.</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $delegated->used_quantity }} шт.</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $available > 0 ? 'success' : 'danger' }}">
                                            {{ $available }} шт.
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
            </div>
            @endif
        </div>
    </div>
</div>
@endsection