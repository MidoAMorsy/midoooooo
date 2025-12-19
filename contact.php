<?php
/**
 * Ahmed Ashraf Portfolio - Contact Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $service_required = sanitize($_POST['service'] ?? ''); // Changed from service_required to service as per likely frontend name, assuming standard naming
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        $errors = [];

        if (empty($name))
            $errors[] = __('required_field');
        if (empty($email) || !isValidEmail($email))
            $errors[] = __('invalid_email');
        if (empty($message))
            $errors[] = __('required_field');

        if (empty($errors)) {
            try {
                $stmt = db()->prepare("INSERT INTO contact_messages (name, email, phone, service_required, subject, message, ip_address) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $service_required, $subject, $message, getClientIP()]);

                // Send email notification
                $emailBody = "New contact form submission:\n\n";
                $emailBody .= "Name: {$name}\n";
                $emailBody .= "Email: {$email}\n";
                $emailBody .= "Phone: {$phone}\n";
                $emailBody .= "Service: {$service_required}\n";
                $emailBody .= "Subject: {$subject}\n\n";
                $emailBody .= "Message:\n{$message}";

                $adminEmail = getSetting('admin_notification_email') ?: ADMIN_EMAIL;
                sendEmail($adminEmail, "New Contact: {$subject}", nl2br($emailBody));

                // Send confirmation to user
                $userSubject = $lang === 'ar' ? 'تم استلام رسالتك بنجاح' : 'We received your message';
                $userBody = $lang === 'ar'
                    ? "مرحباً {$name}،\n\nشكراً لتواصلك معنا. لقد استلمنا رسالتك وسنرد عليك في أقرب وقت.\n\nتحياتنا،\n" . getSetting('site_name_ar')
                    : "Hi {$name},\n\nThank you for contacting us. We have received your message and will get back to you soon.\n\nBest regards,\n" . getSetting('site_name_en');

                sendEmail($email, $userSubject, nl2br($userBody));

                setFlash('success', __('message_sent'));
                redirect(SITE_URL . '/contact.php#contact-form');

            } catch (PDOException $e) {
                setFlash('error', __('system_error'));
            }
        } else {
            setFlash('error', implode(', ', $errors));
        }
    }
}

// SEO Options
$seoOptions = [
    'title' => __('contact'),
    'description' => $lang === 'ar'
        ? 'تواصل مع أحمد أشرف - استفسارات، مشاريع، أو أي سؤال'
        : 'Contact Ahmed Ashraf - Inquiries, projects, or any questions',
];

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="min-height:50vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <h1 class="hero-title"><?php echo __('get_in_touch'); ?></h1>
            <p class="hero-subtitle" style="max-width:600px;margin:0 auto;">
                <?php echo $lang === 'ar'
                    ? 'لديك مشروع أو استفسار؟ لا تتردد في التواصل معي'
                    : 'Have a project or inquiry? Don\'t hesitate to contact me'; ?>
            </p>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container" style="padding:1rem 0;">
    <?php echo breadcrumbs([['title' => __('contact'), 'url' => '']]); ?>
</div>

<!-- Contact Content -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="gap:4rem;">

            <!-- Contact Form -->
            <div data-aos="fade-right" id="contact-form">
                <h2><?php echo __('send_message'); ?></h2>
                <p style="margin-bottom:2rem;color:var(--gray-600);">
                    <?php echo $lang === 'ar'
                        ? 'املأ النموذج التالي وسأرد عليك في أقرب وقت ممكن'
                        : 'Fill out the form below and I\'ll get back to you as soon as possible'; ?>
                </p>

                <?php echo displayFlashMessages(); ?>

                <form method="POST" action="" data-validate>
                    <?php echo csrfField(); ?>

                    <div class="grid grid-2" style="gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="name"><?php echo __('your_name'); ?> *</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                value="<?php echo e($_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email"><?php echo __('your_email'); ?> *</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                value="<?php echo e($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone"><?php echo __('your_phone'); ?></label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                            value="<?php echo e($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject"><?php echo __('subject'); ?></label>
                        <input type="text" id="subject" name="subject" class="form-control"
                            value="<?php echo e($_POST['subject'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message"><?php echo __('message'); ?> *</label>
                        <textarea id="message" name="message" class="form-control" rows="6"
                            required><?php echo e($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo __('send_message'); ?>
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div data-aos="fade-left">
                <!-- Info Cards -->
                <div style="display:grid;gap:1.5rem;margin-bottom:2rem;">
                    <div class="card" style="padding:1.5rem;display:flex;gap:1.5rem;align-items:flex-start;">
                        <div
                            style="width:60px;height:60px;background:var(--gradient-primary);border-radius:1rem;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fas fa-envelope" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom:0.5rem;"><?php echo __('email'); ?></h4>
                            <a href="mailto:<?php echo e(getSetting('contact_email')); ?>"
                                style="color:var(--primary);">
                                <?php echo e(getSetting('contact_email')); ?>
                            </a>
                        </div>
                    </div>

                    <div class="card" style="padding:1.5rem;display:flex;gap:1.5rem;align-items:flex-start;">
                        <div
                            style="width:60px;height:60px;background:var(--gradient-primary);border-radius:1rem;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fas fa-phone" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom:0.5rem;"><?php echo __('phone'); ?></h4>
                            <a href="tel:<?php echo cleanPhoneNumber(getSetting('contact_phone')); ?>"
                                style="color:var(--primary);">
                                <?php echo e(getSetting('contact_phone')); ?>
                            </a>
                            <br>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', getSetting('contact_phone')); ?>"
                                style="color:#25d366;font-size:0.9rem;">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>

                    <div class="card" style="padding:1.5rem;display:flex;gap:1.5rem;align-items:flex-start;">
                        <div
                            style="width:60px;height:60px;background:var(--gradient-primary);border-radius:1rem;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                            <i class="fas fa-map-marker-alt" style="font-size:1.5rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom:0.5rem;"><?php echo __('location'); ?></h4>
                            <p style="margin:0;color:var(--gray-600);">
                                <?php echo e(getSetting('contact_address_' . $lang, 'Cairo, Egypt')); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="card" style="padding:1.5rem;">
                    <h4 style="margin-bottom:1rem;"><?php echo __('follow_us'); ?></h4>
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
                        <?php if ($yt = getSetting('youtube_url')): ?>
                            <a href="<?php echo e($yt); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#ff0000;color:#fff;">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($gh = getSetting('github_url')): ?>
                            <a href="<?php echo e($gh); ?>" target="_blank" rel="noopener" class="btn btn-icon"
                                style="background:#333;color:#fff;">
                                <i class="fab fa-github"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Working Hours -->
                <div class="card" style="padding:1.5rem;margin-top:1.5rem;">
                    <h4 style="margin-bottom:1rem;"><?php echo __('working_hours'); ?></h4>
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
                        <span><?php echo $lang === 'ar' ? 'الأحد - الخميس' : 'Sunday - Thursday'; ?></span>
                        <span style="color:var(--primary);">9:00 AM - 6:00 PM</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span><?php echo $lang === 'ar' ? 'الجمعة - السبت' : 'Friday - Saturday'; ?></span>
                        <span style="color:var(--gray-500);"><?php echo $lang === 'ar' ? 'عطلة' : 'Closed'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section style="padding-bottom:0;">
    <div class="container">
        <div style="border-radius:1rem;overflow:hidden;box-shadow:var(--shadow-lg);">
            <?php
            $mapUrl = getSetting('map_embed_url');
            if ($mapUrl) {
                echo $mapUrl;
            } else {
                ?>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d110502.61185999715!2d31.18407345!3d30.0594885!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Egypt!5e0!3m2!1sen!2seg!4v1639000000000"
                    width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            <?php } ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section bg-gradient" style="color:#fff;margin-top:4rem;">
    <div class="container text-center">
        <h2 style="color:#fff;"><?php echo $lang === 'ar' ? 'جاهز لبدء مشروعك؟' : 'Ready to start your project?'; ?>
        </h2>
        <p style="color:rgba(255,255,255,0.8);max-width:600px;margin:0 auto 2rem;">
            <?php echo $lang === 'ar'
                ? 'تواصل معي الآن ولنناقش كيف يمكنني مساعدتك في تحقيق أهدافك.'
                : 'Contact me now and let\'s discuss how I can help you achieve your goals.'; ?>
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="tel:<?php echo cleanPhoneNumber(getSetting('contact_phone')); ?>" class="btn btn-outline btn-lg">
                <i class="fas fa-phone"></i>
                <?php echo e(getSetting('contact_phone')); ?>
            </a>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', getSetting('contact_phone')); ?>"
                class="btn btn-primary btn-lg" target="_blank">
                <i class="fab fa-whatsapp"></i>
                WhatsApp
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>