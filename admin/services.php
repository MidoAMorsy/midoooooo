<?php
/**
 * Admin Panel - Services Management
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$action = $_GET['action'] ?? 'list';
$pageTitle = __('services');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    if (isset($_POST['save'])) {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
        $data = [
            'title_en' => sanitize($_POST['title_en'] ?? ''),
            'title_ar' => sanitize($_POST['title_ar'] ?? ''),
            'slug' => createSlug($_POST['slug'] ?: $_POST['title_en']),
            'description_en' => sanitize($_POST['description_en'] ?? ''),
            'description_ar' => sanitize($_POST['description_ar'] ?? ''),
            'features_en' => sanitize($_POST['features_en'] ?? ''),
            'features_ar' => sanitize($_POST['features_ar'] ?? ''),
            'icon' => sanitize($_POST['icon'] ?? 'fas fa-cog'),
            'price_from' => !empty($_POST['price_from']) ? (float) $_POST['price_from'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        try {
            if ($id) {
                $fields = [];
                $values = [];
                foreach ($data as $k => $v) {
                    $fields[] = "$k = ?";
                    $values[] = $v;
                }
                $values[] = $id;
                db()->prepare("UPDATE services SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?")->execute($values);
                setFlash('success', 'Service updated');
            } else {
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($data), '?');
                db()->prepare("INSERT INTO services (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")")->execute(array_values($data));
                setFlash('success', 'Service created');
            }
            redirect(SITE_URL . '/admin/services.php');
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }

    if (isset($_POST['delete'])) {
        db()->prepare("DELETE FROM services WHERE id = ?")->execute([(int) $_POST['id']]);
        setFlash('success', 'Service deleted');
        redirect(SITE_URL . '/admin/services.php');
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h1><?php echo __('services'); ?></h1>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i>
                <?php echo $lang === 'ar' ? 'إضافة' : 'Add'; ?></a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;"></th>
                        <th><?php echo __('title'); ?></th>
                        <th><?php echo $lang === 'ar' ? 'السعر' : 'Price'; ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th style="width:120px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = db()->query("SELECT * FROM services ORDER BY sort_order, id");
                    $services = $stmt->fetchAll();
                    foreach ($services as $service):
                        ?>
                        <tr>
                            <td><i class="<?php echo e($service['icon']); ?>"
                                    style="font-size:1.5rem;color:var(--primary);"></i></td>
                            <td><strong><?php echo e(trans($service, 'title')); ?></strong></td>
                            <td><?php echo $service['price_from'] ? '$' . number_format($service['price_from']) : '-'; ?></td>
                            <td><span
                                    class="badge badge-<?php echo $service['is_active'] ? 'success' : 'warning'; ?>"><?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="?action=edit&id=<?php echo $service['id']; ?>"
                                        class="btn btn-icon btn-secondary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-icon btn-danger"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php
        $service = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $stmt = db()->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([(int) $_GET['id']]);
            $service = $stmt->fetch();
        }
        ?>

        <div class="content-header">
            <h1><?php echo $service ? 'Edit Service' : 'Add Service'; ?></h1>
            <a href="services.php"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <form method="POST">
            <?php echo csrfField(); ?>
            <?php if ($service): ?><input type="hidden" name="id" value="<?php echo $service['id']; ?>"><?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;">
                <div>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>English</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title_en" class="form-control" required
                                    value="<?php echo e($service['title_en'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description_en" class="form-control"
                                    rows="4"><?php echo e($service['description_en'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Features (one per line)</label>
                                <textarea name="features_en" class="form-control"
                                    rows="4"><?php echo e($service['features_en'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-card mt-2">
                        <div class="card-header">
                            <h2>Arabic</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Title</label>
                                <input type="text" name="title_ar" class="form-control" dir="rtl"
                                    value="<?php echo e($service['title_ar'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description_ar" class="form-control" rows="4"
                                    dir="rtl"><?php echo e($service['description_ar'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Features (one per line)</label>
                                <textarea name="features_ar" class="form-control" rows="4"
                                    dir="rtl"><?php echo e($service['features_ar'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2>Settings</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control"
                                    value="<?php echo e($service['slug'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Icon (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control" placeholder="fas fa-cog"
                                    value="<?php echo e($service['icon'] ?? 'fas fa-cog'); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Starting Price</label>
                                <input type="number" name="price_from" class="form-control" step="0.01"
                                    value="<?php echo e($service['price_from'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control"
                                    value="<?php echo e($service['sort_order'] ?? 0); ?>">
                            </div>
                            <div class="form-group">
                                <label><input type="checkbox" name="is_active" <?php echo ($service['is_active'] ?? 1) ? 'checked' : ''; ?>> Active</label>
                            </div>
                            <button type="submit" name="save" class="btn btn-primary w-full"><i class="fas fa-save"></i>
                                Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>