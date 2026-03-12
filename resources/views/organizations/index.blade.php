@extends('layouts.app')

@section('title', 'Организации')
@section('page-icon', 'bi-building')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Заголовок с градиентом -->
            <div class="card border-0 bg-gradient-primary text-white shadow-lg mb-4" 
                 style="background: linear-gradient(135deg, {{ $user->isAdmin() ? ' #fd7e14' : '#0d6efd' }} 0%, {{ $user->isAdmin() ? '#a71d2a' : '#0a58ca' }} 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-4" 
                                 style="width: 70px; height: 70px; font-size: 2rem; font-weight: 500; color: white; border: 3px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">
                                    @if($user->isAdmin())
                                        Все организации
                                    @else
                                        Мои организации
                                    @endif
                                </h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-{{ $user->isAdmin() ? 'danger' : 'primary' }} px-3 py-2">
                                        <i class="bi bi-building me-1"></i>Всего: {{ $organizations->total() }}
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route($user->isAdmin() ? 'admin.dashboard' : 'manager.dashboard') }}" 
                                               class="text-white opacity-75">
                                                Панель {{ $user->isAdmin() ? 'админа' : 'менеджера' }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Организации
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            @if($user->isAdmin() || $user->isManager())
                                <a href="{{ route($user->isAdmin() ? 'admin.organization.create' : 'manager.organization.create') }}" 
                                   class="btn btn-light">
                                    <i class="bi bi-plus-lg me-2"></i>Создать организацию
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Карточка с фильтрами и таблицей -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel text-primary me-2"></i>
                        Фильтры
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <!-- ФОРМА ФИЛЬТРОВ -->
                    <form method="GET" class="mb-4" id="filterForm">
                        <div class="row g-3">
                            <!-- Поиск по названию или ИНН -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Поиск по названию или ИНН</label>
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Название или ИНН..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Поиск по названию организации или ИНН</small>
                            </div>
                            
                            <!-- Поиск по владельцу -->
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Владелец (имя или email)</label>
                                <input type="text" 
                                       name="owner_search" 
                                       class="form-control" 
                                       placeholder="Поиск владельца..." 
                                       value="{{ request('owner_search') }}">
                            </div>
                            
                            <!-- Фильтр по менеджеру (только для админа) -->
                            @if($user->isAdmin())
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Менеджер</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">Все менеджеры</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" 
                                            {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <!-- Фильтр по статусу -->
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="">Все</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активна</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивна</option>
                                </select>
                            </div>
                            
                            <!-- Сортировка -->
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Сортировка</label>
                                <select name="sort" class="form-select">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>По дате создания</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>По названию</option>
                                    <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>По статусу</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Направление</label>
                                <select name="direction" class="form-select">
                                    <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                                    <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel me-2"></i>Применить фильтры
                                    </button>
                                    <a href="{{ route($user->isAdmin() ? 'admin.organizations.list' : 'manager.organizations.list') }}" 
                                       class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Активные фильтры -->
                    @if(request()->anyFilled(['search', 'owner_search', 'manager_id', 'status']))
                        <div class="alert alert-info py-2 mb-4">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <i class="bi bi-funnel-fill me-1"></i>
                                <span class="fw-semibold">Активные фильтры:</span>
                                @if(request('search'))
                                    <span class="badge bg-info text-white">Поиск: "{{ request('search') }}"</span>
                                @endif
                                @if(request('owner_search'))
                                    <span class="badge bg-info text-white">Владелец: "{{ request('owner_search') }}"</span>
                                @endif
                                @if(request('manager_id') && $user->isAdmin())
                                    @php
                                        $managerName = $managers->firstWhere('id', request('manager_id'))?->name ?? 'Неизвестно';
                                    @endphp
                                    <span class="badge bg-info text-white">Менеджер: {{ $managerName }}</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge bg-info text-white">Статус: {{ request('status') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- ТАБЛИЦА ОРГАНИЗАЦИЙ -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Организация / ИНН</th>
                                    <th>Наша организация</th>
                                    <th>Владелец</th>
                                    @if($user->isAdmin())
                                        <th>Менеджер</th>
                                    @endif
                                    <th>Статус</th>
                                    <th class="text-center">Сотрудников</th>
                                    <th class="text-center">Лимит</th>
                                    <th>Создана</th>
                                    <th class="text-center">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organizations as $org)
                                    @php
                                        $currentEmployees = $org->members ? $org->members->count() : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-muted">#{{ $org->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px; font-size: 0.9rem; color: #0d6efd;">
                                                    {{ strtoupper(substr($org->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $org->name }}</span>
                                                    @if($org->inn)
                                                        <small class="text-muted d-block">ИНН: {{ $org->inn }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($org->our_organization)
                                                <span class="badge bg-info bg-opacity-10 text-info">{{ $org->our_organization }}</span>
                                            @else
                                                <span class="text-muted fst-italic small">Не указано</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($org->owner && $org->owner->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 28px; height: 28px; font-size: 0.8rem; color: #198754;">
                                                        {{ strtoupper(substr($org->owner->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold small">{{ $org->owner->user->name }}</span>
                                                        <small class="text-muted d-block">{{ $org->owner->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic small">Не назначен</span>
                                            @endif
                                        </td>
                                        
                                        @if($user->isAdmin())
                                            <td>
                                                @if($org->manager)
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 28px; height: 28px; font-size: 0.8rem; color: #0d6efd;">
                                                            {{ strtoupper(substr($org->manager->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <span class="fw-semibold small">{{ $org->manager->name }}</span>
                                                            <small class="text-muted d-block">{{ $org->manager->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted fst-italic small">Не назначен</span>
                                                @endif
                                            </td>
                                        @endif
                                        
                                        <td>
                                            @php
                                                $statusConfig = [
                                                    'active' => ['class' => 'success', 'text' => 'Активна'],
                                                    'suspended' => ['class' => 'warning', 'text' => 'Приостановлена'],
                                                    'expired' => ['class' => 'danger', 'text' => 'Истекла'],
                                                    'inactive' => ['class' => 'secondary', 'text' => 'Неактивна'],
                                                ];
                                                $config = $statusConfig[$org->status] ?? ['class' => 'secondary', 'text' => $org->status];
                                            @endphp
                                            <span class="badge bg-{{ $config['class'] }} px-3 py-2">
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $currentEmployees }}</span>
                                        </td>
                                        
                                        <td class="text-center">
                                            @if($org->max_employees)
                                                <span class="badge bg-secondary">{{ $org->max_employees }}</span>
                                                @if($currentEmployees > $org->max_employees)
                                                    <br><small class="text-danger">превышен</small>
                                                @endif
                                            @else
                                                <span class="text-muted">∞</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px;">
                                                    <i class="bi bi-calendar text-primary small"></i>
                                                </div>
                                                <span class="small">{{ $org->created_at->format('d.m.Y') }}</span>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route($user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show', $org->id) }}" 
                                                   class="btn btn-sm btn-outline-info rounded-circle"
                                                   style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                   title="Просмотр">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route($user->isAdmin() ? 'admin.organization.edit' : 'manager.organization.edit', $org->id) }}" 
                                                   class="btn btn-sm btn-outline-warning rounded-circle"
                                                   style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                   title="Редактировать">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($user->isAdmin())
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger rounded-circle"
                                                            style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                            onclick="confirmDelete({{ $org->id }}, '{{ $org->name }}')"
                                                            title="Удалить">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $user->isAdmin() ? 10 : 9 }}" class="text-center py-5">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="bi bi-building fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="text-muted mb-3">Организации не найдены</h5>
                                            @if(($user->isAdmin() || $user->isManager()) && request()->anyFilled(['search', 'owner_search', 'manager_id', 'status']))
                                                <a href="{{ route($user->isAdmin() ? 'admin.organizations.list' : 'manager.organizations.list') }}" 
                                                   class="btn btn-primary">
                                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить фильтры
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагинация -->
                    @if($organizations->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Показано {{ $organizations->firstItem() ?? 0 }} - {{ $organizations->lastItem() ?? 0 }} 
                                из {{ $organizations->total() }} организаций
                            </div>
                            <div>
                                {{ $organizations->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Форма удаления (для админа) -->
@if($user->isAdmin())
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Удаление организации',
        html: `Вы уверены, что хотите удалить организацию <strong>${name}</strong>?<br><br>
               <span class="text-danger">Это действие удалит также владельца организации и всех сотрудников!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Да, удалить!',
        cancelButtonText: 'Отмена'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = '/{{ $user->isAdmin() ? "admin" : "manager" }}/organization/' + id + '/delete';
            form.submit();
        }
    });
}

// Инициализация тултипов Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endif

<style>
/* ТОЧНО КАК ВО ВСЕХ СТРАНИЦАХ */

/* Круги всегда идеальные */
.rounded-circle {
    border-radius: 50% !important;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* Карточки - скругление 1rem (16px) */
.card {
    border-radius: 1rem;
    transition: all 0.2s;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Бейджи - сильно скругленные 30px */
.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
    border-radius: 30px;
    padding: 0.35em 0.65em;
}

/* Заголовки карточек */
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

/* Градиент для заголовка */
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

/* Таблицы */
.table th {
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

/* Для кнопок действий */
.btn-sm.rounded-circle {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Для иконок в кружках */
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}

/* Для текста в кружках с буквами */
.rounded-circle.bg-primary, 
.rounded-circle.bg-success, 
.rounded-circle.bg-info,
.rounded-circle.bg-warning,
.rounded-circle.bg-danger {
    font-weight: 500;
}

/* Для форм */
.form-label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Для алертов */
.alert-info {
    background-color: #cff4fc;
    border-color: #b6effb;
    color: #055160;
    border-radius: 0.75rem;
}
</style>
@endsection