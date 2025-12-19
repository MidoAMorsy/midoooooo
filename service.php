<?php
/**
 * Single Service Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : null;

if (!$slug) {
    header('Location: ' . SITE_URL . '/services.php');
    exit;
}

// Get service
try {
    $stmt = db()->prepare("SELECT * FROM services WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $service = $stmt->fetch();
} catch (PDOException $e) {
    $service = null;
}

if (!$service) {
    http_response_code(404);
    include 'error.php';
    exit;
}

// Get other services
try {
    $stmt = db()->prepare("SELECT * FROM services WHERE is_active = 1 AND id != ? ORDER BY sort_order LIMIT 4");
    $stmt->execute([$service['id']]);
    $otherServices = $stmt->fetchAll();
} catch (PDOException $e) {
    $otherServices = [];
}

$seoOptions = [
    'title' => trans($service, 'title'),
    'description' => truncate(strip_tags(trans($service, 'description')), 160),
];

include 'includes/header.php';
?>

<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">
                <i class="<?php echo e($service['icon'] ?: 'fas fa-cog'); ?>"></i>
            </div>
            <h1 class="hero-title"><?php echo e(trans($service, 'title')); ?></h1>
        </div>
    </div>
</section>

<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([
        ['title' => __('services'), 'url' => SITE_URL . '/services.php'],
        ['title' => trans($service, 'title'), 'url' => '']
    ]); ?>
</div>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 350px;gap:3rem;">
            <!-- Main Content -->
            <div>
                <div class="card" style="padding:2rem;margin-bottom:2rem;">
                    <h2 style="margin-bottom:1.5rem;"><?php echo $lang === 'ar' ? 'نظرة عامة' : 'Overview'; ?></h2>
                    <div style="font-size:1.1rem;line-height:1.8;color:var(--gray-700);">
                        <?php echo nl2br(e(trans($service, 'description'))); ?>
                    </div>
                </div>

                <?php
                $features = trans($service, 'features');
                if ($features):
                    $featureList = array_filter(explode('|', $features));
                    ?>
                    <div class="card" style="padding:2rem;">
                        <h2 style="margin-bottom:1.5rem;"><?php echo $lang === 'ar' ? 'ما يتضمنه' : 'What\'s Included'; ?>
                        </h2>
                        <ul style="list-style:none;padding:0;">
                            <?php foreach ($featureList as $feature): ?>
                                <li
                                    style="display:flex;align-items:center;gap:1rem;padding:0.75rem 0;border-bottom:1px solid var(--gray-100);">
                                    <i class="fas fa-check-circle" style="color:var(--success);font-size:1.25rem;"></i>
                                    <span style="font-size:1.05rem;"><?php echo e(trim($feature)); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside style="position:sticky;top:100px;align-self:start;">
                <!-- Pricing Card -->
                <div class="card"
                    style="padding:2rem;margin-bottom:1.5rem;text-align:center;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;">
                    <?php if ($service['price_from']): ?>
                        <div style="font-size:0.9rem;opacity:0.9;">
                            <?php echo $lang === 'ar' ? 'يبدأ من' : 'Starting from'; ?></div>
                        <div style="font-size:2.5rem;font-weight:700;margin:0.5rem 0;">
                            $<?php echo number_format($service['price_from']); ?>
                        </div>
                    <?php else: ?>
                        <div style="font-size:1.5rem;font-weight:600;">
                            <?php echo $lang === 'ar' ? 'اتصل للتسعير' : 'Contact for Pricing'; ?>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/contact.php?service=<?php echo e($service['slug']); ?>"
                        class="btn btn-outline btn-lg"
                        style="width:100%;margin-top:1.5rem;border-color:#fff;color:#fff;">
                        <i class="fas fa-envelope"></i>
                        <?php echo $lang === 'ar' ? 'طلب عرض سعر' : 'Request Quote'; ?>
                    </a>
                </div>

                <!-- Contact Info -->
                <div class="card" style="padding:1.5rem;">
                    <h4 style="margin-bottom:1rem;"><?php echo $lang === 'ar' ? 'تواصل مباشرة' : 'Direct Contact'; ?>
                    </h4>
                    <div style="margin-bottom:1rem;">
                        <a href="tel:<?php echo e(getSetting('contact_phone')); ?>"
                            style="display:flex;align-items:center;gap:0.75rem;color:inherit;padding:0.5rem 0;">
                            <i class="fas fa-phone" style="color:var(--primary);"></i>
                            <?php echo e(getSetting('contact_phone')); ?>
                        </a>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <a href="mailto:<?php echo e(getSetting('contact_email')); ?>"
                            style="display:flex;align-items:center;gap:0.75rem;color:inherit;padding:0.5rem 0;">
                            <i class="fas fa-envelope" style="color:var(--primary);"></i>
                            <?php echo e(getSetting('contact_email')); ?>
                        </a>
                    </div>
                    <div>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', getSetting('contact_phone')); ?>"
                            target="_blank"
                            style="display:flex;align-items:center;gap:0.75rem;color:inherit;padding:0.5rem 0;">
                            <i class="fab fa-whatsapp" style="color:#25D366;"></i>
                            WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Other Services -->
                <?php if (!empty($otherServices)): ?>
                    <div class="card" style="padding:1.5rem;margin-top:1.5rem;">
                        <h4 style="margin-bottom:1rem;"><?php echo $lang === 'ar' ? 'خدمات أخرى' : 'Other Services'; ?></h4>
                        <?php foreach ($otherServices as $other): ?>
                            <a href="<?php echo SITE_URL; ?>/service.php?slug=<?php echo e($other['slug']); ?>"
                                style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid var(--gray-100);color:inherit;">
                                <i class="<?php echo e($other['icon'] ?: 'fas fa-cog'); ?>"
                                    style="color:var(--primary);width:20px;text-align:center;"></i>
                                <span><?php echo e(trans($other, 'title')); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section bg-gradient" style="color:#fff;">
    <div class="container text-center">
        <h2 style="color:#fff;"><?php echo $lang === 'ar' ? 'جاهز للبدء؟' : 'Ready to Get Started?'; ?></h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:2rem;">
            <?php echo $lang === 'ar'
                ? 'دعنا نناقش احتياجاتك ونبدأ العمل على مشروعك.'
                : 'Let\'s discuss your needs and start working on your project.'; ?>
        </p>
        <a href="<?php echo SITE_URL; ?>/contact.php?service=<?php echo e($service['slug']); ?>"
            class="btn btn-outline btn-lg">
            <i class="fas fa-paper-plane"></i>
            <?php echo __('get_in_touch'); ?>
        </a>
    </div>
</section>

<style>
    @media (max-width: 1024px) {
        .section>.container>div {
            grid-template-columns: 1fr !important;
        }

        aside {
            position: static !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>