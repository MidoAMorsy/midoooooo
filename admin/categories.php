<?php
/**
 * Admin Panel - Categories & Tags Management
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$type = $_GET['type'] ?? 'categories';
$action = $_GET['action'] ?? 'list';
$pageTitle = $type === 'tags' ? (__('tags')) : (__('categories'));

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $table = $type === 'tags' ? 'tags' : 'categories';

    if (isset($_POST['save'])) {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
        $data = [
            'name_en' => sanitize($_POST['name_en'] ?? ''),
            'name_ar' => sanitize($_POST['name_ar'] ?? ''),
            'slug' => createSlug($_POST['slug'] ?: $_POST['name_en']),
        ];

        if ($type === 'categories') {
            $data['description_en'] = sanitize($_POST['description_en'] ?? '');
            $data['description_ar'] = sanitize($_POST['description_ar'] ?? '');
            $data['parent_id'] = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        }

        try {
            if ($id) {
                $fields = [];
                foreach ($data as $k => $v)
                    $fields[] = "$k = ?";
                $values = array_values($data);
                $values[] = $id;
                db()->prepare("UPDATE $table SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
                setFlash('success', 'Updated successfully');
            } else {
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($data), '?');
                db()->prepare("INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")")->execute(array_values($data));
                setFlash('success', 'Created successfully');
            }
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect(SITE_URL . '/admin/categories.php?type=' . $type);
    }

    if (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        try {
            db()->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
            setFlash('success', 'Deleted successfully');
        } catch (PDOException $e) {
            setFlash('error', 'Cannot delete - may have related items');
        }
        redirect(SITE_URL . '/admin/categories.php?type=' . $type);
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h1><?php echo $pageTitle; ?></h1>
                <!-- Type Switcher -->
                <div style="margin-top:0.5rem;">
                    <a href="?type=categories"
                        class="btn btn-sm <?php echo $type === 'categories' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo __('categories'); ?>
                    </a>
                    <a href="?type=tags"
                        class="btn btn-sm <?php echo $type === 'tags' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo __('tags'); ?>
                    </a>
                </div>
            </div>
            <a href="?type=<?php echo $type; ?>&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo $lang === 'ar' ? 'إضافة' : 'Add New'; ?>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo $lang === 'ar' ? 'الاسم' : 'Name'; ?></th>
                        <th>Slug</th>
                        <?php if ($type === 'categories'): ?>
                            <th><?php echo $lang === 'ar' ? 'المقالات' : 'Posts'; ?></th>
                        <?php endif; ?>
                        <th style="width:120px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $table = $type === 'tags' ? 'tags' : 'categories';
                    try {
                        if ($type === 'categories') {
                            $stmt = db()->query("SELECT c.*, (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count FROM categories c ORDER BY c.name_en");
                        } else {
                            $stmt = db()->query("SELECT * FROM tags ORDER BY name_en");
                        }
                        $items = $stmt->fetchAll();
                    } catch (PDOException $e) {
                        $items = [];
                    }

                    if (empty($items)):
                        ?>
                        <tr>
                            <td colspan="<?php echo $type === 'categories' ? 4 : 3; ?>" class="text-center text-muted"
                                style="padding:3rem;">
                                <?php echo __('no_results'); ?>
                            </td>
                        </tr>
                    <?php else:
                        foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e(trans($item, 'name')); ?></strong>
                                    <?php if (!empty($item['name_ar']) && $lang === 'en'): ?>
                                        <div class="text-muted" style="font-size:0.8rem;"><?php echo e($item['name_ar']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo e($item['slug']); ?></code></td>
                                <?php if ($type === 'categories'): ?>
                                    <td><?php echo $item['post_count']; ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="actions">
                                        <a href="?type=<?php echo $type; ?>&action=edit&id=<?php echo $item['id']; ?>"
                                            class="btn btn-icon btn-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-icon btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php
        $item = null;
        $table = $type === 'tags' ? 'tags' : 'categories';
        if ($action === 'edit' && isset($_GET['id'])) {
            $stmt = db()->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->execute([(int) $_GET['id']]);
            $item = $stmt->fetch();
        }

        // Get parent categories for dropdown
        $parentCategories = [];
        if ($type === 'categories') {
            try {
                $parentCategories = db()->query("SELECT id, name_en, name_ar FROM categories ORDER BY name_en")->fetchAll();
            } catch (PDOException $e) {
            }
        }
        ?>

        <div class="content-header">
            <h1><?php echo $item ? ($lang === 'ar' ? 'تعديل' : 'Edit') : ($lang === 'ar' ? 'إضافة' : 'Add'); ?>
                <?php echo $type === 'tags' ? __('tags') : __('categories'); ?></h1>
            <a href="categories.php?type=<?php echo $type; ?>"><i class="fas fa-arrow-left"></i>
                <?php echo $lang === 'ar' ? 'رجوع' : 'Back'; ?></a>
        </div>

        <div class="dashboard-card" style="max-width:600px;">
            <div class="card-body" style="padding:1.5rem;">
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <?php if ($item): ?><input type="hidden" name="id" value="<?php echo $item['id']; ?>"><?php endif; ?>

                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)'; ?>
                            *</label>
                        <input type="text" name="name_en" class="form-control" required
                            value="<?php echo e($item['name_en'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)'; ?></label>
                        <input type="text" name="name_ar" class="form-control" dir="rtl"
                            value="<?php echo e($item['name_ar'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?php echo e($item['slug'] ?? ''); ?>"
                            placeholder="auto-generated">
                    </div>

                    <?php if ($type === 'categories'): ?>
                        <div class="form-group">
                            <label class="form-label"><?php echo $lang === 'ar' ? 'التصنيف الأب' : 'Parent Category'; ?></label>
                            <select name="parent_id" class="form-control">
                                <option value=""><?php echo $lang === 'ar' ? 'بدون' : 'None'; ?></option>
                                <?php foreach ($parentCategories as $cat): ?>
                                    <?php if ($item && $cat['id'] == $item['id'])
                                        continue; ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($item['parent_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo e(trans($cat, 'name')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label
                                class="form-label"><?php echo $lang === 'ar' ? 'الوصف (إنجليزي)' : 'Description (English)'; ?></label>
                            <textarea name="description_en" class="form-control"
                                rows="3"><?php echo e($item['description_en'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label
                                class="form-label"><?php echo $lang === 'ar' ? 'الوصف (عربي)' : 'Description (Arabic)'; ?></label>
                            <textarea name="description_ar" class="form-control" rows="3"
                                dir="rtl"><?php echo e($item['description_ar'] ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $lang === 'ar' ? 'حفظ' : 'Save'; ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>