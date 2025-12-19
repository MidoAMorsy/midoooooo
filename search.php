<?php
/**
 * Search Results Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();
$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'all';

$results = [
    'posts' => [],
    'projects' => [],
    'services' => []
];

if (!empty($query) && strlen($query) >= 2) {
    $searchTerm = "%{$query}%";

    try {
        // Search posts
        if ($type === 'all' || $type === 'posts') {
            $stmt = db()->prepare("SELECT id, title_en, title_ar, slug, excerpt_en, excerpt_ar, 'post' as type 
                                   FROM posts 
                                   WHERE status = 'published' AND (title_en LIKE ? OR title_ar LIKE ? OR content_en LIKE ? OR content_ar LIKE ?)
                                   ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results['posts'] = $stmt->fetchAll();
        }

        // Search projects
        if ($type === 'all' || $type === 'projects') {
            $stmt = db()->prepare("SELECT id, title_en, title_ar, slug, description_en, description_ar, 'project' as type 
                                   FROM projects 
                                   WHERE status = 'published' AND (title_en LIKE ? OR title_ar LIKE ? OR description_en LIKE ? OR description_ar LIKE ?)
                                   ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results['projects'] = $stmt->fetchAll();
        }

        // Search services
        if ($type === 'all' || $type === 'services') {
            $stmt = db()->prepare("SELECT id, title_en, title_ar, slug, description_en, description_ar, icon, 'service' as type 
                                   FROM services 
                                   WHERE is_active = 1 AND (title_en LIKE ? OR title_ar LIKE ? OR description_en LIKE ? OR description_ar LIKE ?)
                                   ORDER BY sort_order LIMIT 10");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results['services'] = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        // Handle error silently
    }
}

$totalResults = count($results['posts']) + count($results['projects']) + count($results['services']);

$seoOptions = [
    'title' => ($lang === 'ar' ? 'نتائج البحث: ' : 'Search Results: ') . $query,
    'robots' => 'noindex, nofollow'
];

include 'includes/header.php';
?>

<section class="hero" style="min-height:30vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:3rem;">
            <h1 class="hero-title"><?php echo $lang === 'ar' ? 'نتائج البحث' : 'Search Results'; ?></h1>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Search Form -->
        <div class="card" style="padding:1.5rem;margin-bottom:2rem;max-width:700px;margin-left:auto;margin-right:auto;">
            <form method="GET" style="display:flex;gap:0.5rem;">
                <input type="text" name="q" value="<?php echo e($query); ?>" class="form-control"
                    placeholder="<?php echo $lang === 'ar' ? 'ابحث...' : 'Search...'; ?>" style="flex:1;" required
                    minlength="2">
                <select name="type" class="form-control" style="width:auto;">
                    <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>
                        <?php echo $lang === 'ar' ? 'الكل' : 'All'; ?></option>
                    <option value="posts" <?php echo $type === 'posts' ? 'selected' : ''; ?>><?php echo __('blog'); ?>
                    </option>
                    <option value="projects" <?php echo $type === 'projects' ? 'selected' : ''; ?>>
                        <?php echo __('projects'); ?></option>
                    <option value="services" <?php echo $type === 'services' ? 'selected' : ''; ?>>
                        <?php echo __('services'); ?></option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <?php if (!empty($query)): ?>

            <!-- Results Summary -->
            <div class="text-center" style="margin-bottom:2rem;">
                <p style="color:var(--gray-600);">
                    <?php if ($totalResults > 0): ?>
                        <?php echo $lang === 'ar'
                            ? "تم العثور على {$totalResults} نتيجة لـ \"{$query}\""
                            : "Found {$totalResults} results for \"{$query}\""; ?>
                    <?php else: ?>
                        <?php echo $lang === 'ar'
                            ? "لم يتم العثور على نتائج لـ \"{$query}\""
                            : "No results found for \"{$query}\""; ?>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($totalResults > 0): ?>

                <!-- Posts Results -->
                <?php if (!empty($results['posts'])): ?>
                    <div style="margin-bottom:3rem;">
                        <h2 style="margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;">
                            <i class="fas fa-newspaper" style="color:var(--primary);"></i>
                            <?php echo __('blog'); ?>
                            <span class="badge badge-primary"><?php echo count($results['posts']); ?></span>
                        </h2>
                        <div class="grid"
                            style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
                            <?php foreach ($results['posts'] as $post): ?>
                                <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>" class="card"
                                    style="padding:1.5rem;display:block;color:inherit;">
                                    <h3 style="margin-bottom:0.5rem;font-size:1.1rem;"><?php echo e(trans($post, 'title')); ?></h3>
                                    <p style="color:var(--gray-600);font-size:0.9rem;margin:0;">
                                        <?php echo e(truncate(trans($post, 'excerpt') ?: trans($post, 'content'), 100)); ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Projects Results -->
                <?php if (!empty($results['projects'])): ?>
                    <div style="margin-bottom:3rem;">
                        <h2 style="margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;">
                            <i class="fas fa-briefcase" style="color:var(--primary);"></i>
                            <?php echo __('projects'); ?>
                            <span class="badge badge-primary"><?php echo count($results['projects']); ?></span>
                        </h2>
                        <div class="grid"
                            style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
                            <?php foreach ($results['projects'] as $project): ?>
                                <a href="<?php echo SITE_URL; ?>/project.php?slug=<?php echo e($project['slug']); ?>" class="card"
                                    style="padding:1.5rem;display:block;color:inherit;">
                                    <h3 style="margin-bottom:0.5rem;font-size:1.1rem;"><?php echo e(trans($project, 'title')); ?></h3>
                                    <p style="color:var(--gray-600);font-size:0.9rem;margin:0;">
                                        <?php echo e(truncate(trans($project, 'description'), 100)); ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Services Results -->
                <?php if (!empty($results['services'])): ?>
                    <div style="margin-bottom:3rem;">
                        <h2 style="margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;">
                            <i class="fas fa-cogs" style="color:var(--primary);"></i>
                            <?php echo __('services'); ?>
                            <span class="badge badge-primary"><?php echo count($results['services']); ?></span>
                        </h2>
                        <div class="grid"
                            style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
                            <?php foreach ($results['services'] as $service): ?>
                                <a href="<?php echo SITE_URL; ?>/service.php?slug=<?php echo e($service['slug']); ?>" class="card"
                                    style="padding:1.5rem;display:block;color:inherit;">
                                    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.75rem;">
                                        <i class="<?php echo e($service['icon'] ?: 'fas fa-cog'); ?>"
                                            style="font-size:1.5rem;color:var(--primary);"></i>
                                        <h3 style="margin:0;font-size:1.1rem;"><?php echo e(trans($service, 'title')); ?></h3>
                                    </div>
                                    <p style="color:var(--gray-600);font-size:0.9rem;margin:0;">
                                        <?php echo e(truncate(trans($service, 'description'), 100)); ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- No Results -->
                <div class="text-center" style="padding:4rem 0;">
                    <i class="fas fa-search" style="font-size:4rem;color:var(--gray-300);margin-bottom:1.5rem;"></i>
                    <h3><?php echo $lang === 'ar' ? 'لا توجد نتائج' : 'No Results Found'; ?></h3>
                    <p style="color:var(--gray-600);max-width:400px;margin:0 auto 2rem;">
                        <?php echo $lang === 'ar'
                            ? 'حاول استخدام كلمات مختلفة أو تصفح أقسامنا.'
                            : 'Try different keywords or browse our sections.'; ?>
                    </p>
                    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                        <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-secondary"><?php echo __('blog'); ?></a>
                        <a href="<?php echo SITE_URL; ?>/projects.php"
                            class="btn btn-secondary"><?php echo __('projects'); ?></a>
                        <a href="<?php echo SITE_URL; ?>/services.php"
                            class="btn btn-secondary"><?php echo __('services'); ?></a>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>