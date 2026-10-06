@extends('layouts.app')

@section('title', 'المنتجات والمخزون')
@section('page-title', 'المنتجات والمخزون')

@section('page-actions')
    @can('products.create')
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إضافة منتج جديد
    </a>
    @endcan
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">البحث</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="اسم المنتج أو الكود">
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">التصنيف</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">كل التصنيفات</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">الحالة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="low_stock" name="low_stock" value="1" {{ request()->boolean('low_stock') ? 'checked' : '' }}>
                        <label class="form-check-label" for="low_stock">مخزون منخفض (أقل من {{ $threshold }})</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh me-2"></i>إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-boxes me-2"></i>
            قائمة المنتجات
        </h6>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th>سعر البيع</th>
                            <th>التكلفة</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td class="fw-bold">{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category }}</td>
                            <td>{{ number_format($product->price, 2) }} ج.م</td>
                            <td>{{ number_format($product->cost, 2) }} ج.م</td>
                            <td>
                                <span class="badge {{ $product->stock <= 0 ? 'bg-danger' : ($product->stock < $threshold ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ (int) $product->stock }}
                                </span>
                            </td>
                            <td><x-status-badge type="product" :status="$product->status" /></td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" title="عرض" aria-label="عرض">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    @can('products.update')
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" title="تعديل" aria-label="تعديل">
                                        <i class="far fa-pen-to-square"></i>
                                    </a>
                                    @endcan
                                    @can('products.delete')
                                    <form action="{{ route('products.destroy', $product) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                data-confirm="هل أنت متأكد من حذف المنتج {{ $product->name }}؟"
                                                data-bs-toggle="tooltip" title="حذف" aria-label="حذف">
                                            <i class="far fa-trash-can"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $products->withQueryString()->links() }}
            </div>
        @elseif(request()->anyFilled(['search', 'category', 'status', 'low_stock']))
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-magnifying-glass"></i></div>
                <h5>لا توجد نتائج مطابقة</h5>
                <p class="text-muted">جرّب كلمات بحث أو فلاتر مختلفة</p>
                <a href="{{ route('products.index') }}" class="btn btn-soft">إعادة تعيين الفلاتر</a>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-boxes"></i></div>
                <h5>لا توجد منتجات</h5>
                @can('products.create')
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة منتج جديد
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
