<?php
/**
 * Admin Panel - Projects Management
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$action = $_GET['action'] ?? 'list';
$pageTitle = __('projects');

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
            'category' => sanitize($_POST['category'] ?? ''),
            'technologies' => sanitize($_POST['technologies'] ?? ''),
            'demo_url' => sanitize($_POST['demo_url'] ?? ''),
            'github_url' => sanitize($_POST['github_url'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'draft'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ];

        if (!empty($_FILES['image']['name'])) {
            $upload = uploadFile($_FILES['image'], 'projects');
            if ($upload['success'])
                $data['image'] = $upload['filename'];
        } elseif (!empty($_POST['media_file'])) {
            // User selected an image from the library
            $data['image'] = sanitize($_POST['media_file']);
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
                db()->prepare("UPDATE projects SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?")->execute($values);
                setFlash('success', 'Project updated');
            } else {
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($data), '?');
                db()->prepare("INSERT INTO projects (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")")->execute(array_values($data));
                setFlash('success', 'Project created');
            }
            redirect(SITE_URL . '/admin/projects.php');
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }

    if (isset($_POST['delete'])) {
        db()->prepare("DELETE FROM projects WHERE id = ?")->execute([(int) $_POST['id']]);
        setFlash('success', 'Project deleted');
        redirect(SITE_URL . '/admin/projects.php');
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h1><?php echo __('projects'); ?></h1>
            </div>
            <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i>
                <?php echo $lang === 'ar' ? 'إضافة' : 'Add'; ?></a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo __('title'); ?></th>
                        <th><?php echo __('category'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo $lang === 'ar' ? 'مميز' : 'Featured'; ?></th>
                        <th style="width:120px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = db()->query("SELECT * FROM projects ORDER BY created_at DESC");
                    $projects = $stmt->fetchAll();
                    foreach ($projects as $project):
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:0.75rem;">
                                    <?php if ($project['image']): ?>
                                        <img src="<?php echo UPLOADS_URL; ?>/projects/<?php echo e($project['image']); ?>"
                                            style="width:50px;height:35px;object-fit:cover;border-radius:4px;">
                                    <?php endif; ?>
                                    <strong><?php echo e(trans($project, 'title')); ?></strong>
                                </div>
                            </td>
                            <td><?php echo e($project['category']); ?></td>
                            <td><span
                                    class="badge badge-<?php echo $project['status'] === 'published' ? 'success' : 'warning'; ?>"><?php echo $project['status']; ?></span>
                            </td>
                            <td><?php echo $project['is_featured'] ? '<i class="fas fa-star text-warning"></i>' : '-'; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="?action=edit&id=<?php echo $project['id']; ?>"
                                        class="btn btn-icon btn-secondary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
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
        $project = null;
        if ($action === 'edit' && isset($_GET['id'])) {
            $stmt = db()->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([(int) $_GET['id']]);
            $project = $stmt->fetch();
        }
        ?>

        <div class="content-header">
            <h1><?php echo $project ? 'Edit Project' : 'Add Project'; ?></h1>
            <a href="projects.php"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <?php if ($project): ?><input type="hidden" name="id" value="<?php echo $project['id']; ?>"><?php endif; ?>

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
                                    value="<?php echo e($project['title_en'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description_en" class="form-control"
                                    rows="4"><?php echo e($project['description_en'] ?? ''); ?></textarea>
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
                                    value="<?php echo e($project['title_ar'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description_ar" class="form-control" rows="4"
                                    dir="rtl"><?php echo e($project['description_ar'] ?? ''); ?></textarea>
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
                                    value="<?php echo e($project['slug'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control"
                                    value="<?php echo e($project['category'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Technologies</label>
                                <input type="text" name="technologies" class="form-control" placeholder="PHP, MySQL, JS"
                                    value="<?php echo e($project['technologies'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Demo URL</label>
                                <input type="url" name="demo_url" class="form-control"
                                    value="<?php echo e($project['demo_url'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github_url" class="form-control"
                                    value="<?php echo e($project['github_url'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?php echo ($project['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($project['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><input type="checkbox" name="is_featured" <?php echo ($project['is_featured'] ?? 0) ? 'checked' : ''; ?>> Featured</label>
                            </div>
                            <div class="form-group">
                            <div class="form-group">
                                <label class="form-label">Image</label>
                                <div id="image-preview-container">
                                    <?php if (!empty($project['image'])): ?>
                                        <?php 
                                            // Check if it's a project upload or media library file
                                            $imgSrc = UPLOADS_URL . '/projects/' . e($project['image']);
                                            if (!file_exists(UPLOADS_PATH . 'projects/' . $project['image']) && file_exists(UPLOADS_PATH . 'media/' . $project['image'])) {
                                                $imgSrc = UPLOADS_URL . '/media/' . e($project['image']); 
                                            }
                                        ?>
                                        <img src="<?php echo $imgSrc; ?>"
                                            style="width:100%;border-radius:4px;margin-bottom:0.5rem;" id="current-image-preview">
                                    <?php endif; ?>
                                </div>
                                
                                <input type="hidden" name="media_file" id="media_file_input">
                                
                                <div style="display:flex;gap:0.5rem;">
                                    <input type="file" name="image" accept="image/*" class="form-control" onchange="previewFile(this)">
                                    <button type="button" class="btn btn-secondary" onclick="openMediaModal()">
                                        <i class="fas fa-images"></i> Library
                                    </button>
                                </div>
                            </div>
                            <button type="submit" name="save" class="btn btn-primary w-full"><i class="fas fa-save"></i>
                                Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    <!-- Media Library Modal -->
    <div id="media-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;width:90%;max-width:800px;height:80vh;display:flex;flex-direction:column;">
            <div style="padding:1.5rem;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                <h3>Select Image</h3>
                <button type="button" onclick="closeMediaModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:1.5rem;overflow-y:auto;flex:1;">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:1rem;">
                    <?php
                    $mediaPath = UPLOADS_PATH . 'media/';
                    if (is_dir($mediaPath)) {
                        $files = scandir($mediaPath);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..' && is_file($mediaPath . $file)) {
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                                    $url = UPLOADS_URL . '/media/' . $file;
                                    echo "
                                    <div class='media-select-item' onclick=\"selectMediaFile('$file', '$url')\" style='cursor:pointer;border:2px solid transparent;border-radius:4px;overflow:hidden;'>
                                        <img src='$url' style='width:100%;aspect-ratio:1;object-fit:cover;'>
                                        <div style='font-size:0.8rem;padding:0.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;'>$file</div>
                                    </div>";
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <div style="padding:1rem;border-top:1px solid #eee;text-align:right;">
                <button type="button" class="btn btn-ghost" onclick="closeMediaModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function previewFile(input) {
            const container = document.getElementById('image-preview-container');
            container.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.borderRadius = '4px';
                    img.style.marginBottom = '0.5rem';
                    container.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
            // Clear media file input if file upload is selected
            document.getElementById('media_file_input').value = '';
        }

        function openMediaModal() {
            document.getElementById('media-modal').style.display = 'flex';
        }

        function closeMediaModal() {
            document.getElementById('media-modal').style.display = 'none';
        }

        function selectMediaFile(filename, url) {
            document.getElementById('media_file_input').value = filename;
            const container = document.getElementById('image-preview-container');
            container.innerHTML = `<img src="${url}" style="width:100%;border-radius:4px;margin-bottom:0.5rem;">`;
            
            // Clear file input
            const fileInput = document.querySelector('input[type="file"][name="image"]');
            fileInput.value = '';
            
            closeMediaModal();
        }
    </script>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>