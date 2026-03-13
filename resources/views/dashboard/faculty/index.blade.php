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
    <script>
        window.soPerformanceData = @json($soPerformanceData ?? []);
    </script>
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
        @php
            // Variables passed from controller:
            // $strategicObjectivesText, $strategicObjectivesPercent
            // $coreFunctionsText, $coreFunctionsPercent
            // $supportFunctionsText, $supportFunctionsPercent
            // $ipcrAccomplishedText, $ipcrPercentageText, $ipcrPercentageValue
            // $soPerformanceData — array of ['label' => 'SO I', 'name' => '...', 'average' => 3.5]
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
                <button onclick="closeReturnedCalibrationModal()" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
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

<script>
var allFacultySubmissions = [];
var allCalibrationSubmissions = [];
var currentDeanPreviewSubmissionId = null;
var currentDeanPreviewType = null;

(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const myIpcrsUrl = '{{ route('faculty.my-ipcrs') }}';
    const facultySubmissionsUrl = '{{ url('/dean/review/faculty-submissions') }}';
    const deanSubmissionsUrl = '{{ url('/dean/review/dean-submissions') }}';
    const PREVIEW_LIMIT = 3;

    function renderFacultyCard(sub) {
        var calBadge = '';
        var borderClass = 'border-indigo-200';
        var bgClass = 'bg-indigo-50';
        if (sub.calibration_status === 'calibrated') {
            calBadge = '<div class="flex items-center gap-1.5 mt-1"><span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded"><i class="fas fa-check-circle mr-0.5"></i>Calibrated</span><span class="text-xs font-bold text-green-700">' + (parseFloat(sub.calibration_score) || 0).toFixed(2) + '</span></div>';
            borderClass = 'border-green-200';
            bgClass = 'bg-green-50';
        } else if (sub.calibration_status === 'draft') {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded"><i class="fas fa-pencil-alt mr-0.5"></i>Draft</span></div>';
        } else {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded">Pending</span></div>';
        }
        return '<div class="p-3 ' + bgClass + ' rounded-xl border ' + borderClass + '">' +
            '<div class="flex justify-between items-start gap-2">' +
                '<div class="flex-1 min-w-0">' +
                    '<p class="text-sm font-semibold text-gray-900 truncate">' + (sub.user_name || 'Unknown') + '</p>' +
                    '<p class="text-xs text-gray-600 truncate">' + (sub.title || 'Untitled') + '</p>' +
                    '<p class="text-xs text-gray-500">' + (sub.school_year || '') + ' &bull; ' + (sub.semester || '') + '</p>' +
                    calBadge +
                '</div>' +
                '<div class="flex flex-col gap-1 flex-shrink-0">' +
                    '<button onclick="viewFacultySubmission(' + sub.id + ')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-1.5 px-3 rounded text-center">View</button>' +
                    '<span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded text-center">' + (sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : 'N/A') + '</span>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function renderCalibrationCard(sub) {
        var calBadge = '';
        var borderClass = 'border-amber-200';
        var bgClass = 'bg-amber-50';
        if (sub.calibration_status === 'calibrated') {
            calBadge = '<div class="flex items-center gap-1.5 mt-1"><span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded"><i class="fas fa-check-circle mr-0.5"></i>Calibrated</span><span class="text-xs font-bold text-green-700">' + (parseFloat(sub.calibration_score) || 0).toFixed(2) + '</span></div>';
            borderClass = 'border-green-200';
            bgClass = 'bg-green-50';
        } else if (sub.calibration_status === 'draft') {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded"><i class="fas fa-pencil-alt mr-0.5"></i>Draft</span></div>';
        } else {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded">Pending</span></div>';
        }
        return '<div class="p-3 ' + bgClass + ' rounded-xl border ' + borderClass + '">' +
            '<div class="flex justify-between items-start gap-2">' +
                '<div class="flex-1 min-w-0">' +
                    '<p class="text-sm font-semibold text-gray-900 truncate">' + (sub.user_name || 'Unknown') + '</p>' +
                    '<p class="text-xs text-amber-700 font-medium">' + (sub.department || 'N/A') + '</p>' +
                    '<p class="text-xs text-gray-600 truncate">' + (sub.title || 'Untitled') + '</p>' +
                    '<p class="text-xs text-gray-500">' + (sub.school_year || '') + ' &bull; ' + (sub.semester || '') + '</p>' +
                    calBadge +
                '</div>' +
                '<div class="flex flex-col gap-1 flex-shrink-0">' +
                    '<button onclick="viewDeanSubmission(' + sub.id + ')" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold py-1.5 px-3 rounded text-center">View</button>' +
                    '<span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded text-center">' + (sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : 'N/A') + '</span>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    async function loadDeanFacultySubmissions() {
        const container = document.getElementById('deanFacultySubmissionsList');
        if (!container) return;
        try {
            const response = await fetch(facultySubmissionsUrl, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (!data.success || data.submissions.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-500 text-center py-4">No faculty submissions yet</p>';
                return;
            }
            allFacultySubmissions = data.submissions;
            const preview = data.submissions.slice(0, PREVIEW_LIMIT);
            container.innerHTML = '<div class="space-y-3">' + preview.map(renderFacultyCard).join('') + '</div>';
            if (data.submissions.length > PREVIEW_LIMIT) {
                document.getElementById('expandFacultyBtn').classList.remove('hidden');
            }
        } catch (error) {
            container.innerHTML = '<p class="text-xs text-red-500 text-center py-4">Failed to load submissions</p>';
        }
    }

    async function loadDeanCalibrationSubmissions() {
        const container = document.getElementById('deanCalibrationList');
        if (!container) return;
        try {
            const response = await fetch(deanSubmissionsUrl, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (!data.success || data.submissions.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-500 text-center py-4">No other deans\' submissions yet</p>';
                return;
            }
            allCalibrationSubmissions = data.submissions;
            const preview = data.submissions.slice(0, PREVIEW_LIMIT);
            container.innerHTML = '<div class="space-y-3">' + preview.map(renderCalibrationCard).join('') + '</div>';
            if (data.submissions.length > PREVIEW_LIMIT) {
                document.getElementById('expandCalibrationBtn').classList.remove('hidden');
            }
        } catch (error) {
            container.innerHTML = '<p class="text-xs text-red-500 text-center py-4">Failed to load submissions</p>';
        }
    }

    // Expose render functions for expand
    window._renderFacultyCard = renderFacultyCard;
    window._renderCalibrationCard = renderCalibrationCard;

    document.addEventListener('DOMContentLoaded', function() {
        loadDeanFacultySubmissions();
        loadDeanCalibrationSubmissions();
    });
})();

var currentExpandedType = null;
var currentExpandedFilter = 'all';

function expandSection(type) {
    const mainContent = document.getElementById('dashboardMainContent');
    const expandedView = document.getElementById('expandedView');
    const titleEl = document.getElementById('expandedViewTitle');
    const subtitleEl = document.getElementById('expandedViewSubtitle');
    const iconEl = document.getElementById('expandedViewIcon');
    const filtersEl = document.getElementById('expandedViewFilters');

    currentExpandedType = type;
    currentExpandedFilter = 'all';
    mainContent.classList.add('hidden');
    expandedView.classList.remove('hidden');

    if (type === 'faculty') {
        titleEl.textContent = 'Faculty IPCRs';
        subtitleEl.textContent = allFacultySubmissions.length + ' submissions';
        iconEl.className = 'w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';

        // Build filter buttons
        var pendingCount = allFacultySubmissions.filter(function(s) { return s.calibration_status !== 'calibrated'; }).length;
        var calibratedCount = allFacultySubmissions.filter(function(s) { return s.calibration_status === 'calibrated'; }).length;
        filtersEl.innerHTML =
            '<button onclick="filterExpanded(\'all\')" id="exp-filter-all" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-indigo-600 text-white border-indigo-600">All (' + allFacultySubmissions.length + ')</button>' +
            '<button onclick="filterExpanded(\'pending\')" id="exp-filter-pending" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Pending (' + pendingCount + ')</button>' +
            '<button onclick="filterExpanded(\'calibrated\')" id="exp-filter-calibrated" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Calibrated (' + calibratedCount + ')</button>';
        filtersEl.classList.remove('hidden');

        renderExpandedList('all');
    } else {
        titleEl.textContent = 'Dean Calibration';
        subtitleEl.textContent = allCalibrationSubmissions.length + ' submissions';
        iconEl.className = 'w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';

        var dPendingCount = allCalibrationSubmissions.filter(function(s) { return s.calibration_status !== 'calibrated'; }).length;
        var dCalibratedCount = allCalibrationSubmissions.filter(function(s) { return s.calibration_status === 'calibrated'; }).length;
        filtersEl.innerHTML =
            '<button onclick="filterExpanded(\'all\')" id="exp-filter-all" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-amber-600 text-white border-amber-600">All (' + allCalibrationSubmissions.length + ')</button>' +
            '<button onclick="filterExpanded(\'pending\')" id="exp-filter-pending" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Pending (' + dPendingCount + ')</button>' +
            '<button onclick="filterExpanded(\'calibrated\')" id="exp-filter-calibrated" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Calibrated (' + dCalibratedCount + ')</button>';
        filtersEl.classList.remove('hidden');

        renderExpandedList('all');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

window.filterExpanded = function(filter) {
    currentExpandedFilter = filter;
    var activeColor = currentExpandedType === 'faculty' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-amber-600 text-white border-amber-600';
    document.querySelectorAll('.exp-filter-btn').forEach(function(btn) {
        btn.className = 'exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
    });
    var activeBtn = document.getElementById('exp-filter-' + filter);
    if (activeBtn) activeBtn.className = 'exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors ' + activeColor;
    renderExpandedList(filter);
};

function renderExpandedList(filter) {
    var listEl = document.getElementById('expandedListContent');
    var subtitleEl = document.getElementById('expandedViewSubtitle');
    var items, renderer;

    if (currentExpandedType === 'faculty') {
        items = allFacultySubmissions;
        renderer = window._renderFacultyCard;
    } else {
        items = allCalibrationSubmissions;
        renderer = window._renderCalibrationCard;
    }

    var filtered = items;
    if (filter === 'pending') {
        filtered = items.filter(function(s) { return s.calibration_status !== 'calibrated'; });
    } else if (filter === 'calibrated') {
        filtered = items.filter(function(s) { return s.calibration_status === 'calibrated'; });
    }

    subtitleEl.textContent = filtered.length + ' of ' + items.length + ' submissions' + (filter !== 'all' ? ' (' + filter + ')' : '');

    if (filtered.length === 0) {
        listEl.innerHTML = '<div class="text-center py-8"><p class="text-sm text-gray-400">No ' + filter + ' submissions found</p></div>';
    } else {
        listEl.innerHTML = filtered.map(renderer).join('');
    }
}

function collapseExpandedView() {
    document.getElementById('expandedView').classList.add('hidden');
    document.getElementById('dashboardMainContent').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// View a faculty IPCR submission in preview modal
window.viewFacultySubmission = async function(submissionId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const modal = document.getElementById('deanPreviewModal');
    const tableBody = document.getElementById('deanPreviewTableBody');
    const titleEl = document.getElementById('deanPreviewTitle');
    const yearEl = document.getElementById('deanPreviewSchoolYear');
    const semEl = document.getElementById('deanPreviewSemester');
    const rateeEl = document.getElementById('deanPreviewRatee');
    const approvedByEl = document.getElementById('deanPreviewApprovedBy');
    const notedByEl = document.getElementById('deanPreviewNotedBy');
    const statusEl = document.getElementById('deanPreviewStatus');
    const loading = document.getElementById('deanPreviewLoading');
    const content = document.getElementById('deanPreviewContent');

    currentDeanPreviewSubmissionId = submissionId;
    currentDeanPreviewType = 'faculty';
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    document.getElementById('deanPreviewOverallAvg').classList.add('hidden');
    resetCalibrationUI();

    try {
        const response = await fetch('/dean/review/faculty-submissions/' + submissionId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await response.json();
        if (data.success && data.submission) {
            const sub = data.submission;
            titleEl.textContent = sub.title || 'IPCR';
            if (yearEl) yearEl.textContent = sub.school_year || '';
            if (semEl) semEl.textContent = sub.semester || '';
            if (rateeEl) rateeEl.textContent = sub.user_name || 'Unknown';
            if (approvedByEl) approvedByEl.textContent = sub.approved_by || '—';
            if (notedByEl) notedByEl.textContent = sub.noted_by || '—';
            if (statusEl) {
                const st = sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : '';
                statusEl.textContent = st;
                statusEl.className = 'font-semibold px-2 py-0.5 rounded text-xs ' +
                    (sub.status === 'submitted' ? 'bg-blue-100 text-blue-700' :
                     sub.status === 'approved' ? 'bg-green-100 text-green-700' :
                     sub.status === 'returned' ? 'bg-red-100 text-red-700' :
                     'bg-gray-100 text-gray-700');
            }
            if (tableBody && sub.table_body_html) {
                tableBody.innerHTML = sub.table_body_html;
                makeTableCalibrationEditable(tableBody);
                if (sub.calibration && sub.calibration.calibration_data) {
                    applyCalibrationData(tableBody, sub.calibration.calibration_data);
                }
                labelQetaInputsDean(tableBody);
                computeOverallAverage(tableBody);
                attachCalibrationInputListeners(tableBody);
                attachSoDocClickHandlers(tableBody, sub.user_id, submissionId, 'ipcr_submission');
                showCalibrationButtons(sub.calibration);
            }
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            alert('Submission not found.');
        }
    } catch (error) {
        console.error('Error:', error);
        modal.classList.add('hidden');
        alert('Failed to load submission.');
    }
};

// View a dean IPCR submission in preview modal
window.viewDeanSubmission = async function(submissionId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const modal = document.getElementById('deanPreviewModal');
    const tableBody = document.getElementById('deanPreviewTableBody');
    const titleEl = document.getElementById('deanPreviewTitle');
    const yearEl = document.getElementById('deanPreviewSchoolYear');
    const semEl = document.getElementById('deanPreviewSemester');
    const rateeEl = document.getElementById('deanPreviewRatee');
    const approvedByEl = document.getElementById('deanPreviewApprovedBy');
    const notedByEl = document.getElementById('deanPreviewNotedBy');
    const statusEl = document.getElementById('deanPreviewStatus');
    const loading = document.getElementById('deanPreviewLoading');
    const content = document.getElementById('deanPreviewContent');

    currentDeanPreviewSubmissionId = submissionId;
    currentDeanPreviewType = 'dean';
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    document.getElementById('deanPreviewOverallAvg').classList.add('hidden');
    resetCalibrationUI();

    try {
        const response = await fetch('/dean/review/dean-submissions/' + submissionId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await response.json();
        if (data.success && data.submission) {
            const sub = data.submission;
            const deptLabel = sub.department ? ' (' + sub.department + ')' : '';
            titleEl.textContent = (sub.title || 'IPCR') + deptLabel;
            if (yearEl) yearEl.textContent = sub.school_year || '';
            if (semEl) semEl.textContent = sub.semester || '';
            if (rateeEl) rateeEl.textContent = (sub.user_name || 'Unknown') + deptLabel;
            if (approvedByEl) approvedByEl.textContent = sub.approved_by || '—';
            if (notedByEl) notedByEl.textContent = sub.noted_by || '—';
            if (statusEl) {
                const st = sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : '';
                statusEl.textContent = st;
                statusEl.className = 'font-semibold px-2 py-0.5 rounded text-xs ' +
                    (sub.status === 'submitted' ? 'bg-blue-100 text-blue-700' :
                     sub.status === 'approved' ? 'bg-green-100 text-green-700' :
                     sub.status === 'returned' ? 'bg-red-100 text-red-700' :
                     'bg-gray-100 text-gray-700');
            }
            if (tableBody && sub.table_body_html) {
                tableBody.innerHTML = sub.table_body_html;
                makeTableCalibrationEditable(tableBody);
                if (sub.calibration && sub.calibration.calibration_data) {
                    applyCalibrationData(tableBody, sub.calibration.calibration_data);
                }
                labelQetaInputsDean(tableBody);
                computeOverallAverage(tableBody);
                attachCalibrationInputListeners(tableBody);
                attachSoDocClickHandlers(tableBody, sub.user_id, submissionId, 'ipcr_submission');
                showCalibrationButtons(sub.calibration);
            }
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            alert('Submission not found.');
        }
    } catch (error) {
        console.error('Error:', error);
        modal.classList.add('hidden');
        alert('Failed to load submission.');
    }
};

window.closeDeanPreviewModal = function() {
    document.getElementById('deanPreviewModal').classList.add('hidden');
};

// Make table editable only for Q, E, T, Remarks columns (dean calibration)
function makeTableCalibrationEditable(tableBody) {
    var rowIndex = 0;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');

        var cells = row.querySelectorAll('td');
        cells.forEach(function(cell, cellIdx) {
            cell.setAttribute('contenteditable', 'false');
            cell.style.userSelect = 'none';
            cell.querySelectorAll('input, textarea').forEach(function(el) {
                el.setAttribute('readonly', 'true');
                el.setAttribute('disabled', 'true');
                el.style.pointerEvents = 'none';
            });
        });

        // For data rows (8 cells: MFO|SI|Accomplishments|Q|E|T|A|Remarks), enable Q(3), E(4), T(5), Remarks(7)
        if (!isHeaderRow && cells.length >= 8) {
            row.setAttribute('data-calibration-row', rowIndex);
            [3, 4, 5].forEach(function(idx) {
                var input = cells[idx] ? cells[idx].querySelector('input[type="number"]') : null;
                if (input) {
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                    input.style.pointerEvents = 'auto';
                    input.style.backgroundColor = '#fefce8';
                    input.style.border = '1px solid #fbbf24';
                    input.style.borderRadius = '4px';
                    input.setAttribute('data-calibration-field', idx === 3 ? 'q' : idx === 4 ? 'e' : 't');
                    input.min = '1';
                    input.max = '5';
                    input.step = '0.01';
                }
            });
            // Remarks cell (index 7) - enable text input or make contenteditable
            if (cells[7]) {
                var remarksInput = cells[7].querySelector('input, textarea');
                if (remarksInput) {
                    remarksInput.removeAttribute('readonly');
                    remarksInput.removeAttribute('disabled');
                    remarksInput.style.pointerEvents = 'auto';
                    remarksInput.style.backgroundColor = '#fefce8';
                    remarksInput.style.border = '1px solid #fbbf24';
                    remarksInput.style.borderRadius = '4px';
                    remarksInput.setAttribute('data-calibration-field', 'remarks');
                } else {
                    cells[7].setAttribute('contenteditable', 'true');
                    cells[7].style.backgroundColor = '#fefce8';
                    cells[7].style.border = '1px solid #fbbf24';
                    cells[7].style.borderRadius = '4px';
                    cells[7].style.userSelect = 'text';
                    cells[7].style.cursor = 'text';
                    cells[7].setAttribute('data-calibration-field', 'remarks');
                }
            }
            rowIndex++;
        }
    });
}

// Apply saved calibration data to table inputs
function applyCalibrationData(tableBody, calibrationData) {
    if (!calibrationData || !Array.isArray(calibrationData)) return;
    var dataRowIndex = 0;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');
        var cells = row.querySelectorAll('td');
        if (!isHeaderRow && cells.length >= 8) {
            var rowData = calibrationData[dataRowIndex];
            if (rowData) {
                var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
                var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
                var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
                if (qInput && rowData.q !== undefined && rowData.q !== null) qInput.value = rowData.q;
                if (eInput && rowData.e !== undefined && rowData.e !== null) eInput.value = rowData.e;
                if (tInput && rowData.t !== undefined && rowData.t !== null) tInput.value = rowData.t;
                if (rowData.remarks !== undefined && rowData.remarks !== null) {
                    var remarksInput = cells[7] ? cells[7].querySelector('input, textarea') : null;
                    if (remarksInput) {
                        remarksInput.value = rowData.remarks;
                    } else if (cells[7]) {
                        cells[7].textContent = rowData.remarks;
                    }
                }
            }
            dataRowIndex++;
        }
    });
}

// Attach input listeners for live A=(Q+E+T)/3 computation during calibration
function attachCalibrationInputListeners(tableBody) {
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');
        var cells = row.querySelectorAll('td');
        if (!isHeaderRow && cells.length >= 7) {
            var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
            var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
            var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            if (qInput && eInput && tInput && aInput) {
                var recompute = function() {
                    var q = parseFloat(qInput.value);
                    var e = parseFloat(eInput.value);
                    var t = parseFloat(tInput.value);
                    if (!isNaN(q) && !isNaN(e) && !isNaN(t)) {
                        aInput.value = ((q + e + t) / 3).toFixed(2);
                    }
                    computeOverallAverage(tableBody);
                };
                qInput.addEventListener('input', recompute);
                eInput.addEventListener('input', recompute);
                tInput.addEventListener('input', recompute);
            }
        }
    });
}

