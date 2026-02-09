@extends('layouts.app')

@section('title', 'Редактирование сотрудника')

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        // Определяем префикс маршрута
        if ($isAdmin) {
            $routePrefix = 'admin.';
        } elseif ($isManager) {
            $routePrefix = 'manager.';
        } else {
            $routePrefix = 'owner.';
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-gear text-warning"></i> Редактирование: {{ $member->user->name }}
        </h1>
        <div>
            <a href="{{ route($routePrefix . 'org-members.show', [$organization->id, $member->id]) }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к сотруднику
            </a>
        </div>
    </div>

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

    <form action="{{ route($routePrefix . 'org-members.update', [$organization->id, $member->id]) }}" 
          method="POST"
          data-turbo="false">
        @csrf
        @method('POST')
        
        <div class="row">
            <!-- Левая колонка: Личные данные -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Личные данные</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px; color: white; font-size: 28px;">
                                {{ strtoupper(substr($member->user->name, 0, 1)) }}
                            </div>
                            <h4>{{ $member->user->name }}</h4>
                            <p class="text-muted">{{ $member->user->email }}</p>
                        </div>

                        <div class="mb-3">
                            <label for="user_name" class="form-label">Имя сотрудника *</label>
                            <input type="text" class="form-control @error('user.name') is-invalid @enderror" 
                                   id="user_name" name="user[name]" 
                                   value="{{ old('user.name', $member->user->name) }}" required>
                            @error('user.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('user.email') is-invalid @enderror" 
                                   id="user_email" name="user[email]" 
                                   value="{{ old('user.email', $member->user->email) }}" required>
                            @error('user.email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" 
                                       {{ old('is_active', $member->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Активен (может войти в систему)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Дополнительные действия -->
                @if($isAdmin || $isManager)
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Опасные действия</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="#" class="btn btn-outline-warning" 
                                   data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="bi bi-key"></i> Сменить пароль
                                </a>

                                <form action="{{ route($routePrefix . 'org-members.toggle-status', [$organization->id, $member->id]) }}" 
                                      method="POST"
                                      data-turbo="false">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-outline-{{ $member->is_active ? 'warning' : 'success' }} w-100">
                                        <i class="bi bi-toggle-{{ $member->is_active ? 'off' : 'on' }}"></i>
                                        {{ $member->is_active ? 'Деактивировать' : 'Активировать' }}
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete({{ $organization->id }}, {{ $member->id }}, '{{ $member->user->name }}')">
                                    <i class="bi bi-trash"></i> Удалить сотрудника
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Правая колонка: Рабочая информация -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-briefcase"></i> Рабочая информация</h5>
                    </div>

                        <!-- Информация об организации -->
                        <div class="card border-success mt-3">
                            <div class="card-header bg-transparent border-success">
                                <h6 class="mb-0"><i class="bi bi-building me-2"></i> Организация</h6>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $organization->name }}</h5>
                                <p class="card-text">
                                    @if($organization->status === 'active')
                                        <span class="badge bg-success">Активна</span>
                                    @elseif($organization->status === 'suspended')
                                        <span class="badge bg-warning">Приостановлена</span>
                                    @else
                                        <span class="badge bg-danger">Истекла</span>
                                    @endif
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Менеджер: 
                                        @if($member->manager && $member->manager->user)
                                            {{ $member->manager->user->name }}
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Информация о сотруднике -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Системная информация</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <td style="width: 40%;" class="border-0">
                                            <small class="text-muted">Дата добавления:</small>
                                        </td>
                                        <td class="border-0 fw-bold">
                                            {{ $member->created_at->format('d.m.Y H:i') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small class="text-muted">Последнее обновление:</small>
                                        </td>
                                        <td class="fw-bold">
                                            {{ $member->updated_at->format('d.m.Y H:i') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small class="text-muted">Уровень доступа:</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">Сотрудник организации</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small class="text-muted">Статус аккаунта:</small>
                                        </td>
                                        <td>
                                            @if($member->is_active)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i> Активен
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i> Неактивен
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small class="text-muted">ID профиля:</small>
                                        </td>
                                        <td class="text-muted">
                                            #{{ $member->id }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Кнопки сохранения -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Готовы сохранить изменения?</h6>
                                <small class="text-muted">Все поля отмеченные * обязательны для заполнения</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route($routePrefix . 'org-members.show', [$organization->id, $member->id]) }}" 
                                   class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-x-circle"></i> Отменить
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Модальное окно смены пароля -->
@if($isAdmin || $isManager)
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Смена пароля для {{ $member->user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route($routePrefix . 'org-members.change-password', [$organization->id, $member->id]) }}" 
                  method="POST"
                  data-turbo="false">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль *</label>
                        <input type="password" class="form-control" id="new_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Подтверждение пароля *</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        После смены пароля сотруднику потребуется войти в систему заново.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сменить пароль</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Форма для удаления -->
<form id="delete-form" method="POST" style="display: none;" data-turbo="false">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(organizationId, memberId, name) {
    if (confirm(`Вы уверены, что хотите удалить сотрудника "${name}"? Это действие удалит его учетную запись и все связанные данные.`)) {
        const form = document.getElementById('delete-form');
        const prefix = '{{ $isAdmin ? "admin" : "manager" }}';
        form.action = `/${prefix}/organization/${organizationId}/member/${memberId}/delete`;
        form.submit();
    }
}
</script>
@endif
@endsection