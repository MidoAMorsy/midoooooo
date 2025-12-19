<?php
/**
 * Error Page - 404 Not Found
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

http_response_code(404);

$seoOptions = [
    'title' => $lang === 'ar' ? 'الصفحة غير موجودة' : 'Page Not Found',
    'robots' => 'noindex, nofollow',
];

include 'includes/header.php';
?>

<section
    style="min-height:80vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:4rem 1rem;">
    <div>
        <div style="font-size:8rem;font-weight:800;color:var(--primary);line-height:1;margin-bottom:1rem;">
            404
        </div>
        <h1 style="font-size:2rem;margin-bottom:1rem;">
            <?php echo $lang === 'ar' ? 'عفواً! الصفحة غير موجودة' : 'Oops! Page Not Found'; ?>
        </h1>
        <p style="color:var(--gray-600);max-width:500px;margin:0 auto 2rem;">
            <?php echo $lang === 'ar'
                ? 'الصفحة التي تبحث عنها قد تم نقلها أو حذفها أو غير موجودة.'
                : 'The page you are looking for might have been moved, deleted, or does not exist.'; ?>
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;">
            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-home"></i>
                <?php echo __('home'); ?>
            </a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-envelope"></i>
                <?php echo __('contact'); ?>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>