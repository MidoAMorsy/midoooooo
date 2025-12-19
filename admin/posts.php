<?php
/**
 * Admin Panel - Posts Management
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$action = $_GET['action'] ?? 'list';
$pageTitle = __('posts');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Invalid request');
        redirect(SITE_URL . '/admin/posts.php');
    }
    
    if (isset($_POST['save'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'title_en' => sanitize($_POST['title_en'] ?? ''),
            'title_ar' => sanitize($_POST['title_ar'] ?? ''),
            'slug' => createSlug($_POST['slug'] ?: $_POST['title_en']),
            'excerpt_en' => sanitize($_POST['excerpt_en'] ?? ''),
            'excerpt_ar' => sanitize($_POST['excerpt_ar'] ?? ''),
            'content_en' => $_POST['content_en'] ?? '',
            'content_ar' => $_POST['content_ar'] ?? '',
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'status' => sanitize($_POST['status'] ?? 'draft'),
            'author_id' => getCurrentAdmin()['id'],
        ];
        
        // Handle featured image upload
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = uploadFile($_FILES['featured_image'], 'blog');
            if ($upload['success']) {
                $data['featured_image'] = $upload['filename'];
            }
        } elseif (!empty($_POST['media_file'])) {
            // User selected an image from the library
            $data['featured_image'] = sanitize($_POST['media_file']);
        }
        
        try {
            if ($id) {
                // Update
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                
                $sql = "UPDATE posts SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
                $stmt = db()->prepare($sql);
                $stmt->execute($values);
                
                logActivity('post_update', "Updated post: {$data['title_en']}");
                setFlash('success', 'Post updated successfully');
            } else {
                // Insert
                $fields = array_keys($data);
                $placeholders = array_fill(0, count($data), '?');
                
                $sql = "INSERT INTO posts (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = db()->prepare($sql);
                $stmt->execute(array_values($data));
                $id = db()->lastInsertId();
                
                logActivity('post_create', "Created post: {$data['title_en']}");
                setFlash('success', 'Post created successfully');
            }
            
            // Handle tags
            if (isset($_POST['tags'])) {
                db()->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$id]);
                foreach ($_POST['tags'] as $tagId) {
                    db()->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)")->execute([$id, $tagId]);
                }
            }
            
            redirect(SITE_URL . '/admin/posts.php');
            
        } catch (PDOException $e) {
            setFlash('error', 'Database error: ' . $e->getMessage());
        }
    }
    
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        try {
            $stmt = db()->prepare("SELECT title_en FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
            db()->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
            
            logActivity('post_delete', "Deleted post: {$post['title_en']}");
            setFlash('success', 'Post deleted successfully');
        } catch (PDOException $e) {
            setFlash('error', 'Cannot delete post');
        }
        redirect(SITE_URL . '/admin/posts.php');
    }
}

// Get categories for form
try {
    $categories = db()->query("SELECT * FROM categories ORDER BY name_en")->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get tags for form
try {
    $tags = db()->query("SELECT * FROM tags ORDER BY name_en")->fetchAll();
} catch (PDOException $e) {
    $tags = [];
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <?php if ($action === 'list'): ?>
    <!-- Posts List -->
    <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1><?php echo __('posts'); ?></h1>
            <p><?php echo $lang === 'ar' ? 'إدارة المقالات' : 'Manage blog posts'; ?></p>
        </div>
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            <?php echo $lang === 'ar' ? 'إضافة مقال' : 'Add Post'; ?>
        </a>
    </div>
    
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px;"><input type="checkbox" id="select-all"></th>
                    <th><?php echo __('title'); ?></th>
                    <th><?php echo __('category'); ?></th>
                    <th><?php echo __('status'); ?></th>
                    <th><?php echo $lang === 'ar' ? 'المشاهدات' : 'Views'; ?></th>
                    <th><?php echo $lang === 'ar' ? 'التاريخ' : 'Date'; ?></th>
                    <th style="width:120px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $page = max(1, (int)($_GET['page'] ?? 1));
                $perPage = 15;
                $offset = ($page - 1) * $perPage;
                
                try {
                    $total = db()->query("SELECT COUNT(*) FROM posts")->fetchColumn();
                    $stmt = db()->prepare("SELECT p.*, c.name_en as category_name_en, c.name_ar as category_name_ar 
                                          FROM posts p 
                                          LEFT JOIN categories c ON p.category_id = c.id 
                                          ORDER BY p.created_at DESC 
                                          LIMIT $perPage OFFSET $offset");
                    $stmt->execute();
                    $posts = $stmt->fetchAll();
                } catch (PDOException $e) {
                    $posts = [];
                    $total = 0;
                }
                
                if (empty($posts)):
                ?>
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding:3rem;">
                        <?php echo __('no_results'); ?>
                    </td>
                </tr>
                <?php else: foreach ($posts as $post): ?>
                <tr>
                    <td><input type="checkbox" class="item-checkbox" value="<?php echo $post['id']; ?>"></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <?php if ($post['featured_image']): ?>
                            <img src="<?php echo UPLOADS_URL; ?>/blog/<?php echo e($post['featured_image']); ?>" 
                                 style="width:50px;height:35px;object-fit:cover;border-radius:4px;">
                            <?php endif; ?>
                            <div>
                                <strong><?php echo e(trans($post, 'title')); ?></strong>
                                <div class="text-muted" style="font-size:0.75rem;"><?php echo e($post['slug']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e($post['category_name_' . $lang] ?? '-'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                            <?php echo $post['status']; ?>
                        </span>
                    </td>
                    <td><?php echo number_format($post['views']); ?></td>
                    <td><?php echo formatDate($post['created_at']); ?></td>
                    <td>
                        <div class="actions">
                            <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-icon btn-secondary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>" target="_blank" class="btn btn-icon btn-secondary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-icon btn-danger" title="Delete">
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
    
    <?php 
    $pagination = paginate($total, $perPage, $page);
    echo paginationHTML($pagination, SITE_URL . '/admin/posts.php?'); 
    ?>
    
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
    <?php
    $post = null;
    $postTags = [];
    if ($action === 'edit' && isset($_GET['id'])) {
        try {
            $stmt = db()->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([(int)$_GET['id']]);
            $post = $stmt->fetch();
            
            if ($post) {
                $stmt = db()->prepare("SELECT tag_id FROM post_tags WHERE post_id = ?");
                $stmt->execute([$post['id']]);
                $postTags = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (PDOException $e) {
            $post = null;
        }
    }
    ?>
    
    <div class="content-header">
        <h1><?php echo $post ? ($lang === 'ar' ? 'تعديل المقال' : 'Edit Post') : ($lang === 'ar' ? 'إضافة مقال' : 'Add Post'); ?></h1>
        <a href="posts.php" style="color:var(--gray-500);">
            <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>"></i>
            <?php echo $lang === 'ar' ? 'العودة للقائمة' : 'Back to list'; ?>
        </a>
    </div>
    
    <form method="POST" enctype="multipart/form-data" data-validate>
        <?php echo csrfField(); ?>
        <?php if ($post): ?>
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <?php endif; ?>
        
        <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;">
            <!-- Main Content -->
            <div>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><?php echo $lang === 'ar' ? 'المحتوى بالإنجليزية' : 'English Content'; ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label"><?php echo __('title'); ?> *</label>
                            <input type="text" name="title_en" id="title_en" class="form-control" required
                                   value="<?php echo e($post['title_en'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $lang === 'ar' ? 'المقتطف' : 'Excerpt'; ?></label>
                            <textarea name="excerpt_en" class="form-control" rows="2"><?php echo e($post['excerpt_en'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $lang === 'ar' ? 'المحتوى' : 'Content'; ?> *</label>
                            <textarea name="content_en" class="form-control" rows="10" data-editor required><?php echo e($post['content_en'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card mt-2">
                    <div class="card-header">
                        <h2><?php echo $lang === 'ar' ? 'المحتوى بالعربية' : 'Arabic Content'; ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label"><?php echo __('title'); ?></label>
                            <input type="text" name="title_ar" class="form-control" dir="rtl"
                                   value="<?php echo e($post['title_ar'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $lang === 'ar' ? 'المقتطف' : 'Excerpt'; ?></label>
                            <textarea name="excerpt_ar" class="form-control" rows="2" dir="rtl"><?php echo e($post['excerpt_ar'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $lang === 'ar' ? 'المحتوى' : 'Content'; ?></label>
                            <textarea name="content_ar" class="form-control" rows="10" dir="rtl" data-editor><?php echo e($post['content_ar'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><?php echo $lang === 'ar' ? 'النشر' : 'Publish'; ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label"><?php echo __('status'); ?></label>
                            <select name="status" class="form-control">
                                <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                                    <?php echo $lang === 'ar' ? 'مسودة' : 'Draft'; ?>
                                </option>
                                <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                                    <?php echo $lang === 'ar' ? 'منشور' : 'Published'; ?>
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control"
                                   value="<?php echo e($post['slug'] ?? ''); ?>"
                                   placeholder="auto-generated-from-title">
                        </div>
                        <button type="submit" name="save" class="btn btn-primary" style="width:100%;">
                            <i class="fas fa-save"></i>
                            <?php echo $lang === 'ar' ? 'حفظ' : 'Save'; ?>
                        </button>
                    </div>
                </div>
                
                <div class="dashboard-card mt-2">
                    <div class="card-header">
                        <h2><?php echo __('category'); ?></h2>
                    </div>
                    <div class="card-body">
                        <select name="category_id" class="form-control">
                            <option value=""><?php echo $lang === 'ar' ? 'بدون تصنيف' : 'No Category'; ?></option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($post['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e(trans($cat, 'name')); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php if (!empty($tags)): ?>
                <div class="dashboard-card mt-2">
                    <div class="card-header">
                        <h2><?php echo __('tags'); ?></h2>
                    </div>
                    <div class="card-body" style="max-height:200px;overflow-y:auto;">
                        <?php foreach ($tags as $tag): ?>
                        <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                   <?php echo in_array($tag['id'], $postTags) ? 'checked' : ''; ?>>
                            <?php echo e(trans($tag, 'name')); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="dashboard-card mt-2">
                    <div class="card-header">
                        <h2><?php echo $lang === 'ar' ? 'الصورة البارزة' : 'Featured Image'; ?></h2>
                    </div>
                    <div class="card-body">
                        <div id="image-preview-container">
                        <?php if (!empty($post['featured_image'])): ?>
                            <?php 
                                // Check if it's a blog upload or media library file
                                $imgSrc = UPLOADS_URL . '/blog/' . e($post['featured_image']);
                                if (!file_exists(UPLOADS_PATH . 'blog/' . $post['featured_image']) && file_exists(UPLOADS_PATH . 'media/' . $post['featured_image'])) {
                                    $imgSrc = UPLOADS_URL . '/media/' . e($post['featured_image']); 
                                }
                            ?>
                            <img src="<?php echo $imgSrc; ?>" id="image-preview"
                                 style="width:100%;border-radius:var(--radius);margin-bottom:1rem;">
                        <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="media_file" id="media_file_input">
                        
                        <div style="display:flex;gap:0.5rem;flex-direction:column;">
                            <input type="file" name="featured_image" accept="image/*" class="form-control" onchange="previewFile(this)">
                            <button type="button" class="btn btn-secondary w-full" onclick="openMediaModal()">
                                <i class="fas fa-images"></i> Select from Library
                            </button>
                        </div>
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
                <button type="button" onclick="closeMediaModal()" style="background:none;border:none;font-size:1.5rem;border-radius:50%;width:40px;height:40px;cursor:pointer;">&times;</button>
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

    <!-- TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '[data-editor]',
            height: 400,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            images_upload_url: 'upload_image.php', // We might need to implement this or just use base64 for now
            automatic_uploads: true,
            file_picker_types: 'image',
            // Simple file picker callback for Media Library could be added here
        });

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
                    img.style.marginBottom = '1rem';
                    container.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
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
            container.innerHTML = `<img src="${url}" style="width:100%;border-radius:4px;margin-bottom:1rem;">`;
            
            const fileInput = document.querySelector('input[type="file"][name="featured_image"]');
            fileInput.value = '';
            
            closeMediaModal();
        }
    </script>
    
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
