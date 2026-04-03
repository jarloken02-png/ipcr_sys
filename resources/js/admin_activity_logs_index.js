    window.openExportModal = function() {
        document.getElementById('exportModal').classList.remove('hidden');
    };
    window.closeExportModal = function() {
        document.getElementById('exportModal').classList.add('hidden');
    };
    
    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeExportModal();
    });
    
    // Close on backdrop click
    document.getElementById('exportModal').addEventListener('click', function(e) {
        if (e.target === this) closeExportModal();
    });

    // Real-time filter auto-submit
    (function () {
        const form = document.getElementById('filterForm');
        if (!form) return;

        // Debounced submit for the search text input
        const searchInput = form.querySelector('input[name="search"]');
        let searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => form.submit(), 500);
            });
        }

        // Instant submit for selects and date inputs
        form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
            el.addEventListener('change', function () {
                form.submit();
            });
        });
    })();
