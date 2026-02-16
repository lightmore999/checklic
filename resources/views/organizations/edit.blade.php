@extends('layouts.app')

@section('title', 'Редактирование организации')

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $owner = $organization->owner;
        $ownerUser = $owner ? $owner->user : null;
        $currentEmployeesCount = $organization->members ? $organization->members->count() : 0;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-building-gear text-warning"></i> Редактирование: {{ $organization->name }}
        </h1>
        <div>
            <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
               class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Назад к организации
            </a>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Ошибка!</strong> Пожалуйста, исправьте следующие ошибки:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ОСНОВНАЯ ФОРМА РЕДАКТИРОВАНИЯ -->
    <form action="{{ route($routePrefix . 'organization.update', $organization->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Левая колонка: Организация -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Информация об организации</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="organization_name" class="form-label">Название организации *</label>
                            <input type="text" class="form-control @error('organization.name') is-invalid @enderror" 
                                   id="organization_name" name="organization[name]" 
                                   value="{{ old('organization.name', $organization->name) }}" required>
                            @error('organization.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="organization_inn" class="form-label">ИНН</label>
                                <input type="text" class="form-control @error('organization.inn') is-invalid @enderror" 
                                       id="organization_inn" name="organization[inn]" 
                                       value="{{ old('organization.inn', $organization->inn) }}"
                                       maxlength="12"
                                       pattern="[0-9]{10,12}"
                                       placeholder="Введите ИНН"
                                       title="ИНН должен содержать от 10 до 12 цифр">
                                @error('organization.inn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">ИНН организации (10 цифр для юрлиц, 12 для ИП)</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="organization_max_employees" class="form-label">Макс. сотрудников</label>
                                <input type="number" class="form-control @error('organization.max_employees') is-invalid @enderror" 
                                       id="organization_max_employees" name="organization[max_employees]" 
                                       value="{{ old('organization.max_employees', $organization->max_employees) }}"
                                       min="1"
                                       step="1"
                                       placeholder="Например: 50">
                                @error('organization.max_employees')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Оставьте пустым для безлимита</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Статус организации *</label>
                                    <select class="form-select @error('organization.status') is-invalid @enderror" 
                                            id="status" name="organization[status]" required>
                                        <option value="active" {{ old('organization.status', $organization->status) == 'active' ? 'selected' : '' }}>Активна</option>
                                        <option value="suspended" {{ old('organization.status', $organization->status) == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                        <option value="expired" {{ old('organization.status', $organization->status) == 'expired' ? 'selected' : '' }}>Истекла</option>
                                    </select>
                                    @error('organization.status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subscription_ends_at" class="form-label">Подписка до</label>
                                    <input type="date" class="form-control @error('organization.subscription_ends_at') is-invalid @enderror" 
                                           id="subscription_ends_at" name="organization[subscription_ends_at]"
                                           value="{{ old('organization.subscription_ends_at', $organization->subscription_ends_at ? $organization->subscription_ends_at->format('Y-m-d') : '') }}">
                                    @error('organization.subscription_ends_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($isAdmin)
                            <div class="mb-3">
                                <label for="manager_id" class="form-label">Ответственный менеджер</label>
                                <select class="form-select @error('organization.manager_id') is-invalid @enderror" 
                                        id="manager_id" name="organization[manager_id]">
                                    <option value="">-- Без менеджера --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" 
                                                {{ old('organization.manager_id', $organization->manager_id) == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization.manager_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Выберите менеджера из списка</div>
                            </div>
                        @endif

                        <!-- Информация о текущем лимите сотрудников -->
                        @if($organization->max_employees)
                        <div class="alert alert-info mt-3 py-2">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Текущая статистика:</strong>
                            Сотрудников: {{ $currentEmployeesCount }} / {{ $organization->max_employees }}
                            @if($currentEmployeesCount > $organization->max_employees)
                                <span class="badge bg-danger ms-2">Превышен!</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Правая колонка: Владелец организации -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge"></i> 
                            @if($ownerUser)
                                Редактирование владельца
                            @else
                                Назначение владельца
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Поля для редактирования/создания владельца -->
                        <div class="mb-3">
                            <label for="owner_name" class="form-label">Имя владельца *</label>
                            <input type="text" class="form-control @error('owner.name') is-invalid @enderror" 
                                   id="owner_name" name="owner[name]" 
                                   value="{{ old('owner.name', $ownerUser->name ?? '') }}" 
                                   required>
                            @error('owner.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="owner_email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('owner.email') is-invalid @enderror" 
                                   id="owner_email" name="owner[email]" 
                                   value="{{ old('owner.email', $ownerUser->email ?? '') }}" 
                                   required>
                            @error('owner.email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="owner_password" class="form-label">
                                @if($ownerUser)
                                    Новый пароль (оставьте пустым, если не нужно менять)
                                @else
                                    Пароль *
                                @endif
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('owner.password') is-invalid @enderror" 
                                       id="owner_password" name="owner[password]" 
                                       placeholder="Введите пароль"
                                       {{ $ownerUser ? '' : 'required' }}>
                                <button class="btn btn-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('owner.password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($ownerUser)
                                <div class="form-text">Оставьте поле пустым, если не хотите менять пароль</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="owner_password_confirmation" class="form-label">
                                Подтверждение пароля
                                @if(!$ownerUser)
                                    *
                                @endif
                            </label>
                            <input type="password" class="form-control" 
                                   id="owner_password_confirmation" name="owner[password_confirmation]"
                                   placeholder="Повторите пароль"
                                   {{ $ownerUser ? '' : 'required' }}>
                        </div>

                        @if($ownerUser)
                            <div class="alert alert-light border">
                                <h6 class="alert-heading">Информация о владельце:</h6>
                                <p class="mb-1"><strong>Зарегистрирован:</strong> {{ $ownerUser->created_at->format('d.m.Y H:i') }}</p>
                                <p class="mb-1"><strong>Роль:</strong> <span class="badge bg-success">Владелец организации</span></p>
                                <p class="mb-0"><strong>Статус:</strong> 
                                    @if($ownerUser->is_active)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Неактивен</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Кнопки сохранения -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
                               class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Отменить изменения
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Сохранить все изменения
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- КОНЕЦ ОСНОВНОЙ ФОРМЫ РЕДАКТИРОВАНИЯ -->

    <!-- Дополнительные действия для админа (ВНЕ основной формы) -->
    @if($isAdmin)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Опасные действия</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Кнопка изменения статуса -->
                        <div class="col-md-4 mb-2">
                            <form action="{{ route('admin.organization.toggle-status', $organization->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-{{ $organization->status == 'active' ? 'warning' : 'success' }} w-100">
                                    <i class="bi bi-toggle-{{ $organization->status == 'active' ? 'off' : 'on' }}"></i>
                                    {{ $organization->status == 'active' ? 'Деактивировать' : 'Активировать' }}
                                </button>
                            </form>
                        </div>

                        <!-- Кнопка продления подписки -->
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-info w-100" 
                                    data-bs-toggle="modal" data-bs-target="#extendSubscriptionModal">
                                <i class="bi bi-calendar-plus"></i> Продлить подписку
                            </button>
                        </div>

                        <!-- Кнопка удаления -->
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-danger w-100"
                                    onclick="confirmDelete({{ $organization->id }}, '{{ $organization->name }}')">
                                <i class="bi bi-trash"></i> Удалить организацию
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Модальное окно продления подписки -->
@if($isAdmin)
<div class="modal fade" id="extendSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Продлить подписку организации</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.organization.extend-subscription', $organization->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="days" class="form-label">Количество дней продления *</label>
                        <input type="number" class="form-control" id="days" name="days" min="1" max="365" value="30" required>
                        <div class="form-text">Максимум 365 дней</div>
                    </div>
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Текущая подписка:</h6>
                        <p class="mb-0">
                            @if($organization->subscription_ends_at)
                                Действует до: <strong>{{ $organization->subscription_ends_at->format('d.m.Y') }}</strong>
                            @else
                                <strong>Бессрочная</strong>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Продлить подписку</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Форма для удаления -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id, name) {
    if (confirm(`ВНИМАНИЕ! Вы собираетесь удалить организацию "${name}".\n\nЭто действие: \n• Удалит организацию \n• Удалит владельца организации \n• Удалит всех сотрудников \n• Удалит все данные связанные с организацией\n\nЭто действие нельзя отменить!`)) {
        const form = document.getElementById('delete-form');
        form.action = '{{ url("/admin/organization") }}/' + id + '/delete';
        form.submit();
    }
}
</script>
@endif

<script>
// Показать/скрыть пароль
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordField = document.getElementById('owner_password');
    const confirmField = document.getElementById('owner_password_confirmation');
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    
    passwordField.setAttribute('type', type);
    if (confirmField) {
        confirmField.setAttribute('type', type);
    }
    
    this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
});

// Валидация ИНН (только цифры)
document.getElementById('organization_inn')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Валидация максимального количества сотрудников
document.getElementById('organization_max_employees')?.addEventListener('input', function(e) {
    let value = parseInt(this.value);
    if (value < 1) {
        this.value = '';
    }
});

// Предотвращаем двойную отправку формы
document.querySelector('form[action*="update"]')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Проверка ИНН
    const inn = document.getElementById('organization_inn')?.value;
    if (inn && (inn.length < 10 || inn.length > 12)) {
        e.preventDefault();
        alert('ИНН должен содержать от 10 до 12 цифр');
        document.getElementById('organization_inn')?.focus();
        return false;
    }
    
    // Проверка максимального количества сотрудников
    const maxEmployees = document.getElementById('organization_max_employees')?.value;
    if (maxEmployees && parseInt(maxEmployees) < 1) {
        e.preventDefault();
        alert('Максимальное количество сотрудников должно быть не менее 1');
        document.getElementById('organization_max_employees')?.focus();
        return false;
    }
    
    if (submitBtn.disabled) {
        e.preventDefault();
        return;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Сохранение...';
});
</script>
@endsection