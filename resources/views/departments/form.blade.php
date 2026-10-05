@extends('layouts.app')
@section('title', $department->exists ? 'تعديل قسم' : 'إضافة قسم')
@section('page-title', $department->exists ? 'تعديل قسم' : 'إضافة قسم')
@section('page-actions')<a href="{{ route('departments.index') }}" class="btn btn-secondary">رجوع</a>@endsection
@section('content')
<div class="card"><div class="card-body">
    <form action="{{ $department->exists ? route('departments.update', $department) : route('departments.store') }}" method="POST">
        @csrf
        @if($department->exists) @method('PUT') @endif
        <label for="name" class="form-label">اسم القسم</label>
        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" maxlength="255" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <button class="btn btn-primary mt-3">حفظ</button>
    </form>
</div></div>
@endsection
