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
       class="btn btn-secondary btn-sm">
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
                                <label for="organization_inn" class="form-label">
                                    ИНН
                                </label>
                                <input type="text" class="form-control @error('organization.inn') is-invalid @enderror" 
                                       id="organization_inn" name="organization[inn]" 
                                       value="{{ old('organization.inn') }}" 
                                       placeholder="Введите ИНН" 
                                       maxlength="12"
                                       pattern="[0-9]{10,12}"
                                       title="ИНН должен содержать от 10 до 12 цифр">
                                @error('organization.inn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">ИНН организации (10 цифр для юрлиц, 12 для ИП)</div>
                            </div>
                        </div>
                        
                        <div class="row">
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
                                        <option value="expired" {{ old('organization.status') == 'expired' ? 'selected' : '' }}>Истекла</option>
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
                            
                            <div class="col-md-6 mb-3">
                                <label for="organization_max_employees" class="form-label">
                                    Максимальное количество сотрудников
                                </label>
                                <input type="number" class="form-control @error('organization.max_employees') is-invalid @enderror" 
                                       id="organization_max_employees" name="organization[max_employees]" 
                                       value="{{ old('organization.max_employees') }}" 
                                       placeholder="Например: 50"
                                       min="1"
                                       step="1">
                                @error('organization.max_employees')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Оставьте пустым для безлимитного количества сотрудников</div>
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
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
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
                                    {{-- Для менеджера - скрытое поле с его ID --}}
                                    <input type="hidden" name="manager_id" value="{{ $user->id }}">
                                    <input type="text" class="form-control" value="{{ $user->name }} ({{ $user->email }})" readonly>
                                    <div class="form-text text-muted">Вы являетесь менеджером этой организации</div>
                                @else
                                    {{-- Для админа показываем выбор менеджера (пользователей с ролью manager) --}}
                                    <select class="form-select @error('manager_id') is-invalid @enderror" 
                                            id="manager_id" name="manager_id">
                                        <option value="">-- Без менеджера --</option>
                                        <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                            Я буду менеджером ({{ $user->name }})
                                        </option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" 
                                                    {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }} ({{ $manager->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">Выберите менеджера для организации</div>
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
                    
                    <!-- Информационные подсказки для новых полей -->
                    <div class="alert alert-info small py-2 mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Обратите внимание:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li>ИНН должен содержать 10 цифр для юридических лиц или 12 для ИП</li>
                            <li>Лимит сотрудников можно указать для ограничения количества добавляемых сотрудников</li>
                            <li>Если лимит не указан, сотрудников можно добавлять без ограничений</li>
                        </ul>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('manager.dashboard') }}" 
                           class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Отмена
                        </a>
                        
                        <button type="submit" class="btn btn-success" id="submitBtn">
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
        if (subscriptionField) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            subscriptionField.min = tomorrow.toISOString().split('T')[0];
        }
        
        // Валидация ИНН (только цифры)
        const innField = document.getElementById('organization_inn');
        if (innField) {
            innField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
        
        // Валидация максимального количества сотрудников (только положительные числа)
        const maxEmployeesField = document.getElementById('organization_max_employees');
        if (maxEmployeesField) {
            maxEmployeesField.addEventListener('input', function(e) {
                let value = parseInt(this.value);
                if (value < 1) {
                    this.value = '';
                }
            });
        }
        
        // Валидация формы
        const form = document.getElementById('createOrganizationForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('user_password').value;
            const confirmPassword = document.getElementById('user_password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                document.getElementById('user_password').focus();
                return false;
            }
            
            // Проверка ИНН если он заполнен
            const inn = document.getElementById('organization_inn').value;
            if (inn && (inn.length < 10 || inn.length > 12)) {
                e.preventDefault();
                alert('ИНН должен содержать от 10 до 12 цифр');
                document.getElementById('organization_inn').focus();
                return false;
            }
            
            // Проверка максимального количества сотрудников
            const maxEmployees = document.getElementById('organization_max_employees').value;
            if (maxEmployees && parseInt(maxEmployees) < 1) {
                e.preventDefault();
                alert('Максимальное количество сотрудников должно быть не менее 1');
                document.getElementById('organization_max_employees').focus();
                return false;
            }
            
            // Блокируем кнопку повторной отправки
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Создание...';
        });
    });
</script>
@endpush
@endsection