<?php
/**
 * Admin Panel - Dashboard
 */

require_once '../includes/config.php';
requireLogin();

$admin = getCurrentAdmin();
$lang = getCurrentLanguage();

// Get statistics
try {
    $stats = [
        'posts' => db()->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
        'projects' => db()->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
        'messages' => db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn(),
        'views' => db()->query("SELECT SUM(views) FROM posts")->fetchColumn() ?: 0,
    ];

    // Recent messages
    $stmt = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll();

    // Recent posts
    $stmt = db()->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
    $recentPosts = $stmt->fetchAll();

    // Recent activity
    $stmt = db()->query("SELECT al.*, a.username FROM activity_logs al 
                         LEFT JOIN admins a ON al.admin_id = a.id 
                         ORDER BY al.created_at DESC LIMIT 10");
    $activities = $stmt->fetchAll();

} catch (PDOException $e) {
    $stats = ['posts' => 0, 'projects' => 0, 'messages' => 0, 'views' => 0];
    $recentMessages = [];
    $recentPosts = [];
    $activities = [];
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="content-header">
        <h1><?php echo __('dashboard'); ?></h1>
        <p><?php echo $lang === 'ar' ? 'مرحباً بك' : 'Welcome back'; ?>,
            <?php echo e($admin['full_name'] ?: $admin['username']); ?>!</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['posts']); ?></h3>
                <p><?php echo __('posts'); ?></p>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-project-diagram"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['projects']); ?></h3>
                <p><?php echo __('projects'); ?></p>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['messages']); ?></h3>
                <p><?php echo $lang === 'ar' ? 'رسائل جديدة' : 'New Messages'; ?></p>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['views']); ?></h3>
                <p><?php echo $lang === 'ar' ? 'مشاهدات' : 'Total Views'; ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Messages -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-envelope"></i> <?php echo $lang === 'ar' ? 'أحدث الرسائل' : 'Recent Messages'; ?>
                </h2>
                <a href="messages.php" class="btn btn-sm"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-body">
                <?php if (empty($recentMessages)): ?>
                    <p class="text-muted"><?php echo __('no_results'); ?></p>
                <?php else: ?>
                    <div class="message-list">
                        <?php foreach ($recentMessages as $msg): ?>
                            <div class="message-item <?php echo $msg['status'] === 'unread' ? 'unread' : ''; ?>">
                                <div class="message-avatar">
                                    <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <strong><?php echo e($msg['name']); ?></strong>
                                        <span><?php echo timeAgo($msg['created_at']); ?></span>
                                    </div>
                                    <p><?php echo e(truncate($msg['message'], 60)); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-newspaper"></i> <?php echo $lang === 'ar' ? 'أحدث المقالات' : 'Recent Posts'; ?>
                </h2>
                <a href="posts.php" class="btn btn-sm"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-body">
                <?php if (empty($recentPosts)): ?>
                    <p class="text-muted"><?php echo __('no_results'); ?></p>
                <?php else: ?>
                    <div class="post-list">
                        <?php foreach ($recentPosts as $post): ?>
                            <div class="post-item">
                                <div class="post-thumb">
                                    <?php if ($post['featured_image']): ?>
                                        <img src="<?php echo UPLOADS_URL; ?>/blog/<?php echo e($post['featured_image']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="post-info">
                                    <h4><?php echo e(truncate(trans($post, 'title'), 40)); ?></h4>
                                    <div class="post-meta">
                                        <span
                                            class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo $post['status']; ?>
                                        </span>
                                        <span><?php echo formatDate($post['created_at']); ?></span>
                                    </div>
                                </div>
                                <a href="posts.php?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-icon">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="dashboard-card full-width">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> <?php echo $lang === 'ar' ? 'سجل النشاط' : 'Activity Log'; ?></h2>
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <p class="text-muted"><?php echo __('no_results'); ?></p>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php
                                    $icon = match ($activity['action']) {
                                        'login' => 'sign-in-alt',
                                        'logout' => 'sign-out-alt',
                                        'create', 'post_create' => 'plus',
                                        'update', 'post_update' => 'edit',
                                        'delete', 'post_delete' => 'trash',
                                        default => 'circle'
                                    };
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p>
                                        <strong><?php echo e($activity['username'] ?: 'System'); ?></strong>
                                        <?php echo e($activity['action']); ?>
                                        <?php if ($activity['description']): ?>
                                            - <?php echo e(truncate($activity['description'], 50)); ?>
                                        <?php endif; ?>
                                    </p>
                                    <span><?php echo timeAgo($activity['created_at']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3><?php echo $lang === 'ar' ? 'إجراءات سريعة' : 'Quick Actions'; ?></h3>
        <div class="action-buttons">
            <a href="posts.php?action=add" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span><?php echo $lang === 'ar' ? 'مقال جديد' : 'New Post'; ?></span>
            </a>
            <a href="projects.php?action=add" class="action-btn">
                <i class="fas fa-folder-plus"></i>
                <span><?php echo $lang === 'ar' ? 'مشروع جديد' : 'New Project'; ?></span>
            </a>
            <a href="media.php" class="action-btn">
                <i class="fas fa-cloud-upload-alt"></i>
                <span><?php echo $lang === 'ar' ? 'رفع ملف' : 'Upload File'; ?></span>
            </a>
            <a href="messages.php" class="action-btn">
                <i class="fas fa-envelope-open-text"></i>
                <span><?php echo $lang === 'ar' ? 'الرسائل' : 'Messages'; ?></span>
            </a>
            <a href="settings.php" class="action-btn">
                <i class="fas fa-cog"></i>
                <span><?php echo __('settings'); ?></span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>