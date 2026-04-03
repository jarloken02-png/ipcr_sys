<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Director Dashboard - IPCR/OPCR Module</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50" style="visibility: hidden;">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-900">Director Dashboard</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-8">
            <p class="text-gray-600">Welcome, {{ auth()->user()->name }}</p>
            <p class="text-gray-500 text-sm mt-2">Director Dashboard - Content coming soon</p>
        </div>
    </div>
    <script src="{{ Vite::asset('resources/js/dashboard_director_index.js') }}"></script>
</body>
</html>