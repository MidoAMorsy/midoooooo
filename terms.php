<?php
/**
 * Terms of Service Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

$seoOptions = [
    'title' => __('terms_of_service'),
    'description' => $lang === 'ar' ? 'شروط الخدمة' : 'Terms of Service',
];

include 'includes/header.php';
?>

<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo __('terms_of_service'); ?></h1>
        </div>
    </div>
</section>

<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([['title' => __('terms_of_service'), 'url' => '']]); ?>
</div>

<section class="section">
    <div class="container">
        <div class="card" style="padding:3rem;max-width:900px;margin:0 auto;">
            <?php if ($lang === 'ar'): ?>
                <h2>شروط الخدمة</h2>
                <p style="color:var(--gray-500);margin-bottom:2rem;">آخر تحديث: <?php echo date('Y-m-d'); ?></p>

                <h3>قبول الشروط</h3>
                <p>باستخدامك لهذا الموقع، فإنك توافق على هذه الشروط والأحكام.</p>

                <h3>استخدام الخدمة</h3>
                <p>توافق على استخدام الموقع والخدمات لأغراض مشروعة فقط.</p>

                <h3>الملكية الفكرية</h3>
                <p>جميع المحتويات والمواد الموجودة على هذا الموقع محمية بموجب قوانين حقوق النشر.</p>

                <h3>إخلاء المسؤولية</h3>
                <p>الخدمات مقدمة "كما هي" دون أي ضمانات صريحة أو ضمنية.</p>

                <h3>التعديلات</h3>
                <p>نحتفظ بالحق في تعديل هذه الشروط في أي وقت.</p>

            <?php else: ?>
                <h2>Terms of Service</h2>
                <p style="color:var(--gray-500);margin-bottom:2rem;">Last updated: <?php echo date('Y-m-d'); ?></p>

                <h3>Acceptance of Terms</h3>
                <p>By using this website, you agree to these terms and conditions.</p>

                <h3>Use of Service</h3>
                <p>You agree to use the website and services for lawful purposes only.</p>

                <h3>Intellectual Property</h3>
                <p>All content and materials on this website are protected by copyright laws.</p>

                <h3>Disclaimer</h3>
                <p>Services are provided "as is" without any express or implied warranties.</p>

                <h3>Modifications</h3>
                <p>We reserve the right to modify these terms at any time.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>