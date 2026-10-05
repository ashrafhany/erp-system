@extends('layouts.app')
@php($editing = $role->exists)
@section('title', $editing ? 'تعديل دور' : 'إضافة دور')
@section('page-title', $editing ? 'تعديل دور' : 'إضافة دور')
@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $editing ? route('roles.update', $role) : route('roles.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="mb-4"><label class="form-label" for="name">اسم الدور</label><input class="form-control" id="name" name="name" value="{{ old('name', $role->name) }}" required @readonly($role->id === 1)></div>
        @if($role->id === 1)<div class="alert alert-info">اسم دور المدير و guard الخاص به وصلاحية super admin ثابتة. يمكنك تعديل باقي الصلاحيات.</div>@endif
        <div class="mb-3"><button class="btn btn-outline-primary btn-sm" type="button" id="toggle-all-permissions" aria-pressed="false">تحديد الكل</button></div>
        @foreach($groups as $group => $permissions)
            <fieldset class="border rounded p-3 mb-3"><legend class="float-none w-auto px-2 fs-6">{{ $group }}</legend>
                <div class="row">
                    @foreach($permissions as $permission => $label)
                        <div class="col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', $role->permissions->pluck('name')->all())))> {{ $label }}</label></div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <button class="btn btn-primary" type="submit">حفظ</button>
        <a class="btn btn-secondary" href="{{ route('roles.index') }}">رجوع</a>
    </form>
</div></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('toggle-all-permissions');
    const checkboxes = [...document.querySelectorAll('input[name="permissions[]"]')];
    const refresh = () => {
        const allSelected = checkboxes.length > 0 && checkboxes.every(checkbox => checkbox.checked);
        button.textContent = allSelected ? 'إلغاء تحديد الكل' : 'تحديد الكل';
        button.setAttribute('aria-pressed', String(allSelected));
    };
    button.addEventListener('click', () => {
        const selectAll = !checkboxes.every(checkbox => checkbox.checked);
        checkboxes.forEach(checkbox => { checkbox.checked = selectAll; });
        refresh();
    });
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', refresh));
    refresh();
});
</script>
@endpush
