<?php
/**
 * Privacy Policy Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

$seoOptions = [
    'title' => __('privacy_policy'),
    'description' => $lang === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy',
];

include 'includes/header.php';
?>

<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo __('privacy_policy'); ?></h1>
        </div>
    </div>
</section>

<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([['title' => __('privacy_policy'), 'url' => '']]); ?>
</div>

<section class="section">
    <div class="container">
        <div class="card" style="padding:3rem;max-width:900px;margin:0 auto;">
            <?php if ($lang === 'ar'): ?>
                <h2>سياسة الخصوصية</h2>
                <p style="color:var(--gray-500);margin-bottom:2rem;">آخر تحديث: <?php echo date('Y-m-d'); ?></p>

                <h3>جمع المعلومات</h3>
                <p>نحن نجمع المعلومات التي تقدمها لنا مباشرة، مثل عندما تملأ نموذج الاتصال أو ترسل لنا بريدًا إلكترونيًا.
                </p>

                <h3>استخدام المعلومات</h3>
                <p>نستخدم المعلومات التي نجمعها للتواصل معك، وتقديم خدماتنا، وتحسين موقعنا.</p>

                <h3>حماية المعلومات</h3>
                <p>نحن نتخذ إجراءات أمنية مناسبة لحماية معلوماتك الشخصية من الوصول غير المصرح به.</p>

                <h3>ملفات تعريف الارتباط</h3>
                <p>نستخدم ملفات تعريف الارتباط لتحسين تجربتك على موقعنا. يمكنك تعطيلها من إعدادات المتصفح.</p>

                <h3>التواصل معنا</h3>
                <p>إذا كان لديك أي أسئلة حول سياسة الخصوصية، يرجى <a href="<?php echo SITE_URL; ?>/contact.php">التواصل
                        معنا</a>.</p>

            <?php else: ?>
                <h2>Privacy Policy</h2>
                <p style="color:var(--gray-500);margin-bottom:2rem;">Last updated: <?php echo date('Y-m-d'); ?></p>

                <h3>Information Collection</h3>
                <p>We collect information you provide directly to us, such as when you fill out a contact form or send us an
                    email.</p>

                <h3>Use of Information</h3>
                <p>We use the information we collect to communicate with you, provide our services, and improve our website.
                </p>

                <h3>Information Protection</h3>
                <p>We take appropriate security measures to protect your personal information from unauthorized access.</p>

                <h3>Cookies</h3>
                <p>We use cookies to improve your experience on our website. You can disable them from your browser
                    settings.</p>

                <h3>Contact Us</h3>
                <p>If you have any questions about this Privacy Policy, please <a
                        href="<?php echo SITE_URL; ?>/contact.php">contact us</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>