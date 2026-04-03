    // Notification modal
    window.openCreateNotificationModal = function() {
        document.getElementById('notificationModalTitle').textContent = 'Create Notification';
        document.getElementById('notificationForm').action = '/admin/panel/notifications';
        document.getElementById('notificationMethodField').innerHTML = '';
        document.getElementById('notif_title').value = '';
        document.getElementById('notif_message').value = '';
        document.getElementById('notif_type').value = 'info';
        document.getElementById('notif_audience').value = 'all';
        document.getElementById('notif_published_at').value = '';
        document.getElementById('notif_expires_at').value = '';
        document.getElementById('notificationModal').classList.remove('hidden');
    };

    window.openEditNotificationModal = function(id, data) {
        document.getElementById('notificationModalTitle').textContent = 'Edit Notification';
        document.getElementById('notificationForm').action = '/admin/panel/notifications/' + id;
        document.getElementById('notificationMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('notif_title').value = data.title || '';
        document.getElementById('notif_message').value = data.message || '';
        document.getElementById('notif_type').value = data.type || 'info';
        document.getElementById('notif_audience').value = data.audience || 'all';
        
        // Format datetime-local values
        if (data.published_at) {
            const d = new Date(data.published_at);
            document.getElementById('notif_published_at').value = d.toISOString().slice(0, 16);
        } else {
            document.getElementById('notif_published_at').value = '';
        }
        if (data.expires_at) {
            const d = new Date(data.expires_at);
            document.getElementById('notif_expires_at').value = d.toISOString().slice(0, 16);
        } else {
            document.getElementById('notif_expires_at').value = '';
        }
        
        document.getElementById('notificationModal').classList.remove('hidden');
    };

    window.closeNotificationModal = function() {
        document.getElementById('notificationModal').classList.add('hidden');
    };

    // Deadline modal
    window.openCreateDeadlineModal = function() {
        document.getElementById('deadlineModalTitle').textContent = 'Create Deadline';
        document.getElementById('deadlineForm').action = '/admin/panel/deadlines';
        document.getElementById('deadlineMethodField').innerHTML = '';
        document.getElementById('deadline_title').value = '';
        document.getElementById('deadline_description').value = '';
        document.getElementById('deadline_date').value = '';
        document.getElementById('deadline_audience').value = 'all';
        document.getElementById('deadlineModal').classList.remove('hidden');
    };

    window.openEditDeadlineModal = function(id, data) {
        document.getElementById('deadlineModalTitle').textContent = 'Edit Deadline';
        document.getElementById('deadlineForm').action = '/admin/panel/deadlines/' + id;
        document.getElementById('deadlineMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('deadline_title').value = data.title || '';
        document.getElementById('deadline_description').value = data.description || '';
        document.getElementById('deadline_audience').value = data.audience || 'all';
        
        // Format date
        if (data.deadline_date) {
            document.getElementById('deadline_date').value = data.deadline_date.split('T')[0];
        } else {
            document.getElementById('deadline_date').value = '';
        }
        
        document.getElementById('deadlineModal').classList.remove('hidden');
    };

    window.closeDeadlineModal = function() {
        document.getElementById('deadlineModal').classList.add('hidden');
    };

    // Delete modal
    window.openDeleteModal = function(type, id, name) {
        document.getElementById('deleteItemName').textContent = name;
        if (type === 'notification') {
            document.getElementById('deleteForm').action = '/admin/panel/notifications/' + id;
        } else {
            document.getElementById('deleteForm').action = '/admin/panel/deadlines/' + id;
        }
        document.getElementById('deleteModal').classList.remove('hidden');
    };

    window.closeDeleteModal = function() {
        document.getElementById('deleteModal').classList.add('hidden');
    };
