<?php
/**
 * Admin Panel - SEO Settings
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$pageTitle = __('seo_settings');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $settings = [
        'meta_title_en' => sanitize($_POST['meta_title_en'] ?? ''),
        'meta_title_ar' => sanitize($_POST['meta_title_ar'] ?? ''),
        'meta_description_en' => sanitize($_POST['meta_description_en'] ?? ''),
        'meta_description_ar' => sanitize($_POST['meta_description_ar'] ?? ''),
        'meta_keywords_en' => sanitize($_POST['meta_keywords_en'] ?? ''),
        'meta_keywords_ar' => sanitize($_POST['meta_keywords_ar'] ?? ''),
        'og_image' => sanitize($_POST['og_image'] ?? ''),
        'google_analytics' => sanitize($_POST['google_analytics'] ?? ''),
        'google_verification' => sanitize($_POST['google_verification'] ?? ''),
        'bing_verification' => sanitize($_POST['bing_verification'] ?? ''),
        'robots_index' => isset($_POST['robots_index']) ? '1' : '0',
        'robots_follow' => isset($_POST['robots_follow']) ? '1' : '0',
    ];

    try {
        foreach ($settings as $key => $value) {
            updateSetting($key, $value);
        }

        // Handle OG image upload
        if (!empty($_FILES['og_image_file']['name'])) {
            $upload = uploadFile($_FILES['og_image_file'], 'images');
            if ($upload['success']) {
                updateSetting('og_image', UPLOADS_URL . '/images/' . $upload['filename']);
            }
        }

        logActivity('seo_update', 'Updated SEO settings');
        setFlash('success', $lang === 'ar' ? 'تم حفظ إعدادات SEO' : 'SEO settings saved');

    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }

    redirect(SITE_URL . '/admin/seo.php');
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="content-header">
        <h1><?php echo __('seo_settings'); ?></h1>
        <p><?php echo $lang === 'ar' ? 'إعدادات تحسين محركات البحث' : 'Search engine optimization settings'; ?></p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <?php echo csrfField(); ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
            <!-- Meta Tags English -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-globe"></i>
                        <?php echo $lang === 'ar' ? 'Meta Tags (إنجليزي)' : 'Meta Tags (English)'; ?></h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title_en" class="form-control"
                            value="<?php echo e(getSetting('meta_title_en')); ?>"
                            placeholder="Ahmed Ashraf - Digital Marketing Expert">
                        <small class="text-muted">Recommended: 50-60 characters</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description_en" class="form-control" rows="3"
                            placeholder="Professional portfolio showcasing digital marketing and web development services..."><?php echo e(getSetting('meta_description_en')); ?></textarea>
                        <small class="text-muted">Recommended: 150-160 characters</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords_en" class="form-control"
                            value="<?php echo e(getSetting('meta_keywords_en')); ?>"
                            placeholder="digital marketing, web development, SEO">
                    </div>
                </div>
            </div>

            <!-- Meta Tags Arabic -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-globe"></i>
                        <?php echo $lang === 'ar' ? 'Meta Tags (عربي)' : 'Meta Tags (Arabic)'; ?></h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title_ar" class="form-control" dir="rtl"
                            value="<?php echo e(getSetting('meta_title_ar')); ?>"
                            placeholder="أحمد أشرف - خبير التسويق الرقمي">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description_ar" class="form-control" rows="3" dir="rtl"
                            placeholder="محفظة احترافية تعرض خدمات التسويق الرقمي وتطوير الويب..."><?php echo e(getSetting('meta_description_ar')); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords_ar" class="form-control" dir="rtl"
                            value="<?php echo e(getSetting('meta_keywords_ar')); ?>"
                            placeholder="تسويق رقمي، تطوير ويب، سيو">
                    </div>
                </div>
            </div>

            <!-- Social Media / Open Graph -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-share-alt"></i>
                        <?php echo $lang === 'ar' ? 'الشبكات الاجتماعية' : 'Social Sharing'; ?></h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label
                            class="form-label"><?php echo $lang === 'ar' ? 'صورة المشاركة الافتراضية' : 'Default Share Image'; ?></label>
                        <?php if ($ogImage = getSetting('og_image')): ?>
                            <div style="margin-bottom:0.5rem;">
                                <img src="<?php echo e($ogImage); ?>" style="max-width:200px;border-radius:4px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="og_image_file" accept="image/*" class="form-control">
                        <small class="text-muted">Recommended: 1200x630 pixels</small>
                    </div>
                    <div class="form-group">
                        <label
                            class="form-label"><?php echo $lang === 'ar' ? 'أو أدخل رابط الصورة' : 'Or enter image URL'; ?></label>
                        <input type="url" name="og_image" class="form-control"
                            value="<?php echo e(getSetting('og_image')); ?>"
                            placeholder="https://example.com/image.jpg">
                    </div>
                </div>
            </div>

            <!-- Verification & Analytics -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-line"></i>
                        <?php echo $lang === 'ar' ? 'التحقق والتحليلات' : 'Verification & Analytics'; ?></h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" name="google_analytics" class="form-control"
                            value="<?php echo e(getSetting('google_analytics')); ?>"
                            placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Google Search Console</label>
                        <input type="text" name="google_verification" class="form-control"
                            value="<?php echo e(getSetting('google_verification')); ?>" placeholder="Verification code">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bing Webmaster</label>
                        <input type="text" name="bing_verification" class="form-control"
                            value="<?php echo e(getSetting('bing_verification')); ?>" placeholder="Verification code">
                    </div>
                </div>
            </div>

            <!-- Robots Settings -->
            <div class="dashboard-card" style="grid-column:span 2;">
                <div class="card-header">
                    <h2><i class="fas fa-robot"></i>
                        <?php echo $lang === 'ar' ? 'إعدادات الروبوتات' : 'Robots Settings'; ?></h2>
                </div>
                <div class="card-body">
                    <div style="display:flex;gap:2rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="robots_index" <?php echo getSetting('robots_index', '1') === '1' ? 'checked' : ''; ?>>
                            <?php echo $lang === 'ar' ? 'السماح بالفهرسة (index)' : 'Allow indexing (index)'; ?>
                        </label>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="robots_follow" <?php echo getSetting('robots_follow', '1') === '1' ? 'checked' : ''; ?>>
                            <?php echo $lang === 'ar' ? 'السماح بتتبع الروابط (follow)' : 'Allow following links (follow)'; ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i>
                <?php echo $lang === 'ar' ? 'حفظ الإعدادات' : 'Save Settings'; ?>
            </button>
            <a href="<?php echo SITE_URL; ?>/sitemap.php" target="_blank" class="btn btn-secondary btn-lg">
                <i class="fas fa-sitemap"></i>
                <?php echo $lang === 'ar' ? 'عرض Sitemap' : 'View Sitemap'; ?>
            </a>
        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>