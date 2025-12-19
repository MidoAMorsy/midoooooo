<?php
/**
 * Admin Panel - Forgot Password
 */

require_once '../includes/config.php';

$lang = getCurrentLanguage();
$step = $_GET['step'] ?? 'request';
$token = $_GET['token'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'request' && isset($_POST['email'])) {
        $email = sanitize($_POST['email']);

        try {
            $stmt = db()->prepare("SELECT id, username, email FROM admins WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store token
                db()->prepare("UPDATE admins SET reset_token = ?, reset_token_expiry = ? WHERE id = ?")
                    ->execute([$token, $expiry, $admin['id']]);

                // Send email
                $resetUrl = SITE_URL . "/admin/forgot-password.php?step=reset&token=" . $token;
                $subject = $lang === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Password Reset Request';
                $body = $lang === 'ar'
                    ? "مرحباً {$admin['username']}،\n\nلقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بك.\n\nانقر على الرابط التالي:\n{$resetUrl}\n\nهذا الرابط صالح لمدة ساعة واحدة."
                    : "Hello {$admin['username']},\n\nWe received a request to reset your password.\n\nClick the link below:\n{$resetUrl}\n\nThis link is valid for 1 hour.";

                sendEmail($admin['email'], $subject, nl2br($body));

                logActivity('password_reset_request', "Password reset requested for: {$admin['email']}");
            }

            // Always show success to prevent email enumeration
            setFlash('success', $lang === 'ar'
                ? 'إذا كان البريد الإلكتروني مسجلاً، ستتلقى رابط إعادة التعيين.'
                : 'If the email is registered, you will receive a reset link.');

        } catch (PDOException $e) {
            setFlash('error', 'An error occurred');
        }

        redirect(SITE_URL . '/admin/forgot-password.php');
    }

    if ($step === 'reset' && isset($_POST['password']) && $token) {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (strlen($password) < 8) {
            setFlash('error', $lang === 'ar' ? 'كلمة المرور يجب أن تكون 8 أحرف على الأقل' : 'Password must be at least 8 characters');
            redirect(SITE_URL . '/admin/forgot-password.php?step=reset&token=' . $token);
        }

        if ($password !== $confirmPassword) {
            setFlash('error', $lang === 'ar' ? 'كلمات المرور غير متطابقة' : 'Passwords do not match');
            redirect(SITE_URL . '/admin/forgot-password.php?step=reset&token=' . $token);
        }

        try {
            $stmt = db()->prepare("SELECT id FROM admins WHERE reset_token = ? AND reset_token_expiry > NOW()");
            $stmt->execute([$token]);
            $admin = $stmt->fetch();

            if ($admin) {
                $hashedPassword = hashPassword($password);
                db()->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?")
                    ->execute([$hashedPassword, $admin['id']]);

                logActivity('password_reset_complete', "Password reset completed for admin ID: {$admin['id']}");

                setFlash('success', $lang === 'ar' ? 'تم تغيير كلمة المرور بنجاح' : 'Password changed successfully');
                redirect(SITE_URL . '/admin/');
            } else {
                setFlash('error', $lang === 'ar' ? 'الرابط غير صالح أو منتهي الصلاحية' : 'Invalid or expired token');
                redirect(SITE_URL . '/admin/forgot-password.php');
            }

        } catch (PDOException $e) {
            setFlash('error', 'An error occurred');
            redirect(SITE_URL . '/admin/forgot-password.php');
        }
    }
}

// Validate token for reset step
$validToken = false;
if ($step === 'reset' && $token) {
    try {
        $stmt = db()->prepare("SELECT id FROM admins WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        $validToken = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        $validToken = false;
    }

    if (!$validToken) {
        setFlash('error', $lang === 'ar' ? 'الرابط غير صالح أو منتهي الصلاحية' : 'Invalid or expired token');
        redirect(SITE_URL . '/admin/forgot-password.php');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang === 'ar' ? 'استعادة كلمة المرور' : 'Forgot Password'; ?> - <?php echo SITE_NAME; ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0066cc;
            --error: #dc3545;
            --success: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family:
                <?php echo $lang === 'ar' ? '"Cairo"' : '"Inter"'; ?>
                , sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 3rem;
            color: var(--primary);
        }

        h1 {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #0052a3;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="logo">
            <i class="fas fa-lock"></i>
        </div>

        <?php echo displayFlashMessages(); ?>

        <?php if ($step === 'request'): ?>
            <h1><?php echo $lang === 'ar' ? 'نسيت كلمة المرور؟' : 'Forgot Password?'; ?></h1>
            <p class="subtitle">
                <?php echo $lang === 'ar' ? 'أدخل بريدك الإلكتروني لإعادة التعيين' : 'Enter your email to reset your password'; ?>
            </p>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><?php echo __('email'); ?></label>
                    <input type="email" name="email" class="form-control" required autofocus
                        placeholder="admin@example.com">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i>
                    <?php echo $lang === 'ar' ? 'إرسال رابط التعيين' : 'Send Reset Link'; ?>
                </button>
            </form>

        <?php elseif ($step === 'reset' && $validToken): ?>
            <h1><?php echo $lang === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Password'; ?></h1>
            <p class="subtitle"><?php echo $lang === 'ar' ? 'أدخل كلمة المرور الجديدة' : 'Enter your new password'; ?></p>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'كلمة المرور الجديدة' : 'New Password'; ?></label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="form-group">
                    <label
                        class="form-label"><?php echo $lang === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password'; ?></label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-key"></i>
                    <?php echo $lang === 'ar' ? 'تغيير كلمة المرور' : 'Change Password'; ?>
                </button>
            </form>
        <?php endif; ?>

        <a href="<?php echo SITE_URL; ?>/admin/" class="back-link">
            <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>"></i>
            <?php echo $lang === 'ar' ? 'العودة لتسجيل الدخول' : 'Back to Login'; ?>
        </a>
    </div>
</body>

</html>