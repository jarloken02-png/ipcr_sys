        window.toggleMobileMenu = function() {
            const menu = document.getElementById('mobileMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        };

        window.openDeanIpcrInNewTab = function(event, url) {
            if (event) {
                event.preventDefault();
            }

            const newTab = window.open(url, '_blank');

            if (newTab) {
                try {
                    newTab.focus();
                } catch (_) {
                    // Ignore focus errors from browser policies.
                }
            } else if (url) {
                // Popup blocked: fallback to current-tab navigation.
                window.location.href = url;
            }

            return false;
        };

        window.toggleNotificationPopup = function() {
            const popup = document.getElementById('notificationPopup');
            if (popup) {
                popup.classList.toggle('active');
            }
        };

        document.addEventListener('click', function(e) {
            const popup = document.getElementById('notificationPopup');
            const notificationBtn = e.target.closest('button[onclick*="toggleNotificationPopup"]');

            if (!notificationBtn && popup && !popup.contains(e.target)) {
                popup.classList.remove('active');
            }
        });

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

        window.toggleCompactMode = function() {
            compactMode = !compactMode;
            localStorage.setItem('notif_compact', compactMode ? '1' : '0');
            applyCompactMode();
        };

        window.markAllNotificationsRead = function() {
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
                    document.querySelectorAll('#notifBadge').forEach(el => el.classList.add('hidden'));
                    document.querySelectorAll('.notif-unread-dot').forEach(dot => dot.remove());
                    document.querySelectorAll('.notif-card.notif-unread').forEach(card => card.classList.remove('notif-unread'));
                }
            })
            .catch(() => {});
        };

        document.addEventListener('DOMContentLoaded', applyCompactMode);
