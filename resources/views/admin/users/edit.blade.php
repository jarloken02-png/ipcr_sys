@extends('layouts.admin')

@section('title', 'Edit User')

@section('header')
    <div class="flex-1 min-w-0">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit User</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->name }}</p>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    @vite(['resources/css/admin_users_edit.css'])
@endpush

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center gap-2 text-sm font-medium transition-colors">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl p-4">
            <h3 class="text-red-800 dark:text-red-300 font-semibold mb-2 text-sm">Please fix the following errors:</h3>
            <ul class="list-disc list-inside text-red-700 dark:text-red-400 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Photo Section (Left/Top on mobile, Left on desktop) -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 lg:p-6 lg:sticky lg:top-0 transition-colors">
                            <h3 class="text-base lg:text-lg font-bold text-gray-900 dark:text-white mb-3 lg:mb-4">Profile Photo</h3>

                            <!-- Current Profile Photo -->
                            <div class="mb-4 lg:mb-6">
                                <div class="w-full aspect-square bg-gray-50 dark:bg-gray-700/50 rounded-2xl mb-3 flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-600 overflow-hidden">
                                    @if($user->hasProfilePhoto())
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-gray-300 dark:text-gray-500 text-6xl"></i>
                                    @endif
                                </div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 text-center uppercase tracking-wide">@if($user->hasProfilePhoto())Current Profile Photo @else No Profile Photo @endif</p>
                            </div>

                            <!-- Photo Upload Form -->
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-5 lg:pt-6">
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 lg:mb-3 uppercase tracking-wide">Upload Photo</label>
                                <form id="photoUploadForm" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3 flex gap-2 sm:gap-3">
                                        <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden">
                                        <button type="button" onclick="document.getElementById('photoInput').click()" class="flex-1 px-4 py-2 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg text-center text-blue-600 dark:text-blue-400 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/30 transition cursor-pointer text-sm whitespace-nowrap">
                                            <i class="fas fa-cloud-upload-alt mr-1"></i> Choose Photo
                                        </button>
                                    </div>
                                    <div id="uploadProgress" class="hidden mb-3">
                                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div id="progressBar" class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Uploading...</p>
                                    </div>
                                    <div id="uploadMessage" class="text-xs mb-3"></div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Max 5MB • JPEG, PNG, GIF, WebP</p>
                                </form>
                            </div>

                            <!-- All Photos -->
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-5 lg:pt-6 mt-5 lg:mt-6">
                                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wide">All Photos (<span id="photoCount">0</span>)</h4>
                                <div id="allPhotos" class="grid grid-cols-4 lg:grid-cols-3 gap-2 max-h-48 lg:max-h-64 overflow-y-auto pr-1 custom-scrollbar">
                                    <!-- Photos loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Form (Right on desktop, below photo on mobile) -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 sm:p-8 transition-colors">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-8">
                                @csrf
                                @method('PUT')

                                <!-- Personal Information -->
                                <div class="border-b border-gray-100 dark:border-gray-700 pb-8">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5 uppercase tracking-wide">Personal Information</h3>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <!-- Full Name -->
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                            @error('name')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Email -->
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                            @error('email')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Username -->
                                        <div>
                                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username *</label>
                                            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                            @error('username')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Phone -->
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                            @error('phone')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Account Information -->
                                <div class="border-b border-gray-100 dark:border-gray-700 pb-8">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5 uppercase tracking-wide">Account Information</h3>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <!-- Password -->
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password (Leave blank to keep)</label>
                                            <div class="relative">
                                                <input type="password" name="password" id="password" class="w-full px-4 py-2.5 pr-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                                <button type="button" onclick="togglePasswordVisibility('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 z-10 cursor-pointer transition-colors">
                                                    <svg id="password_eye_open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <svg id="password_eye_closed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            @error('password')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Confirm Password -->
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                                            <div class="relative">
                                                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-2.5 pr-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                                <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 z-10 cursor-pointer transition-colors">
                                                    <svg id="password_confirmation_eye_open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <svg id="password_confirmation_eye_closed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Roles (Multiple Selection) -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Roles *</label>
                                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700 transition-colors">
                                                @php
                                                    $userRoles = $user->roles();
                                                @endphp
                                                
                                                @foreach($roles as $role)
                                                    <div class="flex items-center">
                                                        <input 
                                                            type="checkbox" 
                                                            name="roles[]" 
                                                            id="role_{{ $role }}" 
                                                            value="{{ $role }}"
                                                            {{ in_array($role, $userRoles) ? 'checked' : '' }}
                                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-gray-700 role-checkbox transition-colors"
                                                            onchange="handleRoleSelection()"
                                                        >
                                                        <label for="role_{{ $role }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                                            {{ $role == 'hr' ? 'Human Resource' : ucfirst($role) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Select one or more roles</p>
                                            @error('roles')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Status -->
                                        <div>
                                            <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                            <div class="flex items-center gap-2 mt-2">
                                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-gray-700 transition-colors">
                                                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">Active Account</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Department & Designation -->
                                <div class="pt-2">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5 uppercase tracking-wide">Department & Designation</h3>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                                        <!-- Department -->
                                        <div>
                                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                            <select name="department_id" id="department_id" class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                                <option value="">Select a department</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('department_id')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Designation -->
                                        <div>
                                            <label for="designation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                                            <select name="designation_id" id="designation_id" class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                                <option value="">Select a designation</option>
                                                @foreach($designations as $desig)
                                                    <option value="{{ $desig->id }}" {{ old('designation_id', $user->designation_id) == $desig->id ? 'selected' : '' }}>{{ $desig->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('designation_id')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>

                                        <!-- Employment Status (Staff) -->
                                        <div>
                                            <label for="employment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employment Status</label>
                                            <select name="employment_status" id="employment_status" class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                                                <option value="">Select status</option>
                                                <option value="Permanent" {{ old('employment_status', $user->employment_status) === 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                                <option value="Casual" {{ old('employment_status', $user->employment_status) === 'Casual' ? 'selected' : '' }}>Casual</option>
                                                <option value="Contractual" {{ old('employment_status', $user->employment_status) === 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                                <option value="Emergency Laborer" {{ old('employment_status', $user->employment_status) === 'Emergency Laborer' ? 'selected' : '' }}>Emergency Laborer</option>
                                                <option value="Part Time" {{ old('employment_status', $user->employment_status) === 'Part Time' ? 'selected' : '' }}>Part Time</option>
                                            </select>
                                            @error('employment_status')<span class="text-red-500 dark:text-red-400 text-xs mt-1 block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium flex items-center justify-center gap-2 text-sm transition shadow-sm">
                                        <i class="fas fa-save"></i> Update User
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 px-6 py-2.5 rounded-lg font-medium text-center text-sm transition shadow-sm">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
</div>

    <!-- Set as Profile Confirmation Modal -->
    <div id="setProfileModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full animate-scale-in transition-colors">
            <div class="bg-blue-50 dark:bg-blue-900/10 border-b border-blue-200 dark:border-blue-800 px-6 py-5 flex items-center gap-4">
                <div class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-3 flex-shrink-0">
                    <i class="fas fa-check-circle text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Set as Profile Picture</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Update your profile photo</p>
                </div>
            </div>

            <div class="px-6 py-6">
                <p class="text-gray-700 dark:text-gray-300">Set this photo as your profile picture?</p>
            </div>

            <div class="bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex gap-3 justify-end rounded-b-2xl transition-colors">
                <button type="button" onclick="closeSetProfileModal()" class="px-5 py-2.5 rounded-lg font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm text-sm">
                    Cancel
                </button>
                <button type="button" onclick="confirmSetProfile()" class="px-5 py-2.5 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2 text-sm shadow-sm">
                    <i class="fas fa-check"></i> Set as Profile
                </button>
            </div>
        </div>
    </div>

    <!-- Image Crop Modal -->
    <div id="cropModal" class="fixed inset-0 bg-black/75 hidden flex items-center justify-center z-[60] p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full animate-scale-in transition-colors lg:max-h-[90vh] flex flex-col">
            <div class="bg-blue-50 dark:bg-blue-900/10 border-b border-blue-200 dark:border-blue-800 px-6 py-5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-3 flex-shrink-0">
                        <i class="fas fa-crop-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Crop & Resize Photo</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Adjust the user's profile picture</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 overflow-y-auto">
                <div class="mb-5">
                    <div class="h-64 sm:h-96 overflow-hidden bg-gray-100 dark:bg-gray-900 rounded-xl flex items-center justify-center">
                        <img id="cropImage" src="" alt="Crop preview" class="max-w-full block">
                    </div>
                </div>

                <!-- Crop Controls -->
                <div class="flex flex-wrap gap-2 mb-4 justify-center sm:justify-start">
                    <button type="button" onclick="cropperZoomIn()" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-search-plus mr-1"></i> Zoom In
                    </button>
                    <button type="button" onclick="cropperZoomOut()" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-search-minus mr-1"></i> Zoom Out
                    </button>
                    <button type="button" onclick="cropperRotateLeft()" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-undo mr-1"></i> Rotate Left
                    </button>
                    <button type="button" onclick="cropperRotateRight()" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-redo mr-1"></i> Rotate Right
                    </button>
                    <button type="button" onclick="cropperReset()" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-sync mr-1"></i> Reset
                    </button>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    Drag to move, scroll to zoom. The cropped area will be the profile picture.
                </p>
            </div>

            <div class="bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex gap-3 justify-end shrink-0 rounded-b-2xl transition-colors">
                <button type="button" onclick="closeCropModal()" class="px-5 py-2.5 rounded-lg font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm text-sm">
                    Cancel
                </button>
                <button type="button" onclick="applyCropAndUpload()" class="px-5 py-2.5 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 transition flex items-center gap-2 text-sm shadow-sm">
                    <i class="fas fa-check"></i> Crop & Upload
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Photo Confirmation Modal -->
    <div id="deletePhotoModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm transition-opacity">
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
                Are you sure you want to delete this photo? The photo will be permanently removed from the system.
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDeletePhotoModal()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">Cancel</button>
                <button type="button" onclick="confirmDeletePhoto()" class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>

    <div id="adminUsersEditConfig" class="hidden" data-user-id="{{ $user->id }}" aria-hidden="true"></div>
@endsection