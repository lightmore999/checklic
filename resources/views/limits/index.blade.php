@extends('layouts.app')

@section('title', 'Управление лимитами')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Лимиты</h3>
                    <div>
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                            <a href="{{ route('limits.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Создать лимит
                            </a>
                            <a href="{{ route('limits.bulk-create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-layer-group"></i> Массовое создание
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Пользователь</label>
                                <select name="user_id" class="form-control">
                                    <option value="">Все пользователи</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->getRoleDisplayName() }})
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
                                <label>Дата</label>
                                <input type="date" name="date_created" class="form-control" value="{{ request('date_created') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="">Все</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                                    <option value="exhausted" {{ request('status') == 'exhausted' ? 'selected' : '' }}>Исчерпанные</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-info btn-block">Фильтровать</button>
                                <a href="{{ route('limits.index') }}" class="btn btn-secondary ml-2">Сбросить</a>
                            </div>
                        </div>
                    </form>

                    <!-- Таблица лимитов -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Пользователь</th>
                                    <th>Тип отчета</th>
                                    <th>Количество</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Создан</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($limits as $limit)
                                    <tr>
                                        <td>{{ $limit->id }}</td>
                                        <td>
                                            <strong>{{ $limit->user->name }}</strong><br>
                                            <small class="text-muted">{{ $limit->user->email }}</small><br>
                                            <span class="badge bg-{{ $limit->user->getRoleColor() }}">
                                                {{ $limit->user->getRoleDisplayName() }}
                                            </span>
                                        </td>
                                        <td>{{ $limit->reportType->name ?? 'Не указан' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $limit->quantity > 0 ? 'success' : 'danger' }}">
                                                {{ $limit->quantity }}
                                            </span>
                                        </td>
                                        <td>{{ $limit->date_created->format('d.m.Y') }}</td>
                                        <td>
                                            @if($limit->isExhausted())
                                                <span class="badge bg-danger">Исчерпан</span>
                                            @else
                                                <span class="badge bg-success">Активен</span>
                                            @endif
                                        </td>
                                        <td>{{ $limit->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('limits.edit', $limit) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('limits.destroy', $limit) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить лимит?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('users.limits', $limit->user) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Лимиты не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center">
                        {{ $limits->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection