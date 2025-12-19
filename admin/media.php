<?php
/**
 * Admin Panel - Media Library
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$pageTitle = __('media_library');

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    if (isset($_FILES['files'])) {
        $uploaded = 0;
        $errors = [];

        foreach ($_FILES['files']['name'] as $key => $name) {
            if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['files']['name'][$key],
                    'type' => $_FILES['files']['type'][$key],
                    'tmp_name' => $_FILES['files']['tmp_name'][$key],
                    'error' => $_FILES['files']['error'][$key],
                    'size' => $_FILES['files']['size'][$key],
                ];

                $result = uploadFile($file, 'media', 'all');
                if ($result['success']) {
                    $uploaded++;
                } else {
                    $errors[] = $name . ': ' . $result['error'];
                }
            }
        }

        if ($uploaded > 0) {
            setFlash('success', ($lang === 'ar' ? 'تم رفع ' : 'Uploaded ') . $uploaded . ($lang === 'ar' ? ' ملفات' : ' files'));
        }
        if (!empty($errors)) {
            setFlash('error', implode(', ', $errors));
        }

        redirect(SITE_URL . '/admin/media.php');
    }

    if (isset($_POST['delete'])) {
        $filename = sanitize($_POST['filename']);
        if (deleteFile('media/' . $filename)) {
            setFlash('success', 'File deleted');
        } else {
            setFlash('error', 'Could not delete file');
        }
        redirect(SITE_URL . '/admin/media.php');
    }
}

// Get files from media directory
$mediaPath = UPLOADS_PATH . 'media/';
$files = [];

if (is_dir($mediaPath)) {
    $items = scandir($mediaPath);
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..' && is_file($mediaPath . $item)) {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

            $files[] = [
                'name' => $item,
                'path' => 'media/' . $item,
                'url' => UPLOADS_URL . '/media/' . $item,
                'size' => filesize($mediaPath . $item),
                'ext' => $ext,
                'isImage' => $isImage,
                'modified' => filemtime($mediaPath . $item),
            ];
        }
    }

    // Sort by modified date (newest first)
    usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1><?php echo __('media_library'); ?></h1>
            <p><?php echo $lang === 'ar' ? 'إدارة الملفات والصور' : 'Manage files and images'; ?></p>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('upload-modal').style.display='flex'">
            <i class="fas fa-cloud-upload-alt"></i>
            <?php echo $lang === 'ar' ? 'رفع ملفات' : 'Upload Files'; ?>
        </button>
    </div>

    <?php if (empty($files)): ?>
        <div class="dashboard-card" style="padding:4rem;text-align:center;">
            <i class="fas fa-photo-video" style="font-size:4rem;color:var(--gray-300);margin-bottom:1rem;"></i>
            <h3><?php echo $lang === 'ar' ? 'لا توجد ملفات' : 'No files yet'; ?></h3>
            <p style="color:var(--gray-500);">
                <?php echo $lang === 'ar' ? 'ابدأ برفع بعض الملفات' : 'Start by uploading some files'; ?></p>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;">
            <?php foreach ($files as $file): ?>
                <div class="media-item"
                    style="background:#fff;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow);">
                    <div
                        style="aspect-ratio:1;background:var(--gray-100);display:flex;align-items:center;justify-content:center;position:relative;">
                        <?php if ($file['isImage']): ?>
                            <img src="<?php echo e($file['url']); ?>" alt="" style="width:100%;height:100%;object-fit:cover;"
                                loading="lazy">
                        <?php else: ?>
                            <i class="fas fa-file" style="font-size:3rem;color:var(--gray-400);"></i>
                        <?php endif; ?>

                        <div class="media-actions"
                            style="position:absolute;inset:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;gap:0.5rem;">
                            <button onclick="copyUrl('<?php echo e($file['url']); ?>')" class="btn btn-icon"
                                style="background:#fff;color:var(--gray-800);" title="Copy URL">
                                <i class="fas fa-link"></i>
                            </button>
                            <?php if ($file['isImage']): ?>
                                <a href="<?php echo e($file['url']); ?>" target="_blank" class="btn btn-icon"
                                    style="background:#fff;color:var(--gray-800);" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php endif; ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this file?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="filename" value="<?php echo e($file['name']); ?>">
                                <button type="submit" name="delete" class="btn btn-icon"
                                    style="background:var(--error);color:#fff;" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div style="padding:0.75rem;">
                        <div style="font-size:0.8rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                            title="<?php echo e($file['name']); ?>">
                            <?php echo e($file['name']); ?>
                        </div>
                        <div style="font-size:0.7rem;color:var(--gray-500);">
                            <?php echo formatFileSize($file['size']); ?> • <?php echo strtoupper($file['ext']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div id="upload-modal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:var(--radius-lg);width:90%;max-width:500px;padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3><?php echo $lang === 'ar' ? 'رفع ملفات' : 'Upload Files'; ?></h3>
            <button onclick="document.getElementById('upload-modal').style.display='none'"
                style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <div style="border:2px dashed var(--gray-300);border-radius:var(--radius);padding:3rem;text-align:center;margin-bottom:1.5rem;"
                id="drop-zone">
                <i class="fas fa-cloud-upload-alt" style="font-size:3rem;color:var(--gray-400);margin-bottom:1rem;"></i>
                <p style="color:var(--gray-600);margin-bottom:1rem;">
                    <?php echo $lang === 'ar' ? 'اسحب الملفات هنا أو' : 'Drag files here or'; ?></p>
                <input type="file" name="files[]" id="file-input" multiple style="display:none;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('file-input').click()">
                    <?php echo $lang === 'ar' ? 'اختر ملفات' : 'Choose Files'; ?>
                </button>
            </div>
            <div id="file-list" style="margin-bottom:1rem;"></div>
            <button type="submit" class="btn btn-primary w-full" id="upload-btn" disabled>
                <i class="fas fa-upload"></i>
                <?php echo $lang === 'ar' ? 'رفع' : 'Upload'; ?>
            </button>
        </form>
    </div>
</div>

<style>
    .media-item:hover .media-actions {
        display: flex !important;
    }
</style>

<script>
    // File input handling
    document.getElementById('file-input').addEventListener('change', function () {
        const fileList = document.getElementById('file-list');
        const uploadBtn = document.getElementById('upload-btn');
        fileList.innerHTML = '';

        if (this.files.length > 0) {
            uploadBtn.disabled = false;
            for (let file of this.files) {
                fileList.innerHTML += `<div style="padding:0.5rem;background:var(--gray-50);border-radius:4px;margin-bottom:0.5rem;display:flex;justify-content:space-between;">
                <span>${file.name}</span>
                <span style="color:var(--gray-500);">${(file.size / 1024).toFixed(1)} KB</span>
            </div>`;
            }
        } else {
            uploadBtn.disabled = true;
        }
    });

    // Copy URL
    function copyUrl(url) {
        navigator.clipboard.writeText(url);
        showToast('<?php echo $lang === "ar" ? "تم نسخ الرابط" : "URL copied"; ?>', 'success');
    }

    // Close modal on outside click
    document.getElementById('upload-modal').addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
</script>

<?php include 'includes/admin-footer.php'; ?>