<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>CopyCut Salon</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link rel="icon" href="{{ asset('icon.ico') }}">
        
        <!-- Styles -->
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(180deg, #c19ba8, #722340 80.77%, #5a1a31);
                color: #FFFBFA;
                height: 100vh;
                overflow: hidden;
            }

            .navbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                background-color: #FFFBFA;
                padding: 1rem 2rem;
            }

            .navbar a {
                text-decoration: none;
                color: #5A1A31;
                font-weight: 500;
                margin-left: 1rem;
                font-weight: bold;
                position: relative;
            }

            .navbar a:hover {
                text-decoration: underline;
            }

            img {
                max-width: 60%;
            }

            .hero {
                position: relative;
                height: calc(100vh - 80px);
                display: flex;
                align-items: flex-start;
                justify-content: flex-start;
                padding: 4rem 2rem;
                background: url('{{ asset('images/frame 1.png') }}') no-repeat left center fixed;
                background-size: cover;
            }

            @media (max-width: 768px) {
                .hero {
                    padding: 2rem 1rem;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                }
                .hero-content {
                    max-width: 100%;
                }
                .navbar {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                }
                .nav-links {
                    margin-top: 0.5rem;
                }
            }
        </style>
    </head>
    <body class="antialiased">
        <nav class="navbar">
            <div class="logo">
                <a href="finalproject.html"><img src="{{ asset('images/logo.png') }}" alt="CopyCut Logo"></a>
            </div>
            <div class="nav-links">
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            </div>
        </nav>

        <section class="hero"></section>
    </body>
</html>
