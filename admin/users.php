<?php
/**
 * Admin Panel - User Management
 */

require_once '../includes/config.php';
requireLogin();
requireRole('super_admin');

$lang = getCurrentLanguage();
$action = $_GET['action'] ?? 'list';
$pageTitle = __('users');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    if (isset($_POST['save'])) {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
        $data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'admin'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Password handling
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        try {
            if ($id) {
                $fields = [];
                $values = [];
                foreach ($data as $k => $v) {
                    $fields[] = "$k = ?";
                    $values[] = $v;
                }
                $values[] = $id;
                db()->prepare("UPDATE admins SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?")->execute($values);
                setFlash('success', 'User updated');
            } else {
                if (empty($_POST['password'])) {
                    setFlash('error', 'Password is required for new users');
                    redirect(SITE_URL . '/admin/users.php?action=add');
                }
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($data), '?');
                db()->prepare("INSERT INTO admins (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")")->execute(array_values($data));
                setFlash('success', 'User created');
            }
            redirect(SITE_URL . '/admin/users.php');
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }

    if (isset($_POST['delete'])) {
        $deleteId = (int) $_POST['id'];
        if ($deleteId === getCurrentAdmin()['id']) {
            setFlash('error', 'Cannot delete yourself');
        } else {
            db()->prepare("DELETE FROM admins WHERE id = ?")->execute([$deleteId]);
            setFlash('success', 'User deleted');
        }
        redirect(SITE_URL . '/admin/users.php');
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h1><?php echo __('users'); ?></h1>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i>
                <?php echo $lang === 'ar' ? 'إضافة' : 'Add'; ?></a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo $lang === 'ar' ? 'المستخدم' : 'User'; ?></th>
                        <th><?php echo __('email'); ?></th>
                        <th><?php echo $lang === 'ar' ? 'الدور' : 'Role'; ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo $lang === 'ar' ? 'آخر دخول' : 'Last Login'; ?></th>
                        <th style="width:120px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = db()->query("SELECT * FROM admins ORDER BY created_at DESC");
                    $users = $stmt->fetchAll();
                    foreach ($users as $user):
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:0.75rem;">
                                    <img src="<?php echo $user['avatar'] ? UPLOADS_URL . '/avatars/' . e($user['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?: $user['username']) . '&background=0066cc&color=fff'; ?>"
                                        style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                    <div>
                                        <strong><?php echo e($user['full_name'] ?: $user['username']); ?></strong>
                                        <div class="text-muted" style="font-size:0.8rem;">@<?php echo e($user['username']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($user['email']); ?></td>
                            <td><span
                                    class="badge badge-<?php echo $user['role'] === 'super_admin' ? 'info' : 'secondary'; ?>"><?php echo $user['role']; ?></span>
                            </td>
                            <td><span
                                    class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </td>
                            <td><?php echo $user['last_login'] ? timeAgo($user['last_login']) : '-'; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-icon btn-secondary"><i
                                            class="fas fa-edit"></i></a>
                                    <?php if ($user['id'] !== getCurrentAdmin()['id']): ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-icon btn-danger"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php
        $user = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $stmt = db()->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([(int) $_GET['id']]);
            $user = $stmt->fetch();
        }
        ?>

        <div class="content-header">
            <h1><?php echo $user ? 'Edit User' : 'Add User'; ?></h1>
            <a href="users.php"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <div class="dashboard-card" style="max-width:600px;">
            <div class="card-body" style="padding:1.5rem;">
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <?php if ($user): ?><input type="hidden" name="id" value="<?php echo $user['id']; ?>"><?php endif; ?>

                    <div class="form-group">
                        <label class="form-label"><?php echo __('username'); ?> *</label>
                        <input type="text" name="username" class="form-control" required
                            value="<?php echo e($user['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo __('email'); ?> *</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?php echo e($user['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'الاسم الكامل' : 'Full Name'; ?></label>
                        <input type="text" name="full_name" class="form-control"
                            value="<?php echo e($user['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo __('password'); ?>
                            <?php echo $user ? '(leave blank to keep)' : '*'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo !$user ? 'required' : ''; ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'الدور' : 'Role'; ?></label>
                        <select name="role" class="form-control">
                            <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin
                            </option>
                            <option value="super_admin" <?php echo ($user['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            <option value="editor" <?php echo ($user['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="is_active" <?php echo ($user['is_active'] ?? 1) ? 'checked' : ''; ?>> Active</label>
                    </div>
                    <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>