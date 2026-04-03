@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0 w-full">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Administrator Dashboard</h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">IPCR and OPCR Management System</p>
        </div>

        <div class="flex items-center gap-3 text-right whitespace-nowrap hidden sm:flex flex-shrink-0">
            <div class="text-right hidden md:block">
                <p class="text-gray-900 dark:text-white font-semibold text-sm">{{ auth()->user()->name }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Admin</p>
            </div>
            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0 border-2 border-white dark:border-gray-700 shadow-sm">
        </div>
    </div>
@endsection

@section('content')
    <!-- Welcome Banner - Glassmorphic Redesign -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-blue-100 dark:border-blue-900/50 p-6 sm:p-8 mb-6 transition-colors">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-48 h-48 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 dark:opacity-40 animate-blob"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-48 h-48 bg-gradient-to-tr from-cyan-400 to-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 dark:opacity-40 animate-blob animation-delay-2000"></div>
        <div class="relative z-10">
            <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Welcome, {{ explode(' ', auth()->user()->name)[0] }}!</h3>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mt-2">Manage the IPCR/OPCR system from the admin panel</p>
        </div>
    </div>

    <!-- Quick Stats Cards - 2xl Rounded with Hover -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Total Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\User::count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center transform rotate-3">
                <i class="fas fa-users text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Active Users</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ \App\Models\User::where('is_active', true)->count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center transform -rotate-3">
                <i class="fas fa-check text-green-600 dark:text-green-400 text-lg"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Departments</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\Department::count() }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center transform rotate-3">
                <i class="fas fa-building text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Feature Cards - Sleek Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mt-6">
        <a href="{{ route('admin.users.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md p-6 flex items-start gap-4 transition-all duration-300">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-users-cog text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">User Management</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Create, edit, view, and manage all user accounts</p>
            </div>
            <div class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-400 group-hover:bg-blue-600 group-hover:border-blue-600 group-hover:text-white transition-all duration-300">
                <i class="fas fa-arrow-right text-xs"></i>
            </div>
        </a>

        <a href="{{ route('admin.database.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md p-6 flex items-start gap-4 transition-all duration-300">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-database text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Database Management</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Backup, restore, and maintain database health</p>
            </div>
            <div class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-400 group-hover:bg-purple-600 group-hover:border-purple-600 group-hover:text-white transition-all duration-300">
                <i class="fas fa-arrow-right text-xs"></i>
            </div>
        </a>
    </div>

    <!-- Notifications & Deadlines Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-6">
        <!-- Upcoming Deadlines Widget -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-orange-600 dark:text-orange-400 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Upcoming Deadlines</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Synced from Notifications & Deadlines module</p>
                    </div>
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="text-xs font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors">Manage <i class="fas fa-arrow-right ml-1 text-[10px]"></i></a>
            </div>
            <div class="p-4 space-y-2 max-h-72 overflow-y-auto">
                @forelse($deadlines as $deadline)
                    @php
                        $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($deadline->deadline_date, false));
                    @endphp
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-orange-200 dark:hover:border-orange-800 transition-colors">
                        <div class="flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 text-white flex-shrink-0">
                            <span class="text-[9px] uppercase tracking-wider font-semibold opacity-90">{{ $deadline->deadline_date->format('M') }}</span>
                            <span class="text-base font-black leading-none">{{ $deadline->deadline_date->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $deadline->title }}</p>
                            @if($deadline->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $deadline->description }}</p>
                            @endif
                        </div>
                        @if($daysLeft <= 3)
                            <span class="text-[10px] font-bold text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full flex-shrink-0">{{ $daysLeft }}d</span>
                        @elseif($daysLeft <= 7)
                            <span class="text-[10px] font-bold text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 px-2 py-0.5 rounded-full flex-shrink-0">{{ $daysLeft }}d</span>
                        @else
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 flex-shrink-0">{{ $daysLeft }}d</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 dark:text-gray-500">
                        <i class="fas fa-calendar-check text-2xl mb-2 block opacity-40"></i>
                        <p class="text-xs">No upcoming deadlines</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Notifications Widget -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-bell text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Active Notifications</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Currently displayed to users</p>
                    </div>
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 transition-colors">Manage <i class="fas fa-arrow-right ml-1 text-[10px]"></i></a>
            </div>
            <div class="p-4 space-y-2 max-h-72 overflow-y-auto">
                @php
                    $notifIcons = [
                        'info' => ['icon' => 'fa-circle-info', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50 dark:bg-blue-900/30', 'border' => 'border-blue-100 dark:border-blue-800'],
                        'warning' => ['icon' => 'fa-triangle-exclamation', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-50 dark:bg-yellow-900/30', 'border' => 'border-yellow-100 dark:border-yellow-800'],
                        'success' => ['icon' => 'fa-circle-check', 'color' => 'text-green-500', 'bg' => 'bg-green-50 dark:bg-green-900/30', 'border' => 'border-green-100 dark:border-green-800'],
                        'danger' => ['icon' => 'fa-circle-exclamation', 'color' => 'text-red-500', 'bg' => 'bg-red-50 dark:bg-red-900/30', 'border' => 'border-red-100 dark:border-red-800'],
                    ];
                @endphp
                @forelse($notifications as $notif)
                    @php $style = $notifIcons[$notif->type] ?? $notifIcons['info']; @endphp
                    <div class="flex items-start gap-3 p-3 {{ $style['bg'] }} rounded-xl border {{ $style['border'] }} transition-colors">
                        <i class="fas {{ $style['icon'] }} {{ $style['color'] }} text-sm mt-0.5 flex-shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $notif->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Str::limit($notif->message, 80) }}</p>
                        </div>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0 whitespace-nowrap">{{ $notif->created_at->diffForHumans(null, true, true) }}</span>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 dark:text-gray-500">
                        <i class="fas fa-bell-slash text-2xl mb-2 block opacity-40"></i>
                        <p class="text-xs">No active notifications</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- IPCR Submissions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mt-6 overflow-hidden transition-colors">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">IPCR Submissions</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Recent submissions from faculty across all departments</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <div class="relative">
                    <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
                    <select id="departmentFilter" name="department_id" class="pl-8 pr-8 py-2 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white transition-colors cursor-pointer appearance-none" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) $selectedDepartmentId === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-[10px] pointer-events-none"></i>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Name</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider hidden lg:table-cell">Department</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider text-center">SY & Sem</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Submitted</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider text-center">Status</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <td class="py-3 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                                        {{ substr($submission->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $submission->user->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate md:hidden">{{ $submission->user->department->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-6 text-gray-600 dark:text-gray-400 hidden lg:table-cell">{{ $submission->user->department->name ?? 'N/A' }}</td>
                            <td class="py-3 px-6 text-center text-gray-600 dark:text-gray-400">
                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded text-xs font-medium">{{ $submission->school_year }} • {{ $submission->semester }} Sem</span>
                            </td>
                            <td class="py-3 px-6 text-gray-500 dark:text-gray-400 text-xs font-medium">{{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y') : 'N/A' }}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    {{ ucfirst($submission->status ?? 'submitted') }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-right">
                                @if(auth()->user() && auth()->user()->role === 'admin')
                                    <button onclick="openAdminDeleteModal({{ $submission->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center ml-auto text-gray-400 hover:text-red-600 hover:bg-red-50 dark:text-gray-500 dark:hover:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete Submission">
                                        <i class="fas fa-trash-can text-sm"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-500 dark:text-gray-400">No IPCR submissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($submissions as $submission)
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700 space-y-3 relative group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $submission->user->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $submission->user->department->name ?? 'N/A' }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            {{ ucfirst($submission->status ?? 'submitted') }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                        <div>
                            <span class="block text-gray-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider mb-0.5">SY / Sem</span>
                            <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $submission->school_year }} • {{ $submission->semester }} Semi</span>
                        </div>
                        <div>
                            <span class="block text-gray-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider mb-0.5">Submitted</span>
                            <span class="font-medium text-gray-900 dark:text-white text-xs">{{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    @if(auth()->user() && auth()->user()->role === 'admin')
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-600 flex justify-end">
                            <button onclick="openAdminDeleteModal({{ $submission->id }})" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs font-medium flex items-center gap-1 transition-colors">
                                <i class="fas fa-trash-alt"></i> Delete Submission
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-4">No IPCR submissions found.</div>
            @endforelse
        </div>
    </div>
@endsection

@push('modals')
    <!-- Delete Confirmation Modal -->
    <div id="adminDeleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 animate-scale-in transition-colors z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                    <i class="fas fa-trash-can text-red-500 dark:text-red-400"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Submission</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-5">
                Are you sure you want to delete this IPCR submission? All associated data will be permanently removed.
            </p>
            <div class="flex justify-end gap-2">
                <button onclick="closeAdminDeleteModal()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">Cancel</button>
                <button onclick="confirmAdminDeleteSubmission()" id="confirmDeleteBtn" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{ Vite::asset('resources/js/dashboard_admin_index_page.js') }}"></script>
@endpush