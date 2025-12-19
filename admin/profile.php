<?php
/**
 * Admin Panel - Profile Page
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$admin = getCurrentAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    try {
        // Update basic info
        $stmt = db()->prepare("UPDATE admins SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$fullName, $email, $admin['id']]);

        // Update password if provided
        if (!empty($newPassword)) {
            // Verify current password
            $stmt = db()->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$admin['id']]);
            $user = $stmt->fetch();

            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                db()->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hashedPassword, $admin['id']]);
                setFlash('success', $lang === 'ar' ? 'تم تحديث كلمة المرور' : 'Password updated');
            } else {
                setFlash('error', $lang === 'ar' ? 'كلمة المرور الحالية غير صحيحة' : 'Current password is incorrect');
            }
        }

        // Handle avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $upload = uploadFile($_FILES['avatar'], 'avatars');
            if ($upload['success']) {
                db()->prepare("UPDATE admins SET avatar = ? WHERE id = ?")->execute([$upload['filename'], $admin['id']]);
            }
        }

        if (!getFlash('error')) {
            setFlash('success', $lang === 'ar' ? 'تم تحديث الملف الشخصي' : 'Profile updated');
        }

        redirect(SITE_URL . '/admin/profile.php');

    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
}

$pageTitle = __('profile');
include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="content-header">
        <h1><?php echo __('profile'); ?></h1>
        <p><?php echo $lang === 'ar' ? 'تعديل بيانات حسابك' : 'Edit your account information'; ?></p>
    </div>

    <div style="max-width:700px;">
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>

            <!-- Avatar -->
            <div class="dashboard-card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <h2><?php echo $lang === 'ar' ? 'الصورة الشخصية' : 'Profile Photo'; ?></h2>
                </div>
                <div class="card-body" style="display:flex;align-items:center;gap:2rem;">
                    <img src="<?php echo $admin['avatar'] ? UPLOADS_URL . '/avatars/' . e($admin['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($admin['full_name'] ?: $admin['username']) . '&background=0066cc&color=fff&size=120'; ?>"
                        style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                    <div>
                        <input type="file" name="avatar" accept="image/*" class="form-control" style="max-width:250px;">
                        <small class="text-muted"><?php echo $lang === 'ar' ? 'الحد الأقصى 2MB' : 'Max 2MB'; ?></small>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="dashboard-card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <h2><?php echo $lang === 'ar' ? 'المعلومات الأساسية' : 'Basic Information'; ?></h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label"><?php echo __('username'); ?></label>
                        <input type="text" class="form-control" value="<?php echo e($admin['username']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'الاسم الكامل' : 'Full Name'; ?></label>
                        <input type="text" name="full_name" class="form-control"
                            value="<?php echo e($admin['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo __('email'); ?></label>
                        <input type="email" name="email" class="form-control" value="<?php echo e($admin['email']); ?>">
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="dashboard-card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <h2><?php echo $lang === 'ar' ? 'تغيير كلمة المرور' : 'Change Password'; ?></h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label
                            class="form-label"><?php echo $lang === 'ar' ? 'كلمة المرور الحالية' : 'Current Password'; ?></label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label
                            class="form-label"><?php echo $lang === 'ar' ? 'كلمة المرور الجديدة' : 'New Password'; ?></label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <small
                        class="text-muted"><?php echo $lang === 'ar' ? 'اتركها فارغة إذا لم ترد التغيير' : 'Leave blank to keep current password'; ?></small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i>
                <?php echo $lang === 'ar' ? 'حفظ التغييرات' : 'Save Changes'; ?>
            </button>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>