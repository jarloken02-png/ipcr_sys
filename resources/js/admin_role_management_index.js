    // Tab switching
    window.switchTab = function(tab) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-purple-500', 'text-purple-600', 'dark:text-purple-400',
                               'border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400',
                               'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
            b.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        });

        document.getElementById('panel-' + tab).classList.remove('hidden');
        const btn = document.getElementById('tab-' + tab);
        btn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');

        const colors = {
            roles: ['border-purple-500', 'text-purple-600', 'dark:text-purple-400'],
            departments: ['border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400'],
            designations: ['border-blue-500', 'text-blue-600', 'dark:text-blue-400'],
        };
        btn.classList.add(...colors[tab]);

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        history.replaceState(null, '', url);
    };

    // Modal helpers
    window.openModal = function(id) { document.getElementById(id).classList.remove('hidden'); };
    window.closeModal = function(id) { document.getElementById(id).classList.add('hidden'); };

    // Roles
    window.openEditRoleModal = function(id, name, acronym, permissionKeys) {
        document.getElementById('editRoleName').value = name;
        document.getElementById('editRoleAcronym').value = acronym;
        document.getElementById('editRoleForm').action = '/admin/panel/role-management/roles/' + id;

        // Reset all permission checkboxes
        document.querySelectorAll('.edit-perm-checkbox').forEach(cb => cb.checked = false);

        // Check the permissions this role has
        if (permissionKeys && Array.isArray(permissionKeys)) {
            permissionKeys.forEach(key => {
                const cb = document.querySelector('.edit-perm-checkbox[value="' + key + '"]');
                if (cb) cb.checked = true;
            });
        }

        openModal('editRoleModal');
    };

    // Toggle all checkboxes in permission group
    window.toggleGroupCheckboxes = function(btn, prefix) {
        const group = btn.closest('.rounded-lg');
        if (!group) return;
        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    };

    // Departments
    window.openEditDeptModal = function(id, name, code) {
        document.getElementById('editDeptName').value = name;
        document.getElementById('editDeptCode').value = code;
        document.getElementById('editDeptForm').action = '/admin/panel/role-management/departments/' + id;
        openModal('editDepartmentModal');
    };

    // Designations
    window.openEditDesigModal = function(id, title, code) {
        document.getElementById('editDesigTitle').value = title;
        document.getElementById('editDesigCode').value = code;
        document.getElementById('editDesigForm').action = '/admin/panel/role-management/designations/' + id;
        openModal('editDesignationModal');
    };

    // Delete
    let deleteFormRef = null;
    window.openRmDeleteModal = function(name, form) {
        document.getElementById('deleteItemName').textContent = name;
        deleteFormRef = form;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    };
    window.closeRmDeleteModal = function() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
        deleteFormRef = null;
    };
    window.confirmRmDelete = function() {
        if (deleteFormRef) deleteFormRef.submit();
    };

    // Close modals on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['addRoleModal','editRoleModal','addDepartmentModal','editDepartmentModal','addDesignationModal','editDesignationModal','deleteConfirmModal'].forEach(window.closeModal);
        }
    });

    // Close modals on backdrop click
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    });

    // Auto uppercase acronym/code fields
    document.querySelectorAll('input[class*="uppercase"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
