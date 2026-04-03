        let currentSubmissionIdToDelete = null;

        window.openAdminDeleteModal = function(submissionId) {
            currentSubmissionIdToDelete = submissionId;
            document.getElementById('adminDeleteModal').classList.remove('hidden');
        };

        window.closeAdminDeleteModal = function() {
            currentSubmissionIdToDelete = null;
            document.getElementById('adminDeleteModal').classList.add('hidden');
        };

        window.confirmAdminDeleteSubmission = function() {
            if (!currentSubmissionIdToDelete) return;
            
            const btn = document.getElementById('confirmDeleteBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Deleting...</span>';
            btn.disabled = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const url = `/faculty/ipcr/submissions/${currentSubmissionIdToDelete}`;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().catch(() => ({ success: false, message: 'Invalid response' })))
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete submission: ' + (data.message || 'Unknown error'));
                        closeAdminDeleteModal();
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    alert('An error occurred: ' + error.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            } catch (error) {
                alert('An error occurred: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };
