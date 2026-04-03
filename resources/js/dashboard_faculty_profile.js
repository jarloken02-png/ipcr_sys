// Mobile menu toggle
window.toggleMobileMenu = function() {
    const menu = document.querySelector('.mobile-menu');
    const overlay = document.querySelector('.mobile-menu-overlay');
    menu.classList.toggle('active');
    overlay.classList.toggle('active');
};

// Notification popup toggle for desktop
window.toggleNotificationPopup = function() {
    const popup = document.getElementById('notificationPopup');
    if (popup) popup.classList.toggle('active');
};

// Notification popup toggle for mobile — reuses same popup
window.toggleNotificationPopupMobile = function() {
    const popup = document.getElementById('notificationPopup');
    if (popup) popup.classList.toggle('active');
};

// Close notification popup when clicking outside
document.addEventListener('click', function(e) {
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

window.toggleCompactMode = function() {
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

// Profile completeness details toggle
window.toggleCompletenessDetails = function() {
    const details = document.getElementById('completenessDetails');
    const toggle = document.getElementById('completenessToggle');
    
    if (details) {
        details.classList.toggle('hidden');
        const isVisible = !details.classList.contains('hidden');
        
        if (toggle) {
            toggle.innerHTML = isVisible 
                ? 'Hide details <i class="fas fa-chevron-up text-[9px] ml-0.5 completeness-chevron"></i>'
                : 'Show details <i class="fas fa-chevron-down text-[9px] ml-0.5 completeness-chevron"></i>';
        }
    }
};

// Open email verification modal after profile email change
document.addEventListener('DOMContentLoaded', function() {
    const shouldOpenVerify = sessionStorage.getItem('triggerEmailVerification');
    if (shouldOpenVerify) {
        sessionStorage.removeItem('triggerEmailVerification');
        if (typeof window.openEmailVerificationModal === 'function') {
            window.openEmailVerificationModal();
        }
    }
});

// Toggle password visibility
window.togglePasswordVisibility = function(fieldId) {
    const input = document.getElementById(fieldId);
    const eyeOpen = document.getElementById(fieldId + '_eye_open');
    const eyeClosed = document.getElementById(fieldId + '_eye_closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
};

// Change Password Modal Functions
window.openChangePasswordModal = function() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
    // Reset form
    document.getElementById('changePasswordForm').reset();
    // Reset all password fields to hidden state
    ['current_password', 'new_password', 'new_password_confirmation'].forEach(fieldId => {
        const input = document.getElementById(fieldId);
        const eyeOpen = document.getElementById(fieldId + '_eye_open');
        const eyeClosed = document.getElementById(fieldId + '_eye_closed');
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    });
    // Clear any previous messages
    document.getElementById('passwordMessage').classList.add('hidden');
    // Clear error messages
    ['current_password_error', 'new_password_error', 'new_password_confirmation_error'].forEach(id => {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).textContent = '';
    });
};

window.closeChangePasswordModal = function() {
    document.getElementById('changePasswordModal').classList.add('hidden');
};

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('changePasswordModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeChangePasswordModal();
            }
        });
    }

    // Handle form submission
    const form = document.getElementById('changePasswordForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageDiv = document.getElementById('passwordMessage');
            
            // Clear previous errors
            ['current_password_error', 'new_password_error', 'new_password_confirmation_error'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
                document.getElementById(id).textContent = '';
            });
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Success
                    messageDiv.className = 'rounded-lg p-3 text-sm bg-green-50 text-green-800 border border-green-200';
                    messageDiv.textContent = data.message || 'Password updated successfully!';
                    messageDiv.classList.remove('hidden');
                    
                    // Reset form and close after 2 seconds
                    setTimeout(() => {
                        window.closeChangePasswordModal();
                        this.reset();
                    }, 2000);
                } else {
                    // Error
                    if (data.errors) {
                        // Display validation errors
                        Object.keys(data.errors).forEach(key => {
                            const errorElement = document.getElementById(key + '_error');
                            if (errorElement) {
                                errorElement.textContent = data.errors[key][0];
                                errorElement.classList.remove('hidden');
                            }
                        });
                    } else {
                        messageDiv.className = 'rounded-lg p-3 text-sm bg-red-50 text-red-800 border border-red-200';
                        messageDiv.textContent = data.message || 'An error occurred. Please try again.';
                        messageDiv.classList.remove('hidden');
                    }
                }
            } catch (error) {
                messageDiv.className = 'rounded-lg p-3 text-sm bg-red-50 text-red-800 border border-red-200';
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.classList.remove('hidden');
            }
        });
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.closeChangePasswordModal();
        window.closeEditProfileModal();
        window.closePhotoGalleryModal();
        window.closeSetProfileModal();
        window.closeDeletePhotoModal();
        window.closeEmailVerificationModal();
    }
});

