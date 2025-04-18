<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Profile Settings</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --bg-color: #f4f4f4;
                --text-color: #333;
                --card-bg: #fff;
                --primary: #4CAF50;
                --secondary: #e0e0e0;
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

            header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                background: var(--primary);
                color: white;
            }

            .theme-toggle {
                cursor: pointer;
                font-size: 1.2rem;
                margin-right: 1rem;
            }

            .user-dropdown {
                position: relative;
                display: inline-block;
            }

            .user-dropdown-content {
                display: none;
                position: absolute;
                right: 0;
                background: var(--card-bg);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 1;
            }

            .user-dropdown:hover .user-dropdown-content {
                display: block;
            }

            .user-dropdown-content a {
                display: block;
                padding: 0.5rem 1rem;
                color: var(--text-color);
                text-decoration: none;
            }

            .container {
                padding: 1rem;
                display: grid;
                gap: 2rem;
                max-width: 800px;
                margin: auto;
            }

            .card {
                background: var(--card-bg);
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            form {
                display: grid;
                gap: 1rem;
            }

            label {
                font-weight: 600;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                width: 100%;
                padding: 0.5rem;
                border-radius: 6px;
                border: 1px solid #ccc;
                transition: border-color 0.3s ease;
                margin-top: 10px;
            }

            input:valid {
                border-color: #4CAF50;
            }

            input:invalid:focus {
                border-color: red;
            }

            .password-toggle {
                position: relative;
            }

            .password-toggle i {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-5%);
                cursor: pointer;
                color: #888;
            }

            .strength-bar {
                margin-top: 20px;
                height: 6px;
                background: #ccc;
                border-radius: 3px;
                overflow: hidden;
            }

            .strength-fill {
                height: 100%;
                width: 0%;
                transition: width 0.3s ease;
            }

            .fill-weak {
                background: red;
                width: 33%;
            }

            .fill-medium {
                background: orange;
                width: 66%;
            }

            .fill-strong {
                background: green;
                width: 100%;
            }

            .tooltip {
                font-size: 0.8rem;
                color: #888;
                margin-top: 0.2rem;
            }

            .message {
                font-size: 0.9rem;
                color: green;
                animation: fadeOut 3s forwards;
            }

            @keyframes fadeOut {
                0% {
                    opacity: 1;
                }

                80% {
                    opacity: 1;
                }

                100% {
                    opacity: 0;
                    display: none;
                }
            }

            button {
                padding: 0.6rem 1.2rem;
                background: var(--primary);
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
            }

            .danger-button {
                background: #dc2626;
            }

            .secondary-button {
                background: var(--secondary);
                color: black;
            }

            .flex {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
            }

            @media screen and (max-width: 768px) {
                .container {
                    padding: 0.5rem;
                }

                .flex {
                    flex-direction: column;
                }
            }
        </style>
    </head>

    <body data-theme="light">
        <header>
            <div>Profile Settings</div>
            <div style="display: flex; align-items: center;">
                <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></div>
                <div class="user-dropdown">
                    <span id="user-name">{{ Auth::user()->name }}</span>
                    <div class="user-dropdown-content">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf</form>
                    </div>
                </div>
            </div>
        </header>
        <div class="container">
            <div class="card">
                <h2>Update Profile</h2>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div>
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" pattern="[A-Za-z ]+"
                            title="Only letters and spaces" value="{{ old('name', $user->name) }}" required />
                        <div class="tooltip">Only alphabetic characters allowed</div>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                            required />
                        <div class="tooltip">Format: user@example.com</div>
                    </div>
                    @if (session('status') === 'profile-updated')
                        <p class="message">Profile updated successfully.</p>
                    @endif
                    <button type="submit">Save</button>
                </form>
            </div>
            <div class="card">
                <h2>Update Password</h2>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')
                    <div>
                        <label for="current_password">Current Password</label>
                        <div class="password-toggle">
                            <input type="password" name="current_password" id="current_password" required />
                            <i class="fas fa-eye" onclick="togglePassword('current_password', this)"></i>
                        </div>
                    </div>
                    <div>
                        <label for="password">New Password</label>
                        <div class="password-toggle">
                            <input type="password" name="password" id="password" required
                                oninput="checkStrength(this.value)" />
                            <i class="fas fa-eye" onclick="togglePassword('password', this)"></i>
                        </div>
                        <div class="strength-bar">
                            <div id="strengthBar" class="strength-fill"></div>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="password-toggle">
                            <input type="password" name="password_confirmation" id="password_confirmation" required />
                            <i class="fas fa-eye" onclick="togglePassword('password_confirmation', this)"></i>
                        </div>
                    </div>
                    @if (session('status') === 'password-updated')
                        <p class="message">Password updated successfully.</p>
                    @endif
                    <button type="submit">Save</button>
                </form>
            </div>
            <div class="card">
                <h2>Delete Account</h2>
                <p>Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                <form method="POST" action="{{ route('profile.destroy') }}"
                    onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    @csrf
                    @method('delete')
                    <div>
                        <label for="password">Password</label>
                        <div class="password-toggle">
                            <input type="password" name="password" id="delete_password" placeholder="Confirm Password"
                                required />
                            <i class="fas fa-eye" onclick="togglePassword('delete_password', this)"></i>
                        </div>
                    </div>
                    <div class="flex">
                        <button type="button" class="secondary-button"
                            onclick="window.location.reload()">Cancel</button>
                        <button type="submit" class="danger-button">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            function toggleTheme() {
                document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            }

            function togglePassword(id, icon) {
                const field = document.getElementById(id);
                field.type = field.type === 'password' ? 'text' : 'password';
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }

            function checkStrength(password) {
                const bar = document.getElementById('strengthBar');
                const input = document.getElementById('password');
                let strength = 0;
                const regex = [/[a-z]/, /[A-Z]/, /[0-9]/, /[^A-Za-z0-9]/];
                regex.forEach((r) => strength += r.test(password) ? 1 : 0);
                if (password.length >= 8) strength++;

                bar.className = 'strength-fill';
                input.setCustomValidity('');

                if (strength <= 2) {
                    bar.classList.add('fill-weak');
                    input.setCustomValidity('Password must include upper, lower, number, special character and 8+ characters');
                } else if (strength === 3 || strength === 4) {
                    bar.classList.add('fill-medium');
                } else if (strength >= 5) {
                    bar.classList.add('fill-strong');
                }
            }

            // Real-time validation feedback for name and email
            document.addEventListener('DOMContentLoaded', () => {
                const nameField = document.getElementById('name');
                const emailField = document.getElementById('email');
                const passwordField = document.getElementById('password');

                const validateField = (field, regex, message) => {
                    field.addEventListener('input', () => {
                        if (!regex.test(field.value)) {
                            field.setCustomValidity(message);
                            field.reportValidity();
                            field.style.borderColor = 'red';
                        } else {
                            field.setCustomValidity('');
                            field.style.borderColor = '#4CAF50';
                        }
                    });
                };

                validateField(nameField, /^[A-Za-z ]+$/, 'Only alphabetic characters allowed');
                validateField(emailField, /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 'Enter a valid email address');
                validateField(passwordField, /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/,
                    'Password must contain at least one lowercase letter, one uppercase letter, one number, one special character, and be 8 characters or longer'
                    );
            });
        </script>
    </body>

</html>
