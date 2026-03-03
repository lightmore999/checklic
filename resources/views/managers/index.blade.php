@extends('layouts.app')

@section('title', 'Управление менеджерами')
@section('page-icon', 'bi-people')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-people text-primary me-2"></i>
            Управление менеджерами
            @if($managers->total() > 0)
                <span class="badge bg-primary ms-2">{{ $managers->total() }}</span>
            @endif
        </h1>
        <a href="{{ route('admin.managers.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-2"></i>
            Создать менеджера
        </a>
    </div>

    <!-- Флеш-сообщения -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Форма фильтров -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i> Фильтры
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.managers.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Поиск по имени или email -->
                    <div class="col-md-4">
                        <label class="form-label">Поиск</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Имя или email..." value="{{ request('search') }}">
                    </div>
                    
                    <!-- Фильтр по статусу -->
                    <div class="col-md-3">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="">Все</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                        </select>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="col-md-5 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Применить
                            </button>
                            <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица менеджеров -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i> Список менеджеров
            </h5>
            <div class="text-muted">
                Всего: {{ $managers->total() }} менеджеров
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($managers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="min-width: 200px;">Менеджер</th>
                                <th style="min-width: 150px;">Контактные данные</th>
                                <th style="min-width: 100px;">Статус</th>
                                <th style="min-width: 100px;">Организации</th>
                                <th style="min-width: 100px;">Подписки</th>
                                <th style="min-width: 150px;">Дата создания</th>
                                <th style="width: 120px;" class="text-center">Действия</th>
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
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 40px; height: 40px; color: white; font-size: 1rem;">
                                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.managers.show', $manager->id) }}" class="fw-bold text-decoration-none">
                                                    {{ $manager->name }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <i class="bi bi-envelope me-1"></i> {{ $manager->email }}<br>
                                            @if($manager->phone)
                                                <i class="bi bi-telephone me-1"></i> {{ $manager->phone }}
                                            @else
                                                <span class="text-muted"><i class="bi bi-telephone-x me-1"></i> Не указан</span>
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
                                        <div>
                                            <span class="badge bg-info">{{ $manager->organizations_count }}</span>
                                            @if($manager->active_organizations_count > 0)
                                                <br><small class="text-muted">{{ $manager->active_organizations_count }} активных</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-primary">{{ $manager->subscriptions_count }}</span>
                                            @if($manager->active_subscriptions_count > 0)
                                                <br><small class="text-muted">{{ $manager->active_subscriptions_count }} активных</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $manager->created_at->format('d.m.Y') }}</div>
                                        <small class="text-muted">{{ $manager->created_at->format('H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.managers.show', $manager->id) }}" 
                                               class="btn btn-sm btn-info rounded-circle d-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px;"
                                               title="Просмотр"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('admin.managers.edit', $manager->id) }}" 
                                               class="btn btn-sm btn-warning rounded-circle d-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px;"
                                               title="Редактировать"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            @if($manager->id != Auth::id())
                                                <form action="{{ route('admin.managers.toggle-status', $manager->id) }}" 
                                                      method="POST" class="d-inline toggle-status-form">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-{{ $manager->is_active ? 'warning' : 'success' }} rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;"
                                                            title="{{ $manager->is_active ? 'Деактивировать' : 'Активировать' }}"
                                                            data-bs-toggle="tooltip">
                                                        <i class="bi bi-toggle-{{ $manager->is_active ? 'off' : 'on' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px;"
                                                        title="Удалить"
                                                        data-bs-toggle="tooltip"
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
                    <div class="mb-4">
                        <i class="bi bi-people display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Менеджеры не найдены</h4>
                    <p class="text-muted mb-4">
                        @if(request()->anyFilled(['search', 'status']))
                            Попробуйте изменить параметры фильтрации
                        @else
                            Создайте первого менеджера
                        @endif
                    </p>
                    @if(!request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.managers.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-lg me-2"></i> Создать менеджера
                        </a>
                    @else
                        <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Сбросить фильтры
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
                        Показано с {{ $managers->firstItem() }} по {{ $managers->lastItem() }} 
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Удаление менеджера
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить менеджера <strong id="deleteManagerName"></strong>?</p>
                <p class="text-danger mb-0"><small>Это действие также удалит все данные, связанные с менеджером.</small></p>
            </div>
            <div class="modal-footer">
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
            
            if (confirm(`Вы уверены, что хотите ${action.toLowerCase()} этого менеджера?`)) {
                this.submit();
            }
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
    });
</script>
@endpush