// Email Verification Functions
window.openEmailVerificationModal = function() {
    document.getElementById('emailVerificationModal').classList.remove('hidden');
    // Reset to step 1
    document.getElementById('verifyStep1').classList.remove('hidden');
    document.getElementById('verifyStep2').classList.add('hidden');
    // Clear code inputs
    const codeInputs = document.querySelectorAll('.verify-code-input');
    codeInputs.forEach(input => {
        input.value = '';
        input.classList.remove('filled', 'error');
    });
    document.getElementById('verification_code_hidden').value = '';
    // Clear messages
    document.getElementById('sendCodeMessage').classList.add('hidden');
    document.getElementById('sendCodeMessage').textContent = '';
    document.getElementById('verifyCodeMessage').classList.add('hidden');
    document.getElementById('verifyCodeMessage').textContent = '';
};

window.closeEmailVerificationModal = function() {
    document.getElementById('emailVerificationModal').classList.add('hidden');
};

window.sendVerificationCode = function() {
    const btn = document.getElementById('sendCodeBtn');
    const messageDiv = document.getElementById('sendCodeMessage');
    
    // Disable button
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch('/email/verification/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.className = 'mt-3 text-sm bg-green-50 text-green-700 p-3 rounded-lg border border-green-200';
            messageDiv.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + data.message;
            messageDiv.classList.remove('hidden');
            
            // Move to step 2 after 1 second
            setTimeout(() => {
                document.getElementById('verifyStep1').classList.add('hidden');
                document.getElementById('verifyStep2').classList.remove('hidden');
                // Focus first digit input
                const firstInput = document.querySelector('.verify-code-input');
                if (firstInput) firstInput.focus();
            }, 1000);
        } else {
            messageDiv.className = 'mt-3 text-sm bg-red-50 text-red-700 p-3 rounded-lg border border-red-200';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>' + data.message;
            messageDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        messageDiv.className = 'mt-3 text-sm bg-red-50 text-red-700 p-3 rounded-lg border border-red-200';
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>An error occurred. Please try again.';
        messageDiv.classList.remove('hidden');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Verification Code';
    });
};

window.backToStep1 = function() {
    document.getElementById('verifyStep2').classList.add('hidden');
    document.getElementById('verifyStep1').classList.remove('hidden');
    // Clear code inputs
    const codeInputs = document.querySelectorAll('.verify-code-input');
    codeInputs.forEach(input => {
        input.value = '';
        input.classList.remove('filled', 'error');
    });
    document.getElementById('verification_code_hidden').value = '';
    document.getElementById('verifyCodeMessage').classList.add('hidden');
};

