<?php
/**
 * Ahmed Ashraf Portfolio - Services Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

// SEO Options
$seoOptions = [
    'title' => __('services'),
    'description' => $lang === 'ar'
        ? 'خدمات التسويق الرقمي والاستشارات التقنية - أحمد أشرف'
        : 'Digital marketing services and IT consulting - Ahmed Ashraf',
];

// Get all services
try {
    $stmt = db()->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order, id");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="min-height:50vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo __('our_services'); ?></h1>
            <p class="hero-subtitle" style="max-width:600px;margin:0 auto;">
                <?php echo $lang === 'ar'
                    ? 'خدمات متكاملة في التسويق الرقمي والاستشارات التقنية لنمو أعمالك'
                    : 'Comprehensive digital marketing and IT consulting services for your business growth'; ?>
            </p>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([['title' => __('services'), 'url' => '']]); ?>
</div>

<!-- Services Grid -->
<section class="section">
    <div class="container">
        <div class="grid grid-3" style="gap:2rem;">
            <?php foreach ($services as $index => $service): ?>
                <div class="card service-card" id="<?php echo e($service['slug']); ?>" data-aos="fade-up"
                    data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                    <div class="service-icon">
                        <i class="<?php echo e($service['icon'] ?: 'fas fa-cog'); ?>"></i>
                    </div>
                    <h3 class="card-title"><?php echo e(trans($service, 'title')); ?></h3>
                    <p class="card-text"><?php echo e(trans($service, 'description')); ?></p>

                    <?php
                    $features = $lang === 'ar' ? $service['features_ar'] : $service['features_en'];
                    if ($features):
                        $featuresList = explode('|', $features);
                        ?>
                        <ul style="text-align:<?php echo $lang === 'ar' ? 'right' : 'left'; ?>;margin:1.5rem 0;">
                            <?php foreach ($featuresList as $feature): ?>
                                <li style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;color:var(--gray-600);">
                                    <i class="fas fa-check-circle" style="color:var(--success);"></i>
                                    <?php echo e(trim($feature)); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div style="margin-top:auto;padding-top:1.5rem;border-top:1px solid var(--gray-100);">
                        <?php if ($service['price'] && $service['price'] > 0): ?>
                            <div style="font-size:1.5rem;font-weight:700;color:var(--primary);margin-bottom:1rem;">
                                <?php echo number_format($service['price']); ?>
                                <span style="font-size:0.9rem;font-weight:400;color:var(--gray-500);">
                                    <?php echo $lang === 'ar' ? 'ج.م' : 'EGP'; ?>
                                    <?php if ($service['price_type'] === 'hourly'): ?>
                                        / <?php echo $lang === 'ar' ? 'ساعة' : 'hour'; ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div style="font-size:1.1rem;color:var(--gray-600);margin-bottom:1rem;">
                                <?php echo __('price_on_request'); ?>
                            </div>
                        <?php endif; ?>

                        <a href="#contact-form" class="btn btn-primary w-full"
                            data-service="<?php echo e(trans($service, 'title')); ?>">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo __('request_service'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Me -->
<section class="section bg-gray-50">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2><?php echo $lang === 'ar' ? 'لماذا تختارني؟' : 'Why Choose Me?'; ?></h2>
            <p><?php echo $lang === 'ar'
                ? 'ما يميزني عن غيري'
                : 'What sets me apart'; ?>
            </p>
        </div>

        <div class="grid grid-4">
            <div class="card" style="padding:2rem;text-align:center;" data-aos="fade-up">
                <div
                    style="width:70px;height:70px;margin:0 auto 1rem;background:rgba(0,102,204,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-lightbulb" style="font-size:2rem;color:var(--primary);"></i>
                </div>
                <h4><?php echo $lang === 'ar' ? 'حلول مبتكرة' : 'Innovative Solutions'; ?></h4>
                <p style="margin:0;color:var(--gray-600);">
                    <?php echo $lang === 'ar'
                        ? 'أفكار إبداعية وحلول مخصصة لكل عميل'
                        : 'Creative ideas and customized solutions for each client'; ?>
                </p>
            </div>

            <div class="card" style="padding:2rem;text-align:center;" data-aos="fade-up" data-aos-delay="100">
                <div
                    style="width:70px;height:70px;margin:0 auto 1rem;background:rgba(0,102,204,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-laptop-code" style="font-size:2rem;color:var(--primary);"></i>
                </div>
                <h4><?php echo $lang === 'ar' ? 'خبرة تقنية' : 'Technical Expertise'; ?></h4>
                <p style="margin:0;color:var(--gray-600);">
                    <?php echo $lang === 'ar'
                        ? 'خلفية تقنية قوية تدعم الحلول التسويقية'
                        : 'Strong technical background supporting marketing solutions'; ?>
                </p>
            </div>

            <div class="card" style="padding:2rem;text-align:center;" data-aos="fade-up" data-aos-delay="200">
                <div
                    style="width:70px;height:70px;margin:0 auto 1rem;background:rgba(0,102,204,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-chart-line" style="font-size:2rem;color:var(--primary);"></i>
                </div>
                <h4><?php echo $lang === 'ar' ? 'نتائج قابلة للقياس' : 'Measurable Results'; ?></h4>
                <p style="margin:0;color:var(--gray-600);">
                    <?php echo $lang === 'ar'
                        ? 'تقارير مفصلة ومتابعة مستمرة للأداء'
                        : 'Detailed reports and continuous performance monitoring'; ?>
                </p>
            </div>

            <div class="card" style="padding:2rem;text-align:center;" data-aos="fade-up" data-aos-delay="300">
                <div
                    style="width:70px;height:70px;margin:0 auto 1rem;background:rgba(0,102,204,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-headset" style="font-size:2rem;color:var(--primary);"></i>
                </div>
                <h4><?php echo $lang === 'ar' ? 'دعم متواصل' : 'Ongoing Support'; ?></h4>
                <p style="margin:0;color:var(--gray-600);">
                    <?php echo $lang === 'ar'
                        ? 'تواصل مستمر ودعم على مدار الساعة'
                        : 'Continuous communication and round-the-clock support'; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="section" id="contact-form">
    <div class="container">
        <div class="grid grid-2" style="gap:4rem;align-items:center;">
            <div data-aos="fade-right">
                <h2><?php echo __('service_inquiry'); ?></h2>
                <p style="margin-bottom:2rem;">
                    <?php echo $lang === 'ar'
                        ? 'أخبرني عن مشروعك وسأساعدك في تحقيق أهدافك. املأ النموذج وسأتواصل معك في أقرب وقت.'
                        : 'Tell me about your project and I\'ll help you achieve your goals. Fill out the form and I\'ll get back to you soon.'; ?>
                </p>

                <form id="service-inquiry-form" action="<?php echo SITE_URL; ?>/ajax/contact.php" method="POST"
                    data-validate>
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="type" value="service_inquiry">

                    <div class="grid grid-2" style="gap:1rem;">
                        <div class="form-group">
                            <label class="form-label"><?php echo __('your_name'); ?> *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo __('your_email'); ?> *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo __('your_phone'); ?></label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label
                            class="form-label"><?php echo $lang === 'ar' ? 'الخدمة المطلوبة' : 'Service Required'; ?></label>
                        <select name="service" class="form-control" id="service-select">
                            <option value=""><?php echo $lang === 'ar' ? 'اختر الخدمة' : 'Select a service'; ?></option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo e(trans($service, 'title')); ?>">
                                    <?php echo e(trans($service, 'title')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'تفاصيل المشروع' : 'Project Details'; ?>
                            *</label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="<?php echo $lang === 'ar'
                            ? 'اشرح لي مشروعك والنتائج التي تريد تحقيقها...'
                            : 'Describe your project and the results you want to achieve...'; ?>"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo __('send_message'); ?>
                    </button>
                </form>
            </div>

            <div data-aos="fade-left">
                <img src="<?php echo ASSETS_URL; ?>/images/services-illustration.svg" alt="Services"
                    style="max-width:100%;" onerror="this.style.display='none'">

                <div
                    style="background:var(--gradient-primary);padding:2rem;border-radius:1rem;color:#fff;margin-top:2rem;">
                    <h4 style="color:#fff;margin-bottom:1rem;">
                        <i class="fas fa-phone-alt"></i>
                        <?php echo $lang === 'ar' ? 'تواصل مباشر' : 'Direct Contact'; ?>
                    </h4>
                    <p style="color:rgba(255,255,255,0.9);margin-bottom:1rem;">
                        <?php echo $lang === 'ar'
                            ? 'تفضل التحدث مباشرة؟ اتصل بي أو أرسل رسالة على واتساب.'
                            : 'Prefer to talk directly? Call me or send a WhatsApp message.'; ?>
                    </p>
                    <a href="tel:<?php echo cleanPhoneNumber(getSetting('contact_phone')); ?>"
                        style="display:inline-flex;align-items:center;gap:0.5rem;color:#fff;font-weight:600;">
                        <?php echo e(getSetting('contact_phone')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Pre-fill service from URL hash or button click
    document.addEventListener('DOMContentLoaded', function () {
        // From URL hash
        const hash = window.location.hash.slice(1);
        if (hash) {
            const select = document.getElementById('service-select');
            if (select) {
                for (let option of select.options) {
                    if (option.value.toLowerCase().includes(hash.toLowerCase())) {
                        option.selected = true;
                        break;
                    }
                }
            }
        }

        // From button clicks
        document.querySelectorAll('[data-service]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                const service = this.dataset.service;
                const select = document.getElementById('service-select');
                if (select) {
                    for (let option of select.options) {
                        if (option.value === service) {
                            option.selected = true;
                            break;
                        }
                    }
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>