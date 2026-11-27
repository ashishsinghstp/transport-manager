<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Transport Manager')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            @auth
                <a href="{{ route('drivers.index') }}" class="text-lg font-semibold text-indigo-600">Transport Manager</a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('drivers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Drivers</a>
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            @else
                <span class="text-lg font-semibold text-indigo-600">Transport Manager</span>
            @endauth
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-medium">Please fix the following errors:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