// Collect calibration data from the table
function collectCalibrationData(tableBody) {
    var data = [];
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');
        var cells = row.querySelectorAll('td');
        if (!isHeaderRow && cells.length >= 8) {
            var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
            var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
            var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            var remarksInput = cells[7] ? cells[7].querySelector('input, textarea') : null;
            var remarksValue = remarksInput ? remarksInput.value : (cells[7] ? cells[7].textContent.trim() : '');
            data.push({
                q: qInput ? parseFloat(qInput.value) || 0 : 0,
                e: eInput ? parseFloat(eInput.value) || 0 : 0,
                t: tInput ? parseFloat(tInput.value) || 0 : 0,
                a: aInput ? parseFloat(aInput.value) || 0 : 0,
                remarks: remarksValue
            });
        }
    });
    return data;
}

// Show/hide calibration buttons and status
function showCalibrationButtons(calibration) {
    var draftBtn = document.getElementById('deanSaveDraftBtn');
    var calibrateBtn = document.getElementById('deanCalibrateBtn');
    var statusEl = document.getElementById('deanCalibrationStatus');

    draftBtn.classList.remove('hidden');
    calibrateBtn.classList.remove('hidden');

    if (calibration) {
        statusEl.classList.remove('hidden');
        if (calibration.status === 'calibrated') {
            statusEl.textContent = 'Calibrated';
            statusEl.className = 'text-xs font-semibold px-2 py-1 rounded bg-green-100 text-green-700';
        } else {
            statusEl.textContent = 'Draft';
            statusEl.className = 'text-xs font-semibold px-2 py-1 rounded bg-amber-100 text-amber-700';
        }
    } else {
        statusEl.classList.add('hidden');
    }
}

// Reset calibration UI elements
function resetCalibrationUI() {
    document.getElementById('deanSaveDraftBtn').classList.add('hidden');
    document.getElementById('deanCalibrateBtn').classList.add('hidden');
    document.getElementById('deanCalibrationStatus').classList.add('hidden');
}

// Save calibration (draft or finalize)
window.saveDeanCalibration = async function(status) {
    if (!currentDeanPreviewSubmissionId) return;

    var tableBody = document.getElementById('deanPreviewTableBody');
    var calibrationData = collectCalibrationData(tableBody);
    var avgEl = document.getElementById('deanPreviewAvgValue');
    var overallScore = avgEl ? parseFloat(avgEl.textContent) || 0 : 0;

    var actionLabel = status === 'calibrated' ? 'Calibrate' : 'Save Draft';
    if (status === 'calibrated') {
        const confirmed = await new Promise((resolve) => {
            const modal = document.getElementById('calibrationConfirmModal');
            document.getElementById('confirmCalibrationBtn').onclick = function() {
                modal.classList.add('hidden');
                resolve(true);
            };
            window.closeCalibrationConfirmModal = function() {
                modal.classList.add('hidden');
                resolve(false);
            };
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
        if (!confirmed) return;
    }

    var btn = status === 'calibrated' ? document.getElementById('deanCalibrateBtn') : document.getElementById('deanSaveDraftBtn');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>' + actionLabel + '...';

    try {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var response = await fetch('/dean/review/calibrations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                ipcr_submission_id: currentDeanPreviewSubmissionId,
                calibration_data: calibrationData,
                overall_score: overallScore,
                status: status
            })
        });
        var data = await response.json();
        if (data.success) {
            showCalibrationButtons(data.calibration);
            // Update the submission in the local array so cards reflect new status
            var arr = currentDeanPreviewType === 'faculty' ? allFacultySubmissions : allCalibrationSubmissions;
            for (var i = 0; i < arr.length; i++) {
                if (arr[i].id === currentDeanPreviewSubmissionId) {
                    arr[i].calibration_status = data.calibration.status;
                    arr[i].calibration_score = data.calibration.overall_score;
                    break;
                }
            }
            // Refresh expanded view if visible
            if (currentExpandedType && !document.getElementById('expandedView').classList.contains('hidden')) {
                expandSection(currentExpandedType);
                filterExpanded(currentExpandedFilter);
            }
            // Show success toast
            var toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-[2000] bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm font-semibold flex items-center gap-2 animate-pulse';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + (status === 'calibrated' ? 'Calibration saved successfully!' : 'Draft saved successfully!');
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 3000);
        } else {
            alert(data.message || 'Failed to save calibration.');
        }
    } catch (error) {
        console.error('Error saving calibration:', error);
        alert('Failed to save calibration. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// Label QETA inputs and compute A = (Q + E + T) / 3 for each data row
function labelQetaInputsDean(tableBody) {
    if (!tableBody) return;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        if (row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]')) {
            return;
        }
        var cells = row.querySelectorAll('td');
        // Data rows: MFO | SI | Accomplishments | Q | E | T | A | Remarks
        if (cells.length >= 7) {
            var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
            var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
            var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            if (qInput && eInput && tInput && aInput) {
                var q = parseFloat(qInput.value);
                var e = parseFloat(eInput.value);
                var t = parseFloat(tInput.value);
                if (!isNaN(q) && !isNaN(e) && !isNaN(t)) {
                    aInput.value = ((q + e + t) / 3).toFixed(2);
                }
                aInput.readOnly = true;
                aInput.style.backgroundColor = '#f3f4f6';
            }
        }
    });
}

