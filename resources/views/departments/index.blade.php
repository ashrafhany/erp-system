@extends('layouts.app')
@section('title', 'الأقسام')
@section('page-title', 'الأقسام')
@section('page-actions')
    @can('departments.create')<a href="{{ route('departments.create') }}" class="btn btn-primary">إضافة قسم</a>@endcan
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
    <table class="table table-striped align-middle">
        <thead><tr><th>القسم</th><th>عدد الموظفين</th><th>الإجراءات</th></tr></thead>
        <tbody>
        @forelse($departments as $department)
            <tr><td>{{ $department->name }}</td><td>{{ $counts[$department->name] ?? 0 }}</td><td>
                @can('departments.update')<a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">تعديل</a>@endcan
                @can('departments.delete')
                    @if(!($counts[$department->name] ?? 0))
                    <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف القسم؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    @endif
                @endcan
            </td></tr>
        @empty
            <tr><td colspan="3" class="text-center">لا توجد أقسام.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $departments->links() }}
</div></div>
@endsection
