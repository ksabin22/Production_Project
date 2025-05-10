<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Forgot Password - Water Tank Management System</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --bg-color: #f4f4f4;
                --text-color: #333;
                --card-bg: #fff;
                --primary: #4CAF50;
                --secondary: #e0e0e0;
                --error-color: red;
                --success-color: #4CAF50;
            }

            [data-theme="dark"] {
                --bg-color: #121212;
                --text-color: #e0e0e0;
                --card-bg: #1e1e1e;
                --secondary: #2c2c2c;
            }

            body {
                margin: 0;
                font-family: 'Segoe UI', sans-serif;
                background: var(--bg-color);
                color: var(--text-color);
            }

            nav {
                background: var(--primary);
                padding: 1rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            nav h1 {
                font-size: 1.2rem;
                color: white;
                margin-bottom: 0.5rem;
            }

            .nav-links {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .nav-links a {
                color: white;
                text-decoration: none;
                font-weight: bold;
            }

            .theme-toggle {
                cursor: pointer;
                color: white;
                font-size: 1.4rem;
            }

            .container {
                max-width: 420px;
                margin: 4rem auto;
                background: var(--card-bg);
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            }

            input[type="email"] {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #ccc;
                border-radius: 6px;
                margin-top: 0.25rem;
                margin-bottom: 0.2rem;
                transition: border-color 0.3s;
                box-sizing: border-box;
            }

            label {
                font-weight: 600;
            }

            .error-message {
                color: var(--error-color);
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            button {
                padding: 0.5rem 1rem;
                border: none;
                background: var(--primary);
                color: white;
                border-radius: 6px;
                cursor: pointer;
                margin-top: 1rem;
            }

            @media (min-width: 768px) {
                nav {
                    flex-direction: row;
                    justify-content: space-between;
                }

                nav h1 {
                    margin: 0;
                    font-size: 1.4rem;
                }

                .nav-links {
                    justify-content: flex-end;
                }
            }
        </style>
    </head>

    <body data-theme="light">
        <nav>
            <h1>Water Tank Management System</h1>
            <div class="nav-links">
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
                <span class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></span>
            </div>
        </nav>

        <div class="container">
            <p style="font-size: 0.9rem; margin-bottom: 1rem;">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </p>

            @if (session('status'))
                <p style="color: green; font-size: 0.9rem;">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus />
                    <div id="emailError" class="error-message"></div>
                </div>
                <div style="margin-top: 1rem; text-align: right;">
                    <button type="submit">Email Password Reset Link</button>
                </div>
            </form>
        </div>

        <script>
            function toggleTheme() {
                document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            }

            document.addEventListener('DOMContentLoaded', () => {
                const emailField = document.getElementById('email');
                const emailError = document.getElementById('emailError');

                emailField.addEventListener('input', () => {
                    const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value);
                    emailField.style.borderColor = valid ? 'var(--success-color)' : 'var(--error-color)';
                    emailError.textContent = valid ? '' : 'Please enter a valid email address';
                });
            });
        </script>
    </body>

</html>
