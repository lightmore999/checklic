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
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary">
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
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $manager->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $manager->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- НОВОЕ ПОЛЕ: Номер телефона -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="phone" class="form-label">
                                Номер телефона <small class="text-muted">(необязательно)</small>
                            </label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $manager->phone) }}" 
                                   placeholder="+7 (999) 999-99-99">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Формат: международный или местный номер</div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">
                        <i class="bi bi-key"></i> Смена пароля (необязательно)
                    </h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            <div class="form-text">Оставьте пустым, если не нужно менять</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                            <input type="password" class="form-control" id="password_confirmation" 
                                   name="password_confirmation">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-text">
                                @if($manager->is_active)
                                    <span class="text-success">
                                        <i class="bi bi-check-circle"></i> Менеджер активен и может работать
                                    </span>
                                @else
                                    <span class="text-danger">
                                        <i class="bi bi-x-circle"></i> Менеджер деактивирован
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Отображение телефона в информации (если есть) -->
                            @if($manager->phone)
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-telephone"></i> Текущий телефон: {{ $manager->phone }}
                                </small>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Информация:</strong><br>
                                        • Создан: {{ $manager->created_at->format('d.m.Y H:i') }}<br>
                                        • Роль: {{ $manager->getRoleDisplayName() }}<br>
                                        • ID: {{ $manager->id }}<br>
                                        • Статус: {{ $manager->is_active ? 'Активен' : 'Неактивен' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Отмена
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

@push('scripts')
<!-- Если вы используете маску для телефона -->
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Inputmask({ 
            mask: ['+7 (999) 999-99-99', '+375 (99) 999-99-99', '+999 (99) 999-99-99'],
            keepStatic: true,
            showMaskOnHover: false,
            clearIncomplete: true
        }).mask(document.getElementById('phone'));
    });
</script>
@endpush