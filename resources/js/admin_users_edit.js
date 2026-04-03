const photoInput = document.getElementById('photoInput');
const uploadForm = document.getElementById('photoUploadForm');
const uploadProgress = document.getElementById('uploadProgress');
const progressBar = document.getElementById('progressBar');
const uploadMessage = document.getElementById('uploadMessage');
const uploadText = document.getElementById('uploadText');
const adminUsersEditConfig = document.getElementById('adminUsersEditConfig');
const userId = adminUsersEditConfig ? adminUsersEditConfig.dataset.userId : null;

// Crop functionality variables
let cropper = null;
let currentFile = null;

let pendingDeleteForm = null;

// Toggle sidebar visibility
window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('sidebar-hidden');
    overlay.classList.toggle('hidden');
};

// Close sidebar when clicking a link (mobile only, auto close)
document.querySelectorAll('#sidebar a, #sidebar button').forEach(element => {
    element.addEventListener('click', () => {
        if (window.innerWidth < 1024) {
            document.getElementById('sidebar').classList.add('sidebar-hidden');
            document.getElementById('sidebarOverlay').classList.add('hidden');
        }
    });
});

// Handle window resize
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth >= 1024) {
        sidebar.classList.remove('sidebar-hidden');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.add('sidebar-hidden');
        overlay.classList.add('hidden');
    }
});

// Initialize on page load
window.addEventListener('load', () => {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth < 1024) {
        sidebar.classList.add('sidebar-hidden');
    }
});

// Photo input change event - open crop modal instead of uploading directly
photoInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        uploadText.textContent = this.files[0].name;
        openCropModal(this.files[0]);
    }
});

// Open crop modal
window.openCropModal = function(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        currentFile = file;
        const cropImage = document.getElementById('cropImage');
        cropImage.src = e.target.result;
        
        // Show crop modal
        document.getElementById('cropModal').classList.remove('hidden');
        
        // Initialize Cropper
        if (cropper) {
            cropper.destroy();
        }
        
        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            guides: true,
            center: true,
            responsive: true,
            restore: true,
            autoCropArea: 1,
            background: true,
        });
    };
    reader.readAsDataURL(file);
};

// Close crop modal
window.closeCropModal = function() {
    document.getElementById('cropModal').classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    photoInput.value = '';
    uploadText.textContent = 'Choose Photo';
    currentFile = null;
};

// Cropper controls
window.cropperZoomIn = function() {
    if (cropper) cropper.zoom(0.1);
};

window.cropperZoomOut = function() {
    if (cropper) cropper.zoom(-0.1);
};

window.cropperRotateLeft = function() {
    if (cropper) cropper.rotate(-90);
};

window.cropperRotateRight = function() {
    if (cropper) cropper.rotate(90);
};

window.cropperReset = function() {
    if (cropper) cropper.reset();
};

// Apply crop and upload
window.applyCropAndUpload = function() {
    if (!cropper || !currentFile) return;
    
    const canvas = cropper.getCroppedCanvas({
        maxWidth: 400,
        maxHeight: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    canvas.toBlob(function(blob) {
        uploadCroppedPhoto(blob);
    }, currentFile.type, 0.9);
};

// Upload cropped photo
function uploadCroppedPhoto(blob) {
    const formData = new FormData();
    formData.append('photo', blob, 'cropped.jpg');
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    
    uploadProgress.classList.remove('hidden');
    uploadMessage.innerHTML = '';
    closeCropModal();
    
    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
    }, 100);
    
    fetch(`/admin/panel/users/${userId}/photo/upload`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        setTimeout(() => {
            uploadProgress.classList.add('hidden');
            progressBar.style.width = '0%';
            
            if (data.success) {
                uploadMessage.innerHTML = '<span class="text-green-600 text-xs"><i class="fas fa-check-circle mr-1"></i>' + data.message + '</span>';
                loadPhotos();
            } else {
                uploadMessage.innerHTML = '<span class="text-red-600 text-xs"><i class="fas fa-times-circle mr-1"></i>' + data.message + '</span>';
            }
        }, 500);
    })
    .catch(error => {
        clearInterval(progressInterval);
        uploadProgress.classList.add('hidden');
        progressBar.style.width = '0%';
        uploadMessage.innerHTML = '<span class="text-red-600 text-xs"><i class="fas fa-times-circle mr-1"></i>Upload failed</span>';
        console.error('Error:', error);
    });
}

// Old uploadPhoto function - kept for backward compatibility but not used
function uploadPhoto(file) {
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    uploadProgress.classList.remove('hidden');
    uploadMessage.innerHTML = '';

    fetch(`/admin/panel/users/${userId}/photo/upload`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        uploadProgress.classList.add('hidden');
        if (data.success) {
            uploadMessage.innerHTML = '<span class="text-green-600 text-xs"><i class="fas fa-check-circle mr-1"></i>' + data.message + '</span>';
            photoInput.value = '';
            uploadText.textContent = 'Choose Photo';
            loadPhotos();
        } else {
            uploadMessage.innerHTML = '<span class="text-red-600 text-xs"><i class="fas fa-times-circle mr-1"></i>' + data.message + '</span>';
        }
    })
    .catch(error => {
        uploadProgress.classList.add('hidden');
        uploadMessage.innerHTML = '<span class="text-red-600 text-xs"><i class="fas fa-times-circle mr-1"></i>Upload failed</span>';
        console.error('Error:', error);
    });
}

