@extends('layouts.app')

@section('title', 'Редактирование профиля')
@section('page-icon', 'bi-pencil-square')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок с градиентом -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-danger text-white shadow-lg" 
                 style="background: linear-gradient(135deg,  #fd7e14 0%, #e96b02 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Редактирование профиля</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-danger px-3 py-2">
                                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                    </span>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="bi bi-person-gear me-1"></i>Администратор
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
                                            Редактирование профиля
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Основная карточка с формой -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Основная информация
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('admin.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Аватар (для отображения) -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 500; color: white;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <p class="text-muted small mb-0">Ваш аватар</p>
                        </div>

                        <!-- Имя -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person text-primary me-1"></i>
                                Имя <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" 
                                   placeholder="Введите ваше имя"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-envelope text-primary me-1"></i>
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" 
                                   placeholder="Введите email"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Используется для входа в систему</small>
                        </div>

                        <!-- Телефон -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-telephone text-primary me-1"></i>
                                Телефон
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="+7 (999) 999-99-99">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Информация о роли (только для чтения) -->
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px; color: white; font-size: 1rem;">
                                    <i class="bi bi-person-gear"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Ваша роль</small>
                                    <span class="fw-semibold">Администратор</span>
                                    <span class="badge bg-success ms-2">Активен</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Сохранить изменения
                            </button>
                            <a href="{{ route('admin.profile.change-password') }}" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Изменить пароль
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Информация о подписках (для справки) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        Информация об аккаунте
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-calendar text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Дата регистрации</small>
                                    <span class="fw-semibold">{{ $user->created_at->format('d.m.Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-clock text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Последнее обновление</small>
                                    <span class="fw-semibold">{{ $user->updated_at->format('d.m.Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection