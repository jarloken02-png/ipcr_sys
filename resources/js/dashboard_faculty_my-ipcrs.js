// Mobile menu toggle
window.toggleMobileMenu = function () {
    const menu = document.querySelector('.mobile-menu');
    const overlay = document.querySelector('.mobile-menu-overlay');
    menu.classList.toggle('active');
    overlay.classList.toggle('active');
};

// Notification popup toggle for desktop
window.toggleNotificationPopup = function () {
    const popup = document.getElementById('notificationPopup');
    if (popup) popup.classList.toggle('active');
};

// Notification popup toggle for mobile — reuses same popup
window.toggleNotificationPopupMobile = function () {
    const popup = document.getElementById('notificationPopup');
    if (popup) popup.classList.toggle('active');
};

// Close notification popup when clicking outside
document.addEventListener('click', function (e) {
    const popup = document.getElementById('notificationPopup');
    const notificationBtn = e.target.closest('button[onclick*="toggleNotificationPopup"]');
    if (!notificationBtn && popup && !popup.contains(e.target)) {
        popup.classList.remove('active');
    }
});

// Compact mode for notifications — synced via localStorage
let compactMode = localStorage.getItem('notif_compact') === '1';

function applyCompactMode() {
    document.querySelectorAll('.notif-card').forEach(card => {
        if (compactMode) {
            card.classList.add('compact-notif');
            card.querySelectorAll('.notif-message').forEach(m => m.style.display = 'none');
            card.querySelectorAll('.notif-time').forEach(t => t.style.display = 'none');
        } else {
            card.classList.remove('compact-notif');
            card.querySelectorAll('.notif-message').forEach(m => m.style.display = '');
            card.querySelectorAll('.notif-time').forEach(t => t.style.display = '');
        }
    });
    document.querySelectorAll('.compact-toggle-btn').forEach(btn => {
        if (compactMode) {
            btn.classList.add('bg-indigo-100', 'border-indigo-300', 'text-indigo-700');
            btn.classList.remove('bg-gray-50', 'border-gray-200', 'text-gray-500');
        } else {
            btn.classList.remove('bg-indigo-100', 'border-indigo-300', 'text-indigo-700');
            btn.classList.add('bg-gray-50', 'border-gray-200', 'text-gray-500');
        }
    });
}

window.toggleCompactMode = function () {
    compactMode = !compactMode;
    localStorage.setItem('notif_compact', compactMode ? '1' : '0');
    applyCompactMode();
};

document.addEventListener('DOMContentLoaded', applyCompactMode);

// Mark all notifications as read
window.markAllNotificationsRead = function () {
    fetch('/faculty/notifications/mark-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('#notifBadge, #notifBadgeMobile').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.notif-unread-dot').forEach(dot => dot.remove());
            document.querySelectorAll('.notif-card.notif-unread').forEach(card => card.classList.remove('notif-unread'));
        }
    })
    .catch(() => {});
};

