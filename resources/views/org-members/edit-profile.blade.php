@extends('layouts.app')

@section('title', 'Редактирование профиля')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <i class="bi bi-person-gear text-warning"></i> Редактирование профиля
        </h1>
        <a href="{{ route('member.profile') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к профилю
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Личная информация</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('member.profile.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Новый пароль (оставьте пустым, если не хотите менять)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('member.profile') }}" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Информация о безопасности</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-shield-check"></i> Безопасность профиля</h6>
                        <ul class="mb-0">
                            <li>Используйте сложный пароль</li>
                            <li>Никому не сообщайте свои учетные данные</li>
                            <li>При смене email потребуется подтверждение</li>
                            <li>При смене пароля вы будете разлогинены со всех устройств</li>
                        </ul>
                    </div>

                    <div class="alert alert-light">
                        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Важная информация</h6>
                        <p class="mb-1"><strong>Текущий email:</strong> {{ $user->email }}</p>
                        <p class="mb-1"><strong>Роль:</strong> Сотрудник организации</p>
                        <p class="mb-0"><strong>Дата регистрации:</strong> {{ $user->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection