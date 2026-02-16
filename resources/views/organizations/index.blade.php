@extends('layouts.app')

@section('title', 'Организации')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-building me-2"></i>
                        @if($user->isAdmin())
                            Все организации
                        @else
                            Мои организации
                        @endif
                        <span class="badge bg-primary ms-2">{{ $organizations->total() }}</span>
                    </h3>
                    <div>
                        @if($user->isAdmin() || $user->isManager())
                            <a href="{{ route($user->isAdmin() ? 'admin.organization.create' : 'manager.organization.create') }}" 
                               class="btn btn-success btn-sm">
                                <i class="bi bi-plus-lg"></i> Создать организацию
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- ФОРМА ФИЛЬТРОВ -->
                    <form method="GET" class="mb-4" id="filterForm">
                        <div class="row g-3">
                            <!-- Поиск по названию или ИНН -->
                            <div class="col-md-4">
                                <label class="form-label">Поиск по названию или ИНН</label>
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Название или ИНН..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Поиск по названию организации или ИНН</small>
                            </div>
                            
                            <!-- Фильтр по дням подписки -->
                            <div class="col-md-2">
                                <label class="form-label">Дней до конца подписки</label>
                                <select name="subscription_days" class="form-select" onchange="this.form.submit()">
                                    <option value="">Все</option>
                                    <option value="0" {{ request('subscription_days') == '0' ? 'selected' : '' }}>
                                        Истекшие (0 дней)
                                    </option>
                                    <option value="7" {{ request('subscription_days') == '7' ? 'selected' : '' }}>
                                        Менее 7 дней
                                    </option>
                                    <option value="14" {{ request('subscription_days') == '14' ? 'selected' : '' }}>
                                        Менее 14 дней
                                    </option>
                                    <option value="30" {{ request('subscription_days') == '30' ? 'selected' : '' }}>
                                        Менее 30 дней
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Поиск по владельцу -->
                            <div class="col-md-3">
                                <label class="form-label">Владелец (имя или email)</label>
                                <input type="text" 
                                       name="owner_search" 
                                       class="form-control" 
                                       placeholder="Поиск владельца..." 
                                       value="{{ request('owner_search') }}"
                                       onchange="this.form.submit()">
                            </div>
                            
                            <!-- Фильтр по менеджеру (только для админа) -->
                            @if($user->isAdmin())
                            <div class="col-md-3">
                                <label class="form-label">Менеджер</label>
                                <select name="manager_id" class="form-select" onchange="this.form.submit()">
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
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <!-- Фильтр по статусу -->
                            <div class="col-md-2">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">Все</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активна</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивна</option>
                                </select>
                            </div>
                            
                            <!-- Сортировка -->
                            <div class="col-md-2">
                                <label class="form-label">Сортировка</label>
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>По дате создания</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>По названию</option>
                                    <option value="subscription_ends_at" {{ request('sort') == 'subscription_ends_at' ? 'selected' : '' }}>По дате подписки</option>
                                    <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>По статусу</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Направление</label>
                                <select name="direction" class="form-select" onchange="this.form.submit()">
                                    <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                                    <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 d-flex align-items-end">
                                <a href="{{ route($user->isAdmin() ? 'admin.organizations.list' : 'manager.organizations.list') }}" 
                                   class="btn btn-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i> Сбросить фильтры
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Активные фильтры -->
                    @if(request()->anyFilled(['search', 'subscription_days', 'owner_search', 'manager_id', 'status']))
                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <i class="bi bi-funnel me-1"></i>
                                <span>Активные фильтры:</span>
                                @if(request('search'))
                                    <span class="badge bg-info text-white">Поиск: "{{ request('search') }}"</span>
                                @endif
                                @if(request('subscription_days'))
                                    <span class="badge bg-info text-white">
                                        Подписка ≤ {{ request('subscription_days') }} дней
                                    </span>
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
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Название / ИНН</th>
                                    <th>Владелец</th>
                                    @if($user->isAdmin())
                                        <th>Менеджер</th>
                                    @endif
                                    <th>Статус</th>
                                    <th>Подписка до</th>
                                    <th>Осталось дней</th>
                                    <th class="text-center">Сотрудников</th>
                                    <th class="text-center">Лимит</th>
                                    <th>Создана</th>
                                    <th class="text-center" style="min-width: 130px;">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organizations as $org)
                                    @php
                                        $remainingDays = $org->subscription_ends_at 
                                            ? now()->diffInDays($org->subscription_ends_at, false) 
                                            : null;
                                            
                                        $daysClass = 'success';
                                        if ($remainingDays !== null) {
                                            if ($remainingDays < 0) $daysClass = 'danger';
                                            elseif ($remainingDays <= 7) $daysClass = 'warning';
                                            elseif ($remainingDays <= 30) $daysClass = 'info';
                                        }
                                        
                                        $currentEmployees = $org->members ? $org->members->count() : 0;
                                    @endphp
                                    <tr>
                                        <td>#{{ $org->id }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $org->name }}</div>
                                            @if($org->inn)
                                                <small class="text-muted">
                                                    <i class="bi bi-file-text"></i> ИНН: {{ $org->inn }}
                                                </small>
                                            @endif
                                            @if($org->subscription_ends_at && $remainingDays !== null && $remainingDays < 0)
                                                <div class="mt-1">
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-exclamation-triangle"></i> Просрочена
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($org->owner && $org->owner->user)
                                                <div class="fw-bold">{{ $org->owner->user->name }}</div>
                                                <small class="text-muted">{{ $org->owner->user->email }}</small>
                                            @else
                                                <span class="text-muted fst-italic">Не назначен</span>
                                            @endif
                                        </td>
                                        
                                        @if($user->isAdmin())
                                            <td>
                                                @if($org->manager)
                                                    <div class="fw-bold">{{ $org->manager->name }}</div>
                                                    <small class="text-muted">{{ $org->manager->email }}</small>
                                                @else
                                                    <span class="text-muted fst-italic">Не назначен</span>
                                                @endif
                                            </td>
                                        @endif
                                        
                                        <td>
                                            @php
                                                $statusConfig = [
                                                    'active' => ['class' => 'success', 'text' => 'Активна', 'icon' => 'check-circle'],
                                                    'suspended' => ['class' => 'warning', 'text' => 'Приостановлена', 'icon' => 'pause-circle'],
                                                    'expired' => ['class' => 'danger', 'text' => 'Истекла', 'icon' => 'x-circle'],
                                                    'inactive' => ['class' => 'secondary', 'text' => 'Неактивна', 'icon' => 'dash-circle'],
                                                ];
                                                $config = $statusConfig[$org->status] ?? ['class' => 'secondary', 'text' => $org->status, 'icon' => 'question-circle'];
                                            @endphp
                                            <span class="badge bg-{{ $config['class'] }}">
                                                <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        
                                        <td>
                                            @if($org->subscription_ends_at)
                                                {{ $org->subscription_ends_at->format('d.m.Y') }}
                                            @else
                                                <span class="text-muted fst-italic">Бессрочно</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            @if($remainingDays !== null)
                                                <span class="badge bg-{{ $daysClass }}">
                                                    {{ $remainingDays >= 0 ? $remainingDays : 0 }} дн.
                                                </span>
                                                @if($remainingDays < 0)
                                                    <br><small class="text-danger">просрочено на {{ abs($remainingDays) }} дн.</small>
                                                @endif
                                            @else
                                                <span class="text-muted">∞</span>
                                            @endif
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
                                        
                                        <td>{{ $org->created_at->format('d.m.Y') }}</td>
                                        
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route($user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show', $org->id) }}" 
                                                class="btn btn-sm btn-info" 
                                                title="Просмотр"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route($user->isAdmin() ? 'admin.organization.edit' : 'manager.organization.edit', $org->id) }}" 
                                                class="btn btn-sm btn-warning"  
                                                title="Редактировать"
                                                data-bs-toggle="tooltip">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($user->isAdmin())
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger"  
                                                            onclick="confirmDelete({{ $org->id }}, '{{ $org->name }}')"
                                                            title="Удалить"
                                                            data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $user->isAdmin() ? 11 : 10 }}" class="text-center py-5">
                                            <i class="bi bi-building fs-1 text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">Организации не найдены</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагинация -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="text-muted mb-0">
                                Показано {{ $organizations->firstItem() ?? 0 }} - {{ $organizations->lastItem() ?? 0 }} 
                                из {{ $organizations->total() }} организаций
                            </p>
                        </div>
                        <div>
                            {{ $organizations->appends(request()->query())->links() }}
                        </div>
                    </div>
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

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    .badge {
        font-size: 85%;
    }
    .btn-sm {
        line-height: 1;
    }
    .btn-sm i {
        font-size: 1rem;
    }
</style>
@endpush
@endsection