@php
    $summaryUserRedirectUrl = route('faculty.summary-reports', ['category' => 'user-management', 'department' => 'all']);
@endphp

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

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Users</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $userManagementTotalUsers }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-lg"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Active</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $userManagementActiveUsers }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center">
                <i class="fas fa-user-check text-green-600 text-lg"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Inactive</p>
                <p class="text-2xl font-bold text-red-500 mt-1">{{ $userManagementInactiveUsers }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center">
                <i class="fas fa-user-xmark text-red-500 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-white flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">User Management</h3>
                <p class="text-xs text-gray-500 mt-1">Manage users with the same backend behavior as the admin module</p>
            </div>
            <button type="button" onclick="openSummaryAddUserModal()" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-1.5"></i> Add User
            </button>
        </div>

        <form method="GET" action="{{ route('faculty.summary-reports') }}" class="p-4 border-b border-gray-100 bg-gray-50/70 flex flex-col md:flex-row md:items-center gap-3">
            <input type="hidden" name="category" value="user-management">
            <input type="hidden" name="department" value="all">

            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $userManagementSearch }}" placeholder="Search by name, email, username, employee ID" class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="w-full md:w-64">
                <select name="user_department" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string) $userManagementDepartment === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">Apply</button>
                <a href="{{ route('faculty.summary-reports', ['category' => 'user-management', 'department' => 'all']) }}" class="px-4 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition-colors">Reset</a>
            </div>
        </form>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left min-w-[980px]">
                <thead class="bg-gray-50/70 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($userManagementUsers as $user)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-400">{{ $userManagementUsers->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover border border-gray-200">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                        <p class="text-[11px] text-gray-500">{{ $user->employee_id ?? 'No ID' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700">{{ $user->email }}</p>
                                <p class="text-[11px] text-gray-500">{{ $user->phone ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @foreach($user->roles() as $role)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium
                                        @if($role === 'admin') bg-purple-50 text-purple-700
                                        @elseif($role === 'director') bg-emerald-50 text-emerald-700
                                        @elseif($role === 'dean') bg-blue-50 text-blue-700
                                        @else bg-gray-100 text-gray-700
                                        @endif
                                    ">{{ $role === 'hr' ? 'Human Resource' : ucfirst($role) }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if(auth()->id() !== $user->id && $user->employee_id !== 'URS26-ADM00001')
                                <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="redirect_to" value="{{ $summaryUserRedirectUrl }}">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $user->is_active ? 'checked' : '' }}>
                                        <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                        <span class="ml-2 text-xs font-medium text-gray-600 w-14">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                    </label>
                                </form>
                                @else
                                    @if($user->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactive
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="openSummaryViewUserModal({{ $user->id }})" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    @if($user->employee_id !== 'URS26-ADM00001')
                                        <button type="button" onclick="openSummaryEditUserModal({{ $user->id }})" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        @if(auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" style="margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="{{ $summaryUserRedirectUrl }}">
                                                <button type="button" onclick="openSummaryDeleteModal('{{ addslashes($user->name) }}', this.form)" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete">
                                                    <i class="fas fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100">
            @forelse($userManagementUsers as $user)
            <div class="p-4">
                <div class="flex items-center gap-3">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-gray-500 truncate">{{ $user->email }}</p>
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($user->roles() as $role)
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">{{ $role === 'hr' ? 'Human Resource' : ucfirst($role) }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="openSummaryViewUserModal({{ $user->id }})" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition"><i class="fas fa-eye text-xs"></i></button>
                        @if($user->employee_id !== 'URS26-ADM00001')
                            <button type="button" onclick="openSummaryEditUserModal({{ $user->id }})" class="w-8 h-8 rounded-md flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition"><i class="fas fa-pen text-xs"></i></button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-sm text-gray-500">No users found.</div>
            @endforelse
        </div>

        @if($userManagementUsers && $userManagementUsers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-white flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Showing <span class="font-semibold text-gray-900">{{ $userManagementUsers->firstItem() }}</span>
                to <span class="font-semibold text-gray-900">{{ $userManagementUsers->lastItem() }}</span>
                of <span class="font-semibold text-gray-900">{{ $userManagementUsers->total() }}</span>
            </p>
            <div class="flex items-center gap-1">
                @if($userManagementUsers->onFirstPage())
                    <span class="w-8 h-8 rounded-md border border-gray-200 flex items-center justify-center text-gray-300 text-xs"><i class="fas fa-chevron-left"></i></span>
                @else
                    <a href="{{ $userManagementUsers->previousPageUrl() }}" class="w-8 h-8 rounded-md border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-xs"><i class="fas fa-chevron-left"></i></a>
                @endif

                @foreach($userManagementUsers->getUrlRange(max(1, $userManagementUsers->currentPage() - 1), min($userManagementUsers->lastPage(), $userManagementUsers->currentPage() + 1)) as $page => $url)
                    @if($page == $userManagementUsers->currentPage())
                        <span class="w-8 h-8 rounded-md bg-blue-600 text-white flex items-center justify-center text-xs font-semibold">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center text-xs">{{ $page }}</a>
                    @endif
                @endforeach

                @if($userManagementUsers->hasMorePages())
                    <a href="{{ $userManagementUsers->nextPageUrl() }}" class="w-8 h-8 rounded-md border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-xs"><i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="w-8 h-8 rounded-md border border-gray-200 flex items-center justify-center text-gray-300 text-xs"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="summaryUserDeleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Delete User</h3>
            <p class="text-xs text-gray-500 mt-1">This action cannot be undone.</p>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-700">Are you sure you want to delete <strong id="summaryDeleteUserName" class="text-gray-900"></strong>?</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="closeSummaryDeleteModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="button" onclick="confirmSummaryDelete()" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="summaryAddUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Add User</h3>
            <button type="button" onclick="closeSummaryAddUserModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-5 space-y-5">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ $summaryUserRedirectUrl }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                    <input type="email" name="email" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username *</label>
                    <input type="text" name="username" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="phone" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password *</label>
                    <div class="relative">
                        <input type="password" id="summary_add_password" name="password" required class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <button type="button" onclick="toggleSummaryPasswordVisibility('summary_add_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><i class="fas fa-eye" id="summary_add_password_eye"></i></button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password *</label>
                    <div class="relative">
                        <input type="password" id="summary_add_password_confirmation" name="password_confirmation" required class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <button type="button" onclick="toggleSummaryPasswordVisibility('summary_add_password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><i class="fas fa-eye" id="summary_add_password_confirmation_eye"></i></button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Roles *</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    @foreach($userManagementRoles as $role)
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="roles[]" id="summary_add_role_{{ $role }}" value="{{ $role }}" onchange="handleSummaryAddRoleSelection()" class="w-4 h-4 text-blue-600 rounded border-gray-300">
                        <span class="ml-2">{{ $role === 'hr' ? 'Human Resource' : ucfirst($role) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="summary_add_org_fields" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <select name="department_id" id="summary_add_department_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select a department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation</label>
                    <select name="designation_id" id="summary_add_designation_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select a designation</option>
                        @foreach($userManagementDesignations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="summary_add_employment_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Employment Status</label>
                    <select name="employment_status" id="summary_add_employment_status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select status</option>
                        <option value="Permanent">Permanent</option>
                        <option value="Casual">Casual</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Emergency Laborer">Emergency Laborer</option>
                        <option value="Part Time">Part Time</option>
                    </select>
                </div>
            </div>

            <label class="inline-flex items-center text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 rounded border-gray-300">
                <span class="ml-2">Active</span>
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeSummaryAddUserModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- View User Modal -->
<div id="summaryViewUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">View User</h3>
            <button type="button" onclick="closeSummaryViewUserModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-5" id="summaryViewUserLoading">
            <div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>
        </div>
        <div class="p-5 hidden" id="summaryViewUserData">
            <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                <img id="summaryViewUserPhoto" src="" alt="" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                <div>
                    <h4 id="summaryViewUserName" class="text-lg font-bold text-gray-900"></h4>
                    <p id="summaryViewUserEmployeeId" class="text-sm text-gray-500"></p>
                    <div id="summaryViewUserRoles" class="flex gap-1.5 mt-2 flex-wrap"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Email</p><p id="summaryViewUserEmail" class="text-gray-900 font-medium break-all"></p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Username</p><p id="summaryViewUserUsername" class="text-gray-900 font-medium"></p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Phone</p><p id="summaryViewUserPhone" class="text-gray-900 font-medium"></p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Department</p><p id="summaryViewUserDepartment" class="text-gray-900 font-medium"></p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Designation</p><p id="summaryViewUserDesignation" class="text-gray-900 font-medium"></p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Employment Status</p><p id="summaryViewUserEmploymentStatus" class="text-gray-900 font-medium"></p></div>
                <div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-500 mb-1">Status</p><div id="summaryViewUserStatus"></div></div>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" onclick="closeSummaryViewUserModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
            <button type="button" id="summaryViewToEditBtn" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Edit</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="summaryEditUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Edit User</h3>
                <p id="summaryEditUserSubtitle" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <button type="button" onclick="closeSummaryEditUserModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        <div class="p-5" id="summaryEditUserLoading">
            <div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>
        </div>

        <form id="summaryEditUserForm" method="POST" action="" class="p-5 space-y-5 hidden">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ $summaryUserRedirectUrl }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                    <input type="text" id="summary_edit_name" name="name" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                    <input type="email" id="summary_edit_email" name="email" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username *</label>
                    <input type="text" id="summary_edit_username" name="username" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input type="text" id="summary_edit_phone" name="phone" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-xs text-gray-400">(optional)</span></label>
                    <div class="relative">
                        <input type="password" id="summary_edit_password" name="password" class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <button type="button" onclick="toggleSummaryPasswordVisibility('summary_edit_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><i class="fas fa-eye" id="summary_edit_password_eye"></i></button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="summary_edit_password_confirmation" name="password_confirmation" class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <button type="button" onclick="toggleSummaryPasswordVisibility('summary_edit_password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><i class="fas fa-eye" id="summary_edit_password_confirmation_eye"></i></button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Roles *</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    @foreach($userManagementRoles as $role)
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="roles[]" id="summary_edit_role_{{ $role }}" value="{{ $role }}" onchange="handleSummaryEditRoleSelection()" class="w-4 h-4 text-blue-600 rounded border-gray-300">
                        <span class="ml-2">{{ $role === 'hr' ? 'Human Resource' : ucfirst($role) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="summary_edit_org_fields" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <select name="department_id" id="summary_edit_department_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select a department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation</label>
                    <select name="designation_id" id="summary_edit_designation_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select a designation</option>
                        @foreach($userManagementDesignations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="summary_edit_employment_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Employment Status</label>
                    <select name="employment_status" id="summary_edit_employment_status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select status</option>
                        <option value="Permanent">Permanent</option>
                        <option value="Casual">Casual</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Emergency Laborer">Emergency Laborer</option>
                        <option value="Part Time">Part Time</option>
                    </select>
                </div>
            </div>

            <label class="inline-flex items-center text-sm text-gray-700">
                <input type="checkbox" id="summary_edit_is_active" name="is_active" value="1" class="w-4 h-4 text-blue-600 rounded border-gray-300">
                <span class="ml-2">Active</span>
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeSummaryEditUserModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Update User</button>
            </div>
        </form>
    </div>
</div>