// Load all photos
function loadPhotos() {
    fetch(`/admin/panel/users/${userId}/photos`)
        .then(response => response.json())
        .then(data => {
            const allPhotos = document.getElementById('allPhotos');
            const photoCount = document.getElementById('photoCount');
            photoCount.textContent = data.photos.length;
            
            if (data.photos.length === 0) {
                allPhotos.innerHTML = '<p class="text-gray-600 text-xs col-span-3">No photos yet</p>';
                return;
            }

            allPhotos.innerHTML = data.photos.map(photo => `
                <div class="relative group">
                    <img src="${photo.url}" alt="Photo" class="w-full aspect-square object-cover rounded-lg">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 rounded-lg transition flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        ${!photo.is_profile ? `
                            <button onclick="setAsProfile(${photo.id})" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg text-xs transition" title="Set as profile">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        <button onclick="deletePhoto(${photo.id}, '${photo.url}')" class="text-red-600 hover:text-red-900 bg-white p-2 rounded-lg transition" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    ${photo.is_profile ? '<span class="absolute top-1 right-1 bg-blue-500 text-white px-2 py-1 rounded text-xs font-semibold"><i class="fas fa-check"></i></span>' : ''}
                </div>
            `).join('');
        });
}

let pendingPhotoId = null;
let pendingProfilePhotoId = null;

// Set photo as profile - open modal
window.setAsProfile = function(photoId) {
    pendingProfilePhotoId = photoId;
    document.getElementById('setProfileModal').classList.remove('hidden');
};

// Close set profile modal
window.closeSetProfileModal = function() {
    document.getElementById('setProfileModal').classList.add('hidden');
    pendingProfilePhotoId = null;
};

// Confirm set as profile
window.confirmSetProfile = function() {
    if (pendingProfilePhotoId) {
        fetch(`/admin/panel/users/${userId}/photos/${pendingProfilePhotoId}/set-profile`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            closeSetProfileModal();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to set as profile');
            }
        })
        .catch(error => {
            closeSetProfileModal();
            console.error('Error:', error);
            alert('Failed to set as profile');
        });
    }
};

// Delete photo - open modal
window.deletePhoto = function(photoId, photoUrl) {
    pendingPhotoId = photoId;
    document.getElementById('deletePhotoModal').classList.remove('hidden');
};

// Close delete photo modal
window.closeDeletePhotoModal = function() {
    document.getElementById('deletePhotoModal').classList.add('hidden');
    pendingPhotoId = null;
};

// Confirm delete photo
window.confirmDeletePhoto = function() {
    if (pendingPhotoId) {
        fetch(`/admin/panel/users/${userId}/photos/${pendingPhotoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            closeDeletePhotoModal();
            if (data.success) {
                loadPhotos();
            } else {
                alert(data.message || 'Failed to delete photo');
            }
        })
        .catch(error => {
            closeDeletePhotoModal();
            console.error('Error:', error);
            alert('Failed to delete photo');
        });
    }
};

// Load photos on page load
loadPhotos();
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

// Handle role selection to show/hide department and designation
window.handleRoleSelection = function() {
    const hrCheckbox = document.getElementById('role_hr');
    const directorCheckbox = document.getElementById('role_director');
    const departmentField = document.getElementById('department_id');
    const designationField = document.getElementById('designation_id');
    
    if (!departmentField || !designationField) return;
    
    const isHrOrDirector = (hrCheckbox && hrCheckbox.checked) || (directorCheckbox && directorCheckbox.checked);
    
    // Get the parent divs
    const departmentDiv = departmentField.closest('div').parentElement;
    const designationDiv = designationField.closest('div').parentElement;
    
    if (isHrOrDirector) {
        // Hide department and designation fields
        if (departmentDiv && departmentDiv.querySelector('#department_id')) {
            departmentDiv.style.display = 'none';
        }
        if (designationDiv && designationDiv.querySelector('#designation_id')) {
            designationDiv.style.display = 'none';
        }
        
        // Clear their values
        departmentField.value = '';
        designationField.value = '';
    } else {
        // Show department and designation fields
        if (departmentDiv && departmentDiv.querySelector('#department_id')) {
            departmentDiv.style.display = 'block';
        }
        if (designationDiv && designationDiv.querySelector('#designation_id')) {
            designationDiv.style.display = 'block';
        }
    }
};

// Initialize role selection handler on page load
setTimeout(function() {
    handleRoleSelection();
}, 100);