// Compute and display overall average of all A values
function computeOverallAverage(tableBody) {
    var allA = [];
    tableBody.querySelectorAll('tr').forEach(function(row) {
        if (row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]')) {
            return;
        }
        var cells = row.querySelectorAll('td');
        if (cells.length >= 7) {
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            if (aInput && aInput.value) {
                var val = parseFloat(aInput.value);
                if (!isNaN(val)) allA.push(val);
            }
        }
    });
    var container = document.getElementById('deanPreviewOverallAvg');
    var valueEl = document.getElementById('deanPreviewAvgValue');
    if (allA.length > 0) {
        var avg = allA.reduce(function(s, v) { return s + v; }, 0) / allA.length;
        valueEl.textContent = avg.toFixed(2);
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Attach click handlers to SO rows (bg-blue-100) to view supporting documents
function attachSoDocClickHandlers(tableBody, ownerId, docId, docType) {
    var rows = tableBody.querySelectorAll('tr.bg-blue-100');
    rows.forEach(function(row) {
        var soSpan = row.querySelector('span.font-semibold.text-gray-800');
        var soInput = row.querySelector('input[type="text"]');
        var soLabel = '';
        var soDescription = '';
        if (soSpan) soLabel = soSpan.textContent.trim().replace(/:$/, '');
        if (soInput) soDescription = soInput.value || soInput.getAttribute('value') || '';
        if (!soLabel) return;

        // Re-enable pointer events on the row for clicking
        row.style.cursor = 'pointer';
        row.title = 'Click to view supporting documents';
        row.style.pointerEvents = 'auto';
        row.addEventListener('click', function(ev) {
            ev.stopPropagation();
            openDeanSoDocsModal(soLabel, soDescription, ownerId, docId, docType);
        });

        // Add/refresh doc count badge
        var badge = row.querySelector('.so-doc-badge');
        if (!badge) {
            var td = row.querySelector('td');
            if (td) {
                badge = document.createElement('span');
                badge.className = 'so-doc-badge ml-2 inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full';
                badge.innerHTML = '<i class="fas fa-paperclip text-[10px]"></i> <span class="so-doc-count">...</span>';
                badge.style.fontSize = '11px';
                var innerDiv = td.querySelector('div.flex') || td;
                innerDiv.appendChild(badge);
            }
        }
        if (badge) {
            var countEl = badge.querySelector('.so-doc-count');
            if (countEl) fetchDeanSoDocCount(soLabel, ownerId, docId, docType, countEl);
        }
    });
}

function fetchDeanSoDocCount(soLabel, ownerId, docId, docType, countElement) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var url = '/faculty/supporting-documents?documentable_type=' + encodeURIComponent(docType) +
        '&documentable_id=' + docId +
        '&so_label=' + encodeURIComponent(soLabel) +
        '&owner_id=' + ownerId;
    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && countElement) countElement.textContent = data.documents.length;
        })
        .catch(function() { if (countElement) countElement.textContent = '0'; });
}

