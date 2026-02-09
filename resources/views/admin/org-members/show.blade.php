@extends('layouts.app')

@section('title', 'Профиль сотрудника')
@section('page-icon', 'bi-person')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-person text-info me-2"></i>
        Профиль сотрудника
    </h5>
    <div>
        <a href="{{ route('admin.organization.show', $organization->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к организации
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-info me-2"></i>
                    Основная информация
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3" 
                         style="width: 60px; height: 60px; color: white; font-size: 1.5rem;">
                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $member->user->name }}</h4>
                        <div class="text-muted">{{ $member->user->email }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Роль:</div>
                        <span class="badge bg-info">Сотрудник</span>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Статус:</div>
                        @if($member->is_active && $member->user->is_active)
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-danger">Неактивен</span>
                        @endif
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Организация:</div>
                        <div>{{ $organization->name }}</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Начальник:</div>
                        <div>
                            @if($member->boss)
                                {{ $member->boss->name }}
                            @else
                                <span class="text-muted">Не назначен</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Менеджер:</div>
                        <div>{{ $member->manager->name ?? 'Не назначен' }}</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Зарегистрирован:</div>
                        <div>{{ $member->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Действия -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>
                    Действия
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('admin.org-members.toggle-status', [$organization->id, $member->id]) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-{{ $member->is_active ? 'warning' : 'success' }} w-100 mb-2">
                            <i class="bi bi-{{ $member->is_active ? 'ban' : 'check' }} me-1"></i>
                            {{ $member->is_active ? 'Деактивировать' : 'Активировать' }}
                        </button>
                    </form>
                    
                    <form method="POST" action="{{ route('admin.org-members.delete', [$organization->id, $member->id]) }}"
                          onsubmit="return confirm('Удалить сотрудника {{ $member->user->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Удалить сотрудника
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection