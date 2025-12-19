<?php
/**
 * Admin Settings
 */

require_once '../includes/config.php';
requireLogin();

$pageTitle = __('site_settings');
include 'includes/admin-header.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. General Settings
    $settings = [
        'site_name_en' => $_POST['site_name_en'] ?? '',
        'site_name_ar' => $_POST['site_name_ar'] ?? '',
        'site_email' => $_POST['site_email'] ?? '',
        'admin_notification_email' => $_POST['admin_notification_email'] ?? '',
        'map_embed_url' => $_POST['map_embed_url'] ?? '',

        // Social Media
        'facebook_url' => $_POST['facebook'] ?? '',
        'twitter_url' => $_POST['twitter'] ?? '',
        'linkedin_url' => $_POST['linkedin'] ?? '',
        'instagram_url' => $_POST['instagram'] ?? '',
        'tiktok_url' => $_POST['tiktok'] ?? '', // Added TikTok

        // About Section
        'about_content_en' => $_POST['about_content_en'] ?? '',
        'about_content_ar' => $_POST['about_content_ar'] ?? '',
    ];

    foreach ($settings as $key => $value) {
        updateSetting($key, $value);
    }

    // 2. File Uploads
    // Logo (Light)
    if (!empty($_FILES['site_logo']['name'])) {
        $url = uploadFile($_FILES['site_logo'], 'images');
        if ($url['success'])
            updateSetting('header_logo', $url['filename']);
    }

    // Logo (Dark) - For white backgrounds
    if (!empty($_FILES['site_logo_dark']['name'])) {
        $url = uploadFile($_FILES['site_logo_dark'], 'images');
        if ($url['success'])
            updateSetting('header_logo_dark', $url['filename']); // New Setting
    }

    // Profile Picture
    if (!empty($_FILES['profile_picture']['name'])) {
        $url = uploadFile($_FILES['profile_picture'], 'profile');
        if ($url['success'])
            updateSetting('profile_picture', $url['filename']);
    }

    // CV File
    if (!empty($_FILES['cv_file']['name'])) {
        $url = uploadFile($_FILES['cv_file'], 'cv');
        if ($url['success'])
            updateSetting('cv_file', $url['filename']);
    }

    setFlash('success', __('settings_saved'));
    redirect('settings.php');
}

// Helper to get setting safely
function getVal($key, $default = '')
{
    return e(getSetting($key, $default));
}
?>

<div class="admin-content">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-cog"></i> <?php echo __('site_settings'); ?></h3>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">

                <!-- General Info -->
                <h4 class="mb-3 text-primary border-bottom pb-2">General Information</h4>
                <div class="grid grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Site Name (English)</label>
                        <input type="text" name="site_name_en" class="form-control"
                            value="<?php echo getVal('site_name_en'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Site Name (Arabic)</label>
                        <input type="text" name="site_name_ar" class="form-control"
                            value="<?php echo getVal('site_name_ar'); ?>">
                    </div>
                </div>

                <div class="grid grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Public Contact Email</label>
                        <input type="email" name="site_email" class="form-control"
                            value="<?php echo getVal('site_email'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Notification Email</label>
                        <input type="email" name="admin_notification_email" class="form-control"
                            value="<?php echo getVal('admin_notification_email'); ?>">
                        <small class="text-muted">Receive lead notifications here.</small>
                    </div>
                </div>

                <!-- Branding -->
                <h4 class="mb-3 mt-5 text-primary border-bottom pb-2">Branding & Assets</h4>
                <div class="grid grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Header Logo (Light / Default)</label>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        <?php if (getSetting('header_logo')): ?>
                            <div class="mt-2 p-2 bg-dark rounded d-inline-block">
                                <img src="<?php echo getUploadedUrl(getSetting('header_logo')); ?>" style="height:40px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Header Logo (Dark) - <small>For white backgrounds</small></label>
                        <input type="file" name="site_logo_dark" class="form-control" accept="image/*">
                        <?php if (getSetting('header_logo_dark')): ?>
                            <div class="mt-2 p-2 bg-light border rounded d-inline-block">
                                <img src="<?php echo getUploadedUrl(getSetting('header_logo_dark')); ?>"
                                    style="height:40px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Profile Picture (Hero Section)</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        <?php if (getSetting('profile_picture')): ?>
                            <img src="<?php echo getUploadedUrl(getSetting('profile_picture')); ?>"
                                style="height:60px;border-radius:50%;margin-top:10px;">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">CV File (PDF)</label>
                        <input type="file" name="cv_file" class="form-control" accept=".pdf">
                        <?php if (getSetting('cv_file')): ?>
                            <a href="<?php echo getUploadedUrl(getSetting('cv_file'), 'cv'); ?>" target="_blank"
                                class="btn btn-sm btn-ghost mt-2">
                                <i class="fas fa-file-pdf"></i> View Current CV
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- About Me -->
                <h4 class="mb-3 mt-5 text-primary border-bottom pb-2">About Me Section</h4>
                <div class="form-group mb-4">
                    <label class="form-label">About Content (English)</label>
                    <textarea name="about_content_en" class="form-control"
                        rows="5"><?php echo getVal('about_content_en'); ?></textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">About Content (Arabic)</label>
                    <textarea name="about_content_ar" class="form-control" rows="5"
                        dir="rtl"><?php echo getVal('about_content_ar'); ?></textarea>
                </div>

                <!-- Contact & Social -->
                <h4 class="mb-3 mt-5 text-primary border-bottom pb-2">Contact & Social Media</h4>

                <div class="form-group mb-4">
                    <label class="form-label">Google Maps Embed URL</label>
                    <textarea name="map_embed_url" class="form-control" rows="3"
                        placeholder='<iframe src="..."></iframe>'><?php echo getVal('map_embed_url'); ?></textarea>
                </div>

                <div class="grid grid-3 gap-4">
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-facebook"></i> Facebook URL</label>
                        <input type="url" name="facebook" class="form-control"
                            value="<?php echo getVal('facebook_url'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-twitter"></i> Twitter/X URL</label>
                        <input type="url" name="twitter" class="form-control"
                            value="<?php echo getVal('twitter_url'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-linkedin"></i> LinkedIn URL</label>
                        <input type="url" name="linkedin" class="form-control"
                            value="<?php echo getVal('linkedin_url'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-instagram"></i> Instagram URL</label>
                        <input type="url" name="instagram" class="form-control"
                            value="<?php echo getVal('instagram_url'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-tiktok"></i> TikTok URL</label>
                        <input type="url" name="tiktok" class="form-control"
                            value="<?php echo getVal('tiktok_url'); ?>">
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> <?php echo __('save_changes'); ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>