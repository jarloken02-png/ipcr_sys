(function () {
    if (window.summaryNotificationsDeadlinesInitialized) {
        return;
    }
    window.summaryNotificationsDeadlinesInitialized = true;

    const endpointRoot = document.getElementById('summaryManagedEndpoints');
    const notificationBase = endpointRoot?.dataset.notificationBase || '/admin/panel/notifications';
    const deadlineBase = endpointRoot?.dataset.deadlineBase || '/admin/panel/deadlines';

    const toDatetimeLocal = function (value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    };

    const toDateInput = function (value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return typeof value === 'string' ? value.split('T')[0] : '';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return year + '-' + month + '-' + day;
    };

    const closeModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    window.openSummaryCreateNotificationModal = function () {
        const form = document.getElementById('summaryNotificationForm');
        const methodField = document.getElementById('summaryNotificationMethodField');

        document.getElementById('summaryNotificationModalTitle').textContent = 'Create Notification';
        if (form) {
            form.action = notificationBase;
        }
        if (methodField) {
            methodField.innerHTML = '';
        }

        document.getElementById('summary_notif_title').value = '';
        document.getElementById('summary_notif_message').value = '';
        document.getElementById('summary_notif_type').value = 'info';
        document.getElementById('summary_notif_audience').value = 'all';
        document.getElementById('summary_notif_published_at').value = '';
        document.getElementById('summary_notif_expires_at').value = '';

        const modal = document.getElementById('summaryNotificationModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.openSummaryEditNotificationModal = function (id, data) {
        const form = document.getElementById('summaryNotificationForm');
        const methodField = document.getElementById('summaryNotificationMethodField');

        document.getElementById('summaryNotificationModalTitle').textContent = 'Edit Notification';
        if (form) {
            form.action = notificationBase + '/' + id;
        }
        if (methodField) {
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        }

        document.getElementById('summary_notif_title').value = data?.title || '';
        document.getElementById('summary_notif_message').value = data?.message || '';
        document.getElementById('summary_notif_type').value = data?.type || 'info';
        document.getElementById('summary_notif_audience').value = data?.audience || 'all';
        document.getElementById('summary_notif_published_at').value = toDatetimeLocal(data?.published_at);
        document.getElementById('summary_notif_expires_at').value = toDatetimeLocal(data?.expires_at);

        const modal = document.getElementById('summaryNotificationModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.closeSummaryNotificationModal = function () {
        closeModal('summaryNotificationModal');
    };

    window.openSummaryCreateDeadlineModal = function () {
        const form = document.getElementById('summaryDeadlineForm');
        const methodField = document.getElementById('summaryDeadlineMethodField');

        document.getElementById('summaryDeadlineModalTitle').textContent = 'Create Deadline';
        if (form) {
            form.action = deadlineBase;
        }
        if (methodField) {
            methodField.innerHTML = '';
        }

        document.getElementById('summary_deadline_title').value = '';
        document.getElementById('summary_deadline_description').value = '';
        document.getElementById('summary_deadline_date').value = '';
        document.getElementById('summary_deadline_audience').value = 'all';

        const modal = document.getElementById('summaryDeadlineModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.openSummaryEditDeadlineModal = function (id, data) {
        const form = document.getElementById('summaryDeadlineForm');
        const methodField = document.getElementById('summaryDeadlineMethodField');

        document.getElementById('summaryDeadlineModalTitle').textContent = 'Edit Deadline';
        if (form) {
            form.action = deadlineBase + '/' + id;
        }
        if (methodField) {
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        }

        document.getElementById('summary_deadline_title').value = data?.title || '';
        document.getElementById('summary_deadline_description').value = data?.description || '';
        document.getElementById('summary_deadline_date').value = toDateInput(data?.deadline_date);
        document.getElementById('summary_deadline_audience').value = data?.audience || 'all';

        const modal = document.getElementById('summaryDeadlineModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.closeSummaryDeadlineModal = function () {
        closeModal('summaryDeadlineModal');
    };

    window.openSummaryDeleteManagedItemModal = function (type, id, name) {
        const modal = document.getElementById('summaryManagedDeleteModal');
        const form = document.getElementById('summaryManagedDeleteForm');
        const title = document.getElementById('summaryManagedDeleteName');
        const base = type === 'notification' ? notificationBase : deadlineBase;

        if (form) {
            form.action = base + '/' + id;
        }
        if (title) {
            title.textContent = name || 'this item';
        }
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.closeSummaryDeleteManagedItemModal = function () {
        closeModal('summaryManagedDeleteModal');
    };

    const bindBackdropClose = function (id, closeFn) {
        const modal = document.getElementById(id);
        if (!modal) {
            return;
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeFn();
            }
        });
    };

    bindBackdropClose('summaryNotificationModal', window.closeSummaryNotificationModal);
    bindBackdropClose('summaryDeadlineModal', window.closeSummaryDeadlineModal);
    bindBackdropClose('summaryManagedDeleteModal', window.closeSummaryDeleteManagedItemModal);

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        window.closeSummaryNotificationModal();
        window.closeSummaryDeadlineModal();
        window.closeSummaryDeleteManagedItemModal();
    });
})();
