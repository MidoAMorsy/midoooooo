<?php
/**
 * Ahmed Ashraf Portfolio - Authentication Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get current admin user
 */
function getCurrentAdmin()
{
    if (!isLoggedIn()) {
        return null;
    }

    static $admin = null;

    if ($admin === null) {
        try {
            // Debugging query failure
            if (isset($_GET['debug'])) {
                echo "Debug: Fetching admin with ID: " . $_SESSION['admin_id'];
            }

            $stmt = db()->prepare("SELECT id, username, email, full_name, avatar, role, is_active FROM admins WHERE id = ? AND is_active = 1");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (isset($_GET['debug']) && !$admin) {
                die("<br>Debug: Admin not found in DB! ID: {$_SESSION['admin_id']} (Check if is_active is 1)");
            }
        } catch (PDOException $e) {
            if (isset($_GET['debug'])) {
                die("Debug: DB Error: " . $e->getMessage());
            }
            return null;
        }
    }

    return $admin;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        // Debugging session loss
        if (isset($_GET['debug'])) {
            die('Debug: Not logged in. Session: ' . print_r($_SESSION, true));
        }
        setFlash('error', __('login_required'));
        redirect(SITE_URL . '/admin/');
    }

    // Check if user is still valid
    $admin = getCurrentAdmin();
    if (!$admin) {
        logout();
        redirect(SITE_URL . '/admin/');
    }
}

/**
 * Require specific role
 */
function requireRole($roles)
{
    requireLogin();

    $admin = getCurrentAdmin();
    $roles = is_array($roles) ? $roles : [$roles];

    if (!in_array($admin['role'], $roles)) {
        setFlash('error', __('access_denied'));
        redirect(SITE_URL . '/admin/dashboard.php');
    }
}

/**
 * Check if current admin has role
 */
function hasRole($role)
{
    $admin = getCurrentAdmin();
    if (!$admin)
        return false;

    $roles = is_array($role) ? $role : [$role];
    return in_array($admin['role'], $roles);
}

/**
 * Authenticate user
 */
function authenticate($username, $password)
{
    try {
        $stmt = db()->prepare("SELECT id, username, email, password, full_name, role, is_active FROM admins WHERE (username = ? OR email = ?)");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => __('invalid_credentials')];
        }

        if ($user['is_active'] != 1) {
            return ['success' => false, 'error' => __('account_disabled')];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => __('invalid_credentials')];
        }

        // Update last login
        $stmt = db()->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Set session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];

        // Regenerate session ID for security
        // session_regenerate_id(true);

        // Log activity
        logActivity('login', 'User logged in successfully', 'admin', $user['id']);

        return ['success' => true, 'user' => $user];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Logout user
 */
function logout()
{
    // Log activity before destroying session
    if (isLoggedIn()) {
        logActivity('logout', 'User logged out', 'admin', $_SESSION['admin_id']);
    }

    // Clear session data
    $_SESSION = [];

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    // Start new session
    session_start();
}

/**
 * Hash password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
}

/**
 * Change password
 */
function changePassword($adminId, $currentPassword, $newPassword)
{
    try {
        $stmt = db()->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();

        if (!$admin) {
            return ['success' => false, 'error' => __('user_not_found')];
        }

        if (!password_verify($currentPassword, $admin['password'])) {
            return ['success' => false, 'error' => __('current_password_wrong')];
        }

        $hashedPassword = hashPassword($newPassword);
        $stmt = db()->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $adminId]);

        logActivity('password_change', 'Password changed', 'admin', $adminId);

        return ['success' => true];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Create admin user
 */
function createAdmin($data)
{
    try {
        // Check if username exists
        $stmt = db()->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$data['username']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => __('username_exists')];
        }

        // Check if email exists
        $stmt = db()->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => __('email_exists')];
        }

        $hashedPassword = hashPassword($data['password']);

        $stmt = db()->prepare("INSERT INTO admins (username, email, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'] ?? '',
            $data['role'] ?? 'admin',
            $data['is_active'] ?? 1
        ]);

        $adminId = db()->lastInsertId();
        logActivity('admin_create', 'New admin user created: ' . $data['username'], 'admin', $adminId);

        return ['success' => true, 'id' => $adminId];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Update admin user
 */
