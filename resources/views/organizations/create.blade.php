@extends('layouts.app')

@section('title', 'Создание организации')
@section('page-icon', 'bi-building-add')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-building-add text-success me-2"></i>
        Создание новой организации
    </h5>
    <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('manager.dashboard') }}" 
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-success me-2"></i>
                    Информация об организации
                </h6>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form method="POST" 
                      action="{{ $user->isAdmin() ? route('admin.organization.store') : route('manager.organization.store') }}" 
                      id="createOrganizationForm">
                    @csrf
                    
                    <!-- Раздел организации -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-building me-2"></i>
                            Данные организации
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organization_name" class="form-label">
                                    Название организации *
                                </label>
                                <input type="text" class="form-control @error('organization.name') is-invalid @enderror" 
                                       id="organization_name" name="organization[name]" 
                                       value="{{ old('organization.name') }}" 
                                       placeholder="Введите название" required>
                                @error('organization.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Уникальное название организации</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    Статус *
                                </label>
                                @if($user->isAdmin())
                                    <select class="form-select @error('organization.status') is-invalid @enderror" 
                                            id="status" name="organization[status]" required>
                                        <option value="">Выберите статус</option>
                                        <option value="active" {{ old('organization.status') == 'active' ? 'selected' : '' }}>Активна</option>
                                        <option value="suspended" {{ old('organization.status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                        <option value="expire" {{ old('organization.status') == 'expire' ? 'selected' : '' }}>Истекла</option>
                                    </select>
                                @else
                                    <select class="form-select @error('organization.status') is-invalid @enderror" 
                                            id="status" name="organization[status]" required>
                                        <option value="">Выберите статус</option>
                                        <option value="active" {{ old('organization.status') == 'active' ? 'selected' : '' }}>Активна</option>
                                        <option value="inactive" {{ old('organization.status') == 'inactive' ? 'selected' : '' }}>Неактивна</option>
                                    </select>
                                @endif
                                @error('organization.status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subscription_ends_at" class="form-label">
                                    Окончание подписки
                                </label>
                                <input type="date" class="form-control @error('organization.subscription_ends_at') is-invalid @enderror" 
                                       id="subscription_ends_at" name="organization[subscription_ends_at]" 
                                       value="{{ old('organization.subscription_ends_at') }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('organization.subscription_ends_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Оставьте пустым для бессрочной подписки</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="manager_id" class="form-label">
                                    Ответственный менеджер
                                </label>
                                @if($user->isManager())
                                    {{-- Для менеджера показываем только его самого --}}
                                    <input type="hidden" name="manager_id" value="{{ $managerRecord->id }}">
                                    <input type="text" class="form-control" value="{{ $user->name }} ({{ $user->email }})" readonly>
                                    <div class="form-text text-muted">Вы являетесь менеджером этой организации</div>
                                @else
                                    {{-- Для админа показываем выбор менеджера --}}
                                    <select class="form-select @error('manager_id') is-invalid @enderror" 
                                            id="manager_id" name="manager_id">
                                        <option value="">Выберите менеджера</option>
                                        <option value="" {{ old('manager_id') === '' ? 'selected' : '' }}>
                                            Я буду менеджером ({{ $user->name }})
                                        </option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager['id'] }}" 
                                                    {{ old('manager_id') == $manager['id'] ? 'selected' : '' }}>
                                                {{ $manager['name'] }} ({{ $manager['email'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">Если не выберете менеджера, вы станете ответственным</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Раздел владельца -->
                    <div class="mb-4 pt-3 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-person me-2"></i>
                            Данные владельца организации
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_name" class="form-label">
                                    Имя владельца *
                                </label>
                                <input type="text" class="form-control @error('user.name') is-invalid @enderror" 
                                       id="user_name" name="user[name]" 
                                       value="{{ old('user.name') }}" 
                                       placeholder="Введите имя" required>
                                @error('user.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">
                                    Email *
                                </label>
                                <input type="email" class="form-control @error('user.email') is-invalid @enderror" 
                                       id="user_email" name="user[email]" 
                                       value="{{ old('user.email') }}" 
                                       placeholder="email@example.com" required>
                                @error('user.email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_password" class="form-label">
                                    Пароль *
                                </label>
                                <input type="password" class="form-control @error('user.password') is-invalid @enderror" 
                                       id="user_password" name="user[password]" 
                                       placeholder="Минимум 8 символов" required>
                                @error('user.password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_password_confirmation" class="form-label">
                                    Подтверждение пароля *
                                </label>
                                <input type="password" class="form-control" 
                                       id="user_password_confirmation" name="user[password_confirmation]" 
                                       placeholder="Повторите пароль" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('manager.dashboard') }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Отмена
                        </a>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Создать организацию
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Устанавливаем минимальную дату для подписки
        const subscriptionField = document.getElementById('subscription_ends_at');
        if (!subscriptionField.value) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            subscriptionField.min = tomorrow.toISOString().split('T')[0];
        }
        
        // Валидация формы
        const form = document.getElementById('createOrganizationForm');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('user_password').value;
            const confirmPassword = document.getElementById('user_password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                document.getElementById('user_password').focus();
            }
        });
    });
</script>
@endpush
@endsection