// Create IPCR modal controls
window.openCreateIpcrModal = function () {
    const modal = document.getElementById('createIpcrModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
};

window.closeCreateIpcrModal = function () {
    const modal = document.getElementById('createIpcrModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};
// Create OPCR modal controls
window.openCreateOpcrModal = function () {
    console.log('openCreateOpcrModal called');
    const modal = document.getElementById('createOpcrModal');
    console.log('Modal element:', modal);
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        console.error('createOpcrModal not found in DOM');
    }
};

window.closeCreateOpcrModal = function () {
    const modal = document.getElementById('createOpcrModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};

// Tab switching function
window.switchTab = function (tab) {
    const ipcrTab = document.getElementById('ipcrTab');
    const opcrTab = document.getElementById('opcrTab');
    const ipcrButtonArea = document.getElementById('createIpcrButtonArea');
    const opcrButtonArea = document.getElementById('createOpcrButtonArea');
    const opcrTemplatesSection = document.getElementById('opcrTemplatesSection');
    const ipcrTemplatesSection = document.getElementById('ipcrTemplatesSection');
    const submitIpcrSection = document.getElementById('submitIpcrSection');
    const submitOpcrSection = document.getElementById('submitOpcrSection');
    
    // Header Create Button (single dynamic button)
    const headerCreateDraftBtn = document.getElementById('headerCreateDraftBtn');
    const headerCreateDraftBtnLabel = document.getElementById('headerCreateDraftBtnLabel');
    
    // Update URL hash to remember tab state
    window.location.hash = tab;

    // Saved copies sections
    const ipcrSavedCopiesSection = document.getElementById('ipcrSavedCopiesSection');
    const opcrSavedCopiesSection = document.getElementById('opcrSavedCopiesSection');

    if (tab === 'ipcr') {
        // Activate IPCR tab
        ipcrTab.classList.remove('border-transparent', 'text-gray-500');
        ipcrTab.classList.add('border-blue-600', 'text-blue-600');

        // Deactivate OPCR tab
        if (opcrTab) {
            opcrTab.classList.remove('border-blue-600', 'text-blue-600');
            opcrTab.classList.add('border-transparent', 'text-gray-500');
        }

        // Update Header Create Button for IPCR
        if (headerCreateDraftBtn) {
            headerCreateDraftBtn.onclick = openCreateIpcrModal;
            headerCreateDraftBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
            headerCreateDraftBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
        if (headerCreateDraftBtnLabel) headerCreateDraftBtnLabel.textContent = 'Create IPCR';

        // Show IPCR button area (includes saved copies), hide OPCR
        if (ipcrButtonArea) {
            ipcrButtonArea.classList.remove('hidden');
            ipcrButtonArea.style.display = '';
        }
        if (opcrButtonArea) {
            opcrButtonArea.classList.add('hidden');
            opcrButtonArea.style.display = 'none';
        }

        // Show IPCR saved copies, hide OPCR saved copies
        if (ipcrSavedCopiesSection) ipcrSavedCopiesSection.classList.remove('hidden');
        if (opcrSavedCopiesSection) opcrSavedCopiesSection.classList.add('hidden');

        // Show IPCR templates, hide OPCR templates
        if (ipcrTemplatesSection) ipcrTemplatesSection.classList.remove('hidden');
        if (opcrTemplatesSection) opcrTemplatesSection.classList.add('hidden');

        // Show IPCR submit, hide OPCR submit
        if (submitIpcrSection) submitIpcrSection.classList.remove('hidden');
        if (submitOpcrSection) submitOpcrSection.classList.add('hidden');

        // Update header text to "Individual"
        const performanceType = document.getElementById('performanceType');
        const modalPerformanceType = document.getElementById('modalPerformanceType');
        if (performanceType) performanceType.textContent = 'Individual';
        if (modalPerformanceType) modalPerformanceType.textContent = 'Individual';
    } else if (tab === 'opcr') {
        // Activate OPCR tab
        if (opcrTab) {
            opcrTab.classList.remove('border-transparent', 'text-gray-500');
            opcrTab.classList.add('border-blue-600', 'text-blue-600');
        }

        // Deactivate IPCR tab
        ipcrTab.classList.remove('border-blue-600', 'text-blue-600');
        ipcrTab.classList.add('border-transparent', 'text-gray-500');

        // Update Header Create Button for OPCR
        if (headerCreateDraftBtn) {
            headerCreateDraftBtn.onclick = openCreateOpcrModal;
        }
        if (headerCreateDraftBtnLabel) headerCreateDraftBtnLabel.textContent = 'Create OPCR';

        // Show OPCR button area (includes saved copies), hide IPCR
        if (ipcrButtonArea) {
            ipcrButtonArea.classList.add('hidden');
            ipcrButtonArea.style.display = 'none';
        }
        if (opcrButtonArea) {
            opcrButtonArea.classList.remove('hidden');
            opcrButtonArea.style.display = '';
        }

        // Hide IPCR saved copies, show OPCR saved copies
        if (ipcrSavedCopiesSection) ipcrSavedCopiesSection.classList.add('hidden');
        if (opcrSavedCopiesSection) opcrSavedCopiesSection.classList.remove('hidden');

        // Hide IPCR templates, show OPCR templates
        if (ipcrTemplatesSection) ipcrTemplatesSection.classList.add('hidden');
        if (opcrTemplatesSection) opcrTemplatesSection.classList.remove('hidden');

        // Hide IPCR submit, show OPCR submit
        if (submitIpcrSection) submitIpcrSection.classList.add('hidden');
        if (submitOpcrSection) submitOpcrSection.classList.remove('hidden');

        // Update header text to "Office"
        const performanceType = document.getElementById('performanceType');
        const modalPerformanceType = document.getElementById('modalPerformanceType');
        if (performanceType) performanceType.textContent = 'Office';
        if (modalPerformanceType) modalPerformanceType.textContent = 'Office';

        // Fetch and render OPCR templates
        if (window.renderOpcrTemplates) {
            window.renderOpcrTemplates();
        }
    }
};

// Restore tab state on page load
window.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.slice(1); // Remove # prefix
    if (hash === 'opcr') {
        switchTab('opcr');
    } else {
        // Default to IPCR tab (already shown in HTML, but set hash for consistency)
        window.location.hash = 'ipcr';
    }
});