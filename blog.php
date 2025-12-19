<?php
/**
 * Ahmed Ashraf Portfolio - Blog Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 9;

// Filters
$categorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$tagSlug = isset($_GET['tag']) ? sanitize($_GET['tag']) : null;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : null;

// Build query
$where = ["p.status = 'published'"];
$params = [];

if ($categorySlug) {
    $where[] = "c.slug = ?";
    $params[] = $categorySlug;
}

if ($tagSlug) {
    $where[] = "EXISTS (SELECT 1 FROM post_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.post_id = p.id AND t.slug = ?)";
    $params[] = $tagSlug;
}

if ($search) {
    $where[] = "(p.title_en LIKE ? OR p.title_ar LIKE ? OR p.content_en LIKE ? OR p.content_ar LIKE ?)";
    $searchParam = "%{$search}%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

$whereClause = implode(' AND ', $where);

// Get total count
try {
    $countSql = "SELECT COUNT(DISTINCT p.id) FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE {$whereClause}";
    $stmt = db()->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total = 0;
}

$pagination = paginate($total, $perPage, $page);

// Get posts
try {
    $sql = "SELECT p.*, c.name_en as category_name_en, c.name_ar as category_name_ar, c.slug as category_slug,
                   a.full_name as author_name
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN admins a ON p.author_id = a.id
            WHERE {$whereClause} 
            ORDER BY p.created_at DESC 
            LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}

// Get categories for sidebar
try {
    $stmt = db()->query("SELECT c.*, COUNT(p.id) as post_count 
                         FROM categories c 
                         LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
                         GROUP BY c.id 
                         HAVING post_count > 0
                         ORDER BY c.name_en");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get popular tags
try {
    $stmt = db()->query("SELECT t.*, COUNT(pt.post_id) as usage_count 
                         FROM tags t 
                         JOIN post_tags pt ON t.id = pt.tag_id 
                         GROUP BY t.id 
                         ORDER BY usage_count DESC 
                         LIMIT 15");
    $tags = $stmt->fetchAll();
} catch (PDOException $e) {
    $tags = [];
}

// SEO Options
$pageTitle = __('blog');
if ($categorySlug && !empty($categories)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $pageTitle = trans($cat, 'name');
            break;
        }
    }
}
if ($search) {
    $pageTitle = ($lang === 'ar' ? 'بحث: ' : 'Search: ') . $search;
}

$seoOptions = [
    'title' => $pageTitle,
    'description' => $lang === 'ar'
        ? 'مقالات ونصائح في التسويق الرقمي والتكنولوجيا'
        : 'Articles and tips on digital marketing and technology',
];

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="min-height:50vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo e($pageTitle); ?></h1>
            <p class="hero-subtitle" style="max-width:600px;margin:0 auto;">
                <?php echo $lang === 'ar'
                    ? 'مقالات ونصائح في التسويق والتقنية'
                    : 'Articles and tips on marketing and technology'; ?>
            </p>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container" style="padding:1rem 0;">
    <?php
    $breadcrumbItems = [['title' => __('blog'), 'url' => SITE_URL . '/blog.php']];
    if ($categorySlug) {
        $breadcrumbItems[] = ['title' => $pageTitle, 'url' => ''];
    }
    echo breadcrumbs($breadcrumbItems);
    ?>
</div>

<!-- Blog Content -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:3rem;">

            <!-- Posts Grid -->
            <div>
                <!-- Search Bar (Mobile) -->
                <form action="" method="GET" class="mb-6" style="display:none;" id="mobile-search">
                    <div style="display:flex;gap:0.5rem;">
                        <input type="text" name="q" value="<?php echo e($search ?? ''); ?>"
                            placeholder="<?php echo __('search_placeholder'); ?>" class="form-control" style="flex:1;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <?php if (empty($posts)): ?>
                    <div class="text-center" style="padding:4rem 2rem;">
                        <i class="fas fa-newspaper" style="font-size:4rem;color:var(--gray-300);margin-bottom:1rem;"></i>
                        <h3><?php echo __('no_results'); ?></h3>
                        <p style="color:var(--gray-500);">
                            <?php echo $lang === 'ar'
                                ? 'لا توجد مقالات مطابقة لبحثك'
                                : 'No articles match your search'; ?>
                        </p>
                        <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-primary mt-4">
                            <?php echo __('all_posts'); ?>
                        </a>
                    </div>

                <?php else: ?>
                    <div class="grid grid-2" style="gap:2rem;">
                        <?php foreach ($posts as $index => $post): ?>
                            <article class="card" data-aos="fade-up" data-aos-delay="<?php echo ($index % 2) * 100; ?>">
                                <div class="card-image">
                                    <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>">
                                        <img src="<?php echo $post['featured_image']
                                            ? UPLOADS_URL . '/blog/' . e($post['featured_image'])
                                            : ASSETS_URL . '/images/blog-placeholder.jpg'; ?>"
                                            alt="<?php echo e(trans($post, 'title')); ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="card-meta" style="margin-bottom:0.75rem;">
                                        <?php if (!empty($post['category_name_' . $lang])): ?>
                                            <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo e($post['category_slug']); ?>"
                                                class="tag">
                                                <?php echo e($post['category_name_' . $lang]); ?>
                                            </a>
                                        <?php endif; ?>
                                        <span style="color:var(--gray-500);font-size:0.85rem;">
                                            <i class="far fa-calendar"></i>
                                            <?php echo formatDate($post['created_at']); ?>
                                        </span>
                                    </div>

                                    <h3 class="card-title" style="font-size:1.25rem;">
                                        <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>"
                                            style="color:inherit;">
                                            <?php echo e(trans($post, 'title')); ?>
                                        </a>
                                    </h3>

                                    <p class="card-text">
                                        <?php echo e(truncate(trans($post, 'excerpt') ?: strip_tags(trans($post, 'content')), 100)); ?>
                                    </p>

                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
                                        <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>"
                                            class="btn btn-ghost" style="padding:0;">
                                            <?php echo __('read_more'); ?>
                                            <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                                        </a>

                                        <?php if ($post['views'] > 0): ?>
                                            <span style="color:var(--gray-400);font-size:0.85rem;">
                                                <i class="far fa-eye"></i> <?php echo number_format($post['views']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php
                    $baseUrl = SITE_URL . '/blog.php?';
                    if ($categorySlug)
                        $baseUrl .= 'category=' . urlencode($categorySlug) . '&';
                    if ($tagSlug)
                        $baseUrl .= 'tag=' . urlencode($tagSlug) . '&';
                    if ($search)
                        $baseUrl .= 'q=' . urlencode($search) . '&';
                    echo paginationHTML($pagination, $baseUrl);
                    ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside style="position:sticky;top:100px;align-self:start;">
                <!-- Search -->
                <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
                    <h4 style="margin-bottom:1rem;"><?php echo __('search'); ?></h4>
                    <form action="" method="GET">
                        <div style="display:flex;gap:0.5rem;">
                            <input type="text" name="q" value="<?php echo e($search ?? ''); ?>"
                                placeholder="<?php echo __('search_placeholder'); ?>" class="form-control"
                                style="flex:1;">
                            <button type="submit" class="btn btn-primary btn-icon">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Categories -->
                <?php if (!empty($categories)): ?>
                    <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
                        <h4 style="margin-bottom:1rem;"><?php echo __('categories'); ?></h4>
                        <ul style="list-style:none;">
                            <?php foreach ($categories as $cat): ?>
                                <li style="margin-bottom:0.5rem;">
                                    <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo e($cat['slug']); ?>"
                                        style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;color:var(--gray-700);border-bottom:1px solid var(--gray-100);<?php echo $categorySlug === $cat['slug'] ? 'color:var(--primary);font-weight:600;' : ''; ?>">
                                        <span><?php echo e(trans($cat, 'name')); ?></span>
                                        <span class="tag" style="font-size:0.75rem;"><?php echo $cat['post_count']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                    <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
                        <h4 style="margin-bottom:1rem;"><?php echo __('tags'); ?></h4>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                            <?php foreach ($tags as $tag): ?>
                                <a href="<?php echo SITE_URL; ?>/blog.php?tag=<?php echo e($tag['slug']); ?>" class="tag"
                                    style="<?php echo $tagSlug === $tag['slug'] ? 'background:var(--primary);color:#fff;' : ''; ?>">
                                    <?php echo e(trans($tag, 'name')); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Newsletter -->
                <div class="card" style="padding:1.5rem;background:var(--gradient-primary);color:#fff;">
                    <h4 style="color:#fff;margin-bottom:0.5rem;"><?php echo __('newsletter'); ?></h4>
                    <p style="color:rgba(255,255,255,0.8);font-size:0.9rem;margin-bottom:1rem;">
                        <?php echo $lang === 'ar'
                            ? 'اشترك للحصول على أحدث المقالات'
                            : 'Subscribe to get the latest articles'; ?>
                    </p>
                    <form action="<?php echo SITE_URL; ?>/ajax/newsletter.php" method="POST" id="newsletter-form">
                        <input type="email" name="email" required
                            placeholder="<?php echo __('newsletter_placeholder'); ?>" class="form-control"
                            style="margin-bottom:0.75rem;">
                        <button type="submit" class="btn btn-secondary w-full">
                            <?php echo __('subscribe'); ?>
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
    @media (max-width: 1024px) {
        .section>.container>div {
            grid-template-columns: 1fr !important;
        }

        #mobile-search {
            display: block !important;
        }

        aside {
            position: static !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>