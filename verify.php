<?php
/**
 * Certificate Verification Page
 */

require_once 'includes/config.php';

$lang = getCurrentLanguage();
$cert = sanitize($_GET['cert'] ?? $_GET['code'] ?? '');
$verified = false;
$certificate = null;

if ($cert) {
    try {
        // Check in generated certificates
        $stmt = db()->prepare("SELECT gc.*, ct.template_name 
                               FROM generated_certificates gc 
                               LEFT JOIN certificate_templates ct ON gc.template_id = ct.id 
                               WHERE gc.certificate_code = ?");
        $stmt->execute([$cert]);
        $certificate = $stmt->fetch();

        if ($certificate) {
            $verified = true;
        } else {
            // Check in QR codes
            $stmt = db()->prepare("SELECT * FROM qr_codes WHERE certificate_code = ?");
            $stmt->execute([$cert]);
            $qr = $stmt->fetch();
            if ($qr) {
                $verified = true;
                $certificate = ['certificate_code' => $qr['certificate_code'], 'created_at' => $qr['created_at']];
            }
        }
    } catch (PDOException $e) {
        // Error
    }
}

$seoOptions = [
    'title' => $lang === 'ar' ? 'التحقق من الشهادة' : 'Certificate Verification',
    'robots' => 'noindex, nofollow'
];

include 'includes/header.php';
?>

<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <i class="fas fa-certificate" style="font-size:3rem;margin-bottom:1rem;"></i>
            <h1 class="hero-title"><?php echo $lang === 'ar' ? 'التحقق من الشهادة' : 'Certificate Verification'; ?></h1>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="max-width:600px;margin:0 auto;">
            <!-- Search Form -->
            <div class="card" style="padding:2rem;margin-bottom:2rem;">
                <form method="GET">
                    <div class="form-group">
                        <label
                            class="form-label"><?php echo $lang === 'ar' ? 'رقم الشهادة' : 'Certificate Number'; ?></label>
                        <input type="text" name="cert" value="<?php echo e($cert); ?>" class="form-control"
                            placeholder="<?php echo $lang === 'ar' ? 'أدخل رقم الشهادة' : 'Enter certificate number'; ?>"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-search"></i>
                        <?php echo $lang === 'ar' ? 'تحقق' : 'Verify'; ?>
                    </button>
                </form>
            </div>

            <?php if ($cert): ?>
                <!-- Result -->
                <div class="card" style="padding:2rem;text-align:center;">
                    <?php if ($verified && $certificate): ?>
                        <div style="color:var(--success);margin-bottom:1.5rem;">
                            <i class="fas fa-check-circle" style="font-size:4rem;"></i>
                        </div>
                        <h2 style="color:var(--success);margin-bottom:1rem;">
                            <?php echo $lang === 'ar' ? 'تم التحقق بنجاح!' : 'Verified Successfully!'; ?>
                        </h2>
                        <p style="color:var(--gray-600);margin-bottom:2rem;">
                            <?php echo $lang === 'ar' ? 'هذه الشهادة صالحة ومسجلة في نظامنا.' : 'This certificate is valid and registered in our system.'; ?>
                        </p>

                        <div
                            style="text-align:<?php echo $lang === 'ar' ? 'right' : 'left'; ?>;background:var(--gray-50);padding:1.5rem;border-radius:var(--radius-lg);">
                            <div style="margin-bottom:1rem;">
                                <strong><?php echo $lang === 'ar' ? 'رقم الشهادة:' : 'Certificate Number:'; ?></strong>
                                <span style="color:var(--primary);"><?php echo e($certificate['certificate_code']); ?></span>
                            </div>

                            <?php if (!empty($certificate['recipient_name'])): ?>
                                <div style="margin-bottom:1rem;">
                                    <strong><?php echo $lang === 'ar' ? 'اسم الحاصل:' : 'Recipient Name:'; ?></strong>
                                    <span><?php echo e($certificate['recipient_name']); ?></span>
                                </div>
                            <?php endif; ?>

                            <div>
                                <strong><?php echo $lang === 'ar' ? 'تاريخ الإصدار:' : 'Issue Date:'; ?></strong>
                                <span><?php echo formatDate($certificate['created_at']); ?></span>
                            </div>
                        </div>

                    <?php else: ?>
                        <div style="color:var(--error);margin-bottom:1.5rem;">
                            <i class="fas fa-times-circle" style="font-size:4rem;"></i>
                        </div>
                        <h2 style="color:var(--error);margin-bottom:1rem;">
                            <?php echo $lang === 'ar' ? 'الشهادة غير موجودة' : 'Certificate Not Found'; ?>
                        </h2>
                        <p style="color:var(--gray-600);">
                            <?php echo $lang === 'ar'
                                ? 'لم نتمكن من العثور على شهادة بهذا الرقم. يرجى التحقق من الرقم والمحاولة مرة أخرى.'
                                : 'We could not find a certificate with this number. Please check the number and try again.'; ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>