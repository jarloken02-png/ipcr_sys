<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My IPCRs - IPCR Dashboard</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/dashboard_faculty_my-ipcrs.css', 'resources/js/dashboard_faculty_my-ipcrs.js'])
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
                    <a href="{{ route('faculty.dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('faculty.my-ipcrs') }}" class="text-blue-600 font-semibold hover:text-blue-700">My IPCRs</a>
                    @if(auth()->user()->hasRole('hr'))
                        <a href="{{ route('faculty.summary-reports') }}" class="text-gray-600 hover:text-gray-900">Summary Reports</a>
                    @endif
                    <div class="relative">
                        <button onclick="toggleNotificationPopup()" class="text-gray-600 hover:text-gray-900 relative flex items-center gap-1">
                            Notifications
                            @if(($unreadCount ?? 0) > 0)
                                <span class="notification-badge" id="notifBadge" style="position: static; margin-left: 4px;">{{ $unreadCount }}</span>
                            @else
                                <span class="notification-badge hidden" id="notifBadge" style="position: static; margin-left: 4px;">0</span>
                            @endif
                        </button>
                        
                        <!-- Notification Popup -->
                        <div id="notificationPopup" class="notification-popup">
                            <div class="p-3 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Notifications</h3>
                                <div class="flex items-center gap-2">
                                    <button onclick="markAllNotificationsRead()" class="text-[10px] font-semibold text-blue-600 hover:text-blue-800 transition-colors" title="Mark all as read">
                                        Mark all as read
                                    </button>
                                    <button onclick="toggleCompactMode()" class="compact-toggle-btn text-[10px] font-semibold px-2 py-0.5 rounded-full border transition-colors" title="Toggle compact view">
                                        <span class="compact-label">Compact</span>
                                    </button>
                                </div>
                            </div>
                            <div class="max-h-72 overflow-y-auto">
                                <div class="p-2.5 notif-list">
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
                                <span class="notification-badge" id="notifBadgeMobile">{{ $unreadCount }}</span>
                            @else
                                <span class="notification-badge hidden" id="notifBadgeMobile">0</span>
                            @endif
                        </button>
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
                <a href="{{ route('faculty.dashboard') }}" class="block text-gray-600 hover:text-gray-900 py-2">Dashboard</a>
                <a href="{{ route('faculty.my-ipcrs') }}" class="block text-blue-600 font-semibold hover:text-blue-700 py-2">My IPCRs</a>
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

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Left Main Content (2/3 width) -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                <!-- Header Section -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 md:p-8">
                    <h1 id="pageHeaderTitle" class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 mb-4 sm:mb-6 md:mb-8"><span id="performanceType">Individual</span> Performance Commitment and Review for {{ auth()->user()->designation->title ?? 'Faculty' }}</h1>
                    
                    <!-- User Information Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Full Name -->
                        <div>
                            <label class="text-xs sm:text-sm text-gray-500 block mb-1">Full Name</label>
                            <p class="text-sm sm:text-base font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        </div>
                        
                        <!-- Employee ID -->
                        <div>
                            <label class="text-xs sm:text-sm text-gray-500 block mb-1">Employee ID</label>
                            <p class="text-sm sm:text-base font-semibold text-gray-900">{{ auth()->user()->employee_id ?? 'N/A' }}</p>
                        </div>
                        
                        <!-- Designation -->
                        <div>
                            <label class="text-xs sm:text-sm text-gray-500 block mb-1">Designation</label>
                            <p class="text-sm sm:text-base font-semibold text-gray-900">{{ auth()->user()->designation->title ?? 'N/A' }}</p>
                        </div>
                        
                        <!-- Department -->
                        <div>
                            <label class="text-xs sm:text-sm text-gray-500 block mb-1">Department</label>
                            <p class="text-sm sm:text-base font-semibold text-gray-900">{{ auth()->user()->department->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Create IPCR Section -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 md:p-8">
                    <!-- Tab Header & Quick Actions -->
                    <div class="border-b border-gray-200 mb-4 sm:mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- Tabs -->
                            <div class="flex space-x-4 sm:space-x-8 overflow-x-auto">
                                <button id="ipcrTab" class="pb-3 sm:pb-4 px-1 border-b-2 border-blue-600 font-semibold text-blue-600 text-sm sm:text-base whitespace-nowrap" onclick="switchTab('ipcr')">
                                    IPCR Drafts
                                </button>
                                @if(auth()->user()->hasRole('dean'))
                                <button id="opcrTab" class="pb-3 sm:pb-4 px-1 border-b-2 border-transparent font-semibold text-gray-500 text-sm sm:text-base whitespace-nowrap hover:text-gray-700" onclick="switchTab('opcr')">
                                    OPCR Drafts
                                </button>
                                @endif
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 pb-3 sm:pb-4 mt-2 sm:mt-0">
                                <button id="headerCreateDraftBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-sm" onclick="openCreateIpcrModal()">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span id="headerCreateDraftBtnLabel">Create IPCR</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- IPCR Content Area -->
                    <div id="createIpcrButtonArea">
                        <!-- IPCR Saved Copies (rendered via Blade) -->
                        <div id="ipcrSavedCopiesSection" class="@if($savedIpcrs->isEmpty()) hidden @endif">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Saved Copies (Drafts)</h3>
                            <div id="savedCopiesList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($savedIpcrs as $savedIpcr)
                                    <div class="group relative bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                                        <div class="absolute top-4 right-4 transition-opacity">
                                            <button onclick="deleteSavedCopy({{ $savedIpcr->id }})" 
                                                    class="text-gray-400 hover:text-red-500 p-1.5 rounded-full hover:bg-red-50 transition-colors"
                                                    title="Delete Draft">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mb-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600 uppercase tracking-wider border border-blue-100">IPCR</span>
                                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $savedIpcr->saved_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 leading-tight mb-1">{{ $savedIpcr->title }}</h4>
                                            <p class="text-sm font-medium text-gray-500">
                                                {{ $savedIpcr->school_year }} &bull; <span class="text-gray-600">{{ ucfirst($savedIpcr->semester) }}</span>
                                            </p>
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                            <button onclick="editSavedCopy({{ $savedIpcr->id }})"
                                               class="w-full text-center px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors cursor-pointer">
                                                Continue Editing
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if($savedIpcrs->isEmpty())
                            <p id="savedCopiesEmpty" class="text-sm text-gray-500 text-center py-4">No saved drafts yet.</p>
                        @endif
                    </div>

                    @if(auth()->user()->hasRole('dean'))
                    <!-- OPCR Content Area -->
                    <div id="createOpcrButtonArea" class="hidden">
                        <!-- OPCR Saved Copies (rendered via Blade) -->
                        <div id="opcrSavedCopiesSection" class="@if($savedOpcrs->isEmpty()) hidden @endif">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Saved Copies (Drafts)</h3>
                            <div id="opcrSavedCopiesList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($savedOpcrs as $savedOpcr)
                                    <div class="group relative bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                                        <div class="absolute top-4 right-4 transition-opacity">
                                            <button onclick="deleteOpcrSavedCopy({{ $savedOpcr->id }})" 
                                                    class="text-gray-400 hover:text-red-500 p-1.5 rounded-full hover:bg-red-50 transition-colors"
                                                    title="Delete Draft">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mb-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-600 uppercase tracking-wider border border-orange-100">OPCR</span>
                                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $savedOpcr->saved_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 leading-tight mb-1">{{ $savedOpcr->title }}</h4>
                                            <p class="text-sm font-medium text-gray-500">
                                                {{ $savedOpcr->school_year }} &bull; <span class="text-gray-600">{{ ucfirst($savedOpcr->semester) }}</span>
                                            </p>
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                            <button onclick="editOpcrSavedCopy({{ $savedOpcr->id }})"
                                               class="w-full text-center px-4 py-2 text-sm font-semibold text-orange-600 bg-orange-50 rounded-lg hover:bg-orange-100 hover:text-orange-700 transition-colors cursor-pointer">
                                                Continue Editing
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if($savedOpcrs->isEmpty())
                            <p id="opcrSavedCopiesEmpty" class="text-sm text-gray-500 text-center py-4 hidden">No saved OPCR drafts yet.</p>
                        @endif
                    </div>
                    @endif

                    <!-- Create IPCR Modal -->
                    <div id="createIpcrModal" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black/50" onclick="closeCreateIpcrModal()"></div>
                        <div class="relative mx-auto mt-24 w-full max-w-lg bg-white rounded-xl shadow-lg">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                <h2 id="modalHeaderTitle" class="text-lg sm:text-xl font-bold text-gray-900"><span id="modalPerformanceType">Individual</span> Performance Commitment and Review for {{ auth()->user()->designation->title ?? 'Faculty' }}</h2>
                                <button type="button" onclick="closeCreateIpcrModal()" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-6 py-5 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Year</label>
                                    <select id="ipcrSchoolYear" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @php
                                            $currentYear = now()->year;
                                            $startYear = $currentYear - 5;
                                        @endphp
                                        @for ($year = $currentYear; $year >= $startYear; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Period</label>
                                    <select id="ipcrSemester" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="jan-jun">January - June</option>
                                        <option value="jul-dec">July - December</option>
                                    </select>
                                </div>
                                @php
                                    $deptId = auth()->user()->department_id;
                                    $deanUser = $deptId
                                        ? \App\Models\User::where('department_id', $deptId)
                                            ->whereHas('userRoles', function ($query) {
                                                $query->where('role', 'dean');
                                            })
                                            ->first()
                                        : null;
                                    
                                    $directorUser = \App\Models\User::whereHas('userRoles', function ($query) {
                                        $query->where('role', 'director');
                                    })->first();
                                @endphp
                                <div class="pt-2 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500">Name of the Ratee:</label>
                                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Approved By:</label>
                                            <input type="text" id="ipcrCreateApprovedBy" class="w-full text-sm font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $directorUser ? $directorUser->name : '' }}" placeholder="Enter name" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Noted By:</label>
                                            <input type="text" id="ipcrCreateNotedBy" class="w-full text-sm font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $deanUser ? $deanUser->name : '' }}" placeholder="Enter name" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200">
                                <div class="mb-3">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Import from Excel file (optional)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="file" id="ipcrImportFile" accept=".xlsx,.xls" class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Upload an .xlsx file with the IPCR layout to auto-fill the document.</p>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="closeCreateIpcrModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200">Cancel</button>
                                    <button type="button" onclick="proceedCreateIpcr()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">Proceed</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('dean'))
                    <!-- Create OPCR Modal -->
                    <div id="createOpcrModal" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black/50" onclick="closeCreateOpcrModal()"></div>
                        <div class="relative mx-auto mt-24 w-full max-w-lg bg-white rounded-xl shadow-lg">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Office Performance Commitment and Review for {{ auth()->user()->designation->title ?? 'Faculty' }}</h2>
                                <button type="button" onclick="closeCreateOpcrModal()" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-6 py-5 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Year</label>
                                    <select id="opcrSchoolYear" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @php
                                            $currentYear = now()->year;
                                            $startYear = $currentYear - 5;
                                        @endphp
                                        @for ($year = $currentYear; $year >= $startYear; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Period</label>
                                    <select id="opcrSemester" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="jan-jun">January - June</option>
                                        <option value="jul-dec">July - December</option>
                                    </select>
                                </div>
                                @php
                                    $deptId = auth()->user()->department_id;
                                    $deanUser = $deptId
                                        ? \App\Models\User::where('department_id', $deptId)
                                            ->whereHas('userRoles', function ($query) {
                                                $query->where('role', 'dean');
                                            })
                                            ->first()
                                        : null;
                                    
                                    $directorUser = \App\Models\User::whereHas('userRoles', function ($query) {
                                        $query->where('role', 'director');
                                    })->first();
                                @endphp
                                <div class="pt-2 border-t border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500">Name of the Ratee:</label>
                                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Approved By:</label>
                                            <input type="text" id="opcrCreateApprovedBy" class="w-full text-sm font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $directorUser ? $directorUser->name : '' }}" placeholder="Enter name" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Noted By:</label>
                                            <input type="text" id="opcrCreateNotedBy" class="w-full text-sm font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $deanUser ? $deanUser->name : '' }}" placeholder="Enter name" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200">
                                <div class="mb-3">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Import from Excel file (optional)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="file" id="opcrImportFile" accept=".xlsx,.xls" class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Upload an .xlsx file with the OPCR layout to auto-fill the document.</p>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="closeCreateOpcrModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200">Cancel</button>
                                    <button type="button" onclick="proceedCreateOpcr()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">Proceed</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->hasRole('dean'))
                    <!-- OPCR Document Modal -->
                    <div id="opcrDocumentContainer" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                        <div class="relative mx-auto mt-2 sm:mt-8 mb-2 sm:mb-8 w-full max-w-6xl bg-white rounded-2xl shadow-lg max-h-[98vh] sm:max-h-[90vh] overflow-y-auto px-2 sm:px-0">
                            <!-- Document Header -->
                            <div class="bg-gray-50 px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-300 sticky top-0 bg-white z-10">
                                <div class="flex justify-between items-start mb-3 sm:mb-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 sm:gap-2 mb-2">
                                            <input type="text" id="opcrDocumentTitle" class="text-sm sm:text-lg font-bold text-gray-900 border-0 border-b-2 border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 sm:px-2 py-1 -ml-1 sm:-ml-2 w-full" value="OPCR for {{ auth()->user()->designation->title ?? 'Faculty' }}" />
                                            <button onclick="saveOpcrDocumentTitle()" class="text-blue-600 hover:text-blue-700 flex-shrink-0" title="Save title">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-600">Year: <span id="opcrDisplaySchoolYear" class="font-semibold"></span></p>
                                        <p class="text-xs sm:text-sm text-gray-600">Period: <span id="opcrDisplaySemester" class="font-semibold"></span></p>
                                    </div>
                                    <button onclick="closeOpcrDocument()" class="text-gray-500 hover:text-gray-700 ml-2 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Ratee:</span>
                                        <span class="font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</span>
                                    </div>
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Approved By:</span>
                                        <input type="text" id="opcrDocApprovedBy" class="text-sm font-semibold text-gray-900 border-0 border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 py-0 w-full" value="{{ $directorUser ? $directorUser->name : '' }}" placeholder="Enter name" />
                                    </div>
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Noted By:</span>
                                        <input type="text" id="opcrDocNotedBy" class="text-sm font-semibold text-gray-900 border-0 border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 py-0 w-full" value="{{ $deanUser ? $deanUser->name : '' }}" placeholder="Enter name" />
                                    </div>
                                </div>
                            </div>

                            <!-- Excel-like Table -->
                            <div class="overflow-x-auto px-2 sm:px-6 py-3 sm:py-4">
                                <table class="w-full border-collapse min-w-[800px]">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">MFO</th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 25%;">Success Indicators<br><span class="font-semibold text-gray-500">(Target + Measures)</span></th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hidden" rowspan="2" style="width: 20%;">Actual Accomplishments</th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hidden" colspan="4">Rating</th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hidden" rowspan="2" style="width: 15%;">Remarks</th>
                                        </tr>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">Q</th>
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">E</th>
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">T</th>
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">A</th>
                                        </tr>
                                    </thead>
                                    <tbody id="opcrTableBody">
                                        <!-- Table rows will be added dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="px-2 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-300 sticky bottom-0 z-10">
                                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-3">
                                    <div class="flex flex-wrap gap-2">
                                        <div class="relative" id="opcrSectionHeaderDropdown">
                                            <button type="button" onclick="toggleOpcrSectionHeaderDropdown()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-100 flex items-center gap-1 sm:gap-2 whitespace-nowrap">
                                                <span class="hidden sm:inline">+</span> Add Section
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div id="opcrSectionHeaderDropdownMenu" class="hidden absolute left-0 bottom-full mb-2 w-48 sm:w-56 rounded-lg shadow-xl bg-white border border-gray-200 z-[9999]">
                                                <div class="py-1">
                                                    <button type="button" onclick="addOpcrSectionHeader('Strategic Objectives', false)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                        Strategic Objectives
                                                    </button>
                                                    <button type="button" onclick="addOpcrSectionHeader('Core Functions', false)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                        Core Functions
                                                    </button>
                                                    <button type="button" onclick="addOpcrSectionHeader('Support Function', false)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                        Support Function
                                                    </button>
                                                    <button type="button" onclick="addOpcrSectionHeader('', true)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-t border-gray-200">
                                                        Others (Custom)
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="addOpcrSOHeader()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-purple-600 bg-purple-50 border border-purple-200 hover:bg-purple-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">+</span> Add SO
                                        </button>
                                        <button type="button" onclick="addOpcrDataRow()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-green-600 bg-green-50 border border-green-200 hover:bg-green-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">+</span> Add Row
                                        </button>
                                        <button type="button" onclick="removeOpcrLastRow()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">-</span> Remove
                                        </button>
                                    </div>
                                    <div class="flex gap-2 sm:gap-3">
                                        <button id="opcrExportBtn" type="button" onclick="exportOpcrDocument()" class="hidden flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-300 hover:bg-blue-100 whitespace-nowrap flex items-center justify-center gap-1">
                                            <i class="fas fa-file-excel"></i> Export
                                        </button>
                                        <button id="opcrSaveAsTemplateBtn" type="button" onclick="saveOpcrAsTemplate()" class="hidden flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-orange-700 bg-orange-50 border border-orange-300 hover:bg-orange-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">&#128203;</span> Save as Template
                                        </button>
                                        <button type="button" onclick="closeOpcrDocument()" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                                        <button type="button" onclick="saveOpcrDocument()" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-lg text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- IPCR Document Modal -->
                    <div id="ipcrDocumentContainer" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                        <div class="relative mx-auto mt-2 sm:mt-8 mb-2 sm:mb-8 w-full max-w-6xl bg-white rounded-2xl shadow-lg max-h-[98vh] sm:max-h-[90vh] overflow-y-auto px-2 sm:px-0">
                            <!-- Document Header -->
                            <div class="bg-gray-50 px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-300 sticky top-0 bg-white z-10">
                                <div class="flex justify-between items-start mb-3 sm:mb-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 sm:gap-2 mb-2">
                                            <input type="text" id="ipcrDocumentTitle" class="text-sm sm:text-lg font-bold text-gray-900 border-0 border-b-2 border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 sm:px-2 py-1 -ml-1 sm:-ml-2 w-full" value="IPCR for {{ auth()->user()->designation->title ?? 'Faculty' }}" />
                                            <button onclick="saveDocumentTitle()" class="text-blue-600 hover:text-blue-700 flex-shrink-0" title="Save title">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-600">Year: <span id="displaySchoolYear" class="font-semibold"></span></p>
                                        <p class="text-xs sm:text-sm text-gray-600">Period: <span id="displaySemester" class="font-semibold"></span></p>
                                    </div>
                                    <button onclick="closeIpcrDocument()" class="text-gray-500 hover:text-gray-700 ml-2 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Ratee:</span>
                                        <span class="font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</span>
                                    </div>
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Approved By:</span>
                                        <input type="text" id="ipcrDocApprovedBy" class="text-sm font-semibold text-gray-900 border-0 border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 py-0 w-full" value="{{ $directorUser ? $directorUser->name : '' }}" placeholder="Enter name" />
                                    </div>
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Noted By:</span>
                                        <input type="text" id="ipcrDocNotedBy" class="text-sm font-semibold text-gray-900 border-0 border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 py-0 w-full" value="{{ $deanUser ? $deanUser->name : '' }}" placeholder="Enter name" />
                                    </div>
                                </div>
                            </div>

                            <!-- Excel-like Table -->
                            <div class="overflow-x-auto px-2 sm:px-6 py-3 sm:py-4">
                                <table class="w-full border-collapse min-w-[800px]">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">MFO</th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 25%;">Success Indicators<br><span class="font-semibold text-gray-500">(Target + Measures)</span></th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hidden" rowspan="2" style="width: 20%;">Actual Accomplishments</th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hidden" colspan="4">Rating</th>
                                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hidden" rowspan="2" style="width: 15%;">Remarks</th>
                                        </tr>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">Q</th>
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">E</th>
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">T</th>
                                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 hidden" style="width: 8%;">A</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ipcrTableBody">
                                        <!-- Table rows will be added dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="px-2 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-300 sticky bottom-0 z-10">
                                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-3">
                                    <div class="flex flex-wrap gap-2">
                                        <!-- Dropdown for Add Section Header -->
                                        <div class="relative" id="sectionHeaderDropdown">
                                            <button type="button" onclick="toggleSectionHeaderDropdown()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-100 flex items-center gap-1 sm:gap-2 whitespace-nowrap">
                                                <span class="hidden sm:inline">+</span> Add Section
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div id="sectionHeaderDropdownMenu" class="hidden absolute left-0 bottom-full mb-2 w-48 sm:w-56 rounded-lg shadow-xl bg-white border border-gray-200 z-[9999]">
                                                <div class="py-1">
                                                    <button type="button" onclick="addSectionHeader('Strategic Objectives', false)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                        Strategic Objectives
                                                    </button>
                                                    <button type="button" onclick="addSectionHeader('Core Functions', false)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                        Core Functions
                                                    </button>
                                                    <button type="button" onclick="addSectionHeader('Support Function', false)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                        Support Function
                                                    </button>
                                                    <button type="button" onclick="addSectionHeader('', true)" class="w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-t border-gray-200">
                                                        Others (Custom)
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="addSOHeader()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-purple-600 bg-purple-50 border border-purple-200 hover:bg-purple-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">+</span> Add SO
                                        </button>
                                        <button type="button" onclick="addDataRow()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-green-600 bg-green-50 border border-green-200 hover:bg-green-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">+</span> Add Row
                                        </button>
                                        <button type="button" onclick="removeLastRow()" class="px-2 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">-</span> Remove
                                        </button>
                                    </div>
                                    <div class="flex gap-2 sm:gap-3">
                                        <button id="ipcrExportBtn" type="button" onclick="exportIpcrDocument()" class="hidden flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-300 hover:bg-blue-100 whitespace-nowrap flex items-center justify-center gap-1">
                                            <i class="fas fa-file-excel"></i> Export
                                        </button>
                                        <button id="ipcrSaveAsTemplateBtn" type="button" onclick="saveAsTemplate()" class="hidden flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-orange-700 bg-orange-50 border border-orange-300 hover:bg-orange-100 whitespace-nowrap">
                                            <span class="hidden sm:inline">&#128203;</span> Save as Template
                                        </button>
                                        <button type="button" onclick="closeIpcrDocument()" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                                        <button type="button" onclick="saveIpcrDocument()" class="flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-lg text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Template Preview Modal -->
                    <div id="templatePreviewModal" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                        <div class="relative mx-auto mt-2 sm:mt-8 mb-2 sm:mb-8 w-full max-w-6xl bg-white rounded-2xl shadow-lg max-h-[98vh] sm:max-h-[90vh] overflow-y-auto px-2 sm:px-0">
                            <!-- Document Header -->
                            <div class="bg-gray-50 px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-300 sticky top-0 bg-white z-10">
                                <div class="flex justify-between items-start mb-3 sm:mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h2 id="templatePreviewTitle" class="text-sm sm:text-lg font-bold text-gray-900 mb-2"></h2>
                                        <p class="text-xs sm:text-sm text-gray-600">Year: <span id="templatePreviewSchoolYear" class="font-semibold"></span></p>
                                        <p class="text-xs sm:text-sm text-gray-600">Period: <span id="templatePreviewSemester" class="font-semibold"></span></p>
                                    </div>
                                    <button onclick="closeTemplatePreview()" class="text-gray-500 hover:text-gray-700 ml-2 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Ratee:</span>
                                        <span class="font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</span>
                                    </div>
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Approved By:</span>
                                        <input type="text" id="templatePreviewApprovedBy" class="text-sm font-semibold text-gray-900 border-0 border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 py-0 w-full" value="{{ $directorUser ? $directorUser->name : '' }}" placeholder="Enter name" />
                                    </div>
                                    <div class="flex flex-col sm:block">
                                        <span class="text-gray-600">Noted By:</span>
                                        <input type="text" id="templatePreviewNotedBy" class="text-sm font-semibold text-gray-900 border-0 border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 bg-transparent px-1 py-0 w-full" value="{{ $deanUser ? $deanUser->name : '' }}" placeholder="Enter name" />
                                    </div>
                                </div>
                            </div>

                            <!-- Excel-like Table -->
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
                                    <tbody id="templatePreviewTableBody">
                                        <!-- Table rows will be added dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="px-2 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-300 sticky bottom-0 z-10">
                                <div class="flex flex-col sm:flex-row justify-end items-stretch sm:items-center gap-2 sm:gap-3">
                                    <button type="button" onclick="exportFromPreview()" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-300 hover:bg-blue-100 flex items-center justify-center gap-1">
                                        <i class="fas fa-file-excel"></i> Export
                                    </button>
                                    <button type="button" onclick="useTemplateAsDraft()" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-green-700 bg-green-50 border border-green-300 hover:bg-green-100 flex items-center justify-center gap-1">
                                        <i class="fas fa-copy"></i> Use Template
                                    </button>
                                    <button type="button" id="updateSubmissionBtn" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-white bg-green-600 hover:bg-green-700 hidden">Update Submission</button>
                                    <button type="button" id="saveCopyBtn" onclick="saveCopyFromPreview()" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700">Save</button>
                                    <button type="button" onclick="closeTemplatePreview()" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                                </div>
                                <input type="hidden" id="currentPreviewTemplateId" value="">
                                <input type="hidden" id="currentSubmissionIdToUpdate" value="">
                                <input type="hidden" id="currentSubmissionType" value="ipcr">
                                <input type="hidden" id="currentDocumentOwnerId" value="">
                            </div>
                        </div>
                    </div>

                    <!-- SO Supporting Documents Modal -->
                    <div id="soDocumentsModal" class="fixed inset-0 z-[60] hidden">
                        <div class="absolute inset-0 bg-black/60" onclick="closeSoDocumentsModal()"></div>
                        <div class="relative mx-auto mt-8 sm:mt-16 w-full max-w-xl bg-white rounded-xl shadow-2xl max-h-[85vh] overflow-hidden flex flex-col">
                            <!-- Header -->
                            <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex-shrink-0">
                                <div class="flex justify-between items-center">
                                    <div class="min-w-0 flex-1">
                                        <h3 id="soDocModalTitle" class="text-base sm:text-lg font-bold text-gray-900 truncate">SO Details</h3>
                                        <p id="soDocModalDescription" class="text-xs sm:text-sm text-gray-500 mt-0.5 truncate"></p>
                                    </div>
                                    <button onclick="closeSoDocumentsModal()" class="text-gray-400 hover:text-gray-600 ml-3 flex-shrink-0 p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Upload Area -->
                            <div id="soDocUploadSection" class="px-5 py-4 border-b border-gray-100 flex-shrink-0">
                                <form id="soDocUploadForm" enctype="multipart/form-data" class="flex items-center gap-3">
                                    @csrf
                                    <input type="file" id="soDocFileInput" name="file" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                    <input type="hidden" id="soDocType" name="documentable_type" value="">
                                    <input type="hidden" id="soDocId" name="documentable_id" value="">
                                    <input type="hidden" id="soDocLabel" name="so_label" value="">
                                    <button type="button" onclick="document.getElementById('soDocFileInput').click()" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 border-2 border-dashed border-blue-300 rounded-lg text-blue-600 hover:bg-blue-50 transition text-sm font-medium">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span id="soDocUploadText">Choose file to upload</span>
                                    </button>
                                    <button type="button" id="soDocUploadBtn" onclick="uploadSoDocument()" class="hidden px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                                        <i class="fas fa-upload mr-1"></i> Upload
                                    </button>
                                </form>
                                <div id="soDocUploadProgress" class="hidden mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div id="soDocProgressBar" class="bg-blue-600 h-1.5 rounded-full transition-all" style="width: 0%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Uploading...</p>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">Images, PDF, Office docs up to 10MB</p>
                            </div>

                            <!-- Documents List -->
                            <div class="flex-1 overflow-y-auto px-5 py-3" style="max-height: 45vh;">
                                <div id="soDocumentsList">
                                    <div class="flex items-center justify-center py-8">
                                        <i class="fas fa-spinner fa-spin text-gray-300 mr-2"></i>
                                        <span class="text-sm text-gray-400">Loading documents...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 flex-shrink-0">
                                <button type="button" onclick="closeSoDocumentsModal()" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Image Preview Modal (z-index 70) -->
                    <div id="imagePreviewModal" class="fixed inset-0 z-[70] hidden">
                        <div class="absolute inset-0 bg-black/80" onclick="closeImagePreview()"></div>
                        <div class="relative z-10 flex items-center justify-center h-full p-4">
                            <div class="bg-white rounded-lg shadow-2xl" style="width: 920px; max-width: 95vw;">
                                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                                    <h3 id="imagePreviewTitle" class="text-sm font-semibold text-gray-800 flex-1 truncate mr-4">Image Preview</h3>
                                    <div class="flex items-center gap-2">
                                        <button onclick="zoomOut()" class="px-3 py-1.5 rounded text-gray-600 hover:bg-gray-200 transition" title="Zoom Out">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <span id="zoomLevel" class="text-xs text-gray-500 font-medium w-12 text-center">100%</span>
                                        <button onclick="zoomIn()" class="px-3 py-1.5 rounded text-gray-600 hover:bg-gray-200 transition" title="Zoom In">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                        <button onclick="resetZoom()" class="px-3 py-1.5 rounded text-gray-600 hover:bg-gray-200 transition text-xs" title="Reset Zoom">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button onclick="closeImagePreview()" class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="imagePreviewContainer" class="bg-gray-100 overflow-auto" style="height: 700px; max-height: 75vh;">
                                    <div class="flex items-center justify-center min-h-full p-4">
                                        <img id="imagePreviewImg" src="" alt="Image preview" class="rounded shadow-lg transition-transform duration-200" style="cursor: move; max-width: none;" />
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-2 px-4 py-3 bg-gray-50 border-t">
                                    <span class="text-xs text-gray-500"><i class="fas fa-info-circle mr-1"></i>Use zoom controls or scroll to zoom. Drag to pan when zoomed.</span>
                                    <div class="flex gap-2">
                                        <a id="imagePreviewDownload" href="" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </a>
                                        <button onclick="closeImagePreview()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rename Document Modal (z-index 60) -->
                    <div id="renameDocumentModal" class="fixed inset-0 z-[60] hidden">
                        <div class="absolute inset-0 bg-black/60" onclick="closeRenameModal()"></div>
                        <div class="relative z-10 flex items-center justify-center h-full p-4">
                            <div class="bg-white rounded-lg w-full max-w-md shadow-xl">
                                <div class="flex items-center justify-between px-5 py-4 border-b">
                                    <h3 class="text-lg font-bold text-gray-800">Rename Document</h3>
                                    <button onclick="closeRenameModal()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="p-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New filename</label>
                                    <input type="text" id="renameDocumentInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter new filename" />
                                    <p class="text-xs text-gray-500 mt-1">Extension will be preserved automatically</p>
                                </div>
                                <div class="flex items-center justify-end gap-2 px-5 py-4 bg-gray-50 border-t">
                                    <button onclick="closeRenameModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button onclick="submitRename()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">
                                        <i class="fas fa-check mr-1"></i> Rename
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IPCR Form Modal -->
                    <div id="ipcrFormModal" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black/50" onclick="closeIpcrFormModal()"></div>
                        <div class="relative mx-auto mt-12 w-full max-w-4xl bg-white rounded-xl shadow-lg max-h-[85vh] overflow-y-auto">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900">IPCR Template Builder</h2>
                                <button type="button" onclick="closeIpcrFormModal()" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-6 py-5">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <label class="text-sm font-semibold text-gray-700">Template Name:</label>
                                        <input type="text" id="currentTemplateName" placeholder="Enter template name" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Rating Period: January - June 2026</h3>
                                </div>

                                <form id="ipcrForm">
                                    <div class="mb-6">
                                        <!-- Global Formatting Toolbar -->
                                        <div class="format-toolbar mb-4">
                                            <button type="button" class="format-btn" data-command="bold" title="Bold (Ctrl+B)">
                                                <b>B</b>
                                            </button>
                                            <button type="button" class="format-btn" data-command="italic" title="Italic (Ctrl+I)">
                                                <i>I</i>
                                            </button>
                                            <button type="button" class="format-btn" data-command="underline" title="Underline (Ctrl+U)">
                                                <u>U</u>
                                            </button>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div class="text-center font-semibold text-gray-700">MRO</div>
                                            <div class="text-center font-semibold text-gray-700">Success Indicators<br>(Target + Measures)</div>
                                        </div>

                                        <!-- Strategic Objectives Container -->
                                        <div id="strategicObjectivesContainer">
                                            <div class="mb-4">
                                                <div class="editable-field" contenteditable="true" data-placeholder="Strategic objectives..."></div>
                                            </div>
                                        </div>

                                        <!-- Dynamic Headers Container -->
                                        <div id="headersContainer"></div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-wrap gap-3 justify-between items-center border-t border-gray-200 pt-4">
                                        <div class="flex gap-3">
                                            <button type="button" onclick="addHeader()" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 font-semibold text-sm">
                                                + Add Header
                                            </button>
                                            <button type="button" onclick="addRow()" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 font-semibold text-sm">
                                                + Add Row
                                            </button>
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="button" onclick="clearForm()" class="text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-semibold text-sm">
                                                Clear Form
                                            </button>
                                            <button type="button" onclick="closeIpcrFormModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                                            <button type="button" id="saveButton" onclick="generateIPCR()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold text-sm">
                                                Generate IPCR
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <!-- Right Sidebar (1/3 width) -->
            <div id="rightSidebar" class="space-y-4 sm:space-y-6">
                <!-- IPCR Templates -->
                <div id="ipcrTemplatesSection" class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">IPCR Templates</h3>
                    
                    <div id="templatesContainer">
                        @forelse($templates ?? [] as $template)
                            <!-- Template Item -->
                            <div class="template-card mb-3 relative">
                                <button onclick="deleteTemplate({{ $template->id }})" class="absolute top-2 right-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full p-2 transition" title="Delete template">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                                <div class="mb-3 pr-8">
                                    <p class="text-sm sm:text-base font-semibold text-gray-900">{{ $template->title }}</p>
                                    @if($template->school_year && $template->semester)
                                        <p class="text-xs sm:text-sm text-gray-600">{{ $template->school_year }} &bull; {{ $template->semester }}</p>
                                    @else
                                        <p class="text-xs sm:text-sm text-gray-600">{{ $template->period }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">Saved on {{ $template->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="flex gap-2 ml-7">
                                    <button onclick="viewTemplate({{ $template->id }})" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-semibold py-2 px-3 sm:px-4 rounded">
                                        View
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">No saved templates yet</p>
                        @endforelse
                    </div>
                </div>

                @if(auth()->user()->hasRole('dean'))
                <!-- OPCR Templates -->
                <div id="opcrTemplatesSection" class="bg-white rounded-lg shadow-sm p-4 sm:p-6 hidden">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">OPCR Templates</h3>
                    
                    <div id="opcrTemplatesContainer">
                        <p class="text-sm text-gray-500 text-center py-4">No OPCR templates yet</p>
                    </div>
                </div>
                @endif

                <!-- Submit IPCR -->
                <div id="submitIpcrSection" class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Submit IPCR</h3>
                    @php
                        $currentYear = now()->year;
                        $currentSchoolYear = (string)$currentYear;
                        $currentSemester = now()->month <= 6 ? 'January - June' : 'July - December';
                        $hasSubmission = isset($submissions) && count($submissions) > 0;
                    @endphp
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Current Period:</span>
                            <span class="font-semibold text-gray-900">{{ $currentSemester }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Current Year:</span>
                            <span class="font-semibold text-gray-900">{{ $currentSchoolYear }}</span>
                        </div>
                    </div>
                    @if($hasSubmission)
                        <button type="button" disabled class="mt-4 w-full bg-gray-400 text-white text-sm font-semibold py-2 rounded cursor-not-allowed opacity-75">
                            &#10003; Submitted
                        </button>
                    @else
                        <button type="button" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded" onclick="openSubmitIpcrModal()">
                            Submit
                        </button>
                    @endif
                    
                    <!-- Submitted IPCRs List -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Submitted IPCRs</h4>
                        @forelse($submissions ?? [] as $submission)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm font-semibold text-gray-900 mb-1">{{ $submission->title }}</p>
                                <p class="text-xs text-gray-600">{{ $submission->school_year }} &bull; {{ $submission->semester }}</p>
                                <p class="text-xs text-gray-500 mb-2">Submitted: {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y') : 'N/A' }}</p>
                                <div class="flex gap-2">
                                    <button onclick="viewSubmission({{ $submission->id }})" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-1.5 px-3 rounded">
                                        View & Edit
                                    </button>
                                    <button onclick="unsubmitSubmission({{ $submission->id }})" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold py-1.5 px-3 rounded">
                                        Unsubmit
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 text-center py-4">No submissions yet</p>
                        @endforelse
                    </div>
                </div>

                @if(auth()->user()->hasRole('dean'))
                <!-- Submit OPCR -->
                <div id="submitOpcrSection" class="bg-white rounded-lg shadow-sm p-4 sm:p-6 hidden">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Submit OPCR</h3>
                    @php
                        $hasOpcrSubmission = isset($opcrSubmissions) && count($opcrSubmissions) > 0;
                    @endphp
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Current Period:</span>
                            <span class="font-semibold text-gray-900">{{ $currentSemester }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Current Year:</span>
                            <span class="font-semibold text-gray-900">{{ $currentSchoolYear }}</span>
                        </div>
                    </div>
                    @if($hasOpcrSubmission)
                        <button type="button" disabled class="mt-4 w-full bg-gray-400 text-white text-sm font-semibold py-2 rounded cursor-not-allowed opacity-75">
                            &#10003; Submitted
                        </button>
                    @else
                        <button type="button" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded" onclick="openSubmitOpcrModal()">
                            Submit
                        </button>
                    @endif
                    
                    <!-- Submitted OPCRs List -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Submitted OPCRs</h4>
                        @forelse($opcrSubmissions ?? [] as $opcrSub)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm font-semibold text-gray-900 mb-1">{{ $opcrSub->title }}</p>
                                <p class="text-xs text-gray-600">{{ $opcrSub->school_year }} &bull; {{ $opcrSub->semester }}</p>
                                <p class="text-xs text-gray-500 mb-2">Submitted: {{ $opcrSub->submitted_at ? $opcrSub->submitted_at->format('M d, Y') : 'N/A' }}</p>
                                <div class="flex gap-2">
                                    <button onclick="viewOpcrSubmission({{ $opcrSub->id }})" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-1.5 px-3 rounded">
                                        View & Edit
                                    </button>
                                    <button onclick="unsubmitOpcrSubmission({{ $opcrSub->id }})" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold py-1.5 px-3 rounded">
                                        Unsubmit
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 text-center py-4">No submissions yet</p>
                        @endforelse
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
    <div id="submitIpcrModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Submit IPCR</h2>
                <button type="button" onclick="closeSubmitIpcrModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Draft</label>
                    <select id="submitSavedCopySelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">No drafts found</option>
                    </select>
                </div>
            </div>
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                <button type="button" onclick="closeSubmitIpcrModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Cancel
                </button>
                <button type="button" onclick="submitSelectedCopy()" class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition text-sm">
                    Submit
                </button>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasRole('dean'))
    <!-- Submit OPCR Modal -->
    <div id="submitOpcrModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Submit OPCR</h2>
                <button type="button" onclick="closeSubmitOpcrModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select OPCR Draft</label>
                    <select id="submitOpcrTemplateSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">No drafts found</option>
                    </select>
                </div>
            </div>
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                <button type="button" onclick="closeSubmitOpcrModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Cancel
                </button>
                <button type="button" onclick="submitSelectedOpcrTemplate()" class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition text-sm">
                    Submit
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div id="modalHeader" class="bg-yellow-50 border-b border-yellow-200 px-6 py-4 flex items-center gap-3">
                <div class="bg-yellow-100 rounded-full w-12 h-12 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Confirm Action</h2>
                    <p class="text-sm text-gray-600">This action cannot be undone</p>
                </div>
            </div>

            <div class="px-6 py-4">
                <p id="modalMessage" class="text-gray-700 mb-2 text-sm"></p>
                <p id="modalSubMessage" class="text-sm text-gray-600"></p>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                <button type="button" onclick="closeConfirmationModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Cancel
                </button>
                <button type="button" id="confirmButton" onclick="confirmAction()" class="px-4 py-2 rounded-lg font-semibold text-white bg-yellow-600 hover:bg-yellow-700 transition flex items-center gap-2 text-sm">
                    <i class="fas fa-check"></i> <span id="confirmButtonText">Confirm</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div id="alertModalHeader" class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center gap-3">
                <div id="alertModalIconContainer" class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center">
                    <i id="alertModalIcon" class="fas fa-info-circle text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 id="alertModalTitle" class="text-lg font-bold text-gray-900">Information</h2>
                </div>
            </div>

            <div class="px-6 py-4">
                <p id="alertModalMessage" class="text-gray-700 text-sm"></p>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end">
                <button type="button" onclick="closeAlertModal()" class="px-6 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition text-sm">
                    OK
                </button>
            </div>
        </div>
    </div>

    @php
        $facultyMyIpcrsPageConfig = [
            'ipcrRoleLabel' => auth()->user()->designation->title ?? 'Faculty',
            'csrfToken' => csrf_token(),
            'isDean' => auth()->user()->hasRole('dean'),
            'routes' => [
                'ipcrImport' => route('faculty.ipcr.import'),
                'savedCopiesBase' => url('faculty/ipcr/saved-copies'),
                'savedCopiesStore' => route('faculty.ipcr.saved-copies.store'),
                'templatesFromSavedCopy' => route('faculty.ipcr.templates.from-saved-copy'),
                'savedCopiesIndex' => route('faculty.ipcr.saved-copies.index'),
                'templatesBase' => url('faculty/ipcr/templates'),
                'ipcrStore' => route('faculty.ipcr.store'),
                'submissionsBase' => url('faculty/ipcr/submissions'),
                'supportingDocumentsIndex' => route('faculty.supporting-documents.index'),
                'supportingDocumentsStore' => route('faculty.supporting-documents.store'),
            ],
        ];
    @endphp
    <script type="application/json" id="faculty-my-ipcrs-page-config">
        @json($facultyMyIpcrsPageConfig)
    </script>
    <script src="{{ Vite::asset('resources/js/dashboard_faculty_my-ipcrs_page.js') }}"></script>
</body>
</html>
