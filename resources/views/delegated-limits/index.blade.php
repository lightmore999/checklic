@extends('layouts.app')

@section('title', 'Делегированные лимиты')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Делегированные отчеты</h3>
                    <div>
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isOrgOwner())
                            <a href="{{ route('delegated-limits.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-share-alt"></i> Делегировать лимит
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Получатель</label>
                                <select name="user_id" class="form-control">
                                    <option value="">Все получатели</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->getRoleDisplayName() }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label>Владелец лимита</label>
                                <select name="owner_id" class="form-control">
                                    <option value="">Все владельцы</option>
                                    @foreach($users->whereIn('role', ['org_owner']) as $owner)
                                        <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label>Тип отчета</label>
                                <select name="report_type_id" class="form-control">
                                    <option value="">Все типы</option>
                                    @foreach($reportTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('report_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="">Все</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                                    <option value="exhausted" {{ request('status') == 'exhausted' ? 'selected' : '' }}>Исчерпанные</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                                </select>
                            </div>
                            
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-info btn-block">Фильтр</button>
                            </div>
                        </div>
                    </form>

                    <!-- Таблица делегированных лимитов -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Получатель</th>
                                    <th>Владелец лимита</th>
                                    <th>Тип отчета</th>
                                    <th>Количество</th>
                                    <th>Дата делегирования</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($delegatedLimits as $delegated)
                                    <tr>
                                        <td>{{ $delegated->id }}</td>
                                        <td>
                                            <strong>{{ $delegated->user->name }}</strong><br>
                                            <small class="text-muted">{{ $delegated->user->email }}</small><br>
                                            <span class="badge bg-{{ $delegated->user->getRoleColor() }}">
                                                {{ $delegated->user->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $delegated->limit->user->name ?? 'Не указан' }}</strong><br>
                                            <small class="text-muted">Владелец организации</small>
                                        </td>
                                        <td>
                                            {{ $delegated->limit->reportType->name ?? 'Не указан' }}<br>
                                            <small class="text-muted">Дата: {{ $delegated->limit->date_created->format('d.m.Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $delegated->quantity > 0 ? 'success' : 'danger' }}">
                                                {{ $delegated->quantity }}
                                            </span>
                                        </td>
                                        <td>{{ $delegated->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            @if($delegated->is_active)
                                                @if($delegated->isExhausted())
                                                    <span class="badge bg-danger">Исчерпан</span>
                                                @else
                                                    <span class="badge bg-success">Активен</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Неактивен</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('delegated-limits.show', $delegated) }}" class="btn btn-sm btn-info" title="Просмотреть">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('delegated-limits.edit', $delegated) }}" class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            
                                            @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isOrgOwner())
                                                @php
                                                    $canDelete = false;
                                                    if (auth()->user()->isAdmin()) {
                                                        $canDelete = true;
                                                    } elseif (auth()->user()->isOrgOwner() && $delegated->limit->user_id == auth()->id()) {
                                                        $canDelete = true;
                                                    } elseif (auth()->user()->isManager()) {
                                                        // Проверка для менеджера
                                                        $canDelete = true; // Упрощенно, нужно добавить логику проверки
                                                    }
                                                @endphp
                                                
                                                @if($canDelete)
                                                    <form action="{{ route('delegated-limits.destroy', $delegated) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Возвратить лимит владельцу?')"
                                                                title="Возвратить лимит">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-5">
                                                <i class="fas fa-share-alt fa-3x text-muted mb-3"></i>
                                                <h4 class="text-muted">Делегированных лимитов нет</h4>
                                                <p class="text-muted mb-4">Начните делегирование лимитов своим сотрудникам</p>
                                                @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isOrgOwner())
                                                    <a href="{{ route('delegated-limits.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-share-alt"></i> Делегировать лимит
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center">
                        {{ $delegatedLimits->links() }}
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Всего: {{ $delegatedLimits->total() }} | 
                                На странице: {{ $delegatedLimits->count() }}
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                @php
                                    $activeCount = $delegatedLimits->where('is_active', true)->count();
                                    $exhaustedCount = $delegatedLimits->where('quantity', '<=', 0)->count();
                                @endphp
                                Активных: {{ $activeCount }} | 
                                Исчерпано: {{ $exhaustedCount }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Подтверждение возврата лимита
        $('form[action*="delegated-limits"]').on('submit', function(e) {
            var button = $(this).find('button[type="submit"]');
            var delegatedId = $(this).attr('action').split('/').pop();
            
            if (!confirm('Вы уверены, что хотите вернуть лимит владельцу?')) {
                e.preventDefault();
                return false;
            }
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        });
    });
</script>
@endpush