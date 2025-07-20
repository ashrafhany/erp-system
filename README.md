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

### 🔗 RESTful API
- ✅ Complete RESTful API for all modules
- ✅ JSON responses with standardized format
- ✅ CORS support for external applications
- ✅ Comprehensive error handling
- ✅ 40+ API endpoints for mobile/web integration
- ✅ Dashboard statistics API
- ✅ Employee management API
- ✅ Attendance tracking API
- ✅ Payroll processing API
- ✅ Customer management API
- ✅ Invoice management API

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

### 6. API Documentation
The system includes a comprehensive RESTful API. See `API_DOCUMENTATION.md` for complete API reference with examples and endpoints.

**API Base URL**: `http://localhost:8000/api/v1/`

## 📱 Usage

### System Access
1. Open your browser and navigate to `http://localhost:8000`
2. You will see the main dashboard
3. Use the sidebar menu to navigate between sections

### API Access
The system provides a comprehensive RESTful API for external applications:
- **Base URL**: `http://localhost:8000/api/v1/`
- **Content-Type**: `application/json`
- **Response Format**: JSON with standardized structure
- **Documentation**: See `API_DOCUMENTATION.md` for complete reference

### Sample API Usage
```javascript
// Get dashboard statistics
GET http://localhost:8000/api/v1/dashboard/stats

// Create new employee
POST http://localhost:8000/api/v1/employees
{
    "name": "John Doe",
    "email": "john@company.com",
    "position": "Developer",
    "salary": 5000
}

// Employee check-in
POST http://localhost:8000/api/v1/attendance/employees/1/checkin
```

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

### API Endpoints (`/api/v1/`)
- **Dashboard Stats**: `GET /api/v1/dashboard/stats`
- **Employees**: `GET|POST /api/v1/employees`
- **Employee Details**: `GET|PUT|DELETE /api/v1/employees/{id}`
- **Attendance**: `GET|POST /api/v1/attendance`
- **Check-in/Check-out**: `POST /api/v1/attendance/employees/{id}/checkin|checkout`
- **Payroll**: `GET|POST /api/v1/payroll`
- **Customers**: `GET|POST /api/v1/customers`
- **Invoices**: `GET|POST /api/v1/invoices`
- **Reports**: `GET /api/v1/attendance/reports/daily|monthly`

*For complete API documentation, see `API_DOCUMENTATION.md`*

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

### API Response Format
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": { ... },
    "errors": null
}
```

## 🔒 Security

### Implemented Protection
- **CSRF Protection** - Protection against CSRF attacks
- **SQL Injection Prevention** - Prevention of SQL injection
- **XSS Protection** - Protection against XSS
- **Input Validation** - Input validation
- **Secure Headers** - Security headers
- **API Rate Limiting** - API request rate limiting
- **CORS Configuration** - Cross-origin resource sharing setup

## 🛠️ Customization and Development

### Adding New Features
```bash
# Create new Migration
php artisan make:migration create_new_table

# Create Model
php artisan make:model ModelName -m

# Create Controller
php artisan make:controller ControllerName --resource

# Create API Controller
php artisan make:controller Api/ControllerName --api
```

### Design Customization
- Files in `resources/views/`
- Using Bootstrap 5 and Font Awesome
- Full Arabic language support (RTL)

## 📊 Available Reports

### Attendance Reports
- Daily attendance report
- Monthly attendance report
- Overtime hours report

### Payroll Reports
- Monthly payroll statement
- Allowances and deductions report
- Tax report

### Financial Reports
- Invoice report
- Payment report
- Outstanding amounts report

### API Integration Reports
- API usage statistics
- Response time analytics
- Error rate monitoring

## 🔄 Backup

### Creating Backup
```bash
# Database backup
cp database/database.sqlite backup/database_$(date +%Y%m%d).sqlite

# Files backup
tar -czf backup/storage_$(date +%Y%m%d).tar.gz storage/
```

## 🐛 Troubleshooting

### Common Issues and Solutions

#### Database Error
```bash
# Reset migrations
php artisan migrate:fresh --seed
```

#### Cache Error
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 🔮 Future Plans

### Upcoming Features
- [ ] Inventory management system
- [ ] Advanced reports with charts
- [x] **API for external applications** ✅ **COMPLETED**
- [ ] Notification system
- [ ] Automatic backup system
- [ ] Multi-language support
- [ ] Mobile application
- [ ] API authentication (JWT/Sanctum)
- [ ] Real-time notifications via WebSockets

## 🤝 Contributing

We welcome your contributions to system development!

### How to Contribute
1. Fork the project
2. Create new branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Create Pull Request

### Contribution Standards
- Follow PSR-12 coding standards
- Write tests for new features
- Document changes in README
- Use Arabic comments in code

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

### Frequently Asked Questions

**Q: How can I add a new employee?**
A: Go to "Employee Management" section and click "Add New Employee"

**Q: How can I record employee attendance?**
A: You can use quick buttons on employee detail page or create new attendance record

**Q: How can I create an invoice?**
A: Go to "Invoice Management" section and click "Create New Invoice"

**Q: How can I use the API?**
A: The system provides a RESTful API at `/api/v1/`. See `API_DOCUMENTATION.md` for complete reference with examples.

**Q: Can I integrate this with a mobile app?**
A: Yes! The API supports CORS and provides JSON responses perfect for mobile app integration.

---

## 🎯 Project Summary

The Mini ERP System is a comprehensive and simplified solution for resource management in small and medium enterprises. The system provides all essential tools needed to manage employees, attendance, payroll, customers, and invoices in one easy-to-use interface.

**The system was developed using the latest technologies and following best programming practices, with full Arabic language support and responsive design that works on all devices.**

### 🚀 New API Features
- **Complete RESTful API** with 40+ endpoints
- **Mobile-ready** JSON responses
- **CORS enabled** for external applications
- **Standardized error handling** and response format
- **Comprehensive documentation** with examples

### 📱 Integration Possibilities
- Mobile applications (Flutter, React Native, Ionic)
- Web applications (React, Vue, Angular)
- Third-party system integration
- Custom dashboard development
- Automated reporting tools

---

*Last updated: July 2025*
