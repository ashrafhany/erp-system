@extends('layouts.app')
@section('title', 'المستخدمون')
@section('page-title', 'إدارة المستخدمين')
@section('page-actions')
    @can('users.create') <a href="{{ route('users.create') }}" class="btn btn-primary">إضافة مستخدم</a> @endcan
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
    <table class="table align-middle">
        <thead><tr><th>الاسم</th><th>البريد</th><th>نوع الحساب</th><th>الدور</th><th>الإجراءات</th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td><td>{{ $user->email }}</td>
                <td>{{ $user->account_type === 'admin' ? 'إداري' : 'موظف' }}</td>
                <td>{{ $user->roles_name ?: 'بدون دور' }}</td>
                <td>
                    @can('users.update') <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user) }}">تعديل</a> @endcan
                    @can('users.delete')
                        @if(!auth()->user()->is($user) && $user->id !== 1 && !$user->hasRole('super-admin'))
                            <form class="d-inline" action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('حذف المستخدم؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">لا يوجد مستخدمون.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $users->links() }}
</div></div>
@endsection
