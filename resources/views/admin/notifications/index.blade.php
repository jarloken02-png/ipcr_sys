@extends('layouts.admin')

@section('title', 'Notifications & Deadlines')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0 w-full">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Notifications & Deadlines</h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Manage system notifications and upcoming deadlines visible to all users</p>
        </div>
    </div>
@endsection

@section('content')
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 transition-all duration-300 group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center transform group-hover:rotate-3 transition-transform">
                    <i class="fas fa-bell text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Total Notifications</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $notifications->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 transition-all duration-300 group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center transform group-hover:-rotate-3 transition-transform">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Active Notifications</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $notifications->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 transition-all duration-300 group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center transform group-hover:rotate-3 transition-transform">
                    <i class="fas fa-calendar-alt text-orange-600 dark:text-orange-400 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Total Deadlines</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $deadlines->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 transition-all duration-300 group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center transform group-hover:-rotate-3 transition-transform">
                    <i class="fas fa-clock text-purple-600 dark:text-purple-400 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Upcoming Deadlines</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $deadlines->where('is_active', true)->where('deadline_date', '>=', now()->toDateString())->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         NOTIFICATIONS SECTION
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-6 overflow-hidden transition-colors">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-bell text-blue-500"></i> Notifications
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">System-wide notifications visible on all user dashboards</p>
            </div>
            <button onclick="openCreateNotificationModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                <i class="fas fa-plus text-xs"></i> New Notification
            </button>
        </div>

        {{-- Notifications Table (Desktop) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Title</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Type</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Audience</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Status</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Published</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Expires</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
@php
                        $typeColors = [
                            'info'    => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'warning' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                            'success' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'danger'  => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                        ];
                    @endphp
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @forelse($notifications as $notification)
                        @php
                            $isScheduled = $notification->is_active && $notification->published_at && $notification->published_at->isFuture();
                            $isExpired   = $notification->is_active && $notification->expires_at && $notification->expires_at->isPast();
                            $isLive      = $notification->is_active && !$isScheduled && !$isExpired;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="py-3 px-6">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-xs">{{ $notification->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($notification->message, 60) }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-6">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $typeColors[$notification->type] ?? $typeColors['info'] }}">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </td>
                            <td class="py-3 px-6">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ ucfirst($notification->audience) }}</span>
                            </td>
                            <td class="py-3 px-6">
                                @if(!$notification->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                    </span>
                                @elseif($isScheduled)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300" title="Will go live on {{ $notification->published_at->format('M d, Y g:i A') }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Scheduled
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Expired
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Live
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-xs dark:text-gray-400 {{ $isScheduled ? 'text-purple-600 font-semibold' : 'text-gray-500' }}">
                                {{ $notification->published_at ? $notification->published_at->format('M d, Y') : 'Immediate' }}
                                @if($isScheduled) <span class="block text-[10px] text-purple-400">not yet live</span> @endif
                            </td>
                            <td class="py-3 px-6 text-xs dark:text-gray-400 {{ $isExpired ? 'text-red-500 font-semibold' : 'text-gray-500' }}">{{ $notification->expires_at ? $notification->expires_at->format('M d, Y') : 'Never' }}</td>
                            <td class="py-3 px-6">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditNotificationModal({{ $notification->id }}, {{ Js::from($notification) }})" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:text-blue-400 dark:hover:bg-blue-900/30 transition-colors" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.notifications.toggle', $notification) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 dark:hover:text-yellow-400 dark:hover:bg-yellow-900/30 transition-colors" title="{{ $notification->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $notification->is_active ? 'fa-toggle-on text-green-500' : 'fa-toggle-off' }} text-sm"></i>
                                        </button>
                                    </form>
                                    <button onclick="openDeleteModal('notification', {{ $notification->id }}, '{{ addslashes($notification->title) }}')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-bell-slash text-3xl mb-2 block opacity-30"></i>
                                <p class="text-sm">No notifications yet. Create one to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Notifications Mobile Cards --}}
        <div class="md:hidden space-y-3 p-4">
            @forelse($notifications as $notification)
                @php
                    $isScheduled = $notification->is_active && $notification->published_at && $notification->published_at->isFuture();
                    $isExpired   = $notification->is_active && $notification->expires_at && $notification->expires_at->isPast();
                @endphp
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ Str::limit($notification->message, 80) }}</p>
                        </div>
                        @if(!$notification->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 flex-shrink-0 ml-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Off
                            </span>
                        @elseif($isScheduled)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 flex-shrink-0 ml-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Scheduled
                            </span>
                        @elseif($isExpired)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex-shrink-0 ml-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Expired
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 flex-shrink-0 ml-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Live
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $typeColors[$notification->type] ?? $typeColors['info'] }}">{{ ucfirst($notification->type) }}</span>
                        <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 px-1.5 py-0.5 rounded">{{ ucfirst($notification->audience) }}</span>
                    </div>
                    <div class="flex items-center justify-end gap-1 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <button onclick="openEditNotificationModal({{ $notification->id }}, {{ Js::from($notification) }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 text-xs font-medium px-2 py-1"><i class="fas fa-pen mr-1"></i>Edit</button>
                        <form method="POST" action="{{ route('admin.notifications.toggle', $notification) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 text-xs font-medium px-2 py-1">
                                <i class="fas {{ $notification->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"></i>{{ $notification->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <button onclick="openDeleteModal('notification', {{ $notification->id }}, '{{ addslashes($notification->title) }}')" class="text-red-600 dark:text-red-400 hover:text-red-700 text-xs font-medium px-2 py-1"><i class="fas fa-trash-can mr-1"></i>Delete</button>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <i class="fas fa-bell-slash text-2xl mb-2 block opacity-30"></i>
                    <p class="text-sm">No notifications yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         DEADLINES SECTION
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-colors">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-orange-500"></i> Upcoming Deadlines
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Manage submission deadlines shown on user dashboards</p>
            </div>
            <button onclick="openCreateDeadlineModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                <i class="fas fa-plus text-xs"></i> New Deadline
            </button>
        </div>

        {{-- Deadlines Table (Desktop) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Title</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Deadline Date</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Audience</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Status</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider">Days Left</th>
                        <th class="py-3 px-6 font-semibold text-xs uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @forelse($deadlines as $deadline)
                        @php
                            $isPast = $deadline->deadline_date->isPast();
                            $daysLeft = $isPast ? 0 : (int) now()->startOfDay()->diffInDays($deadline->deadline_date, false);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $isPast ? 'opacity-60' : '' }}">
                            <td class="py-3 px-6">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-xs">{{ $deadline->title }}</p>
                                    @if($deadline->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ Str::limit($deadline->description, 60) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-6 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $deadline->deadline_date->format('M d, Y') }}</td>
                            <td class="py-3 px-6">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ ucfirst($deadline->audience) }}</span>
                            </td>
                            <td class="py-3 px-6">
                                @if($deadline->is_active && !$isPast)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                    </span>
                                @elseif($isPast)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Past Due
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-6">
                                @if($isPast)
                                    <span class="text-xs font-bold text-red-500">Passed</span>
                                @elseif($daysLeft <= 3)
                                    <span class="text-xs font-bold text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-full">{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}</span>
                                @elseif($daysLeft <= 7)
                                    <span class="text-xs font-bold text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 rounded-full">{{ $daysLeft }} days</span>
                                @else
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $daysLeft }} days</span>
                                @endif
                            </td>
                            <td class="py-3 px-6">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditDeadlineModal({{ $deadline->id }}, {{ Js::from($deadline) }})" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:text-blue-400 dark:hover:bg-blue-900/30 transition-colors" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.deadlines.toggle', $deadline) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 dark:hover:text-yellow-400 dark:hover:bg-yellow-900/30 transition-colors" title="{{ $deadline->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $deadline->is_active ? 'fa-toggle-on text-green-500' : 'fa-toggle-off' }} text-sm"></i>
                                        </button>
                                    </form>
                                    <button onclick="openDeleteModal('deadline', {{ $deadline->id }}, '{{ addslashes($deadline->title) }}')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-calendar-xmark text-3xl mb-2 block opacity-30"></i>
                                <p class="text-sm">No deadlines yet. Create one to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Deadlines Mobile Cards --}}
        <div class="md:hidden space-y-3 p-4">
            @forelse($deadlines as $deadline)
                @php
                    $isPast = $deadline->deadline_date->isPast();
                    $daysLeft = $isPast ? 0 : (int) now()->startOfDay()->diffInDays($deadline->deadline_date, false);
                @endphp
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700 space-y-3 {{ $isPast ? 'opacity-60' : '' }}">
                    <div class="flex justify-between items-start">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $deadline->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $deadline->deadline_date->format('M d, Y') }}</p>
                        </div>
                        @if(!$isPast && $daysLeft <= 7)
                            <span class="text-[10px] font-bold {{ $daysLeft <= 3 ? 'text-red-600 bg-red-50' : 'text-yellow-600 bg-yellow-50' }} px-2 py-0.5 rounded-full flex-shrink-0 ml-2">{{ $daysLeft }}d left</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-end gap-1 pt-2 border-t border-gray-200 dark:border-gray-600">
                        <button onclick="openEditDeadlineModal({{ $deadline->id }}, {{ Js::from($deadline) }})" class="text-blue-600 dark:text-blue-400 text-xs font-medium px-2 py-1"><i class="fas fa-pen mr-1"></i>Edit</button>
                        <form method="POST" action="{{ route('admin.deadlines.toggle', $deadline) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-yellow-600 dark:text-yellow-400 text-xs font-medium px-2 py-1">
                                <i class="fas {{ $deadline->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"></i>{{ $deadline->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <button onclick="openDeleteModal('deadline', {{ $deadline->id }}, '{{ addslashes($deadline->title) }}')" class="text-red-600 dark:text-red-400 text-xs font-medium px-2 py-1"><i class="fas fa-trash-can mr-1"></i>Delete</button>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <i class="fas fa-calendar-xmark text-2xl mb-2 block opacity-30"></i>
                    <p class="text-sm">No deadlines yet.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('modals')
    {{-- ══════════════════════════════════════════════════════════════
         CREATE / EDIT NOTIFICATION MODAL
         ══════════════════════════════════════════════════════════════ --}}
    <div id="notificationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
        <div class="absolute inset-0 bg-black/40" onclick="closeNotificationModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 animate-scale-in transition-colors z-10 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h3 id="notificationModalTitle" class="text-lg font-bold text-gray-900 dark:text-white">Create Notification</h3>
            </div>
            <form id="notificationForm" method="POST" action="">
                @csrf
                <div id="notificationMethodField"></div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="notif_title" required maxlength="255" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" placeholder="e.g. System maintenance scheduled">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" id="notif_message" required maxlength="2000" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none" placeholder="Notification details..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select name="type" id="notif_type" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="success">Success</option>
                                <option value="danger">Danger</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Audience</label>
                            <select name="audience" id="notif_audience" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="all">All Users</option>
                                <option value="faculty">Faculty Only</option>
                                <option value="dean">Dean Only</option>
                                <option value="director">Director Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Publish Date <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="datetime-local" name="published_at" id="notif_published_at" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Expiry Date <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="datetime-local" name="expires_at" id="notif_expires_at" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeNotificationModal()" class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors">Save Notification</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         CREATE / EDIT DEADLINE MODAL
         ══════════════════════════════════════════════════════════════ --}}
    <div id="deadlineModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
        <div class="absolute inset-0 bg-black/40" onclick="closeDeadlineModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 animate-scale-in transition-colors z-10 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h3 id="deadlineModalTitle" class="text-lg font-bold text-gray-900 dark:text-white">Create Deadline</h3>
            </div>
            <form id="deadlineForm" method="POST" action="">
                @csrf
                <div id="deadlineMethodField"></div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="deadline_title" required maxlength="255" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" placeholder="e.g. IPCR Submission Deadline">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="description" id="deadline_description" maxlength="1000" rows="2" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none" placeholder="Additional details about the deadline"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Deadline Date <span class="text-red-500">*</span></label>
                            <input type="date" name="deadline_date" id="deadline_date" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Audience</label>
                            <select name="audience" id="deadline_audience" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="all">All Users</option>
                                <option value="faculty">Faculty Only</option>
                                <option value="dean">Dean Only</option>
                                <option value="director">Director Only</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" onclick="closeDeadlineModal()" class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-semibold transition-colors">Save Deadline</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         DELETE CONFIRMATION MODAL
         ══════════════════════════════════════════════════════════════ --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
        <div class="absolute inset-0 bg-black/40" onclick="closeDeleteModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 animate-scale-in transition-colors z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                    <i class="fas fa-trash-can text-red-500 dark:text-red-400"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirm Delete</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-5">
                Are you sure you want to delete "<span id="deleteItemName" class="font-semibold"></span>"?
            </p>
            <form id="deleteForm" method="POST" action="">
                @csrf @method('DELETE')
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition">Delete</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
<script src="{{ Vite::asset('resources/js/admin_notifications_index.js') }}"></script>
@endpush
