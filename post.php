<?php
/**
 * Ahmed Ashraf Portfolio - Single Post Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : null;

if (!$slug) {
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

// Get post
try {
    $stmt = db()->prepare("SELECT p.*, c.name_en as category_name_en, c.name_ar as category_name_ar, 
                                  c.slug as category_slug, a.full_name as author_name, a.avatar as author_avatar
                           FROM posts p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           LEFT JOIN admins a ON p.author_id = a.id
                           WHERE p.slug = ? AND p.status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} catch (PDOException $e) {
    $post = null;
}

if (!$post) {
    http_response_code(404);
    include 'error.php';
    exit;
}

// Increment views
try {
    $stmt = db()->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$post['id']]);
} catch (PDOException $e) {
}

// Get post tags
try {
    $stmt = db()->prepare("SELECT t.* FROM tags t 
                           JOIN post_tags pt ON t.id = pt.tag_id 
                           WHERE pt.post_id = ?");
    $stmt->execute([$post['id']]);
    $postTags = $stmt->fetchAll();
} catch (PDOException $e) {
    $postTags = [];
}

// Get related posts
try {
    $stmt = db()->prepare("SELECT * FROM posts 
                           WHERE status = 'published' AND id != ? AND category_id = ?
                           ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$post['id'], $post['category_id']]);
    $relatedPosts = $stmt->fetchAll();
} catch (PDOException $e) {
    $relatedPosts = [];
}

// Get comments
try {
    $stmt = db()->prepare("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $stmt->execute([$post['id']]);
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    $comments = [];
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    if (verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $commentName = sanitize($_POST['comment_name'] ?? '');
        $commentEmail = sanitize($_POST['comment_email'] ?? '');
        $commentContent = sanitize($_POST['comment_content'] ?? '');

        if ($commentName && $commentEmail && $commentContent && isValidEmail($commentEmail)) {
            try {
                $stmt = db()->prepare("INSERT INTO comments (post_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$post['id'], $commentName, $commentEmail, $commentContent]);
                setFlash('success', $lang === 'ar' ? 'تم إرسال تعليقك وسيظهر بعد المراجعة' : 'Your comment has been submitted and will appear after review');
                redirect(SITE_URL . '/post.php?slug=' . $slug . '#comments');
            } catch (PDOException $e) {
                setFlash('error', __('system_error'));
            }
        } else {
            setFlash('error', __('form_error'));
        }
    }
}

// SEO
$seoOptions = [
    'title' => trans($post, 'title'),
    'description' => trans($post, 'excerpt') ?: truncate(strip_tags(trans($post, 'content')), 160),
    'image' => $post['featured_image'] ? UPLOADS_URL . '/blog/' . $post['featured_image'] : null,
    'type' => 'article',
];

$schemaType = 'Article';
$schemaData = [
    'title' => trans($post, 'title'),
    'description' => trans($post, 'excerpt') ?: truncate(strip_tags(trans($post, 'content')), 160),
    'image' => $post['featured_image'] ? UPLOADS_URL . '/blog/' . $post['featured_image'] : null,
    'published' => $post['created_at'],
    'modified' => $post['updated_at'],
    'url' => SITE_URL . '/post.php?slug=' . $post['slug'],
];

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <?php if (!empty($post['category_name_' . $lang])): ?>
                <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo e($post['category_slug']); ?>" class="tag"
                    style="background:rgba(255,255,255,0.2);color:#fff;margin-bottom:1rem;display:inline-block;">
                    <?php echo e($post['category_name_' . $lang]); ?>
                </a>
            <?php endif; ?>
            <h1 class="hero-title" style="font-size:2.5rem;"><?php echo e(trans($post, 'title')); ?></h1>
            <div style="display:flex;justify-content:center;gap:2rem;margin-top:1.5rem;color:rgba(255,255,255,0.8);">
                <span><i class="far fa-calendar"></i> <?php echo formatDate($post['created_at']); ?></span>
                <span><i class="far fa-user"></i> <?php echo e($post['author_name'] ?: 'Ahmed Ashraf'); ?></span>
                <span><i class="far fa-eye"></i> <?php echo number_format($post['views']); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([
        ['title' => __('blog'), 'url' => SITE_URL . '/blog.php'],
        ['title' => trans($post, 'title'), 'url' => '']
    ]); ?>
</div>

<!-- Post Content -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:3rem;">

            <!-- Main Content -->
            <article>
                <!-- Featured Image -->
                <?php if ($post['featured_image']): ?>
                    <div style="margin-bottom:2rem;border-radius:1rem;overflow:hidden;">
                        <img src="<?php echo UPLOADS_URL; ?>/blog/<?php echo e($post['featured_image']); ?>"
                            alt="<?php echo e(trans($post, 'title')); ?>" style="width:100%;height:auto;">
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <div class="post-content" style="font-size:1.1rem;line-height:1.8;">
                    <?php echo trans($post, 'content'); ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($postTags)): ?>
                    <div style="margin-top:2rem;padding-top:2rem;border-top:1px solid var(--gray-200);">
                        <strong
                            style="margin-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>:1rem;"><?php echo __('tags'); ?>:</strong>
                        <?php foreach ($postTags as $tag): ?>
                            <a href="<?php echo SITE_URL; ?>/blog.php?tag=<?php echo e($tag['slug']); ?>" class="tag">
                                <?php echo e(trans($tag, 'name')); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Share -->
                <div style="margin-top:2rem;padding:1.5rem;background:var(--gray-50);border-radius:1rem;">
                    <strong
                        style="margin-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>:1rem;"><?php echo __('share_post'); ?>:</strong>
                    <div style="display:inline-flex;gap:0.5rem;">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentUrl()); ?>"
                            target="_blank" class="btn btn-icon" style="background:#1877f2;color:#fff;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getCurrentUrl()); ?>&text=<?php echo urlencode(trans($post, 'title')); ?>"
                            target="_blank" class="btn btn-icon" style="background:#1da1f2;color:#fff;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(getCurrentUrl()); ?>&title=<?php echo urlencode(trans($post, 'title')); ?>"
                            target="_blank" class="btn btn-icon" style="background:#0077b5;color:#fff;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode(trans($post, 'title') . ' ' . getCurrentUrl()); ?>"
                            target="_blank" class="btn btn-icon" style="background:#25d366;color:#fff;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <button
                            onclick="navigator.clipboard.writeText('<?php echo getCurrentUrl(); ?>');showToast('<?php echo $lang === 'ar' ? 'تم نسخ الرابط' : 'Link copied'; ?>', 'success');"
                            class="btn btn-icon" style="background:var(--gray-600);color:#fff;">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>

                <!-- Comments Section -->
                <div id="comments" style="margin-top:3rem;">
                    <h3><?php echo __('comments'); ?> (<?php echo count($comments); ?>)</h3>

                    <?php echo displayFlashMessages(); ?>

                    <?php if (empty($comments)): ?>
                        <p style="color:var(--gray-500);padding:2rem 0;"><?php echo __('no_comments'); ?></p>
                    <?php else: ?>
                        <div style="margin:2rem 0;">
                            <?php foreach ($comments as $comment): ?>
                                <div style="padding:1.5rem;background:var(--gray-50);border-radius:1rem;margin-bottom:1rem;">
                                    <div style="display:flex;justify-content:space-between;margin-bottom:1rem;">
                                        <strong><?php echo e($comment['author_name']); ?></strong>
                                        <span style="color:var(--gray-500);font-size:0.9rem;">
                                            <?php echo timeAgo($comment['created_at']); ?>
                                        </span>
                                    </div>
                                    <p style="margin:0;"><?php echo nl2br(e($comment['content'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Comment Form -->
                    <div style="margin-top:2rem;">
                        <h4><?php echo __('leave_comment'); ?></h4>
                        <form method="POST" action="">
                            <?php echo csrfField(); ?>
                            <div class="grid grid-2" style="gap:1rem;">
                                <div class="form-group">
                                    <label class="form-label"><?php echo __('your_name'); ?> *</label>
                                    <input type="text" name="comment_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label"><?php echo __('your_email'); ?> *</label>
                                    <input type="email" name="comment_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?php echo __('message'); ?> *</label>
                                <textarea name="comment_content" class="form-control" rows="4" required></textarea>
                            </div>
                            <button type="submit" name="comment_submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                <?php echo __('post_comment'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </article>

            <!-- Sidebar -->
            <aside style="position:sticky;top:100px;align-self:start;">
                <!-- Author -->
                <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;text-align:center;">
                    <img src="<?php echo $post['author_avatar']
                        ? UPLOADS_URL . '/avatars/' . e($post['author_avatar'])
                        : 'https://ui-avatars.com/api/?name=' . urlencode($post['author_name'] ?: 'Ahmed Ashraf') . '&background=0066cc&color=fff'; ?>"
                        alt="<?php echo e($post['author_name']); ?>"
                        style="width:80px;height:80px;border-radius:50%;margin:0 auto 1rem;">
                    <h4 style="margin-bottom:0.5rem;"><?php echo e($post['author_name'] ?: 'Ahmed Ashraf'); ?></h4>
                    <p style="color:var(--gray-500);margin:0;">
                        <?php echo $lang === 'ar' ? 'كاتب المقال' : 'Author'; ?>
                    </p>
                </div>

                <!-- Related Posts -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="card" style="padding:1.5rem;">
                        <h4 style="margin-bottom:1rem;"><?php echo __('related_posts'); ?></h4>
                        <?php foreach ($relatedPosts as $related): ?>
                            <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($related['slug']); ?>"
                                style="display:flex;gap:1rem;margin-bottom:1rem;color:inherit;">
                                <img src="<?php echo $related['featured_image']
                                    ? UPLOADS_URL . '/blog/' . e($related['featured_image'])
                                    : ASSETS_URL . '/images/blog-placeholder.jpg'; ?>" alt=""
                                    style="width:80px;height:60px;object-fit:cover;border-radius:0.5rem;">
                                <div>
                                    <h5 style="font-size:0.95rem;margin-bottom:0.25rem;line-height:1.3;">
                                        <?php echo e(truncate(trans($related, 'title'), 50)); ?>
                                    </h5>
                                    <span style="font-size:0.8rem;color:var(--gray-500);">
                                        <?php echo formatDate($related['created_at']); ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<style>
    .post-content h2,
    .post-content h3,
    .post-content h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .post-content p {
        margin-bottom: 1.5rem;
    }

    .post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1.5rem 0;
    }

    .post-content ul,
    .post-content ol {
        margin: 1.5rem 0;
        padding-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>: 2rem;
    }

    .post-content li {
        margin-bottom: 0.5rem;
    }

    .post-content blockquote {
        border-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>: 4px solid var(--primary);
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        background: var(--gray-50);
        border-radius: 0.5rem;
        font-style: italic;
    }

    .post-content pre {
        background: var(--gray-900);
        color: var(--gray-100);
        padding: 1.5rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1.5rem 0;
    }

    .post-content code {
        background: var(--gray-100);
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.9em;
    }

    .post-content pre code {
        background: transparent;
        padding: 0;
    }

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