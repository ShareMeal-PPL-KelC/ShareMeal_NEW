<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ShareMeal' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-manrope { font-family: 'Manrope', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Shared Component Styles for CDN */
        .btn-primary {
            display: inline-flex; items-center; justify-content; center; border-radius: 0.75rem; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; transition-property: all; background-color: #174413; color: white;
        }
        .btn-primary:hover { background-color: #10310e; }
        
        .btn-secondary {
            display: inline-flex; items-center; justify-content; center; border-radius: 0.75rem; border-width: 1px; border-color: #cbd5e1; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; transition-property: all; background-color: white; color: #334155;
        }
        .btn-secondary:hover { background-color: #f8fafc; }

        .input, .select {
            width: 100%; border-radius: 0.75rem; border-width: 1px; border-color: #cbd5e1; background-color: white; padding: 0.75rem 1rem; font-size: 0.875rem; color: #0f172a; outline: 2px solid transparent; outline-offset: 2px;
        }
        .input:focus, .select:focus { border-color: #174413; }
        
        .select { appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; }

        .card-soft {
            border-radius: 1rem; border-width: 1px; border-color: rgba(255, 255, 255, 0.1); background-color: rgba(255, 255, 255, 0.8); box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); backdrop-filter: blur(4px);
        }
    </style>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="antialiased">
    @if (session('success') || session('error'))
        <div class="fixed inset-x-0 top-4 z-50 flex justify-center px-4">
            @if (session('success'))
                <div class="rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-lg">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white shadow-lg">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
