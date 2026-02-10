@extends('layouts.app')

@section('title', 'Добавление сотрудника')

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $routePrefix = $isAdmin ? 'admin.' : 'manager.';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-plus text-success"></i> Добавление сотрудника
        </h1>
        <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к организации
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix . 'org-members.store', $organization->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Имя сотрудника *</label>
                            <input type="text" class="form-control @error('user.name') is-invalid @enderror" 
                                   id="user_name" name="user[name]" 
                                   value="{{ old('user.name') }}" required>
                            @error('user.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('user.email') is-invalid @enderror" 
                                   id="user_email" name="user[email]" 
                                   value="{{ old('user.email') }}" required>
                            @error('user.email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_password" class="form-label">Пароль *</label>
                            <input type="password" class="form-control @error('user.password') is-invalid @enderror" 
                                   id="user_password" name="user[password]" required>
                            @error('user.password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_password_confirmation" class="form-label">Подтверждение пароля *</label>
                            <input type="password" class="form-control" 
                                   id="user_password_confirmation" name="user[password_confirmation]" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
                               class="btn btn-outline-secondary">
                                Отмена
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-plus"></i> Добавить сотрудника
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Организация</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 60px; height: 60px; color: white; font-size: 20px;">
                            <i class="bi bi-building"></i>
                        </div>
                        <h5>{{ $organization->name }}</h5>
                        @if($organization->status === 'active')
                            <span class="badge bg-success">Активна</span>
                        @elseif($organization->status === 'suspended')
                            <span class="badge bg-warning">Приостановлена</span>
                        @else
                            <span class="badge bg-danger">Истекла</span>
                        @endif
                    </div>

                    <div class="small">
                        <p><i class="bi bi-people me-2"></i> Сотрудников: {{ $organization->members->count() }}</p>
                        @if($organization->owner && $organization->owner->user)
                            <p><i class="bi bi-person-badge me-2"></i> Владелец: {{ $organization->owner->user->name }}</p>
                            <p class="text-muted small">(будет автоматически назначен начальником)</p>
                        @endif
                        @if($organization->manager && $organization->manager->user)
                            <p><i class="bi bi-person-gear me-2"></i> Менеджер: {{ $organization->manager->user->name }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Важно знать</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light">
                        <h6 class="alert-heading">При добавлении сотрудника:</h6>
                        <ul class="mb-0 small">
                            <li>Создается учетная запись с ролью "Сотрудник"</li>
                            <li>Начальником автоматически назначается владелец организации</li>
                            <li>Менеджером назначается ответственный менеджер организации</li>
                            <li>Пароль будет отправлен на указанный email</li>
                            <li>Сотрудник сможет войти сразу после создания</li>
                            <li>Все поля отмеченные * обязательны</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection