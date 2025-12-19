<?php
/**
 * Header Template
 */

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$currentLang = getCurrentLanguage();
$dir = getDirection();
$isRTL = isRTL();

// Get site settings
$siteName = getSetting('site_name_' . $currentLang, 'Ahmed Ashraf');
$siteTagline = getSetting('site_tagline_' . $currentLang, '');

// Current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>" dir="<?php echo $dir; ?>">

<head>
    <?php echo renderSEOHead($seoOptions ?? [], $schemaType ?? 'WebSite', $schemaData ?? []); ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo ASSETS_URL; ?>/images/apple-touch-icon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/main.css">

    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo e($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Google Analytics -->
    <?php $gaCode = getSetting('google_analytics');
    if ($gaCode): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e($gaCode); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '<?php echo e($gaCode); ?>');
        </script>
    <?php endif; ?>
</head>
<style>
    /* Dynamic Logo Switching */
    .navbar .logo-dark {
        display: none;
    }

    .navbar.scrolled .logo-light {
        display: none !important;
    }

    .navbar.scrolled .logo-dark {
        display: block !important;
    }

    /* Ensure Header background is white when scrolled */
    .navbar.scrolled {
        background: #fff !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>" class="navbar-brand">
                <?php
                $logoLight = getUploadedUrl(getSetting('header_logo'), 'images', 'logo.png');
                $logoDarkSetting = getSetting('header_logo_dark');
                $logoDark = $logoDarkSetting ? getUploadedUrl($logoDarkSetting, 'images') : $logoLight;
                ?>
                <img src="<?php echo $logoLight; ?>" alt="Logo" class="logo-light"
                    style="height:50px;width:auto;object-fit:contain;">
                <img src="<?php echo $logoDark; ?>" alt="Logo" class="logo-dark"
                    style="height:50px;width:auto;object-fit:contain;display:none;">
            </a>

            <div class="navbar-menu" id="navbar-menu">
                <ul class="navbar-nav">
                    <li>
                        <a href="<?php echo SITE_URL; ?>/"
                            class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                            <?php echo __('home'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/about.php"
                            class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>">
                            <?php echo __('about'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/services.php"
                            class="nav-link <?php echo $currentPage === 'services' ? 'active' : ''; ?>">
                            <?php echo __('services'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/projects.php"
                            class="nav-link <?php echo $currentPage === 'projects' ? 'active' : ''; ?>">
                            <?php echo __('projects'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/blog.php"
                            class="nav-link <?php echo $currentPage === 'blog' ? 'active' : ''; ?>">
                            <?php echo __('blog'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/contact.php"
                            class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">
                            <?php echo __('contact'); ?>
                        </a>
                    </li>
                </ul>

                <!-- Language Switcher -->
                <div class="lang-switcher">
                    <a href="?lang=en" data-lang="en"
                        class="<?php echo $currentLang === 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=ar" data-lang="ar" class="<?php echo $currentLang === 'ar' ? 'active' : ''; ?>">ع</a>
                </div>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php $flashMessages = displayFlashMessages(); ?>
    <?php if ($flashMessages): ?>
        <div class="flash-messages" style="position:fixed;top:80px;right:20px;z-index:1000;max-width:400px;">
            <?php echo $flashMessages; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>