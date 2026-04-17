<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ShareMeal' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
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
