<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', 'نظام ERP المصغر')</title>

    <script>
        // تطبيق الوضع (فاتح/داكن) وحالة القائمة المصغرة قبل الرسم لتجنب الوميض
        (function () {
            var root = document.documentElement, theme = null;
            try {
                theme = localStorage.getItem('theme');
                if (localStorage.getItem('sidebar-collapsed') === '1') root.classList.add('sidebar-collapsed');
            } catch (e) {}
            if (theme !== 'dark' && theme !== 'light') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            root.setAttribute('data-bs-theme', theme);
            root.style.setProperty('--glow-offset', -(Date.now() % 36000) / 1000 + 's');
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
@php
    $user = auth()->user();
    $menu = getMenuData();
    $labels = [
        'dashboard' => 'لوحة المراقبة', 'employees' => 'الموظفون', 'departments' => 'الأقسام',
        'attendance' => 'الحضور والانصراف', 'payroll' => 'الرواتب', 'customers' => 'العملاء',
        'invoices' => 'الفواتير', 'products' => 'المنتجات والمخزون', 'reports' => 'التقارير',
        'users' => 'المستخدمون', 'roles' => 'الأدوار والصلاحيات',
    ];
    $groups = [
        'الرئيسية' => ['dashboard'],
        'الموارد البشرية' => ['employees', 'departments', 'attendance', 'payroll'],
        'المبيعات والمخزون' => ['customers', 'invoices', 'products'],
        'التحليل' => ['reports'],
        'الإدارة' => ['users', 'roles'],
    ];
    // أي قسم جديد في getMenuData غير موجود في المجموعات يظهر تحت "أخرى"
    $grouped = collect($groups)->flatten()->all();
    $others = array_values(array_diff(array_keys($menu), $grouped));
    if ($others) {
        $groups['أخرى'] = $others;
    }

    // أرقام تنبيه تظهر بجانب عناصر القائمة
    $navBadges = [];
    if ($user->can('invoices.view')) {
        $navBadges['invoices'] = ['count' => \App\Models\Invoice::overdue()->count(), 'title' => 'فواتير متأخرة'];
    }
    if ($user->can('products.view')) {
        $navBadges['products'] = [
            'count' => \App\Models\Product::where('status', 'active')->lowStock(\App\Http\Controllers\ProductController::LOW_STOCK_THRESHOLD)->count(),
            'title' => 'منتجات مخزونها منخفض',
        ];
    }

    $navGroups = [];
    foreach ($groups as $title => $resources) {
        foreach ($resources as $resource) {
            $routeName = $resource === 'dashboard' ? 'dashboard' : $resource . '.index';
            if (! isset($menu[$resource]) || ! Route::has($routeName) || ! $user->can($resource . '.view')) {
                continue;
            }
            $navGroups[$title][] = [
                'label' => $labels[$resource] ?? $resource,
                'icon' => $menu[$resource],
                'url' => route($routeName),
                'active' => request()->routeIs($resource === 'dashboard' ? 'dashboard' : $resource . '.*'),
                'badge' => $navBadges[$resource] ?? null,
            ];
        }
    }

    $currentSection = collect($navGroups)->flatten(1)->firstWhere('active', true);
    $initial = mb_substr(trim($user->name), 0, 1);
    $formSection = request()->routeIs('*.create', '*.edit') ? \Illuminate\Support\Str::beforeLast(request()->route()->getName(), '.') : null;
    $pageTitle = trim($__env->yieldContent('page-title', 'لوحة المراقبة'));
@endphp

    <div class="page-progress" aria-hidden="true"></div>
    <div class="sidebar-overlay" data-sidebar-close></div>

    <!-- القائمة الجانبية -->
    <aside class="sidebar" id="sidebar" aria-label="القائمة الرئيسية">
        <span class="sidebar-spotlight" aria-hidden="true"></span>

        <div class="sidebar-brand">
            <span class="brand-logo"><i class="fas fa-layer-group"></i></span>
            <span class="brand-text">نظام ERP<small>إدارة الموارد</small></span>
            <button type="button" class="icon-btn sidebar-close" data-sidebar-close aria-label="إغلاق القائمة">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            <span class="nav-hover-pill" aria-hidden="true"></span>
            @foreach($navGroups as $title => $items)
                <div class="nav-group" data-group="{{ $title }}" @if(collect($items)->contains('active', true)) data-active @endif>
                    <button type="button" class="nav-group-title" aria-expanded="true">
                        <span>{{ $title }}</span>
                        <i class="fas fa-chevron-down nav-group-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="nav-group-body">
                        <ul class="nav flex-column">
                            @foreach($items as $item)
                                <li class="nav-item">
                                    <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" data-label="{{ $item['label'] }}" @if($item['active']) aria-current="page" @endif>
                                        <i class="{{ $item['icon'] }}"></i>
                                        <span class="nav-label">{{ $item['label'] }}</span>
                                        @if($item['badge'] && $item['badge']['count'] > 0)
                                            <span class="nav-badge" title="{{ $item['badge']['count'] }} {{ $item['badge']['title'] }}">{{ $item['badge']['count'] > 99 ? '99+' : $item['badge']['count'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </nav>
        <script>
            // استعادة المجموعات المطوية قبل الرسم (مجموعة الصفحة الحالية تبقى مفتوحة دائماً)
            (function () {
                var closed = [];
                try { closed = JSON.parse(localStorage.getItem('nav-collapsed-groups') || '[]'); } catch (e) {}
                document.querySelectorAll('#sidebarNav .nav-group').forEach(function (group) {
                    if (closed.indexOf(group.dataset.group) !== -1 && !group.hasAttribute('data-active')) {
                        group.classList.add('is-collapsed');
                        group.querySelector('.nav-group-title').setAttribute('aria-expanded', 'false');
                    }
                });
            })();
        </script>

        <div class="sidebar-user">
            <span class="avatar avatar-online">{{ $initial }}</span>
            <div class="sidebar-user-info">
                <strong>{{ $user->name }}</strong>
                <small>{{ $user->roles_name ?? $user->email }}</small>
            </div>
        </div>
    </aside>

    <div class="app-main">
        <!-- الشريط العلوي -->
        <header class="topbar">
            <button type="button" class="icon-btn" id="sidebarToggle" aria-controls="sidebar" aria-label="فتح/إغلاق القائمة">
                <i class="fas fa-bars"></i>
            </button>

            <a href="{{ Route::has('dashboard') && $user->can('dashboard.view') ? route('dashboard') : '#' }}" class="topbar-brand">
                <span class="brand-logo"><i class="fas fa-layer-group"></i></span>
                <span class="d-none d-sm-inline">نظام ERP</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="topbar-date d-none d-lg-inline">
                    <i class="far fa-calendar me-1"></i>{{ now()->locale('ar')->translatedFormat('l، j F Y') }}
                </span>

                <button type="button" class="icon-btn theme-toggle" id="themeToggle" aria-label="تبديل الوضع الداكن" title="تبديل الوضع الداكن">
                    <i class="fas fa-moon"></i><i class="fas fa-sun"></i>
                </button>

                <div class="dropdown">
                    <button class="user-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar">{{ $initial }}</span>
                        <span class="user-menu-meta">
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->roles_name ?? 'مستخدم' }}</small>
                        </span>
                        <i class="fas fa-chevron-down small text-muted d-none d-md-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2">
                            <div class="fw-bold">{{ $user->name }}</div>
                            <div class="small text-muted">{{ $user->email }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @if(Route::has('users.edit') && $user->can('users.update'))
                            <li><a class="dropdown-item" href="{{ route('users.edit', $user) }}"><i class="fas fa-user-cog"></i> إعدادات الحساب</a></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit"><i class="fas fa-sign-out-alt text-danger"></i> تسجيل الخروج</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @if(Route::has('dashboard') && $user->can('dashboard.view'))
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
                            @endif
                            @if($currentSection && $currentSection['label'] !== $pageTitle && ! request()->routeIs('dashboard'))
                                <li class="breadcrumb-item"><a href="{{ $currentSection['url'] }}">{{ $currentSection['label'] }}</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                        </ol>
                    </nav>
                    <h1 class="page-title">{{ $pageTitle }}</h1>
                    @hasSection('page-subtitle')
                        <p class="page-subtitle">@yield('page-subtitle')</p>
                    @endif
                </div>

                <div class="page-actions btn-toolbar">
                    @if($formSection && Route::has($formSection . '.index'))
                        <a class="btn btn-light border" href="{{ route($formSection . '.index') }}"><i class="fas fa-arrow-right me-1"></i> رجوع</a>
                    @endif
                    @yield('page-actions')
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- الفوتر -->
        <footer class="app-footer">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'نظام ERP') }}. جميع الحقوق محفوظة.</span>
            <span>نظام ERP المصغّر &middot; الإصدار 1.0</span>
        </footer>
    </div>

    <button type="button" class="back-to-top" id="backToTop" aria-label="العودة لأعلى الصفحة">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- الإشعارات المنبثقة -->
    <div class="toast-stack" aria-live="polite" aria-atomic="true">
        @foreach(['success' => 'fa-check', 'error' => 'fa-exclamation'] as $type => $icon)
            @if(session($type))
                <div class="app-toast app-toast-{{ $type === 'error' ? 'danger' : 'success' }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" style="--toast-duration: {{ $type === 'error' ? '8s' : '5s' }}">
                    <span class="app-toast-icon"><i class="fas {{ $icon }}"></i></span>
                    <div class="app-toast-body">{{ session($type) }}</div>
                    <button type="button" class="btn-close" data-toast-close aria-label="إغلاق"></button>
                    <span class="app-toast-progress"></span>
                </div>
            @endif
        @endforeach
    </div>

    <!-- مودال التأكيد (يُستخدم مع data-confirm) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px">
            <div class="modal-content confirm-modal">
                <div class="modal-body text-center p-4">
                    <div class="confirm-icon"><i class="fas fa-question"></i></div>
                    <h5 class="fw-bold mb-2" id="confirmModalTitle">تأكيد العملية</h5>
                    <p class="text-muted mb-4" data-confirm-message></p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-primary flex-fill" data-confirm-ok>تأكيد</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
