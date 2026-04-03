import * as Turbo from "@hotwired/turbo";
Turbo.start();

// Sidebar toggle
window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('sidebar-hidden');
    overlay.classList.toggle('hidden');
};

// Initialize Sidebar & Global Listeners on Turbo Load
document.addEventListener('turbo:load', () => {
    // 1. Sidebar Logic
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebar) {
        // Set initial state based on width
        if (window.innerWidth < 1280) {
            sidebar.classList.add('sidebar-hidden');
        } else {
            sidebar.classList.remove('sidebar-hidden');
            overlay.classList.add('hidden');
        }

        // Close sidebar when clicking a link (mobile only)
        document.querySelectorAll('#sidebar a, #sidebar button[type="submit"]').forEach(element => {
            element.addEventListener('click', () => {
                if (window.innerWidth < 1280) {
                    sidebar.classList.add('sidebar-hidden');
                    overlay.classList.add('hidden');
                }
            });
        });
    }

    // 2. User Management - Search & Filters
    const searchInput = document.getElementById('searchInput');
    const departmentFilter = document.getElementById('departmentFilter');
    const filterForm = document.getElementById('filterForm');

    if (searchInput && filterForm) {
        let searchTimeout = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                filterForm.submit();
                e.preventDefault(); // Prevent default since we handle submit
            }
        });
    }

    if (departmentFilter && filterForm) {
        departmentFilter.addEventListener('change', function () {
            filterForm.submit();
        });
    }

    const openAddUserModalOnLoad = document.getElementById('openAddUserModalOnLoad');
    if (openAddUserModalOnLoad && typeof window.openAddUserModal === 'function') {
        window.openAddUserModal();
    }

    // 3. Modals (Re-attach close listeners on outside click/escape)
    // Note: Global open/close functions are defined below and don't need re-definition
});

// Handle window resize (Persistent listener)
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    if (window.innerWidth >= 1280) {
        sidebar.classList.remove('sidebar-hidden');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.add('sidebar-hidden');
        overlay.classList.add('hidden');
    }
});

// --- GLOBAL MODAL FUNCTIONS (users & database) ---

// User Management Actions
let pendingDeleteUserForm = null;
window.openConfirmationModal = function (userName, form) {
    document.getElementById('deleteUserName').textContent = userName;
    pendingDeleteUserForm = form;
    document.getElementById('confirmationModal').classList.remove('hidden');
};
window.closeConfirmationModal = function () {
    const modal = document.getElementById('confirmationModal');
    if (modal) modal.classList.add('hidden');
    pendingDeleteUserForm = null;
};
window.confirmDelete = function () {
    // Check which modal is currently visible to decide what action to take
    const deleteModal = document.getElementById('deleteModal');
    const confirmationModal = document.getElementById('confirmationModal');

    if (deleteModal && !deleteModal.classList.contains('hidden') && deleteBackupForm) {
        // Database backup delete
        deleteBackupForm.submit();
        window.closeDeleteModal();
    } else if (confirmationModal && !confirmationModal.classList.contains('hidden') && pendingDeleteUserForm) {
        // User delete
        pendingDeleteUserForm.submit();
        window.closeConfirmationModal();
    } else {
        // Fallback: try each in order
        if (deleteBackupForm) {
            deleteBackupForm.submit();
            if (window.closeDeleteModal) window.closeDeleteModal();
        } else if (pendingDeleteUserForm) {
            pendingDeleteUserForm.submit();
            window.closeConfirmationModal();
        }
    }
};

// Add User Modal
window.openAddUserModal = function () {
    document.getElementById('addUserModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (window.handleAddRoleSelection) {
        window.handleAddRoleSelection();
    }
};
window.closeAddUserModal = function () {
    document.getElementById('addUserModal').classList.add('hidden');
    document.body.style.overflow = '';
};

// Toggle Password Visibility
window.togglePasswordVisibility = function (fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const eyeOpen = document.getElementById(fieldId + '_eye_open');
    const eyeClosed = document.getElementById(fieldId + '_eye_closed');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeOpen?.classList.add('hidden');
        eyeClosed?.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeOpen?.classList.remove('hidden');
        eyeClosed?.classList.add('hidden');
    }
};

// Handle add-user role selection to show/hide staff-related fields.
window.handleAddRoleSelection = function () {
    const roleCheckboxes = Array.from(document.querySelectorAll('#addUserModal input[name="roles[]"]:checked'));
    const selectedRoles = roleCheckboxes.map(cb => cb.value);

    const isHrOrDirector = selectedRoles.includes('hr') || selectedRoles.includes('director');
    const hasFacultyRole = selectedRoles.includes('faculty');

    const deptWrapper = document.getElementById('addDeptWrapper');
    const desigWrapper = document.getElementById('addDesigWrapper');
    const employmentWrapper = document.getElementById('employmentStatusWrapper');

    const deptSelect = document.getElementById('department_id');
    const desigSelect = document.getElementById('designation_id');
    const employmentSelect = document.getElementById('employment_status');

    if (!deptWrapper || !desigWrapper || !employmentWrapper || !deptSelect || !desigSelect || !employmentSelect) {
        return;
    }

    if (isHrOrDirector) {
        deptWrapper.style.display = 'none';
        desigWrapper.style.display = 'none';
        employmentWrapper.style.display = 'none';

        deptSelect.value = '';
        desigSelect.value = '';
        employmentSelect.value = '';
        return;
    }

    deptWrapper.style.display = '';
    desigWrapper.style.display = '';
    employmentWrapper.style.display = hasFacultyRole ? '' : 'none';

    if (!hasFacultyRole) {
        employmentSelect.value = '';
    }
};

