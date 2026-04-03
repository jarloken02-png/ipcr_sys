(function () {
    if (window.summaryUserManagementInitialized) {
        return;
    }
    window.summaryUserManagementInitialized = true;

    let summaryPendingDeleteForm = null;
    let summaryCurrentViewUserId = null;

    const roleBadgeClasses = function (role) {
        if (role === 'admin') return 'bg-purple-50 text-purple-700';
        if (role === 'director') return 'bg-emerald-50 text-emerald-700';
        if (role === 'dean') return 'bg-blue-50 text-blue-700';
        return 'bg-gray-100 text-gray-700';
    };

    const setOrgVisibility = function (prefix) {
        const hr = document.getElementById(prefix + '_role_hr');
        const director = document.getElementById(prefix + '_role_director');
        const faculty = document.getElementById(prefix + '_role_faculty');

        const orgWrapper = document.getElementById(prefix + '_org_fields');
        const dept = document.getElementById(prefix + '_department_id');
        const desig = document.getElementById(prefix + '_designation_id');
        const empWrapper = document.getElementById(prefix + '_employment_wrapper');
        const employment = document.getElementById(prefix + '_employment_status');

        const isHrOrDirector = (hr && hr.checked) || (director && director.checked);
        const isFaculty = faculty && faculty.checked;

        if (orgWrapper) {
            orgWrapper.style.display = isHrOrDirector ? 'none' : '';
        }

        if (isHrOrDirector) {
            if (dept) dept.value = '';
            if (desig) desig.value = '';
            if (employment) employment.value = '';
        }

        if (empWrapper) {
            empWrapper.style.display = (!isHrOrDirector && isFaculty) ? '' : 'none';
        }

        if ((!isFaculty || isHrOrDirector) && employment) {
            employment.value = '';
        }
    };

    window.handleSummaryAddRoleSelection = function () {
        setOrgVisibility('summary_add');
    };

    window.handleSummaryEditRoleSelection = function () {
        setOrgVisibility('summary_edit');
    };

    window.toggleSummaryPasswordVisibility = function (fieldId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_eye');

        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    window.openSummaryDeleteModal = function (userName, form) {
        const modal = document.getElementById('summaryUserDeleteModal');
        const nameEl = document.getElementById('summaryDeleteUserName');
        summaryPendingDeleteForm = form;
        if (nameEl) nameEl.textContent = userName;
        if (modal) modal.classList.remove('hidden');
    };

    window.closeSummaryDeleteModal = function () {
        const modal = document.getElementById('summaryUserDeleteModal');
        if (modal) modal.classList.add('hidden');
        summaryPendingDeleteForm = null;
    };

    window.confirmSummaryDelete = function () {
        if (summaryPendingDeleteForm) {
            summaryPendingDeleteForm.submit();
        }
        window.closeSummaryDeleteModal();
    };

    window.openSummaryAddUserModal = function () {
        const modal = document.getElementById('summaryAddUserModal');
        if (modal) modal.classList.remove('hidden');
        setOrgVisibility('summary_add');
    };

    window.closeSummaryAddUserModal = function () {
        const modal = document.getElementById('summaryAddUserModal');
        if (modal) modal.classList.add('hidden');
    };

    window.openSummaryViewUserModal = function (userId) {
        summaryCurrentViewUserId = userId;

        const modal = document.getElementById('summaryViewUserModal');
        const loading = document.getElementById('summaryViewUserLoading');
        const data = document.getElementById('summaryViewUserData');

        if (modal) modal.classList.remove('hidden');
        if (loading) loading.classList.remove('hidden');
        if (data) data.classList.add('hidden');

        fetch('/admin/panel/users/' + userId + '/json')
            .then(function (response) { return response.json(); })
            .then(function (user) {
                const photo = document.getElementById('summaryViewUserPhoto');
                const name = document.getElementById('summaryViewUserName');
                const empId = document.getElementById('summaryViewUserEmployeeId');
                const email = document.getElementById('summaryViewUserEmail');
                const username = document.getElementById('summaryViewUserUsername');
                const phone = document.getElementById('summaryViewUserPhone');
                const dept = document.getElementById('summaryViewUserDepartment');
                const desig = document.getElementById('summaryViewUserDesignation');
                const employment = document.getElementById('summaryViewUserEmploymentStatus');
                const status = document.getElementById('summaryViewUserStatus');
                const roles = document.getElementById('summaryViewUserRoles');

                if (photo) {
                    photo.src = user.profile_photo_url;
                    photo.alt = user.name || 'User';
                }
                if (name) name.textContent = user.name || '';
                if (empId) empId.textContent = user.employee_id || 'No Employee ID';
                if (email) email.textContent = user.email || 'N/A';
                if (username) username.textContent = user.username || 'N/A';
                if (phone) phone.textContent = user.phone || 'N/A';
                if (dept) dept.textContent = user.department_name || 'N/A';
                if (desig) desig.textContent = user.designation_name || 'N/A';
                if (employment) employment.textContent = user.employment_status || 'N/A';
                if (status) {
                    status.innerHTML = user.is_active
                        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active</span>'
                        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactive</span>';
                }
                if (roles) {
                    roles.innerHTML = (user.roles || []).map(function (role) {
                        const label = role === 'hr' ? 'Human Resource' : (role.charAt(0).toUpperCase() + role.slice(1));
                        return '<span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium ' + roleBadgeClasses(role) + '">' + label + '</span>';
                    }).join('');
                }

                if (loading) loading.classList.add('hidden');
                if (data) data.classList.remove('hidden');
            })
            .catch(function () {
                if (loading) {
                    loading.innerHTML = '<p class="text-sm text-red-600 text-center">Failed to load user data.</p>';
                }
            });
    };

    window.closeSummaryViewUserModal = function () {
        const modal = document.getElementById('summaryViewUserModal');
        const loading = document.getElementById('summaryViewUserLoading');
        const data = document.getElementById('summaryViewUserData');

        if (modal) modal.classList.add('hidden');
        if (loading) {
            loading.classList.remove('hidden');
            loading.innerHTML = '<div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        }
        if (data) data.classList.add('hidden');

        summaryCurrentViewUserId = null;
    };

    window.openSummaryEditUserModal = function (userId) {
        const modal = document.getElementById('summaryEditUserModal');
        const loading = document.getElementById('summaryEditUserLoading');
        const form = document.getElementById('summaryEditUserForm');

        if (modal) modal.classList.remove('hidden');
        if (loading) loading.classList.remove('hidden');
        if (form) form.classList.add('hidden');

        fetch('/admin/panel/users/' + userId + '/json')
            .then(function (response) { return response.json(); })
            .then(function (user) {
                const editForm = document.getElementById('summaryEditUserForm');
                const subtitle = document.getElementById('summaryEditUserSubtitle');

                if (editForm) {
                    editForm.action = '/admin/panel/users/' + user.id;
                }
                if (subtitle) subtitle.textContent = user.name || '';

                const setValue = function (id, value) {
                    const el = document.getElementById(id);
                    if (el) el.value = value || '';
                };

                setValue('summary_edit_name', user.name);
                setValue('summary_edit_email', user.email);
                setValue('summary_edit_username', user.username);
                setValue('summary_edit_phone', user.phone);
                setValue('summary_edit_department_id', user.department_id);
                setValue('summary_edit_designation_id', user.designation_id);
                setValue('summary_edit_employment_status', user.employment_status);
                setValue('summary_edit_password', '');
                setValue('summary_edit_password_confirmation', '');

                const status = document.getElementById('summary_edit_is_active');
                if (status) status.checked = !!user.is_active;

                document.querySelectorAll('[id^="summary_edit_role_"]').forEach(function (cb) {
                    cb.checked = (user.roles || []).includes(cb.value);
                });

                handleSummaryEditRoleSelection();

                if (loading) loading.classList.add('hidden');
                if (form) form.classList.remove('hidden');
            })
            .catch(function () {
                if (loading) {
                    loading.innerHTML = '<p class="text-sm text-red-600 text-center">Failed to load user data.</p>';
                }
            });
    };

    window.closeSummaryEditUserModal = function () {
        const modal = document.getElementById('summaryEditUserModal');
        const loading = document.getElementById('summaryEditUserLoading');
        const form = document.getElementById('summaryEditUserForm');

        if (modal) modal.classList.add('hidden');
        if (loading) {
            loading.classList.remove('hidden');
            loading.innerHTML = '<div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        }
        if (form) form.classList.add('hidden');
    };

    const summaryViewToEditBtn = document.getElementById('summaryViewToEditBtn');
    if (summaryViewToEditBtn) {
        summaryViewToEditBtn.addEventListener('click', function () {
            if (summaryCurrentViewUserId) {
                closeSummaryViewUserModal();
                openSummaryEditUserModal(summaryCurrentViewUserId);
            }
        });
    }

    const deleteModal = document.getElementById('summaryUserDeleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === deleteModal) closeSummaryDeleteModal();
        });
    }

    const addModal = document.getElementById('summaryAddUserModal');
    if (addModal) {
        addModal.addEventListener('click', function (e) {
            if (e.target === addModal) closeSummaryAddUserModal();
        });
    }

    const viewModal = document.getElementById('summaryViewUserModal');
    if (viewModal) {
        viewModal.addEventListener('click', function (e) {
            if (e.target === viewModal) closeSummaryViewUserModal();
        });
    }

    const editModal = document.getElementById('summaryEditUserModal');
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeSummaryEditUserModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSummaryDeleteModal();
            closeSummaryAddUserModal();
            closeSummaryViewUserModal();
            closeSummaryEditUserModal();
        }
    });

    setOrgVisibility('summary_add');
})();
