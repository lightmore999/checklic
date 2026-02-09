@extends('layouts.app')

@section('title', 'Добавление сотрудника')
@section('page-icon', 'bi-person-plus')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <i class="bi bi-person-plus text-primary me-2"></i>
        Добавление сотрудника в организацию: {{ $organization->name }}
    </h5>
    <div>
        <a href="{{ route('admin.organization.show', $organization->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    Данные сотрудника
                </h6>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('admin.org-members.store', $organization->id) }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Имя *</label>
                        <input type="text" class="form-control @error('user.name') is-invalid @enderror" 
                               id="name" name="user[name]" 
                               value="{{ old('user.name') }}" 
                               placeholder="Введите имя" required>
                        @error('user.name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control @error('user.email') is-invalid @enderror" 
                               id="email" name="user[email]" 
                               value="{{ old('user.email') }}" 
                               placeholder="email@example.com" required>
                        @error('user.email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль *</label>
                        <input type="password" class="form-control @error('user.password') is-invalid @enderror" 
                               id="password" name="user[password]" 
                               placeholder="Минимум 8 символов" required>
                        @error('user.password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Подтверждение пароля *</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="user[password_confirmation]" 
                               placeholder="Повторите пароль" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Сотрудник будет добавлен в организацию <strong>{{ $organization->name }}</strong>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.organization.show', $organization->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Отмена
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Добавить сотрудника
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection