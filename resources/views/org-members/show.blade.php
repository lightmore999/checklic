@extends('layouts.app')

@section('title', $member->user->name)

@section('content')
<div class="container-fluid">
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isManager = Auth::user()->isManager();
        $isOwner = Auth::user()->isOrgOwner();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-badge text-primary"></i> {{ $member->user->name }}
        </h1>
        <div>
            <a href="{{ route($routePrefix . 'organization.show', $organization->id) }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к организации
            </a>
            @if($isAdmin || $isManager)
                <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                   class="btn btn-primary ms-2">
                    <i class="bi bi-pencil"></i> Редактировать
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Левая колонка: Личная информация -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; color: white; font-size: 36px;">
                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                    </div>
                    <h4>{{ $member->user->name }}</h4>
                    <p class="text-muted">{{ $member->user->email }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-info">Сотрудник</span>
                        @if($member->user->is_active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-danger">Неактивен</span>
                        @endif
                    </div>
                    
                    <p class="text-muted small">
                        <i class="bi bi-calendar"></i> Зарегистрирован: {{ $member->user->created_at->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <!-- Организация -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Организация</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" 
                             style="width: 50px; height: 50px; color: white;">
                            <i class="bi bi-building"></i>
                        </div>
                        <h6>{{ $organization->name }}</h6>
                        @if($organization->status === 'active')
                            <span class="badge bg-success">Активна</span>
                        @elseif($organization->status === 'suspended')
                            <span class="badge bg-warning">Приостановлена</span>
                        @else
                            <span class="badge bg-danger">Истекла</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Действия для админа/менеджера -->
            @if($isAdmin || $isManager)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Действия</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route($routePrefix . 'org-members.edit', [$organization->id, $member->id]) }}" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Редактировать данные
                            </a>

                            <form action="{{ route($routePrefix . 'org-members.toggle-status', [$organization->id, $member->id]) }}" 
                                  method="POST">
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
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Рабочая информация</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Статус сотрудника:</th>
                                    <td>
                                        @if($member->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Добавлен:</th>
                                    <td>{{ $member->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Менеджер:</th>
                                    <td>
                                        @if($member->manager && $member->manager->user)
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 28px; height: 28px; color: white; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($member->manager->user->name, 0, 1)) }}
                                                </div>
                                                <div>{{ $member->manager->user->name }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Последнее обновление:</th>
                                    <td>{{ $member->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика (если есть) -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Статистика</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-0">0</h2>
                                    <small class="text-muted">Всего отчетов</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h2 class="text-success mb-0">0</h2>
                                    <small class="text-muted">За этот месяц</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h2 class="text-warning mb-0">0</h2>
                                    <small class="text-muted">В работе</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h2 class="text-info mb-0">0</h2>
                                    <small class="text-muted">Завершено</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($member->position || $member->phone)
                        <div class="alert alert-light mt-3">
                            <h6 class="alert-heading">Дополнительная информация:</h6>
                            @if($member->position)
                                <p class="mb-1"><strong>Должность:</strong> {{ $member->position }}</p>
                            @endif
                            @if($member->phone)
                                <p class="mb-0"><strong>Контактный телефон:</strong> {{ $member->phone }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@if($isAdmin || $isManager)

<!-- Форма для удаления -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(organizationId, memberId, name, prefix) {
    if (confirm(`Вы уверены, что хотите удалить сотрудника "${name}"? Это действие удалит его учетную запись и все связанные данные.`)) {
        const form = document.getElementById('delete-form');
        form.action = `/${prefix}/organization/${organizationId}/member/${memberId}/delete`;
        form.submit();
    }
}
</script>
@endif
@endsection