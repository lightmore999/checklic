@extends('layouts.app')

@section('title', 'Редактирование менеджера')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Редактирование менеджера
                </h5>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.managers.update', $manager->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Имя менеджера *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $manager->name) }}" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email', $manager->email) }}" required>
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">
                        <i class="bi bi-key"></i> Смена пароля (необязательно)
                    </h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Оставьте пустым, если не нужно менять</div>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                            <input type="password" class="form-control" id="password_confirmation" 
                                   name="password_confirmation">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">
                        <i class="bi bi-toggle2-on"></i> Статус аккаунта
                    </h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                       id="is_active" name="is_active" 
                                       value="1" {{ $manager->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Активный аккаунт
                                </label>
                            </div>
                            <div class="form-text">
                                @if($manager->is_active)
                                    <span class="text-success">Менеджер активен и может работать</span>
                                @else
                                    <span class="text-danger">Менеджер деактивирован</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Информация:</strong><br>
                                        • Создан: {{ $manager->created_at->format('d.m.Y H:i') }}<br>
                                        • Роль: Менеджер<br>
                                        • ID: {{ $manager->id }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection