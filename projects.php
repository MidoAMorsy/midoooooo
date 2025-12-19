<?php
/**
 * Ahmed Ashraf Portfolio - Projects Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

// SEO Options
$seoOptions = [
    'title' => __('projects'),
    'description' => $lang === 'ar'
        ? 'مشاريع وتطبيقات ويب - أحمد أشرف'
        : 'Web projects and applications - Ahmed Ashraf',
];

// Get all projects
try {
    $stmt = db()->query("SELECT * FROM projects WHERE status = 'published' ORDER BY featured DESC, sort_order, id");
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    $projects = [];
}

// Get unique categories
$categories = array_unique(array_filter(array_column($projects, 'category')));

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="min-height:50vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo __('my_projects'); ?></h1>
            <p class="hero-subtitle" style="max-width:600px;margin:0 auto;">
                <?php echo $lang === 'ar'
                    ? 'مجموعة من المشاريع والتطبيقات التي قمت بتطويرها'
                    : 'A collection of projects and applications I have developed'; ?>
            </p>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([['title' => __('projects'), 'url' => '']]); ?>
</div>

<!-- Filter Tabs -->
<?php if (!empty($categories)): ?>
    <section style="padding:2rem 0;">
        <div class="container">
            <div class="flex justify-center gap-2 flex-wrap" data-tabs>
                <button class="btn btn-primary" data-tab="all">
                    <?php echo $lang === 'ar' ? 'الكل' : 'All'; ?>
                </button>
                <?php foreach ($categories as $cat): ?>
                    <button class="btn btn-ghost" data-tab="<?php echo e(createSlug($cat)); ?>">
                        <?php echo e($cat); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Projects Grid -->
<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="grid grid-3" id="projects-grid">
            <?php foreach ($projects as $index => $project): ?>
                <div class="card project-card" data-category="<?php echo e(createSlug($project['category'] ?? '')); ?>"
                    data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                    <div class="card-image" style="aspect-ratio: 16/9; overflow: hidden; background: #f0f0f0;">
                        <img src="<?php
                        $img = $project['featured_image'];
                        if (filter_var($img, FILTER_VALIDATE_URL)) {
                            echo e($img);
                        } elseif ($img) {
                            // The user might have images in root uploads or assets
                            echo SITE_URL . '/uploads/projects/' . e($img);
                        } else {
                            echo ASSETS_URL . '/images/project-placeholder.jpg';
                        }
                        ?>" alt="<?php echo e(trans($project, 'title')); ?>" 
                            loading="lazy" 
                            style="width:100%; height:100%; object-fit:cover;"
                            onerror="this.src='<?php echo ASSETS_URL; ?>/images/project-placeholder.jpg';">
                        <div class="card-overlay">
                            <?php if ($project['demo_url']): ?>
                                <a href="<?php echo e($project['demo_url']); ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                    <?php echo __('live_demo'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($project['github_url']): ?>
                                <a href="<?php echo e($project['github_url']); ?>" class="btn btn-secondary" target="_blank">
                                    <i class="fab fa-github"></i>
                                    <?php echo __('view_code'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($project['category']): ?>
                            <span class="tag" style="margin-bottom:0.75rem;"><?php echo e($project['category']); ?></span>
                        <?php endif; ?>

                        <h3 class="card-title"><?php echo e(trans($project, 'title')); ?></h3>
                        <p class="card-text"><?php echo e(truncate(trans($project, 'description'), 120)); ?></p>

                        <div class="project-tags">
                            <?php
                            $techs = explode(',', $project['technologies'] ?? '');
                            foreach (array_slice($techs, 0, 4) as $tech): ?>
                                <span class="tag"><?php echo e(trim($tech)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Apps Section -->
<section class="section bg-gradient" style="color:#fff;">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 style="color:#fff;">
                <?php echo $lang === 'ar' ? 'تطبيقات الويب المميزة' : 'Featured Web Applications'; ?>
            </h2>
            <p style="color:rgba(255,255,255,0.8);">
                <?php echo $lang === 'ar'
                    ? 'جرب هذه التطبيقات مباشرة'
                    : 'Try these applications directly'; ?>
            </p>
        </div>

        <div class="grid grid-3">
            <!-- Attendance System -->
            <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;" data-aos="fade-up">
                <div
                    style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;">
                    <i class="fas fa-user-clock" style="font-size:1.5rem;"></i>
                </div>
                <h3 style="color:#fff;margin-bottom:1rem;"><?php echo __('attendance_system'); ?></h3>
                <p style="color:rgba(255,255,255,0.8);margin-bottom:1.5rem;">
                    <?php echo $lang === 'ar'
                        ? 'نظام إدارة الحضور والانصراف مع تحليل البيانات والتقارير التفصيلية'
                        : 'Attendance management system with data analysis and detailed reports'; ?>
                </p>
                <a href="<?php echo SITE_URL; ?>/apps/attendance/" class="btn btn-outline">
                    <?php echo __('try_now'); ?>
                    <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                </a>
            </div>

            <!-- QR Generator -->
            <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;" data-aos="fade-up"
                data-aos-delay="100">
                <div
                    style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;">
                    <i class="fas fa-qrcode" style="font-size:1.5rem;"></i>
                </div>
                <h3 style="color:#fff;margin-bottom:1rem;"><?php echo __('qr_generator'); ?></h3>
                <p style="color:rgba(255,255,255,0.8);margin-bottom:1.5rem;">
                    <?php echo $lang === 'ar'
                        ? 'إنشاء رموز QR للشهادات مع أوضاع متعددة للإنشاء والتصدير'
                        : 'Generate QR codes for certificates with multiple generation and export modes'; ?>
                </p>
                <a href="<?php echo SITE_URL; ?>/apps/qr-generator/" class="btn btn-outline">
                    <?php echo __('try_now'); ?>
                    <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                </a>
            </div>

            <!-- Certificate Creator -->
            <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;" data-aos="fade-up"
                data-aos-delay="200">
                <div
                    style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;">
                    <i class="fas fa-certificate" style="font-size:1.5rem;"></i>
                </div>
                <h3 style="color:#fff;margin-bottom:1rem;"><?php echo __('certificate_creator'); ?></h3>
                <p style="color:rgba(255,255,255,0.8);margin-bottom:1.5rem;">
                    <?php echo $lang === 'ar'
                        ? 'تصميم وإنشاء شهادات احترافية مع دعم الخطوط العربية'
                        : 'Design and create professional certificates with Arabic font support'; ?>
                </p>
                <a href="<?php echo SITE_URL; ?>/apps/certificate-creator/" class="btn btn-outline">
                    <?php echo __('try_now'); ?>
                    <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    // Category filtering
    document.querySelectorAll('[data-tab]').forEach(btn => {
        btn.addEventListener('click', function () {
            // Update active button
            document.querySelectorAll('[data-tab]').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-ghost');
            });
            this.classList.remove('btn-ghost');
            this.classList.add('btn-primary');

            // Filter projects
            const category = this.dataset.tab;
            document.querySelectorAll('.project-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>