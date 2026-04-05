<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University of Rizal System Binangonan</title>
    <link rel="icon" type="image/jpeg" href="{{ \App\Support\MediaAsset::publicImageUrl('urs_logo.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth_login.css', 'resources/js/auth_login.js'])
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

            <h1>Forgot Password</h1>
            <p class="subtitle">Enter your email address and we'll send you a verification code.</p>

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

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="name@example.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <button type="submit" class="login-btn">Send Verification Code</button>
            </form>

            <div class="back-link">
                <a href="{{ route('login') }}">← Back to Login</a>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
