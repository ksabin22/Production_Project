<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Confirm Password - Water Tank Management System</title>
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

            .password-toggle {
                position: relative;
            }

            .password-toggle input {
                width: 100%;
                padding-right: 2.5rem;
                box-sizing: border-box;
            }

            .password-toggle i {
                position: absolute;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                cursor: pointer;
                color: #888;
            }

            input[type="password"] {
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
            }

            .message {
                font-size: 0.9rem;
                margin-bottom: 1rem;
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
            <p class="message">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                @csrf
                <div>
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" required
                            autocomplete="current-password" />
                        <i class="fas fa-eye" onclick="togglePassword('password', this)"></i>
                    </div>
                    <div id="passwordError" class="error-message"></div>
                </div>

                <div style="margin-top: 1.5rem; text-align: right;">
                    <button type="submit">Confirm</button>
                </div>
            </form>
        </div>

        <script>
            function toggleTheme() {
                document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            }

            function togglePassword(id, icon) {
                const field = document.getElementById(id);
                if (field.type === 'password') {
                    field.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    field.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const passwordField = document.getElementById('password');
                const passwordError = document.getElementById('passwordError');

                passwordField.addEventListener('input', () => {
                    const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(passwordField
                        .value);
                    passwordField.style.borderColor = strong ? 'var(--success-color)' : 'var(--error-color)';
                    passwordError.textContent = strong ? '' :
                        'Must include upper, lower, number, symbol, and be 8+ characters';
                });
            });
        </script>
    </body>

</html>
