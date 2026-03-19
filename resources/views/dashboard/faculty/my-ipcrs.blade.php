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
                                            <span class="hidden sm:inline">📋</span> Save as Template
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
                                            <span class="hidden sm:inline">📋</span> Save as Template
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
                                        <p class="text-xs sm:text-sm text-gray-600">{{ $template->school_year }} • {{ $template->semester }}</p>
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
                            ✓ Submitted
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
                                <p class="text-xs text-gray-600">{{ $submission->school_year }} • {{ $submission->semester }}</p>
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
                            ✓ Submitted
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
                                <p class="text-xs text-gray-600">{{ $opcrSub->school_year }} • {{ $opcrSub->semester }}</p>
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

    <style>
        @keyframes scale-in {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .animate-scale-in {
            animation: scale-in 0.2s ease-out;
        }
        
        /* Text Formatting Toolbar */
        .format-toolbar {
            display: flex;
            gap: 2px;
            margin-bottom: 4px;
            background: #f9fafb;
            padding: 4px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        
        .format-btn {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s;
            border-radius: 4px;
        }
        
        .format-btn:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .format-btn.active {
            background: #3b82f6;
            border-color: #2563eb;
            color: white;
        }
        
        /* Contenteditable field styling */
        .editable-field {
            min-height: 80px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
            outline: none;
            transition: all 0.15s;
        }
        
        .editable-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .editable-field:empty:before {
            content: attr(data-placeholder);
            color: #9ca3af;
        }
    </style>

    <script>
        let headerCount = 0;
        let soHeaderCount = 1;
        let currentHeaderForRows = null;
        let pendingAction = null;
        let pendingActionData = null;
        let isEditMode = false;
        let currentTemplateId = null;
        let currentSavedCopyId = null;
        const ipcrRoleLabel = @json(auth()->user()->designation->title ?? 'Faculty');
        const csrfToken = @json(csrf_token());

        // =====================================================
        // QETA Auto-Computation: A = average of Q, E, T
        // =====================================================
        function computeQetaAverage(row) {
            const qInput = row.querySelector('.qeta-q');
            const eInput = row.querySelector('.qeta-e');
            const tInput = row.querySelector('.qeta-t');
            const aInput = row.querySelector('.qeta-a');
            if (!qInput || !eInput || !tInput || !aInput) return;

            const q = parseFloat(qInput.value);
            const e = parseFloat(eInput.value);
            const t = parseFloat(tInput.value);

            if (!isNaN(q) && !isNaN(e) && !isNaN(t)) {
                aInput.value = ((q + e + t) / 3).toFixed(2);
            } else {
                aInput.value = '';
            }
        }

        /**
         * Retroactively label Q/E/T/A number inputs in data rows that were loaded
         * from saved HTML (which doesn't have the qeta-* classes yet).
         * Also makes A inputs readonly and recomputes existing averages.
         * @param {HTMLElement} tableBody - The tbody element to process
         */
        function labelQetaInputs(tableBody) {
            if (!tableBody) return;
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                // Skip section/header rows (they have bg-* classes or colspan)
                if (row.classList.contains('bg-green-100') ||
                    row.classList.contains('bg-purple-100') ||
                    row.classList.contains('bg-orange-100') ||
                    row.classList.contains('bg-blue-100') ||
                    row.classList.contains('bg-gray-100') ||
                    row.querySelector('td[colspan]')) {
                    return;
                }

                // Get all cells in the data row
                const cells = row.querySelectorAll('td');
                // Data rows: MFO | SI | Accomplishments | Q | E | T | A | Remarks
                // Indices:    0  | 1  |       2        | 3 | 4 | 5 | 6 |    7
                if (cells.length >= 7) {
                    const qInput = cells[3]?.querySelector('input[type="number"]');
                    const eInput = cells[4]?.querySelector('input[type="number"]');
                    const tInput = cells[5]?.querySelector('input[type="number"]');
                    const aInput = cells[6]?.querySelector('input[type="number"]');

                    if (qInput && !qInput.classList.contains('qeta-q')) qInput.classList.add('qeta-q');
                    if (eInput && !eInput.classList.contains('qeta-e')) eInput.classList.add('qeta-e');
                    if (tInput && !tInput.classList.contains('qeta-t')) tInput.classList.add('qeta-t');
                    if (aInput && !aInput.classList.contains('qeta-a')) {
                        aInput.classList.add('qeta-a');
                        aInput.readOnly = true;
                        aInput.style.backgroundColor = '#f3f4f6';
                        aInput.title = 'Auto-computed average of Q, E, T';
                    }

                    // Recompute average for rows that already have values
                    computeQetaAverage(row);
                }
            });
        }

        /**
         * Setup event delegation for QETA auto-computation on a table body.
         * Listens for input events on .qeta-q, .qeta-e, .qeta-t and recomputes A.
         */
        function setupQetaDelegation(tableBody) {
            if (!tableBody || tableBody.dataset.qetaDelegated) return;
            tableBody.dataset.qetaDelegated = 'true';

            tableBody.addEventListener('input', function(e) {
                const target = e.target;
                if (target.classList.contains('qeta-q') ||
                    target.classList.contains('qeta-e') ||
                    target.classList.contains('qeta-t')) {
                    const row = target.closest('tr');
                    if (row) computeQetaAverage(row);
                }
            });
        }

        window.openCreateIpcrModal = function() {
            const modal = document.getElementById('createIpcrModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        window.closeCreateIpcrModal = function() {
            const modal = document.getElementById('createIpcrModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            // Reset file input on close
            const importFile = document.getElementById('ipcrImportFile');
            if (importFile) importFile.value = '';
        }

        /**
         * Upload an xlsx file and populate the IPCR or OPCR document editor.
         */
        async function importXlsxFile(file, docType) {
            const formData = new FormData();
            formData.append('file', file);

            // Show loading overlay
            const loadingOverlay = document.getElementById('importLoadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
            }

            try {
                const response = await fetch('{{ route("faculty.ipcr.import") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();

                if (!result.success) {
                    showAlertModal('error', 'Import Failed', result.message || 'Could not parse the uploaded file.');
                    return;
                }

                const data = result.data;

                if (docType === 'ipcr') {
                    const tableBody = document.getElementById('ipcrTableBody');
                    if (tableBody && data.table_body_html) {
                        tableBody.innerHTML = data.table_body_html;
                        unhideTableColumns();
                        labelQetaInputs(tableBody);
                    }
                    if (data.noted_by) {
                        const el = document.getElementById('ipcrDocNotedBy');
                        if (el) el.value = data.noted_by;
                    }
                    if (data.approved_by) {
                        const el = document.getElementById('ipcrDocApprovedBy');
                        if (el) el.value = data.approved_by;
                    }
                    if (data.title) {
                        const el = document.getElementById('ipcrDocumentTitle');
                        if (el) el.value = data.title;
                    }
                } else {
                    const tableBody = document.getElementById('opcrTableBody');
                    if (tableBody && data.table_body_html) {
                        tableBody.innerHTML = data.table_body_html;
                        unhideOpcrTableColumns();
                        labelQetaInputs(tableBody);
                    }
                    if (data.noted_by) {
                        const el = document.getElementById('opcrDocNotedBy');
                        if (el) el.value = data.noted_by;
                    }
                    if (data.approved_by) {
                        const el = document.getElementById('opcrDocApprovedBy');
                        if (el) el.value = data.approved_by;
                    }
                    if (data.title) {
                        const el = document.getElementById('opcrDocumentTitle');
                        if (el) el.value = data.title;
                    }
                }

                showAlertModal('success', 'Imported', 'The Excel file has been imported successfully. Review the data and save when ready.');
            } catch (error) {
                console.error('Import error:', error);
                showAlertModal('error', 'Import Error', 'An error occurred while importing the file.');
            } finally {
                // Hide loading overlay
                const loadingOverlay = document.getElementById('importLoadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.add('hidden');
                    loadingOverlay.classList.remove('flex');
                }
            }
        }

        function hideIpcrTableColumns() {
            const container = document.getElementById('ipcrDocumentContainer');
            if (!container) return;
            const headerRows = container.querySelectorAll('thead tr');
            if (headerRows.length >= 2) {
                const firstRowTh = headerRows[0].querySelectorAll('th');
                for (let i = 2; i < firstRowTh.length; i++) {
                    firstRowTh[i].classList.add('hidden');
                    firstRowTh[i].style.display = 'none';
                }
                const secondRowTh = headerRows[1].querySelectorAll('th');
                secondRowTh.forEach(th => {
                    th.classList.add('hidden');
                    th.style.display = 'none';
                });
            }
            const rows = document.querySelectorAll('#ipcrTableBody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length <= 1) return;
                for (let i = 2; i < cells.length; i++) {
                    cells[i].classList.add('hidden');
                    cells[i].style.display = 'none';
                }
            });
        }

        window.proceedCreateIpcr = function() {
            const schoolYear = document.getElementById('ipcrSchoolYear').value;
            const semester = document.getElementById('ipcrSemester').value;
            
            // Update display values
            document.getElementById('displaySchoolYear').textContent = schoolYear;
            document.getElementById('displaySemester').textContent = semester === 'jan-jun' ? 'January - June' : 'July - December';
            
            // Transfer noted/approved from create modal to document inputs
            const approvedBy = document.getElementById('ipcrCreateApprovedBy')?.value || '';
            const notedBy = document.getElementById('ipcrCreateNotedBy')?.value || '';
            const docApproved = document.getElementById('ipcrDocApprovedBy');
            const docNoted = document.getElementById('ipcrDocNotedBy');
            if (docApproved) docApproved.value = approvedBy;
            if (docNoted) docNoted.value = notedBy;
            
            // Clear table body and reset title
            const tableBody = document.getElementById('ipcrTableBody');
            if (tableBody) {
                tableBody.innerHTML = '';
            }
            
            // Reset document title
            const titleInput = document.getElementById('ipcrDocumentTitle');
            if (titleInput) {
                titleInput.value = `IPCR for ${ipcrRoleLabel}`;
            }
            
            // Reset saved copy ID and SO counter
            currentSavedCopyId = null;
            soHeaderCount = 0;
            
            // Re-hide the rating/accomplishment/remarks columns for fresh creation
            hideIpcrTableColumns();
            
            // Show Save as Template button for fresh creation so users can save as template
            document.getElementById('ipcrExportBtn')?.classList.add('hidden');
            document.getElementById('ipcrSaveAsTemplateBtn')?.classList.remove('hidden');

            // Check for import file (from create modal input or header button)
            const importFile = document.getElementById('ipcrImportFile');
            if (importFile && importFile.files.length > 0) {
                importXlsxFile(importFile.files[0], 'ipcr');
                importFile.value = '';
            } else if (window._pendingHeaderImportFile) {
                importXlsxFile(window._pendingHeaderImportFile, 'ipcr');
                window._pendingHeaderImportFile = null;
            }

            // Hide modal and show IPCR document
            closeCreateIpcrModal();
            
            // Show IPCR document modal
            document.getElementById('ipcrDocumentContainer').classList.remove('hidden');
        };

        window.closeIpcrDocument = function() {
            document.getElementById('ipcrDocumentContainer').classList.add('hidden');
            
            hideIpcrTableColumns();
            currentSavedCopyId = null;
        };

        window.saveDocumentTitle = function() {
            const titleInput = document.getElementById('ipcrDocumentTitle');
            if (titleInput) {
                showAlertModal('success', 'Title Updated', 'Document title has been updated. Remember to save your document to persist changes.');
            }
        }

        function openIpcrFormModal() {
            const modal = document.getElementById('ipcrFormModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        window.closeIpcrFormModal = function() {
            const modal = document.getElementById('ipcrFormModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        window.openSubmitIpcrModal = function() {
            populateSubmitTemplates();
            const modal = document.getElementById('submitIpcrModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        window.closeSubmitIpcrModal = function() {
            const modal = document.getElementById('submitIpcrModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        async function populateSubmitTemplates() {
            const select = document.getElementById('submitSavedCopySelect');
            if (!select) return;

            const drafts = await getSavedCopies();
            select.innerHTML = '';

            if (drafts.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No saved drafts found';
                select.appendChild(option);
                select.disabled = true;
                return;
            }

            select.disabled = false;
            drafts.forEach(draft => {
                const option = document.createElement('option');
                option.value = draft.id;
                option.textContent = `${draft.title} • ${draft.school_year} • ${draft.semester}`;
                select.appendChild(option);
            });
        }

        async function getTemplates() {
            try {
                const response = await fetch('/faculty/ipcr/templates', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                return data.success ? data.templates : [];
            } catch (error) {
                console.error('Error fetching templates:', error);
                return [];
            }
        }

        window.submitSelectedCopy = async function() {
            const select = document.getElementById('submitSavedCopySelect');
            const selectedId = select ? select.value : '';
            if (!selectedId) {
                showAlertModal('warning', 'Select a Draft', 'Please select a draft to submit.');
                return;
            }
            
            try {
                console.log('Fetching saved copy draft:', selectedId);
                const response = await fetch(`/faculty/ipcr/saved-copies/${selectedId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Saved copy response status:', response.status);
                const data = await response.json();
                console.log('Saved copy data:', data);
                
                if (!data.success) {
                    showAlertModal('error', 'Not Found', 'Selected draft could not be found.');
                    return;
                }
                
                const item = data.savedCopy;
                const soCounts = item.so_count_json || { strategic_objectives: 0, core_functions: 0, support_functions: 0 };
                
                console.log('Item from saved copy draft:', item);
                console.log('Table body HTML:', item.table_body_html);
                console.log('SO Counts:', soCounts);
                
                // Use FormData instead of JSON to avoid HTML encoding issues
                const formData = new FormData();
                formData.append('title', item.title);
                formData.append('school_year', item.school_year);
                formData.append('semester', item.semester);
                formData.append('table_body_html', item.table_body_html || '');
                formData.append('so_count_json', JSON.stringify(soCounts));
                formData.append('saved_copy_id', selectedId); // Include saved_copy_id for document copying
                formData.append('noted_by', item.noted_by || '');
                formData.append('approved_by', item.approved_by || '');
                
                console.log('FormData entries:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ':', pair[1].substring(0, 100));
                }

                const submitResponse = await fetch('/faculty/ipcr/submissions', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                console.log('Submit response status:', submitResponse.status);
                if (!submitResponse.ok) {
                    const submitData = await submitResponse.json().catch(() => ({}));
                    console.error('Submit failed:', submitData);
                    throw new Error(submitData.message || 'Failed to submit IPCR');
                }
                
                const submitResult = await submitResponse.json();
                console.log('Submit success:', submitResult);

                closeSubmitIpcrModal();
                showAlertModal('success', 'Submitted', 'Your IPCR has been submitted successfully.', function() {
                    // Reload page to show the new submission
                    window.location.reload();
                });
            } catch (error) {
                console.error('Submit error:', error);
                showAlertModal('error', 'Submit Failed', error.message || 'Failed to submit IPCR.');
            }
        }

        window.saveIpcrDocument = function() {
            const schoolYear = document.getElementById('displaySchoolYear')?.textContent?.trim();
            const semester = document.getElementById('displaySemester')?.textContent?.trim();
            const titleInput = document.getElementById('ipcrDocumentTitle');
            const title = titleInput ? titleInput.value.trim() : `IPCR for ${ipcrRoleLabel}`;
            const tableBody = document.getElementById('ipcrTableBody');
            const tableBodyHtml = tableBody ? buildTableBodySnapshot(tableBody) : '';
            const notedBy = document.getElementById('ipcrDocNotedBy')?.value?.trim() || '';
            const approvedBy = document.getElementById('ipcrDocApprovedBy')?.value?.trim() || '';

            const payload = {
                title: title,
                school_year: schoolYear || 'N/A',
                semester: semester || 'N/A',
                table_body_html: tableBodyHtml,
                noted_by: notedBy,
                approved_by: approvedBy
            };

            const url = currentSavedCopyId 
                ? `{{ url('faculty/ipcr/saved-copies') }}/${currentSavedCopyId}` 
                : '{{ route("faculty.ipcr.saved-copies.store") }}';
            const method = currentSavedCopyId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertModal('success', 'Saved', data.message);
                    // Reload page to ensure UI reflects database state
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to save IPCR draft');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while saving the IPCR draft.');
            });
        };

        function extractSoCounts() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return { strategic_objectives: 0, core_functions: 0, support_functions: 0 };
            
            let counts = {
                strategic_objectives: 0,
                core_functions: 0,
                support_functions: 0
            };
            
            let currentSection = null;
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const className = row.className;
                
                // Detect section headers
                if (className.includes('bg-green-100')) {
                    currentSection = 'strategic_objectives';
                } else if (className.includes('bg-purple-100')) {
                    currentSection = 'core_functions';
                } else if (className.includes('bg-orange-100')) {
                    currentSection = 'support_functions';
                } else if (className.includes('bg-gray-100') && row.querySelector('td[colspan]')) {
                    // Custom section (Others) - don't assign to any count
                    currentSection = null;
                }
                
                // Count SO headers (blue rows) under the current section
                if (className.includes('bg-blue-100') && currentSection) {
                    counts[currentSection]++;
                }
            });
            
            return counts;
        }

        function extractSoCountsFromHtml(tableBodyHtml) {
            if (!tableBodyHtml || typeof tableBodyHtml !== 'string') {
                return { strategic_objectives: 0, core_functions: 0, support_functions: 0 };
            }

            const container = document.createElement('tbody');
            container.innerHTML = tableBodyHtml;

            let counts = {
                strategic_objectives: 0,
                core_functions: 0,
                support_functions: 0
            };

            let currentSection = null;
            const rows = container.querySelectorAll('tr');

            rows.forEach(row => {
                const className = row.className || '';

                if (className.includes('bg-green-100')) {
                    currentSection = 'strategic_objectives';
                } else if (className.includes('bg-purple-100')) {
                    currentSection = 'core_functions';
                } else if (className.includes('bg-orange-100')) {
                    currentSection = 'support_functions';
                } else if (className.includes('bg-gray-100') && row.querySelector('td[colspan]')) {
                    currentSection = null;
                }

                if (className.includes('bg-blue-100') && currentSection) {
                    counts[currentSection]++;
                }
            });

            return counts;
        }

        // ── Export IPCR saved copy from document container ──────────
        window.exportIpcrDocument = function() {
            if (!currentSavedCopyId) {
                showAlertModal('info', 'Save First', 'Please save the document first before exporting.');
                return;
            }
            window.location.href = `/faculty/ipcr/saved-copies/${currentSavedCopyId}/export`;
        };

        window.saveAsTemplate = function() {
            const schoolYear = document.getElementById('displaySchoolYear')?.textContent?.trim();
            const semester = document.getElementById('displaySemester')?.textContent?.trim();
            const titleInput = document.getElementById('ipcrDocumentTitle');
            const title = titleInput ? titleInput.value.trim() : `IPCR Template`;
            const tableBody = document.getElementById('ipcrTableBody');
            const tableBodyHtml = tableBody ? buildTableBodySnapshot(tableBody) : '';
            const notedBy = document.getElementById('ipcrDocNotedBy')?.value?.trim() || '';
            const approvedBy = document.getElementById('ipcrDocApprovedBy')?.value?.trim() || '';

            if (!tableBodyHtml || tableBodyHtml.trim() === '') {
                showAlertModal('warning', 'Empty Template', 'Please add some content before saving as a template.');
                return;
            }

            // Extract SO counts
            const soCounts = extractSoCounts();

            const payload = {
                title: title,
                school_year: schoolYear || 'N/A',
                semester: semester || 'N/A',
                table_body_html: tableBodyHtml,
                so_count_json: soCounts,
                noted_by: notedBy,
                approved_by: approvedBy
            };

            console.log('Saving template with counts:', soCounts);

            fetch('{{ route("faculty.ipcr.templates.from-saved-copy") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const message = data.updated 
                        ? 'Your existing template has been updated with the new content.' 
                        : 'Your IPCR has been saved as a template. You can find it in the Templates section.';
                    const title = data.updated ? 'Template Updated' : 'Template Saved';
                    
                    showAlertModal('success', title, message, function() {
                        location.reload();
                    });
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to save as template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', error.message || 'An error occurred while saving the template.');
            });
        }

        async function getSavedCopies() {
            try {
                const response = await fetch('{{ route("faculty.ipcr.saved-copies.index") }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                return data.success ? data.savedCopies : [];
            } catch (error) {
                console.error('Error fetching saved copies:', error);
                return [];
            }
        }

        function buildTableBodySnapshot(tableBody) {
            const clone = tableBody.cloneNode(true);
            clone.querySelectorAll('input, textarea').forEach(field => {
                if (field.tagName.toLowerCase() === 'input') {
                    field.setAttribute('value', field.value);
                } else if (field.tagName.toLowerCase() === 'textarea') {
                    field.textContent = field.value;
                }
            });
            return clone.innerHTML;
        }

        function formatSavedDate(isoDate) {
            const date = new Date(isoDate);
            if (Number.isNaN(date.getTime())) {
                return 'Unknown date';
            }
            return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        }

        async function renderSavedCopies() {
            const list = document.getElementById('savedCopiesList');
            const empty = document.getElementById('savedCopiesEmpty');
            if (!list || !empty) return;

            const savedCopies = await getSavedCopies();
            list.innerHTML = '';

            if (savedCopies.length === 0) {
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');
            savedCopies.forEach(item => {
                const card = document.createElement('div');
                card.className = 'submission-card';
                const savedDate = item.saved_at || item.created_at;
                card.innerHTML = `
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-900">${item.title}</p>
                            <p class="text-xs text-gray-500 mt-1">${item.school_year} • ${item.semester}</p>
                            <p class="text-xs text-gray-500 mt-1">Saved on ${formatSavedDate(savedDate)}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button class="text-blue-600 hover:text-blue-700 font-semibold text-xs sm:text-sm" onclick="editSavedCopy(${item.id})">View</button>
                            <button class="text-red-600 hover:text-red-700 font-semibold text-xs sm:text-sm" onclick="deleteSavedCopy(${item.id})">Delete</button>
                        </div>
                    </div>
                `;
                list.appendChild(card);
            });
        }

        window.deleteSavedCopy = function(id) {
            openConfirmationModal(
                'Delete Saved Copy',
                'Are you sure you want to delete this saved copy?',
                'This action cannot be undone.',
                'danger',
                'Delete',
                function() {
                    fetch(`{{ url('faculty/ipcr/saved-copies') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (currentSavedCopyId === id) {
                                currentSavedCopyId = null;
                            }
                            showAlertModal('success', 'Deleted', 'Saved copy deleted successfully!', function() {
                                window.location.reload();
                            });
                        } else {
                            showAlertModal('error', 'Error', data.message || 'Failed to delete saved copy');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while deleting the saved copy.');
                    });
                }
            );
        }

        function updateSoHeaderCountFromTable() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr.bg-blue-100');
            let count = 0;
            rows.forEach(row => {
                const input = row.querySelector('input[type="text"]');
                if (input && input.value.includes('SO')) {
                    count += 1;
                }
            });
            soHeaderCount = Math.max(1, count);
        }

        function unhideTableColumns() {
            // Unhide table headers
            const headers = document.querySelectorAll('#ipcrDocumentContainer thead th.hidden');
            headers.forEach(header => {
                header.classList.remove('hidden');
                header.style.display = '';
            });
            
            // Unhide table body cells
            const cells = document.querySelectorAll('#ipcrTableBody td.hidden');
            cells.forEach(cell => {
                cell.classList.remove('hidden');
                cell.style.display = '';
            });
        }
        
        window.editSavedCopy = function(id) {
            fetch(`{{ url('faculty/ipcr/saved-copies') }}/${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = data.savedCopy;
                    const tableBody = document.getElementById('ipcrTableBody');
                    if (tableBody && item.table_body_html) {
                        tableBody.innerHTML = item.table_body_html;
                        
                        // Unhide columns for saved copy
                        unhideTableColumns();
                        // Label QETA inputs and set up auto-computation
                        labelQetaInputs(tableBody);
                    }

                    // Load title
                    const titleInput = document.getElementById('ipcrDocumentTitle');
                    if (titleInput && item.title) {
                        titleInput.value = item.title;
                    }

                    if (item.school_year) {
                        const displaySchoolYear = document.getElementById('displaySchoolYear');
                        if (displaySchoolYear) displaySchoolYear.textContent = item.school_year;
                    }
                    if (item.semester) {
                        const displaySemester = document.getElementById('displaySemester');
                        if (displaySemester) displaySemester.textContent = item.semester;
                    }

                    // Load noted/approved by
                    const docNotedBy = document.getElementById('ipcrDocNotedBy');
                    if (docNotedBy && item.noted_by) docNotedBy.value = item.noted_by;
                    const docApprovedBy = document.getElementById('ipcrDocApprovedBy');
                    if (docApprovedBy && item.approved_by) docApprovedBy.value = item.approved_by;

                    updateSoHeaderCountFromTable();
                    currentSavedCopyId = item.id;

                    // Show Export + Save as Template when editing an existing saved copy
                    document.getElementById('ipcrExportBtn')?.classList.remove('hidden');
                    document.getElementById('ipcrSaveAsTemplateBtn')?.classList.remove('hidden');

                    document.getElementById('ipcrDocumentContainer').classList.remove('hidden');
                } else {
                    showAlertModal('error', 'Not Found', 'Saved copy could not be found.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while loading the saved copy.');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderSavedCopies();
            @if(auth()->user()->hasRole('dean'))
            renderOpcrSavedCopies();
            @endif
            
            // Setup update button event listener
            const updateBtn = document.getElementById('updateSubmissionBtn');
            if (updateBtn) {
                updateBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submissionId = document.getElementById('currentSubmissionIdToUpdate').value;
                    if (submissionId) {
                        console.log('Update button clicked, submission ID:', submissionId);
                        updateSubmissionData(submissionId);
                    } else {
                        console.error('No submission ID found');
                    }
                });
            }

            // Setup QETA event delegation on all table bodies
            setupQetaDelegation(document.getElementById('ipcrTableBody'));
            setupQetaDelegation(document.getElementById('opcrTableBody'));
            setupQetaDelegation(document.getElementById('templatePreviewTableBody'));
        });

        window.addSectionHeader = function(headerText = '', isEditable = true) {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;
            
            // Close dropdown after selection
            toggleSectionHeaderDropdown();
            
            // Reset SO counter for non-custom sections
            if (!isEditable) {
                soHeaderCount = 0;
            }
            
            // Determine color based on section type
            let bgColor = 'bg-gray-100'; // Default for custom/others
            if (!isEditable) {
                if (headerText === 'Strategic Objectives') {
                    bgColor = 'bg-green-100';
                } else if (headerText === 'Core Functions') {
                    bgColor = 'bg-purple-100';
                } else if (headerText === 'Support Function') {
                    bgColor = 'bg-orange-100';
                }
            }
            
            // Create new section header row
            const newRow = document.createElement('tr');
            newRow.className = bgColor;
            
            if (isEditable) {
                // Editable section header (Others/Custom)
                newRow.innerHTML = `
                    <td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">
                        <input type="text" class="w-full bg-transparent border-0 focus:ring-0 font-semibold text-gray-800" placeholder="Enter custom section header..." value="${headerText}" />
                    </td>
                `;
            } else {
                // Non-editable section header (predefined sections)
                newRow.innerHTML = `
                    <td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">
                        <div class="font-semibold text-gray-800">${headerText}</div>
                        <input type="hidden" value="${headerText}" />
                    </td>
                `;
                // Track section type for future development
                newRow.setAttribute('data-section-type', headerText.toLowerCase().replace(/\s+/g, '-'));
            }
            
            // Append to table
            tableBody.appendChild(newRow);
        }
        
        window.toggleSectionHeaderDropdown = function() {
            const dropdown = document.getElementById('sectionHeaderDropdownMenu');
            if (!dropdown) return;
            
            dropdown.classList.toggle('hidden');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('sectionHeaderDropdown');
            const dropdownMenu = document.getElementById('sectionHeaderDropdownMenu');
            
            if (dropdown && dropdownMenu && !dropdown.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        window.addSOHeader = function() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;
            
            // Find the last section header (green, purple, or orange background)
            const allRows = tableBody.querySelectorAll('tr');
            let lastSectionIndex = -1;
            
            for (let i = allRows.length - 1; i >= 0; i--) {
                const row = allRows[i];
                if (row.classList.contains('bg-green-100') || 
                    row.classList.contains('bg-purple-100') || 
                    row.classList.contains('bg-orange-100')) {
                    lastSectionIndex = i;
                    break;
                }
            }
            
            // Count existing SO headers after the last section header
            let currentSOCount = 0;
            for (let i = lastSectionIndex + 1; i < allRows.length; i++) {
                const row = allRows[i];
                if (row.classList.contains('bg-blue-100')) {
                    const span = row.querySelector('span.font-semibold.text-gray-800');
                    if (span && span.textContent.includes('SO')) {
                        currentSOCount++;
                    }
                }
            }
            
            // Set next SO number
            const nextSONumber = currentSOCount + 1;
            const soLabel = convertToRoman(nextSONumber);
            
            // Create new SO header row
            const newRow = document.createElement('tr');
            newRow.className = 'bg-blue-100';
            newRow.innerHTML = `
                <td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800">SO ${soLabel}:</span>
                        <input type="text" class="flex-1 bg-transparent border-0 focus:ring-0 font-semibold text-gray-800" placeholder="Enter SO description..." value="" />
                    </div>
                </td>
            `;
            
            // Append to table
            tableBody.appendChild(newRow);
        }

        // Convert number to Roman numeral
        function convertToRoman(num) {
            const romanNumerals = [
                { value: 1000, numeral: 'M' },
                { value: 900, numeral: 'CM' },
                { value: 500, numeral: 'D' },
                { value: 400, numeral: 'CD' },
                { value: 100, numeral: 'C' },
                { value: 90, numeral: 'XC' },
                { value: 50, numeral: 'L' },
                { value: 40, numeral: 'XL' },
                { value: 10, numeral: 'X' },
                { value: 9, numeral: 'IX' },
                { value: 5, numeral: 'V' },
                { value: 4, numeral: 'IV' },
                { value: 1, numeral: 'I' }
            ];
            
            let result = '';
            for (let i = 0; i < romanNumerals.length; i++) {
                while (num >= romanNumerals[i].value) {
                    result += romanNumerals[i].numeral;
                    num -= romanNumerals[i].value;
                }
            }
            return result;
        }

        window.addDataRow = function() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;

            // Check if columns are currently visible
            const container = document.getElementById('ipcrDocumentContainer');
            const remarksHeader = container?.querySelector('thead th:last-child');
            const isExpanded = remarksHeader && !remarksHeader.classList.contains('hidden');
            const hiddenClass = isExpanded ? '' : ' hidden';
            
            // Create new data row
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="border border-gray-300 px-2 py-2">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter MFO"></textarea>
                </td>
                <td class="border border-gray-300 px-2 py-2">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Success Indicators"></textarea>
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Actual Accomplishments"></textarea>
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-q" min="1" max="5" step="1" placeholder="-">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-e" min="1" max="5" step="1" placeholder="-">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-t" min="1" max="5" step="1" placeholder="-">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-a" min="1" max="5" step="0.01" placeholder="-" readonly style="background-color: #f3f4f6;" title="Auto-computed average of Q, E, T">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Remarks"></textarea>
                </td>
            `;
            
            // Append to table
            tableBody.appendChild(newRow);
        }

        function deleteSectionHeader(button) {
            // Find the parent row and remove it
            const row = button.closest('tr');
            if (row) {
                row.remove();
            }
        }

        function removeLastSectionHeader() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;
            
            // Find all rows and remove the last one that looks like a section header (bg-blue-100 or bg-blue-50)
            const rows = tableBody.querySelectorAll('tr');
            for (let i = rows.length - 1; i >= 0; i--) {
                if (rows[i].classList.contains('bg-blue-100') || rows[i].classList.contains('bg-blue-50')) {
                    rows[i].remove();
                    break;
                }
            }
        }

        function removeLastSOHeader() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;
            
            // Find all rows and remove the last one that is a SO header (bg-blue-100 with specific structure)
            const rows = tableBody.querySelectorAll('tr');
            for (let i = rows.length - 1; i >= 0; i--) {
                if (rows[i].classList.contains('bg-blue-100')) {
                    const span = rows[i].querySelector('span');
                    if (span && span.textContent.includes('SO')) {
                        rows[i].remove();
                        soHeaderCount--;
                        break;
                    }
                }
            }
        }

        function removeLastDataRow() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;
            
            // Find all rows and remove the last one that is a data row (no special classes)
            const rows = tableBody.querySelectorAll('tr');
            for (let i = rows.length - 1; i >= 0; i--) {
                if (!rows[i].classList.contains('bg-blue-50') && 
                    !rows[i].classList.contains('bg-blue-100') && 
                    !rows[i].classList.contains('bg-green-100') &&
                    !rows[i].classList.contains('bg-gray-100')) {
                    rows[i].remove();
                    break;
                }
            }
        }

        window.removeLastRow = function() {
            const tableBody = document.getElementById('ipcrTableBody');
            if (!tableBody) return;
            
            // Remove the last row in the table body
            const rows = tableBody.querySelectorAll('tr');
            if (rows.length > 0) {
                rows[rows.length - 1].remove();
            }
        }

        // Text formatting functions
        let activeEditableField = null;
        
        function formatText(command) {
            if (!activeEditableField || !activeEditableField.classList.contains('editable-field')) {
                console.log('No active field');
                return;
            }
            
            console.log('Formatting with command:', command);
            console.log('Active field:', activeEditableField);
            
            // Focus the field first
            activeEditableField.focus();
            
            // Small delay to ensure focus is set
            setTimeout(() => {
                document.execCommand(command, false, null);
                updateFormatButtons();
            }, 10);
        }
        
        function updateFormatButtons() {
            const toolbar = document.querySelector('.format-toolbar');
            if (!toolbar) return;
            
            const buttons = toolbar.querySelectorAll('.format-btn');
            buttons[0].classList.toggle('active', document.queryCommandState('bold'));
            buttons[1].classList.toggle('active', document.queryCommandState('italic'));
            buttons[2].classList.toggle('active', document.queryCommandState('underline'));
        }
        
        function setupFormatField(field) {
            console.log('Setting up format field:', field);
            
            // Track focus to know which field is active
            field.addEventListener('focus', function() {
                activeEditableField = field;
                console.log('Field focused:', field);
                updateFormatButtons();
            });
            
            field.addEventListener('blur', function() {
                console.log('Field blurred');
            });
            
            // Handle keyboard shortcuts
            field.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    if (e.key === 'b') {
                        e.preventDefault();
                        document.execCommand('bold');
                        updateFormatButtons();
                    } else if (e.key === 'i') {
                        e.preventDefault();
                        document.execCommand('italic');
                        updateFormatButtons();
                    } else if (e.key === 'u') {
                        e.preventDefault();
                        document.execCommand('underline');
                        updateFormatButtons();
                    }
                }
            });
            
            // Update buttons on selection change
            field.addEventListener('mouseup', () => updateFormatButtons());
            field.addEventListener('keyup', () => updateFormatButtons());
        }

        // Initialize formatting toolbar event listeners
        function initFormatToolbar() {
            const toolbar = document.querySelector('.format-toolbar');
            if (!toolbar) return;
            
            const buttons = toolbar.querySelectorAll('.format-btn');
            buttons.forEach(button => {
                button.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    const command = this.getAttribute('data-command');
                    formatText(command);
                });
            });
        }
        
        function showIPCRForm() {
            openIpcrFormModal();
            
            // Initialize toolbar event listeners
            initFormatToolbar();
            
            // Setup formatting for the initial Strategic Objectives field
            const strategicField = document.querySelector('#strategicObjectivesContainer .editable-field');
            if (strategicField) setupFormatField(strategicField);
        }

        window.addHeader = function() {
            headerCount++;
            const headersContainer = document.getElementById('headersContainer');
            
            const headerDiv = document.createElement('div');
            headerDiv.className = 'mb-2';
            headerDiv.id = `header-${headerCount}`;
            headerDiv.dataset.headerId = headerCount;
            headerDiv.dataset.type = 'header';
            
            const soFieldId = `so-field-${headerCount}`;
            
            headerDiv.innerHTML = `
                <div class="flex items-start gap-2">
                    <div id="${soFieldId}" class="editable-field flex-1" contenteditable="true" data-placeholder="SO I"></div>
                    <div class="flex flex-col gap-0.5">
                        <button type="button" onclick="moveHeaderUp(${headerCount})" class="bg-blue-50 text-blue-600 border border-blue-200 w-5 h-5 flex items-center justify-center hover:bg-blue-100 hover:border-blue-300 transition-all" title="Move Up">
                            <i class="fas fa-chevron-up" style="font-size: 8px;"></i>
                        </button>
                        <button type="button" onclick="moveHeaderDown(${headerCount})" class="bg-blue-50 text-blue-600 border border-blue-200 w-5 h-5 flex items-center justify-center hover:bg-blue-100 hover:border-blue-300 transition-all" title="Move Down">
                            <i class="fas fa-chevron-down" style="font-size: 8px;"></i>
                        </button>
                    </div>
                    <button type="button" onclick="removeHeader(${headerCount})" class="bg-red-50 text-red-600 border border-red-200 w-5 h-5 flex items-center justify-center hover:bg-red-100 hover:border-red-300 flex-shrink-0 transition-all">
                        <i class="fas fa-times" style="font-size: 8px;"></i>
                    </button>
                </div>
            `;
            
            headersContainer.appendChild(headerDiv);
            
            // Setup formatting for the new field
            const soField = document.getElementById(soFieldId);
            if (soField) setupFormatField(soField);
            
            currentHeaderForRows = headerCount;
        }

        window.addRow = function() {
            if (currentHeaderForRows === null) {
                showAlertModal('warning', 'Add Header First', 'Please add a header first before adding rows.');
                return;
            }
            
            const headersContainer = document.getElementById('headersContainer');
            if (!headersContainer) {
                showAlertModal('warning', 'Add Header First', 'Please add a header first.');
                return;
            }
            
            const rowId = `row-${currentHeaderForRows}-${Date.now()}`;
            const mroFieldId = `mro-${rowId}`;
            const successFieldId = `success-${rowId}`;
            
            const rowDiv = document.createElement('div');
            rowDiv.className = 'mb-2';
            rowDiv.id = rowId;
            rowDiv.dataset.type = 'row';
            rowDiv.dataset.headerId = currentHeaderForRows;
            
            rowDiv.innerHTML = `
                <div class="flex items-start gap-2">
                    <div class="flex-1 grid grid-cols-2 gap-4">
                        <div id="${mroFieldId}" class="editable-field" contenteditable="true" data-placeholder="Major Responsibility & Output"></div>
                        <div id="${successFieldId}" class="editable-field" contenteditable="true" data-placeholder="Success Indicator"></div>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <button type="button" onclick="moveRowUp('${rowId}')" class="bg-indigo-50 text-indigo-600 border border-indigo-200 w-5 h-5 flex items-center justify-center hover:bg-indigo-100 hover:border-indigo-300 transition-all" title="Move Up">
                            <i class="fas fa-chevron-up" style="font-size: 8px;"></i>
                        </button>
                        <button type="button" onclick="moveRowDown('${rowId}')" class="bg-indigo-50 text-indigo-600 border border-indigo-200 w-5 h-5 flex items-center justify-center hover:bg-indigo-100 hover:border-indigo-300 transition-all" title="Move Down">
                            <i class="fas fa-chevron-down" style="font-size: 8px;"></i>
                        </button>
                    </div>
                    <button type="button" onclick="removeRow('${rowId}')" class="bg-red-50 text-red-600 border border-red-200 w-5 h-5 flex items-center justify-center hover:bg-red-100 hover:border-red-300 flex-shrink-0 transition-all">
                        <i class="fas fa-times" style="font-size: 8px;"></i>
                    </button>
                </div>
            `;
            
            headersContainer.appendChild(rowDiv);
            
            // Setup formatting for the new fields
            const mroField = document.getElementById(mroFieldId);
            const successField = document.getElementById(successFieldId);
            if (mroField) setupFormatField(mroField);
            if (successField) setupFormatField(successField);
        }

        window.removeHeader = function(headerId) {
            const headerElement = document.getElementById(`header-${headerId}`);
            if (headerElement) {
                headerElement.remove();
                if (currentHeaderForRows === headerId) {
                    // Find the last remaining header or reset to null
                    const remainingHeaders = document.querySelectorAll('[data-header-id]');
                    if (remainingHeaders.length > 0) {
                        currentHeaderForRows = parseInt(remainingHeaders[remainingHeaders.length - 1].dataset.headerId);
                    } else {
                        currentHeaderForRows = null;
                    }
                }
            }
        }

        window.removeRow = function(rowId) {
            const rowElement = document.getElementById(rowId);
            if (rowElement) {
                rowElement.remove();
            }
        }

        window.clearForm = function() {
            openConfirmationModal(
                'Clear Form',
                'Are you sure you want to clear the form?',
                'All unsaved data will be lost.',
                'warning',
                'Clear',
                function() {
                    // Clear strategic objectives
                    document.getElementById('strategicObjectivesContainer').innerHTML = `
                        <div class="mb-4">
                            <div class="editable-field" contenteditable="true" data-placeholder="Strategic objectives..."></div>
                        </div>
                    `;
                    
                    // Setup formatting for the new field
                    const strategicField = document.querySelector('#strategicObjectivesContainer .editable-field');
                    if (strategicField) setupFormatField(strategicField);
                    
                    // Clear all headers
                    document.getElementById('headersContainer').innerHTML = '';
                    
                    // Reset counters
                    headerCount = 0;
                    currentHeaderForRows = null;
                }
            );
        }

        window.generateIPCR = function() {
            // Validate form has content
            const stratObjectives = document.querySelectorAll('#strategicObjectivesContainer .editable-field');
            const headers = document.querySelectorAll('[data-header-id]');
            
            let hasContent = false;
            stratObjectives.forEach(field => {
                if (field.innerHTML.trim()) hasContent = true;
            });
            
            if (!hasContent && headers.length === 0) {
                showAlertModal('warning', 'Empty Form', 'Please fill in at least some content before saving.');
                return;
            }
            
            // Get template name - from edit field if in edit mode, otherwise generate default
            let templateName;
            if (isEditMode) {
                templateName = document.getElementById('currentTemplateName').value.trim() || 'IPCR Template';
            } else {
                const now = new Date();
                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const currentMonth = monthNames[now.getMonth()];
                const currentYear = now.getFullYear();
                templateName = `IPCR - ${currentMonth} ${currentYear}`;
            }
            
            // Collect form data
            const formData = {
                title: templateName,
                strategic_objectives: [],
                headers: []
            };
            
            console.log('==== COLLECTING FORM DATA ====');
            
            // Get strategic objectives (already queried above, reuse the same variable)
            console.log('Strategic objective fields found:', stratObjectives.length);
            stratObjectives.forEach((field, i) => {
                console.log(`SO ${i}:`, field.innerHTML);
                if (field.innerHTML.trim()) {
                    formData.strategic_objectives.push(field.innerHTML.trim());
                }
            });
            
            // Get headers and rows - Check container exists
            const headersContainer = document.getElementById('headersContainer');
            console.log('Headers container:', headersContainer);
            console.log('Headers container HTML:', headersContainer ? headersContainer.innerHTML : 'NULL');
            
            // Get all items (headers and rows) from container
            const allItems = headersContainer ? headersContainer.children : [];
            console.log('Total items in container:', allItems.length);
            
            let currentHeader = null;
            
            for (let i = 0; i < allItems.length; i++) {
                const item = allItems[i];
                const itemType = item.dataset.type;
                
                console.log(`Item ${i}: type=${itemType}, id=${item.id}`);
                
                if (itemType === 'header') {
                    const soField = item.querySelector('.editable-field');
                    currentHeader = {
                        so: soField ? soField.innerHTML.trim() : '',
                        rows: []
                    };
                    formData.headers.push(currentHeader);
                    console.log(`Created header: ${currentHeader.so}`);
                } else if (itemType === 'row' && currentHeader) {
                    const editableFields = item.querySelectorAll('.editable-field');
                    if (editableFields.length === 2) {
                        const rowData = {
                            mro: editableFields[0].innerHTML.trim(),
                            success_indicator: editableFields[1].innerHTML.trim()
                        };
                        currentHeader.rows.push(rowData);
                        console.log(`Added row to current header:`, rowData);
                    }
                }
            }
            
            console.log('\n==== FINAL FORM DATA ====');
            console.log('Final form data to save:', formData);
            console.log('Total headers to save:', formData.headers.length);
            formData.headers.forEach((h, i) => {
                console.log(`Header ${i}:`, h);
            });
            
            // Ensure arrays are properly formatted
            const dataToSend = {
                title: formData.title,
                strategic_objectives: Array.isArray(formData.strategic_objectives) ? formData.strategic_objectives : [],
                headers: Array.isArray(formData.headers) ? formData.headers : []
            };
            
            console.log('\n==== DATA TO SEND ====');
            console.log('Data being sent to server:', dataToSend);
            console.log('JSON stringified:', JSON.stringify(dataToSend));
            
            // Determine if update or create
            const url = isEditMode ? `{{ url('faculty/ipcr/templates') }}/${currentTemplateId}` : '{{ route("faculty.ipcr.store") }}';
            const method = isEditMode ? 'PUT' : 'POST';
            
            // Send formData to server via AJAX
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(dataToSend)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertModal('success', isEditMode ? 'Changes Saved' : 'Template Created', isEditMode ? 'Your changes have been saved successfully!' : 'IPCR template created successfully!', function() {
                        location.reload();
                    });
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to save IPCR template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while saving the IPCR template.');
            });
        }

        window.loadTemplateToDocument = function(templateId) {
            fetch(`{{ url('faculty/ipcr/templates') }}/${templateId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const template = data.template;
                    
                    // Check if template has table_body_html
                    if (template.table_body_html) {
                        const tableBody = document.getElementById('templatePreviewTableBody');
                        if (tableBody) {
                            tableBody.innerHTML = template.table_body_html;
                        }

                        // Load title
                        const titleElement = document.getElementById('templatePreviewTitle');
                        if (titleElement && template.title) {
                            titleElement.textContent = template.title;
                        }

                        // Load school year and semester
                        if (template.school_year) {
                            const displaySchoolYear = document.getElementById('templatePreviewSchoolYear');
                            if (displaySchoolYear) displaySchoolYear.textContent = template.school_year;
                        }
                        if (template.semester) {
                            const displaySemester = document.getElementById('templatePreviewSemester');
                            if (displaySemester) displaySemester.textContent = template.semester;
                        }

                        // Load noted/approved by
                        const tpNotedBy = document.getElementById('templatePreviewNotedBy');
                        if (tpNotedBy) tpNotedBy.value = template.noted_by || '';
                        const tpApprovedBy = document.getElementById('templatePreviewApprovedBy');
                        if (tpApprovedBy) tpApprovedBy.value = template.approved_by || '';

                        // Store template ID for save copy functionality
                        const templateIdField = document.getElementById('currentPreviewTemplateId');
                        if (templateIdField) {
                            templateIdField.value = templateId;
                        }

                        // Clear submission fields so doc context resolves to template
                        const submIdField = document.getElementById('currentSubmissionIdToUpdate');
                        if (submIdField) submIdField.value = '';
                        const submTypeField = document.getElementById('currentSubmissionType');
                        if (submTypeField) submTypeField.value = 'ipcr';

                        // Unhide all columns in the preview modal
                        const previewModal = document.getElementById('templatePreviewModal');
                        if (previewModal) {
                            const headers = previewModal.querySelectorAll('thead th.hidden');
                            headers.forEach(header => header.classList.remove('hidden'));
                            const cells = previewModal.querySelectorAll('td.hidden');
                            cells.forEach(cell => { cell.classList.remove('hidden'); cell.style.display = ''; });
                        }

                        // Show Edit IPCR button, hide Update Submission button
                        const saveCopyBtn = document.getElementById('saveCopyBtn');
                        if (saveCopyBtn) saveCopyBtn.style.display = '';
                        const updateBtn = document.getElementById('updateSubmissionBtn');
                        if (updateBtn) updateBtn.classList.add('hidden');

                        document.getElementById('templatePreviewModal').classList.remove('hidden');
                        attachSoDocumentClickHandlers();
                        // Label QETA inputs and set up auto-computation
                        labelQetaInputs(document.getElementById('templatePreviewTableBody'));
                    } else {
                        showAlertModal('info', 'Legacy Template', 'This template was created with the old format. Please use the Edit button to modify it.');
                    }
                } else {
                    showAlertModal('error', 'Not Found', 'Template could not be found.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while loading the template.');
            });
        }

        // ── Export from template preview modal (handles submissions + templates, IPCR + OPCR) ──
        window.exportFromPreview = function() {
            const submissionId = document.getElementById('currentSubmissionIdToUpdate')?.value;
            const templateId = document.getElementById('currentPreviewTemplateId')?.value;
            const docType = document.getElementById('currentSubmissionType')?.value || 'ipcr';

            let url = '';
            if (submissionId) {
                // Exporting a submission
                url = `/faculty/${docType}/submissions/${submissionId}/export`;
            } else if (templateId) {
                // Exporting a template
                url = `/faculty/${docType}/templates/${templateId}/export`;
            } else {
                showAlertModal('info', 'Nothing to Export', 'No document is loaded to export.');
                return;
            }
            window.location.href = url;
        };
        
        function doCloseTemplatePreview() {
            // Clear submission tracking fields
            const submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
            if (submissionIdField) submissionIdField.value = '';
            const submissionTypeField = document.getElementById('currentSubmissionType');
            if (submissionTypeField) submissionTypeField.value = 'ipcr';

            // Make table cells non-editable when closing
            const cells = document.getElementById('templatePreviewTableBody')?.querySelectorAll('td');
            if (cells) {
                cells.forEach(cell => {
                    cell.removeAttribute('contenteditable');
                    cell.style.cursor = 'auto';
                    cell.style.backgroundColor = '';
                    cell.style.userSelect = '';
                });
            }

            document.getElementById('templatePreviewModal').classList.add('hidden');
        }

        window.closeTemplatePreview = function() {
            // Skip unsaved-changes prompt if in read-only dean view
            const ownerIdField = document.getElementById('currentDocumentOwnerId');
            const isReadOnly = ownerIdField && ownerIdField.value;

            // Check if we're in edit mode with unsaved changes
            const submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
            if (!isReadOnly && submissionIdField && submissionIdField.value) {
                const tableBody = document.getElementById('templatePreviewTableBody');
                if (tableBody && tableBody.innerHTML.trim() !== '') {
                    openConfirmationModal(
                        'Unsaved Changes',
                        'You have unsaved changes. Are you sure you want to close without saving?',
                        'Any edits you made will be lost.',
                        'danger',
                        'Close Without Saving',
                        function() {
                            doCloseTemplatePreview();
                        }
                    );
                    return;
                }
            }
            doCloseTemplatePreview();
        }
        
        window.loadTemplate = function(templateId) {
            fetch(`{{ url('faculty/ipcr/templates') }}/${templateId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('==== RAW API RESPONSE ====');
                    console.log('Full data:', data);
                    console.log('Template object:', data.template);
                    console.log('Content field:', data.template.content);
                    console.log('Content type:', typeof data.template.content);
                    
                    if (data.success) {
                        const template = data.template;
                        let content;
                        
                        // Parse content if it's a string, otherwise use as is
                        if (typeof template.content === 'string') {
                            console.log('Parsing content as JSON string');
                            content = JSON.parse(template.content);
                        } else {
                            console.log('Content is already an object');
                            content = template.content;
                        }
                        
                        console.log('==== PARSED CONTENT ====');
                        console.log('Full content:', content);
                        console.log('Strategic objectives:', content.strategic_objectives);
                        console.log('Headers array:', content.headers);
                        console.log('Number of headers:', content.headers ? content.headers.length : 0);
                        
                        // Ensure headers is an array
                        if (!Array.isArray(content.headers)) {
                            console.warn('Headers is not an array, converting to array');
                            content.headers = content.headers ? Object.values(content.headers) : [];
                        }
                        
                        // Ensure strategic_objectives is an array
                        if (!Array.isArray(content.strategic_objectives)) {
                            console.warn('Strategic objectives is not an array, converting to array');
                            content.strategic_objectives = content.strategic_objectives ? Object.values(content.strategic_objectives) : [];
                        }
                        
                        if (content.headers) {
                            content.headers.forEach((h, i) => {
                                console.log(`Header ${i}:`, h);
                                console.log(`  - SO: ${h.so}`);
                                console.log(`  - Rows:`, h.rows);
                                console.log(`  - Row count: ${h.rows ? h.rows.length : 0}`);
                            });
                        }
                        
                        // Set edit mode
                        isEditMode = true;
                        currentTemplateId = templateId;
                        
                        // Update button text
                        document.getElementById('saveButton').innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';
                        
                        // Set template name
                        document.getElementById('currentTemplateName').value = template.title;
                        
                        // Show the form
                        document.getElementById('createIpcrButtonArea').style.display = 'none';
                        document.getElementById('ipcrFormContainer').style.display = 'block';
                        
                        // Clear existing form
                        document.getElementById('strategicObjectivesContainer').innerHTML = '';
                        document.getElementById('headersContainer').innerHTML = '';
                        headerCount = 0;
                        currentHeaderForRows = null;
                        
                        // Load strategic objectives
                        if (content.strategic_objectives && Array.isArray(content.strategic_objectives) && content.strategic_objectives.length > 0) {
                            content.strategic_objectives.forEach(obj => {
                                const div = document.createElement('div');
                                div.className = 'mb-4';
                                const soContent = obj || '';
                                div.innerHTML = `<div class="editable-field" contenteditable="true" data-placeholder="Strategic objectives...">${soContent}</div>`;
                                document.getElementById('strategicObjectivesContainer').appendChild(div);
                                
                                // Setup formatting for this field
                                const field = div.querySelector('.editable-field');
                                if (field) setupFormatField(field);
                            });
                        } else {
                            const div = document.createElement('div');
                            div.className = 'mb-4';
                            div.innerHTML = `<div class="editable-field" contenteditable="true" data-placeholder="Strategic objectives..."></div>`;
                            document.getElementById('strategicObjectivesContainer').appendChild(div);
                            
                            // Setup formatting for this field
                            const field = div.querySelector('.editable-field');
                            if (field) setupFormatField(field);
                        }
                        
                        // Load headers and rows
                        if (content.headers && Array.isArray(content.headers) && content.headers.length > 0) {
                            console.log('Loading headers...', content.headers);
                            
                            for (let headerIndex = 0; headerIndex < content.headers.length; headerIndex++) {
                                const headerData = content.headers[headerIndex];
                                console.log(`Loading header ${headerIndex + 1}:`, headerData);
                                
                                headerCount++;
                                const currentHeaderId = headerCount;
                                const headersContainer = document.getElementById('headersContainer');
                                
                                const soValue = (headerData.so || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                
                                // Create header element
                                const headerDiv = document.createElement('div');
                                headerDiv.className = 'mb-2';
                                headerDiv.id = `header-${currentHeaderId}`;
                                headerDiv.dataset.headerId = currentHeaderId;
                                headerDiv.dataset.type = 'header';
                                
                                const soFieldId = `so-field-${currentHeaderId}`;
                                const soContent = headerData.so || '';
                                
                                headerDiv.innerHTML = `
                                    <div class="flex items-start gap-2">
                                        <div id="${soFieldId}" class="editable-field flex-1" contenteditable="true" data-placeholder="SO I">${soContent}</div>
                                        <div class="flex flex-col gap-0.5">
                                            <button type="button" onclick="moveHeaderUp(${currentHeaderId})" class="bg-blue-50 text-blue-600 border border-blue-200 w-5 h-5 flex items-center justify-center hover:bg-blue-100 hover:border-blue-300 transition-all" title="Move Up">
                                                <i class="fas fa-chevron-up" style="font-size: 8px;"></i>
                                            </button>
                                            <button type="button" onclick="moveHeaderDown(${currentHeaderId})" class="bg-blue-50 text-blue-600 border border-blue-200 w-5 h-5 flex items-center justify-center hover:bg-blue-100 hover:border-blue-300 transition-all" title="Move Down">
                                                <i class="fas fa-chevron-down" style="font-size: 8px;"></i>
                                            </button>
                                        </div>
                                        <button type="button" onclick="removeHeader(${currentHeaderId})" class="bg-red-50 text-red-600 border border-red-200 w-5 h-5 flex items-center justify-center hover:bg-red-100 hover:border-red-300 flex-shrink-0 transition-all">
                                            <i class="fas fa-times" style="font-size: 8px;"></i>
                                        </button>
                                    </div>
                                `;
                                
                                headersContainer.appendChild(headerDiv);
                                
                                // Setup formatting for the loaded field
                                const soField = document.getElementById(soFieldId);
                                if (soField) setupFormatField(soField);
                                
                                console.log(`Header ${currentHeaderId} appended to DOM`);
                                
                                // Create rows for this header
                                if (headerData.rows && headerData.rows.length > 0) {
                                    for (let rowIndex = 0; rowIndex < headerData.rows.length; rowIndex++) {
                                        const rowData = headerData.rows[rowIndex];
                                        const rowId = `row-${currentHeaderId}-${rowIndex}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
                                        const mroFieldId = `mro-${rowId}`;
                                        const successFieldId = `success-${rowId}`;
                                        
                                        const mroContent = rowData.mro || '';
                                        const successContent = rowData.success_indicator || '';
                                        
                                        const rowDiv = document.createElement('div');
                                        rowDiv.className = 'mb-2';
                                        rowDiv.id = rowId;
                                        rowDiv.dataset.type = 'row';
                                        rowDiv.dataset.headerId = currentHeaderId;
                                        
                                        rowDiv.innerHTML = `
                                            <div class="flex items-start gap-2">
                                                <div class="flex-1 grid grid-cols-2 gap-4">
                                                    <div id="${mroFieldId}" class="editable-field" contenteditable="true" data-placeholder="Major Responsibility & Output">${mroContent}</div>
                                                    <div id="${successFieldId}" class="editable-field" contenteditable="true" data-placeholder="Success Indicator">${successContent}</div>
                                                </div>
                                                <div class="flex flex-col gap-0.5">
                                                    <button type="button" onclick="moveRowUp('${rowId}')" class="bg-indigo-50 text-indigo-600 border border-indigo-200 w-5 h-5 flex items-center justify-center hover:bg-indigo-100 hover:border-indigo-300 transition-all" title="Move Up">
                                                        <i class="fas fa-chevron-up" style="font-size: 8px;"></i>
                                                    </button>
                                                    <button type="button" onclick="moveRowDown('${rowId}')" class="bg-indigo-50 text-indigo-600 border border-indigo-200 w-5 h-5 flex items-center justify-center hover:bg-indigo-100 hover:border-indigo-300 transition-all" title="Move Down">
                                                        <i class="fas fa-chevron-down" style="font-size: 8px;"></i>
                                                    </button>
                                                </div>
                                                <button type="button" onclick="removeRow('${rowId}')" class="bg-red-50 text-red-600 border border-red-200 w-5 h-5 flex items-center justify-center hover:bg-red-100 hover:border-red-300 flex-shrink-0 transition-all">
                                                    <i class="fas fa-times" style="font-size: 8px;"></i>
                                                </button>
                                            </div>
                                        `;
                                        
                                        headersContainer.appendChild(rowDiv);
                                        
                                        // Setup formatting for the loaded fields
                                        const mroField = document.getElementById(mroFieldId);
                                        const successField = document.getElementById(successFieldId);
                                        if (mroField) setupFormatField(mroField);
                                        if (successField) setupFormatField(successField);
                                        
                                        headersContainer.appendChild(rowDiv);
                                        console.log(`Row ${rowIndex + 1} for header ${currentHeaderId} appended to DOM`);
                                    }
                                    console.log(`Built ${headerData.rows.length} rows for header ${currentHeaderId}`);
                                }
                                
                                currentHeaderForRows = currentHeaderId;
                            }
                            
                            console.log('All headers loaded. Total headers:', headerCount);
                        }
                    } else {
                        showAlertModal('error', 'Load Failed', data.message || 'Failed to load template');
                    }
                })
                .catch(error => {
                    console.error('==== ERROR DETAILS ====');
                    console.error('Error type:', error.name);
                    console.error('Error message:', error.message);
                    console.error('Error stack:', error.stack);
                    console.error('Full error:', error);
                    showAlertModal('error', 'Error', 'An error occurred while loading the template.');
                });
        }
        
        window.deleteTemplate = function(templateId) {
            openConfirmationModal(
                'Delete Template',
                'Are you sure you want to delete this template?',
                'This action cannot be undone.',
                'danger',
                'Delete',
                function() {
                    fetch(`{{ url('faculty/ipcr/templates') }}/${templateId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlertModal('success', 'Deleted', 'Template deleted successfully!', function() {
                                location.reload();
                            });
                        } else {
                            showAlertModal('error', 'Delete Failed', data.message || 'Failed to delete template');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while deleting the template.');
                    });
                }
            );
        }
        
        function setActiveTemplate(templateId) {
            fetch(`{{ url('faculty/ipcr/templates') }}/${templateId}/set-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Template set as active');
                    // Reload to update dashboard counts
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to set template as active');
                    // Reload to reset radio button state
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while setting the active template.');
                location.reload();
            });
        }

        function setActiveSubmission(submissionId) {
            fetch(`{{ url('faculty/ipcr/submissions') }}/${submissionId}/set-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Submission set as active');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to set submission as active');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while setting the active submission.');
                location.reload();
            });
        }

        function useTemplateAsDraft() {
            const templateId = document.getElementById('currentPreviewTemplateId')?.value;
            const docType = document.getElementById('currentSubmissionType')?.value;
            
            if (!templateId) {
                showAlertModal('error', 'Error', 'No template selected');
                return;
            }
            
            // Determine the document type
            const docTypeToUse = docType === 'opcr' ? 'opcr' : 'ipcr';
            const endpoint = docTypeToUse === 'opcr' 
                ? `/faculty/opcr/templates/${templateId}/save-copy`
                : `{{ url('faculty/ipcr/templates') }}/${templateId}/save-copy`;
            
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const docLabel = docTypeToUse.toUpperCase();
                    showAlertModal('success', 'Success', `Template has been copied to ${docLabel} drafts`, function() {
                        // Close preview modal
                        closeTemplatePreview();
                        // Refresh saved copies list
                        if (docTypeToUse === 'opcr') {
                            renderOpcrSavedCopies();
                        } else {
                            renderSavedCopies();
                        }
                    });
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to use template as draft');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while copying the template.');
            });
        }

        function saveCopyFromTemplate(templateId) {
            fetch(`{{ url('faculty/ipcr/templates') }}/${templateId}/save-copy`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertModal('success', 'Success', 'Template saved to Saved Copy successfully');
                    // Close preview modal
                    closeTemplatePreview();
                    // Refresh saved copies list
                    renderSavedCopies();
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to save copy');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while saving the copy.');
            });
        }

        window.saveCopyFromPreview = function() {
            console.log('saveCopyFromPreview called');
            
            // Check if we're updating a submission
            const submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
            console.log('Submission ID field:', submissionIdField);
            console.log('Submission ID value:', submissionIdField ? submissionIdField.value : 'NOT FOUND');
            
            if (submissionIdField && submissionIdField.value) {
                console.log('Calling updateSubmissionData with ID:', submissionIdField.value);
                updateSubmissionData(submissionIdField.value);
                return;
            }

            const templateIdField = document.getElementById('currentPreviewTemplateId');
            if (!templateIdField || !templateIdField.value) {
                showAlertModal('error', 'Error', 'No template selected');
                return;
            }
            saveCopyFromTemplate(templateIdField.value);
        }

        function updateSubmissionData(submissionId) {
            console.log('=== updateSubmissionData called ===');
            console.log('Submission ID:', submissionId);
            
            const tableBody = document.getElementById('templatePreviewTableBody');
            
            if (!tableBody) {
                showAlertModal('error', 'Error', 'Table body not found');
                return;
            }
            
            // IMPORTANT: Sync all input/textarea values before capturing HTML
            console.log('Syncing all field values...');
            const allCells = tableBody.querySelectorAll('td');
            let syncCount = 0;
            allCells.forEach(cell => {
                // Sync all textareas - set their textContent to their current value
                const textareas = cell.querySelectorAll('textarea');
                textareas.forEach((textarea) => {
                    const currentValue = textarea.value;
                    textarea.textContent = currentValue;
                    textarea.innerHTML = currentValue;
                    syncCount++;
                    console.log(`Synced textarea (${currentValue.length} chars): "${currentValue.substring(0, 50)}"`);
                });
                
                // Sync all regular inputs (not hidden) - ensure their value attribute is current
                const visibleInputs = cell.querySelectorAll('input:not([type="hidden"])');
                visibleInputs.forEach((input) => {
                    const currentValue = input.value;
                    input.setAttribute('value', currentValue);
                    syncCount++;
                    console.log(`Synced input: "${currentValue}"`);
                });
                
                // Sync hidden inputs with their associated div content
                const hiddenInputs = cell.querySelectorAll('input[type="hidden"]');
                const divs = cell.querySelectorAll('div');
                
                hiddenInputs.forEach((input, idx) => {
                    const div = divs[idx];
                    if (div && !div.querySelector('textarea') && !div.querySelector('input')) {
                        const oldValue = input.value;
                        const newValue = div.textContent.trim();
                        if (oldValue !== newValue) {
                            input.value = newValue;
                            input.setAttribute('value', newValue);
                            div.textContent = newValue;
                            syncCount++;
                            console.log(`Synced hidden input: "${oldValue}" -> "${newValue}"`);
                        }
                    }
                });
            });
            console.log(`Total fields synced: ${syncCount}`);
            
            // Clone the table body to work with a copy
            const clonedTableBody = tableBody.cloneNode(true);
            
            // Remove contenteditable attributes and inline styles that were added for editing
            const allClonedCells = clonedTableBody.querySelectorAll('td');
            allClonedCells.forEach(cell => {
                cell.removeAttribute('contenteditable');
                // Remove the blue background we added
                if (cell.style.backgroundColor === 'rgb(240, 249, 255)') {
                    cell.style.backgroundColor = '';
                }
                if (cell.style.cursor === 'default') {
                    cell.style.cursor = '';
                }
                if (cell.style.userSelect === 'none') {
                    cell.style.userSelect = '';
                }
                // Clean up empty style attribute
                if (cell.getAttribute('style') === '') {
                    cell.removeAttribute('style');
                }
            });
            
            // Also remove editing styles from textareas and inputs in cloned table
            const clonedTextareas = clonedTableBody.querySelectorAll('textarea');
            const clonedInputs = clonedTableBody.querySelectorAll('input');
            
            clonedTextareas.forEach(textarea => {
                textarea.removeAttribute('contenteditable');
                if (textarea.style.pointerEvents === 'auto') textarea.style.pointerEvents = '';
                if (textarea.style.backgroundColor === 'white') textarea.style.backgroundColor = '';
                if (textarea.style.cursor === 'text') textarea.style.cursor = '';
                if (textarea.style.userSelect === 'text') textarea.style.userSelect = '';
                if (textarea.getAttribute('style') === '') textarea.removeAttribute('style');
            });
            
            clonedInputs.forEach(input => {
                input.removeAttribute('contenteditable');
                if (input.style.pointerEvents === 'auto') input.style.pointerEvents = '';
                if (input.style.backgroundColor === 'white') input.style.backgroundColor = '';
                if (input.style.cursor === 'text') input.style.cursor = '';
                if (input.style.userSelect === 'text') input.style.userSelect = '';
                if (input.getAttribute('style') === '') input.removeAttribute('style');
            });
            
            let tableBodyHtml = clonedTableBody.innerHTML;

            console.log('Table HTML length:', tableBodyHtml.length);
            console.log('First 500 chars:', tableBodyHtml.substring(0, 500));

            if (!tableBodyHtml || tableBodyHtml.trim() === '') {
                showAlertModal('error', 'Empty Table', 'Cannot update with empty table content.');
                return;
            }

            // Use FormData instead of JSON to avoid JSON encoding issues with HTML
            const formData = new FormData();
            formData.append('table_body_html', tableBodyHtml);
            formData.append('_method', 'PUT');

            // Include noted/approved by from templatePreview inputs
            const tpNotedBy = document.getElementById('templatePreviewNotedBy');
            if (tpNotedBy) formData.append('noted_by', tpNotedBy.value);
            const tpApprovedBy = document.getElementById('templatePreviewApprovedBy');
            if (tpApprovedBy) formData.append('approved_by', tpApprovedBy.value);

            // Determine which endpoint to use based on submission type
            const submissionType = document.getElementById('currentSubmissionType')?.value || 'ipcr';
            const baseUrl = submissionType === 'opcr' ? '/faculty/opcr/submissions' : '{{ url('faculty/ipcr/submissions') }}';
            const typeLabel = submissionType === 'opcr' ? 'OPCR' : 'IPCR';

            console.log('FormData created, attempting fetch...');
            console.log('Submission type:', submissionType);
            console.log('Fetch URL:', baseUrl + '/' + submissionId);

            // Show loading state
            const updateBtn = document.getElementById('updateSubmissionBtn');
            const originalText = updateBtn.textContent;
            updateBtn.disabled = true;
            updateBtn.textContent = 'Updating...';

            fetch(baseUrl + '/' + submissionId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                console.log('Response received, status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response error text:', text);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response JSON:', data);
                if (data.success) {
                    // Clear the submission ID field
                    const submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
                    if (submissionIdField) submissionIdField.value = '';

                    // Close the modal
                    closeTemplatePreview();
                    
                    showAlertModal('success', 'Updated Successfully', 'The submitted ' + typeLabel + ' has been updated successfully.', function() {
                        // Reload page to reflect changes
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    });
                } else {
                    showAlertModal('error', 'Update Failed', data.message || 'Failed to update submission');
                    updateBtn.disabled = false;
                    updateBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showAlertModal('error', 'Error', error.message || 'An error occurred while updating the submission.');
                updateBtn.disabled = false;
                updateBtn.textContent = originalText;
            });
        }
        
        window.deleteSubmission = function(submissionId) {
            openConfirmationModal(
                'Delete Submission',
                'Are you sure you want to delete this submission?',
                'This action cannot be undone.',
                'danger',
                'Delete',
                function() {
                    const url = `/faculty/ipcr/submissions/${submissionId}`;
                    
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        return response.json().catch(() => ({ success: false, message: 'Invalid response' }));
                    })
                    .then(data => {
                        if (data.success) {
                            showAlertModal('success', 'Deleted Successfully', 'The submission has been deleted successfully.', function() {
                                setTimeout(() => { location.reload(); }, 500);
                            });
                        } else {
                            showAlertModal('error', 'Delete Failed', data.message || 'Failed to delete submission');
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        showAlertModal('error', 'Error', error.message || 'An error occurred while deleting the submission.');
                    });
                }
            );
        }
        
        window.unsubmitSubmission = function(submissionId) {
            openConfirmationModal(
                'Unsubmit IPCR',
                'Are you sure you want to unsubmit this IPCR?',
                'It will be reverted to draft status.',
                'warning',
                'Unsubmit',
                function() {
                    fetch(`/faculty/ipcr/submissions/${submissionId}/unsubmit`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json().catch(() => ({ success: false, message: 'Invalid response' })))
                    .then(data => {
                        if (data.success) {
                            showAlertModal('success', 'Unsubmitted', 'The IPCR has been reverted to draft status.', function() {
                                setTimeout(() => { location.reload(); }, 500);
                            });
                        } else {
                            showAlertModal('error', 'Failed', data.message || 'Failed to unsubmit');
                        }
                    })
                    .catch(error => {
                        console.error('Unsubmit error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while unsubmitting.');
                    });
                }
            );
        }

        window.unsubmitOpcrSubmission = function(submissionId) {
            openConfirmationModal(
                'Unsubmit OPCR',
                'Are you sure you want to unsubmit this OPCR?',
                'It will be reverted to draft status.',
                'warning',
                'Unsubmit',
                function() {
                    fetch(`/faculty/opcr/submissions/${submissionId}/unsubmit`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json().catch(() => ({ success: false, message: 'Invalid response' })))
                    .then(data => {
                        if (data.success) {
                            showAlertModal('success', 'Unsubmitted', 'The OPCR has been reverted to draft status.', function() {
                                setTimeout(() => { location.reload(); }, 500);
                            });
                        } else {
                            showAlertModal('error', 'Failed', data.message || 'Failed to unsubmit');
                        }
                    })
                    .catch(error => {
                        console.error('Unsubmit error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while unsubmitting.');
                    });
                }
            );
        }
        
        function openConfirmationModal(title, message, subMessage, type, confirmText, callback) {
            const modal = document.getElementById('confirmationModal');
            const modalHeader = document.getElementById('modalHeader');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalSubMessage = document.getElementById('modalSubMessage');
            const confirmButton = document.getElementById('confirmButton');
            const confirmButtonText = document.getElementById('confirmButtonText');
            
            // Set content
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalSubMessage.textContent = subMessage;
            confirmButtonText.textContent = confirmText;
            
            // Set styling based on type
            if (type === 'danger') {
                modalHeader.className = 'bg-red-50 border-b border-red-200 px-6 py-4 flex items-center gap-3';
                modalHeader.querySelector('div').className = 'bg-red-100 rounded-full w-12 h-12 flex items-center justify-center';
                modalHeader.querySelector('i').className = 'fas fa-exclamation-triangle text-red-600 text-xl';
                confirmButton.className = 'px-4 py-2 rounded-lg font-semibold text-white bg-red-600 hover:bg-red-700 transition flex items-center gap-2 text-sm';
            } else {
                modalHeader.className = 'bg-yellow-50 border-b border-yellow-200 px-6 py-4 flex items-center gap-3';
                modalHeader.querySelector('div').className = 'bg-yellow-100 rounded-full w-12 h-12 flex items-center justify-center';
                modalHeader.querySelector('i').className = 'fas fa-exclamation-triangle text-yellow-600 text-xl';
                confirmButton.className = 'px-4 py-2 rounded-lg font-semibold text-white bg-yellow-600 hover:bg-yellow-700 transition flex items-center gap-2 text-sm';
            }
            
            // Store callback
            pendingAction = callback;
            
            // Show modal
            modal.classList.remove('hidden');
        }
        
        window.closeConfirmationModal = function() {
            document.getElementById('confirmationModal').classList.add('hidden');
            pendingAction = null;
        }
        
        window.confirmAction = function() {
            if (pendingAction) {
                pendingAction();
            }
            closeConfirmationModal();
        }
        
        // Close modal on outside click
        document.getElementById('confirmationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmationModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeConfirmationModal();
                closeAlertModal();
            }
        });
        
        // Move header up
        window.moveHeaderUp = function(headerId) {
            const header = document.getElementById(`header-${headerId}`);
            if (header && header.previousElementSibling) {
                header.parentNode.insertBefore(header, header.previousElementSibling);
            }
        }
        
        // Move header down
        window.moveHeaderDown = function(headerId) {
            const header = document.getElementById(`header-${headerId}`);
            if (header && header.nextElementSibling) {
                header.parentNode.insertBefore(header.nextElementSibling, header);
            }
        }
        
        // Move row up
        window.moveRowUp = function(rowId) {
            const row = document.getElementById(rowId);
            if (row && row.previousElementSibling) {
                row.parentNode.insertBefore(row, row.previousElementSibling);
            }
        }
        
        // Move row down
        window.moveRowDown = function(rowId) {
            const row = document.getElementById(rowId);
            if (row && row.nextElementSibling) {
                row.parentNode.insertBefore(row.nextElementSibling, row);
            }
        }
        
        // View template (placeholder for future implementation)
        window.viewTemplate = function(templateId) {
            fetch(`{{ url('faculty/ipcr/templates') }}/${templateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const template = data.template;
                        
                        // Check if template has table_body_html
                        if (template.table_body_html) {
                            const tableBody = document.getElementById('templatePreviewTableBody');
                            if (tableBody) {
                                tableBody.innerHTML = template.table_body_html;
                            }

                            // Load title
                            const titleElement = document.getElementById('templatePreviewTitle');
                            if (titleElement && template.title) {
                                titleElement.textContent = template.title;
                            }

                            // Load school year and semester
                            if (template.school_year) {
                                const displaySchoolYear = document.getElementById('templatePreviewSchoolYear');
                                if (displaySchoolYear) displaySchoolYear.textContent = template.school_year;
                            }
                            if (template.semester) {
                                const displaySemester = document.getElementById('templatePreviewSemester');
                                if (displaySemester) displaySemester.textContent = template.semester;
                            }

                            // Store template ID for save copy functionality
                            const templateIdField = document.getElementById('currentPreviewTemplateId');
                            if (templateIdField) {
                                templateIdField.value = templateId;
                            }

                            // Clear submission fields so doc context resolves to template
                            const submIdField = document.getElementById('currentSubmissionIdToUpdate');
                            if (submIdField) submIdField.value = '';
                            const submTypeField = document.getElementById('currentSubmissionType');
                            if (submTypeField) submTypeField.value = 'ipcr';

                            // Unhide all columns in the preview modal
                            const previewModal = document.getElementById('templatePreviewModal');
                            if (previewModal) {
                                const headers = previewModal.querySelectorAll('thead th.hidden');
                                headers.forEach(header => header.classList.remove('hidden'));
                                const cells = previewModal.querySelectorAll('td.hidden');
                                cells.forEach(cell => { cell.classList.remove('hidden'); cell.style.display = ''; });
                            }

                            // Show Edit IPCR button, hide Update Submission button
                            const saveCopyBtn = document.getElementById('saveCopyBtn');
                            if (saveCopyBtn) saveCopyBtn.style.display = '';
                            const updateBtn = document.getElementById('updateSubmissionBtn');
                            if (updateBtn) updateBtn.classList.add('hidden');

                            document.getElementById('templatePreviewModal').classList.remove('hidden');
                            attachSoDocumentClickHandlers();
                            // Label QETA inputs and set up auto-computation
                            labelQetaInputs(document.getElementById('templatePreviewTableBody'));
                        } else {
                            showAlertModal('info', 'Legacy Template', 'This template was created with the old format. Please use the Edit button to modify it.');
                        }
                    } else {
                        showAlertModal('error', 'Not Found', 'Template could not be found.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlertModal('error', 'Error', 'An error occurred while loading the template.');
                });
        }
        
        window.viewSubmission = function(submissionId) {
            console.log('Viewing/Editing submission:', submissionId);
            // Add cache-busting parameter
            fetch(`{{ url('faculty/ipcr/submissions') }}/${submissionId}?t=${Date.now()}`, {
                headers: {
                    'Cache-Control': 'no-cache'
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log('View submission response:', data);
                    if (data.success) {
                        const submission = data.submission;
                        
                        // Store submission ID and type for update functionality
                        const submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
                        if (submissionIdField) {
                            submissionIdField.value = submissionId;
                            console.log('Submission ID stored in field:', submissionIdField.value);
                        }
                        const submissionTypeField = document.getElementById('currentSubmissionType');
                        if (submissionTypeField) submissionTypeField.value = 'ipcr';
                        
                        // Load table body
                        const tableBody = document.getElementById('templatePreviewTableBody');
                        if (tableBody && submission.table_body_html) {
                            tableBody.innerHTML = submission.table_body_html;
                            
                            // Make all table cells NON-editable, but enable inputs/textareas inside
                            const cells = tableBody.querySelectorAll('td');
                            console.log('Total cells found:', cells.length);
                            
                            cells.forEach((cell) => {
                                // Make cells non-editable
                                cell.setAttribute('contenteditable', 'false');
                                cell.style.cursor = 'default';
                                cell.style.userSelect = 'none';
                                cell.style.backgroundColor = '#f0f9ff';
                                cell.classList.add('hover:bg-blue-50');
                                
                                // Make inputs and textareas inside cells fully editable
                                const inputs = cell.querySelectorAll('input');
                                const textareas = cell.querySelectorAll('textarea');
                                
                                inputs.forEach(input => {
                                    input.setAttribute('contenteditable', 'true');
                                    input.removeAttribute('readonly');
                                    input.removeAttribute('disabled');
                                    input.style.pointerEvents = 'auto';
                                    input.style.backgroundColor = 'white';
                                    input.style.cursor = 'text';
                                    input.style.userSelect = 'text';
                                });
                                
                                textareas.forEach(textarea => {
                                    textarea.setAttribute('contenteditable', 'true');
                                    textarea.removeAttribute('readonly');
                                    textarea.removeAttribute('disabled');
                                    textarea.style.pointerEvents = 'auto';
                                    textarea.style.backgroundColor = 'white';
                                    textarea.style.cursor = 'text';
                                    textarea.style.userSelect = 'text';
                                });
                            });
                        }

                        // Label QETA inputs and set up auto-computation
                        labelQetaInputs(tableBody);

                        // Load title
                        const titleElement = document.getElementById('templatePreviewTitle');
                        if (titleElement && submission.title) {
                            titleElement.textContent = submission.title;
                        }

                        // Load school year and semester
                        if (submission.school_year) {
                            const displaySchoolYear = document.getElementById('templatePreviewSchoolYear');
                            if (displaySchoolYear) displaySchoolYear.textContent = submission.school_year;
                        }
                        if (submission.semester) {
                            const displaySemester = document.getElementById('templatePreviewSemester');
                            if (displaySemester) displaySemester.textContent = submission.semester;
                        }

                        // Load noted/approved by
                        const tpNotedBy = document.getElementById('templatePreviewNotedBy');
                        if (tpNotedBy) tpNotedBy.value = submission.noted_by || '';
                        const tpApprovedBy = document.getElementById('templatePreviewApprovedBy');
                        if (tpApprovedBy) tpApprovedBy.value = submission.approved_by || '';

                        // Unhide QETA, Remarks, and Ratings columns
                        const previewModal = document.getElementById('templatePreviewModal');
                        if (previewModal) {
                            const headers = previewModal.querySelectorAll('thead th.hidden');
                            headers.forEach(header => header.classList.remove('hidden'));
                            
                            const cells = previewModal.querySelectorAll('td.hidden');
                            cells.forEach(cell => cell.classList.remove('hidden'));
                        }

                        // Hide Edit IPCR button and show Update Submission button
                        const saveCopyBtn = document.getElementById('saveCopyBtn');
                        if (saveCopyBtn) {
                            saveCopyBtn.style.display = 'none';
                            saveCopyBtn.classList.add('hidden');
                        }
                        
                        const updateBtn = document.getElementById('updateSubmissionBtn');
                        if (updateBtn) {
                            updateBtn.classList.remove('hidden');
                            updateBtn.style.display = 'flex';
                            console.log('Update button shown');
                        }

                        document.getElementById('templatePreviewModal').classList.remove('hidden');
                        attachSoDocumentClickHandlers();
                    } else {
                        showAlertModal('error', 'Not Found', 'Submission could not be found.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlertModal('error', 'Error', 'An error occurred while loading the submission.');
                });
        }

        function updateSubmission(submissionId) {
            console.log('=== updateSubmission (Edit) called with ID:', submissionId, '===');
            
            fetch(`{{ url('faculty/ipcr/submissions') }}/${submissionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Submission data loaded:', data.submission);
                        const submission = data.submission;
                        
                        // Store submission ID for update functionality
                        const submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
                        if (submissionIdField) {
                            submissionIdField.value = submissionId;
                            console.log('Submission ID stored in field:', submissionIdField.value);
                        }

                        // Load table body
                        const tableBody = document.getElementById('templatePreviewTableBody');
                        if (tableBody && submission.table_body_html) {
                            tableBody.innerHTML = submission.table_body_html;
                            console.log('Table loaded, HTML length:', tableBody.innerHTML.length);
                            
                            // Make all table cells editable
                            const cells = tableBody.querySelectorAll('td');
                            console.log('Total cells found:', cells.length);
                            
                            cells.forEach((cell, index) => {
                                cell.setAttribute('contenteditable', 'true');
                                cell.style.cursor = 'text';
                                cell.style.backgroundColor = '#f0f9ff';
                                cell.classList.add('hover:bg-blue-50');
                                
                                if (index < 3) {
                                    console.log(`Cell ${index} set to editable:`, cell.innerHTML.substring(0, 50));
                                }
                            });
                        }

                        // Load title with (Edit) suffix
                        const titleElement = document.getElementById('templatePreviewTitle');
                        if (titleElement && submission.title) {
                            titleElement.textContent = submission.title + ' (Edit)';
                        }

                        // Load school year and semester
                        if (submission.school_year) {
                            const displaySchoolYear = document.getElementById('templatePreviewSchoolYear');
                            if (displaySchoolYear) displaySchoolYear.textContent = submission.school_year;
                        }
                        if (submission.semester) {
                            const displaySemester = document.getElementById('templatePreviewSemester');
                            if (displaySemester) displaySemester.textContent = submission.semester;
                        }

                        // Unhide QETA, Remarks, and Ratings columns
                        const previewModal = document.getElementById('templatePreviewModal');
                        if (previewModal) {
                            const headers = previewModal.querySelectorAll('thead th.hidden');
                            headers.forEach(header => header.classList.remove('hidden'));
                            
                            const cells = previewModal.querySelectorAll('td.hidden');
                            cells.forEach(cell => cell.classList.remove('hidden'));
                        }

                        // Hide save copy button and show update button
                        const saveCopyBtn = document.getElementById('saveCopyBtn');
                        if (saveCopyBtn) {
                            saveCopyBtn.style.display = 'none';
                            saveCopyBtn.classList.add('hidden');
                        }
                        
                        const updateBtn = document.getElementById('updateSubmissionBtn');
                        if (updateBtn) {
                            updateBtn.classList.remove('hidden');
                            updateBtn.style.display = 'flex';
                            console.log('Update button shown, display:', window.getComputedStyle(updateBtn).display);
                        }

                        // Set submission type for document context
                        const submTypeField2 = document.getElementById('currentSubmissionType');
                        if (submTypeField2) submTypeField2.value = 'ipcr';

                        document.getElementById('templatePreviewModal').classList.remove('hidden');
                        attachSoDocumentClickHandlers();
                    } else {
                        showAlertModal('error', 'Not Found', 'Submission could not be found.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlertModal('error', 'Error', 'An error occurred while loading the submission.');
                });
        }
        
        // Alert Modal Functions
        let alertModalCallback = null;
        let alertModalTimer = null;
        
        function showAlertModal(type, title, message, callback) {
            const modal = document.getElementById('alertModal');
            const modalHeader = document.getElementById('alertModalHeader');
            const iconContainer = document.getElementById('alertModalIconContainer');
            const icon = document.getElementById('alertModalIcon');
            const modalTitle = document.getElementById('alertModalTitle');
            const modalMessage = document.getElementById('alertModalMessage');
            
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            alertModalCallback = callback || null;
            
            // Clear any existing timer
            if (alertModalTimer) {
                clearTimeout(alertModalTimer);
                alertModalTimer = null;
            }
            
            // Set styling based on type
            if (type === 'success') {
                modalHeader.className = 'bg-green-50 border-b border-green-200 px-6 py-4 flex items-center gap-3';
                iconContainer.className = 'bg-green-100 rounded-full p-3';
                icon.className = 'fas fa-check-circle text-green-600 text-xl';
            } else if (type === 'error') {
                modalHeader.className = 'bg-red-50 border-b border-red-200 px-6 py-4 flex items-center gap-3';
                iconContainer.className = 'bg-red-100 rounded-full p-3';
                icon.className = 'fas fa-exclamation-circle text-red-600 text-xl';
            } else if (type === 'warning') {
                modalHeader.className = 'bg-yellow-50 border-b border-yellow-200 px-6 py-4 flex items-center gap-3';
                iconContainer.className = 'bg-yellow-100 rounded-full p-3';
                icon.className = 'fas fa-exclamation-triangle text-yellow-600 text-xl';
            } else {
                modalHeader.className = 'bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center gap-3';
                iconContainer.className = 'bg-blue-100 rounded-full p-3';
                icon.className = 'fas fa-info-circle text-blue-600 text-xl';
            }
            
            modal.classList.remove('hidden');
            
            // Auto-dismiss after 5 seconds
            alertModalTimer = setTimeout(() => {
                closeAlertModal();
            }, 5000);
        }
        
        window.closeAlertModal = function() {
            if (alertModalTimer) {
                clearTimeout(alertModalTimer);
                alertModalTimer = null;
            }
            document.getElementById('alertModal').classList.add('hidden');
            if (alertModalCallback) {
                alertModalCallback();
                alertModalCallback = null;
            }
        }
        
        // Close alert modal on outside click
        document.getElementById('alertModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAlertModal();
            }
        });

        // =====================================================
        // OPCR FUNCTIONS (completely independent from IPCR)
        // =====================================================
        let opcrSoHeaderCount = 0;
        let currentOpcrSavedCopyId = null;

        function hideOpcrTableColumns() {
            const container = document.getElementById('opcrDocumentContainer');
            if (!container) return;
            const headerRows = container.querySelectorAll('thead tr');
            if (headerRows.length >= 2) {
                const firstRowTh = headerRows[0].querySelectorAll('th');
                for (let i = 2; i < firstRowTh.length; i++) {
                    firstRowTh[i].classList.add('hidden');
                }
                const secondRowTh = headerRows[1].querySelectorAll('th');
                secondRowTh.forEach(th => th.classList.add('hidden'));
            }
            const rows = document.querySelectorAll('#opcrTableBody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length <= 1) return;
                for (let i = 2; i < cells.length; i++) {
                    cells[i].classList.add('hidden');
                }
            });
        }

        window.proceedCreateOpcr = function() {
            const schoolYear = document.getElementById('opcrSchoolYear').value;
            const semester = document.getElementById('opcrSemester').value;

            document.getElementById('opcrDisplaySchoolYear').textContent = schoolYear;
            document.getElementById('opcrDisplaySemester').textContent = semester === 'jan-jun' ? 'January - June' : 'July - December';

            // Transfer noted/approved from create modal to document inputs
            const approvedBy = document.getElementById('opcrCreateApprovedBy')?.value || '';
            const notedBy = document.getElementById('opcrCreateNotedBy')?.value || '';
            const docApproved = document.getElementById('opcrDocApprovedBy');
            const docNoted = document.getElementById('opcrDocNotedBy');
            if (docApproved) docApproved.value = approvedBy;
            if (docNoted) docNoted.value = notedBy;

            const tableBody = document.getElementById('opcrTableBody');
            if (tableBody) tableBody.innerHTML = '';

            const titleInput = document.getElementById('opcrDocumentTitle');
            if (titleInput) titleInput.value = `OPCR for ${ipcrRoleLabel}`;

            currentOpcrSavedCopyId = null;
            opcrSoHeaderCount = 0;

            // Re-hide rating/accomplishment/remarks columns for fresh creation
            hideOpcrTableColumns();

            // Show Save as Template button for fresh creation so users can save as template
            document.getElementById('opcrExportBtn')?.classList.add('hidden');
            document.getElementById('opcrSaveAsTemplateBtn')?.classList.remove('hidden');

            // Check for import file (from create modal input or header button)
            const importFile = document.getElementById('opcrImportFile');
            if (importFile && importFile.files.length > 0) {
                importXlsxFile(importFile.files[0], 'opcr');
                importFile.value = '';
            } else if (window._pendingHeaderImportFile) {
                importXlsxFile(window._pendingHeaderImportFile, 'opcr');
                window._pendingHeaderImportFile = null;
            }

            closeCreateOpcrModal();

            document.getElementById('opcrDocumentContainer').classList.remove('hidden');
        };

        window.closeOpcrDocument = function() {
            document.getElementById('opcrDocumentContainer').classList.add('hidden');
            
            hideOpcrTableColumns();
            currentOpcrSavedCopyId = null;
        }

        window.saveOpcrDocumentTitle = function() {
            const titleInput = document.getElementById('opcrDocumentTitle');
            if (titleInput) {
                showAlertModal('success', 'Title Updated', 'OPCR title has been updated. Remember to save your document to persist changes.');
            }
        }

        window.toggleOpcrSectionHeaderDropdown = function() {
            const dropdown = document.getElementById('opcrSectionHeaderDropdownMenu');
            if (!dropdown) return;
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('opcrSectionHeaderDropdown');
            const dropdownMenu = document.getElementById('opcrSectionHeaderDropdownMenu');
            if (dropdown && dropdownMenu && !dropdown.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        window.addOpcrSectionHeader = function(headerText = '', isEditable = true) {
            const tableBody = document.getElementById('opcrTableBody');
            if (!tableBody) return;

            toggleOpcrSectionHeaderDropdown();

            if (!isEditable) opcrSoHeaderCount = 0;

            let bgColor = 'bg-gray-100';
            if (!isEditable) {
                if (headerText === 'Strategic Objectives') bgColor = 'bg-green-100';
                else if (headerText === 'Core Functions') bgColor = 'bg-purple-100';
                else if (headerText === 'Support Function') bgColor = 'bg-orange-100';
            }

            const newRow = document.createElement('tr');
            newRow.className = bgColor;

            if (isEditable) {
                newRow.innerHTML = `
                    <td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">
                        <input type="text" class="w-full bg-transparent border-0 focus:ring-0 font-semibold text-gray-800" placeholder="Enter custom section header..." value="${headerText}" />
                    </td>
                `;
            } else {
                newRow.innerHTML = `
                    <td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">
                        <div class="font-semibold text-gray-800">${headerText}</div>
                        <input type="hidden" value="${headerText}" />
                    </td>
                `;
                newRow.setAttribute('data-section-type', headerText.toLowerCase().replace(/\s+/g, '-'));
            }

            tableBody.appendChild(newRow);
        }

        window.addOpcrSOHeader = function() {
            const tableBody = document.getElementById('opcrTableBody');
            if (!tableBody) return;

            const allRows = tableBody.querySelectorAll('tr');
            let lastSectionIndex = -1;

            for (let i = allRows.length - 1; i >= 0; i--) {
                const row = allRows[i];
                if (row.classList.contains('bg-green-100') ||
                    row.classList.contains('bg-purple-100') ||
                    row.classList.contains('bg-orange-100')) {
                    lastSectionIndex = i;
                    break;
                }
            }

            let currentSOCount = 0;
            for (let i = lastSectionIndex + 1; i < allRows.length; i++) {
                const row = allRows[i];
                if (row.classList.contains('bg-blue-100')) {
                    const span = row.querySelector('span.font-semibold.text-gray-800');
                    if (span && span.textContent.includes('SO')) currentSOCount++;
                }
            }

            const nextSONumber = currentSOCount + 1;
            const soLabel = convertToRoman(nextSONumber);

            const newRow = document.createElement('tr');
            newRow.className = 'bg-blue-100';
            newRow.innerHTML = `
                <td colspan="8" class="border border-gray-300 px-3 py-2 font-semibold text-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800">SO ${soLabel}:</span>
                        <input type="text" class="flex-1 bg-transparent border-0 focus:ring-0 font-semibold text-gray-800" placeholder="Enter SO description..." value="" />
                    </div>
                </td>
            `;

            tableBody.appendChild(newRow);
        }

        window.addOpcrDataRow = function() {
            const tableBody = document.getElementById('opcrTableBody');
            if (!tableBody) return;

            // Check if columns are currently visible
            const container = document.getElementById('opcrDocumentContainer');
            const remarksHeader = container?.querySelector('thead th:last-child');
            const isExpanded = remarksHeader && !remarksHeader.classList.contains('hidden');
            const hiddenClass = isExpanded ? '' : ' hidden';

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="border border-gray-300 px-2 py-2">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter MFO"></textarea>
                </td>
                <td class="border border-gray-300 px-2 py-2">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Success Indicators"></textarea>
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Actual Accomplishments"></textarea>
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-q" min="1" max="5" step="1" placeholder="-">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-e" min="1" max="5" step="1" placeholder="-">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-t" min="1" max="5" step="1" placeholder="-">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <input type="number" class="w-full h-20 px-2 py-1 text-xs text-center border-0 focus:ring-0 qeta-a" min="1" max="5" step="0.01" placeholder="-" readonly style="background-color: #f3f4f6;" title="Auto-computed average of Q, E, T">
                </td>
                <td class="border border-gray-300 px-2 py-2${hiddenClass}">
                    <textarea class="w-full h-20 px-2 py-1 text-xs resize-none border-0 focus:ring-0" placeholder="Enter Remarks"></textarea>
                </td>
            `;

            tableBody.appendChild(newRow);
        }

        window.removeOpcrLastRow = function() {
            const tableBody = document.getElementById('opcrTableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            if (rows.length > 0) rows[rows.length - 1].remove();
        }

        function extractOpcrSoCounts() {
            const tableBody = document.getElementById('opcrTableBody');
            if (!tableBody) return { strategic_objectives: 0, core_functions: 0, support_functions: 0 };

            let counts = { strategic_objectives: 0, core_functions: 0, support_functions: 0 };
            let currentSection = null;
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const className = row.className;
                if (className.includes('bg-green-100')) currentSection = 'strategic_objectives';
                else if (className.includes('bg-purple-100')) currentSection = 'core_functions';
                else if (className.includes('bg-orange-100')) currentSection = 'support_functions';
                else if (className.includes('bg-gray-100') && row.querySelector('td[colspan]')) currentSection = null;

                if (className.includes('bg-blue-100') && currentSection) counts[currentSection]++;
            });

            return counts;
        }

        window.saveOpcrDocument = function() {
            const schoolYear = document.getElementById('opcrDisplaySchoolYear')?.textContent?.trim();
            const semester = document.getElementById('opcrDisplaySemester')?.textContent?.trim();
            const titleInput = document.getElementById('opcrDocumentTitle');
            const title = titleInput ? titleInput.value.trim() : `OPCR for ${ipcrRoleLabel}`;
            const tableBody = document.getElementById('opcrTableBody');
            const tableBodyHtml = tableBody ? buildTableBodySnapshot(tableBody) : '';
            const notedBy = document.getElementById('opcrDocNotedBy')?.value?.trim() || '';
            const approvedBy = document.getElementById('opcrDocApprovedBy')?.value?.trim() || '';

            const payload = {
                title: title,
                school_year: schoolYear || 'N/A',
                semester: semester || 'N/A',
                table_body_html: tableBodyHtml,
                noted_by: notedBy,
                approved_by: approvedBy
            };

            const url = currentOpcrSavedCopyId
                ? `/faculty/opcr/saved-copies/${currentOpcrSavedCopyId}`
                : '/faculty/opcr/saved-copies';
            const method = currentOpcrSavedCopyId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentOpcrSavedCopyId = data.savedCopy.id;
                    showAlertModal('success', 'Saved', data.message);
                    // Reload page to ensure UI reflects database state
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to save OPCR draft');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', 'An error occurred while saving the OPCR draft.');
            });
        }

        // ── Export OPCR saved copy from document container ──────────
        window.exportOpcrDocument = function() {
            if (!currentOpcrSavedCopyId) {
                showAlertModal('info', 'Save First', 'Please save the document first before exporting.');
                return;
            }
            window.location.href = `/faculty/opcr/saved-copies/${currentOpcrSavedCopyId}/export`;
        };

        window.saveOpcrAsTemplate = function() {
            const schoolYear = document.getElementById('opcrDisplaySchoolYear')?.textContent?.trim();
            const semester = document.getElementById('opcrDisplaySemester')?.textContent?.trim();
            const titleInput = document.getElementById('opcrDocumentTitle');
            const title = titleInput ? titleInput.value.trim() : `OPCR Template`;
            const tableBody = document.getElementById('opcrTableBody');
            const tableBodyHtml = tableBody ? buildTableBodySnapshot(tableBody) : '';
            const notedBy = document.getElementById('opcrDocNotedBy')?.value?.trim() || '';
            const approvedBy = document.getElementById('opcrDocApprovedBy')?.value?.trim() || '';

            if (!tableBodyHtml || tableBodyHtml.trim() === '') {
                showAlertModal('warning', 'Empty Template', 'Please add some content before saving as a template.');
                return;
            }

            const soCounts = extractOpcrSoCounts();

            const payload = {
                title: title,
                school_year: schoolYear || 'N/A',
                semester: semester || 'N/A',
                table_body_html: tableBodyHtml,
                so_count_json: soCounts,
                noted_by: notedBy,
                approved_by: approvedBy
            };

            fetch('/faculty/opcr/templates/from-saved-copy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP ${response.status}: ${text}`); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const message = data.updated
                        ? 'Your existing OPCR template has been updated with the new content.'
                        : 'Your OPCR has been saved as a template.';
                    const heading = data.updated ? 'Template Updated' : 'Template Saved';
                    showAlertModal('success', heading, message);
                } else {
                    showAlertModal('error', 'Error', data.message || 'Failed to save OPCR as template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('error', 'Error', error.message || 'An error occurred while saving the OPCR template.');
            });
        };

        // OPCR Saved Copies Functions
        async function getOpcrSavedCopies() {
            try {
                const response = await fetch('/faculty/opcr/saved-copies', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                return data.savedCopies || [];
            } catch (error) {
                console.error('Error fetching OPCR saved copies:', error);
                return [];
            }
        }

        async function renderOpcrSavedCopies() {
            const list = document.getElementById('opcrSavedCopiesList');
            const empty = document.getElementById('opcrSavedCopiesEmpty');
            if (!list || !empty) return;

            const savedCopies = await getOpcrSavedCopies();
            list.innerHTML = '';

            if (savedCopies.length === 0) {
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');
            savedCopies.forEach(item => {
                const card = document.createElement('div');
                card.className = 'submission-card';
                const savedDate = item.saved_at || item.created_at;
                card.innerHTML = `
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-900">${item.title}</p>
                            <p class="text-xs text-gray-500 mt-1">${item.school_year} • ${item.semester}</p>
                            <p class="text-xs text-gray-500 mt-1">Saved on ${formatSavedDate(savedDate)}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button class="text-blue-600 hover:text-blue-700 font-semibold text-xs sm:text-sm" onclick="editOpcrSavedCopy(${item.id})">View</button>
                            <button class="text-red-600 hover:text-red-700 font-semibold text-xs sm:text-sm" onclick="deleteOpcrSavedCopy(${item.id})">Delete</button>
                        </div>
                    </div>
                `;
                list.appendChild(card);
            });
        }

        window.deleteOpcrSavedCopy = function(id) {
            openConfirmationModal(
                'Delete OPCR Saved Copy',
                'Are you sure you want to delete this saved copy?',
                'This action cannot be undone.',
                'danger',
                'Delete',
                function() {
                    fetch(`/faculty/opcr/saved-copies/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (currentOpcrSavedCopyId === id) {
                                currentOpcrSavedCopyId = null;
                            }
                            showAlertModal('success', 'Deleted', 'OPCR saved copy deleted successfully!', function() {
                                window.location.reload();
                            });
                        } else {
                            showAlertModal('error', 'Error', data.message || 'Failed to delete OPCR saved copy');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while deleting the OPCR saved copy.');
                    });
                }
            );
        };

        function unhideOpcrTableColumns() {
            // Unhide table headers
            const headers = document.querySelectorAll('#opcrDocumentContainer thead th.hidden');
            headers.forEach(header => {
                header.classList.remove('hidden');
                header.style.display = '';
            });
            
            // Unhide table body cells
            const cells = document.querySelectorAll('#opcrTableBody td.hidden');
            cells.forEach(cell => {
                cell.classList.remove('hidden');
                cell.style.display = '';
            });
        }

        window.editOpcrSavedCopy = async function(id) {
            try {
                const response = await fetch(`/faculty/opcr/saved-copies/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                
                if (data.savedCopy) {
                    const copy = data.savedCopy;
                    currentOpcrSavedCopyId = copy.id;
                    
                    document.getElementById('opcrDisplaySchoolYear').textContent = copy.school_year;
                    document.getElementById('opcrDisplaySemester').textContent = copy.semester;
                    document.getElementById('opcrDocumentTitle').value = copy.title;
                    
                    // Load noted/approved by
                    const docNotedBy = document.getElementById('opcrDocNotedBy');
                    if (docNotedBy) docNotedBy.value = copy.noted_by || '';
                    const docApprovedBy = document.getElementById('opcrDocApprovedBy');
                    if (docApprovedBy) docApprovedBy.value = copy.approved_by || '';
                    
                    const tableBody = document.getElementById('opcrTableBody');
                    if (tableBody && copy.table_body_html) {
                        tableBody.innerHTML = copy.table_body_html;
                        
                        // Unhide columns for saved copy
                        unhideOpcrTableColumns();
                        // Label QETA inputs and set up auto-computation
                        labelQetaInputs(tableBody);
                    }
                    
                    // Show Export + Save as Template when editing an existing saved copy
                    document.getElementById('opcrExportBtn')?.classList.remove('hidden');
                    document.getElementById('opcrSaveAsTemplateBtn')?.classList.remove('hidden');

                    document.getElementById('opcrDocumentContainer').classList.remove('hidden');
                } else {
                    showAlertModal('error', 'Not Found', 'OPCR saved copy could not be found.');
                }
            } catch (error) {
                console.error('Error loading OPCR saved copy:', error);
                showAlertModal('error', 'Error', 'An error occurred while loading the OPCR saved copy.');
            }
        };

        // ========================================
        // SO Supporting Documents Functions
        // ========================================
        
        let soDocCurrentContext = { type: '', id: 0, label: '', ownerId: '' };

        /**
         * After rendering table body in preview modal, make SO rows clickable.
         * Called after loading template/submission/saved copy into preview.
         * @param {string} ownerId - Optional user_id of document owner (for dean viewing faculty docs)
         */
        function attachSoDocumentClickHandlers(ownerId) {
            // Track document owner - empty means current auth user, set means viewing someone else's
            const ownerIdField = document.getElementById('currentDocumentOwnerId');
            if (ownerIdField) ownerIdField.value = ownerId || '';

            const tableBody = document.getElementById('templatePreviewTableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr.bg-blue-100');
            rows.forEach(function(row) {
                // Extract the SO label text from the row
                const soSpan = row.querySelector('span.font-semibold.text-gray-800');
                const soInput = row.querySelector('input[type="text"]');
                let soLabel = '';
                let soDescription = '';

                if (soSpan) {
                    soLabel = soSpan.textContent.trim().replace(/:$/, '');
                }
                if (soInput) {
                    soDescription = soInput.value || soInput.getAttribute('value') || '';
                }

                if (!soLabel) return;

                // Remove any old listeners by cloning (must happen BEFORE DOM manipulation below)
                const newRow = row.cloneNode(true);
                newRow.style.cursor = 'pointer';
                newRow.title = 'Click to view/attach supporting documents';
                newRow.addEventListener('click', function(e) {
                    openSoDocumentsModal(soLabel, soDescription);
                });
                row.parentNode.replaceChild(newRow, row);

                // Disable pointer events on inputs inside the SO row so clicks pass through to the TR handler
                // (cells.forEach in viewSubmission sets pointerEvents=auto on all inputs, which blocks TR clicks)
                newRow.querySelectorAll('input, textarea').forEach(function(el) {
                    el.style.pointerEvents = 'none';
                    el.style.cursor = 'pointer';
                });

                // Ensure the badge exists in the newRow and refresh its count
                let badge = newRow.querySelector('.so-doc-badge');
                if (!badge) {
                    const td = newRow.querySelector('td');
                    if (td) {
                        badge = document.createElement('span');
                        badge.className = 'so-doc-badge ml-2 inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full';
                        badge.innerHTML = '<i class="fas fa-paperclip text-[10px]"></i> <span class="so-doc-count">...</span>';
                        badge.style.fontSize = '11px';
                        const innerDiv = td.querySelector('div.flex') || td;
                        innerDiv.appendChild(badge);
                    }
                }
                // Always refresh the count (covers existing badges baked into saved HTML)
                if (badge) {
                    const countEl = badge.querySelector('.so-doc-count');
                    if (countEl) fetchSoDocCount(soLabel, countEl);
                }
            });
        }

        function fetchSoDocCount(soLabel, countElement) {
            const docType = getCurrentDocumentableType();
            const docId = getCurrentDocumentableId();
            if (!docType || !docId) {
                if (countElement) countElement.textContent = '0';
                return;
            }

            const ownerId = document.getElementById('currentDocumentOwnerId')?.value || '';
            const ownerParam = ownerId ? `&owner_id=${ownerId}` : '';
            fetch(`{{ route('faculty.supporting-documents.index') }}?documentable_type=${docType}&documentable_id=${docId}&so_label=${encodeURIComponent(soLabel)}${ownerParam}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && countElement) {
                    countElement.textContent = data.documents.length;
                }
            })
            .catch(() => { if (countElement) countElement.textContent = '0'; });
        }

        function getCurrentDocumentableType() {
            const submissionType = document.getElementById('currentSubmissionType')?.value || 'ipcr';
            const submissionId = document.getElementById('currentSubmissionIdToUpdate')?.value;
            const templateId = document.getElementById('currentPreviewTemplateId')?.value;

            if (submissionId) {
                return submissionType === 'opcr' ? 'opcr_submission' : 'ipcr_submission';
            }
            if (templateId) {
                return submissionType === 'opcr' ? 'opcr_template' : 'ipcr_template';
            }
            return 'ipcr_template';
        }

        function getCurrentDocumentableId() {
            const submissionId = document.getElementById('currentSubmissionIdToUpdate')?.value;
            const templateId = document.getElementById('currentPreviewTemplateId')?.value;

            return submissionId || templateId || 0;
        }

        function openSoDocumentsModal(soLabel, soDescription) {
            soDocCurrentContext.type = getCurrentDocumentableType();
            soDocCurrentContext.id = getCurrentDocumentableId();
            soDocCurrentContext.label = soLabel;
            // Read owner_id from hidden field (set by dean view functions)
            soDocCurrentContext.ownerId = document.getElementById('currentDocumentOwnerId')?.value || '';

            document.getElementById('soDocType').value = soDocCurrentContext.type;
            document.getElementById('soDocId').value = soDocCurrentContext.id;
            document.getElementById('soDocLabel').value = soDocCurrentContext.label;

            document.getElementById('soDocModalTitle').textContent = soLabel;
            document.getElementById('soDocModalDescription').textContent = soDescription || '';

            // Hide upload section when viewing someone else's documents (dean view)
            const uploadSection = document.getElementById('soDocUploadSection');
            if (soDocCurrentContext.ownerId) {
                if (uploadSection) uploadSection.classList.add('hidden');
            } else {
                if (uploadSection) uploadSection.classList.remove('hidden');
                // Reset upload form
                document.getElementById('soDocFileInput').value = '';
                document.getElementById('soDocUploadText').textContent = 'Choose file to upload';
                document.getElementById('soDocUploadBtn').classList.add('hidden');
                document.getElementById('soDocUploadProgress').classList.add('hidden');
            }

            document.getElementById('soDocumentsModal').classList.remove('hidden');

            loadSoDocuments();
        }

        window.closeSoDocumentsModal = function() {
            document.getElementById('soDocumentsModal').classList.add('hidden');
            // Refresh badge counts in the preview (preserve owner_id context)
            const ownerId = document.getElementById('currentDocumentOwnerId')?.value || '';
            attachSoDocumentClickHandlers(ownerId);
        };

        // File input change handler
        document.getElementById('soDocFileInput').addEventListener('change', function() {
            if (this.files.length > 0) {
                const name = this.files[0].name;
                const displayName = name.length > 30 ? name.substring(0, 27) + '...' : name;
                document.getElementById('soDocUploadText').textContent = displayName;
                document.getElementById('soDocUploadBtn').classList.remove('hidden');
            } else {
                document.getElementById('soDocUploadText').textContent = 'Choose file to upload';
                document.getElementById('soDocUploadBtn').classList.add('hidden');
            }
        });

        function loadSoDocuments() {
            const container = document.getElementById('soDocumentsList');
            container.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin text-gray-300 mr-2"></i><span class="text-sm text-gray-400">Loading documents...</span></div>';

            const { type, id, label, ownerId } = soDocCurrentContext;
            const ownerParam = ownerId ? `&owner_id=${ownerId}` : '';

            fetch(`{{ route('faculty.supporting-documents.index') }}?documentable_type=${type}&documentable_id=${id}&so_label=${encodeURIComponent(label)}${ownerParam}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderSoDocuments(data.documents, !!ownerId);
                } else {
                    container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Failed to load documents</p>';
                }
            })
            .catch(() => {
                container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading documents</p>';
            });
        }

        function renderSoDocuments(documents, readOnly) {
            const container = document.getElementById('soDocumentsList');

            if (!documents || documents.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-folder-open text-gray-200 text-3xl mb-3"></i>
                        <p class="text-sm text-gray-400">No supporting documents yet</p>
                        ${readOnly ? '' : '<p class="text-xs text-gray-300 mt-1">Upload files using the form above</p>'}
                    </div>`;
                return;
            }

            container.innerHTML = documents.map(function(doc) {
                const isImage = (doc.mime_type || '').match(/jpg|jpeg|png|gif|webp|image/i);
                const isPdf = (doc.mime_type || '').match(/pdf/i) || (doc.original_name || '').endsWith('.pdf');
                let icon = 'fas fa-file text-gray-400';
                if (isImage) icon = 'fas fa-image text-green-500';
                else if (isPdf) icon = 'fas fa-file-pdf text-red-500';
                else if ((doc.original_name || '').match(/\.(doc|docx)$/i)) icon = 'fas fa-file-word text-blue-500';
                else if ((doc.original_name || '').match(/\.(xls|xlsx)$/i)) icon = 'fas fa-file-excel text-green-600';
                else if ((doc.original_name || '').match(/\.(ppt|pptx)$/i)) icon = 'fas fa-file-powerpoint text-orange-500';

                const nameDisplay = doc.original_name.length > 30 ? doc.original_name.substring(0, 27) + '...' : doc.original_name;

                // Image preview thumbnail or icon
                let previewHtml = `<i class="${icon} text-lg flex-shrink-0"></i>`;
                if (isImage) {
                    previewHtml = `<div class="w-12 h-12 flex-shrink-0 rounded overflow-hidden bg-gray-200"><img src="${doc.path}" alt="${doc.original_name}" class="w-full h-full object-cover" /></div>`;
                }

                // View button - popup for images, download route for others
                const viewAction = isImage 
                    ? `onclick="viewImagePreview(${doc.id}, '${doc.path.replace(/'/g, "\\'")}', '${doc.original_name.replace(/'/g, "\\'")}')"` 
                    : `onclick="window.location.href='/faculty/supporting-documents/${doc.id}/download'"`;

                // Hide rename/delete for read-only (dean viewing faculty docs)
                const actionButtons = readOnly ? '' : `
                            <button onclick="openRenameModal(${doc.id}, '${doc.original_name.replace(/'/g, "\\'")}')" class="p-2 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-all" title="Rename">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteSoDocument(${doc.id})" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>`;

                return `
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-2 hover:bg-gray-100 transition relative">
                        ${previewHtml}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" title="${doc.original_name}">${nameDisplay}</p>
                            <p class="text-xs text-gray-400">${doc.file_size_human} &bull; ${doc.created_at}</p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button ${viewAction} class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-all" title="${isImage ? 'Preview' : 'View'}">
                                <i class="fas fa-${isImage ? 'search-plus' : 'external-link-alt'}"></i>
                            </button>
                            ${actionButtons}
                        </div>
                    </div>`;
            }).join('');
        }

        window.uploadSoDocument = function() {
            const fileInput = document.getElementById('soDocFileInput');
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('documentable_type', soDocCurrentContext.type);
            formData.append('documentable_id', soDocCurrentContext.id);
            formData.append('so_label', soDocCurrentContext.label);

            const progressContainer = document.getElementById('soDocUploadProgress');
            const progressBar = document.getElementById('soDocProgressBar');
            const uploadBtn = document.getElementById('soDocUploadBtn');

            progressContainer.classList.remove('hidden');
            uploadBtn.disabled = true;
            uploadBtn.classList.add('opacity-50');
            progressBar.style.width = '0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("faculty.supporting-documents.store") }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                }
            };

            xhr.onload = function() {
                progressContainer.classList.add('hidden');
                uploadBtn.disabled = false;
                uploadBtn.classList.remove('opacity-50');
                fileInput.value = '';
                document.getElementById('soDocUploadText').textContent = 'Choose file to upload';
                uploadBtn.classList.add('hidden');

                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        loadSoDocuments();
                    } else {
                        showAlertModal('error', 'Upload Failed', data.message || 'Failed to upload document.');
                    }
                } catch (e) {
                    showAlertModal('error', 'Upload Failed', 'An error occurred during upload.');
                }
            };

            xhr.onerror = function() {
                progressContainer.classList.add('hidden');
                uploadBtn.disabled = false;
                uploadBtn.classList.remove('opacity-50');
                showAlertModal('error', 'Upload Failed', 'Network error occurred.');
            };

            xhr.send(formData);
        };

        // Image preview functions
        let currentZoom = 1;
        let isDragging = false;
        let startX, startY, scrollLeft, scrollTop;

        window.viewImagePreview = function(docId, url, filename) {
            const img = document.getElementById('imagePreviewImg');
            const container = document.getElementById('imagePreviewContainer');
            
            img.src = url;
            document.getElementById('imagePreviewTitle').textContent = filename;
            // Use server-side download route for proper filename
            document.getElementById('imagePreviewDownload').href = '/faculty/supporting-documents/' + docId + '/download';
            document.getElementById('imagePreviewModal').classList.remove('hidden');
            
            // Reset zoom
            currentZoom = 1;
            img.style.transform = 'scale(1)';
            document.getElementById('zoomLevel').textContent = '100%';
            
            // Enable dragging when image loads
            img.onload = function() {
                setupImageDrag();
            };
        };

        window.closeImagePreview = function() {
            document.getElementById('imagePreviewModal').classList.add('hidden');
            document.getElementById('imagePreviewImg').src = '';
            currentZoom = 1;
        };

        window.zoomIn = function() {
            currentZoom = Math.min(currentZoom + 0.25, 4);
            updateZoom();
        };

        window.zoomOut = function() {
            currentZoom = Math.max(currentZoom - 0.25, 0.5);
            updateZoom();
        };

        window.resetZoom = function() {
            currentZoom = 1;
            updateZoom();
            document.getElementById('imagePreviewContainer').scrollTo(0, 0);
        };

        function updateZoom() {
            const img = document.getElementById('imagePreviewImg');
            img.style.transform = `scale(${currentZoom})`;
            document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
        }

        function setupImageDrag() {
            const container = document.getElementById('imagePreviewContainer');
            const img = document.getElementById('imagePreviewImg');
            
            // Mouse drag
            img.addEventListener('mousedown', function(e) {
                if (currentZoom > 1) {
                    isDragging = true;
                    img.style.cursor = 'grabbing';
                    startX = e.pageX - container.offsetLeft;
                    startY = e.pageY - container.offsetTop;
                    scrollLeft = container.scrollLeft;
                    scrollTop = container.scrollTop;
                }
            });

            container.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const y = e.pageY - container.offsetTop;
                const walkX = (x - startX) * 2;
                const walkY = (y - startY) * 2;
                container.scrollLeft = scrollLeft - walkX;
                container.scrollTop = scrollTop - walkY;
            });

            container.addEventListener('mouseup', function() {
                isDragging = false;
                if (currentZoom > 1) {
                    img.style.cursor = 'move';
                } else {
                    img.style.cursor = 'default';
                }
            });

            container.addEventListener('mouseleave', function() {
                isDragging = false;
                if (currentZoom > 1) {
                    img.style.cursor = 'move';
                } else {
                    img.style.cursor = 'default';
                }
            });

            // Mouse wheel zoom
            container.addEventListener('wheel', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    if (e.deltaY < 0) {
                        zoomIn();
                    } else {
                        zoomOut();
                    }
                }
            }, { passive: false });
        }

        // Rename document functions
        let currentRenameDocId = null;
        let currentRenameOriginal = '';

        window.openRenameModal = function(docId, originalName) {
            currentRenameDocId = docId;
            currentRenameOriginal = originalName;
            
            // Extract filename without extension
            const lastDot = originalName.lastIndexOf('.');
            const nameWithoutExt = lastDot > 0 ? originalName.substring(0, lastDot) : originalName;
            
            document.getElementById('renameDocumentInput').value = nameWithoutExt;
            document.getElementById('renameDocumentModal').classList.remove('hidden');
            
            setTimeout(() => {
                document.getElementById('renameDocumentInput').focus();
                document.getElementById('renameDocumentInput').select();
            }, 100);
        };

        window.closeRenameModal = function() {
            document.getElementById('renameDocumentModal').classList.add('hidden');
            currentRenameDocId = null;
            currentRenameOriginal = '';
        };

        window.submitRename = function() {
            const newName = document.getElementById('renameDocumentInput').value.trim();
            if (!newName) {
                showAlertModal('error', 'Invalid Name', 'Please enter a valid filename.');
                return;
            }

            // Preserve original extension
            const lastDot = currentRenameOriginal.lastIndexOf('.');
            const extension = lastDot > 0 ? currentRenameOriginal.substring(lastDot) : '';
            const newFullName = newName + extension;

            fetch(`/faculty/supporting-documents/${currentRenameDocId}/rename`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ original_name: newFullName })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeRenameModal();
                    loadSoDocuments();
                    attachSoDocumentClickHandlers();
                } else {
                    showAlertModal('error', 'Rename Failed', data.message || 'Failed to rename document.');
                }
            })
            .catch(() => {
                showAlertModal('error', 'Error', 'An error occurred while renaming.');
            });
        };

        // Handle Enter key in rename input
        document.addEventListener('DOMContentLoaded', function() {
            const renameInput = document.getElementById('renameDocumentInput');
            if (renameInput) {
                renameInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        submitRename();
                    }
                });
            }
        });

        window.deleteSoDocument = function(id) {
            openConfirmationModal(
                'Delete Document',
                'Are you sure you want to delete this supporting document?',
                'This action cannot be undone.',
                'danger',
                'Delete',
                function() {
                    fetch(`/faculty/supporting-documents/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            loadSoDocuments();
                            attachSoDocumentClickHandlers();
                        } else {
                            showAlertModal('error', 'Error', data.message || 'Failed to delete document.');
                        }
                    })
                    .catch(() => {
                        showAlertModal('error', 'Error', 'An error occurred while deleting.');
                    });
                }
            );
        };

        // OPCR Templates Functions
        window.renderOpcrTemplates = async function() {
            const container = document.getElementById('opcrTemplatesContainer');
            if (!container) return;

            try {
                const templates = await getOpcrTemplates();
                
                if (templates.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No OPCR templates yet</p>';
                    return;
                }

                container.innerHTML = templates.map(template => {
                    const titleHtml = template.title || 'Untitled Template';
                    let periodHtml = '';
                    if (template.school_year && template.semester) {
                        periodHtml = '<p class="text-xs sm:text-sm text-gray-600">' + template.school_year + ' • ' + template.semester + '</p>';
                    } else if (template.period) {
                        periodHtml = '<p class="text-xs sm:text-sm text-gray-600">' + template.period + '</p>';
                    }
                    const dateStr = new Date(template.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
                    
                    return '<div class="template-card mb-3 relative border border-gray-200 rounded-lg p-3">' +
                        '<button onclick="deleteOpcrTemplate(' + template.id + ')" class="absolute top-2 right-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full p-2 transition" title="Delete template">' +
                        '<i class="fas fa-trash text-sm"></i>' +
                        '</button>' +
                        '<div class="mb-3 pr-8">' +
                        '<p class="text-sm sm:text-base font-semibold text-gray-900">' + titleHtml + '</p>' +
                        periodHtml +
                        '<p class="text-xs text-gray-500">Saved on ' + dateStr + '</p>' +
                        '</div>' +
                        '<div class="flex gap-2 ml-7">' +
                        '<button onclick="viewOpcrTemplate(' + template.id + ')" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-semibold py-2 px-3 sm:px-4 rounded">' +
                        'View' +
                        '</button>' +
                        '</div>' +
                        '</div>';
                }).join('');
            } catch (error) {
                console.error('Error rendering OPCR templates:', error);
                container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Failed to load templates</p>';
            }
        };

        async function getOpcrTemplates() {
            try {
                const response = await fetch('/faculty/opcr/templates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                return data.success ? data.templates : [];
            } catch (error) {
                console.error('Error fetching OPCR templates:', error);
                return [];
            }
        }

        window.deleteOpcrTemplate = function(id) {
            showConfirmModal(
                'Delete OPCR Template',
                'Are you sure you want to delete this OPCR template? This action cannot be undone.',
                () => {
                    fetch(`/faculty/opcr/templates/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlertModal('success', 'Deleted', 'OPCR template deleted successfully');
                            renderOpcrTemplates();
                        } else {
                            showAlertModal('error', 'Error', data.message || 'Failed to delete OPCR template');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while deleting the OPCR template.');
                    });
                }
            );
        };

        window.loadOpcrTemplateToDocument = async function(id) {
            try {
                const response = await fetch(`/faculty/opcr/templates/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                
                if (data.template) {
                    const template = data.template;
                    currentOpcrSavedCopyId = null;
                    
                    document.getElementById('opcrDisplaySchoolYear').textContent = template.school_year || 'N/A';
                    document.getElementById('opcrDisplaySemester').textContent = template.semester || 'N/A';
                    document.getElementById('opcrDocumentTitle').value = template.title;

                    // Load noted/approved by
                    const docNotedBy = document.getElementById('opcrDocNotedBy');
                    if (docNotedBy && template.noted_by) docNotedBy.value = template.noted_by;
                    const docApprovedBy = document.getElementById('opcrDocApprovedBy');
                    if (docApprovedBy && template.approved_by) docApprovedBy.value = template.approved_by;
                    
                    const tableBody = document.getElementById('opcrTableBody');
                    if (tableBody && template.table_body_html) {
                        tableBody.innerHTML = template.table_body_html;
                        unhideOpcrTableColumns();
                        // Label QETA inputs and set up auto-computation
                        labelQetaInputs(tableBody);
                    }
                    
                    document.getElementById('createOpcrButtonArea').style.display = 'none';
                    // Hide right sidebar behind modal
                    const rightSidebar2 = document.getElementById('rightSidebar');
                    if (rightSidebar2) rightSidebar2.style.display = 'none';
                    document.getElementById('opcrDocumentContainer').classList.remove('hidden');
                } else {
                    showAlertModal('error', 'Not Found', 'OPCR template could not be found.');
                }
            } catch (error) {
                console.error('Error loading OPCR template:', error);
                showAlertModal('error', 'Error', 'An error occurred while loading the OPCR template.');
            }
        };

        window.viewOpcrTemplate = async function(id) {
            try {
                const response = await fetch('/faculty/opcr/templates/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();

                if (data.template) {
                    const template = data.template;

                    if (template.table_body_html) {
                        const tableBody = document.getElementById('templatePreviewTableBody');
                        if (tableBody) {
                            tableBody.innerHTML = template.table_body_html;
                        }

                        const titleElement = document.getElementById('templatePreviewTitle');
                        if (titleElement && template.title) {
                            titleElement.textContent = template.title;
                        }

                        if (template.school_year) {
                            const displaySchoolYear = document.getElementById('templatePreviewSchoolYear');
                            if (displaySchoolYear) displaySchoolYear.textContent = template.school_year;
                        }
                        if (template.semester) {
                            const displaySemester = document.getElementById('templatePreviewSemester');
                            if (displaySemester) displaySemester.textContent = template.semester;
                        }

                        // Load noted/approved by
                        const tpNotedBy = document.getElementById('templatePreviewNotedBy');
                        if (tpNotedBy) tpNotedBy.value = template.noted_by || '';
                        const tpApprovedBy = document.getElementById('templatePreviewApprovedBy');
                        if (tpApprovedBy) tpApprovedBy.value = template.approved_by || '';

                        // Unhide all columns in the preview modal
                        const previewModal = document.getElementById('templatePreviewModal');
                        if (previewModal) {
                            const headers = previewModal.querySelectorAll('thead th.hidden');
                            headers.forEach(function(header) { header.classList.remove('hidden'); });
                            const cells = previewModal.querySelectorAll('td.hidden');
                            cells.forEach(function(cell) { cell.classList.remove('hidden'); });
                        }

                        // Set OPCR template context for supporting documents
                        const templateIdField = document.getElementById('currentPreviewTemplateId');
                        if (templateIdField) templateIdField.value = id;
                        const submIdField = document.getElementById('currentSubmissionIdToUpdate');
                        if (submIdField) submIdField.value = '';
                        const submTypeField = document.getElementById('currentSubmissionType');
                        if (submTypeField) submTypeField.value = 'opcr';

                        // Hide the Edit IPCR / save copy button for OPCR template view
                        const saveCopyBtn = document.getElementById('saveCopyBtn');
                        if (saveCopyBtn) saveCopyBtn.style.display = 'none';
                        const updateBtn = document.getElementById('updateSubmissionBtn');
                        if (updateBtn) updateBtn.classList.add('hidden');

                        document.getElementById('templatePreviewModal').classList.remove('hidden');
                        attachSoDocumentClickHandlers();
                        // Label QETA inputs and set up auto-computation
                        labelQetaInputs(document.getElementById('templatePreviewTableBody'));
                    } else {
                        showAlertModal('info', 'Legacy Template', 'This template was created with the old format.');
                    }
                } else {
                    showAlertModal('error', 'Not Found', 'OPCR template could not be found.');
                }
            } catch (error) {
                console.error('Error viewing OPCR template:', error);
                showAlertModal('error', 'Error', 'An error occurred while loading the OPCR template.');
            }
        };

        // ========== OPCR Submit Functions ==========
        window.openSubmitOpcrModal = function() {
            populateSubmitOpcrTemplates();
            var modal = document.getElementById('submitOpcrModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        };

        window.closeSubmitOpcrModal = function() {
            var modal = document.getElementById('submitOpcrModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        async function populateSubmitOpcrTemplates() {
            var select = document.getElementById('submitOpcrTemplateSelect');
            if (!select) return;

            var drafts = await getOpcrSavedCopies();
            select.innerHTML = '';

            if (drafts.length === 0) {
                var option = document.createElement('option');
                option.value = '';
                option.textContent = 'No OPCR saved drafts found';
                select.appendChild(option);
                select.disabled = true;
                return;
            }

            select.disabled = false;
            drafts.forEach(function(draft) {
                var option = document.createElement('option');
                option.value = draft.id;
                option.textContent = draft.title + ' \u2022 ' + (draft.school_year || '') + ' \u2022 ' + (draft.semester || '');
                select.appendChild(option);
            });
        }

        window.submitSelectedOpcrTemplate = async function() {
            var select = document.getElementById('submitOpcrTemplateSelect');
            var selectedId = select ? select.value : '';
            if (!selectedId) {
                showAlertModal('warning', 'Select a Draft', 'Please select an OPCR draft to submit.');
                return;
            }

            try {
                var response = await fetch('/faculty/opcr/saved-copies/' + selectedId, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                var data = await response.json();

                if (!data.success && !data.savedCopy) {
                    showAlertModal('error', 'Not Found', 'Selected OPCR draft could not be found.');
                    return;
                }

                var item = data.savedCopy;
                var soCounts = item.so_count_json || { strategic_objectives: 0, core_functions: 0, support_functions: 0 };

                var formData = new FormData();
                formData.append('title', item.title);
                formData.append('school_year', item.school_year || 'N/A');
                formData.append('semester', item.semester || 'N/A');
                formData.append('table_body_html', item.table_body_html || '');
                formData.append('so_count_json', JSON.stringify(soCounts));
                formData.append('saved_copy_id', selectedId); // Include saved_copy_id for document copying
                formData.append('noted_by', item.noted_by || '');
                formData.append('approved_by', item.approved_by || '');

                var submitResponse = await fetch('/faculty/opcr/submissions', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!submitResponse.ok) {
                    var submitData = await submitResponse.json().catch(function() { return {}; });
                    throw new Error(submitData.message || 'Failed to submit OPCR');
                }

                closeSubmitOpcrModal();
                showAlertModal('success', 'Submitted', 'Your OPCR has been submitted successfully.', function() {
                    window.location.reload();
                });
            } catch (error) {
                console.error('Submit OPCR error:', error);
                showAlertModal('error', 'Submit Failed', error.message || 'Failed to submit OPCR.');
            }
        };

        window.viewOpcrSubmission = function(submissionId) {
            fetch('/faculty/opcr/submissions/' + submissionId + '?t=' + Date.now(), {
                headers: {
                    'Cache-Control': 'no-cache'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.submission) {
                    var submission = data.submission;

                    // Store submission ID and type for update functionality
                    var submissionIdField = document.getElementById('currentSubmissionIdToUpdate');
                    if (submissionIdField) submissionIdField.value = submissionId;
                    var submissionTypeField = document.getElementById('currentSubmissionType');
                    if (submissionTypeField) submissionTypeField.value = 'opcr';

                    // Load table body
                    var tableBody = document.getElementById('templatePreviewTableBody');
                    if (tableBody && submission.table_body_html) {
                        tableBody.innerHTML = submission.table_body_html;

                        // Make all table cells NON-editable, but enable inputs/textareas inside
                        var cells = tableBody.querySelectorAll('td');
                        cells.forEach(function(cell) {
                            cell.setAttribute('contenteditable', 'false');
                            cell.style.cursor = 'default';
                            cell.style.userSelect = 'none';
                            cell.style.backgroundColor = '#f0fdf4';
                            cell.classList.add('hover:bg-green-50');

                            var inputs = cell.querySelectorAll('input');
                            var textareas = cell.querySelectorAll('textarea');

                            inputs.forEach(function(input) {
                                input.setAttribute('contenteditable', 'true');
                                input.removeAttribute('readonly');
                                input.removeAttribute('disabled');
                                input.style.pointerEvents = 'auto';
                                input.style.backgroundColor = 'white';
                                input.style.cursor = 'text';
                                input.style.userSelect = 'text';
                            });

                            textareas.forEach(function(textarea) {
                                textarea.setAttribute('contenteditable', 'true');
                                textarea.removeAttribute('readonly');
                                textarea.removeAttribute('disabled');
                                textarea.style.pointerEvents = 'auto';
                                textarea.style.backgroundColor = 'white';
                                textarea.style.cursor = 'text';
                                textarea.style.userSelect = 'text';
                            });
                        });
                    }

                    // Label QETA inputs and set up auto-computation
                    labelQetaInputs(tableBody);

                    // Load title
                    var titleElement = document.getElementById('templatePreviewTitle');
                    if (titleElement && submission.title) titleElement.textContent = submission.title;

                    // Load year and period
                    if (submission.school_year) {
                        var displaySchoolYear = document.getElementById('templatePreviewSchoolYear');
                        if (displaySchoolYear) displaySchoolYear.textContent = submission.school_year;
                    }
                    if (submission.semester) {
                        var displaySemester = document.getElementById('templatePreviewSemester');
                        if (displaySemester) displaySemester.textContent = submission.semester;
                    }

                    // Load noted/approved by
                    var tpNotedBy = document.getElementById('templatePreviewNotedBy');
                    if (tpNotedBy) tpNotedBy.value = submission.noted_by || '';
                    var tpApprovedBy = document.getElementById('templatePreviewApprovedBy');
                    if (tpApprovedBy) tpApprovedBy.value = submission.approved_by || '';

                    // Unhide all columns in the preview modal
                    var previewModal = document.getElementById('templatePreviewModal');
                    if (previewModal) {
                        var headers = previewModal.querySelectorAll('thead th.hidden');
                        headers.forEach(function(header) { header.classList.remove('hidden'); });
                        var hiddenCells = previewModal.querySelectorAll('td.hidden');
                        hiddenCells.forEach(function(cell) { cell.classList.remove('hidden'); });
                    }

                    // Hide Edit IPCR button and show Update Submission button (green for OPCR)
                    var saveCopyBtn = document.getElementById('saveCopyBtn');
                    if (saveCopyBtn) {
                        saveCopyBtn.style.display = 'none';
                        saveCopyBtn.classList.add('hidden');
                    }

                    var updateBtn = document.getElementById('updateSubmissionBtn');
                    if (updateBtn) {
                        updateBtn.classList.remove('hidden');
                        updateBtn.style.display = 'flex';
                    }

                    document.getElementById('templatePreviewModal').classList.remove('hidden');
                    attachSoDocumentClickHandlers();
                } else {
                    showAlertModal('error', 'Not Found', 'OPCR submission could not be found.');
                }
            })
            .catch(function(error) {
                console.error('Error viewing OPCR submission:', error);
                showAlertModal('error', 'Error', 'An error occurred while loading the OPCR submission.');
            });
        };

        window.deleteOpcrSubmission = function(submissionId) {
            showConfirmModal(
                'Delete OPCR Submission',
                'Are you sure you want to delete this OPCR submission? This action cannot be undone.',
                function() {
                    fetch('/faculty/opcr/submissions/' + submissionId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            showAlertModal('success', 'Deleted', 'OPCR submission deleted successfully.', function() {
                                window.location.reload();
                            });
                        } else {
                            showAlertModal('error', 'Error', data.message || 'Failed to delete OPCR submission');
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        showAlertModal('error', 'Error', 'An error occurred while deleting the OPCR submission.');
                    });
                }
            );
        };

    </script>
<!-- Import Loading Overlay -->
<div id="importLoadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[10000] p-4 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in overflow-hidden">
        <div class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center gap-3">
            <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Importing File...</h2>
            </div>
        </div>
        <div class="px-6 py-5">
            <p class="text-gray-700 text-sm">Please wait while we process your document. This may take a moment.</p>
        </div>
    </div>
</div>

<script>document.body.style.visibility = 'visible';</script>
</body>
</html>