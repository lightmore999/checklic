@extends('layouts.app')

@section('title', 'Панель сотрудника')

@section('content')
<div class="text-center py-5">
    <h1>Панель сотрудника организации</h1>
    <p class="lead">Здесь будет создание отчетов</p>
    <p>Вы вошли как: <strong>{{ Auth::user()->name }}</strong></p>
    <p>Роль: <span class="badge bg-warning">Сотрудник организации</span></p>
</div>
@endsection