// Database Management Actions
let restoreBackupForm = null;
window.openRestoreModal = function (filename, form) {
    restoreBackupForm = form;
    document.getElementById('restoreFileName').textContent = filename;
    document.getElementById('restoreModal').classList.remove('hidden');
};
window.closeRestoreModal = function () {
    document.getElementById('restoreModal').classList.add('hidden');
    restoreBackupForm = null;
};
window.confirmRestore = function () {
    if (restoreBackupForm) restoreBackupForm.submit();
};

let deleteBackupForm = null;
window.openDeleteModal = function (filename, form) { // Specific to database
    deleteBackupForm = form;
    document.getElementById('deleteFileName').textContent = filename;
    document.getElementById('deleteModal').classList.remove('hidden');
};
window.closeDeleteModal = function () {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.add('hidden');
    deleteBackupForm = null;
};
// confirmDelete is shared/handled above

// Reset stale form references when navigating away (Turbo)
document.addEventListener('turbo:before-visit', function () {
    pendingDeleteUserForm = null;
    deleteBackupForm = null;
    restoreBackupForm = null;
});

window.openSettingsModal = function () {
    document.getElementById('settingsModal').classList.remove('hidden');
};
window.closeSettingsModal = function () {
    document.getElementById('settingsModal').classList.add('hidden');
};

// Global Event Delegation for Modal Close (Backdrop Click & Escape)
document.addEventListener('click', function (e) {
    // User Modals
    if (e.target.id === 'confirmationModal') window.closeConfirmationModal();
    if (e.target.id === 'addUserModal') window.closeAddUserModal();
    if (e.target.id === 'viewUserModal') window.closeViewUserModal();
    if (e.target.id === 'editUserModal') window.closeEditUserModal();

    // Database Modals
    if (e.target.id === 'restoreModal') window.closeRestoreModal();
    if (e.target.id === 'deleteModal') window.closeDeleteModal();
    if (e.target.id === 'settingsModal') window.closeSettingsModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        window.closeConfirmationModal();
        window.closeAddUserModal();
        if (window.closeViewUserModal) window.closeViewUserModal();
        if (window.closeEditUserModal) window.closeEditUserModal();
        if (window.closeRestoreModal) window.closeRestoreModal();
        if (window.closeDeleteModal) window.closeDeleteModal();
        if (window.closeSettingsModal) window.closeSettingsModal();
    }
});

// ============================================
// VIEW USER MODAL
// ============================================

let currentViewUserId = null;

function getRoleBadgeClasses(role) {
    switch (role) {
        case 'admin': return 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300';
        case 'director': return 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300';
        case 'dean': return 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
        case 'hr': return 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300';
        default: return 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300';
    }
}

window.openViewUserModal = function (userId) {
    currentViewUserId = userId;
    const modal = document.getElementById('viewUserModal');
    const loading = document.getElementById('viewUserLoading');
    const data = document.getElementById('viewUserData');

    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loading.classList.remove('hidden');
    loading.innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>';
    data.classList.add('hidden');

    fetch(`/admin/panel/users/${userId}/json`)
        .then(response => response.json())
        .then(user => {
            // Populate header
            document.getElementById('viewUserPhoto').src = user.profile_photo_url;
            document.getElementById('viewUserPhoto').alt = user.name;
            document.getElementById('viewUserName').textContent = user.name;
            document.getElementById('viewUserEmployeeId').textContent = user.employee_id || 'No Employee ID';

            // Roles badges
            const rolesContainer = document.getElementById('viewUserRoles');
            rolesContainer.innerHTML = user.roles.map(role =>
                `<span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium ${getRoleBadgeClasses(role)}">${role === 'hr' ? 'Human Resource' : role.charAt(0).toUpperCase() + role.slice(1)}</span>`
            ).join('');

            // Personal info
            document.getElementById('viewUserFullName').textContent = user.name;
            document.getElementById('viewUserEmail').textContent = user.email;
            document.getElementById('viewUserUsername').textContent = user.username;
            document.getElementById('viewUserPhone').textContent = user.phone || 'N/A';

            // Account & Organization
            if (user.is_active) {
                document.getElementById('viewUserStatus').innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active</span>';
            } else {
                document.getElementById('viewUserStatus').innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactive</span>';
            }
            document.getElementById('viewUserDepartment').textContent = user.department_name || 'N/A';
            document.getElementById('viewUserDesignation').textContent = user.designation_name || 'N/A';
            const viewEmployment = document.getElementById('viewUserEmploymentStatus');
            if (viewEmployment) {
                viewEmployment.textContent = user.employment_status || 'N/A';
            }

            // Show/hide Edit button based on whether this is the protected admin
            const editBtn = document.getElementById('viewToEditBtn');
            if (editBtn) {
                if (user.employee_id === 'URS26-ADM00001') {
                    editBtn.classList.add('hidden');
                } else {
                    editBtn.classList.remove('hidden');
                }
            }

            loading.classList.add('hidden');
            data.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading user:', error);
            loading.innerHTML = '<p class="text-red-500 text-sm">Failed to load user data.</p>';
        });
};

