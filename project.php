<?php
/**
 * Single Project Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : null;

if (!$slug) {
    header('Location: ' . SITE_URL . '/projects.php');
    exit;
}

// Get project
try {
    $stmt = db()->prepare("SELECT * FROM projects WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $project = $stmt->fetch();
} catch (PDOException $e) {
    $project = null;
}

if (!$project) {
    http_response_code(404);
    include 'error.php';
    exit;
}

// Get related projects
try {
    $stmt = db()->prepare("SELECT * FROM projects WHERE status = 'published' AND id != ? AND category = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$project['id'], $project['category']]);
    $relatedProjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $relatedProjects = [];
}

$seoOptions = [
    'title' => trans($project, 'title'),
    'description' => truncate(strip_tags(trans($project, 'description')), 160),
    'image' => $project['image'] ? UPLOADS_URL . '/projects/' . $project['image'] : null,
];

include 'includes/header.php';
?>

<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <span class="hero-badge"><?php echo e($project['category']); ?></span>
            <h1 class="hero-title"><?php echo e(trans($project, 'title')); ?></h1>
        </div>
    </div>
</section>

<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([
        ['title' => __('projects'), 'url' => SITE_URL . '/projects.php'],
        ['title' => trans($project, 'title'), 'url' => '']
    ]); ?>
</div>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 350px;gap:3rem;">
            <!-- Main Content -->
            <div>
                <?php if ($project['image']): ?>
                    <div style="margin-bottom:2rem;border-radius:1rem;overflow:hidden;">
                        <img src="<?php echo UPLOADS_URL; ?>/projects/<?php echo e($project['image']); ?>"
                            alt="<?php echo e(trans($project, 'title')); ?>" style="width:100%;height:auto;">
                    </div>
                <?php endif; ?>

                <div class="post-content" style="font-size:1.1rem;line-height:1.8;">
                    <?php echo nl2br(e(trans($project, 'description'))); ?>
                </div>
            </div>

            <!-- Sidebar -->
            <aside style="position:sticky;top:100px;align-self:start;">
                <!-- Project Info -->
                <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
                    <h4 style="margin-bottom:1rem;"><?php echo $lang === 'ar' ? 'تفاصيل المشروع' : 'Project Details'; ?>
                    </h4>

                    <?php if ($project['technologies']): ?>
                        <div style="margin-bottom:1rem;">
                            <strong
                                style="display:block;margin-bottom:0.5rem;"><?php echo $lang === 'ar' ? 'التقنيات' : 'Technologies'; ?></strong>
                            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                                <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                                    <span class="tag"><?php echo e(trim($tech)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom:1rem;">
                        <strong
                            style="display:block;margin-bottom:0.5rem;"><?php echo $lang === 'ar' ? 'التصنيف' : 'Category'; ?></strong>
                        <span><?php echo e($project['category']); ?></span>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <strong
                            style="display:block;margin-bottom:0.5rem;"><?php echo $lang === 'ar' ? 'تاريخ النشر' : 'Published'; ?></strong>
                        <span><?php echo formatDate($project['created_at']); ?></span>
                    </div>

                    <hr style="margin:1.5rem 0;">

                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        <?php if ($project['demo_url']): ?>
                            <a href="<?php echo e($project['demo_url']); ?>" target="_blank" class="btn btn-primary w-full">
                                <i class="fas fa-external-link-alt"></i>
                                <?php echo $lang === 'ar' ? 'عرض المشروع' : 'Live Demo'; ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($project['github_url']): ?>
                            <a href="<?php echo e($project['github_url']); ?>" target="_blank"
                                class="btn btn-secondary w-full">
                                <i class="fab fa-github"></i>
                                GitHub
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related Projects -->
                <?php if (!empty($relatedProjects)): ?>
                    <div class="card" style="padding:1.5rem;">
                        <h4 style="margin-bottom:1rem;"><?php echo $lang === 'ar' ? 'مشاريع مشابهة' : 'Related Projects'; ?>
                        </h4>
                        <?php foreach ($relatedProjects as $related): ?>
                            <a href="<?php echo SITE_URL; ?>/project.php?slug=<?php echo e($related['slug']); ?>"
                                style="display:flex;gap:1rem;margin-bottom:1rem;color:inherit;">
                                <?php if ($related['image']): ?>
                                    <img src="<?php echo UPLOADS_URL; ?>/projects/<?php echo e($related['image']); ?>" alt=""
                                        style="width:80px;height:60px;object-fit:cover;border-radius:0.5rem;">
                                <?php endif; ?>
                                <div>
                                    <h5 style="font-size:0.95rem;margin-bottom:0.25rem;">
                                        <?php echo e(truncate(trans($related, 'title'), 50)); ?></h5>
                                    <span
                                        style="font-size:0.8rem;color:var(--gray-500);"><?php echo e($related['category']); ?></span>
                                </div>
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
        <h2 style="color:#fff;"><?php echo $lang === 'ar' ? 'هل لديك مشروع مماثل؟' : 'Have a similar project?'; ?></h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:2rem;">
            <?php echo $lang === 'ar'
                ? 'دعنا نناقش كيف يمكنني مساعدتك في تحقيق رؤيتك.'
                : 'Let\'s discuss how I can help bring your vision to life.'; ?>
        </p>
        <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline btn-lg">
            <i class="fas fa-envelope"></i>
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