@extends('layouts.app')

@section('title', 'Редактирование менеджера')
@section('page-icon', 'bi-pencil-square')

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
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="h2 mb-2">{{ $manager->name }}</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-primary px-3 py-2">
                                        <i class="bi bi-envelope me-1"></i>{{ $manager->email }}
                                    </span>
                                    <span class="badge bg-primary px-3 py-2">
                                        <i class="bi bi-person-badge me-1"></i>Менеджер
                                    </span>
                                    @if($manager->is_active)
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Активен
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-circle-fill me-1"></i>Неактивен
                                        </span>
                                    @endif
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
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.managers.show', $manager->id) }}" class="text-white opacity-75">
                                                {{ $manager->name }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Редактирование
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.managers.show', $manager->id) }}" class="btn btn-light">
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
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        Редактирование менеджера
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('admin.managers.update', $manager->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Аватар (для отображения) -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 500; color: white;">
                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                            </div>
                            <p class="text-muted small mb-0">Аватар менеджера</p>
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
                                       value="{{ old('name', $manager->name) }}" 
                                       placeholder="Введите имя менеджера"
                                       required>
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
                                       value="{{ old('email', $manager->email) }}" 
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
                                       name="phone" 
                                       value="{{ old('phone', $manager->phone) }}" 
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
                            Смена пароля <small class="text-muted">(необязательно)</small>
                        </h6>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Новый пароль</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" 
                                           placeholder="Введите новый пароль">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Оставьте пустым, если не нужно менять</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Подтверждение пароля</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           name="password_confirmation" 
                                           placeholder="Повторите новый пароль">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Информация
                                    </small>
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Создан:</span>
                                            <span class="fw-semibold">{{ $manager->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Роль:</span>
                                            <span class="fw-semibold">{{ $manager->getRoleDisplayName() }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">ID:</span>
                                            <span class="fw-semibold">#{{ $manager->id }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Статус:</span>
                                            @if($manager->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-danger">Неактивен</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-building me-1"></i>
                                        Контактные данные
                                    </small>
                                    <div class="small">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-envelope text-primary me-2"></i>
                                            <span class="text-muted me-1">Email:</span>
                                            <span class="fw-semibold">{{ $manager->email }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-telephone text-primary me-2"></i>
                                            <span class="text-muted me-1">Телефон:</span>
                                            @if($manager->phone)
                                                <span class="fw-semibold">{{ $manager->phone }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Не указан</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.managers.show', $manager->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Сохранить изменения
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