function openDeanSoDocsModal(soLabel, soDescription, ownerId, docId, docType) {
    document.getElementById('deanSoDocsTitle').textContent = soLabel;
    document.getElementById('deanSoDocsDesc').textContent = soDescription || '';
    document.getElementById('deanSoDocsModal').classList.remove('hidden');

    var container = document.getElementById('deanSoDocsList');
    container.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-5 w-5 text-gray-300 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm text-gray-400">Loading documents...</span></div>';

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var url = '/faculty/supporting-documents?documentable_type=' + encodeURIComponent(docType) +
        '&documentable_id=' + docId +
        '&so_label=' + encodeURIComponent(soLabel) +
        '&owner_id=' + ownerId;
    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                renderDeanSoDocs(data.documents);
            } else {
                container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Failed to load documents</p>';
            }
        })
        .catch(function() {
            container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading documents</p>';
        });
}

function renderDeanSoDocs(documents) {
    var container = document.getElementById('deanSoDocsList');
    if (!documents || documents.length === 0) {
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-folder-open text-gray-200 text-3xl mb-3"></i><p class="text-sm text-gray-400">No supporting documents</p></div>';
        return;
    }
    container.innerHTML = documents.map(function(doc) {
        var isImage = (doc.mime_type || '').match(/jpg|jpeg|png|gif|webp|image/i);
        var isPdf = (doc.mime_type || '').match(/pdf/i) || (doc.original_name || '').endsWith('.pdf');
        var icon = 'fas fa-file text-gray-400';
        if (isImage) icon = 'fas fa-image text-green-500';
        else if (isPdf) icon = 'fas fa-file-pdf text-red-500';
        else if ((doc.original_name || '').match(/\.(doc|docx)$/i)) icon = 'fas fa-file-word text-blue-500';
        else if ((doc.original_name || '').match(/\.(xls|xlsx)$/i)) icon = 'fas fa-file-excel text-green-600';
        else if ((doc.original_name || '').match(/\.(ppt|pptx)$/i)) icon = 'fas fa-file-powerpoint text-orange-500';

        var nameDisplay = doc.original_name.length > 35 ? doc.original_name.substring(0, 32) + '...' : doc.original_name;
        var previewHtml = isImage
            ? '<div class="w-10 h-10 flex-shrink-0 rounded overflow-hidden bg-gray-200"><img src="' + doc.path + '" alt="" class="w-full h-full object-cover" /></div>'
            : '<i class="' + icon + ' text-lg flex-shrink-0"></i>';

        return '<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-2 hover:bg-gray-100 transition">' +
            previewHtml +
            '<div class="flex-1 min-w-0">' +
                '<p class="text-sm font-medium text-gray-800 truncate" title="' + doc.original_name + '">' + nameDisplay + '</p>' +
                '<p class="text-xs text-gray-400">' + (doc.file_size_human || '') + ' &bull; ' + (doc.created_at || '') + '</p>' +
            '</div>' +
            '<a href="/faculty/supporting-documents/' + doc.id + '/download" class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition" title="Download"><i class="fas fa-download"></i></a>' +
        '</div>';
    }).join('');
}

