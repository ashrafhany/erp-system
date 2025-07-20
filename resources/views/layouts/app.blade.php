<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام ERP المصغر')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            border-radius: 5px;
        }

        .main-content {
            padding: 20px;
        }

        .stat-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .btn {
            border-radius: 8px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
                <div class="position-sticky">
                    <div class="text-center mb-4">
                        <h4 class="navbar-brand">
                            <i class="fas fa-chart-line me-2"></i>
                            نظام ERP
                        </h4>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                               href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                لوحة المراقبة
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}"
                               href="{{ route('employees.index') }}">
                                <i class="fas fa-users me-2"></i>
                                إدارة الموظفين
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}"
                               href="{{ route('attendance.index') }}">
                                <i class="fas fa-clock me-2"></i>
                                الحضور والانصراف
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}"
                               href="{{ route('payroll.index') }}">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                إدارة الرواتب
                            </a>
                        </li>

                        <hr class="my-3">

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                               href="{{ route('customers.index') }}">
                                <i class="fas fa-user-tie me-2"></i>
                                إدارة العملاء
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                               href="{{ route('invoices.index') }}">
                                <i class="fas fa-file-invoice me-2"></i>
                                إدارة الفواتير
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- المحتوى الرئيسي -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <!-- شريط التنقل العلوي -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'لوحة المراقبة')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            @yield('page-actions')
                        </div>
                    </div>
                </div>

                <!-- الرسائل -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- محتوى الصفحة -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