// Verify code form submission
document.addEventListener('DOMContentLoaded', function() {
    const verifyForm = document.getElementById('verifyCodeForm');
    if (verifyForm) {
        // Handle individual digit inputs
        const codeInputs = document.querySelectorAll('.verify-code-input');
        const hiddenCodeInput = document.getElementById('verification_code_hidden');
        
        if (codeInputs.length && hiddenCodeInput) {
            // Handle input for each box
            codeInputs.forEach((input, index) => {
                // Handle input
                input.addEventListener('input', function(e) {
                    let value = this.value;
                    
                    // Only allow numbers
                    value = value.replace(/[^0-9]/g, '');
                    this.value = value;

                    if (value.length === 1) {
                        this.classList.add('filled');
                        // Move to next input
                        if (index < codeInputs.length - 1) {
                            codeInputs[index + 1].focus();
                        }
                    } else {
                        this.classList.remove('filled');
                    }

                    updateVerificationCode();
                });

                // Handle paste
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                    
                    if (pastedData.length > 0) {
                        for (let i = 0; i < pastedData.length && i < codeInputs.length; i++) {
                            codeInputs[i].value = pastedData[i];
                            codeInputs[i].classList.add('filled');
                        }
                        
                        // Focus the next empty input or the last one
                        const nextIndex = Math.min(pastedData.length, codeInputs.length - 1);
                        codeInputs[nextIndex].focus();
                        
                        updateVerificationCode();
                    }
                });

                // Handle backspace
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        codeInputs[index - 1].focus();
                        codeInputs[index - 1].value = '';
                        codeInputs[index - 1].classList.remove('filled');
                        updateVerificationCode();
                    }
                    
                    // Handle arrow keys
                    if (e.key === 'ArrowLeft' && index > 0) {
                        codeInputs[index - 1].focus();
                    }
                    if (e.key === 'ArrowRight' && index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus();
                    }
                });

                // Select all on focus
                input.addEventListener('focus', function() {
                    this.select();
                });
            });

            function updateVerificationCode() {
                let code = '';
                codeInputs.forEach(input => {
                    code += input.value;
                });
                hiddenCodeInput.value = code;
                
                // Auto-submit the form when all 6 digits are entered
                if (code.length === 6) {
                    setTimeout(() => {
                        submitVerificationCode(code);
                    }, 300); // Small delay for better UX
                }
            }
        }
        
        // Manual form submission (for the verify button)
        verifyForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const code = document.getElementById('verification_code_hidden').value;
            submitVerificationCode(code);
        });
    }
    
    async function submitVerificationCode(code) {
        const messageDiv = document.getElementById('verifyCodeMessage');
        const btn = document.getElementById('verifyCodeBtn');
        const codeInputs = document.querySelectorAll('.verify-code-input');
        
        if (code.length !== 6) {
            messageDiv.className = 'text-sm bg-red-50 text-red-700 p-3 rounded-lg border border-red-200';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Please enter a 6-digit code.';
            messageDiv.classList.remove('hidden');
            
            // Show error animation
            codeInputs.forEach(input => {
                input.classList.add('error');
                setTimeout(() => input.classList.remove('error'), 500);
            });
            return;
        }
        
        // Disable button
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Verifying...';
        }
        messageDiv.classList.add('hidden');
        
        try {
            const response = await fetch('/email/verification/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ code: code })
            });
            
            const data = await response.json();
            
            if (data.success) {
                messageDiv.className = 'text-sm bg-green-50 text-green-700 p-3 rounded-lg border border-green-200';
                messageDiv.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + data.message;
                messageDiv.classList.remove('hidden');
                
                // Show success animation
                codeInputs.forEach(input => {
                    input.classList.add('filled');
                });
                
                // Reload page after 2 seconds to update verification status
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                messageDiv.className = 'text-sm bg-red-50 text-red-700 p-3 rounded-lg border border-red-200';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>' + data.message;
                messageDiv.classList.remove('hidden');
                
                // Show error animation
                codeInputs.forEach(input => {
                    input.classList.add('error');
                    setTimeout(() => input.classList.remove('error'), 500);
                });
                
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i> Verify';
                }
            }
        } catch (error) {
            messageDiv.className = 'text-sm bg-red-50 text-red-700 p-3 rounded-lg border border-red-200';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>An error occurred. Please try again.';
            messageDiv.classList.remove('hidden');
            console.error('Error:', error);
            
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Verify';
            }
        }
    }
});

