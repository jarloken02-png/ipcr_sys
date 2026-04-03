@extends('layouts.admin')

@section('title', 'User Management')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0 w-full">
        <div class="flex-1">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">User Management</h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Manage your {{ $totalUsers }} users</p>
        </div>
        <button onclick="openAddUserModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold flex items-center gap-2 text-sm transition-all duration-300 shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Total Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalUsers }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center transform rotate-3">
                <i class="fas fa-users text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Active</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-500 mt-1">{{ $activeUsers }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center transform -rotate-3">
                <i class="fas fa-user-check text-green-600 dark:text-green-400 text-lg"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Inactive</p>
                <p class="text-2xl font-bold text-red-500 dark:text-red-400 mt-1">{{ $inactiveUsers }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center transform rotate-3">
                <i class="fas fa-user-xmark text-red-500 dark:text-red-400 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 mb-6 transition-colors">
        <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm" class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Search -->
            <div class="relative flex-1 w-full sm:w-auto">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                <input
                    type="text"
                    name="search"
                    id="searchInput"
                    value="{{ request('search') }}"
                    placeholder="Search by name, email, or username..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 focus:bg-white dark:focus:bg-gray-600 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition"
                >
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Department Filter -->
                <div class="relative flex-1 sm:w-48">
                    <i class="fas fa-building absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                    <select
                        name="department"
                        id="departmentFilter"
                        class="w-full pl-10 pr-8 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-600 text-gray-900 dark:text-white transition cursor-pointer appearance-none"
                    >
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-[10px] pointer-events-none"></i>
                </div>

                @if(request('search') || request('department'))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition flex items-center gap-2 whitespace-nowrap" title="Clear filters">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-colors">
        <table class="w-full text-left">
            <thead class="bg-gray-50/50 dark:bg-gray-700/30">
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Contact</th>
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Department</th>
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400 dark:text-gray-500 font-medium">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $user->employee_id ?? 'No ID' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden xl:table-cell">
                            <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $user->email }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $user->phone ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @foreach($user->roles() as $role)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($role === 'admin') bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300
                                    @elseif($role === 'director') bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300
                                    @elseif($role === 'dean') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                    @else bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300
                                    @endif
                                ">{{ ucfirst($role) }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-sm text-gray-600 dark:text-gray-400 truncate block max-w-[140px]">{{ $user->department?->name ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if(auth()->user()->id !== $user->id && $user->employee_id !== 'URS26-ADM00001')
                                <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $user->is_active ? 'checked' : '' }}>
                                        <div class="relative w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                        <span class="ms-2 text-xs font-medium text-gray-600 dark:text-gray-400 w-14">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                    </label>
                                </form>
                            @else
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactive
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="openViewUserModal({{ $user->id }})" class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="View">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                @if($user->employee_id !== 'URS26-ADM00001')
                                    <button type="button" onclick="openEditUserModal({{ $user->id }})" class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    @if(auth()->user()->id !== $user->id)
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline deleteForm" style="margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="openConfirmationModal('{{ addslashes($user->name) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Delete">
                                                    <i class="fas fa-trash-can text-sm"></i>
                                                </button>
                                            </form>
                                    @endif
                                @else
                                    <span class="w-7 h-7 rounded-md flex items-center justify-center text-gray-300 dark:text-gray-600" title="Protected">
                                        <i class="fas fa-lock text-xs"></i>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                            <i class="fas fa-users text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                            No users found. <a href="{{ route('admin.users.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Create one now</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-2">
        @forelse($users as $user)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-3 flex items-center gap-3 transition-colors">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                        @if($user->is_active)
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 mt-0.5">
                        @foreach($user->roles() as $role)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium
                                @if($role === 'admin') bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300
                                @elseif($role === 'director') bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300
                                @elseif($role === 'dean') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                @else bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300
                                @endif
                            ">{{ ucfirst($role) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-0.5 flex-shrink-0">
                    <button type="button" onclick="openViewUserModal({{ $user->id }})" class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    @if($user->employee_id !== 'URS26-ADM00001')
                        <button type="button" onclick="openEditUserModal({{ $user->id }})" class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        @if(auth()->user()->id !== $user->id)
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline deleteForm" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="openConfirmationModal('{{ $user->name }}', this.form)" class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="w-7 h-7 rounded-md flex items-center justify-center text-gray-300 dark:text-gray-600">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 dark:text-gray-500 py-12 text-sm">
                <i class="fas fa-users text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                No users found. <a href="{{ route('admin.users.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Create one now</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="mt-4 flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 transition-colors">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Showing <span class="font-medium text-gray-900 dark:text-white">{{ $users->firstItem() }}</span> to <span class="font-medium text-gray-900 dark:text-white">{{ $users->lastItem() }}</span> of <span class="font-medium text-gray-900 dark:text-white">{{ $users->total() }}</span>
            </p>
            <div class="flex items-center gap-1">
                @if($users->onFirstPage())
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 dark:text-gray-600 text-xs">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs transition">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif

                @foreach($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                    @if($page == $users->currentPage())
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-600 text-white text-xs font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs transition">{{ $page }}</a>
                    @endif
                @endforeach

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs transition">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 dark:text-gray-600 text-xs">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('modals')
    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 animate-scale-in transition-colors z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                    <i class="fas fa-trash-can text-red-500 dark:text-red-400"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete User</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-5">
                Are you sure you want to delete <strong id="deleteUserName" class="text-gray-900 dark:text-white">this user</strong>? This action will permanently remove their data from the system.
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeConfirmationModal()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">Cancel</button>
                <button type="button" onclick="confirmDelete()" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-4xl mx-4 animate-scale-in relative flex flex-col max-h-[90vh] transition-colors">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center shrink-0">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create New User</h2>
                <button onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto">
                <!-- Validation Errors (Inline) -->
                <div id="modalErrors" class="hidden mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <h3 class="text-red-800 dark:text-red-300 font-semibold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 dark:text-red-400 text-sm" id="modalErrorList"></ul>
                </div>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <h3 class="text-red-800 dark:text-red-300 font-semibold mb-2">Please fix the following errors:</h3>
                        <ul class="list-disc list-inside text-red-700 dark:text-red-400 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div id="openAddUserModalOnLoad" class="hidden" aria-hidden="true"></div>
                @endif

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf
    
                    <!-- Personal Information -->
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
    
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            </div>
    
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            </div>
    
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username *</label>
                                <input type="text" name="username" id="username" value="{{ old('username') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            </div>
    
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>
    
                    <!-- Account Information -->
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Information</h3>
    
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password *</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required class="w-full px-4 py-2 pr-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <button type="button" onclick="togglePasswordVisibility('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 z-10 cursor-pointer">
                                        <i class="fas fa-eye" id="password_eye_open"></i>
                                        <i class="fas fa-eye-slash hidden" id="password_eye_closed"></i>
                                    </button>
                                </div>
                            </div>
    
                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password *</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-2 pr-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 z-10 cursor-pointer">
                                        <i class="fas fa-eye" id="password_confirmation_eye_open"></i>
                                        <i class="fas fa-eye-slash hidden" id="password_confirmation_eye_closed"></i>
                                    </button>
                                </div>
                            </div>
    
                            <!-- Roles (Multiple Selection) -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Roles *</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                                    @php
                                        $selectedRoles = old('roles', []);
                                    @endphp
                                    
                                    @foreach($roles as $role)
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                name="roles[]" 
                                                id="role_{{ $role }}" 
                                                value="{{ $role }}"
                                                {{ in_array($role, $selectedRoles) ? 'checked' : '' }}
                                                onchange="window.handleAddRoleSelection && window.handleAddRoleSelection()"
                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-500 focus:ring-blue-500 dark:bg-gray-600 role-checkbox transition-colors"
                                            >
                                            <label for="role_{{ $role }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                {{ $role == 'hr' ? 'Human Resource' : ucfirst($role) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Select one or more roles</p>
                            </div>
    
                            <!-- Status -->
                            <div>
                                <label for="form_is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <div class="flex items-center gap-2 mt-2">
                                    <input type="checkbox" name="is_active" id="form_is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-500 focus:ring-blue-500 dark:bg-gray-600 transition-colors">
                                    <label for="form_is_active" class="text-gray-700 dark:text-gray-300">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Department & Designation -->
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Department & Designation</h3>
    
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Department -->
                            <div id="addDeptWrapper">
                                <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                <select name="department_id" id="department_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <option value="">Select a department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
    
                            <!-- Designation -->
                            <div id="addDesigWrapper">
                                <label for="designation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                                <select name="designation_id" id="designation_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <option value="">Select a designation</option>
                                    @foreach($designations as $desig)
                                        <option value="{{ $desig->id }}" {{ old('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Employment Status (Staff) -->
                            <div id="employmentStatusWrapper">
                                <label for="employment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employment Status</label>
                                <select name="employment_status" id="employment_status" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                    <option value="">Select status</option>
                                    <option value="Permanent" {{ old('employment_status') === 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                    <option value="Casual" {{ old('employment_status') === 'Casual' ? 'selected' : '' }}>Casual</option>
                                    <option value="Contractual" {{ old('employment_status') === 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                    <option value="Emergency Laborer" {{ old('employment_status') === 'Emergency Laborer' ? 'selected' : '' }}>Emergency Laborer</option>
                                    <option value="Part Time" {{ old('employment_status') === 'Part Time' ? 'selected' : '' }}>Part Time</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Used for staff and part-time faculty records under faculty role.</p>
                            </div>
                        </div>
                    </div>
    
                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 transition shadow-sm">
                            <i class="fas fa-save"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div id="viewUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl mx-4 animate-scale-in relative flex flex-col max-h-[90vh]">
            <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center shrink-0">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">View User</h2>
                <button onclick="closeViewUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto" id="viewUserContent">
                <!-- Loading State -->
                <div id="viewUserLoading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="viewUserData" class="hidden">
                    <!-- User Header -->
                    <div class="flex items-center gap-4 mb-6 pb-6 border-b dark:border-gray-700">
                        <img id="viewUserPhoto" src="" alt="" class="w-20 h-20 rounded-xl object-cover border-2 border-gray-200 dark:border-gray-600">
                        <div>
                            <h3 id="viewUserName" class="text-xl font-bold text-gray-900 dark:text-white"></h3>
                            <p id="viewUserEmployeeId" class="text-sm text-gray-500 dark:text-gray-400"></p>
                            <div id="viewUserRoles" class="flex gap-2 mt-2 flex-wrap"></div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Personal Information</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Full Name</p>
                                <p id="viewUserFullName" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Email</p>
                                <p id="viewUserEmail" class="text-sm font-medium text-gray-900 dark:text-white break-all"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Username</p>
                                <p id="viewUserUsername" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Phone</p>
                                <p id="viewUserPhone" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Account & Organization -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Account & Organization</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Status</p>
                                <span id="viewUserStatus"></span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Department</p>
                                <p id="viewUserDepartment" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Designation</p>
                                <p id="viewUserDesignation" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Employment Status</p>
                                <p id="viewUserEmploymentStatus" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t dark:border-gray-700 px-6 py-4 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeViewUserModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Close
                </button>
                <button type="button" id="viewToEditBtn" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fas fa-pen text-xs"></i> Edit User
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-4xl mx-4 animate-scale-in relative flex flex-col max-h-[90vh]">
            <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit User</h2>
                    <p id="editUserSubtitle" class="text-sm text-gray-500 dark:text-gray-400"></p>
                </div>
                <button onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto">
                <!-- Loading State -->
                <div id="editUserLoading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- Edit Form (hidden until loaded) -->
                <div id="editUserFormWrapper" class="hidden">
                    <!-- Validation Errors -->
                    <div id="editModalErrors" class="hidden mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <h3 class="text-red-800 dark:text-red-400 font-semibold mb-2 text-sm">Please fix the following errors:</h3>
                        <ul class="list-disc list-inside text-red-700 dark:text-red-300 text-sm" id="editModalErrorList"></ul>
                    </div>

                    <form id="editUserForm" method="POST" action="" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information -->
                        <div class="border-b dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                                    <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                                    <input type="email" name="email" id="edit_email" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label for="edit_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username *</label>
                                    <input type="text" name="username" id="edit_username" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label for="edit_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                                    <input type="text" name="phone" id="edit_phone" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="border-b dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
                                    <div class="relative">
                                        <input type="password" name="password" id="edit_password" class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <button type="button" onclick="toggleEditPasswordVisibility('edit_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 z-10 cursor-pointer">
                                            <i class="fas fa-eye" id="edit_password_eye_open"></i>
                                            <i class="fas fa-eye-slash hidden" id="edit_password_eye_closed"></i>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label for="edit_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                                    <div class="relative">
                                        <input type="password" name="password_confirmation" id="edit_password_confirmation" class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <button type="button" onclick="toggleEditPasswordVisibility('edit_password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 z-10 cursor-pointer">
                                            <i class="fas fa-eye" id="edit_password_confirmation_eye_open"></i>
                                            <i class="fas fa-eye-slash hidden" id="edit_password_confirmation_eye_closed"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Roles -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Roles *</label>
                                    <div class="space-y-2 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                                        @foreach($roles as $role)
                                            <div class="flex items-center">
                                                <input type="checkbox" name="roles[]" id="edit_role_{{ $role }}" value="{{ $role }}" class="w-4 h-4 text-blue-600 rounded edit-role-checkbox" onchange="window.handleEditRoleSelection && window.handleEditRoleSelection()">
                                                <label for="edit_role_{{ $role }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                    {{ $role == 'hr' ? 'Human Resource' : ucfirst($role) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Select one or more roles</p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="edit_is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 text-blue-600 rounded">
                                        <label for="edit_is_active" class="text-gray-700 dark:text-gray-300 text-sm">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Department & Designation -->
                        <div class="border-b dark:border-gray-700 pb-6" id="editDeptSection">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Department & Designation</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div id="editDeptWrapper">
                                    <label for="edit_department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                    <select name="department_id" id="edit_department_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="">Select a department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="editDesigWrapper">
                                    <label for="edit_designation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                                    <select name="designation_id" id="edit_designation_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="">Select a designation</option>
                                        @foreach($designations as $desig)
                                            <option value="{{ $desig->id }}">{{ $desig->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="editEmploymentStatusWrapper">
                                    <label for="edit_employment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employment Status</label>
                                    <select name="employment_status" id="edit_employment_status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="">Select status</option>
                                        <option value="Permanent">Permanent</option>
                                        <option value="Casual">Casual</option>
                                        <option value="Contractual">Contractual</option>
                                        <option value="Emergency Laborer">Emergency Laborer</option>
                                        <option value="Part Time">Part Time</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 transition shadow-sm text-sm">
                                <i class="fas fa-save"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endpush