<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Summary Reports - IPCR Dashboard</title>
    <link rel="icon" type="image/jpeg" href="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <img src="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}" alt="URS Logo" class="h-10 sm:h-12 w-auto object-contain flex-shrink-0">
                    <h1 class="text-base sm:text-xl font-bold text-gray-900">IPCR Dashboard</h1>
                </div>

                <div class="hidden lg:flex items-center space-x-6 xl:space-x-8">
                    <a href="{{ route('faculty.dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('faculty.my-ipcrs') }}" class="text-gray-600 hover:text-gray-900">My IPCRs</a>
                    <a href="{{ route('faculty.summary-reports') }}" class="text-blue-600 font-semibold hover:text-blue-700">Summary Reports</a>
                    <div class="relative">
                        <button onclick="toggleNotificationPopup()" class="text-gray-600 hover:text-gray-900 relative flex items-center gap-1">
                            Notifications
                            @if(($unreadCount ?? 0) > 0)
                                <span class="notification-badge" id="notifBadge" style="position: static; margin-left: 4px;">{{ $unreadCount }}</span>
                            @else
                                <span class="notification-badge hidden" id="notifBadge" style="position: static; margin-left: 4px;">0</span>
                            @endif
                        </button>

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
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">Logout</button>
                    </form>
                </div>

                <div class="lg:hidden">
                    <button onclick="toggleMobileMenu()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="mobileMenu" class="hidden lg:hidden pt-4 space-y-2">
                <a href="{{ route('faculty.dashboard') }}" class="block text-gray-600 hover:text-gray-900 py-2">Dashboard</a>
                <a href="{{ route('faculty.my-ipcrs') }}" class="block text-gray-600 hover:text-gray-900 py-2">My IPCRs</a>
                <a href="{{ route('faculty.summary-reports') }}" class="block text-blue-600 font-semibold hover:text-blue-700 py-2">Summary Reports</a>
                <a href="{{ route('faculty.profile') }}" class="block text-gray-600 hover:text-gray-900 py-2">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold py-2">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .notification-popup {
            display: none;
            position: fixed;
            top: auto;
            left: auto;
            right: 10px;
            width: 320px;
            max-width: calc(100vw - 20px);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        }

        @media (min-width: 641px) {
            .notification-popup {
                position: absolute;
                top: calc(100% + 10px);
                right: 0;
                width: 320px;
                max-width: 90vw;
            }
        }

        @media (max-width: 640px) {
            .notification-popup {
                top: 60px;
                width: 280px;
            }
        }

        .notification-popup.active {
            display: block;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        .notification-item {
            position: relative;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .notification-blue {
            background-color: #dbeafe;
            border-left: 3px solid #3b82f6;
        }

        .notification-green {
            background-color: #dcfce7;
            border-left: 3px solid #22c55e;
        }

        .notification-yellow {
            background-color: #fef3c7;
            border-left: 3px solid #f59e0b;
        }

        .notification-red {
            background-color: #fee2e2;
            border-left: 3px solid #ef4444;
        }

        .notification-gray {
            background-color: #f3f4f6;
            border-left: 3px solid #6b7280;
        }

        .notification-item.compact-notif {
            padding: 8px 10px;
            margin-bottom: 6px;
        }

        .notification-item.notif-unread {
            border-left-width: 4px;
            font-weight: 500;
        }

        .notification-item.notif-unread::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(59, 130, 246, 0.04);
            pointer-events: none;
            border-radius: inherit;
        }
    </style>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex flex-col md:flex-row gap-6 lg:gap-8">
            <!-- Sidebar / Mobile Category Menu -->
            <div class="w-full md:w-56 lg:w-64 flex-shrink-0 md:sticky md:top-24 self-start md:h-[calc(100vh-8rem)]">
                <div class="bg-white md:rounded-2xl md:shadow-sm md:border border-gray-100 overflow-hidden h-full -mx-4 px-4 md:mx-0 md:px-0 flex flex-col">
                    <div class="p-2 md:p-6 flex flex-wrap md:flex-nowrap md:flex-col gap-2 justify-center md:justify-start overflow-hidden items-center md:items-stretch flex-1">
                        <a href="{{ route('faculty.summary-reports', ['category' => 'faculty', 'department' => $activeDepartment]) }}" 
                           class="flex shrink-0 items-center gap-2 md:gap-3 px-3 py-2 md:px-4 md:py-3 rounded-lg md:rounded-xl transition-colors {{ $activeCategory === 'faculty' ? 'bg-blue-50 text-blue-600 font-semibold md:shadow-none shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="text-xs md:text-sm">Faculty</span>
                        </a>
                        
                        <a href="{{ route('faculty.summary-reports', ['category' => 'staff', 'department' => 'all']) }}" 
                           class="flex shrink-0 items-center gap-2 md:gap-3 px-3 py-2 md:px-4 md:py-3 rounded-lg md:rounded-xl transition-colors {{ $activeCategory === 'staff' ? 'bg-blue-50 text-blue-600 font-semibold md:shadow-none shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="text-xs md:text-sm">Staff</span>
                        </a>
                        
                        <a href="{{ route('faculty.summary-reports', ['category' => 'dean-director', 'department' => 'all']) }}" 
                           class="flex shrink-0 items-center gap-2 md:gap-3 px-3 py-2 md:px-4 md:py-3 rounded-lg md:rounded-xl transition-colors {{ $activeCategory === 'dean-director' ? 'bg-blue-50 text-blue-600 font-semibold md:shadow-none shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                            <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span class="text-xs md:text-sm">Dean and Director</span>
                        </a>

                        <div class="hidden md:block w-full" style="margin-top: auto;">
                            <hr class="my-4 border-gray-100">
                            <a href="{{ route('faculty.summary-reports', ['category' => 'dean-ipcrs', 'department' => 'all']) }}" 
                               class="flex shrink-0 items-center gap-2 md:gap-3 px-3 py-2 md:px-4 md:py-3 rounded-lg md:rounded-xl transition-colors {{ $activeCategory === 'dean-ipcrs' ? 'bg-blue-50 text-blue-600 font-semibold md:shadow-none shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-xs md:text-sm">Dean IPCRs</span>
                            </a>

                            <a href="{{ route('faculty.summary-reports', ['category' => 'user-management', 'department' => 'all']) }}" 
                               class="flex shrink-0 items-center gap-2 md:gap-3 px-3 py-2 md:px-4 md:py-3 rounded-lg md:rounded-xl transition-colors {{ $activeCategory === 'user-management' ? 'bg-blue-50 text-blue-600 font-semibold md:shadow-none shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-xs md:text-sm">User Management</span>
                            </a>
                        </div>
                        
                        <!-- Mobile View for Dean IPCRs -->
                        <a href="{{ route('faculty.summary-reports', ['category' => 'dean-ipcrs', 'department' => 'all']) }}" 
                           class="md:hidden flex shrink-0 items-center gap-2 px-3 py-2 rounded-lg transition-colors {{ $activeCategory === 'dean-ipcrs' ? 'bg-blue-50 text-blue-600 font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-xs">Dean IPCRs</span>
                        </a>

                        <!-- Mobile View for User Management -->
                        <a href="{{ route('faculty.summary-reports', ['category' => 'user-management', 'department' => 'all']) }}" 
                           class="md:hidden flex shrink-0 items-center gap-2 px-3 py-2 rounded-lg transition-colors {{ $activeCategory === 'user-management' ? 'bg-blue-50 text-blue-600 font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-xs">User Management</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 min-w-0">
                <!-- Tabs -->
                @if(!in_array($activeCategory, ['staff', 'dean-director', 'dean-ipcrs', 'user-management'], true))
                <div class="mb-6">
                    <div class="flex space-x-2 overflow-x-auto pb-1">
                        <a href="{{ route('faculty.summary-reports', ['category' => $activeCategory, 'department' => 'all']) }}"
                           class="px-5 py-2 rounded-full text-sm whitespace-nowrap shadow-sm transition-colors {{ $activeDepartment === 'all' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-gray-200 text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            All
                        </a>
                        @foreach($departments as $dept)
                        <a href="{{ route('faculty.summary-reports', ['category' => $activeCategory, 'department' => $dept->code]) }}"
                           class="px-5 py-2 rounded-full text-sm whitespace-nowrap shadow-sm transition-colors {{ $activeDepartment === $dept->code ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-gray-200 text-slate-600 font-medium hover:bg-slate-50 hover:text-slate-900' }}">
                            {{ $dept->code }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Tables Container -->
                @if($activeCategory === 'dean-director')
                <div class="space-y-6">
                    @if(session('success'))
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-white flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Summary of Performance Evaluation</h3>
                                <p class="text-xs text-gray-500 mt-1">Campus Director and College Deans</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('faculty.summary-reports.dean-director.export') }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                                    Export
                                </a>
                            </div>
                        </div>

                        <!-- Desktop View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[1050px]">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50/50">
                                        <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[260px]">Name of Employee</th>
                                        <th class="px-3 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[140px] text-center">Strategic Objectives<br>35%</th>
                                        <th class="px-3 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[140px] text-center">Core Function/s<br>55%</th>
                                        <th class="px-3 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[140px] text-center">Support Function/s<br>10%</th>
                                        <th class="px-3 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[100px] text-center">Total (1-5)</th>
                                        <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[180px] text-center">Equivalent Adjectival Rating</th>
                                        <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[130px] text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($deanDirectorRows as $row)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-5 py-3">
                                            @php
                                                $desktopFormId = 'deanDirectorScoreForm' . $row['user_id'];
                                                $desktopClearFormId = 'deanDirectorClearForm' . $row['user_id'];
                                            @endphp
                                            <form id="{{ $desktopFormId }}" method="POST" action="{{ route('faculty.summary-reports.dean-director.update', ['user' => $row['user_id']]) }}" class="hidden">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                            <form id="{{ $desktopClearFormId }}" method="POST" action="{{ route('faculty.summary-reports.dean-director.update', ['user' => $row['user_id']]) }}" class="hidden">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="clear_scores" value="1" />
                                            </form>
                                            <div class="font-bold text-gray-900 text-sm">{{ $row['employee_name'] ?? 'N/A' }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">
                                                {{ $row['employee_label_short'] ?? $row['employee_label'] }}{{ $row['employee_id'] ? ' · ' . $row['employee_id'] : '' }}
                                            </div>
                                            @if($row['is_manual'])
                                                <span class="inline-flex mt-2 px-2 py-0.5 rounded border border-blue-200 bg-blue-50 text-[10px] font-semibold uppercase tracking-wider text-blue-700">Manual override</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <input type="number" name="strategic_score" form="{{ $desktopFormId }}" step="0.01" min="0" max="35"
                                                value="{{ $row['strategic_score'] !== null ? number_format($row['strategic_score'], 2, '.', '') : '' }}"
                                                class="w-24 text-center text-sm text-gray-900 border border-gray-200 rounded-md px-2 py-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <input type="number" name="core_score" form="{{ $desktopFormId }}" step="0.01" min="0" max="55"
                                                value="{{ $row['core_score'] !== null ? number_format($row['core_score'], 2, '.', '') : '' }}"
                                                class="w-24 text-center text-sm text-gray-900 border border-gray-200 rounded-md px-2 py-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <input type="number" name="support_score" form="{{ $desktopFormId }}" step="0.01" min="0" max="10"
                                                value="{{ $row['support_score'] !== null ? number_format($row['support_score'], 2, '.', '') : '' }}"
                                                class="w-24 text-center text-sm text-gray-900 border border-gray-200 rounded-md px-2 py-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                        </td>
                                        <td class="px-3 py-3 text-center text-sm font-bold text-gray-900">
                                            {{ $row['total_score'] !== null ? number_format($row['total_score'], 2) : '—' }}
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            @if($row['adjectival_rating'])
                                                @php
                                                    $leaderRatingColor = match($row['adjectival_rating']) {
                                                        'Outstanding' => 'text-blue-700 bg-blue-50 border border-blue-200',
                                                        'Very Satisfactory' => 'text-green-700 bg-green-50 border border-green-200',
                                                        'Satisfactory' => 'text-yellow-700 bg-yellow-50 border border-yellow-200',
                                                        'Unsatisfactory' => 'text-orange-700 bg-orange-50 border border-orange-200',
                                                        'Poor' => 'text-red-700 bg-red-50 border border-red-200',
                                                        default => 'text-gray-700 bg-gray-50 border border-gray-200',
                                                    };
                                                @endphp
                                                <span class="inline-block px-2.5 py-1 rounded-md shadow-sm text-xs font-bold {{ $leaderRatingColor }}">
                                                    {{ $row['adjectival_rating'] }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400 italic">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="submit" form="{{ $desktopFormId }}" title="Save" class="inline-flex items-center justify-center p-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                                <button type="submit" form="{{ $desktopClearFormId }}" title="Clear" class="inline-flex items-center justify-center p-2 rounded-md bg-white border border-gray-300 text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-12 text-center">
                                            <div class="text-gray-400 mb-1">
                                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <p class="text-sm text-gray-500">No dean or director records found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile View -->
                        <div class="md:hidden divide-y divide-gray-100">
                            @forelse($deanDirectorRows as $row)
                            <div class="p-4 bg-white hover:bg-gray-50 transition-colors">
                                @php
                                    $mobileFormId = 'deanDirectorMobileScoreForm' . $row['user_id'];
                                @endphp
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">{{ $row['employee_name'] ?? 'N/A' }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $row['employee_label_short'] ?? $row['employee_label'] }}{{ $row['employee_id'] ? ' · ' . $row['employee_id'] : '' }}</p>
                                        @if($row['is_manual'])
                                            <span class="inline-flex mt-2 px-2 py-0.5 rounded border border-blue-200 bg-blue-50 text-[10px] font-semibold uppercase tracking-wider text-blue-700">Manual override</span>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-gray-900">{{ $row['total_score'] !== null ? number_format($row['total_score'], 2) : '—' }}</div>
                                        <div class="text-[10px] text-gray-500 uppercase tracking-widest">Total (1-5)</div>
                                    </div>
                                </div>
                                <form id="{{ $mobileFormId }}" method="POST" action="{{ route('faculty.summary-reports.dean-director.update', ['user' => $row['user_id']]) }}" class="space-y-2 mb-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 gap-2">
                                        <label class="text-[11px] text-gray-600 font-medium">
                                            Strategic Objectives (35%)
                                            <input type="number" name="strategic_score" step="0.01" min="0" max="35"
                                                value="{{ $row['strategic_score'] !== null ? number_format($row['strategic_score'], 2, '.', '') : '' }}"
                                                class="mt-1 w-full border border-gray-200 rounded-md px-2.5 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                        </label>
                                        <label class="text-[11px] text-gray-600 font-medium">
                                            Core Function/s (55%)
                                            <input type="number" name="core_score" step="0.01" min="0" max="55"
                                                value="{{ $row['core_score'] !== null ? number_format($row['core_score'], 2, '.', '') : '' }}"
                                                class="mt-1 w-full border border-gray-200 rounded-md px-2.5 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                        </label>
                                        <label class="text-[11px] text-gray-600 font-medium">
                                            Support Function/s (10%)
                                            <input type="number" name="support_score" step="0.01" min="0" max="10"
                                                value="{{ $row['support_score'] !== null ? number_format($row['support_score'], 2, '.', '') : '' }}"
                                                class="mt-1 w-full border border-gray-200 rounded-md px-2.5 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="submit" title="Save" class="flex justify-center items-center w-full px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                        <button type="submit" name="clear_scores" value="1" formnovalidate title="Clear" class="flex justify-center items-center w-full px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </form>
                                <div>
                                    @if($row['adjectival_rating'])
                                        @php
                                            $leaderRatingColor = match($row['adjectival_rating']) {
                                                'Outstanding' => 'text-blue-700 bg-blue-50 border border-blue-200',
                                                'Very Satisfactory' => 'text-green-700 bg-green-50 border border-green-200',
                                                'Satisfactory' => 'text-yellow-700 bg-yellow-50 border border-yellow-200',
                                                'Unsatisfactory' => 'text-orange-700 bg-orange-50 border border-orange-200',
                                                'Poor' => 'text-red-700 bg-red-50 border border-red-200',
                                                default => 'text-gray-700 bg-gray-50 border border-gray-200',
                                            };
                                        @endphp
                                        <span class="w-full block text-center px-2.5 py-1.5 rounded-md shadow-sm text-xs font-bold {{ $leaderRatingColor }}">
                                            {{ $row['adjectival_rating'] }}
                                        </span>
                                    @else
                                        <span class="block w-full text-center text-sm text-gray-400 italic py-1">N/A</span>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-sm text-gray-500">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                No dean or director records found.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @elseif($activeCategory === 'dean-ipcrs')
                <div class="space-y-6">
                    @if(session('success'))
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-white flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Dean IPCRs (Calibrated)</h3>
                                <p class="text-xs text-gray-500 mt-1">History of calibrated IPCR submissions from deans</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="hidden sm:inline text-xs text-gray-500">{{ $deanIpcrRows->count() }} {{ Str::plural('submission', $deanIpcrRows->count()) }}</span>
                                <button type="button" onclick="document.getElementById('deanIpcrFilterForm').classList.toggle('hidden')" class="px-3 py-1.5 border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold rounded-md flex items-center gap-1.5 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                    Filters
                                </button>
                            </div>
                        </div>

                        @php
                            $hasDeanFilters = ($deanIpcrFilters['search'] ?? '') !== '' || 
                                              ($deanIpcrFilters['dean_id'] ?? 'all') !== 'all' || 
                                              ($deanIpcrFilters['dean_department_id'] ?? $deanIpcrFilters['department_id'] ?? 'all') !== 'all' || 
                                              ($deanIpcrFilters['school_year'] ?? 'all') !== 'all' || 
                                              ($deanIpcrFilters['semester'] ?? 'all') !== 'all' || 
                                              ($deanIpcrFilters['submitted_from'] ?? '') !== '' || 
                                              ($deanIpcrFilters['submitted_to'] ?? '') !== '';
                        @endphp
                        <form id="deanIpcrFilterForm" method="GET" action="{{ route('faculty.summary-reports') }}" class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/70 {{ $hasDeanFilters ? '' : 'hidden' }}">
                            <input type="hidden" name="category" value="dean-ipcrs">
                            <input type="hidden" name="department" value="all">

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <label for="deanIpcrSearch" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Search</label>
                                    <input id="deanIpcrSearch" type="text" name="search" value="{{ $deanIpcrFilters['search'] ?? '' }}" placeholder="Title, dean name, employee ID" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="deanIdFilter" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Dean</label>
                                    <select id="deanIdFilter" name="dean_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="all">All Deans</option>
                                        @foreach($deanIpcrDeans as $dean)
                                            <option value="{{ $dean->id }}" {{ ($deanIpcrFilters['dean_id'] ?? 'all') == (string) $dean->id ? 'selected' : '' }}>{{ $dean->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="deanDepartmentFilter" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Department</label>
                                    <select id="deanDepartmentFilter" name="dean_department_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="all">All Departments</option>
                                        @foreach($deanIpcrDepartments as $dept)
                                            <option value="{{ $dept->id }}" {{ ($deanIpcrFilters['department_id'] ?? 'all') == (string) $dept->id ? 'selected' : '' }}>{{ $dept->code }} - {{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="deanSchoolYearFilter" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">School Year</label>
                                    <select id="deanSchoolYearFilter" name="school_year" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="all">All School Years</option>
                                        @foreach($deanIpcrSchoolYears as $schoolYear)
                                            <option value="{{ $schoolYear }}" {{ ($deanIpcrFilters['school_year'] ?? 'all') == (string) $schoolYear ? 'selected' : '' }}>{{ $schoolYear }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="deanSemesterFilter" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Semester</label>
                                    <select id="deanSemesterFilter" name="semester" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        <option value="all">All Semesters</option>
                                        @foreach($deanIpcrSemesters as $semester)
                                            <option value="{{ $semester }}" {{ ($deanIpcrFilters['semester'] ?? 'all') == (string) $semester ? 'selected' : '' }}>{{ $semester }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="submittedFromFilter" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Submitted From</label>
                                    <input id="submittedFromFilter" type="date" name="submitted_from" value="{{ $deanIpcrFilters['submitted_from'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label for="submittedToFilter" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 mb-1">Submitted To</label>
                                    <input id="submittedToFilter" type="date" name="submitted_to" value="{{ $deanIpcrFilters['submitted_to'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">Apply Filters</button>
                                <a href="{{ route('faculty.summary-reports', ['category' => 'dean-ipcrs', 'department' => 'all']) }}" class="px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition-colors">Reset</a>
                            </div>
                        </form>

                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full min-w-[900px] border-collapse text-left">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500 min-w-[180px]">Dean & Dept</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500 min-w-[200px]">IPCR Title</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500">Period</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500">Submitted</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500 text-center">Score</th>
                                        <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500">Calibrated Details</th>
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-500 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($deanIpcrRows as $row)
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-sm text-gray-900">{{ $row['dean_name'] }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $row['department_code'] }}{{ $row['employee_id'] ? ' · ' . $row['employee_id'] : '' }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm text-gray-900 line-clamp-2" title="{{ $row['title'] }}">{{ $row['title'] }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm text-gray-800">{{ $row['semester'] }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">SY {{ $row['school_year'] }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-800">{{ $row['submitted_at'] ? $row['submitted_at']->format('M d, Y') : 'N/A' }}</td>
                                        <td class="px-3 py-3 text-center">
                                            @if($row['calibrated_score'] !== null)
                                                <span class="text-sm font-bold text-green-700 bg-green-50 px-2.5 py-1 rounded-md border border-green-200">{{ number_format($row['calibrated_score'], 2) }}</span>
                                            @else
                                                <span class="text-sm text-gray-400 italic">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm text-gray-800">{{ $row['calibrated_by'] ?? 'N/A' }}</div>
                                            @if($row['calibrated_at'])
                                                <div class="text-[11px] text-gray-500 mt-0.5">{{ $row['calibrated_at']->format('M d, Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('faculty.summary-reports.dean-ipcrs.show', ['submission' => $row['id']]) }}" onclick="return openDeanIpcrInNewTab(event, this.href)" title="View" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                                <a href="{{ route('faculty.summary-reports.dean-ipcrs.export', ['submission' => $row['id']]) }}" title="Export" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-md transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                            No calibrated dean IPCR submissions found for the selected filters.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="md:hidden divide-y divide-gray-100">
                            @forelse($deanIpcrRows as $row)
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">{{ $row['dean_name'] }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $row['department_code'] }}{{ $row['employee_id'] ? ' · ' . $row['employee_id'] : '' }}</p>
                                        <p class="text-xs text-gray-700 mt-1 font-medium">{{ $row['title'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] uppercase tracking-wider text-gray-500">Calibrated</div>
                                        <div class="text-sm font-bold text-green-700">{{ $row['calibrated_score'] !== null ? number_format($row['calibrated_score'], 2) : 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-3 text-[11px] text-gray-600">
                                    <div><span class="font-semibold">SY:</span> {{ $row['school_year'] }}</div>
                                    <div><span class="font-semibold">Sem:</span> {{ $row['semester'] }}</div>
                                    <div><span class="font-semibold">Submitted:</span> {{ $row['submitted_at'] ? $row['submitted_at']->format('M d, Y') : 'N/A' }}</div>
                                    <div><span class="font-semibold">By:</span> {{ $row['calibrated_by'] ?? 'N/A' }}</div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('faculty.summary-reports.dean-ipcrs.show', ['submission' => $row['id']]) }}" onclick="return openDeanIpcrInNewTab(event, this.href)" class="flex flex-1 justify-center items-center gap-1.5 px-3 py-2 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    <a href="{{ route('faculty.summary-reports.dean-ipcrs.export', ['submission' => $row['id']]) }}" class="flex flex-1 justify-center items-center gap-1.5 px-3 py-2 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Export
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-sm text-gray-500">
                                No calibrated dean IPCR submissions found for the selected filters.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @elseif($activeCategory === 'user-management')
                @include('dashboard.faculty.partials.user-management')
                @else
                @php
                    $summaryExportUrl = $activeCategory === 'staff'
                        ? route('faculty.summary-reports.staff.export')
                        : route('faculty.summary-reports.faculty.export', ['department' => $activeDepartment]);

                    $reportSections = $activeCategory === 'staff'
                        ? [
                            [
                                'title' => 'Staff (Permanent, Casual and Contractual)',
                                'members' => $users,
                                'empty' => 'No staff members (Permanent, Casual and Contractual) found',
                            ],
                            [
                                'title' => 'Emergency Laborer',
                                'members' => $emergencyUsers,
                                'empty' => 'No emergency laborers found',
                            ],
                        ]
                        : [
                            [
                                'title' => 'Faculty Members',
                                'members' => $users,
                                'empty' => 'No faculty members found',
                            ],
                            [
                                'title' => 'Part-Time Faculty Members',
                                'members' => $partTimeUsers,
                                'empty' => 'No part-time faculty members found',
                            ],
                        ];
                @endphp
                <div class="space-y-6">
                    @foreach($reportSections as $section)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-white flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">{{ $section['title'] }}</h3>
                            <div class="flex items-center gap-2">
                                @if($loop->first)
                                <a href="{{ $summaryExportUrl }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                                    Export
                                </a>
                                @endif
                                <span class="text-xs text-gray-500">{{ $section['members']->count() }} {{ Str::plural('member', $section['members']->count()) }}</span>
                            </div>
                        </div>
                        <!-- Desktop View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[700px]">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50/50">
                                        <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[220px]">Employee</th>
                                        <th class="px-3 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[180px]">Role & Dept</th>
                                        <th class="px-3 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[120px]">Status</th>
                                        <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest min-w-[140px] text-center">Rating</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($section['members'] as $user)
                                    @php
                                        $initials = collect(explode(' ', $user->name))->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(2)->implode('');
                                        $avatarColors = ['bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-green-100 text-green-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
                                        $colorIndex = crc32($user->name) % count($avatarColors);
                                        $avatarColor = $avatarColors[abs($colorIndex)];
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                @if($user->hasProfilePhoto())
                                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                                @else
                                                    <div class="w-8 h-8 rounded-full {{ $avatarColor }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                        {{ $initials }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-bold text-gray-900 text-sm leading-tight">{{ $user->name }}</div>
                                                    <div class="text-[11px] text-gray-500 mt-0.5">{{ $user->employee_id ?? '—' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm text-gray-900">{{ $user->designation->title ?? 'N/A' }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $user->department->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            @if($activeCategory === 'staff')
                                                @php
                                                    $staffStatusColor = match($user->employment_status) {
                                                        'Permanent' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                                                        'Casual' => 'text-amber-700 bg-amber-50 border border-amber-200',
                                                        'Contractual' => 'text-sky-700 bg-sky-50 border border-sky-200',
                                                        'Emergency Laborer' => 'text-indigo-700 bg-indigo-50 border border-indigo-200',
                                                        default => 'text-gray-700 bg-gray-50 border border-gray-200',
                                                    };
                                                @endphp
                                                <span class="inline-block px-2.5 py-1 rounded-md shadow-sm text-xs font-bold {{ $staffStatusColor }}">
                                                    {{ $user->employment_status ?? 'N/A' }}
                                                </span>
                                            @elseif($user->employment_status === 'Part Time')
                                                <span class="inline-block px-2.5 py-1 rounded-md shadow-sm text-xs font-bold text-violet-700 bg-violet-50 border border-violet-200">
                                                    Part Time
                                                </span>
                                            @elseif($user->is_active)
                                                <span class="inline-flex items-center gap-1.5 text-sm text-green-700 bg-green-50 px-2 py-0.5 rounded-md border border-green-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-sm text-gray-500 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            @if($user->calibrated_rating !== null)
                                                <div class="flex flex-col items-center justify-center gap-1">
                                                    <span class="text-sm font-bold text-gray-900 bg-gray-100 px-2.5 py-0.5 rounded">{{ number_format($user->calibrated_rating, 2) }}</span>
                                                    @if($user->adjectival_rating)
                                                        @php
                                                            $ratingColor = match($user->adjectival_rating) {
                                                                'Outstanding' => 'text-blue-700 bg-blue-50 border border-blue-200',
                                                                'Very Satisfactory' => 'text-green-700 bg-green-50 border border-green-200',
                                                                'Satisfactory' => 'text-yellow-700 bg-yellow-50 border border-yellow-200',
                                                                'Unsatisfactory' => 'text-orange-700 bg-orange-50 border border-orange-200',
                                                                'Poor' => 'text-red-700 bg-red-50 border border-red-200',
                                                                default => 'text-gray-700 bg-gray-50 border border-gray-200',
                                                            };
                                                        @endphp
                                                        <span class="inline-block px-2 py-0.5 rounded shadow-sm text-[10px] font-bold {{ $ratingColor }}">
                                                            {{ $user->adjectival_rating }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400 italic">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-12 text-center">
                                            <div class="text-gray-400 mb-1">
                                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </div>
                                            <p class="text-sm text-gray-500">{{ $section['empty'] }}{{ $activeDepartment !== 'all' ? ' in this department' : '' }}.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile View -->
                        <div class="md:hidden divide-y divide-gray-100">
                            @forelse($section['members'] as $user)
                            @php
                                $initials = collect(explode(' ', $user->name))->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(2)->implode('');
                                $avatarColors = ['bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-green-100 text-green-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
                                $colorIndex = crc32($user->name) % count($avatarColors);
                                $avatarColor = $avatarColors[abs($colorIndex)];
                            @endphp
                            <div class="p-4 bg-white hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3 mb-3">
                                    @if($user->hasProfilePhoto())
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-full {{ $avatarColor }} flex items-center justify-center font-bold text-sm flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-900 text-sm leading-tight truncate">{{ $user->name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 truncate">{{ $user->designation->title ?? 'N/A' }}{{ $user->employee_id ? ' · ' . $user->employee_id : '' }}</div>
                                    </div>
                                    <div class="text-right pl-2 shrink-0">
                                        <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-0.5">Rating</div>
                                        @if($user->calibrated_rating !== null)
                                            <div class="text-sm font-bold text-gray-900">{{ number_format($user->calibrated_rating, 2) }}</div>
                                        @else
                                            <div class="text-sm text-gray-400 italic">N/A</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 items-center mb-3 text-xs">
                                    <span class="inline-flex items-center gap-1 text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        {{ $user->department->name ?? 'N/A' }}
                                    </span>
                                    
                                    @if($activeCategory === 'staff')
                                        @php
                                            $staffStatusColor = match($user->employment_status) {
                                                'Permanent' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                                                'Casual' => 'text-amber-700 bg-amber-50 border border-amber-200',
                                                'Contractual' => 'text-sky-700 bg-sky-50 border border-sky-200',
                                                'Emergency Laborer' => 'text-indigo-700 bg-indigo-50 border border-indigo-200',
                                                default => 'text-gray-700 bg-gray-50 border border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-block px-2 py-1 rounded-md text-xs font-medium {{ $staffStatusColor }}">
                                            {{ $user->employment_status ?? 'N/A' }}
                                        </span>
                                    @elseif($user->employment_status === 'Part Time')
                                        <span class="inline-block px-2 py-1 rounded-md text-xs font-medium text-violet-700 bg-violet-50 border border-violet-200">
                                            Part Time
                                        </span>
                                    @elseif($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-green-700 bg-green-50 px-2 py-1 rounded-md border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 bg-gray-50 px-2 py-1 rounded-md border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if($user->adjectival_rating)
                                        @php
                                            $ratingColor = match($user->adjectival_rating) {
                                                'Outstanding' => 'text-blue-700 bg-blue-50 border border-blue-200',
                                                'Very Satisfactory' => 'text-green-700 bg-green-50 border border-green-200',
                                                'Satisfactory' => 'text-yellow-700 bg-yellow-50 border border-yellow-200',
                                                'Unsatisfactory' => 'text-orange-700 bg-orange-50 border border-orange-200',
                                                'Poor' => 'text-red-700 bg-red-50 border border-red-200',
                                                default => 'text-gray-700 bg-gray-50 border border-gray-200',
                                            };
                                        @endphp
                                        <span class="w-full block text-center px-2.5 py-1.5 rounded-md shadow-sm text-xs font-bold {{ $ratingColor }}">
                                            {{ $user->adjectival_rating }}
                                        </span>
                                    @else
                                        <span class="block w-full text-center text-sm text-gray-400 italic py-1">N/A</span>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-sm text-gray-500">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                {{ $section['empty'] }}{{ $activeDepartment !== 'all' ? ' in this department' : '' }}.
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    @if(in_array($activeCategory, ['faculty', 'staff', 'dean-director'], true))
    <a href="{{ route('faculty.summary-reports.export-all', ['category' => $activeCategory, 'department' => $activeDepartment]) }}"
       class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-full bg-emerald-600 text-white text-sm font-bold shadow-lg hover:bg-emerald-700 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export All
    </a>
    @endif

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        function openDeanIpcrInNewTab(event, url) {
            if (event) {
                event.preventDefault();
            }

            const newTab = window.open(url, '_blank');

            if (newTab) {
                try {
                    newTab.focus();
                } catch (_) {
                    // Ignore focus errors from browser policies.
                }
            } else if (url) {
                // Popup blocked: fallback to current-tab navigation.
                window.location.href = url;
            }

            return false;
        }

        function toggleNotificationPopup() {
            const popup = document.getElementById('notificationPopup');
            if (popup) {
                popup.classList.toggle('active');
            }
        }

        document.addEventListener('click', function(e) {
            const popup = document.getElementById('notificationPopup');
            const notificationBtn = e.target.closest('button[onclick*="toggleNotificationPopup"]');

            if (!notificationBtn && popup && !popup.contains(e.target)) {
                popup.classList.remove('active');
            }
        });

        let compactMode = localStorage.getItem('notif_compact') === '1';

        function applyCompactMode() {
            document.querySelectorAll('.notif-card').forEach(card => {
                if (compactMode) {
                    card.classList.add('compact-notif');
                    card.querySelectorAll('.notif-message').forEach(m => m.style.display = 'none');
                    card.querySelectorAll('.notif-time').forEach(t => t.style.display = 'none');
                } else {
                    card.classList.remove('compact-notif');
                    card.querySelectorAll('.notif-message').forEach(m => m.style.display = '');
                    card.querySelectorAll('.notif-time').forEach(t => t.style.display = '');
                }
            });

            document.querySelectorAll('.compact-toggle-btn').forEach(btn => {
                if (compactMode) {
                    btn.classList.add('bg-indigo-100', 'border-indigo-300', 'text-indigo-700');
                    btn.classList.remove('bg-gray-50', 'border-gray-200', 'text-gray-500');
                } else {
                    btn.classList.remove('bg-indigo-100', 'border-indigo-300', 'text-indigo-700');
                    btn.classList.add('bg-gray-50', 'border-gray-200', 'text-gray-500');
                }
            });
        }

        function toggleCompactMode() {
            compactMode = !compactMode;
            localStorage.setItem('notif_compact', compactMode ? '1' : '0');
            applyCompactMode();
        }

        function markAllNotificationsRead() {
            fetch('{{ route('faculty.notifications.mark-read') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('#notifBadge').forEach(el => el.classList.add('hidden'));
                    document.querySelectorAll('.notif-unread-dot').forEach(dot => dot.remove());
                    document.querySelectorAll('.notif-card.notif-unread').forEach(card => card.classList.remove('notif-unread'));
                }
            })
            .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', applyCompactMode);
    </script>
</body>
</html>