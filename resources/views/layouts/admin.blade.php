<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - IPCR/OPCR Module</title>
    <link rel="icon" type="image/jpeg" href="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ Vite::asset('resources/js/tailwind_admin_config.js') }}"></script>
    <script src="{{ Vite::asset('resources/js/admin_layout_theme.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin_layout.css', 'resources/js/admin_layout.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200" style="visibility: hidden;">
    <div class="flex min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Mobile Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar Navigation -->
        <div id="sidebar" class="sidebar-hidden fixed xl:sticky xl:top-0 xl:h-screen inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-lg z-40 overflow-y-auto border-r dark:border-gray-700 transition-colors duration-200">
            <div class="p-6 border-b dark:border-gray-700">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Admin Panel</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">IPCR/OPCR Module</p>
            </div>

            <nav class="p-6 space-y-2">
                @php $currentRoute = Route::currentRouteName(); @endphp

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ $currentRoute === 'admin.dashboard' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="fas fa-home w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ str_starts_with($currentRoute, 'admin.users') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>User Management</span>
                </a>

                <a href="{{ route('admin.role-management.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ str_starts_with($currentRoute, 'admin.role-management') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="fas fa-sitemap w-5"></i>
                    <span>Role & Dept Mgmt</span>
                </a>

                <a href="{{ route('admin.database.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ str_starts_with($currentRoute, 'admin.database') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="fas fa-database w-5"></i>
                    <span>Database Management</span>
                </a>

                <a href="{{ route('admin.activity-logs.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ str_starts_with($currentRoute, 'admin.activity-logs') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="fas fa-clock-rotate-left w-5"></i>
                    <span>Activity Logs</span>
                </a>

                <a href="{{ route('admin.notifications.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   {{ str_starts_with($currentRoute, 'admin.notifications') || str_starts_with($currentRoute, 'admin.deadlines') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="fas fa-bell w-5"></i>
                    <span>Notifications & Deadlines</span>
                </a>

                @if(auth()->user()->hasAnyRole(['faculty', 'dean']))
                <a href="{{ route('faculty.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition">
                    <i class="fas fa-arrow-left w-5"></i>
                    <span>Back to Faculty</span>
                </a>
                @endif

                <hr class="my-4 border-gray-200 dark:border-gray-700">

                <button type="button" data-dark-mode-toggle="1" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition text-left">
                    <div class="w-5 flex justify-center">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-500"></i>
                    </div>
                    <span>Dark Mode</span>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition w-full">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
            <!-- Top Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30 transition-colors duration-200">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center gap-4">
                    <div class="flex-1 flex items-center gap-4">
                        <button id="hamburgerBtn" onclick="toggleSidebar()" class="xl:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-200">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="flex-1 flex items-center justify-between gap-4">
                             @yield('header')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="flex-1 p-4 sm:p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-lg flex items-center gap-2 text-sm">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg flex items-center gap-2 text-sm">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
