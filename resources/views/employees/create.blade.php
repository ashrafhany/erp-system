@extends('layouts.app')

@section('title', 'إضافة موظف جديد')
@section('page-title', 'إضافة موظف جديد')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-plus me-2"></i>
                    بيانات الموظف الجديد
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">رقم الموظف <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('employee_id') is-invalid @enderror"
                                   id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">اسم العائلة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                   id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="hire_date" class="form-label">تاريخ التوظيف <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                   id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                            @error('hire_date')
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
                            <label for="department" class="form-label">القسم <span class="text-danger">*</span></label>
                            <select class="form-select @error('department') is-invalid @enderror"
                                    id="department" name="department" required>
                                <option value="">اختر القسم</option>
                                <option value="الإدارة" {{ old('department') == 'الإدارة' ? 'selected' : '' }}>الإدارة</option>
                                <option value="المحاسبة" {{ old('department') == 'المحاسبة' ? 'selected' : '' }}>المحاسبة</option>
                                <option value="الموارد البشرية" {{ old('department') == 'الموارد البشرية' ? 'selected' : '' }}>الموارد البشرية</option>
                                <option value="المبيعات" {{ old('department') == 'المبيعات' ? 'selected' : '' }}>المبيعات</option>
                                <option value="التسويق" {{ old('department') == 'التسويق' ? 'selected' : '' }}>التسويق</option>
                                <option value="تقنية المعلومات" {{ old('department') == 'تقنية المعلومات' ? 'selected' : '' }}>تقنية المعلومات</option>
                                <option value="خدمة العملاء" {{ old('department') == 'خدمة العملاء' ? 'selected' : '' }}>خدمة العملاء</option>
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">المنصب <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('position') is-invalid @enderror"
                                   id="position" name="position" value="{{ old('position') }}" required>
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="basic_salary" class="form-label">الراتب الأساسي <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('basic_salary') is-invalid @enderror"
                                       id="basic_salary" name="basic_salary" value="{{ old('basic_salary') }}" required>
                                <span class="input-group-text">ج.م</span>
                            </div>
                            @error('basic_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                <option value="terminated" {{ old('status') == 'terminated' ? 'selected' : '' }}>منتهي الخدمة</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            حفظ الموظف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
