<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University of Rizal System Binangonan</title>
    <link rel="icon" type="image/jpeg" href="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth_login.css', 'resources/css/auth_reset-password.css', 'resources/js/auth_login.js', 'resources/js/auth_reset-password.js'])
</head>
<body>
    <div class="auth-card">
        <div class="auth-card__image">
            <img
                src="{{ \App\Support\MediaAsset::publicImageUrl('login_img.png') }}"
                data-light-src="{{ \App\Support\MediaAsset::publicImageUrl('login_img.png') }}"
                data-dark-src="{{ \App\Support\MediaAsset::publicImageUrl('login_imgdrk.png') }}"
                alt="Illustration"
            >
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
                    <img src="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}" alt="URS Logo" class="auth-logo">
                </div>

                <h1>Create New Password</h1>

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

            <div class="verified-badge">
                <i class="fas fa-check-circle"></i>
                Email verified: {{ $email ?? session('verified_email') }}
            </div>

            <div class="password-requirements">
                <p>Password Requirements:</p>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Use a mix of letters, numbers, and symbols for better security</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? session('verified_email') }}">
                <input type="hidden" name="reset_token" value="{{ $reset_token ?? session('reset_token') }}">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-toggle">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter new password"
                            required
                            autofocus
                            style="padding-left: 2.5rem;"
                        >
                        <button type="button" class="toggle-btn" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <div class="password-toggle">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm new password"
                            required
                            style="padding-left: 2.5rem;"
                        >
                        <button type="button" class="toggle-btn" onclick="toggleConfirmPasswordVisibility()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">Reset Password</button>
            </form>

            <div class="back-link">
                <a href="{{ route('password.request') }}">← Start Over</a>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