window.closeDeanSoDocsModal = function() {
    document.getElementById('deanSoDocsModal').classList.add('hidden');
};
</script>
@endif

@if(isset($returnedCalibration) && $returnedCalibration)
<script>
window.openReturnedCalibrationModal = function() {
    var modal = document.getElementById('returnedCalibrationModal');
    if (!modal) return;
    modal.classList.remove('hidden');

    var tableBody = document.getElementById('returnedCalibrationTableBody');
    if (!tableBody) return;

    var calData = @json($returnedCalibration->calibration_data ?? []);
    var tableHtml = @json($activeSubmission->table_body_html ?? '');

    // Load original table HTML
    tableBody.innerHTML = tableHtml;

    // Apply calibration values and make everything read-only
    var dataRowIndex = 0;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');

        var cells = row.querySelectorAll('td');

        // Make all cells read-only
        cells.forEach(function(cell) {
            cell.setAttribute('contenteditable', 'false');
            cell.style.userSelect = 'none';
            cell.querySelectorAll('input, textarea').forEach(function(el) {
                el.setAttribute('readonly', 'true');
                el.setAttribute('disabled', 'true');
                el.style.pointerEvents = 'none';
            });
        });

        // For data rows, overlay calibration values
        if (!isHeaderRow && cells.length >= 8) {
            var rowData = calData[dataRowIndex];
            if (rowData) {
                var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
                var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
                var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
                var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;

                if (qInput && rowData.q !== undefined && rowData.q !== null) {
                    qInput.value = rowData.q;
                    qInput.style.backgroundColor = '#ecfdf5';
                }
                if (eInput && rowData.e !== undefined && rowData.e !== null) {
                    eInput.value = rowData.e;
                    eInput.style.backgroundColor = '#ecfdf5';
                }
                if (tInput && rowData.t !== undefined && rowData.t !== null) {
                    tInput.value = rowData.t;
                    tInput.style.backgroundColor = '#ecfdf5';
                }
                if (aInput && rowData.a !== undefined && rowData.a !== null) {
                    aInput.value = rowData.a;
                    aInput.style.backgroundColor = '#ecfdf5';
                    aInput.style.fontWeight = 'bold';
                } else if (aInput && qInput && eInput && tInput) {
                    var q = parseFloat(qInput.value) || 0;
                    var e = parseFloat(eInput.value) || 0;
                    var t = parseFloat(tInput.value) || 0;
                    aInput.value = ((q + e + t) / 3).toFixed(2);
                    aInput.style.backgroundColor = '#ecfdf5';
                    aInput.style.fontWeight = 'bold';
                }
                if (rowData.remarks !== undefined && rowData.remarks !== null && rowData.remarks !== '') {
                    var remarksInput = cells[7] ? cells[7].querySelector('input, textarea') : null;
                    if (remarksInput) {
                        remarksInput.value = rowData.remarks;
                        remarksInput.style.backgroundColor = '#ecfdf5';
                    } else if (cells[7]) {
                        cells[7].textContent = rowData.remarks;
                        cells[7].style.backgroundColor = '#ecfdf5';
                    }
                }
            }
            dataRowIndex++;
        }
    });
};

window.closeReturnedCalibrationModal = function() {
    document.getElementById('returnedCalibrationModal').classList.add('hidden');
};
</script>
@endif

<script>document.body.style.visibility = 'visible';</script>
</body>
</html>