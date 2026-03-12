@extends('layouts.app')

@section('title', 'Изменение пароля')
@section('page-icon', 'bi-key')

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
                                <h1 class="h2 mb-2">Изменение пароля</h1>
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
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.profile.edit') }}" class="text-white opacity-75">
                                                Редактирование профиля
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Изменение пароля
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.profile.edit') }}" class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">
                        <i class="bi bi-key text-warning me-2"></i>
                        Изменение пароля
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('admin.profile.change-password.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Текущий пароль -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-lock text-primary me-1"></i>
                                Текущий пароль <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       placeholder="Введите текущий пароль"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Новый пароль -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-lock-fill text-primary me-1"></i>
                                Новый пароль <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="new_password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       placeholder="Введите новый пароль"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Минимум 8 символов</small>
                        </div>

                        <!-- Подтверждение пароля -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-check-circle text-primary me-1"></i>
                                Подтверждение пароля <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       name="new_password_confirmation" 
                                       class="form-control" 
                                       placeholder="Повторите новый пароль"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-warning py-2 mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            После смены пароля вы останетесь в системе. Для повторного входа используйте новый пароль.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Изменить пароль
                            </button>
                            <a href="{{ route('admin.profile.edit') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-2"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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