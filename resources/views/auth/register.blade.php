<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon - Register</title>
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
            <h2>Join Us!</h2>
            <p>Create Your Account</p>
            
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <!-- Name -->
                <div class="input-container">
                    <i class="ri-user-line"></i>
                    <input 
                        id="name" 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}"
                        placeholder="Full Name" 
                        required 
                        autofocus 
                        autocomplete="name"
                    />
                </div>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                
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
                        autocomplete="new-password"
                    />
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                
                <!-- Confirm Password -->
                <div class="input-container">
                    <i class="ri-lock-2-line"></i>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        placeholder="Confirm Password" 
                        required 
                        autocomplete="new-password"
                    />
                </div>
                @error('password_confirmation')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                
                <button type="submit" class="btn">REGISTER</button>
            </form>
            
            <div class="text-center">
                <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>