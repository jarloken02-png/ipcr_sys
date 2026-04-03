window.togglePasswordVisibility = function () {
    const passwordField = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-btn i');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleBtn.classList.remove('fa-eye');
        toggleBtn.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleBtn.classList.remove('fa-eye-slash');
        toggleBtn.classList.add('fa-eye');
    }
}

// Add scroll effect for mobile view
document.addEventListener('DOMContentLoaded', () => {
    const bgImage = document.querySelector('.auth-card__image');
    const authForm = document.querySelector('.auth-card__form');

    const temporarySuccess = document.querySelector('.temporary-success-message[data-autohide="true"]');
    if (temporarySuccess) {
        window.setTimeout(() => {
            temporarySuccess.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            temporarySuccess.style.opacity = '0';
            temporarySuccess.style.transform = 'translateY(-4px)';

            window.setTimeout(() => {
                temporarySuccess.remove();
            }, 350);
        }, 2500);
    }

    if (bgImage) {
        window.addEventListener('scroll', () => {
            if (window.innerWidth <= 900) {
                const scrollPos = window.scrollY;
                // Scale from 1 to 1.15 based on scroll depth
                const scaleValue = 1 + (scrollPos * 0.001);
                bgImage.style.transform = `scale(${Math.min(scaleValue, 1.25)})`;

                // Remove top borders when hitting the top (margin-top is 30vh)
                if (authForm) {
                    const threshold = window.innerHeight * 0.3;
                    if (scrollPos >= threshold - 5) {
                        authForm.classList.add('hit-top');
                    } else {
                        authForm.classList.remove('hit-top');
                    }
                }
            } else {
                bgImage.style.transform = 'scale(1)'; // reset on desktop
                if (authForm) {
                    authForm.classList.remove('hit-top');
                }
            }
        });
    }

    // --- Dark Mode Logic (Temporary Testing) ---
    const body = document.body;
    const bgImageTag = document.querySelector('.auth-card__image img');

    // Create the temporary toggle switch
    const toggleContainer = document.createElement('div');
    toggleContainer.className = 'temp-dark-mode-toggle';
    toggleContainer.innerHTML = `
        <button id="darkModeSwitch" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </button>
    `;
    document.body.appendChild(toggleContainer);

    const toggleBtn = document.getElementById('darkModeSwitch');

    // Check local storage for preference
    const savedMode = localStorage.getItem('auth_dark_mode');
    if (savedMode === 'true') {
        enableDarkMode();
    }

    toggleBtn.addEventListener('click', () => {
        if (body.classList.contains('dark-mode')) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    });

    function enableDarkMode() {
        body.classList.add('dark-mode');
        localStorage.setItem('auth_dark_mode', 'true');
        toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        if (bgImageTag) {
            bgImageTag.src = bgImageTag.src.replace('login_img.png', 'login_imgdrk.png');
        }
    }

    function disableDarkMode() {
        body.classList.remove('dark-mode');
        localStorage.setItem('auth_dark_mode', 'false');
        toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        if (bgImageTag) {
            bgImageTag.src = bgImageTag.src.replace('login_imgdrk.png', 'login_img.png');
        }
    }
});
