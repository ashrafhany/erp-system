<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <title>تسجيل الدخول | نظام ERP</title>
    <script>
        // نفس الوضع (فاتح/داكن) المحفوظ في لوحة التحكم
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            if (theme !== 'dark' && theme !== 'light') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? ['صباح الخير', 'fa-sun'] : ($hour < 18 ? ['مساء الخير', 'fa-cloud-sun'] : ['مساء الخير', 'fa-moon']);
@endphp
    <main class="login-shell">
        <!-- الجانب التعريفي -->
        <section class="login-aside d-none d-lg-flex" aria-hidden="true">
            <span class="aurora aurora-1"></span>
            <span class="aurora aurora-2"></span>
            <span class="aurora aurora-3"></span>

            <div class="login-brand">
                <span class="brand-logo"><i class="fas fa-layer-group"></i></span>
                <span>نظام ERP<small>إدارة الموارد</small></span>
            </div>

            <div class="login-hero">
                <h2>كل شغلك في <span class="text-gradient">مكان واحد</span></h2>
                <p>الموظفين والحضور والرواتب والعملاء والفواتير والمخزون، بلوحة واحدة واضحة وتقارير جاهزة.</p>

                <!-- معاينة عائمة للنظام -->
                <div class="login-preview" id="loginPreview">
                    <div class="glass-card preview-main" style="--depth: 1">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="preview-label">الإيرادات الشهرية</span>
                            <span class="preview-trend"><i class="fas fa-arrow-up"></i> 12%</span>
                        </div>
                        <div class="preview-value">128,450 <small>ج.م</small></div>
                        <div class="preview-bars">
                            @foreach([38, 52, 45, 64, 58, 76, 70, 92] as $i => $h)
                                <span style="--h: {{ $h }}%; --i: {{ $i }}"></span>
                            @endforeach
                        </div>
                    </div>

                    <div class="glass-card preview-ring" style="--depth: 2.2">
                        <div class="ring" style="--p: 92"><span>92%</span></div>
                        <div>
                            <span class="preview-label">حضور اليوم</span>
                            <strong>46 / 50</strong>
                        </div>
                    </div>

                    <div class="glass-card preview-toast" style="--depth: 3">
                        <span class="toast-icon"><i class="fas fa-check"></i></span>
                        <div>
                            <strong>تم تحصيل فاتورة</strong>
                            <span class="preview-label">INV-1042 · 4,200 ج.م</span>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="login-features">
                <li><i class="fas fa-users"></i> الموارد البشرية</li>
                <li><i class="fas fa-file-invoice"></i> الفواتير والتحصيل</li>
                <li><i class="fas fa-boxes-stacked"></i> المخزون</li>
                <li><i class="fas fa-chart-pie"></i> التقارير</li>
            </ul>
        </section>

        <!-- نموذج الدخول -->
        <section class="login-form-wrap">
            <div class="login-topbar">
                <div class="login-brand d-lg-none">
                    <span class="brand-logo"><i class="fas fa-layer-group"></i></span>
                    <span>نظام ERP</span>
                </div>
                <button type="button" class="icon-btn theme-toggle ms-auto" id="themeToggle" aria-label="تبديل الوضع الداكن" title="تبديل الوضع الداكن">
                    <i class="fas fa-moon"></i><i class="fas fa-sun"></i>
                </button>
            </div>

            <div class="login-card {{ $errors->any() ? 'has-error' : '' }}">
                <div class="login-stagger">
                    <span class="greeting-pill"><i class="fas {{ $greeting[1] }}"></i> {{ $greeting[0] }}</span>
                    <h1 class="login-title">مرحباً بعودتك <span class="wave" aria-hidden="true">👋</span></h1>
                    <p class="text-muted mb-4">سجّل الدخول للمتابعة إلى لوحة التحكم</p>

                    @if($errors->any())
                        <div class="login-error" role="alert">
                            <i class="fas fa-circle-exclamation"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@company.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input id="password" name="password" type="password" class="form-control has-action @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="••••••••">
                                <button type="button" class="input-action" id="togglePassword" aria-label="إظهار كلمة المرور" aria-pressed="false">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div class="caps-hint" id="capsHint" role="status"><i class="fas fa-triangle-exclamation"></i> زر Caps Lock مفعّل</div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">تذكرني على هذا الجهاز</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 login-submit">
                            <span>دخول</span> <i class="fas fa-arrow-left"></i>
                        </button>
                    </form>
                </div>
            </div>

            <p class="login-footnote">
                <i class="fas fa-shield-halved"></i> اتصال آمن وصلاحيات حسب دور كل مستخدم
                <br>&copy; {{ date('Y') }} {{ config('app.name', 'نظام ERP') }}
            </p>
        </section>
    </main>

    <script>
    (function () {
        // إظهار/إخفاء كلمة المرور
        var password = document.getElementById('password');
        var toggle = document.getElementById('togglePassword');
        toggle.addEventListener('click', function () {
            var show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(show));
            toggle.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            toggle.querySelector('i').className = show ? 'far fa-eye-slash' : 'far fa-eye';
            password.focus();
        });

        // تنبيه عند تفعيل Caps Lock أثناء كتابة كلمة المرور
        var caps = document.getElementById('capsHint');
        ['keydown', 'keyup'].forEach(function (type) {
            password.addEventListener(type, function (e) {
                caps.classList.toggle('is-visible', !!(e.getModifierState && e.getModifierState('CapsLock')));
            });
        });
        password.addEventListener('blur', function () { caps.classList.remove('is-visible'); });

        // المعاينة العائمة تتحرك قليلاً مع الماوس (عمق مختلف لكل كارت)
        var preview = document.getElementById('loginPreview');
        var aside = document.querySelector('.login-aside');
        if (preview && aside && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            var frame = null;
            aside.addEventListener('pointermove', function (e) {
                if (frame) return;
                frame = requestAnimationFrame(function () {
                    var rect = aside.getBoundingClientRect();
                    preview.style.setProperty('--px', ((e.clientX - rect.left) / rect.width - .5).toFixed(3));
                    preview.style.setProperty('--py', ((e.clientY - rect.top) / rect.height - .5).toFixed(3));
                    frame = null;
                });
            });
            aside.addEventListener('pointerleave', function () {
                preview.style.setProperty('--px', 0);
                preview.style.setProperty('--py', 0);
            });
        }
    })();
    </script>
</body>
</html>