window.closeViewUserModal = function () {
    const modal = document.getElementById('viewUserModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    currentViewUserId = null;
};

// View to Edit transition button
document.addEventListener('turbo:load', () => {
    const viewToEditBtn = document.getElementById('viewToEditBtn');
    if (viewToEditBtn) {
        viewToEditBtn.addEventListener('click', function () {
            if (currentViewUserId) {
                const userId = currentViewUserId; // Save before close nullifies it
                closeViewUserModal();
                openEditUserModal(userId);
            }
        });
    }

    if (window.handleAddRoleSelection) {
        window.handleAddRoleSelection();
    }
});

// ============================================
// EDIT USER MODAL
// ============================================

window.openEditUserModal = function (userId) {
    const modal = document.getElementById('editUserModal');
    const loading = document.getElementById('editUserLoading');
    const formWrapper = document.getElementById('editUserFormWrapper');
    const errorsDiv = document.getElementById('editModalErrors');

    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loading.classList.remove('hidden');
    loading.innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>';
    formWrapper.classList.add('hidden');
    if (errorsDiv) errorsDiv.classList.add('hidden');

    fetch(`/admin/panel/users/${userId}/json`)
        .then(response => response.json())
        .then(user => {
            // Set form action
            document.getElementById('editUserForm').action = `/admin/panel/users/${user.id}`;
            document.getElementById('editUserSubtitle').textContent = user.name;

            // Populate fields
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';

            // Status
            document.getElementById('edit_is_active').checked = user.is_active;

            // Roles
            document.querySelectorAll('.edit-role-checkbox').forEach(cb => {
                cb.checked = user.roles.includes(cb.value);
            });

            // Department & Designation
            document.getElementById('edit_department_id').value = user.department_id || '';
            document.getElementById('edit_designation_id').value = user.designation_id || '';
            const editEmployment = document.getElementById('edit_employment_status');
            if (editEmployment) {
                editEmployment.value = user.employment_status || '';
            }

            // Handle role-based dept/desig visibility
            handleEditRoleSelection();

            loading.classList.add('hidden');
            formWrapper.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading user:', error);
            loading.innerHTML = '<p class="text-red-500 text-sm">Failed to load user data.</p>';
        });
};

window.closeEditUserModal = function () {
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
};

// Handle role selection to show/hide department and designation in edit modal
window.handleEditRoleSelection = function () {
    const hrCheckbox = document.getElementById('edit_role_hr');
    const directorCheckbox = document.getElementById('edit_role_director');
    const facultyCheckbox = document.getElementById('edit_role_faculty');
    const deptSection = document.getElementById('editDeptSection');
    const employmentWrapper = document.getElementById('editEmploymentStatusWrapper');

    if (!deptSection) return;

    const isHrOrDirector = (hrCheckbox && hrCheckbox.checked) || (directorCheckbox && directorCheckbox.checked);
    const hasFacultyRole = facultyCheckbox && facultyCheckbox.checked;
    const employmentSelect = document.getElementById('edit_employment_status');

    if (isHrOrDirector) {
        deptSection.style.display = 'none';
        const deptSelect = document.getElementById('edit_department_id');
        const desigSelect = document.getElementById('edit_designation_id');
        if (deptSelect) deptSelect.value = '';
        if (desigSelect) desigSelect.value = '';
        if (employmentSelect) employmentSelect.value = '';
    } else {
        deptSection.style.display = '';

        if (employmentWrapper) {
            employmentWrapper.style.display = hasFacultyRole ? '' : 'none';
        }

        if (!hasFacultyRole && employmentSelect) {
            employmentSelect.value = '';
        }
    }
};

// Toggle Password Visibility for Edit modal
window.toggleEditPasswordVisibility = function (fieldId) {
    const input = document.getElementById(fieldId);
    const eyeOpen = document.getElementById(fieldId + '_eye_open');
    const eyeClosed = document.getElementById(fieldId + '_eye_closed');

    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen?.classList.add('hidden');
        eyeClosed?.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen?.classList.remove('hidden');
        eyeClosed?.classList.add('hidden');
    }
};
