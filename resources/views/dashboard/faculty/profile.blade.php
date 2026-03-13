<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile - IPCR Dashboard</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    @vite(['resources/css/dashboard_faculty_profile.css', 'resources/js/dashboard_faculty_profile.js'])
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
                    <a href="{{ route('faculty.my-ipcrs') }}" class="text-gray-600 hover:text-gray-900">My IPCRs</a>
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
                    <a href="{{ route('faculty.profile') }}" class="text-blue-600 font-semibold hover:text-blue-700">Profile</a>
                    
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
                <a href="{{ route('faculty.my-ipcrs') }}" class="block text-gray-600 hover:text-gray-900 py-2">My IPCRs</a>
                <a href="{{ route('faculty.profile') }}" class="block text-blue-600 font-semibold hover:text-blue-700 py-2">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold py-2">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-6">
        
        <!-- Profile Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Left: Profile Info -->
                <div class="flex flex-col sm:flex-row gap-6 flex-1">
                    <!-- Avatar -->
                    <div class="flex-shrink-0 relative group mx-auto sm:mx-0">
                        @if(auth()->user()->hasProfilePhoto())
                            <img src="{{ auth()->user()->profile_photo_url }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=128&background=3b82f6&color=fff" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md">
                        @endif
                        <button onclick="openPhotoGalleryModal()" class="absolute bottom-0 right-0 bg-white border-2 border-white rounded-full w-9 h-9 flex items-center justify-center shadow-md hover:bg-gray-50 transition text-gray-500 hover:text-blue-600" title="Manage photos">
                            <i class="fas fa-camera text-sm"></i>
                        </button>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 text-center sm:text-left space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ auth()->user()->name }}</h1>
                                <p class="text-gray-500 font-medium">{{ auth()->user()->email }}</p>
                            </div>
                            <button onclick="openEditProfileModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm self-center sm:self-start">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                        </div>

                        <div class="text-gray-600">
                            {{ auth()->user()->designation->title ?? 'No Designation' }} &middot; {{ auth()->user()->department->name ?? 'No Department' }}
                        </div>

                        <div class="flex flex-wrap gap-2 justify-center sm:justify-start pt-1">
                            @foreach(auth()->user()->roles() as $role)
                                @if(in_array($role, ['dean', 'director']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ ucfirst($role) }}
                                    </span>
                                @endif
                            @endforeach
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                Faculty
                            </span>
                             @if(auth()->user()->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Activity Stats (Vertical Divider on Desktop) -->
                <div class="lg:w-72 flex-shrink-0 lg:border-l lg:border-gray-200 lg:pl-8 flex flex-col justify-center">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Activity</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Last Login</span>
                            <span class="text-sm font-medium text-gray-900">
                                @if(auth()->user()->last_login_at)
                                    {{ auth()->user()->last_login_at->format('M d, Y g:i A') }}
                                @else
                                    Never
                                @endif
                            </span>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Member Since</span>
                            <span class="text-sm font-medium text-gray-900">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Photos</span>
                            <span class="text-sm font-medium text-gray-900"><span id="activityPhotoCount">{{ $photoCount }}</span> uploaded</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Completeness Bar -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-sm font-medium text-gray-700">Profile Completeness</span>
                    @if($profileCompleteness['percentage'] < 100)
                        <button onclick="document.getElementById('completenessDetails').classList.toggle('hidden')" class="text-sm text-green-600 hover:text-green-700 font-medium">
                            {{ $profileCompleteness['percentage'] }}% &middot; Complete Now
                        </button>
                    @else
                        <span class="text-sm text-green-600 font-medium">Complete</span>
                    @endif
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $profileCompleteness['percentage'] }}%"></div>
                </div>
                 <!-- Expandable Checklist -->
                 <div id="completenessDetails" class="hidden mt-4 animate-slideDown bg-gray-50 rounded-xl p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach($profileCompleteness['fields'] as $field)
                            <div class="flex items-center gap-2 text-sm {{ $field['completed'] ? 'text-green-700' : 'text-gray-500' }}">
                                <i class="fas {{ $field['completed'] ? 'fa-check-circle' : 'fa-circle' }} {{ $field['completed'] ? 'text-green-500' : 'text-gray-300' }}"></i>
                                <span>{{ $field['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column (Information) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Personal Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Employee ID</label>
                            <div class="text-gray-900 font-medium">
                                {{ auth()->user()->employee_id ?? 'Not assigned' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Username</label>
                            <div class="text-gray-900 font-medium">
                                {{ auth()->user()->username }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email Address</label>
                            <div class="flex items-center gap-2">
                                <div class="text-gray-900 font-medium">{{ auth()->user()->email }}</div>
                                @if(auth()->user()->email_verified_at)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Verified</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Phone Number</label>
                            <div class="text-gray-900 font-medium">
                                {{ auth()->user()->phone ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Work Information -->
                 <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Work Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Department</label>
                            <div class="text-gray-900 font-medium">
                                {{ auth()->user()->department->name ?? 'Not assigned' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Designation</label>
                            <div class="text-gray-900 font-medium">
                                {{ auth()->user()->designation->title ?? 'Not assigned' }}
                            </div>
                        </div>
                         <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Employment Status</label>
                            <div class="text-gray-900 font-medium">
                                Permanent
                            </div>
                        </div>
                         <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Campus</label>
                            <div class="text-gray-900 font-medium">
                                Binangonan Campus
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Settings & Help) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Account & Security -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Account & Security</h2>
                    <div class="space-y-1">
                        <button onclick="openEditProfileModal()" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition text-left group">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:shadow-sm transition">
                                <i class="fas fa-pen text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Edit Profile</span>
                        </button>

                         <button onclick="openChangePasswordModal()" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition text-left group">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:shadow-sm transition">
                                <i class="fas fa-lock text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Change Password</span>
                        </button>

                        <button onclick="openPhotoGalleryModal()" class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition text-left group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:shadow-sm transition">
                                    <i class="fas fa-image text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Manage Photos</span>
                            </div>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-semibold group-hover:bg-white">{{ $photoCount }}</span>
                        </button>
                        
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition group">
                             <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:shadow-sm transition">
                                    <i class="fas fa-envelope text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Email Verification</span>
                            </div>
                            @if(auth()->user()->email_verified_at)
                                <span class="text-green-600 text-xs font-semibold">Verified</span>
                            @else
                                <button onclick="openEmailVerificationModal()" class="text-blue-600 hover:text-blue-700 text-xs font-semibold hover:underline">Verify</button>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition group">
                             <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:shadow-sm transition">
                                    <i class="fas fa-user-tag text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Roles</span>
                            </div>
                            <span class="text-gray-500 text-xs">{{ count(auth()->user()->roles()) }} assigned</span>
                        </div>

                        @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 transition text-left group">
                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-500 group-hover:bg-white group-hover:shadow-sm transition">
                                <i class="fas fa-shield-halved text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-red-600 group-hover:text-red-700">Admin Panel</span>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Need Help -->
                <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600">
                             <i class="fas fa-life-ring"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Need Help?</h3>
                            <p class="text-sm text-gray-600 mb-3">If you have questions about your IPCR or profile, please contact support.</p>
                            <a href="mailto:admin@urs.edu.ph" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                admin@urs.edu.ph
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Photo Gallery Modal -->
    <div id="photoGalleryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full animate-scale-in my-8">
            <div class="bg-purple-50 border-b border-purple-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-images text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Photo Gallery</h2>
                        <p class="text-sm text-gray-600">Manage your profile photos (<span id="galleryPhotoCount">0</span> photos)</p>
                    </div>
                </div>
                <button type="button" onclick="closePhotoGalleryModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4">
                <!-- Upload New Photo -->
                <div class="mb-4 p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 transition text-center">
                    <input type="file" id="galleryPhotoInput" accept="image/*" class="hidden">
                    <button type="button" onclick="document.getElementById('galleryPhotoInput').click()" class="w-full">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm font-medium text-gray-600">Click to upload a new photo</p>
                        <p class="text-xs text-gray-400 mt-1">Max 5MB &bull; JPEG, PNG, GIF, WebP</p>
                    </button>
                    <div id="galleryUploadProgress" class="hidden mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div id="galleryProgressBar" class="bg-blue-600 h-1.5 rounded-full transition-all" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Uploading...</p>
                    </div>
                    <div id="galleryUploadMessage" class="text-xs mt-2"></div>
                </div>

                <!-- Photo Grid -->
                <div id="galleryPhotos" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    <p class="text-xs text-gray-500 col-span-full text-center py-8">Loading photos...</p>
                </div>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                <button type="button" onclick="closePhotoGalleryModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Set Profile Photo Confirmation Modal -->
    <div id="setProfileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-[55] p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full animate-scale-in">
            <div class="px-6 py-4 text-center">
                <div class="bg-blue-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user-circle text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Set as Profile Photo?</h3>
                <p class="text-sm text-gray-600">This photo will be used as your profile picture across the system.</p>
            </div>
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-center">
                <button type="button" onclick="closeSetProfileModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Cancel
                </button>
                <button type="button" onclick="confirmSetProfile()" class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition text-sm">
                    <i class="fas fa-check mr-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Photo Confirmation Modal -->
    <div id="deletePhotoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-[55] p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full animate-scale-in">
            <div class="px-6 py-4 text-center">
                <div class="bg-red-100 rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-trash-alt text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Photo?</h3>
                <p class="text-sm text-gray-600">This action cannot be undone. The photo will be permanently removed.</p>
            </div>
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-center">
                <button type="button" onclick="closeDeletePhotoModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Cancel
                </button>
                <button type="button" onclick="confirmDeletePhoto()" class="px-4 py-2 rounded-lg font-semibold text-white bg-red-600 hover:bg-red-700 transition text-sm">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Image Crop Modal -->
    <div id="cropModal" class="fixed inset-0 bg-black bg-opacity-75 hidden flex items-center justify-center z-[60] p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full animate-scale-in">
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-crop-alt text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Crop & Resize Photo</h2>
                        <p class="text-sm text-gray-600">Adjust your profile picture</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                <div class="mb-4">
                    <div class="max-h-96 overflow-hidden bg-gray-100 rounded-lg flex items-center justify-center">
                        <img id="cropImage" src="" alt="Crop preview" style="max-width: 100%; display: block;">
                    </div>
                </div>

                <!-- Crop Controls -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" onclick="cropperZoomIn()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-search-plus mr-1"></i> Zoom In
                    </button>
                    <button type="button" onclick="cropperZoomOut()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-search-minus mr-1"></i> Zoom Out
                    </button>
                    <button type="button" onclick="cropperRotateLeft()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-undo mr-1"></i> Rotate Left
                    </button>
                    <button type="button" onclick="cropperRotateRight()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-redo mr-1"></i> Rotate Right
                    </button>
                    <button type="button" onclick="cropperReset()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-sync mr-1"></i> Reset
                    </button>
                </div>

                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Drag to move, scroll to zoom. The cropped area will be your profile picture.
                </p>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                <button type="button" onclick="closeCropModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Cancel
                </button>
                <button type="button" onclick="applyCropAndUpload()" class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2 text-sm">
                    <i class="fas fa-check"></i> Crop & Upload
                </button>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center gap-3">
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Change Password</h2>
                    <p class="text-sm text-gray-600">Update your account password</p>
                </div>
            </div>

            <form id="changePasswordForm" method="POST" action="{{ route('faculty.password.change') }}">
                @csrf
                @method('PATCH')
                
                <div class="px-6 py-4 space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password" required
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Enter current password">
                            <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 cursor-pointer">
                                <svg id="current_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="current_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <span class="text-red-500 text-xs hidden" id="current_password_error"></span>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <div class="relative">
                            <input type="password" id="new_password" name="new_password" required
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Enter new password">
                            <button type="button" onclick="togglePasswordVisibility('new_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 cursor-pointer">
                                <svg id="new_password_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="new_password_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <span class="text-red-500 text-xs hidden" id="new_password_error"></span>
                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters</p>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Confirm new password">
                            <button type="button" onclick="togglePasswordVisibility('new_password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 cursor-pointer">
                                <svg id="new_password_confirmation_eye_open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="new_password_confirmation_eye_closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <span class="text-red-500 text-xs hidden" id="new_password_confirmation_error"></span>
                    </div>

                    <!-- Success/Error Messages -->
                    <div id="passwordMessage" class="hidden rounded-lg p-3 text-sm"></div>
                </div>

                <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                    <button type="button" onclick="closeChangePasswordModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full animate-scale-in max-h-[90vh] overflow-y-auto scrollbar-hide">
            <div class="bg-blue-50 border-b border-blue-200 px-6 py-4 flex items-center gap-3">
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Edit Profile</h2>
                    <p class="text-sm text-gray-600">Update your profile information</p>
                </div>
            </div>

            <form id="editProfileForm" data-action="{{ route('faculty.profile.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')
                
                <div class="px-6 py-4">
                    <!-- Profile Picture Section -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Profile Picture</h3>
                        
                        <div class="flex items-center gap-4">
                            <!-- Current Photo Preview -->
                            <div class="flex-shrink-0">
                                <div id="modalPhotoPreview" class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-300 bg-gray-200 flex items-center justify-center">
                                    @if(auth()->user()->hasProfilePhoto())
                                        <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-gray-400 text-2xl"></i>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Upload Controls -->
                            <div class="flex-1">
                                <input type="file" id="modalPhotoInput" name="photo" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('modalPhotoInput').click()" class="px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-100 transition text-sm font-medium">
                                    <i class="fas fa-camera mr-2"></i>Choose Photo
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Max 5MB • JPEG, PNG, GIF, WebP</p>
                                <div id="modalUploadProgress" class="hidden mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div id="modalProgressBar" class="bg-blue-600 h-1.5 rounded-full transition-all" style="width: 0%"></div>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">Uploading...</p>
                                </div>
                                <div id="modalUploadMessage" class="text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Full Name -->
                            <div>
                                <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="name" id="edit_name" value="{{ auth()->user()->name }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Enter full name">
                                <span class="text-red-500 text-xs hidden" id="edit_name_error"></span>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="email" id="edit_email" value="{{ auth()->user()->email }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Enter email address">
                                <span class="text-red-500 text-xs hidden" id="edit_email_error"></span>
                            </div>

                            <!-- Username -->
                            <div>
                                <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                                <input type="text" name="username" id="edit_username" value="{{ auth()->user()->username }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Enter username">
                                <span class="text-red-500 text-xs hidden" id="edit_username_error"></span>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="edit_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="phone" id="edit_phone" value="{{ auth()->user()->phone ?? '' }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Enter phone number">
                                <span class="text-red-500 text-xs hidden" id="edit_phone_error"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Work Information -->
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Work Information</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Department -->
                            <div>
                                <label for="edit_department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select name="department_id" id="edit_department_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">Select a department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ auth()->user()->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-xs hidden" id="edit_department_id_error"></span>
                            </div>

                            <!-- Designation -->
                            <div>
                                <label for="edit_designation_id" class="block text-sm font-medium text-gray-700 mb-2">Designation</label>
                                <select name="designation_id" id="edit_designation_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">Select a designation</option>
                                    @foreach($designations as $desig)
                                        <option value="{{ $desig->id }}" {{ auth()->user()->designation_id == $desig->id ? 'selected' : '' }}>{{ $desig->title }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-xs hidden" id="edit_designation_id_error"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <div id="editProfileMessage" class="hidden rounded-lg p-3 text-sm mt-6"></div>
                </div>

                <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                    <button type="button" onclick="closeEditProfileModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Verification Modal -->
    <div id="emailVerificationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div class="bg-green-50 border-b border-green-200 px-6 py-4 flex items-center gap-3">
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-envelope-open-text text-green-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Verify Email Address</h2>
                    <p class="text-sm text-gray-600">Confirm your email to secure your account</p>
                </div>
            </div>

            <div id="verificationContent" class="px-6 py-4">
                <!-- Step 1: Request Code -->
                <div id="verifyStep1">
                    <p class="text-sm text-gray-600 mb-4">
                        Click the button below to receive a 6-digit verification code at <strong>{{ auth()->user()->email }}</strong>
                    </p>
                    
                    <button type="button" onclick="sendVerificationCode()" id="sendCodeBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Send Verification Code
                    </button>
                    
                    <div id="sendCodeMessage" class="mt-3 text-sm hidden"></div>
                </div>

                <!-- Step 2: Enter Code -->
                <div id="verifyStep2" class="hidden">
                    <p class="text-sm text-gray-600 mb-1">
                        Enter the 6-digit code sent to your email:
                    </p>
                    <p class="text-xs text-gray-500 mb-4">
                        <i class="fas fa-envelope mr-1"></i>{{ auth()->user()->email }}
                    </p>

                    <form id="verifyCodeForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 text-center">Verification Code</label>
                            <div class="flex justify-center gap-2 mb-3">
                                <input type="text" class="verify-code-input" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="verify-code-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="verify-code-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="verify-code-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="verify-code-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="verify-code-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                            </div>
                            <input type="hidden" id="verification_code_hidden" name="code" value="">
                            <p class="text-xs text-gray-500 text-center">Code expires in 30 minutes</p>
                        </div>

                        <div id="verifyCodeMessage" class="text-sm hidden"></div>

                        <div class="flex gap-3">
                            <button type="button" onclick="backToStep1()" class="flex-1 border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </button>
                            <button type="submit" id="verifyCodeBtn" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-check mr-1"></i> Verify
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-center">
                        <p class="text-xs text-blue-700 mb-2">Didn't receive the code?</p>
                        <button type="button" onclick="sendVerificationCode()" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                            <i class="fas fa-redo mr-1"></i> Resend Code
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end">
                <button type="button" onclick="closeEmailVerificationModal()" class="px-4 py-2 rounded-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 transition text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

<script>document.body.style.visibility = 'visible';</script>
</body>
</html>
