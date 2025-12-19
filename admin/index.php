<?php
/**
 * Admin Panel - Login Page
 */

require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check brute force
    if (!checkLoginAttempts(getClientIP())) {
        setFlash('error', 'Too many login attempts. Please try again later.');
    } else if (verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $result = authenticate($username, $password);

        recordLoginAttempt(getClientIP(), $result['success']);

        if ($result['success']) {
            setFlash('success', 'Welcome back!');
            redirect(SITE_URL . '/admin/dashboard.php');
        } else {
            setFlash('error', $result['error']);
        }
    } else {
        setFlash('error', __('session_expired'));
    }
}

$lang = getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo getDirection(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login'); ?> | <?php echo getSetting('site_name_' . $lang, SITE_NAME); ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary: #0066cc;
            --primary-dark: #004999;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --success: #22c55e;
            --error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family:
                <?php echo $lang === 'ar' ? "'Cairo'" : "'Inter'"; ?>
                , sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, #00bcd4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: #fff;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            height: 60px;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-800);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>: 3rem;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            <?php echo $lang === 'ar' ? 'left' : 'right'; ?>
            : 1rem;
            transform: translateY(-50%);
            color: var(--gray-400);
            cursor: pointer;
        }

        .checkbox-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .checkbox-group a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, var(--primary) 0%, #00bcd4 100%);
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 102, 204, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="<?php echo ASSETS_URL; ?>/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                <h1><?php echo __('admin_panel'); ?></h1>
                <p><?php echo __('login'); ?></p>
            </div>

            <?php echo displayFlashMessages(); ?>

            <form method="POST" action="">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label class="form-label"><?php echo __('username'); ?></label>
                    <input type="text" name="username" class="form-control" required autofocus
                        placeholder="<?php echo __('username'); ?>" value="<?php echo e($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo __('password'); ?></label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required
                            placeholder="<?php echo __('password'); ?>">
                        <span class="input-icon" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggle-icon"></i>
                        </span>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="remember">
                        <?php echo __('remember_me'); ?>
                    </label>
                    <a href="forgot-password.php"><?php echo __('forgot_password'); ?></a>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <?php echo __('login'); ?>
                </button>
            </form>
        </div>

        <a href="<?php echo SITE_URL; ?>" class="back-link">
            <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>"></i>
            <?php echo $lang === 'ar' ? 'العودة للموقع' : 'Back to website'; ?>
        </a>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggle-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>