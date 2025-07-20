@extends('layouts.app')

@section('title', 'إدارة العملاء')
@section('page-title', 'إدارة العملاء')

@section('page-actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        إضافة عميل جديد
    </a>
@endsection

@section('content')
<!-- فلاتر البحث -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">البحث</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="اسم العميل أو رمز العميل">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">الحالة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh me-2"></i>إعادة تعيين
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- قائمة العملاء -->
<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-user-tie me-2"></i>
            قائمة العملاء
        </h6>
    </div>
    <div class="card-body">
        @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رمز العميل</th>
                            <th>الاسم / الشركة</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>حد الائتمان</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $customer->customer_code }}</span>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $customer->name }}</div>
                                    @if($customer->company_name)
                                        <small class="text-muted">{{ $customer->company_name }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->credit_limit > 0)
                                    {{ number_format($customer->credit_limit, 2) }} ج.م
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->status == 'active')
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer) }}"
                                          method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- الترقيم -->
            <div class="d-flex justify-content-center mt-4">
                {{ $customers->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-tie fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">لا يوجد عملاء مسجلين</h5>
                <p class="text-muted">ابدأ بإضافة عميل جديد لإدارة قاعدة بيانات العملاء</p>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة عميل جديد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
