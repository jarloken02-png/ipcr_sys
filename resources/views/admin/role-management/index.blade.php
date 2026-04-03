@extends('layouts.admin')

@section('title', 'Role & Department Management')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
        <div class="flex-1">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Role & Department Management</h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">
                Manage system roles, departments, and designations
            </p>
        </div>
    </div>
@endsection

@section('content')
    @php $activeTab = request('tab', 'roles'); @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300 group">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Roles</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ count($roles) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center transform group-hover:rotate-3 transition-transform">
                <i class="fas fa-user-tag text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300 group">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Departments</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ count($departments) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center transform group-hover:-rotate-3 transition-transform">
                <i class="fas fa-building text-emerald-600 dark:text-emerald-400 text-lg"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-1 p-5 lg:p-6 flex items-center justify-between transition-all duration-300 group">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">Designations</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ count($designations) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center transform group-hover:rotate-3 transition-transform">
                <i class="fas fa-briefcase text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl border border-gray-100 dark:border-gray-700 mb-6 shadow-sm overflow-hidden transition-all duration-300">
        <div class="flex">
            <button onclick="switchTab('roles')" id="tab-roles"
                class="tab-btn flex-1 px-4 py-4 text-sm font-semibold tracking-wide text-center transition-all duration-300 border-b-2 {{ $activeTab === 'roles' ? 'border-purple-500 text-purple-600 dark:text-purple-400 bg-purple-50/50 dark:bg-purple-900/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                <i class="fas fa-user-tag mr-2 mb-1 sm:mb-0 block sm:inline"></i> Roles
            </button>
            <button onclick="switchTab('departments')" id="tab-departments"
                class="tab-btn flex-1 px-4 py-4 text-sm font-semibold tracking-wide text-center transition-all duration-300 border-b-2 {{ $activeTab === 'departments' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-900/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                <i class="fas fa-building mr-2 mb-1 sm:mb-0 block sm:inline"></i> Departments
            </button>
            <button onclick="switchTab('designations')" id="tab-designations"
                class="tab-btn flex-1 px-4 py-4 text-sm font-semibold tracking-wide text-center transition-all duration-300 border-b-2 {{ $activeTab === 'designations' ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                <i class="fas fa-briefcase mr-2 mb-1 sm:mb-0 block sm:inline"></i> Designations
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════ ROLES TAB ═══════════════════════════════════ --}}
    <div id="panel-roles" class="tab-panel {{ $activeTab !== 'roles' ? 'hidden' : '' }}">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm transition-all duration-300 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between bg-white/50 dark:bg-gray-800/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">All Roles</h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">Manage system roles and permissions</p>
                </div>
                <button onclick="openModal('addRoleModal')" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all duration-300 shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Add Role
                </button>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50">
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">#</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Role Name</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Acronym</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Permissions</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Users</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($roles as $index => $role)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors group">
                                <td class="px-6 py-4 text-xs font-medium text-gray-400 dark:text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold
                                        @if($role->name === 'admin') bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800
                                        @elseif($role->name === 'director') bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                        @elseif($role->name === 'dean') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                                        @elseif($role->name === 'hr') bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800
                                        @else bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700
                                        @endif
                                    ">
                                        {{ str_replace('_', ' ', $role->name) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-900 dark:text-gray-200 font-semibold tracking-tight">{{ $role->acronym }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                        <i class="fas fa-shield-alt text-[10px]"></i> {{ $role->permissions->count() }} perm(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-users text-[10px] mr-1.5"></i> {{ $role->user_roles_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                        <button onclick="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', '{{ $role->acronym }}', {{ json_encode($role->permissions->pluck('key')) }})" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        @if(strtolower($role->name) !== 'admin')
                                        <form method="POST" action="{{ route('admin.role-management.roles.destroy', $role) }}" class="inline deleteForm" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="openRmDeleteModal('{{ addslashes($role->name) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                                <i class="fas fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                        @else
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Admin role is protected">
                                            <i class="fas fa-lock text-xs"></i>
                                        </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                    <i class="fas fa-user-tag text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden space-y-3 p-4">
                @forelse($roles as $role)
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700 flex items-start justify-between">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider
                                @if($role->name === 'admin') bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300
                                @elseif($role->name === 'director') bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300
                                @elseif($role->name === 'dean') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                @elseif($role->name === 'hr') bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300
                                @else bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300
                                @endif
                            ">{{ str_replace('_', ' ', $role->name) }}</span>
                            <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                <p><span class="font-mono font-medium text-gray-900 dark:text-gray-200">{{ $role->acronym }}</span></p>
                                <p><i class="fas fa-users text-[10px]"></i> {{ $role->user_roles_count }} users</p>
                                <p><i class="fas fa-shield-alt text-[10px]"></i> {{ $role->permissions->count() }} permissions</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 self-end">
                            <button onclick="openEditRoleModal({{ $role->id }}, '{{ addslashes($role->name) }}', '{{ $role->acronym }}', {{ json_encode($role->permissions->pluck('key')) }})" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 dark:hover:bg-emerald-900/30 dark:hover:border-emerald-800 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            @if(strtolower($role->name) !== 'admin')
                            <form method="POST" action="{{ route('admin.role-management.roles.destroy', $role) }}" class="inline deleteForm" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="openRmDeleteModal('{{ addslashes($role->name) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200 dark:hover:bg-red-900/30 dark:hover:border-red-800 transition-colors">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </form>
                            @else
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Admin role is protected">
                                <i class="fas fa-lock text-xs"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-400 dark:text-gray-500 py-12 text-sm">No roles found.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════ DEPARTMENTS TAB ═══════════════════════════════════ --}}
    <div id="panel-departments" class="tab-panel {{ $activeTab !== 'departments' ? 'hidden' : '' }}">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm transition-all duration-300 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between bg-white/50 dark:bg-gray-800/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">All Departments</h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">Manage academic and administrative departments</p>
                </div>
                <button onclick="openModal('addDepartmentModal')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all duration-300 shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Add Department
                </button>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50">
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">#</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Department Name</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Code</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Users</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($departments as $index => $dept)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors group">
                                <td class="px-6 py-4 text-xs font-medium text-gray-400 dark:text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ $dept->name }}</td>
                                <td class="px-6 py-4 text-sm font-mono text-emerald-600 dark:text-emerald-400 font-semibold tracking-tight">{{ $dept->code }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-users text-[10px] mr-1.5"></i> {{ $dept->users_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                        <button onclick="openEditDeptModal({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->code }}')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.role-management.departments.destroy', $dept) }}" class="inline deleteForm" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="openRmDeleteModal('{{ addslashes($dept->name) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                                <i class="fas fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                    <i class="fas fa-building text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                                    No departments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden space-y-3 p-4">
                @forelse($departments as $dept)
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700 flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $dept->name }}</p>
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <p><span class="font-mono font-medium text-emerald-600 dark:text-emerald-400">{{ $dept->code }}</span></p>
                                <p><i class="fas fa-users text-[10px]"></i> {{ $dept->users_count }} users</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 self-end">
                            <button onclick="openEditDeptModal({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->code }}')" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 dark:hover:bg-emerald-900/30 dark:hover:border-emerald-800 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.role-management.departments.destroy', $dept) }}" class="inline deleteForm" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="openRmDeleteModal('{{ addslashes($dept->name) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200 dark:hover:bg-red-900/30 dark:hover:border-red-800 transition-colors">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-400 dark:text-gray-500 py-12 text-sm">No departments found.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════ DESIGNATIONS TAB ═══════════════════════════════════ --}}
    <div id="panel-designations" class="tab-panel {{ $activeTab !== 'designations' ? 'hidden' : '' }}">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm transition-all duration-300 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-4 items-center justify-between bg-white/50 dark:bg-gray-800/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">All Designations</h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">Manage job titles and designations</p>
                </div>
                <button onclick="openModal('addDesignationModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all duration-300 shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Add Designation
                </button>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50">
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">#</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Title</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Code</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Users</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($designations as $index => $desig)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors group">
                                <td class="px-6 py-4 text-xs font-medium text-gray-400 dark:text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ $desig->title }}</td>
                                <td class="px-6 py-4 text-sm font-mono text-blue-600 dark:text-blue-400 font-semibold tracking-tight">{{ $desig->code }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-users text-[10px] mr-1.5"></i> {{ $desig->users_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                        <button onclick="openEditDesigModal({{ $desig->id }}, '{{ addslashes($desig->title) }}', '{{ $desig->code }}')" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.role-management.designations.destroy', $desig) }}" class="inline deleteForm" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="openRmDeleteModal('{{ addslashes($desig->title) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                                <i class="fas fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                    <i class="fas fa-briefcase text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                                    No designations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden space-y-3 p-4">
                @forelse($designations as $desig)
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-100 dark:border-gray-700 flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $desig->title }}</p>
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <p><span class="font-mono font-medium text-blue-600 dark:text-blue-400">{{ $desig->code }}</span></p>
                                <p><i class="fas fa-users text-[10px]"></i> {{ $desig->users_count }} users</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 self-end">
                            <button onclick="openEditDesigModal({{ $desig->id }}, '{{ addslashes($desig->title) }}', '{{ $desig->code }}')" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 dark:hover:bg-emerald-900/30 dark:hover:border-emerald-800 transition-colors">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.role-management.designations.destroy', $desig) }}" class="inline deleteForm" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="openRmDeleteModal('{{ addslashes($desig->title) }}', this.form)" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200 dark:hover:bg-red-900/30 dark:hover:border-red-800 transition-colors">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-400 dark:text-gray-500 py-12 text-sm">No designations found.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- ═════ Add Role Modal ═════ --}}
    <div id="addRoleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 animate-scale-in transition-colors max-h-[90vh] flex flex-col">
            <div class="p-5 border-b dark:border-gray-700 flex justify-between items-center flex-shrink-0">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add New Role</h2>
                <button onclick="closeModal('addRoleModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.role-management.roles.store') }}" class="p-5 space-y-4 overflow-y-auto flex-1">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name *</label>
                        <input type="text" name="name" required placeholder="e.g. chairperson" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acronym *</label>
                        <input type="text" name="acronym" required placeholder="e.g. CHP" maxlength="10" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><i class="fas fa-shield-alt mr-1 text-indigo-500"></i> Permissions</label>
                    <div class="space-y-3">
                        @foreach($permissions as $group => $perms)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">{{ $group }}</h4>
                                    <button type="button" onclick="toggleGroupCheckboxes(this, 'add')" class="text-[10px] text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">Toggle All</button>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm['key'] }}" class="w-3.5 h-3.5 text-indigo-600 rounded border-gray-300 dark:border-gray-500 focus:ring-indigo-500 perm-checkbox">
                                            <span class="text-xs text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition">{{ $perm['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addRoleModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition shadow-sm"><i class="fas fa-save mr-1"></i> Create Role</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═════ Edit Role Modal ═════ --}}
    <div id="editRoleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 animate-scale-in transition-colors max-h-[90vh] flex flex-col">
            <div class="p-5 border-b dark:border-gray-700 flex justify-between items-center flex-shrink-0">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Role</h2>
                <button onclick="closeModal('editRoleModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
            </div>
            <form id="editRoleForm" method="POST" class="p-5 space-y-4 overflow-y-auto flex-1">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name *</label>
                        <input type="text" name="name" id="editRoleName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acronym *</label>
                        <input type="text" name="acronym" id="editRoleAcronym" required maxlength="10" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><i class="fas fa-shield-alt mr-1 text-indigo-500"></i> Permissions</label>
                    <div class="space-y-3" id="editPermissionsContainer">
                        @foreach($permissions as $group => $perms)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">{{ $group }}</h4>
                                    <button type="button" onclick="toggleGroupCheckboxes(this, 'edit')" class="text-[10px] text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">Toggle All</button>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm['key'] }}" class="w-3.5 h-3.5 text-indigo-600 rounded border-gray-300 dark:border-gray-500 focus:ring-indigo-500 edit-perm-checkbox">
                                            <span class="text-xs text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition">{{ $perm['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('editRoleModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition shadow-sm"><i class="fas fa-save mr-1"></i> Update Role</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═════ Add Department Modal ═════ --}}
    <div id="addDepartmentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 animate-scale-in transition-colors">
            <div class="p-5 border-b dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add New Department</h2>
                <button onclick="closeModal('addDepartmentModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.role-management.departments.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name *</label>
                    <input type="text" name="name" required placeholder="e.g. College of Engineering" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                    <input type="text" name="code" required placeholder="e.g. COE" maxlength="10" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used in auto-generated employee IDs (e.g. URS26-COE12345)</p>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addDepartmentModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm"><i class="fas fa-save mr-1"></i> Create Department</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═════ Edit Department Modal ═════ --}}
    <div id="editDepartmentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 animate-scale-in transition-colors">
            <div class="p-5 border-b dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Department</h2>
                <button onclick="closeModal('editDepartmentModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
            </div>
            <form id="editDeptForm" method="POST" class="p-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name *</label>
                    <input type="text" name="name" id="editDeptName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                    <input type="text" name="code" id="editDeptCode" required maxlength="10" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('editDepartmentModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm"><i class="fas fa-save mr-1"></i> Update Department</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═════ Add Designation Modal ═════ --}}
    <div id="addDesignationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 animate-scale-in transition-colors">
            <div class="p-5 border-b dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Add New Designation</h2>
                <button onclick="closeModal('addDesignationModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.role-management.designations.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Senior Lecturer" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                    <input type="text" name="code" required placeholder="e.g. SR_LEC" maxlength="20" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('addDesignationModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm"><i class="fas fa-save mr-1"></i> Create Designation</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═════ Edit Designation Modal ═════ --}}
    <div id="editDesignationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 animate-scale-in transition-colors">
            <div class="p-5 border-b dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Designation</h2>
                <button onclick="closeModal('editDesignationModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"><i class="fas fa-times"></i></button>
            </div>
            <form id="editDesigForm" method="POST" class="p-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
                    <input type="text" name="title" id="editDesigTitle" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                    <input type="text" name="code" id="editDesigCode" required maxlength="20" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('editDesignationModal')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm"><i class="fas fa-save mr-1"></i> Update Designation</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═════ Delete Confirmation Modal ═════ --}}
    <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
        <div class="absolute inset-0 bg-black/40"></div>
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
                Are you sure you want to delete <strong id="deleteItemName" class="text-gray-900 dark:text-white">this item</strong>? This action will permanently remove it and all associated data.
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeRmDeleteModal()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">Cancel</button>
                <button type="button" onclick="confirmRmDelete()" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script src="{{ Vite::asset('resources/js/admin_role_management_index.js') }}"></script>
@endpush
