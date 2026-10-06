<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #fff; }
        .sheet { max-width: 800px; margin: 0 auto; padding: 24px; }
        @media print {
            .no-print { display: none !important; }
            .sheet { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="no-print d-flex gap-2 mb-4">
            <button type="button" class="btn btn-primary" onclick="window.print()">طباعة / حفظ PDF</button>
            <a href="@yield('back-url')" class="btn btn-outline-secondary">رجوع</a>
        </div>
        @yield('content')
    </div>
</body>
</html>
