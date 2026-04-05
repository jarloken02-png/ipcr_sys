<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Dashboard - IPCR/OPCR Module</title>
    <link rel="icon" type="image/jpeg" href="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold">Dean Dashboard</h1>
                <p class="text-sm text-slate-500">Review and calibrate faculty and dean submissions.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-50 text-red-700 font-semibold hover:bg-red-100 transition">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <h2 class="text-lg font-semibold">Welcome, {{ auth()->user()->name }}</h2>
            <p class="mt-2 text-slate-600 text-sm sm:text-base">
                Use the quick actions below to open your review modules.
            </p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('dean.review.faculty-submissions') }}"
                   class="block rounded-xl border border-indigo-200 bg-indigo-50 p-5 hover:bg-indigo-100 transition">
                    <h3 class="font-semibold text-indigo-900">Faculty IPCR Reviews</h3>
                    <p class="text-sm text-indigo-800 mt-1">Review submissions from faculty in your department.</p>
                </a>

                <a href="{{ route('dean.review.dean-submissions') }}"
                   class="block rounded-xl border border-amber-200 bg-amber-50 p-5 hover:bg-amber-100 transition">
                    <h3 class="font-semibold text-amber-900">Dean Calibration</h3>
                    <p class="text-sm text-amber-800 mt-1">Calibrate submissions from other deans.</p>
                </a>
            </div>
        </div>
    </main>
</body>
</html>