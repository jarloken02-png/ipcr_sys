@php
    $summaryNotifDeadlineRedirectUrl = route('faculty.summary-reports', ['category' => 'notifications-deadlines', 'department' => 'all']);

    $notificationTypeClasses = [
        'info' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'warning' => 'bg-amber-50 text-amber-700 border border-amber-200',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'danger' => 'bg-red-50 text-red-700 border border-red-200',
    ];

    $totalNotifications = $summaryAdminNotifications->count();
    $activeNotifications = $summaryAdminNotifications->where('is_active', true)->count();
    $totalDeadlines = $summaryUpcomingDeadlines->count();
    $upcomingDeadlines = $summaryUpcomingDeadlines
        ->where('is_active', true)
        ->filter(fn ($deadline) => $deadline->deadline_date && $deadline->deadline_date->gte(now()->startOfDay()))
        ->count();
@endphp

<div class="space-y-6">
    <div id="summaryManagedEndpoints"
         data-notification-base="{{ url('/admin/panel/notifications') }}"
         data-deadline-base="{{ url('/admin/panel/deadlines') }}"></div>

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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Notifications</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalNotifications }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
                <i class="fas fa-bell text-blue-600 text-lg"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Active Notifications</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $activeNotifications }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Deadlines</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalDeadlines }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-orange-600 text-lg"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Upcoming</p>
                <p class="text-2xl font-bold text-violet-600 mt-1">{{ $upcomingDeadlines }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center">
                <i class="fas fa-clock text-violet-600 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Notifications</h3>
                <p class="text-xs text-gray-500 mt-1">Manage dashboard notifications from Summary Reports</p>
            </div>
            <button type="button" onclick="openSummaryCreateNotificationModal()" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-1.5"></i> New Notification
            </button>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left min-w-[980px]">
                <thead class="bg-gray-50/70 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Audience</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Published</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Expires</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summaryAdminNotifications as $notification)
                        @php
                            $isScheduled = $notification->is_active && $notification->published_at && $notification->published_at->isFuture();
                            $isExpired = $notification->is_active && $notification->expires_at && $notification->expires_at->isPast();
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900">{{ $notification->title }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ Str::limit($notification->message, 100) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $notificationTypeClasses[$notification->type] ?? $notificationTypeClasses['info'] }}">{{ ucfirst($notification->type) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($notification->audience) }}</td>
                            <td class="px-4 py-3">
                                @if(!$notification->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                @elseif($isScheduled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700"><span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>Scheduled</span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Expired</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Live</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $notification->published_at ? $notification->published_at->format('M d, Y h:i A') : 'Immediate' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $notification->expires_at ? $notification->expires_at->format('M d, Y h:i A') : 'Never' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="openSummaryEditNotificationModal({{ $notification->id }}, {{ Js::from($notification) }})" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.notifications.toggle', $notification) }}" class="inline" style="margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">
                                        <button type="submit" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition" title="{{ $notification->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $notification->is_active ? 'fa-toggle-on text-emerald-500' : 'fa-toggle-off' }} text-sm"></i>
                                        </button>
                                    </form>

                                    <button type="button" onclick="openSummaryDeleteManagedItemModal('notification', {{ $notification->id }}, '{{ addslashes($notification->title) }}')" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No notifications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100">
            @forelse($summaryAdminNotifications as $notification)
                @php
                    $isScheduled = $notification->is_active && $notification->published_at && $notification->published_at->isFuture();
                    $isExpired = $notification->is_active && $notification->expires_at && $notification->expires_at->isPast();
                @endphp
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">{{ $notification->title }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($notification->message, 90) }}</p>
                        </div>
                        @if(!$notification->is_active)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Inactive</span>
                        @elseif($isScheduled)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-violet-700">Scheduled</span>
                        @elseif($isExpired)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600">Expired</span>
                        @else
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Live</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-end gap-1 mt-3">
                        <button type="button" onclick="openSummaryEditNotificationModal({{ $notification->id }}, {{ Js::from($notification) }})" class="text-blue-600 hover:text-blue-700 text-xs font-medium px-2 py-1"><i class="fas fa-pen mr-1"></i>Edit</button>
                        <form method="POST" action="{{ route('admin.notifications.toggle', $notification) }}" class="inline" style="margin:0;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">
                            <button type="submit" class="text-amber-600 hover:text-amber-700 text-xs font-medium px-2 py-1"><i class="fas {{ $notification->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"></i>{{ $notification->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <button type="button" onclick="openSummaryDeleteManagedItemModal('notification', {{ $notification->id }}, '{{ addslashes($notification->title) }}')" class="text-red-600 hover:text-red-700 text-xs font-medium px-2 py-1"><i class="fas fa-trash-can mr-1"></i>Delete</button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">No notifications found.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Upcoming Deadlines</h3>
                <p class="text-xs text-gray-500 mt-1">Manage submission deadlines from Summary Reports</p>
            </div>
            <button type="button" onclick="openSummaryCreateDeadlineModal()" class="inline-flex items-center px-4 py-2 rounded-lg bg-orange-600 text-white text-xs font-semibold hover:bg-orange-700 transition-colors">
                <i class="fas fa-plus mr-1.5"></i> New Deadline
            </button>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left min-w-[900px]">
                <thead class="bg-gray-50/70 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Deadline Date</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Audience</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Days Left</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summaryUpcomingDeadlines as $deadline)
                        @php
                            $isPast = $deadline->deadline_date ? $deadline->deadline_date->isPast() : false;
                            $daysLeft = $isPast || !$deadline->deadline_date
                                ? 0
                                : (int) now()->startOfDay()->diffInDays($deadline->deadline_date, false);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors {{ $isPast ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900">{{ $deadline->title }}</p>
                                @if($deadline->description)
                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ Str::limit($deadline->description, 100) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $deadline->deadline_date ? $deadline->deadline_date->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($deadline->audience) }}</td>
                            <td class="px-4 py-3">
                                @if($deadline->is_active && !$isPast)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active</span>
                                @elseif($isPast)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Past Due</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($isPast)
                                    <span class="text-xs font-bold text-red-500">Passed</span>
                                @elseif($daysLeft <= 3)
                                    <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-full">{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}</span>
                                @elseif($daysLeft <= 7)
                                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full">{{ $daysLeft }} days</span>
                                @else
                                    <span class="text-xs font-semibold text-gray-600">{{ $daysLeft }} days</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="openSummaryEditDeadlineModal({{ $deadline->id }}, {{ Js::from($deadline) }})" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.deadlines.toggle', $deadline) }}" class="inline" style="margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">
                                        <button type="submit" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition" title="{{ $deadline->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $deadline->is_active ? 'fa-toggle-on text-emerald-500' : 'fa-toggle-off' }} text-sm"></i>
                                        </button>
                                    </form>

                                    <button type="button" onclick="openSummaryDeleteManagedItemModal('deadline', {{ $deadline->id }}, '{{ addslashes($deadline->title) }}')" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No deadlines found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100">
            @forelse($summaryUpcomingDeadlines as $deadline)
                @php
                    $isPast = $deadline->deadline_date ? $deadline->deadline_date->isPast() : false;
                    $daysLeft = $isPast || !$deadline->deadline_date
                        ? 0
                        : (int) now()->startOfDay()->diffInDays($deadline->deadline_date, false);
                @endphp
                <div class="p-4 {{ $isPast ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">{{ $deadline->title }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $deadline->deadline_date ? $deadline->deadline_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        @if(!$isPast && $daysLeft <= 7)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $daysLeft <= 3 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">{{ $daysLeft }}d left</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-end gap-1 mt-3">
                        <button type="button" onclick="openSummaryEditDeadlineModal({{ $deadline->id }}, {{ Js::from($deadline) }})" class="text-blue-600 hover:text-blue-700 text-xs font-medium px-2 py-1"><i class="fas fa-pen mr-1"></i>Edit</button>
                        <form method="POST" action="{{ route('admin.deadlines.toggle', $deadline) }}" class="inline" style="margin:0;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">
                            <button type="submit" class="text-amber-600 hover:text-amber-700 text-xs font-medium px-2 py-1"><i class="fas {{ $deadline->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"></i>{{ $deadline->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <button type="button" onclick="openSummaryDeleteManagedItemModal('deadline', {{ $deadline->id }}, '{{ addslashes($deadline->title) }}')" class="text-red-600 hover:text-red-700 text-xs font-medium px-2 py-1"><i class="fas fa-trash-can mr-1"></i>Delete</button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">No deadlines found.</div>
            @endforelse
        </div>
    </div>
</div>

<div id="summaryNotificationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 id="summaryNotificationModalTitle" class="text-lg font-bold text-gray-900">Create Notification</h3>
            <button type="button" onclick="closeSummaryNotificationModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="summaryNotificationForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div id="summaryNotificationMethodField"></div>
            <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                <input type="text" id="summary_notif_title" name="title" required maxlength="255" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Message *</label>
                <textarea id="summary_notif_message" name="message" required maxlength="2000" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select id="summary_notif_type" name="type" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="success">Success</option>
                        <option value="danger">Danger</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Audience</label>
                    <select id="summary_notif_audience" name="audience" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="all">All Users</option>
                        <option value="faculty">Faculty</option>
                        <option value="dean">Dean</option>
                        <option value="director">Director</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Publish Date</label>
                    <input type="datetime-local" id="summary_notif_published_at" name="published_at" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Expiry Date</label>
                    <input type="datetime-local" id="summary_notif_expires_at" name="expires_at" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeSummaryNotificationModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save Notification</button>
            </div>
        </form>
    </div>
</div>

<div id="summaryDeadlineModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 id="summaryDeadlineModalTitle" class="text-lg font-bold text-gray-900">Create Deadline</h3>
            <button type="button" onclick="closeSummaryDeadlineModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="summaryDeadlineForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div id="summaryDeadlineMethodField"></div>
            <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                <input type="text" id="summary_deadline_title" name="title" required maxlength="255" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="summary_deadline_description" name="description" maxlength="1000" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Deadline Date *</label>
                    <input type="date" id="summary_deadline_date" name="deadline_date" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Audience</label>
                    <select id="summary_deadline_audience" name="audience" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="all">All Users</option>
                        <option value="faculty">Faculty</option>
                        <option value="dean">Dean</option>
                        <option value="director">Director</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeSummaryDeadlineModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg">Save Deadline</button>
            </div>
        </form>
    </div>
</div>

<div id="summaryManagedDeleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Delete Item</h3>
            <p class="text-xs text-gray-500 mt-1">This action cannot be undone.</p>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-700">Are you sure you want to delete <strong id="summaryManagedDeleteName" class="text-gray-900"></strong>?</p>
            <form id="summaryManagedDeleteForm" method="POST" action="" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <input type="hidden" name="redirect_to" value="{{ $summaryNotifDeadlineRedirectUrl }}">
                <button type="button" onclick="closeSummaryDeleteManagedItemModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
            </form>
        </div>
    </div>
</div>
