@extends('layouts.app')

@section('title', 'تفاصيل المنتج')
@section('page-title', $product->name)

@section('page-actions')
    <a href="{{ route('products.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>العودة للمنتجات
    </a>
    @can('products.update')
    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>تعديل
    </a>
    @endcan
@endsection

@section('content')
@php
    $types = ['initial' => 'رصيد افتتاحي', 'purchase' => 'توريد', 'sale' => 'بيع', 'return' => 'مرتجع', 'adjustment' => 'تسوية'];
@endphp
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-primary mb-3">بيانات المنتج</h6>
                <dl class="row mb-0">
                    <dt class="col-5">الكود</dt><dd class="col-7">{{ $product->sku }}</dd>
                    <dt class="col-5">التصنيف</dt><dd class="col-7">{{ $product->category }}</dd>
                    <dt class="col-5">سعر البيع</dt><dd class="col-7">{{ number_format($product->price, 2) }} ج.م</dd>
                    <dt class="col-5">التكلفة</dt><dd class="col-7">{{ number_format($product->cost, 2) }} ج.م</dd>
                    <dt class="col-5">الضريبة</dt><dd class="col-7">{{ rtrim(rtrim(number_format($product->tax_rate, 2), '0'), '.') }}%</dd>
                    <dt class="col-5">الحالة</dt>
                    <dd class="col-7">
                        <span class="badge {{ $product->status == 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $product->status == 'active' ? 'نشط' : 'غير نشط' }}</span>
                    </dd>
                    <dt class="col-5">الرصيد الحالي</dt>
                    <dd class="col-7">
                        <span class="badge {{ $stock <= 0 ? 'bg-danger' : ($stock < $threshold ? 'bg-warning text-dark' : 'bg-success') }}">{{ $stock }}</span>
                    </dd>
                </dl>
                @if($product->description)
                    <hr>
                    <p class="mb-0 text-muted">{{ $product->description }}</p>
                @endif
            </div>
        </div>

        @can('products.adjust')
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="text-primary mb-3">تسجيل حركة مخزون</h6>
                <form action="{{ route('products.inventory', $product) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="type" class="form-label">النوع</label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach(['purchase' => 'توريد (كمية موجبة)', 'sale' => 'بيع (كمية سالبة)', 'return' => 'مرتجع (كمية موجبة)', 'adjustment' => 'تسوية (موجبة أو سالبة)'] as $value => $label)
                                <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="quantity_change" class="form-label">الكمية</label>
                        <input id="quantity_change" name="quantity_change" type="number" step="1" class="form-control @error('quantity_change') is-invalid @enderror" value="{{ old('quantity_change') }}" required>
                        @error('quantity_change')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="unit_cost" class="form-label">تكلفة الوحدة (اختياري)</label>
                        <input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" class="form-control @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost') }}">
                        @error('unit_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">المرجع (اختياري)</label>
                        <input id="reference" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference') }}" maxlength="255">
                        @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                        <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary">تسجيل</button>
                </form>
            </div>
        </div>
        @endcan
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 text-primary"><i class="fas fa-history me-2"></i>سجل حركة المخزون</h6>
            </div>
            <div class="card-body">
                @if($movements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>الكمية</th>
                                    <th>تكلفة الوحدة</th>
                                    <th>المرجع / ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $types[$movement->type] ?? $movement->type }}</td>
                                    <td dir="ltr" class="text-end {{ $movement->quantity_change > 0 ? 'text-success' : 'text-danger' }}">{{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}</td>
                                    <td>{{ $movement->unit_cost !== null ? number_format($movement->unit_cost, 2) : '--' }}</td>
                                    <td>
                                        {{ $movement->reference }}
                                        @if($movement->notes)<small class="text-muted d-block">{{ $movement->notes }}</small>@endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">{{ $movements->links() }}</div>
                @else
                    <p class="text-muted text-center my-4">لا توجد حركات مخزون لهذا المنتج.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
