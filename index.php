<?php
/**
 * Ahmed Ashraf Portfolio - Homepage
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

// SEO Options
$seoOptions = [
    'title' => getSetting('site_name_' . $lang, 'Ahmed Ashraf'),
    'description' => getSetting('site_description_' . $lang, 'Marketing Professional & IT Expert'),
];

// Get featured services
try {
    $stmt = db()->query("SELECT * FROM services WHERE status = 'active' AND featured = 1 ORDER BY sort_order LIMIT 6");
    $featuredServices = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredServices = [];
}

// Get featured projects
try {
    $stmt = db()->query("SELECT * FROM projects WHERE status = 'published' AND featured = 1 ORDER BY sort_order LIMIT 3");
    $featuredProjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredProjects = [];
}

// Get latest posts
try {
    $stmt = db()->query("SELECT p.*, c.name_en as category_name_en, c.name_ar as category_name_ar 
                         FROM posts p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.status = 'published' 
                         ORDER BY p.created_at DESC LIMIT 3");
    $latestPosts = $stmt->fetchAll();
} catch (PDOException $e) {
    $latestPosts = [];
}

$schemaType = 'Person';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-shapes">
        <div class="hero-shape"></div>
        <div class="hero-shape"></div>
        <div class="hero-shape"></div>
    </div>

    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-rocket"></i>
                    <span><?php echo $lang === 'ar' ? 'متاح للعمل' : 'Available for Work'; ?></span>
                </div>

                <h1 class="hero-title">
                    <?php echo __('hero_title'); ?>
                </h1>

                <p class="hero-subtitle">
                    <?php echo __('hero_subtitle'); ?>
                </p>

                <p style="color:rgba(255,255,255,0.8);margin-bottom:2rem;">
                    <?php echo __('hero_description'); ?>
                </p>

                <div class="hero-buttons">
                    <a href="#contact" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo __('get_started'); ?>
                    </a>
                    <a href="#projects" class="btn btn-outline btn-lg">
                        <i class="fas fa-eye"></i>
                        <?php echo __('view_work'); ?>
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number" data-count="6">0</span>
                        <span class="hero-stat-label"><?php echo __('years_experience'); ?></span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number" data-count="50">0</span>
                        <span class="hero-stat-label"><?php echo __('projects_completed'); ?></span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number" data-count="100">0</span>
                        <span class="hero-stat-label"><?php echo __('happy_clients'); ?></span>
                    </div>
                </div>
            </div>

            <div class="hero-image">
                <div class="hero-image-wrapper">
                    <img src="<?php echo getUploadedUrl(getSetting('profile_picture'), 'profile', 'profile-placeholder.jpg'); ?>"
                        alt="Ahmed Ashraf"
                        onerror="this.src='https://ui-avatars.com/api/?name=Ahmed+Ashraf&size=400&background=0066cc&color=fff&bold=true'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section bg-gray-50" id="services">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo __('our_services'); ?></h2>
            <p><?php echo $lang === 'ar'
                ? 'أقدم مجموعة متنوعة من خدمات التسويق والتقنية لمساعدة عملك على النمو'
                : 'I offer a diverse range of marketing and technical services to help your business grow'; ?>
            </p>
        </div>

        <div class="grid grid-3">
            <?php foreach ($featuredServices as $index => $service): ?>
                <div class="card service-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="service-icon">
                        <i class="<?php echo e($service['icon'] ?: 'fas fa-cog'); ?>"></i>
                    </div>
                    <h3 class="card-title"><?php echo e(trans($service, 'title')); ?></h3>
                    <p class="card-text"><?php echo e(trans($service, 'description')); ?></p>
                    <a href="<?php echo SITE_URL; ?>/services.php#<?php echo e($service['slug']); ?>" class="btn btn-ghost">
                        <?php echo __('learn_more'); ?>
                        <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8" data-aos="fade-up">
            <a href="<?php echo SITE_URL; ?>/services.php" class="btn btn-secondary">
                <?php echo __('view_all_services'); ?>
                <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
            </a>
        </div>
    </div>
</section>

<!-- Projects Section -->
<section class="section" id="projects">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo __('featured_projects'); ?></h2>
            <p><?php echo $lang === 'ar'
                ? 'مشاريع وتطبيقات ويب قمت بتطويرها'
                : 'Web applications and projects I have developed'; ?>
            </p>
        </div>

        <div class="grid grid-3">
            <?php foreach ($featuredProjects as $index => $project): ?>
                <div class="card project-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card-image">
                        <img src="<?php
                        $img = $project['featured_image'];
                        $imgUrl = ASSETS_URL . '/images/project-placeholder.jpg';

                        if (!empty($img)) {
                            if (filter_var($img, FILTER_VALIDATE_URL)) {
                                $imgUrl = $img;
                            } elseif (file_exists(UPLOADS_PATH . 'projects/' . $img)) {
                                $imgUrl = UPLOADS_URL . '/projects/' . e($img);
                            } elseif (file_exists(UPLOADS_PATH . 'media/' . $img)) {
                                $imgUrl = UPLOADS_URL . '/media/' . e($img);
                            }
                        }
                        echo $imgUrl;

                        ?>" alt="<?php echo e(trans($project, 'title')); ?>"
                            style="object-fit:cover;height:250px;width:100%;">
                        <div class="card-overlay">
                            <?php if ($project['demo_url']): ?>
                                <a href="<?php echo e($project['demo_url']); ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    <?php echo __('live_demo'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="project-tags">
                            <?php
                            $techs = explode(',', $project['technologies'] ?? '');
                            foreach (array_slice($techs, 0, 3) as $tech): ?>
                                <span class="tag"><?php echo e(trim($tech)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <h3 class="card-title"><?php echo e(trans($project, 'title')); ?></h3>
                        <p class="card-text"><?php echo e(truncate(trans($project, 'description'), 100)); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8" data-aos="fade-up">
            <a href="<?php echo SITE_URL; ?>/projects.php" class="btn btn-primary">
                <?php echo __('all_projects'); ?>
                <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
            </a>
        </div>
    </div>
</section>

<!-- About Preview Section -->
<section class="section bg-gradient" style="color:#fff;" id="about-preview">
    <div class="container">
        <div class="grid grid-2" style="gap:4rem;align-items:center;">
            <div data-aos="fade-right">
                <h2 style="color:#fff;"><?php echo __('about_me'); ?></h2>
                <div style="color:rgba(255,255,255,0.9);font-size:1.1rem;margin-bottom:1.5rem;">
                    <?php
                    $aboutContent = getSetting('about_content_' . $lang);
                    if (!$aboutContent) {
                        $aboutContent = $lang === 'ar'
                            ? 'أنا أحمد أشرف، محترف تسويق بخلفية تقنية قوية...'
                            : 'I am Ahmed Ashraf, a marketing professional...';
                    }
                    echo nl2br($aboutContent);
                    ?>
                </div>
                <ul style="list-style:none;margin-bottom:2rem;">
                    <li
                        style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;color:rgba(255,255,255,0.9);">
                        <i class="fas fa-check-circle" style="color:#00bcd4;"></i>
                        <?php echo $lang === 'ar' ? '6+ سنوات خبرة في التقنية والتسويق' : '6+ years of experience in IT & Marketing'; ?>
                    </li>
                    <li
                        style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;color:rgba(255,255,255,0.9);">
                        <i class="fas fa-check-circle" style="color:#00bcd4;"></i>
                        <?php echo $lang === 'ar' ? 'خبرة في إدارة الفرق والمشاريع' : 'Experience in team and project management'; ?>
                    </li>
                    <li
                        style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;color:rgba(255,255,255,0.9);">
                        <i class="fas fa-check-circle" style="color:#00bcd4;"></i>
                        <?php echo $lang === 'ar' ? 'شهادات معتمدة في التسويق والتقنية' : 'Certified in marketing and technology'; ?>
                    </li>
                </ul>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="<?php echo SITE_URL; ?>/about.php" class="btn btn-outline btn-lg">
                        <?php echo __('learn_more'); ?>
                    </a>
                    <a href="<?php echo getUploadedUrl(getSetting('cv_file'), 'cv', '#'); ?>"
                        class="btn btn-primary btn-lg" download>
                        <i class="fas fa-download"></i>
                        <?php echo __('download_cv'); ?>
                    </a>
                </div>
            </div>
            <div data-aos="fade-left" style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
                <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;text-align:center;">
                    <div style="font-size:3rem;font-weight:800;margin-bottom:0.5rem;" data-count="6">0</div>
                    <div style="color:rgba(255,255,255,0.8);"><?php echo __('years_experience'); ?></div>
                </div>
                <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;text-align:center;">
                    <div style="font-size:3rem;font-weight:800;margin-bottom:0.5rem;" data-count="50">0</div>
                    <div style="color:rgba(255,255,255,0.8);"><?php echo __('projects_completed'); ?></div>
                </div>
                <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;text-align:center;">
                    <div style="font-size:3rem;font-weight:800;margin-bottom:0.5rem;" data-count="100">0</div>
                    <div style="color:rgba(255,255,255,0.8);"><?php echo __('happy_clients'); ?></div>
                </div>
                <div style="background:rgba(255,255,255,0.1);padding:2rem;border-radius:1rem;text-align:center;">
                    <div style="font-size:3rem;font-weight:800;margin-bottom:0.5rem;" data-count="8">0</div>
                    <div style="color:rgba(255,255,255,0.8);"><?php echo __('certificates'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<?php if (!empty($latestPosts)): ?>
    <section class="section" id="blog">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2><?php echo __('latest_posts'); ?></h2>
                <p><?php echo $lang === 'ar'
                    ? 'أحدث المقالات والنصائح في التسويق والتقنية'
                    : 'Latest articles and tips on marketing and technology'; ?>
                </p>
            </div>

            <div class="grid grid-3">
                <?php foreach ($latestPosts as $index => $post): ?>
                    <article class="card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card-image">
                            <img src="<?php
                            $img = $post['featured_image'];
                            if (filter_var($img, FILTER_VALIDATE_URL)) {
                                echo e($img);
                            } elseif ($img) {
                                echo UPLOADS_URL . '/blog/' . e($img);
                            } else {
                                echo ASSETS_URL . '/images/blog-placeholder.jpg';
                            }
                            ?>" alt="<?php echo e(trans($post, 'title')); ?>">
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <?php if (!empty($post['category_name_' . $lang])): ?>
                                    <span class="tag"><?php echo e($post['category_name_' . $lang]); ?></span>
                                <?php endif; ?>
                                <span><i class="far fa-calendar"></i> <?php echo formatDate($post['created_at']); ?></span>
                            </div>
                            <h3 class="card-title" style="margin-top:1rem;">
                                <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>">
                                    <?php echo e(trans($post, 'title')); ?>
                                </a>
                            </h3>
                            <p class="card-text">
                                <?php echo e(truncate(trans($post, 'excerpt') ?: strip_tags(trans($post, 'content')), 120)); ?>
                            </p>
                            <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($post['slug']); ?>"
                                class="btn btn-ghost">
                                <?php echo __('read_more'); ?>
                                <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-8" data-aos="fade-up">
                <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-secondary">
                    <?php echo __('all_posts'); ?>
                    <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>"></i>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Contact Section -->
<section class="section bg-gray-50" id="contact">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo __('get_in_touch'); ?></h2>
            <p><?php echo $lang === 'ar'
                ? 'لديك مشروع أو استفسار؟ تواصل معي وسأرد عليك في أقرب وقت'
                : 'Have a project or inquiry? Contact me and I\'ll get back to you soon'; ?>
            </p>
        </div>

        <div class="grid grid-2" style="gap:4rem;">
            <!-- Contact Form -->
            <div data-aos="fade-right">
                <form id="contact-form" action="<?php echo SITE_URL; ?>/ajax/contact.php" method="POST" data-validate>
                    <?php echo csrfField(); ?>

                    <div class="grid grid-2" style="gap:1.5rem;">
                        <div class="form-group">
                            <label class="form-label" for="name"><?php echo __('your_name'); ?> *</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                data-error-required="<?php echo __('required_field'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email"><?php echo __('your_email'); ?> *</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                data-error-required="<?php echo __('required_field'); ?>"
                                data-error-email="<?php echo __('invalid_email'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone"><?php echo __('your_phone'); ?></label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject"><?php echo __('subject'); ?></label>
                        <input type="text" id="subject" name="subject" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message"><?php echo __('message'); ?> *</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required
                            data-error-required="<?php echo __('required_field'); ?>"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo __('send_message'); ?>
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div data-aos="fade-left">
                <div
                    style="background:#fff;padding:2rem;border-radius:1rem;box-shadow:var(--shadow-md);margin-bottom:2rem;">
                    <h4 style="margin-bottom:1.5rem;"><?php echo __('contact_info'); ?></h4>

                    <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.5rem;">
                        <div
                            style="width:50px;height:50px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <strong><?php echo __('email'); ?></strong>
                            <p style="margin:0;"><a
                                    href="mailto:<?php echo e(getSetting('contact_email')); ?>"><?php echo e(getSetting('contact_email')); ?></a>
                            </p>
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.5rem;">
                        <div
                            style="width:50px;height:50px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <strong><?php echo __('phone'); ?></strong>
                            <p style="margin:0;"><a
                                    href="tel:<?php echo cleanPhoneNumber(getSetting('contact_phone')); ?>"><?php echo e(getSetting('contact_phone')); ?></a>
                            </p>
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;align-items:flex-start;">
                        <div
                            style="width:50px;height:50px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <strong><?php echo __('location'); ?></strong>
                            <p style="margin:0;"><?php echo e(getSetting('contact_address_' . $lang)); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div style="background:#fff;padding:2rem;border-radius:1rem;box-shadow:var(--shadow-md);">
                    <h4 style="margin-bottom:1.5rem;"><?php echo __('follow_us'); ?></h4>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <?php if ($fb = getSetting('facebook_url')): ?>
                            <a href="<?php echo e($fb); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#1877f2;color:#fff;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($tw = getSetting('twitter_url')): ?>
                            <a href="<?php echo e($tw); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#1da1f2;color:#fff;">
                                <i class="fab fa-twitter"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($li = getSetting('linkedin_url')): ?>
                            <a href="<?php echo e($li); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#0077b5;color:#fff;">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($ig = getSetting('instagram_url')): ?>
                            <a href="<?php echo e($ig); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);color:#fff;">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($tk = getSetting('tiktok_url')): ?>
                            <a href="<?php echo e($tk); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#000;color:#fff;">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($yt = getSetting('youtube_url')): ?>
                            <a href="<?php echo e($yt); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#ff0000;color:#fff;">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>