@extends('layouts.app')

@section('title', 'Создание менеджера')
@section('page-icon', 'bi-person-plus')

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
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Создание нового менеджера</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-primary px-3 py-2">
                                        <i class="bi bi-person-badge me-1"></i>Менеджер
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}" class="text-white opacity-75">
                                                Панель админа
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.managers.index') }}" class="text-white opacity-75">
                                                Менеджеры
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Создание
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.managers.index') }}" class="btn btn-light">
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
                        <i class="bi bi-person-plus text-primary me-2"></i>
                        Данные нового менеджера
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('admin.managers.store') }}">
                        @csrf
                        
                        <!-- Иконка для нового пользователя -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 500; color: #0d6efd;">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <p class="text-muted small mb-0">Заполните данные нового менеджера</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person text-primary me-1"></i>
                                    Имя менеджера <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       placeholder="Введите имя менеджера"
                                       required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope text-primary me-1"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="Введите email"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Используется для входа в систему</small>
                            </div>
                            
                            <!-- Номер телефона -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-telephone text-primary me-1"></i>
                                    Номер телефона <small class="text-muted">(необязательно)</small>
                                </label>
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone"
                                       name="phone" 
                                       value="{{ old('phone') }}" 
                                       placeholder="+7 (999) 999-99-99">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Формат: международный или местный номер</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3 fw-semibold">
                            <i class="bi bi-key text-warning me-2"></i>
                            Пароль
                        </h6>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Пароль <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" 
                                           placeholder="Введите пароль"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Минимум 8 символов</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Подтверждение пароля <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           name="password_confirmation" 
                                           placeholder="Повторите пароль"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Информационное сообщение -->
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Внимание:</strong><br>
                                Менеджер сможет создавать организации и владельцев организаций.
                                Пароль должен быть не менее 8 символов.
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Создать менеджера
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Стили для единообразия */
.rounded-circle {
    border-radius: 50% !important;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.card {
    border-radius: 1rem;
    transition: all 0.2s;
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
    border-radius: 30px;
    padding: 0.35em 0.65em;
}

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

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.form-label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.form-control {
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}

/* Стили для алертов */
.alert-info {
    background-color: #cff4fc;
    border-color: #b6effb;
    color: #055160;
    border-radius: 0.75rem;
}
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Маска для телефона
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            Inputmask({ 
                mask: ['+7 (999) 999-99-99', '+375 (99) 999-99-99', '+999 (99) 999-99-99'],
                keepStatic: true,
                showMaskOnHover: false,
                clearIncomplete: true
            }).mask(phoneInput);
        }

        // Переключение видимости пароля
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    });
</script>
@endpush


