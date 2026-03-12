@extends('layouts.app')

@section('title', 'Управление менеджерами')
@section('page-icon', 'bi-people')

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
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h1 class="h2 mb-2">Управление менеджерами</h1>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-danger px-3 py-2">
                                        <i class="bi bi-people me-1"></i>Всего: {{ $managers->total() }}
                                    </span>
                                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar me-1"></i>{{ now()->format('d.m.Y') }}
                                    </span>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}" class="text-white opacity-75">
                                                Панель админа
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">
                                            Менеджеры
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.managers.create') }}" class="btn btn-light">
                                <i class="bi bi-plus-lg me-2"></i>Создать менеджера
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Успешно!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Ошибка!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Карточка с фильтрами -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>
                Фильтры
            </h5>
        </div>
        <div class="card-body pt-3">
            <form method="GET" action="{{ route('admin.managers.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Поиск по имени или email -->
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Поиск</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Имя или email..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <small class="text-muted">Поиск по имени или email менеджера</small>
                    </div>
                    
                    <!-- Фильтр по статусу -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Статус</label>
                        <select name="status" class="form-select">
                            <option value="">Все</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                        </select>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Применить
                            </button>
                            <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Активные фильтры -->
            @if(request()->anyFilled(['search', 'status']))
                <div class="alert alert-info py-2 mt-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <i class="bi bi-funnel-fill me-1"></i>
                        <span class="fw-semibold">Активные фильтры:</span>
                        @if(request('search'))
                            <span class="badge bg-info text-white">Поиск: "{{ request('search') }}"</span>
                        @endif
                        @if(request('status'))
                            <span class="badge bg-info text-white">Статус: {{ request('status') == 'active' ? 'Активные' : 'Неактивные' }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Таблица менеджеров -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul text-primary me-2"></i>
                Список менеджеров
            </h5>
            <span class="badge bg-primary">{{ $managers->total() }} всего</span>
        </div>
        
        <div class="card-body p-0">
            @if($managers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="min-width: 200px;">Менеджер</th>
                                <th style="min-width: 200px;">Контактные данные</th>
                                <th style="min-width: 100px;">Статус</th>
                                <th style="min-width: 100px;">Организации</th>
                                <th style="min-width: 100px;">Подписки</th>
                                <th style="min-width: 120px;">Дата создания</th>
                                <th style="width: 160px;" class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($managers as $manager)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#{{ $manager->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 40px; height: 40px; font-size: 1.2rem; color: #0d6efd;">
                                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.managers.show', $manager->id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $manager->name }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="small">
                                                <i class="bi bi-envelope text-muted me-1"></i>{{ $manager->email }}
                                            </span>
                                            @if($manager->phone)
                                                <span class="small">
                                                    <i class="bi bi-telephone text-muted me-1"></i>{{ $manager->phone }}
                                                </span>
                                            @else
                                                <span class="small text-muted">
                                                    <i class="bi bi-telephone-x me-1"></i>Не указан
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($manager->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-info">{{ $manager->organizations_count }} шт.</span>
                                            @if($manager->active_organizations_count > 0)
                                                <small class="text-muted">{{ $manager->active_organizations_count }} активных</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-primary">{{ $manager->subscriptions_count }} шт.</span>
                                            @if($manager->active_subscriptions_count > 0)
                                                <small class="text-muted">{{ $manager->active_subscriptions_count }} активных</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 28px; height: 28px;">
                                                <i class="bi bi-calendar text-primary small"></i>
                                            </div>
                                            <div>
                                                <span class="small">{{ $manager->created_at->format('d.m.Y') }}</span>
                                                <small class="text-muted d-block">{{ $manager->created_at->format('H:i') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.managers.show', $manager->id) }}" 
                                               class="btn btn-sm btn-outline-info rounded-circle"
                                               style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                               title="Просмотр">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('admin.managers.edit', $manager->id) }}" 
                                               class="btn btn-sm btn-outline-warning rounded-circle"
                                               style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                               title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            @if($manager->id != Auth::id())
                                                <form action="{{ route('admin.managers.toggle-status', $manager->id) }}" 
                                                      method="POST" class="d-inline toggle-status-form">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-{{ $manager->is_active ? 'warning' : 'success' }} rounded-circle"
                                                            style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                            title="{{ $manager->is_active ? 'Деактивировать' : 'Активировать' }}">
                                                        <i class="bi bi-toggle-{{ $manager->is_active ? 'off' : 'on' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger rounded-circle"
                                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                        title="Удалить"
                                                        onclick="confirmDelete({{ $manager->id }}, '{{ $manager->name }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-people fs-1 text-secondary"></i>
                    </div>
                    <h5 class="text-muted mb-3">Менеджеры не найдены</h5>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['search', 'status']))
                            Попробуйте изменить параметры фильтрации
                        @else
                            Создайте первого менеджера
                        @endif
                    </p>
                    @if(!request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i> Создать менеджера
                        </a>
                    @else
                        <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i> Сбросить фильтры
                        </a>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Пагинация -->
        @if($managers->hasPages() && $managers->count() > 0)
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small order-2 order-md-1">
                        Показано {{ $managers->firstItem() }} - {{ $managers->lastItem() }} 
                        из {{ $managers->total() }} менеджеров
                    </div>
                    <div class="order-1 order-md-2">
                        {{ $managers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @elseif($managers->total() > 0 && !$managers->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="text-muted small text-center">
                    Всего {{ $managers->total() }} менеджеров
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteManagerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Удаление менеджера
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                    </div>
                    <p>Вы уверены, что хотите удалить менеджера <strong id="deleteManagerName"></strong>?</p>
                    <p class="text-danger mb-0"><small>Это действие также удалит все данные, связанные с менеджером.</small></p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <form action="" method="POST" id="deleteManagerForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

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
.bg-gradient-danger {
    background: linear-gradient(135deg,  #fd7e14 0%, #e96b02 100%);
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

.alert-success {
    border-radius: 0.75rem;
}

.alert-danger {
    border-radius: 0.75rem;
}

/* Модальное окно */
.modal-content {
    border-radius: 1rem;
    border: none;
}

.modal-header {
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}
</style>

@push('scripts')
<script>
    // Функция подтверждения удаления
    window.confirmDelete = function(id, name) {
        document.getElementById('deleteManagerName').textContent = name;
        document.getElementById('deleteManagerForm').action = '{{ url("admin/managers") }}/' + id + '/delete';
        new bootstrap.Modal(document.getElementById('deleteManagerModal')).show();
    };
    
    // Подтверждение изменения статуса
    document.querySelectorAll('.toggle-status-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('button');
            const action = button.title;
            
            Swal.fire({
                title: 'Подтверждение',
                text: `Вы уверены, что хотите ${action.toLowerCase()} этого менеджера?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: button.classList.contains('btn-outline-warning') ? '#ffc107' : '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Да, продолжить',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    
    // Авто-сабмит формы при изменении полей
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status"]');
        
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        }
        
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
        
        // Инициализация тултипов
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<!-- SweetAlert2 для красивых подтверждений -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush