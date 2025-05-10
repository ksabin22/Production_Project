<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title>Email Verification - Water Tank Management System</title>
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
                font-size: 1.4rem;
                color: white;
                margin: 0 0 0.5rem;
            }

            .nav-links {
                display: flex;
                align-items: center;
                gap: 1.2rem;
                flex-wrap: wrap;
                position: relative;
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
                margin-right: 1rem;
            }

            .user-dropdown {
                position: relative;
                color: white;
                font-weight: bold;
                cursor: pointer;
            }

            .user-dropdown-content {
                display: none;
                position: absolute;
                top: 2.2rem;
                right: 0;
                background: var(--card-bg);
                color: var(--text-color);
                border: 1px solid var(--secondary);
                border-radius: 6px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                min-width: 160px;
                z-index: 100;
            }

            .user-dropdown-content a {
                display: block;
                padding: 0.75rem 1rem;
                text-decoration: none;
                color: inherit;
            }

            .user-dropdown-content a:hover {
                background: var(--secondary);
            }

            .container {
                max-width: 500px;
                margin: 4rem auto;
                background: var(--card-bg);
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            }

            button {
                padding: 0.6rem 1.2rem;
                border: none;
                background: var(--primary);
                color: white;
                border-radius: 6px;
                cursor: pointer;
            }

            .link-button {
                background: transparent;
                border: none;
                color: var(--text-color);
                text-decoration: underline;
                font-size: 0.9rem;
                cursor: pointer;
            }

            .message {
                font-size: 0.95rem;
                margin-bottom: 1rem;
            }

            .success-message {
                color: var(--success-color);
                font-weight: 500;
            }

            @media (min-width: 768px) {
                nav {
                    flex-direction: row;
                    justify-content: space-between;
                    align-items: center;
                }

                nav h1 {
                    margin: 0;
                }
            }
        </style>
    </head>

    <body data-theme="light">
        <nav>
            <h1>Water Tank Management System</h1>
            <div class="nav-links">
                <div class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-adjust"></i></div>
                <div class="user-dropdown" id="userDropdown">
                    <span><?php echo e(Auth::user()->name); ?></span>
                    <div class="user-dropdown-content" id="dropdownMenu">
                        <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                        <a href="<?php echo e(route('logout')); ?>"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container">
            <p class="message">
                <?php echo e(__('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.')); ?>

            </p>

            <?php if(session('status') == 'verification-link-sent'): ?>
                <p class="message success-message">
                    <?php echo e(__('A new verification link has been sent to the email address you provided during registration.')); ?>

                </p>
            <?php endif; ?>

            <div
                style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <form method="POST" action="<?php echo e(route('verification.send')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit">Resend Verification Email</button>
                </form>

                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="link-button">Log Out</button>
                </form>
            </div>
        </div>

        <script>
            function toggleTheme() {
                const body = document.body;
                body.dataset.theme = body.dataset.theme === 'dark' ? 'light' : 'dark';
            }

            document.addEventListener('DOMContentLoaded', () => {
                const userDropdown = document.getElementById('userDropdown');
                const dropdownMenu = document.getElementById('dropdownMenu');

                userDropdown.addEventListener('click', (e) => {
                    e.stopPropagation(); // prevent body click from hiding it immediately
                    dropdownMenu.style.display =
                        dropdownMenu.style.display === 'block' ? 'none' : 'block';
                });

                document.addEventListener('click', (e) => {
                    if (!userDropdown.contains(e.target)) {
                        dropdownMenu.style.display = 'none';
                    }
                });
            });
        </script>
    </body>

</html>
<?php /**PATH /Users/sabinkhanal/Desktop/Production_Project/water-tank-app 3/resources/views/auth/verify-email.blade.php ENDPATH**/ ?>