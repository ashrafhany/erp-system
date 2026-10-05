@extends('layouts.app')
@php($editing = $user->exists)
@section('title', $editing ? 'تعديل مستخدم' : 'إضافة مستخدم')
@section('page-title', $editing ? 'تعديل مستخدم' : 'إضافة مستخدم')
@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="mb-3"><label class="form-label" for="name">الاسم</label><input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
        <div class="mb-3"><label class="form-label" for="email">البريد الإلكتروني</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required @readonly($user->id === 1)></div>
        <div class="mb-3"><label class="form-label" for="account_type">نوع الحساب</label><select class="form-select" id="account_type" name="account_type" required @disabled($user->id === 1)><option value="employee" @selected(old('account_type', $user->account_type ?? 'employee') === 'employee')>موظف</option><option value="admin" @selected(old('account_type', $user->account_type) === 'admin')>إداري</option></select>@if($user->id === 1)<input type="hidden" name="account_type" value="admin">@endif</div>
        <div class="mb-3"><label class="form-label" for="password">كلمة المرور {{ $editing ? '(اتركها فارغة للإبقاء عليها)' : '' }}</label><input class="form-control" type="password" id="password" name="password" {{ $editing ? '' : 'required' }} minlength="6" autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label" for="password_confirmation">تأكيد كلمة المرور</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"></div>
        @can('roles.update')
            <div class="mb-3"><label class="form-label" for="role_id">الدور الأساسي</label><select class="form-select" id="role_id" name="role_id"><option value="">بدون دور</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((int) old('role_id', $user->roles->first()?->id) === $role->id)>{{ $role->name }}</option>@endforeach</select></div>
        @endcan
        <button class="btn btn-primary" type="submit">حفظ</button>
        <a class="btn btn-secondary" href="{{ route('users.index') }}">رجوع</a>
    </form>
</div></div>
@endsection
