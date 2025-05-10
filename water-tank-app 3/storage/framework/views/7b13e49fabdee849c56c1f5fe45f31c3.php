<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Register - Water Tank Management</title>
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
                margin: 0.3rem 0;
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

            label {
                font-weight: 600;
            }

            .password-toggle {
                position: relative;
                width: 100%;
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

            input[type="text"],
            input[type="email"],
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

            .link {
                font-size: 0.9rem;
                color: var(--text-color);
                text-decoration: underline;
            }

            .flex {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
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

            @media (max-width: 600px) {
                .container {
                    margin: 2rem 1rem;
                }
            }
        </style>
    </head>

    <body data-theme="light">
        <nav>
            <h1>Water Tank Management System</h1>
            <div class="nav-links">
                <a href="<?php echo e(route('login')); ?>">Login</a>
                <a href="<?php echo e(route('register')); ?>">Register</a>
                <span class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></span>
            </div>
        </nav>

        <div class="container">
            <h2>Register</h2>
            <form method="POST" action="<?php echo e(route('register')); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required autocomplete="name" />
                    <div id="nameError" class="error-message"></div>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="reg_email" name="email" required autocomplete="username" />
                    <div id="regEmailError" class="error-message"></div>
                </div>

                <div>
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="reg_password" name="password" required autocomplete="new-password" />
                        <i class="fas fa-eye" onclick="togglePassword('reg_password', this)"></i>
                    </div>
                    <div id="regPasswordError" class="error-message"></div>
                </div>

                <div>
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            autocomplete="new-password" />
                        <i class="fas fa-eye" onclick="togglePassword('password_confirmation', this)"></i>
                    </div>
                </div>

                <div class="flex mt-4">
                    <a class="link" href="<?php echo e(route('login')); ?>">Already registered?</a>
                    <button type="submit">Register</button>
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
    const nameField = document.getElementById('name');
    const emailField = document.getElementById('reg_email');
    const passwordField = document.getElementById('reg_password');
    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('regEmailError');
    const passwordError = document.getElementById('regPasswordError');

    nameField.addEventListener('input', () => {
        const valid = /^[A-Za-z ]+$/.test(nameField.value);
        nameField.style.borderColor = valid ? 'var(--success-color)' : 'var(--error-color)';
        nameError.textContent = valid ? '' : 'Only alphabetic characters and spaces allowed';
    });

    emailField.addEventListener('input', () => {
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value);
        emailField.style.borderColor = valid ? 'var(--success-color)' : 'var(--error-color)';
        emailError.textContent = valid ? '' : 'Enter a valid email address';
    });

    passwordField.addEventListener('input', () => {
        const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(passwordField.value);
        passwordField.style.borderColor = strong ? 'var(--success-color)' : 'var(--error-color)';
        passwordError.textContent = strong
            ? ''
            : 'Must include uppercase, lowercase, number, special character, 8+ characters';
    });
});

        </script>
    </body>

</html>
<?php /**PATH /Users/sabinkhanal/Desktop/Production_Project/water-tank-app 3/resources/views/auth/register.blade.php ENDPATH**/ ?>