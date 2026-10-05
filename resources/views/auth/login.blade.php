<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | نظام ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { min-height: 100vh; background: #f4f6fb; } .login-card { max-width: 420px; width: 100%; }</style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <main class="card shadow-sm login-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-4 text-center">تسجيل الدخول إلى نظام ERP</h1>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" value="1"> تذكرني</label>
                <button type="submit" class="btn btn-primary w-100">دخول</button>
            </form>
        </div>
    </main>
</body>
</html>
