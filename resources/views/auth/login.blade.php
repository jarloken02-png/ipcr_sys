<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University of Rizal System Binangonan</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth_login.css', 'resources/js/auth_login.js'])
</head>
<body>
    <div class="auth-card">
        <!-- Left: Image -->
        <div class="auth-card__image">
            <img src="{{ asset('images/login_img.png') }}" alt="Login illustration">
        </div>

        <!-- Right: Form -->
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

                <h1>Welcome</h1>

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

                <form method="POST" action="{{ route('login.post') }}" data-turbo="false">
                    @csrf

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                value="{{ old('username') }}"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-toggle">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="toggle-btn" onclick="togglePasswordVisibility()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>