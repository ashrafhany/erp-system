# 🏢 Mini ERP System - نظام ERP المصغّر

A comprehensive and integrated mini Enterprise Resource Planning (ERP) system built with Laravel 11, providing complete management for employees, payroll, attendance, customers, and invoicing.

## 📋 Table of Contents

- [Features](#-features)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Usage](#-usage)
- [System Interfaces](#-system-interfaces)
- [Database](#-database)
- [Security](#-security)
- [Contributing](#-contributing)
- [Support](#-support)

## ✨ Features

### 👥 Employee Management
- ✅ Add, edit, and delete employees
- ✅ Track personal and professional information
- ✅ Department and position management
- ✅ Employee status tracking (active/inactive/terminated)
- ✅ Comprehensive employee detail views

### ⏰ Attendance Management
- ✅ Employee check-in and check-out
- ✅ Automatic work hours calculation
- ✅ Attendance status tracking (present/absent/late/half-day)
- ✅ Daily and monthly attendance reports
- ✅ Advanced search filters

### 💰 Payroll Management
- ✅ Monthly salary calculations
- ✅ Overtime and allowance management
- ✅ Deduction and tax calculations
- ✅ Payroll approval and payment
- ✅ Detailed payroll reports

### 🤝 Customer Management
- ✅ Comprehensive customer database
- ✅ Contact and company information tracking
- ✅ Credit limit management
- ✅ Customer status and notes tracking

### 🧾 Invoice Management
- ✅ Create and manage invoices
- ✅ Multiple invoice items support
- ✅ Automatic tax and discount calculations
- ✅ Payment status tracking
- ✅ Partial and full payment recording

### 📊 Dashboard
- ✅ Comprehensive system statistics
- ✅ Key performance indicators
- ✅ Daily attendance monitoring
- ✅ Pending and overdue invoice tracking

## 🔧 System Requirements

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Laravel** 11.x
- **Database**: SQLite (default) or MySQL or PostgreSQL
- **Web Server**: Apache or Nginx or Laravel's built-in server

### Additional Requirements
- **Node.js** >= 16.0 (for frontend)
- **npm** or **yarn** (for package management)

## 🚀 Installation

### 1. Clone the Project
```bash
git clone https://github.com/your-username/mini-erp-system.git
cd mini-erp-system
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed with sample data
php artisan db:seed
```

### 5. Run the System
```bash
# Start Laravel server
php artisan serve

# Start Vite for frontend (in separate terminal)
npm run dev
```

The system will be available at: `http://localhost:8000`

## 📱 Usage

### System Access
1. Open your browser and navigate to `http://localhost:8000`
2. You will see the main dashboard
3. Use the sidebar menu to navigate between sections

### Sample Data
The system comes with comprehensive sample data:
- **5 employees** in different departments
- **4 customers** with complete information
- Sample attendance and payroll data

## 🖥️ System Interfaces

### Dashboard (`/`)
- Display general statistics
- Key performance indicators
- Latest invoices and activities

### Employee Management (`/employees`)
- **View Employees**: `/employees`
- **Add Employee**: `/employees/create`
- **Employee Details**: `/employees/{id}`
- **Edit Employee**: `/employees/{id}/edit`

### Attendance Management (`/attendance`)
- **Attendance Records**: `/attendance`
- **Add New Attendance**: `/attendance/create`
- **Quick Check-in**: `/attendance/checkin/{employee}`
- **Quick Check-out**: `/attendance/checkout/{employee}`

### Payroll Management (`/payroll`)
- **Payroll Records**: `/payroll`
- **Create New Payroll**: `/payroll/create`
- **Generate Automatic Payroll**: `/payroll/generate/{employee}`
- **Approve Payroll**: `/payroll/approve/{payroll}`

### Customer Management (`/customers`)
- **View Customers**: `/customers`
- **Add Customer**: `/customers/create`
- **Customer Details**: `/customers/{id}`

### Invoice Management (`/invoices`)
- **View Invoices**: `/invoices`
- **Create Invoice**: `/invoices/create`
- **Invoice Details**: `/invoices/{id}`
- **Add Items**: `/invoices/{id}/items`

## 🗄️ Database

### Main Tables

#### `employees` - Employees
```sql
- id (Unique identifier)
- employee_id (Employee number)
- first_name, last_name (Name)
- email (Email address)
- department, position (Department and position)
- basic_salary (Basic salary)
- hire_date (Hire date)
- status (Status)
```

#### `attendances` - Attendance
```sql
- id (Unique identifier)
- employee_id (Employee identifier)
- date (Date)
- check_in, check_out (Check-in and check-out time)
- total_hours (Total hours)
- status (Attendance status)
```

#### `payroll_records` - Payroll Records
```sql
- id (Unique identifier)
- employee_id (Employee identifier)
- payroll_month (Payroll month)
- basic_salary (Basic salary)
- overtime_amount (Overtime amount)
- allowances (Allowances)
- deductions (Deductions)
- net_salary (Net salary)
```

#### `customers` - Customers
```sql
- id (Unique identifier)
- customer_code (Customer code)
- name (Customer name)
- company_name (Company name)
- email, phone (Contact information)
- credit_limit (Credit limit)
```

#### `invoices` - Invoices
```sql
- id (Unique identifier)
- invoice_number (Invoice number)
- customer_id (Customer identifier)
- invoice_date, due_date (Invoice dates)
- total_amount (Total amount)
- paid_amount (Paid amount)
- status (Invoice status)
```

## 🔒 الأمان

### الحماية المطبقة
- **CSRF Protection** - حماية من هجمات CSRF
- **SQL Injection Prevention** - منع حقن SQL
- **XSS Protection** - حماية من XSS
- **Input Validation** - التحقق من صحة المدخلات
- **Secure Headers** - رؤوس الأمان

## 🛠️ التخصيص والتطوير

### إضافة ميزات جديدة
```bash
# إنشاء Migration جديد
php artisan make:migration create_new_table

# إنشاء Model
php artisan make:model ModelName -m

# إنشاء Controller
php artisan make:controller ControllerName --resource
```

### تخصيص التصميم
- الملفات في `resources/views/`
- استخدام Bootstrap 5 و Font Awesome
- دعم كامل للغة العربية (RTL)

## 📊 التقارير المتاحة

### تقارير الحضور
- تقرير الحضور اليومي
- تقرير الحضور الشهري
- تقرير ساعات العمل الإضافية

### تقارير الرواتب
- كشف رواتب شهري
- تقرير البدلات والخصومات
- تقرير الضرائب

### تقارير المالية
- تقرير الفواتير
- تقرير المدفوعات
- تقرير المبالغ المعلقة

## 🔄 النسخ الاحتياطي

### إنشاء نسخة احتياطية
```bash
# نسخ احتياطية لقاعدة البيانات
cp database/database.sqlite backup/database_$(date +%Y%m%d).sqlite

# نسخ احتياطية للملفات المرفوعة
tar -czf backup/storage_$(date +%Y%m%d).tar.gz storage/
```

## 🐛 استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### خطأ في قاعدة البيانات
```bash
# إعادة تشغيل الهجرات
php artisan migrate:fresh --seed
```

#### خطأ في التخزين المؤقت
```bash
# مسح التخزين المؤقت
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 🔮 الخطط المستقبلية

### الميزات القادمة
- [ ] نظام إدارة المخزون
- [ ] تقارير متقدمة مع الرسوم البيانية
- [ ] API للتطبيقات الخارجية
- [ ] نظام الإشعارات
- [ ] نظام النسخ الاحتياطي التلقائي
- [ ] دعم عدة لغات
- [ ] تطبيق موبايل

## 🤝 المساهمة

نرحب بمساهماتكم في تطوير النظام!

### كيفية المساهمة
1. Fork المشروع
2. إنشاء branch جديد (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)
4. Push إلى البranch (`git push origin feature/AmazingFeature`)
5. إنشاء Pull Request

### معايير المساهمة
- اتباع PSR-12 coding standards
- كتابة اختبارات للميزات الجديدة
- توثيق التغييرات في README
- استخدام التعليقات باللغة العربية

## 📝 الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

## 📞 الدعم الفني

### الأسئلة الشائعة

**س: كيف يمكنني إضافة موظف جديد؟**
ج: اذهب إلى قسم "إدارة الموظفين" واضغط على "إضافة موظف جديد"

**س: كيف يمكنني تسجيل حضور الموظفين؟**
ج: يمكنك استخدام الأزرار السريعة في صفحة تفاصيل الموظف أو إنشاء سجل حضور جديد

**س: كيف يمكنني إنشاء فاتورة؟**
ج: اذهب إلى قسم "إدارة الفواتير" واضغط على "إنشاء فاتورة جديدة"

---

## 🎯 ملخص المشروع

نظام ERP المصغّر هو حل متكامل ومبسط لإدارة الموارد في الشركات الصغيرة والمتوسطة. يوفر النظام جميع الأدوات الأساسية اللازمة لإدارة الموظفين والحضور والرواتب والعملاء والفواتير في واجهة واحدة سهلة الاستخدام.

**تم تطوير النظام باستخدام أحدث التقنيات ووفقاً لأفضل الممارسات في البرمجة، مع دعم كامل للغة العربية وتصميم متجاوب يعمل على جميع الأجهزة.**

</div>

---

*آخر تحديث: يوليو 2025*