// Photo Management
let pendingPhotoId = null;

// Load all photos into gallery
function loadPhotos() {
    fetch('/faculty/profile/photos')
        .then(response => response.json())
        .then(data => {
            // Update all photo count displays
            const countElements = ['galleryPhotoCount', 'sidebarPhotoCount', 'activityPhotoCount'];
            countElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = data.photos.length;
            });

            const galleryPhotos = document.getElementById('galleryPhotos');
            if (!galleryPhotos) return;
            
            if (data.photos.length === 0) {
                galleryPhotos.innerHTML = '<p class="text-sm text-gray-500 col-span-full text-center py-8"><i class="fas fa-image text-gray-300 text-3xl block mb-2"></i>No photos uploaded yet</p>';
                return;
            }

            galleryPhotos.innerHTML = '';
            data.photos.forEach(photo => {
                const photoDiv = document.createElement('div');
                photoDiv.className = 'relative group rounded-lg overflow-hidden bg-gray-100';
                photoDiv.innerHTML = `
                    <img src="${photo.url}" alt="Profile photo" class="w-full aspect-square object-cover cursor-pointer">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        <button onclick="openSetProfileModal(${photo.id})" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg text-xs" title="Set as profile photo">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <button onclick="openDeletePhotoModal(${photo.id})" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg text-xs" title="Delete photo">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    ${photo.is_profile ? '<div class="absolute top-1.5 right-1.5 bg-green-500 text-white px-2 py-0.5 rounded-full text-[10px] font-semibold shadow"><i class="fas fa-check mr-0.5"></i>Current</div>' : ''}
                `;
                galleryPhotos.appendChild(photoDiv);
            });
        })
        .catch(error => console.error('Error loading photos:', error));
}

// Photo Gallery Modal
window.openPhotoGalleryModal = function() {
    document.getElementById('photoGalleryModal').classList.remove('hidden');
    loadPhotos();
};

window.closePhotoGalleryModal = function() {
    document.getElementById('photoGalleryModal').classList.add('hidden');
};

// Gallery photo upload with crop
document.addEventListener('DOMContentLoaded', function() {
    const galleryPhotoInput = document.getElementById('galleryPhotoInput');
    if (galleryPhotoInput) {
        galleryPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                showGalleryMessage('File size must be less than 5MB', 'error');
                this.value = '';
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showGalleryMessage('Please upload a valid image file (JPEG, PNG, GIF, WebP)', 'error');
                this.value = '';
                return;
            }

            // Store file and open crop modal
            currentFile = file;
            window._uploadSource = 'gallery';
            openCropModal(file);
        });
    }

    // Close gallery modal on outside click
    const galleryModal = document.getElementById('photoGalleryModal');
    if (galleryModal) {
        galleryModal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closePhotoGalleryModal();
            }
        });
    }
});

function showGalleryMessage(message, type) {
    const messageDiv = document.getElementById('galleryUploadMessage');
    if (!messageDiv) return;
    messageDiv.textContent = message;
    messageDiv.className = type === 'success' ? 'text-xs mt-2 text-green-600' : 'text-xs mt-2 text-red-600';
    setTimeout(() => {
        messageDiv.textContent = '';
        messageDiv.className = 'text-xs mt-2';
    }, 3000);
}

// Set as profile modal
window.openSetProfileModal = function(photoId) {
    pendingPhotoId = photoId;
    document.getElementById('setProfileModal').classList.remove('hidden');
};

window.closeSetProfileModal = function() {
    document.getElementById('setProfileModal').classList.add('hidden');
    pendingPhotoId = null;
};

