<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="{{ url('/') }}"><img src="{{ asset('images/Logo.png') }}" alt="CopyCut Logo"></a>
        </div>
        <div class="nav-links">
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        </div>
    </nav>
    
    <div class="login-container">
        <section class="hero"></section>
        <div class="form-side">
            <h2>Hello Again!</h2>
            <p>Welcome Back</p>
            
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email Address -->
                <div class="input-container">
                    <i class="ri-mail-line"></i>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        placeholder="Email Address" 
                        required 
                        autofocus 
                        autocomplete="username"
                    />
                </div>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                
                <!-- Password -->
                <div class="input-container">
                    <i class="ri-lock-line"></i>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        placeholder="Password" 
                        required 
                        autocomplete="current-password"
                    />
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                
                <!-- Remember Me -->
                <div class="remember-me">
                    <label for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        {{ __('Remember me') }}
                    </label>
                </div>
                
                <button type="submit" class="btn">LOGIN</button>
            </form>
            
            <div class="text-center">
                @if (Route::has('password.request'))
                    <p><a href="{{ route('password.request') }}">Forgot Password?</a></p>
                @endif
                <p>Don't have an account? <a href="{{ route('register') }}">Signup now</a></p>
            </div>
        </div>
    </div>
</body>
</html>