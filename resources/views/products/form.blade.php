@extends('layouts.app')

@section('title', $product->exists ? 'تعديل منتج' : 'إضافة منتج')
@section('page-title', $product->exists ? 'تعديل منتج' : 'إضافة منتج')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" method="POST">
            @csrf
            @if($product->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">اسم المنتج</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" maxlength="255" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="sku" class="form-label">كود المنتج (SKU)</label>
                    <input id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror"
                           value="{{ old('sku', $product->sku) }}" maxlength="50" required>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">التصنيف</label>
                    <input id="category" name="category" class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category', $product->category) }}" maxlength="100" required>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="price" class="form-label">سعر البيع (ج.م)</label>
                    <input id="price" name="price" type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $product->price) }}" required>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="cost" class="form-label">التكلفة (ج.م)</label>
                    <input id="cost" name="cost" type="number" step="0.01" min="0" class="form-control @error('cost') is-invalid @enderror"
                           value="{{ old('cost', $product->cost) }}" required>
                    @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="tax_rate" class="form-label">نسبة الضريبة (%)</label>
                    <input id="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" class="form-control @error('tax_rate') is-invalid @enderror"
                           value="{{ old('tax_rate', $product->tax_rate) }}">
                    @error('tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">الحالة</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @unless($product->exists)
                <div class="col-md-3">
                    <label for="initial_stock" class="form-label">الرصيد الافتتاحي</label>
                    <input id="initial_stock" name="initial_stock" type="number" min="0" step="1" class="form-control @error('initial_stock') is-invalid @enderror"
                           value="{{ old('initial_stock', 0) }}">
                    @error('initial_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endunless

                <div class="col-md-{{ $product->exists ? 12 : 9 }}">
                    <label for="image_url" class="form-label">رابط الصورة</label>
                    <input id="image_url" name="image_url" type="url" class="form-control @error('image_url') is-invalid @enderror"
                           value="{{ old('image_url', $product->image_url) }}" maxlength="255">
                    @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">الوصف</label>
                    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <button class="btn btn-primary mt-3">حفظ</button>
        </form>
    </div>
</div>
@endsection