window.confirmSetProfile = function() {
    if (!pendingPhotoId) return;

    fetch('/faculty/profile/photo/set-profile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({ photo_id: pendingPhotoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeSetProfileModal();
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
};

// Delete photo modal
window.openDeletePhotoModal = function(photoId) {
    pendingPhotoId = photoId;
    document.getElementById('deletePhotoModal').classList.remove('hidden');
};

window.closeDeletePhotoModal = function() {
    document.getElementById('deletePhotoModal').classList.add('hidden');
    pendingPhotoId = null;
};

window.confirmDeletePhoto = function() {
    if (!pendingPhotoId) return;

    fetch(`/faculty/profile/photo/${pendingPhotoId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeletePhotoModal();
            loadPhotos();
        }
    })
    .catch(error => console.error('Error:', error));
};

// Edit Profile Modal Functions
window.openEditProfileModal = function() {
    document.getElementById('editProfileModal').classList.remove('hidden');
    // Clear any previous messages
    document.getElementById('editProfileMessage').classList.add('hidden');
    // Clear error messages
    ['edit_name_error', 'edit_email_error', 'edit_username_error', 'edit_phone_error', 'edit_department_id_error', 'edit_designation_id_error'].forEach(id => {
        const errorElement = document.getElementById(id);
        if (errorElement) {
            errorElement.classList.add('hidden');
            errorElement.textContent = '';
        }
    });
};

window.closeEditProfileModal = function() {
    document.getElementById('editProfileModal').classList.add('hidden');
};

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editProfileModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeEditProfileModal();
            }
        });
    }

    // Handle edit profile form submission
    const editForm = document.getElementById('editProfileForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageDiv = document.getElementById('editProfileMessage');
            const actionUrl = this.dataset.action;
            
            // Clear previous errors
            ['edit_name_error', 'edit_email_error', 'edit_username_error', 'edit_phone_error', 'edit_department_id_error', 'edit_designation_id_error'].forEach(id => {
                const errorElement = document.getElementById(id);
                if (errorElement) {
                    errorElement.classList.add('hidden');
                    errorElement.textContent = '';
                }
            });
            
            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Success
                    messageDiv.className = 'rounded-lg p-3 text-sm bg-green-50 text-green-800 border border-green-200';
                    messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (data.message || 'Profile updated successfully!');
                    messageDiv.classList.remove('hidden');

                    if (data.email_changed) {
                        sessionStorage.setItem('triggerEmailVerification', '1');
                    }
                    
                    // Close modal and reload page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    // Error
                    if (data.errors) {
                        // Display validation errors
                        Object.keys(data.errors).forEach(key => {
                            const errorElement = document.getElementById('edit_' + key + '_error');
                            if (errorElement) {
                                errorElement.textContent = data.errors[key][0];
                                errorElement.classList.remove('hidden');
                            }
                        });
                    } else {
                        messageDiv.className = 'rounded-lg p-3 text-sm bg-red-50 text-red-800 border border-red-200';
                        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + (data.message || 'An error occurred. Please try again.');
                        messageDiv.classList.remove('hidden');
                    }
                }
            } catch (error) {
                messageDiv.className = 'rounded-lg p-3 text-sm bg-red-50 text-red-800 border border-red-200';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>An error occurred. Please try again.';
                messageDiv.classList.remove('hidden');
                console.error('Error:', error);
            }
        });
    }
});

// Modal photo upload functionality with crop
let cropper = null;
let currentFile = null;

document.addEventListener('DOMContentLoaded', function() {
    const modalPhotoInput = document.getElementById('modalPhotoInput');
    
    if (modalPhotoInput) {
        modalPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showModalMessage('File size must be less than 5MB', 'error');
                this.value = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showModalMessage('Please upload a valid image file (JPEG, PNG, GIF, WebP)', 'error');
                this.value = '';
                return;
            }

            // Store file and open crop modal
            currentFile = file;
            window._uploadSource = 'modal';
            openCropModal(file);
        });
    }
});

window.openCropModal = function(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const cropImage = document.getElementById('cropImage');
        cropImage.src = e.target.result;
        
        // Show modal
        document.getElementById('cropModal').classList.remove('hidden');
        
        // Initialize cropper after a short delay to ensure image is loaded
        setTimeout(() => {
            if (cropper) {
                cropper.destroy();
            }
            
            cropper = new Cropper(cropImage, {
                aspectRatio: 1, // Square crop for profile picture
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                minContainerWidth: 200,
                minContainerHeight: 200,
            });
        }, 100);
    };
    reader.readAsDataURL(file);
};

window.closeCropModal = function() {
    document.getElementById('cropModal').classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    // Reset file input
    const modalPhotoInput = document.getElementById('modalPhotoInput');
    if (modalPhotoInput) {
        modalPhotoInput.value = '';
    }
    currentFile = null;
};

window.cropperZoomIn = function() {
    if (cropper) {
        cropper.zoom(0.1);
    }
};

window.cropperZoomOut = function() {
    if (cropper) {
        cropper.zoom(-0.1);
    }
};

window.cropperRotateLeft = function() {
    if (cropper) {
        cropper.rotate(-90);
    }
};

window.cropperRotateRight = function() {
    if (cropper) {
        cropper.rotate(90);
    }
};

window.cropperReset = function() {
    if (cropper) {
        cropper.reset();
    }
};

window.applyCropAndUpload = function() {
    if (!cropper) return;
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Convert canvas to blob
    canvas.toBlob(function(blob) {
        if (!blob) {
            showModalMessage('Failed to process image', 'error');
            return;
        }
        
        // Create a file from blob
        const croppedFile = new File([blob], currentFile.name, {
            type: currentFile.type,
            lastModified: Date.now(),
        });

        const uploadSource = window._uploadSource || 'modal';
        
        // Close crop modal
        closeCropModal();
        
        // Show preview in edit profile modal if source is modal
        if (uploadSource === 'modal') {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('modalPhotoPreview');
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(croppedFile);
        }
        
        // Upload the cropped photo
        uploadCroppedPhoto(croppedFile, uploadSource);
        
        // Reset upload source
        window._uploadSource = 'modal';
    }, currentFile.type, 0.9);
}

function uploadCroppedPhoto(file, source) {
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value);

    let progressContainer, progressBar;
    
    if (source === 'gallery') {
        progressContainer = document.getElementById('galleryUploadProgress');
        progressBar = document.getElementById('galleryProgressBar');
    } else {
        progressContainer = document.getElementById('modalUploadProgress');
        progressBar = document.getElementById('modalProgressBar');
    }

    if (progressContainer) progressContainer.classList.remove('hidden');
    if (progressBar) progressBar.style.width = '0%';

    fetch('/faculty/profile/photo/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (progressBar) progressBar.style.width = '100%';
        setTimeout(() => {
            if (progressContainer) progressContainer.classList.add('hidden');
            if (data.success) {
                if (source === 'gallery') {
                    showGalleryMessage(data.message, 'success');
                    loadPhotos();
                } else {
                    showModalMessage(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                const msg = data.message || 'Failed to upload photo';
                if (source === 'gallery') {
                    showGalleryMessage(msg, 'error');
                } else {
                    showModalMessage(msg, 'error');
                }
            }
        }, 500);
    })
    .catch(error => {
        if (progressContainer) progressContainer.classList.add('hidden');
        const msg = 'An error occurred while uploading';
        if (source === 'gallery') {
            showGalleryMessage(msg, 'error');
        } else {
            showModalMessage(msg, 'error');
        }
        console.error('Upload error:', error);
    });
}

function showModalMessage(message, type) {
    const messageDiv = document.getElementById('modalUploadMessage');
    messageDiv.textContent = message;
    messageDiv.className = type === 'success' ? 'text-xs mt-1 text-green-600' : 'text-xs mt-1 text-red-600';
    
    setTimeout(() => {
        messageDiv.textContent = '';
        messageDiv.className = 'text-xs mt-1';
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.body) {
        document.body.style.visibility = 'visible';
    }
});
