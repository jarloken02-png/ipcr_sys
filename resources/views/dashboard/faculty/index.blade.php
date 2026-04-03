<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Faculty Dashboard - IPCR/OPCR Module</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @php
        $directorSubmissionTrendDataSafe = $directorSubmissionTrendData ?? [0, 0, 0, 0, 0, 0];
    @endphp
    @vite(['resources/css/dashboard_faculty_index.css', 'resources/js/dashboard_faculty_index.js'])
</head>
<body class="bg-gray-50" style="visibility: hidden;">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex justify-between items-center">
                <!-- Logo and Title -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <img src="{{ asset('images/urs_logo.jpg') }}" alt="URS Logo" class="h-10 sm:h-12 w-auto object-contain flex-shrink-0">
                    <h1 class="text-base sm:text-xl font-bold text-gray-900">IPCR Dashboard</h1>
                </div>
                
                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex items-center space-x-6 xl:space-x-8">
                    <a href="{{ route('faculty.dashboard') }}" class="text-blue-600 font-semibold hover:text-blue-700">Dashboard</a>
                    <a href="{{ route('faculty.my-ipcrs') }}" class="text-gray-600 hover:text-gray-900">My IPCRs</a>
                    @if(auth()->user()->hasRole('hr'))
                        <a href="{{ route('faculty.summary-reports') }}" class="text-gray-600 hover:text-gray-900">Summary Reports</a>
                    @endif
                    <a href="{{ route('faculty.profile') }}" class="text-gray-600 hover:text-gray-900">Profile</a>
                    
                    <!-- Profile Picture -->
                    <div class="flex items-center space-x-3">
                        @if(auth()->user()->hasProfilePhoto())
                            <img src="{{ auth()->user()->profile_photo_url }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="profile-img">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="profile-img">
                        @endif
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">Logout</button>
                    </form>
                </div>

                <!-- Mobile Menu Button & Profile -->
                <div class="flex lg:hidden items-center space-x-3">
                    <!-- Notification Bell Icon -->
                    <div class="relative">
                        <button onclick="toggleNotificationPopup()" class="text-gray-600 hover:text-gray-900 relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            @if(($unreadCount ?? 0) > 0)
                                <span class="notification-badge" id="notifBadge">{{ $unreadCount }}</span>
                            @else
                                <span class="notification-badge hidden" id="notifBadge">0</span>
                            @endif
                        </button>
                        
                        <!-- Notification Popup -->
                        <div id="notificationPopup" class="notification-popup">
                            <div class="p-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Notifications</h3>
                                <div class="flex items-center gap-2">
                                    <button onclick="markAllNotificationsRead()" class="text-[10px] font-semibold text-blue-600 hover:text-blue-800 transition-colors" id="markReadBtn" title="Mark all as read">
                                        Mark all as read
                                    </button>
                                    <button onclick="toggleCompactMode()" class="compact-toggle-btn text-[10px] font-semibold px-2 py-0.5 rounded-full border transition-colors" title="Toggle compact view">
                                        <span class="compact-label">Compact</span>
                                    </button>
                                </div>
                            </div>
                            <div class="max-h-72 overflow-y-auto" id="popupNotifScroll">
                                <div class="p-2.5 notif-list" id="popupNotifList">
                                    @forelse(($notifications ?? collect()) as $notif)
                                        @php
                                            $notifStyles = [
                                                'info' => 'notification-blue',
                                                'warning' => 'notification-yellow',
                                                'success' => 'notification-green',
                                                'danger' => 'notification-red',
                                            ];
                                            $iconColors = [
                                                'info' => 'text-blue-500',
                                                'warning' => 'text-yellow-600',
                                                'success' => 'text-green-500',
                                                'danger' => 'text-red-500',
                                            ];
                                            $isUnread = !in_array($notif->id, $readNotifIds ?? []);
                                        @endphp
                                        <div class="notification-item notif-card {{ $notifStyles[$notif->type] ?? 'notification-gray' }} mb-1.5{{ $isUnread ? ' notif-unread' : '' }}" data-notif-id="{{ $notif->id }}">
                                            <div class="flex items-start space-x-2">
                                                <svg class="w-3.5 h-3.5 {{ $iconColors[$notif->type] ?? 'text-gray-600' }} mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($notif->type === 'success')
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    @elseif($notif->type === 'warning' || $notif->type === 'danger')
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    @else
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    @endif
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <p class="notif-title text-xs font-semibold text-gray-900">{{ $notif->title }}</p>
                                                        @if($isUnread)
                                                            <span class="notif-unread-dot w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                                                        @endif
                                                    </div>
                                                    <p class="notif-message text-[11px] text-gray-600 mt-0.5">{{ Str::limit($notif->message, 80) }}</p>
                                                    <p class="notif-time text-[9px] text-gray-400 mt-0.5">{{ ($notif->published_at ?? $notif->created_at)->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="notification-item notification-gray">
                                            <div class="flex items-start space-x-2">
                                                <svg class="w-3.5 h-3.5 text-gray-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-900">No notifications</p>
                                                    <p class="text-[11px] text-gray-600">You're all caught up!</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        @if(auth()->user()->hasProfilePhoto())
                            <img src="{{ auth()->user()->profile_photo_url }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="profile-img">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="profile-img">
                        @endif
                    </div>
                    <div class="hamburger" onclick="toggleMobileMenu()">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Overlay -->
            <div class="mobile-menu-overlay lg:hidden" onclick="toggleMobileMenu()"></div>

            <!-- Mobile Menu -->
            <div class="mobile-menu lg:hidden flex-col space-y-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Menu</h2>
                    <button onclick="toggleMobileMenu()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <a href="{{ route('faculty.dashboard') }}" class="block text-blue-600 font-semibold hover:text-blue-700 py-2">Dashboard</a>
                <a href="{{ route('faculty.my-ipcrs') }}" class="block text-gray-600 hover:text-gray-900 py-2">My IPCRs</a>
                @if(auth()->user()->hasRole('hr'))
                    <a href="{{ route('faculty.summary-reports') }}" class="block text-gray-600 hover:text-gray-900 py-2">Summary Reports</a>
                @endif
                <a href="{{ route('faculty.profile') }}" class="block text-gray-600 hover:text-gray-900 py-2">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold py-2">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Expanded Full-List View (hidden by default) -->
    <div id="expandedView" class="hidden max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-8">
        <div class="mb-6">
            <button onclick="collapseExpandedView()" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors group">
                <svg class="w-5 h-5 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Dashboard
            </button>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div id="expandedViewIcon" class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center"></div>
                        <div>
                            <h2 id="expandedViewTitle" class="text-lg font-bold text-gray-900"></h2>
                            <p id="expandedViewSubtitle" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                    <div id="expandedViewFilters" class="flex flex-wrap gap-2 hidden"></div>
                </div>
            </div>
            <div id="expandedListContent" class="p-4 space-y-3 max-h-[70vh] overflow-y-auto"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="dashboardMainContent" class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-8">
        @if($isDirectorDashboard ?? false)
        <div class="space-y-6 sm:space-y-8">
            <!-- Header Title -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Campus Overview</h2>
                        <p class="text-base text-gray-500 mt-1">Real-time status of IPCR submissions across all departments.</p>
                    </div>
                </div>
            </div>

            <!-- Top Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                <!-- Total Faculty Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                            +2 this mo
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Total Faculty</p>
                        <h3 class="text-4xl font-bold text-gray-900">{{ $directorOverview['totalFaculty'] ?? 0 }}</h3>
                    </div>
                </div>

                <!-- Submission Rate Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <!-- Decorative matching the mockup toggles -->
                        <div class="flex space-x-1">
                            <div class="w-4 h-4 rounded-full bg-gray-300"></div>
                            <div class="w-4 h-4 rounded-full bg-gray-200"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Submission Rate</p>
                        <h3 class="text-4xl font-bold text-gray-900">{{ $directorOverview['submissionRate'] ?? 0 }}<span class="text-2xl font-semibold">%</span></h3>
                    </div>
                </div>

                <!-- Avg Performance Score Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-gray-400 mt-2">
                            Target 4.5
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Avg Performance Score</p>
                        <div class="flex items-baseline space-x-1">
                            <h3 class="text-4xl font-bold text-gray-900">{{ number_format((float) ($directorOverview['avgPerformanceScore'] ?? 0), 1) }}</h3>
                            <span class="text-lg text-gray-400 font-semibold">/ 5.0</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Reviews Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="w-3 h-3 rounded-full bg-red-500 bg-opacity-80 mt-2"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 mb-1">Pending Reviews</p>
                        <h3 class="text-4xl font-bold text-gray-900">{{ $directorOverview['pendingReviews'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
                <div class="xl:col-span-2 space-y-4 sm:space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Departmental Progress</h3>
                            <a href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View All Departments</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @forelse(($departmentProgress ?? collect()) as $department)
                                @php
                                    $percent = (int) $department['percent'];
                                    // Map color code to stroke colors for SVG
                                    $strokeColor = match($department['code']) {
                                        'COB' => '#3b82f6', // blue
                                        'COA' => '#a855f7', // purple
                                        'CCS' => '#10b981', // green
                                        default => '#6366f1' // indigo
                                    };
                                    $circumference = 2 * pi() * 40; // r=40
                                    $offset = $circumference - ($percent / 100) * $circumference;
                                @endphp
                                <div class="rounded-3xl border border-gray-100 p-6 bg-white hover:shadow-md transition-shadow relative overflow-hidden group">
                                    <div class="flex flex-col items-center">
                                        <!-- Circular Progress SVG -->
                                        <div class="relative w-32 h-32 mb-4">
                                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                                <!-- Background Circle -->
                                                <circle cx="50" cy="50" r="40" fill="transparent" stroke-width="8" class="circular-progress-bg text-gray-100" />
                                                <!-- Progress Circle -->
                                                <circle cx="50" cy="50" r="40" fill="transparent" stroke-width="8" class="circular-progress-circle" style="stroke: {{ $strokeColor }}; stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $offset }}; stroke-linecap: round;" />
                                            </svg>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-2xl font-bold text-gray-900">{{ $percent }}<span class="text-lg font-semibold">%</span></span>
                                            </div>
                                        </div>
                                        
                                        <!-- Title -->
                                        <h4 class="text-xl font-bold text-gray-900 mb-1">{{ $department['code'] }}</h4>
                                        <p class="text-xs font-medium text-gray-500 mb-6 truncate" title="{{ $department['name'] }}">{{ $department['name'] }}</p>
                                        
                                        <!-- Footer / Details Button -->
                                        <div class="w-full flex items-center justify-between mt-auto">
                                            <span class="text-sm font-medium text-gray-400">Faculty: <span class="font-bold text-gray-700">{{ $department['faculty_count'] }}</span></span>
                                            <button class="px-4 py-1.5 text-sm font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-full transition-colors">Details</button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="md:col-span-2 xl:col-span-3 rounded-3xl bg-gray-50 border border-gray-100 p-6 text-center text-sm text-gray-500">
                                    No department progress data yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l3-3 2 2 5-5M7 7h10M7 12h4"></path></svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Submission Activity Trends</h3>
                            </div>
                            <span class="text-sm font-semibold text-gray-500">Month 1 - 6</span>
                        </div>
                        <div class="h-64 sm:h-72">
                            <canvas id="directorSubmissionTrendChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
                        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Top Faculty Performers</h3>
                            <button class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[560px]">
                                <thead>
                                    <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 bg-gray-50 border-b border-gray-100">
                                        <th class="py-4 px-6">Rank</th>
                                        <th class="py-4 px-6">Faculty Name</th>
                                        <th class="py-4 px-6">Department</th>
                                        <th class="py-4 px-6">Current Score</th>
                                        <th class="py-4 px-6">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topPerformers ?? [] as $index => $performer)
                                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group cursor-default">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center justify-center w-7 h-7 rounded-full {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }} text-xs font-bold shadow-sm">
                                                    {{ $index + 1 }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $performer->faculty_name }}</div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-sm text-gray-500">{{ $performer->department_code }}</div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="flex items-center space-x-3">
                                                    <span class="text-sm font-black text-blue-600 w-8">{{ number_format((float) $performer->current_score, 1) }}</span>
                                                    @php
                                                        $scoreWidth = max(0, min(100, ((float) $performer->current_score / 5.0) * 100));
                                                    @endphp
                                                    <div class="w-16 h-1.5 bg-blue-100 rounded-full overflow-hidden">
                                                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $scoreWidth }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-green-100 text-green-700">SUBMITTED</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-sm text-gray-400 font-medium">
                                                <div class="flex flex-col items-center justify-center space-y-2">
                                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                                    <span>No calibrated performer records yet</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 sm:space-y-6">
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7">
                        <div class="flex items-center space-x-2.5 mb-7 text-gray-900">
                            <svg class="w-[22px] h-[22px] text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-xl font-bold tracking-tight">Recent Activity</h3>
                        </div>
                        <div class="relative pl-5">
                            <!-- Vertical Line -->
                            <div class="absolute top-2.5 bottom-2.5 left-[15px] w-px bg-gray-100"></div>
                            
                            <div class="space-y-8">
                                @forelse(($recentActivities ?? collect())->take(4) as $index => $activity)
                                    @php
                                        // Mock color/style based on index to match the static image
                                        $dotColor = match($index) {
                                            0 => 'bg-[#3b82f6]', // blue
                                            1 => 'bg-[#f59e0b]', // orange
                                            2 => 'bg-[#ef4444]', // red
                                            default => 'bg-[#e2e8f0]' // grey
                                        };
                                        
                                        // Mock dynamic titles based on the image provided for the visual match
                                        $title = match($index) {
                                            0 => 'Dr. Jane Smith submitted IPCR',
                                            1 => 'CSS Progress Updated',
                                            2 => 'Revision Requested',
                                            default => 'New Faculty Added'
                                        };
                                        $timeStr = match($index) {
                                            0 => '2 minutes ago - COB Dept',
                                            1 => '45 minutes ago - System',
                                            2 => '2 hours ago - Admin',
                                            default => '5 hours ago - HR Sync'
                                        };
                                        $descStr = match($index) {
                                            0 => null, // Has special box
                                            1 => 'Department reached 88% completion milestone for Semester 1.',
                                            2 => 'Prof. John Doe\'s submission was returned for missing evidence.',
                                            default => null
                                        };
                                        $actionLink = match($index) {
                                            2 => 'View Dispute',
                                            default => null
                                        };
                                        $specialBox = match($index) {
                                            0 => 'Auto-calculated Score: 4.9',
                                            default => null
                                        };
                                    @endphp
                                    <div class="relative flex items-start">
                                        <!-- Timeline Dot -->
                                        <div class="absolute -left-[20.5px] mt-1.5 w-3 h-3 rounded-full {{ $dotColor }} ring-[6px] ring-white z-10"></div>
                                        
                                        <!-- Content -->
                                        <div class="ml-5 min-w-0">
                                            <h4 class="text-[15px] font-semibold text-slate-800 mb-0.5">{{ $title }}</h4>
                                            <p class="text-[13px] text-slate-400 mb-2">{{ $timeStr }}</p>
                                            
                                            @if($descStr)
                                                <p class="text-[14px] text-slate-600 leading-relaxed mb-2">{{ $descStr }}</p>
                                            @endif
                                            
                                            @if($specialBox)
                                                <div class="px-4 py-2.5 bg-[#f8fbff] border border-blue-100/60 rounded-xl inline-block mt-1">
                                                    <span class="text-[14px] font-medium text-blue-600 tracking-wide">{{ $specialBox }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($actionLink)
                                                <a href="#" class="text-[14px] font-bold text-red-600 hover:text-red-700 hover:underline mt-1 inline-block">{{ $actionLink }}</a>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6">
                                        <p class="text-sm text-gray-500 font-medium">No recent activity available.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        
                        <!-- View All Button -->
                        <div class="mt-8">
                            <button class="w-full py-3.5 bg-[#f8fafc] hover:bg-gray-100 text-[14px] font-semibold text-slate-700 rounded-2xl transition-colors">
                                View All History
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hidden lg:block">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">Notifications</h3>
                                @if(($unreadCount ?? 0) > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700" id="sidebarNotifBadge">{{ $unreadCount }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 hidden" id="sidebarNotifBadge">0</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="markAllNotificationsRead()" class="text-[10px] font-semibold text-blue-600 hover:text-blue-800 transition-colors" title="Mark all as read">
                                    Mark all as read
                                </button>
                                <button onclick="toggleCompactMode()" class="compact-toggle-btn text-[10px] font-semibold px-2 py-0.5 rounded-full border transition-colors" title="Toggle compact view">
                                    <span class="compact-label">Compact</span>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-64 overflow-y-auto rounded-lg" id="sidebarNotifScroll">
                            <div class="space-y-1.5 notif-list" id="sidebarNotifList">
                                @forelse(($notifications ?? collect()) as $notif)
                                    @php
                                        $sidebarNotifStyles = [
                                            'info' => 'notification-blue',
                                            'warning' => 'notification-yellow',
                                            'success' => 'notification-green',
                                            'danger' => 'notification-red',
                                        ];
                                        $sidebarIconColors = [
                                            'info' => 'text-blue-500',
                                            'warning' => 'text-yellow-600',
                                            'success' => 'text-green-500',
                                            'danger' => 'text-red-500',
                                        ];
                                        $isUnread = !in_array($notif->id, $readNotifIds ?? []);
                                    @endphp
                                    <div class="notification-item notif-card {{ $sidebarNotifStyles[$notif->type] ?? 'notification-gray' }}{{ $isUnread ? ' notif-unread' : '' }}" data-notif-id="{{ $notif->id }}">
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 {{ $sidebarIconColors[$notif->type] ?? 'text-gray-600' }} mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                @if($notif->type === 'success')
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                @elseif($notif->type === 'warning' || $notif->type === 'danger')
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                @else
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                @endif
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <p class="notif-title text-xs sm:text-sm font-semibold text-gray-900">{{ $notif->title }}</p>
                                                    @if($isUnread)
                                                        <span class="notif-unread-dot w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                                                    @endif
                                                </div>
                                                <p class="notif-message text-xs text-gray-600 mt-0.5">{{ Str::limit($notif->message, 80) }}</p>
                                                <p class="notif-time text-[9px] text-gray-400 mt-0.5">{{ ($notif->published_at ?? $notif->created_at)->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="notification-item notification-gray">
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-gray-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-sm font-semibold text-gray-900">No notifications</p>
                                                <p class="text-xs text-gray-600">You're all caught up!</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        @php
            // Variables passed from controller:
            // $strategicObjectivesText, $strategicObjectivesPercent
            // $coreFunctionsText, $coreFunctionsPercent
            // $supportFunctionsText, $supportFunctionsPercent
            // $ipcrAccomplishedText, $ipcrPercentageText, $ipcrPercentageValue
            // $soPerformanceData - array of ['label' => 'SO I', 'name' => '...', 'average' => 3.5]
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Left Main Content (2/3 width) -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                <!-- Welcome Section -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Welcome, {{ explode(' ', auth()->user()->name)[0] }}!</h2>
                    <p class="text-sm sm:text-base text-gray-500 mt-1">Here's a Summary of your performance and upcoming task</p>
                </div>

                <!-- Metrics Cards - Horizontal Scroll on Mobile -->
                <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0 pb-2">
                    <div class="flex sm:grid sm:grid-cols-3 gap-4 min-w-max sm:min-w-0">
                        <!-- Strategic Objectives Card -->
                        <div class="compact-metric sm:metric-card flex-shrink-0 w-64 sm:w-auto">
                            <div class="flex items-center justify-between mb-3 sm:mb-4">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full">{{ $strategicObjectivesPercent }}</span>
                            </div>
                            <div>
                                <h4 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $strategicObjectivesText ?? 'N/A' }}</h4>
                                <p class="text-xs sm:text-sm font-medium text-gray-500 mt-1">Strategic Objectives</p>
                            </div>
                        </div>

                        <!-- Core Functions Card -->
                        <div class="compact-metric sm:metric-card flex-shrink-0 w-64 sm:w-auto">
                            <div class="flex items-center justify-between mb-3 sm:mb-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-full">{{ $coreFunctionsPercent }}</span>
                            </div>
                            <div>
                                <h4 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $coreFunctionsText ?? 'N/A' }}</h4>
                                <p class="text-xs sm:text-sm font-medium text-gray-500 mt-1">Core Functions</p>
                            </div>
                        </div>

                        <!-- Support Functions Card -->
                        <div class="compact-metric sm:metric-card flex-shrink-0 w-64 sm:w-auto">
                            <div class="flex items-center justify-between mb-3 sm:mb-4">
                                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 bg-purple-50 text-purple-600 rounded-full">{{ $supportFunctionsPercent }}</span>
                            </div>
                            <div>
                                <h4 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">{{ $supportFunctionsText ?? 'N/A' }}</h4>
                                <p class="text-xs sm:text-sm font-medium text-gray-500 mt-1">Support Functions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- IPCR Progress Bar -->
                @php
                    // Parse metric card values to calculate total progress
                    $parseMetric = function($text) {
                        if (preg_match('/(\d+)\/(\d+)/', $text, $matches)) {
                            return ['accomplished' => (int)$matches[1], 'total' => (int)$matches[2]];
                        }
                        return ['accomplished' => 0, 'total' => 0];
                    };
                    
                    $strategic = $parseMetric($strategicObjectivesText);
                    $core = $parseMetric($coreFunctionsText);
                    $support = $parseMetric($supportFunctionsText);
                    
                    $totalAccomplished = $strategic['accomplished'] + $core['accomplished'] + $support['accomplished'];
                    $totalGoals = $strategic['total'] + $core['total'] + $support['total'];
                    
                    $calculatedPercentage = $totalGoals > 0 ? round(($totalAccomplished / $totalGoals) * 100, 1) : 0;
                @endphp
                <div class="metric-card">
                    <div class="flex items-center justify-between mb-5 sm:mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">IPCR Completion</h3>
                                <p class="text-xs text-gray-500 font-medium">Semester Progress</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl sm:text-3xl font-bold text-indigo-600 tracking-tight">{{ $calculatedPercentage }}%</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: {{ $calculatedPercentage }}%;"></div>
                        </div>
                        <div class="flex justify-between items-center text-xs sm:text-sm font-medium">
                            <span class="text-gray-500">Accomplished</span>
                            <span class="text-gray-900 bg-gray-100 px-2.5 py-0.5 rounded-md"><span class="text-indigo-600 font-bold">{{ $totalAccomplished }}</span> of {{ $totalGoals }} Goals</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Overview Section -->
                <div class="metric-card">
                    <div class="flex flex-col gap-6 lg:gap-8">
                        <!-- Chart Area -->
                        <div>
                            <div class="flex items-center justify-between mb-4 sm:mb-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Performance Overview</h3>
                                </div>
                            </div>

                            <!-- Section Filter Buttons -->
                            @php
                                $sections = collect($soPerformanceData ?? [])->pluck('section')->unique()->filter()->values()->toArray();
                                $sectionLabels = [
                                    'strategic_objectives' => 'Strategic Objectives',
                                    'core_functions' => 'Core Functions',
                                    'support_functions' => 'Support Functions',
                                ];
                            @endphp
                            @if(count($sections) > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <button type="button" onclick="filterSection('all')" id="filter-all" class="section-filter-btn active px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors">All</button>
                                    @foreach($sections as $sec)
                                        <button type="button" onclick="filterSection('{{ $sec }}')" id="filter-{{ $sec }}" class="section-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors">{{ $sectionLabels[$sec] ?? ucfirst(str_replace('_', ' ', $sec)) }}</button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="relative bg-gray-50/50 rounded-xl p-2 sm:p-4 border border-gray-100" style="height: 350px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>

                        <!-- Expected Target Area -->
                        <div>
                            <div class="flex items-center space-x-3 mb-4 sm:mb-5">
                                <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Performance Objectives</h3>
                            </div>
                            <div id="expectedTargetList" class="space-y-4">
                                @php
                                    $grouped = collect($soPerformanceData ?? [])->groupBy('section');
                                    $sectionColors = [
                                        'strategic_objectives' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'dot' => 'bg-green-500'],
                                        'core_functions' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'dot' => 'bg-purple-500'],
                                        'support_functions' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700', 'dot' => 'bg-orange-500'],
                                    ];
                                @endphp
                                @forelse($grouped as $sectionKey => $items)
                                    @php $colors = $sectionColors[$sectionKey] ?? ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'dot' => 'bg-gray-500']; @endphp
                                    <div class="section-group" data-section="{{ $sectionKey }}">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="w-2 h-2 rounded-full {{ $colors['dot'] }}"></span>
                                            <h4 class="text-xs font-bold {{ $colors['text'] }} uppercase tracking-wider">{{ $sectionLabels[$sectionKey] ?? ucfirst(str_replace('_', ' ', $sectionKey)) }}</h4>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($items as $so)
                                                @php
                                                    $globalIndex = 0;
                                                    foreach ($soPerformanceData as $idx => $entry) {
                                                        if ($entry['label'] === $so['label'] && $entry['section'] === $so['section']) {
                                                            $globalIndex = $idx;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <div onclick="openSoModal({{ $globalIndex !== false ? $globalIndex : 0 }})"
                                                     class="bg-white hover:bg-indigo-50/50 transition-all duration-200 rounded-xl p-3 sm:p-4 border border-gray-100 hover:border-indigo-200 hover:shadow-sm flex items-center justify-between group cursor-pointer transform hover:-translate-y-0.5">
                                                    
                                                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1 min-w-0 pr-4">
                                                        <div class="flex flex-col items-center justify-center flex-shrink-0 w-12 h-12 rounded-xl {{ $so['average'] >= 4.5 ? 'bg-emerald-50 text-emerald-700' : ($so['average'] >= 3.0 ? 'bg-blue-50 text-blue-700' : ($so['average'] > 0 ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700')) }} border border-transparent group-hover:border-current/10 transition-colors">
                                                            <span class="text-sm sm:text-base font-black leading-none">{{ number_format($so['average'], 2) }}</span>
                                                            <span class="text-[8px] sm:text-[9px] font-bold uppercase tracking-wider opacity-70 mt-1">Target</span>
                                                        </div>
                                                        
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-[13px] sm:text-sm text-gray-800 font-semibold leading-snug group-hover:text-indigo-900 transition-colors line-clamp-2" title="{{ $so['name'] }}">{{ $so['name'] }}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center space-x-3 flex-shrink-0">
                                                        @if(($so['actual_rating'] ?? 0) > 0)
                                                            <div class="flex flex-col items-end justify-center">
                                                                <span class="text-[8px] sm:text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Calibrated</span>
                                                                <span class="text-sm font-black {{ $so['actual_rating'] >= 4.5 ? 'text-emerald-500' : ($so['actual_rating'] >= 3.0 ? 'text-blue-500' : 'text-amber-500') }}">{{ number_format($so['actual_rating'], 2) }}</span>
                                                            </div>
                                                        @else
                                                            <div class="flex flex-col items-end justify-center opacity-40">
                                                                <span class="text-[8px] sm:text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Calibrated</span>
                                                                <span class="text-sm font-bold text-gray-400">--</span>
                                                            </div>
                                                        @endif
                                                        <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-center">
                                        <p class="text-xs sm:text-sm text-gray-400 font-medium">No submitted IPCRs yet</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar (1/3 width) -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Upcoming Deadlines -->
                <div class="metric-card">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-5">
                        <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Upcoming Deadlines</h3>
                    </div>
                    <div class="space-y-3">
                        @forelse(($deadlines ?? collect()) as $deadline)
                            @php
                                $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($deadline->deadline_date, false));
                            @endphp
                            <div class="flex items-start space-x-3 sm:space-x-4 p-3 bg-white hover:bg-rose-50/50 border border-gray-100 hover:border-rose-100 rounded-xl transition-all cursor-default">
                                <div class="deadline-badge">
                                    <span class="text-[10px] sm:text-xs uppercase tracking-wider font-semibold opacity-80">{{ $deadline->deadline_date->format('M') }}</span>
                                    <span class="text-lg sm:text-xl font-black leading-none mt-0.5">{{ $deadline->deadline_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <p class="text-sm font-bold text-gray-900">{{ $deadline->title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $deadline->description ?? $daysLeft . ' days remaining' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                                <p class="text-xs text-gray-400 font-medium">No upcoming deadlines</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Notifications -->
                <div class="metric-card hidden lg:block">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Notifications</h3>
                            @if(($unreadCount ?? 0) > 0)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700" id="sidebarNotifBadge">{{ $unreadCount }}</span>
                            @else
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 hidden" id="sidebarNotifBadge">0</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="markAllNotificationsRead()" class="text-[10px] font-semibold text-blue-600 hover:text-blue-800 transition-colors" title="Mark all as read">
                                Mark all as read
                            </button>
                            <button onclick="toggleCompactMode()" class="compact-toggle-btn text-[10px] font-semibold px-2 py-0.5 rounded-full border transition-colors" title="Toggle compact view">
                                <span class="compact-label">Compact</span>
                            </button>
                        </div>
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-lg" id="sidebarNotifScroll">
                        <div class="space-y-1.5 notif-list" id="sidebarNotifList">
                            @forelse(($notifications ?? collect()) as $notif)
                                @php
                                    $sidebarNotifStyles = [
                                        'info' => 'notification-blue',
                                        'warning' => 'notification-yellow',
                                        'success' => 'notification-green',
                                        'danger' => 'notification-red',
                                    ];
                                    $sidebarIconColors = [
                                        'info' => 'text-blue-500',
                                        'warning' => 'text-yellow-600',
                                        'success' => 'text-green-500',
                                        'danger' => 'text-red-500',
                                    ];
                                    $isUnread = !in_array($notif->id, $readNotifIds ?? []);
                                @endphp
                                <div class="notification-item notif-card {{ $sidebarNotifStyles[$notif->type] ?? 'notification-gray' }}{{ $isUnread ? ' notif-unread' : '' }}" data-notif-id="{{ $notif->id }}">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 {{ $sidebarIconColors[$notif->type] ?? 'text-gray-600' }} mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            @if($notif->type === 'success')
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            @elseif($notif->type === 'warning' || $notif->type === 'danger')
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            @else
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            @endif
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="notif-title text-xs sm:text-sm font-semibold text-gray-900">{{ $notif->title }}</p>
                                                @if($isUnread)
                                                    <span class="notif-unread-dot w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                                                @endif
                                            </div>
                                            <p class="notif-message text-xs text-gray-600 mt-0.5">{{ Str::limit($notif->message, 80) }}</p>
                                            <p class="notif-time text-[9px] text-gray-400 mt-0.5">{{ ($notif->published_at ?? $notif->created_at)->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="notification-item notification-gray">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-gray-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs sm:text-sm font-semibold text-gray-900">No notifications</p>
                                            <p class="text-xs text-gray-600">You're all caught up!</p>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Returned IPCR -->
                <div class="metric-card">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-5">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Returned IPCR</h3>
                    </div>
                    @if(isset($returnedCalibration) && $returnedCalibration)
                        <div class="bg-green-50 rounded-xl p-3 sm:p-4 border border-green-200 hover:border-green-300 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900">{{ $activeSubmission->title ?? 'IPCR' }}</p>
                                    <p class="text-xs text-gray-500 font-medium">{{ ($activeSubmission->school_year ?? '') }} &bull; {{ ($activeSubmission->semester ?? '') }}</p>
                                </div>
                                <button onclick="openReturnedCalibrationModal()" class="bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-bold px-3 py-1.5 rounded-lg flex-shrink-0 transition-colors shadow-sm">View</button>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-green-200">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Calibrated</span>
                                    <span class="text-xs text-gray-500">by {{ $returnedCalibration->dean->name ?? 'Dean' }}</span>
                                </div>
                                <span class="text-sm font-bold text-green-700">{{ number_format($returnedCalibration->overall_score, 2) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4 border border-gray-100">
                            <p class="text-xs text-gray-400 text-center font-medium">No calibrated IPCR returned yet</p>
                        </div>
                    @endif
                </div>

                @if(auth()->user()->hasRole('dean'))
                <!-- Dean: Faculty IPCR Submissions Review -->
                <div class="metric-card">
                    <div class="flex items-center justify-between mb-4 sm:mb-5">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Faculty IPCRs</h3>
                                <p class="text-xs text-gray-500">{{ $departmentCode ?: $departmentName }}</p>
                            </div>
                        </div>
                        <button id="expandFacultyBtn" onclick="expandSection('faculty')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors whitespace-nowrap">View All <svg class="w-3 h-3 inline ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                    <div id="deanFacultySubmissionsList">
                        <div class="flex justify-center py-4">
                            <svg class="animate-spin h-5 w-5 text-indigo-500" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Dean: Cross-Calibration with Other Deans -->
                <div class="metric-card">
                    <div class="flex items-center justify-between mb-4 sm:mb-5">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">Dean Calibration</h3>
                                <p class="text-xs text-gray-500">Other deans' IPCRs</p>
                            </div>
                        </div>
                        <button id="expandCalibrationBtn" onclick="expandSection('calibration')" class="text-xs font-semibold text-amber-600 hover:text-amber-800 transition-colors whitespace-nowrap">View All <svg class="w-3 h-3 inline ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                    <div id="deanCalibrationList">
                        <div class="flex justify-center py-4">
                            <svg class="animate-spin h-5 w-5 text-amber-500" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- SO Detail Modal -->
    <div id="soDetailModal" class="so-modal-overlay" onclick="closeSoModal(event)">
        <div class="so-modal-container" onclick="event.stopPropagation()">
            <!-- Header -->
            <div id="soModalHeader" class="so-modal-header">
                <div class="flex items-start justify-between w-full">
                    <div class="flex-1 min-w-0 pr-4">
                        <div class="flex items-center space-x-2 mb-1.5">
                            <span id="soModalSectionBadge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200"></span>
                        </div>
                        <h2 id="soModalTitle" class="text-base sm:text-lg font-bold leading-tight text-gray-900 mb-1 truncate"></h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold text-gray-700">Average:</span>
                            <span id="soModalAvgValue" class="text-xs font-bold text-gray-900 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200"></span>
                        </div>
                    </div>
                    <button onclick="closeSoModal()" class="flex-shrink-0 w-8 h-8 rounded bg-gray-50 hover:bg-gray-200 flex items-center justify-center transition-colors border border-gray-200 text-gray-500 hover:text-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="so-modal-body">
                <!-- Rows Table -->
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center space-x-2">
                        <span class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center"><svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></span>
                        <span>Performance Rows</span>
                    </h3>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-xs border-collapse min-w-[600px]">
                            <thead class="bg-gray-50/80 backdrop-blur-sm sticky top-0 z-10 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-1/4">MFO</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-1/4">Success Indicators</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-1/4">Actual Accomplishments</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider w-12">Q</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider w-12">E</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider w-12">T</th>
                                    <th class="px-3 py-3 text-center text-[11px] font-bold text-indigo-600 uppercase tracking-wider w-16 bg-indigo-50/50">Avg</th>
                                </tr>
                            </thead>
                            <tbody id="soModalTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Supporting Documents -->
                <div>
                    <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center space-x-2">
                        <span class="w-5 h-5 rounded-full bg-purple-100 flex items-center justify-center"><svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg></span>
                        <span>Supporting Documents</span>
                        <span id="soModalDocCount" class="text-xs bg-purple-100 text-purple-700 font-bold px-2 py-0.5 rounded-full"></span>
                    </h3>
                    <div id="soModalDocsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageViewerOverlay" class="fixed inset-0 bg-black/90 z-[1100] hidden items-center justify-center opacity-0 transition-opacity duration-300" onclick="closeImageViewer()">
        <button class="absolute top-4 right-4 text-white hover:text-gray-300 z-50 p-2" onclick="closeImageViewer()">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="imageViewerContent" class="max-w-[90vw] max-h-[90vh] object-contain transform scale-95 transition-transform duration-300 rounded-lg shadow-2xl" src="" alt="Expanded View" onclick="event.stopPropagation()">
    </div>

@if(isset($returnedCalibration) && $returnedCalibration)
<!-- Returned Calibration Preview Modal -->
<div id="returnedCalibrationModal" class="fixed inset-0 z-[1000] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeReturnedCalibrationModal()"></div>
    <div class="relative mx-auto mt-2 sm:mt-8 mb-2 sm:mb-8 w-full max-w-6xl bg-white rounded-2xl shadow-lg max-h-[98vh] sm:max-h-[90vh] overflow-y-auto px-2 sm:px-0">
        <!-- Header -->
        <div class="bg-green-50 px-3 sm:px-6 py-3 sm:py-4 border-b border-green-200 sticky top-0 z-10">
            <div class="flex justify-between items-start mb-3 sm:mb-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="text-sm sm:text-lg font-bold text-gray-900">{{ $activeSubmission->title ?? 'IPCR' }}</h2>
                        <span class="font-semibold px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">Calibrated</span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600">Year: <span class="font-semibold">{{ $activeSubmission->school_year ?? '' }}</span></p>
                    <p class="text-xs sm:text-sm text-gray-600">Period: <span class="font-semibold">{{ $activeSubmission->semester ?? '' }}</span></p>
                </div>
                <button onclick="closeReturnedCalibrationModal()" class="text-gray-500 hover:text-gray-700 ml-2 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
                <div class="flex flex-col sm:block">
                    <span class="text-gray-600">Ratee:</span>
                    <span class="font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex flex-col sm:block">
                    <span class="text-gray-600">Calibrated By:</span>
                    <span class="font-semibold text-gray-900">{{ $returnedCalibration->dean->name ?? 'Dean' }}</span>
                </div>
                <div class="flex flex-col sm:block">
                    <span class="text-gray-600">Calibration Score:</span>
                    <span class="font-bold text-green-700">{{ number_format($returnedCalibration->overall_score, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto px-2 sm:px-6 py-3 sm:py-4">
            <table class="w-full border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">MFO</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 25%;">Success Indicators<br><span class="font-semibold text-gray-500">(Target + Measures)</span></th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 20%;">Actual Accomplishments</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" colspan="4">Rating</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">Remarks</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">Q</th>
                        <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">E</th>
                        <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">T</th>
                        <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">A</th>
                    </tr>
                </thead>
                <tbody id="returnedCalibrationTableBody"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="px-2 sm:px-6 py-3 sm:py-4 bg-green-50 border-t border-green-200 sticky bottom-0 z-10">
            <div class="flex items-center justify-between">
                <div class="text-xs sm:text-sm">
                    <span class="text-gray-600">Overall Calibrated Score:</span>
                    <span class="ml-1 font-bold text-green-700 text-sm sm:text-base">{{ number_format($returnedCalibration->overall_score, 2) }}</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button onclick="exportReturnedCalibration()" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-300 hover:bg-blue-100 flex items-center gap-1">
                        <i class="fas fa-file-excel"></i>
                        Export
                    </button>
                    <button onclick="closeReturnedCalibrationModal()" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->hasRole('dean'))
<!-- Dean IPCR Preview Modal -->
<div id="deanPreviewModal" class="fixed inset-0 z-[1000] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeanPreviewModal()"></div>
    <div class="relative mx-auto mt-2 sm:mt-8 mb-2 sm:mb-8 w-full max-w-6xl bg-white rounded-2xl shadow-lg max-h-[98vh] sm:max-h-[90vh] overflow-y-auto px-2 sm:px-0">
        <!-- Document Header -->
        <div class="bg-gray-50 px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-300 sticky top-0 bg-white z-10">
            <div class="flex justify-between items-start mb-3 sm:mb-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 id="deanPreviewTitle" class="text-sm sm:text-lg font-bold text-gray-900"></h2>
                        <span id="deanPreviewStatus" class="font-semibold px-2 py-0.5 rounded text-xs"></span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-600">Year: <span id="deanPreviewSchoolYear" class="font-semibold"></span></p>
                    <p class="text-xs sm:text-sm text-gray-600">Period: <span id="deanPreviewSemester" class="font-semibold"></span></p>
                </div>
                <button onclick="closeDeanPreviewModal()" class="text-gray-500 hover:text-gray-700 ml-2 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
                <div class="flex flex-col sm:block">
                    <span class="text-gray-600">Ratee:</span>
                    <span id="deanPreviewRatee" class="font-semibold text-gray-900 truncate"></span>
                </div>
                <div class="flex flex-col sm:block">
                    <span class="text-gray-600">Approved By:</span>
                    <span id="deanPreviewApprovedBy" class="font-semibold text-gray-900 truncate"></span>
                </div>
                <div class="flex flex-col sm:block">
                    <span class="text-gray-600">Noted By:</span>
                    <span id="deanPreviewNotedBy" class="font-semibold text-gray-900 truncate"></span>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="deanPreviewLoading" class="flex items-center justify-center py-20">
            <svg class="animate-spin h-8 w-8 text-indigo-500" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <!-- Table Content -->
        <div id="deanPreviewContent" class="hidden">
            <div class="overflow-x-auto px-2 sm:px-6 py-3 sm:py-4">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">MFO</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 25%;">Success Indicators<br><span class="font-semibold text-gray-500">(Target + Measures)</span></th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 20%;">Actual Accomplishments</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" colspan="4">Rating</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">Remarks</th>
                        </tr>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">Q</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">E</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">T</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600" style="width: 8%;">A</th>
                        </tr>
                    </thead>
                    <tbody id="deanPreviewTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-2 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-300 sticky bottom-0 z-10">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div id="deanPreviewOverallAvg" class="hidden text-xs sm:text-sm">
                        <span class="text-gray-600">Overall Average Rating:</span>
                        <span id="deanPreviewAvgValue" class="ml-1 font-bold text-indigo-700 text-sm sm:text-base"></span>
                    </div>
                    <span id="deanCalibrationStatus" class="hidden text-xs font-semibold px-2 py-1 rounded"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="deanSaveDraftBtn" onclick="saveDeanCalibration('draft')" class="hidden px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-300 hover:bg-amber-100 transition">
                        <i class="fas fa-save mr-1"></i>Save Draft
                    </button>
                    <button id="deanCalibrateBtn" onclick="saveDeanCalibration('calibrated')" class="hidden px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition">
                        <i class="fas fa-check-circle mr-1"></i>Calibrate
                    </button>
                    <button onclick="closeDeanPreviewModal()" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dean SO Documents Viewer Modal (read-only) -->
<div id="deanSoDocsModal" class="fixed inset-0 z-[1100] hidden">
    <div class="absolute inset-0 bg-black/60" onclick="closeDeanSoDocsModal()"></div>
    <div class="relative mx-auto mt-10 sm:mt-20 w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden z-10">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <h3 id="deanSoDocsTitle" class="text-sm font-bold text-gray-900 truncate"></h3>
                <p id="deanSoDocsDesc" class="text-xs text-gray-500 truncate mt-0.5"></p>
            </div>
            <button onclick="closeDeanSoDocsModal()" class="text-gray-400 hover:text-gray-600 ml-3 flex-shrink-0 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="deanSoDocsList" class="px-5 py-4 max-h-80 overflow-y-auto">
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin h-5 w-5 text-gray-300 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="text-sm text-gray-400">Loading documents...</span>
            </div>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button onclick="closeDeanSoDocsModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
        </div>
    </div>
</div>

<!-- Calibration Confirm Modal -->
<div id="calibrationConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-[2000] p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in z-10">
        <div class="bg-yellow-50 border-b border-yellow-200 px-6 py-4 flex items-center gap-3">
            <div class="bg-yellow-100 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Finalize Calibration?</h2>
                <p class="text-sm text-gray-600">This action cannot be undone</p>
            </div>
        </div>

        <div class="px-6 py-4">
            <p class="text-gray-700 mb-2 text-sm">Are you sure you want to finalize this calibration?</p>
            <p class="text-sm text-gray-600">The scores will be reflected on the dashboard.</p>
        </div>

        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
            <button type="button" onclick="closeCalibrationConfirmModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                Cancel
            </button>
            <button type="button" id="confirmCalibrationBtn" class="px-4 py-2 rounded-lg font-semibold text-white bg-yellow-600 hover:bg-yellow-700 transition flex items-center gap-2 text-sm">
                <i class="fas fa-check"></i> <span>Confirm</span>
            </button>
        </div>
    </div>
</div>

@endif

    @php
        $facultyIndexPageConfig = [
            'csrfToken' => csrf_token(),
            'soPerformanceData' => $soPerformanceData ?? [],
            'directorSubmissionTrendData' => $directorSubmissionTrendDataSafe,
            'routes' => [
                'myIpcrs' => route('faculty.my-ipcrs'),
                'facultySubmissions' => url('/dean/review/faculty-submissions'),
                'deanSubmissions' => url('/dean/review/dean-submissions'),
            ],
            'returnedCalibration' => (isset($returnedCalibration) && $returnedCalibration) ? [
                'calibrationData' => $returnedCalibration->calibration_data ?? [],
                'tableHtml' => $activeSubmission->table_body_html ?? '',
                'submissionId' => $returnedCalibration->ipcr_submission_id ?? null,
            ] : null,
        ];
    @endphp
    <script type="application/json" id="faculty-index-page-config">
        @json($facultyIndexPageConfig)
    </script>
    <script src="{{ Vite::asset('resources/js/dashboard_faculty_index_page.js') }}"></script>
</body>
</html>
