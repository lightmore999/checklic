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
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; color: white; font-size: 36px;">
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
        </div>

        <!-- Информация о работе -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Рабочая информация</h5>
                </div>
                <div class="card-body">
                    @if($memberProfile && $organization)
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
                                                    @if($organization->status === 'active')
                                                        <span class="badge bg-success">Активна</span>
                                                    @elseif($organization->status === 'suspended')
                                                        <span class="badge bg-warning">Приостановлена</span>
                                                    @else
                                                        <span class="badge bg-danger">Истекла</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Должность:</th>
                                        <td>{{ $memberProfile->position ?? 'Не указана' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Телефон:</th>
                                        <td>{{ $memberProfile->phone ?? 'Не указан' }}</td>
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
                                                    <div>{{ $memberProfile->boss->user->name }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">Не назначен</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Менеджер:</th>
                                        <td>
                                            @if($memberProfile->manager && $memberProfile->manager->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                        {{ strtoupper(substr($memberProfile->manager->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>{{ $memberProfile->manager->user->name }}</div>
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
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Информация о вашей работе не найдена. Обратитесь к администратору.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Статистика (если нужна) -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h2 class="text-primary mb-0">0</h2>
                            <small class="text-muted">Созданных отчетов</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h2 class="text-success mb-0">0</h2>
                            <small class="text-muted">Выполненных задач</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h2 class="text-info mb-0">0</h2>
                            <small class="text-muted">Активных лицензий</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection