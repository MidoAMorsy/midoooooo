<?php
/**
 * Admin Panel - Header Template
 */

$lang = getCurrentLanguage();
$dir = getDirection();
$admin = getCurrentAdmin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? __('admin_panel'); ?> | <?php echo getSetting('site_name_' . $lang, SITE_NAME); ?>
    </title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/admin.css">
</head>

<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="sidebar-brand">
                <img src="<?php echo ASSETS_URL; ?>/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                <span><?php echo $lang === 'ar' ? 'لوحة التحكم' : 'Admin Panel'; ?></span>
            </a>
            <button class="sidebar-close" id="sidebar-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span><?php echo __('dashboard'); ?></span>
                    </a>
                </li>

                <li class="nav-section"><?php echo $lang === 'ar' ? 'المحتوى' : 'Content'; ?></li>

                <li>
                    <a href="posts.php" class="<?php echo $currentPage === 'posts' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i>
                        <span><?php echo __('posts'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="projects.php" class="<?php echo $currentPage === 'projects' ? 'active' : ''; ?>">
                        <i class="fas fa-project-diagram"></i>
                        <span><?php echo __('projects'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="services.php" class="<?php echo $currentPage === 'services' ? 'active' : ''; ?>">
                        <i class="fas fa-concierge-bell"></i>
                        <span><?php echo __('services'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="media.php" class="<?php echo $currentPage === 'media' ? 'active' : ''; ?>">
                        <i class="fas fa-photo-video"></i>
                        <span><?php echo __('media_library'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="categories.php" class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i>
                        <span><?php echo __('categories'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="comments.php" class="<?php echo $currentPage === 'comments' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span><?php echo __('comments'); ?></span>
                        <?php
                        try {
                            $pendingCount = db()->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
                            if ($pendingCount > 0):
                                ?>
                                <span class="badge"><?php echo $pendingCount; ?></span>
                            <?php endif;
                        } catch (PDOException $e) {
                        } ?>
                    </a>
                </li>

                <li class="nav-section"><?php echo $lang === 'ar' ? 'التواصل' : 'Communication'; ?></li>

                <li>
                    <a href="messages.php" class="<?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo __('messages'); ?></span>
                        <?php
                        $unreadCount = db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
                        if ($unreadCount > 0):
                            ?>
                            <span class="badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-section"><?php echo $lang === 'ar' ? 'التطبيقات' : 'Applications'; ?></li>

                <li>
                    <a href="<?php echo SITE_URL; ?>/apps/attendance/" target="_blank">
                        <i class="fas fa-user-clock"></i>
                        <span><?php echo __('attendance_system'); ?></span>
                        <i class="fas fa-external-link-alt"
                            style="font-size:0.7rem;margin-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>:auto;"></i>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/apps/qr-generator/" target="_blank">
                        <i class="fas fa-qrcode"></i>
                        <span><?php echo __('qr_generator'); ?></span>
                        <i class="fas fa-external-link-alt"
                            style="font-size:0.7rem;margin-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>:auto;"></i>
                    </a>
                </li>
                <li>
                    <a href="<?php echo SITE_URL; ?>/apps/certificate-creator/" target="_blank">
                        <i class="fas fa-certificate"></i>
                        <span><?php echo __('certificate_creator'); ?></span>
                        <i class="fas fa-external-link-alt"
                            style="font-size:0.7rem;margin-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>:auto;"></i>
                    </a>
                </li>

                <li class="nav-section"><?php echo __('settings'); ?></li>

                <li>
                    <a href="seo.php" class="<?php echo $currentPage === 'seo' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i>
                        <span><?php echo __('seo_settings'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span><?php echo __('site_settings'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog"></i>
                        <span><?php echo __('users'); ?></span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="<?php echo SITE_URL; ?>" target="_blank">
                <i class="fas fa-globe"></i>
                <span><?php echo $lang === 'ar' ? 'زيارة الموقع' : 'View Site'; ?></span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="<?php echo __('search_placeholder'); ?>">
            </div>

            <div class="topbar-actions">
                <!-- Language Switcher -->
                <div class="lang-dropdown">
                    <button class="topbar-btn">
                        <i class="fas fa-globe"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="?lang=en" <?php echo $lang === 'en' ? 'class="active"' : ''; ?>>English</a>
                        <a href="?lang=ar" <?php echo $lang === 'ar' ? 'class="active"' : ''; ?>>العربية</a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="notifications-dropdown">
                    <button class="topbar-btn">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- User Menu -->
                <div class="user-dropdown">
                    <button class="user-btn">
                        <img src="<?php echo $admin['avatar']
                            ? UPLOADS_URL . '/avatars/' . e($admin['avatar'])
                            : 'https://ui-avatars.com/api/?name=' . urlencode($admin['full_name'] ?: $admin['username']) . '&background=0066cc&color=fff'; ?>"
                            alt="<?php echo e($admin['username']); ?>">
                        <span><?php echo e($admin['full_name'] ?: $admin['username']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="profile.php"><i class="fas fa-user"></i> <?php echo __('profile'); ?></a>
                        <a href="settings.php"><i class="fas fa-cog"></i> <?php echo __('settings'); ?></a>
                        <hr>
                        <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i>
                            <?php echo __('logout'); ?></a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php echo displayFlashMessages(); ?>