function updateAdmin($adminId, $data)
{
    try {
        $updates = [];
        $params = [];

        if (isset($data['email'])) {
            // Check if email exists for other user
            $stmt = db()->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $adminId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => __('email_exists')];
            }
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }

        if (isset($data['full_name'])) {
            $updates[] = "full_name = ?";
            $params[] = $data['full_name'];
        }

        if (isset($data['avatar'])) {
            $updates[] = "avatar = ?";
            $params[] = $data['avatar'];
        }

        if (isset($data['role'])) {
            $updates[] = "role = ?";
            $params[] = $data['role'];
        }

        if (isset($data['is_active'])) {
            $updates[] = "is_active = ?";
            $params[] = $data['is_active'];
        }

        if (empty($updates)) {
            return ['success' => true];
        }

        $params[] = $adminId;
        $sql = "UPDATE admins SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        logActivity('admin_update', 'Admin user updated', 'admin', $adminId);

        return ['success' => true];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Delete admin user
 */
function deleteAdmin($adminId)
{
    // Prevent deleting yourself
    if ($adminId == $_SESSION['admin_id']) {
        return ['success' => false, 'error' => __('cannot_delete_self')];
    }

    try {
        $stmt = db()->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);

        logActivity('admin_delete', 'Admin user deleted', 'admin', $adminId);

        return ['success' => true];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Generate password reset token
 */
function generateResetToken($email)
{
    try {
        $stmt = db()->prepare("SELECT id FROM admins WHERE email = ? AND status = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin) {
            return ['success' => false, 'error' => __('email_not_found')];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token (you might want a separate table for this)
        updateSetting('reset_token_' . $admin['id'], json_encode([
            'token' => $token,
            'expires' => $expires
        ]));

        // Send email with reset link
        $resetUrl = SITE_URL . '/admin/reset-password.php?token=' . $token . '&id=' . $admin['id'];
        $subject = SITE_NAME . ' - Password Reset';
        $body = "Click the following link to reset your password:\n\n" . $resetUrl . "\n\nThis link expires in 1 hour.";

        sendEmail($email, $subject, $body, false);

        return ['success' => true];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Verify reset token
 */
function verifyResetToken($adminId, $token)
{
    $stored = getSetting('reset_token_' . $adminId);
    if (!$stored) {
        return false;
    }

    $data = json_decode($stored, true);
    if (!$data || $data['token'] !== $token) {
        return false;
    }

    if (strtotime($data['expires']) < time()) {
        return false;
    }

    return true;
}

/**
 * Reset password with token
 */
function resetPasswordWithToken($adminId, $token, $newPassword)
{
    if (!verifyResetToken($adminId, $token)) {
        return ['success' => false, 'error' => __('invalid_token')];
    }

    try {
        $hashedPassword = hashPassword($newPassword);
        $stmt = db()->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $adminId]);

        // Remove token
        $stmt = db()->prepare("DELETE FROM site_settings WHERE setting_key = ?");
        $stmt->execute(['reset_token_' . $adminId]);

        logActivity('password_reset', 'Password reset via token', 'admin', $adminId);

        return ['success' => true];

    } catch (PDOException $e) {
        return ['success' => false, 'error' => __('system_error')];
    }
}

/**
 * Check login attempts (brute force protection)
 */
function checkLoginAttempts($ip)
{
    $key = 'login_attempts_' . md5($ip);
    $data = getSetting($key);

    if (!$data) {
        return true;
    }

    $attempts = json_decode($data, true);

    // Reset if window expired (15 minutes)
    if (time() - $attempts['first_attempt'] > 900) {
        return true;
    }

    // Block if more than 5 attempts
    if ($attempts['count'] >= 5) {
        return false;
    }

    return true;
}

/**
 * Record login attempt
 */
function recordLoginAttempt($ip, $success)
{
    $key = 'login_attempts_' . md5($ip);

    if ($success) {
        // Clear attempts on success
        $stmt = db()->prepare("DELETE FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return;
    }

    $data = getSetting($key);

    if (!$data) {
        $attempts = [
            'count' => 1,
            'first_attempt' => time()
        ];
    } else {
        $attempts = json_decode($data, true);

        // Reset if window expired
        if (time() - $attempts['first_attempt'] > 900) {
            $attempts = [
                'count' => 1,
                'first_attempt' => time()
            ];
        } else {
            $attempts['count']++;
        }
    }

    updateSetting($key, json_encode($attempts));
}
