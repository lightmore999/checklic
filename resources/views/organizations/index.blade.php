@extends('layouts.app')

@section('title', 'Организации')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> 
                        @if($user->isAdmin())
                            Все организации
                        @else
                            Мои организации
                        @endif
                        <span class="badge bg-primary ml-2">{{ $organizations->total() }}</span>
                    </h3>
                    <div>
                        @if($user->isAdmin() || $user->isManager())
                            <a href="{{ route($user->isAdmin() ? 'admin.organization.create' : 'manager.organization.create') }}" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Создать организацию
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- ФОРМА ФИЛЬТРОВ -->
                    <form method="GET" class="mb-4" id="filterForm">
                        <div class="row">
                            <!-- Поиск по названию -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Поиск по названию</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               name="search" 
                                               class="form-control" 
                                               placeholder="Введите название организации..." 
                                               value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Фильтр по дням подписки -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Дней до конца подписки</label>
                                    <select name="subscription_days" class="form-control" onchange="this.form.submit()">
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
                            </div>
                            
                            <!-- Поиск по владельцу -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Владелец (имя или email)</label>
                                    <input type="text" 
                                           name="owner_search" 
                                           class="form-control" 
                                           placeholder="Поиск владельца..." 
                                           value="{{ request('owner_search') }}"
                                           onchange="this.form.submit()">
                                </div>
                            </div>
                            
                            <!-- Фильтр по менеджеру (только для админа) -->
                            @if($user->isAdmin())
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Менеджер</label>
                                    <select name="manager_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">Все менеджеры</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" 
                                                {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="row mt-2">
                            <!-- Фильтр по статусу -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Статус</label>
                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                        <option value="">Все</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активна</option>
                                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Приостановлена</option>
                                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Истекла</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивна</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Сортировка -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Сортировка</label>
                                    <select name="sort" class="form-control" onchange="this.form.submit()">
                                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>По дате создания</option>
                                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>По названию</option>
                                        <option value="subscription_ends_at" {{ request('sort') == 'subscription_ends_at' ? 'selected' : '' }}>По дате подписки</option>
                                        <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>По статусу</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Направление</label>
                                    <select name="direction" class="form-control" onchange="this.form.submit()">
                                        <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                                        <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-group">
                                    <a href="{{ route($user->isAdmin() ? 'admin.organizations.list' : 'manager.organizations.list') }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Сбросить фильтры
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Активные фильтры -->
                    @if(request()->anyFilled(['search', 'subscription_days', 'owner_search', 'manager_id', 'status']))
                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex align-items-center flex-wrap">
                                <i class="fas fa-filter mr-2"></i>
                                <span>Активные фильтры:</span>
                                @if(request('search'))
                                    <span class="badge badge-info ml-2">Название: "{{ request('search') }}"</span>
                                @endif
                                @if(request('subscription_days'))
                                    <span class="badge badge-info ml-2">
                                        Подписка ≤ {{ request('subscription_days') }} дней
                                    </span>
                                @endif
                                @if(request('owner_search'))
                                    <span class="badge badge-info ml-2">Владелец: "{{ request('owner_search') }}"</span>
                                @endif
                                @if(request('manager_id'))
                                    @php
                                        $managerName = $managers->firstWhere('id', request('manager_id'))?->name ?? 'Неизвестно';
                                    @endphp
                                    <span class="badge badge-info ml-2">Менеджер: {{ $managerName }}</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge badge-info ml-2">Статус: {{ request('status') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- ТАБЛИЦА ОРГАНИЗАЦИЙ -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Владелец</th>
                                    @if($user->isAdmin())
                                        <th>Менеджер</th>
                                    @endif
                                    <th>Статус</th>
                                    <th>Подписка до</th>
                                    <th>Осталось дней</th>
                                    <th>Сотрудников</th>
                                    <th>Создана</th>
                                    <th>Действия</th>
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
                                    @endphp
                                    <tr>
                                        <td>#{{ $org->id }}</td>
                                        <td>
                                            <strong>{{ $org->name }}</strong>
                                            @if($org->subscription_ends_at && $remainingDays !== null && $remainingDays < 0)
                                                <span class="badge bg-danger d-block mt-1">
                                                    <i class="fas fa-exclamation-triangle"></i> Просрочена
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($org->owner && $org->owner->user)
                                                <strong>{{ $org->owner->user->name }}</strong><br>
                                                <small>{{ $org->owner->user->email }}</small>
                                            @else
                                                <span class="text-muted">Не назначен</span>
                                            @endif
                                        </td>
                                        
                                        @if($user->isAdmin())
                                            <td>
                                                @if($org->manager)
                                                    <strong>{{ $org->manager->name }}</strong><br>
                                                    <small>{{ $org->manager->email }}</small>
                                                @else
                                                    <span class="text-muted">Не назначен</span>
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
                                            <span class="badge bg-{{ $config['class'] }}">
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        
                                        <td>
                                            @if($org->subscription_ends_at)
                                                {{ $org->subscription_ends_at->format('d.m.Y') }}
                                            @else
                                                <span class="text-muted">Бессрочно</span>
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
                                            <span class="badge bg-info">{{ $org->members->count() }}</span>
                                        </td>
                                        
                                        <td>{{ $org->created_at->format('d.m.Y') }}</td>
                                        
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route($user->isAdmin() ? 'admin.organization.show' : 'manager.organization.show', $org->id) }}" 
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route($user->isAdmin() ? 'admin.organization.edit' : 'manager.organization.edit', $org->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($user->isAdmin())
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete({{ $org->id }}, '{{ $org->name }}')"
                                                            title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $user->isAdmin() ? 10 : 9 }}" class="text-center py-4">
                                            <i class="fas fa-building fa-3x mb-3 text-muted"></i>
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
                        <div class="d-flex justify-content-center">
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
</script>
@endif

@push('styles')
<style>
    .badge {
        font-size: 85%;
        padding: 0.4em 0.6em;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .ml-2 {
        margin-left: 0.5rem;
    }
    .mr-2 {
        margin-right: 0.5rem;
    }
    .bg-success {
        background-color: #28a745 !important;
    }
    .bg-danger {
        background-color: #dc3545 !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    .bg-info {
        background-color: #17a2b8 !important;
    }
    .bg-secondary {
        background-color: #6c757d !important;
    }
</style>
@endpush
@endsection