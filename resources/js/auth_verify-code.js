document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');
    const form = document.getElementById('verifyForm');

    if (!inputs.length || !fullCodeInput || !form) return;

    // Focus first input on load
    inputs[0].focus();

    // Handle input for each box
    inputs.forEach((input, index) => {
        // Handle input
        input.addEventListener('input', function(e) {
            let value = this.value;
            
            // Only allow numbers
            value = value.replace(/[^0-9]/g, '');
            this.value = value;

            if (value.length === 1) {
                this.classList.add('filled');
                // Move to next input
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            } else {
                this.classList.remove('filled');
            }

            updateFullCode();
        });

        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            
            if (pastedData.length > 0) {
                for (let i = 0; i < pastedData.length && i < inputs.length; i++) {
                    inputs[i].value = pastedData[i];
                    inputs[i].classList.add('filled');
                }
                
                // Focus the next empty input or the last one
                const nextIndex = Math.min(pastedData.length, inputs.length - 1);
                inputs[nextIndex].focus();
                
                updateFullCode();
            }
        });

        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                inputs[index - 1].classList.remove('filled');
                updateFullCode();
            }
            
            // Handle arrow keys
            if (e.key === 'ArrowLeft' && index > 0) {
                inputs[index - 1].focus();
            }
            if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // Select all on focus
        input.addEventListener('focus', function() {
            this.select();
        });
    });

    function updateFullCode() {
        let code = '';
        inputs.forEach(input => {
            code += input.value;
        });
        fullCodeInput.value = code;
        
        // Auto-submit the form when all 6 digits are entered
        if (code.length === 6) {
            setTimeout(() => {
                form.submit();
            }, 300); // Small delay for better UX
        }
    }

    // Initialize button state
    updateFullCode();
});

// Error shake animation handler - to be called from blade if needed
window.showCodeError = function() {
    const inputs = document.querySelectorAll('.code-input');
    inputs.forEach(input => {
        input.classList.add('error');
        setTimeout(() => {
            input.classList.remove('error');
        }, 500);
    });
};

// Resend timer functionality
window.startResendTimer = function() {
    const resendLink = document.getElementById('resendLink');
    const timer = document.getElementById('timer');
    const countdown = document.getElementById('countdown');
    
    if (!resendLink || !timer || !countdown) return;
    
    let seconds = 60;

    resendLink.style.display = 'none';
    timer.style.display = 'block';

    const interval = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(interval);
            resendLink.style.display = 'inline';
            timer.style.display = 'none';
        }
    }, 1000);
};

document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    if (!body) return;

    if (body.dataset.showCodeError === '1' && typeof window.showCodeError === 'function') {
        window.showCodeError();
    }

    if (body.dataset.startResendTimer === '1' && typeof window.startResendTimer === 'function') {
        window.startResendTimer();
    }
});
