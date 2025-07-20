# Mini ERP System API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Response Format
All API responses follow this standard format:

### Success Response
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors (if applicable)
    }
}
```

## HTTP Status Codes
- `200` - OK (Success)
- `201` - Created (Resource created successfully)
- `400` - Bad Request (Invalid input)
- `401` - Unauthorized (Authentication required)
- `403` - Forbidden (Access denied)
- `404` - Not Found (Resource not found)
- `409` - Conflict (Resource already exists)
- `422` - Unprocessable Entity (Validation failed)
- `500` - Internal Server Error

## Authentication
Currently, the API does not require authentication. In production, you should implement authentication using Laravel Sanctum or Passport.

## Endpoints

### 🏠 Dashboard

#### Get Dashboard Statistics
```http
GET /api/v1/dashboard
```

**Response:**
```json
{
    "success": true,
    "message": "Dashboard statistics retrieved successfully",
    "data": {
        "employees": {
            "total": 5,
            "active": 4,
            "inactive": 1
        },
        "attendance": {
            "today_total": 4,
            "today_present": 3,
            "today_absent": 1,
            "today_late": 0,
            "attendance_rate": 75.0
        },
        "payroll": {
            "current_month_count": 3,
            "pending": 2,
            "approved": 1,
            "total_amount": 45000.00
        },
        "customers": {
            "total": 4,
            "active": 4
        },
        "invoices": {
            "total": 6,
            "pending": 2,
            "paid": 3,
            "overdue": 1,
            "total_amount": 125000.00,
            "paid_amount": 75000.00,
            "pending_amount": 50000.00
        },
        "recent_activities": {
            "attendance": [...],
            "invoices": [...],
            "payroll": [...]
        }
    }
}
```

### 👥 Employee Management

#### Get All Employees
```http
GET /api/v1/employees
```

**Query Parameters:**
- `search` (string, optional) - Search by name or email
- `department` (string, optional) - Filter by department
- `status` (string, optional) - Filter by status (active, inactive, terminated)
- `per_page` (integer, optional) - Number of results per page (default: 15)

#### Get Employee by ID
```http
GET /api/v1/employees/{id}
```

#### Create New Employee
```http
POST /api/v1/employees
```

**Request Body:**
```json
{
    "employee_id": "EMP001",
    "first_name": "Ahmed",
    "last_name": "Mohamed",
    "email": "ahmed.mohamed@company.com",
    "phone": "+201234567890",
    "department": "IT",
    "position": "Software Developer",
    "basic_salary": 15000.00,
    "hire_date": "2025-07-20",
    "status": "active",
    "address": "Cairo, Egypt",
    "national_id": "12345678901234"
}
```

#### Update Employee
```http
PUT /api/v1/employees/{id}
```

#### Delete Employee
```http
DELETE /api/v1/employees/{id}
```

#### Get Employee Attendance
```http
GET /api/v1/employees/{id}/attendance
```

**Query Parameters:**
- `month` (string, optional) - Filter by month (YYYY-MM format)
- `per_page` (integer, optional) - Number of results per page

#### Get Employee Payroll
```http
GET /api/v1/employees/{id}/payroll
```

**Query Parameters:**
- `year` (string, optional) - Filter by year (YYYY format)
- `per_page` (integer, optional) - Number of results per page

### ⏰ Attendance Management

#### Get All Attendance Records
```http
GET /api/v1/attendance
```

**Query Parameters:**
- `date` (date, optional) - Filter by specific date (YYYY-MM-DD)
- `employee_id` (integer, optional) - Filter by employee ID
- `status` (string, optional) - Filter by status (present, absent, late, half_day)
- `month` (string, optional) - Filter by month (YYYY-MM)
- `per_page` (integer, optional) - Number of results per page

#### Get Attendance by ID
```http
GET /api/v1/attendance/{id}
```

#### Create Attendance Record
```http
POST /api/v1/attendance
```

**Request Body:**
```json
{
    "employee_id": 1,
    "date": "2025-07-20",
    "check_in": "09:00",
    "check_out": "17:00",
    "status": "present",
    "notes": "Regular work day"
}
```

#### Update Attendance Record
```http
PUT /api/v1/attendance/{id}
```

#### Delete Attendance Record
```http
DELETE /api/v1/attendance/{id}
```

#### Employee Check-in
```http
POST /api/v1/attendance/checkin/{employee_id}
```

#### Employee Check-out
```http
POST /api/v1/attendance/checkout/{employee_id}
```

#### Daily Attendance Report
```http
GET /api/v1/attendance/report/daily
```

**Query Parameters:**
- `date` (date, optional) - Report date (default: today)

#### Monthly Attendance Report
```http
GET /api/v1/attendance/report/monthly
```

**Query Parameters:**
- `month` (string, optional) - Report month (YYYY-MM format, default: current month)

### 💰 Payroll Management

#### Get All Payroll Records
```http
GET /api/v1/payroll
```

**Query Parameters:**
- `month` (string, optional) - Filter by payroll month (YYYY-MM)
- `employee_id` (integer, optional) - Filter by employee ID
- `status` (string, optional) - Filter by status (pending, approved, paid)
- `year` (string, optional) - Filter by year (YYYY)
- `per_page` (integer, optional) - Number of results per page

#### Get Payroll by ID
```http
GET /api/v1/payroll/{id}
```

#### Create Payroll Record
```http
POST /api/v1/payroll
```

**Request Body:**
```json
{
    "employee_id": 1,
    "payroll_month": "2025-07-01",
    "basic_salary": 15000.00,
    "overtime_hours": 10,
    "overtime_rate": 50.00,
    "allowances": 1000.00,
    "deductions": 500.00,
    "tax_amount": 1200.00,
    "notes": "Regular monthly payroll"
}
```

#### Update Payroll Record
```http
PUT /api/v1/payroll/{id}
```

#### Delete Payroll Record
```http
DELETE /api/v1/payroll/{id}
```

#### Generate Automatic Payroll
```http
POST /api/v1/payroll/generate/{employee_id}
```

**Request Body:**
```json
{
    "payroll_month": "2025-07-01"
}
```

#### Approve Payroll
```http
POST /api/v1/payroll/approve/{payroll_id}
```

#### Monthly Payroll Report
```http
GET /api/v1/payroll/report/monthly
```

**Query Parameters:**
- `month` (string, optional) - Report month (YYYY-MM format)

### 🤝 Customer Management

#### Get All Customers
```http
GET /api/v1/customers
```

**Query Parameters:**
- `search` (string, optional) - Search by name or company
- `status` (string, optional) - Filter by status (active, inactive)
- `per_page` (integer, optional) - Number of results per page

#### Get Customer by ID
```http
GET /api/v1/customers/{id}
```

#### Create New Customer
```http
POST /api/v1/customers
```

**Request Body:**
```json
{
    "customer_code": "CUST001",
    "name": "Mohamed Ahmed",
    "company_name": "Tech Solutions Ltd",
    "email": "mohamed@techsolutions.com",
    "phone": "+201234567890",
    "address": "Cairo, Egypt",
    "credit_limit": 50000.00,
    "status": "active",
    "notes": "VIP Customer"
}
```

#### Update Customer
```http
PUT /api/v1/customers/{id}
```

#### Delete Customer
```http
DELETE /api/v1/customers/{id}
```

#### Get Customer Invoices
```http
GET /api/v1/customers/{id}/invoices
```

**Query Parameters:**
- `status` (string, optional) - Filter by invoice status
- `from_date` (date, optional) - Filter from date
- `to_date` (date, optional) - Filter to date
- `per_page` (integer, optional) - Number of results per page

### 🧾 Invoice Management

#### Get All Invoices
```http
GET /api/v1/invoices
```

**Query Parameters:**
- `customer_id` (integer, optional) - Filter by customer ID
- `status` (string, optional) - Filter by status (draft, sent, paid, overdue)
- `from_date` (date, optional) - Filter from date
- `to_date` (date, optional) - Filter to date
- `search` (string, optional) - Search by invoice number or customer name
- `per_page` (integer, optional) - Number of results per page

#### Get Invoice by ID
```http
GET /api/v1/invoices/{id}
```

#### Create New Invoice
```http
POST /api/v1/invoices
```

**Request Body:**
```json
{
    "customer_id": 1,
    "invoice_date": "2025-07-20",
    "due_date": "2025-08-20",
    "tax_rate": 14.0,
    "discount_amount": 500.00,
    "notes": "Monthly service invoice",
    "items": [
        {
            "description": "Web Development Services",
            "quantity": 1,
            "unit_price": 10000.00
        },
        {
            "description": "Hosting Services",
            "quantity": 12,
            "unit_price": 500.00
        }
    ]
}
```

#### Update Invoice
```http
PUT /api/v1/invoices/{id}
```

#### Delete Invoice
```http
DELETE /api/v1/invoices/{id}
```

#### Add Invoice Item
```http
POST /api/v1/invoices/{invoice_id}/items
```

**Request Body:**
```json
{
    "description": "Additional Consulting Hours",
    "quantity": 8,
    "unit_price": 200.00
}
```

#### Remove Invoice Item
```http
DELETE /api/v1/invoices/items/{item_id}
```

#### Send Invoice
```http
POST /api/v1/invoices/{id}/send
```

#### Record Payment
```http
POST /api/v1/invoices/{id}/payment
```

**Request Body:**
```json
{
    "amount": 5000.00,
    "payment_date": "2025-07-20",
    "payment_method": "bank_transfer",
    "notes": "Partial payment received"
}
```

#### Generate PDF
```http
GET /api/v1/invoices/{id}/pdf
```

## Error Handling

The API returns appropriate HTTP status codes and error messages:

### Validation Errors (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": [
            "Validation error message"
        ]
    }
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Conflict (409)
```json
{
    "success": false,
    "message": "Resource already exists or conflict occurred"
}
```

## Rate Limiting
Currently, no rate limiting is implemented. Consider implementing rate limiting for production use.

## Pagination
List endpoints return paginated results with the following structure:

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [...],
        "first_page_url": "http://localhost:8000/api/v1/employees?page=1",
        "from": 1,
        "last_page": 3,
        "last_page_url": "http://localhost:8000/api/v1/employees?page=3",
        "links": [...],
        "next_page_url": "http://localhost:8000/api/v1/employees?page=2",
        "path": "http://localhost:8000/api/v1/employees",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 45
    }
}
```

## Testing
You can test the API using tools like:
- Postman
- Insomnia
- cURL
- Any HTTP client library

Example cURL request:
```bash
curl -X GET \
  http://localhost:8000/api/v1/employees \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json'
```

## Security Considerations
For production deployment:
1. Implement authentication (Laravel Sanctum/Passport)
2. Add rate limiting
3. Validate and sanitize all inputs
4. Use HTTPS
5. Implement proper CORS policies
6. Add request logging and monitoring
