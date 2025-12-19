<?php
/**
 * AJAX Handler - Newsletter Subscription
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$lang = getCurrentLanguage();

// Validation
if (empty($email)) {
    echo json_encode(['success' => false, 'error' => $lang === 'ar' ? 'البريد الإلكتروني مطلوب' : 'Email is required']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'error' => $lang === 'ar' ? 'بريد إلكتروني غير صالح' : 'Invalid email address']);
    exit;
}

try {
    // Check if already subscribed
    $stmt = db()->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['is_active']) {
            echo json_encode([
                'success' => false,
                'error' => $lang === 'ar' ? 'أنت مشترك بالفعل' : 'You are already subscribed'
            ]);
        } else {
            // Reactivate subscription
            db()->prepare("UPDATE newsletter_subscribers SET is_active = 1, subscribed_at = NOW() WHERE id = ?")
                ->execute([$existing['id']]);
            echo json_encode([
                'success' => true,
                'message' => $lang === 'ar' ? 'تم تفعيل اشتراكك مجدداً' : 'Your subscription has been reactivated'
            ]);
        }
        exit;
    }

    // New subscription
    $stmt = db()->prepare("INSERT INTO newsletter_subscribers (email, ip_address, subscribed_at) VALUES (?, ?, NOW())");
    $stmt->execute([$email, getClientIP()]);

    // Send welcome email
    $subject = $lang === 'ar' ? 'مرحباً بك في النشرة البريدية' : 'Welcome to Our Newsletter';
    $body = $lang === 'ar'
        ? "شكراً لاشتراكك في نشرتنا البريدية!\n\nستتلقى آخر الأخبار والتحديثات."
        : "Thank you for subscribing to our newsletter!\n\nYou'll receive the latest news and updates.";

    sendEmail($email, $subject, nl2br($body));

    echo json_encode([
        'success' => true,
        'message' => $lang === 'ar' ? 'شكراً لاشتراكك!' : 'Thank you for subscribing!'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
