@extends('layouts.app')

@section('title', 'إضافة عميل جديد')
@section('page-title', 'إضافة عميل جديد')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-plus me-2"></i>
                    بيانات العميل الجديد
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_code" class="form-label">رمز العميل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_code') is-invalid @enderror"
                                   id="customer_code" name="customer_code" value="{{ old('customer_code') }}" required>
                            @error('customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">اسم الشركة</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                               id="company_name" name="company_name" value="{{ old('company_name') }}">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">العنوان</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tax_number" class="form-label">الرقم الضريبي</label>
                            <input type="text" class="form-control @error('tax_number') is-invalid @enderror"
                                   id="tax_number" name="tax_number" value="{{ old('tax_number') }}">
                            @error('tax_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="credit_limit" class="form-label">حد الائتمان</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('credit_limit') is-invalid @enderror"
                                       id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}">
                                <span class="input-group-text">ج.م</span>
                            </div>
                            @error('credit_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            حفظ العميل
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// إنشاء رمز عميل تلقائي
document.addEventListener('DOMContentLoaded', function() {
    const customerCodeField = document.getElementById('customer_code');
    if (!customerCodeField.value) {
        // إنشاء رمز عميل تلقائي بناءً على التاريخ والوقت
        const now = new Date();
        const year = now.getFullYear().toString().slice(-2);
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const time = now.getTime().toString().slice(-4);

        customerCodeField.value = `CUST${year}${month}${day}${time}`;
    }
});
</script>
@endpush
