@extends('layouts.app')
@section('title', 'الأدوار')
@section('page-title', 'الأدوار والصلاحيات')
@section('page-actions')
    @can('roles.create') <a href="{{ route('roles.create') }}" class="btn btn-primary">إضافة دور</a> @endcan
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
    <table class="table align-middle"><thead><tr><th>الدور</th><th>المستخدمون</th><th>الإجراءات</th></tr></thead><tbody>
        @foreach($roles as $role)
        <tr><td>{{ $role->name }}</td><td>{{ $role->users_count }}</td><td>
            @can('roles.update') <a class="btn btn-sm btn-outline-primary" href="{{ route('roles.edit', $role) }}">تعديل الصلاحيات</a> @endcan
            @if($role->id !== 1 && $role->name !== 'super-admin')
                @can('roles.delete')
                    @if($role->users_count === 0)
                        <form class="d-inline" action="{{ route('roles.destroy', $role) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="هل أنت متأكد من حذف هذا الدور؟">حذف</button></form>
                    @endif
                @endcan
            @endif
        </td></tr>
        @endforeach
    </tbody></table>
</div></div>
@endsection
