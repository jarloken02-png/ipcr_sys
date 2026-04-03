<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - University of Rizal System Binangonan</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth_login.css', 'resources/css/auth_verify-code.css', 'resources/js/auth_login.js', 'resources/js/auth_verify-code.js'])
</head>
<body data-show-code-error="{{ $errors->has('code') ? '1' : '0' }}" data-start-resend-timer="{{ session('success') ? '1' : '0' }}">
    <div class="auth-card">
        <div class="auth-card__image">
            <img src="{{ asset('images/login_img.png') }}" alt="Illustration">
        </div>

        <div class="auth-card__form">
            <div class="auth-card__form-inner">
                <div class="auth-header-brand">
                    <div class="auth-brand-text-wrapper">
                        <div class="auth-university-name">
                            <span class="uni-name">UNIVERSITY OF RIZAL SYSTEM</span>
                            <span class="uni-campus">BINANGONAN CAMPUS</span>
                        </div>
                    </div>
                    <img src="{{ asset('images/urs_logo.jpg') }}" alt="URS Logo" class="auth-logo">
                </div>

                <h1>Verify Your Email</h1>

                @if (session('success'))
                <div class="success-message">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <p class="code-description">
                We've sent a 6-digit verification code to<br>
                <span class="email-highlight">{{ $email ?? 'your email' }}</span>
            </p>

            <form method="POST" action="{{ route('password.verify') }}" id="verifyForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                <input type="hidden" name="code" id="fullCode" value="">

                <div class="code-inputs">
                    <input type="text" class="code-input" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                </div>
            </form>

            <div class="resend-section">
                <p>Didn't receive the code?</p>
                <form method="POST" action="{{ route('password.email') }}" id="resendForm">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                    <a href="#" id="resendLink" onclick="document.getElementById('resendForm').submit(); return false;">Resend Code</a>
                </form>
                <div class="timer" id="timer" style="display: none;">
                    Resend available in <span id="countdown">60</span>s
                </div>
            </div>

            <div class="back-link">
                <a href="{{ route('password.request') }}">← Change Email</a>
            </div>
            </div>
        </div>
    </div>

</body>
</html>
