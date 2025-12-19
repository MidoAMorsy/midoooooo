<?php
/**
 * AJAX Handler - Contact Form
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');
$type = sanitize($_POST['type'] ?? 'contact');
$service = sanitize($_POST['service'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Invalid email address';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

// Rate limiting - max 5 messages per hour per IP
$ip = getClientIP();
try {
    $stmt = db()->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$ip]);
    $recentCount = $stmt->fetchColumn();

    if ($recentCount >= 5) {
        echo json_encode(['success' => false, 'error' => 'Too many messages. Please try again later.']);
        exit;
    }
} catch (PDOException $e) {
    // Continue even if rate limit check fails
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit;
}

// If it's a service inquiry, prepend service name to subject
if ($type === 'service_inquiry' && $service) {
    $subject = "[Service Inquiry: {$service}] " . $subject;
}

try {
    $stmt = db()->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'unread')");
    $stmt->execute([$name, $email, $phone, $subject, $message, $ip]);

    // Send email notification to admin
    $emailBody = "New contact form submission:\n\n";
    $emailBody .= "Name: {$name}\n";
    $emailBody .= "Email: {$email}\n";
    if ($phone)
        $emailBody .= "Phone: {$phone}\n";
    if ($service)
        $emailBody .= "Service: {$service}\n";
    $emailBody .= "Subject: {$subject}\n\n";
    $emailBody .= "Message:\n{$message}\n\n";
    $emailBody .= "---\n";
    $emailBody .= "Sent from: " . SITE_URL . "\n";
    $emailBody .= "IP Address: {$ip}\n";
    $emailBody .= "Time: " . date('Y-m-d H:i:s');

    sendEmail(
        getSetting('contact_email', ADMIN_EMAIL),
        "New Contact: " . ($subject ?: 'No Subject'),
        nl2br($emailBody)
    );

    // Send confirmation to user
    $lang = getCurrentLanguage();
    $confirmSubject = $lang === 'ar'
        ? 'شكراً لتواصلك - أحمد أشرف'
        : 'Thank you for contacting - Ahmed Ashraf';

    $confirmBody = $lang === 'ar'
        ? "مرحباً {$name}،\n\nشكراً لتواصلك معنا. تم استلام رسالتك وسأقوم بالرد عليك في أقرب وقت ممكن.\n\nمع أطيب التحيات،\nأحمد أشرف"
        : "Hello {$name},\n\nThank you for contacting us. Your message has been received and I will respond as soon as possible.\n\nBest regards,\nAhmed Ashraf";

    sendEmail($email, $confirmSubject, nl2br($confirmBody));

    $successMessage = $lang === 'ar'
        ? 'تم إرسال رسالتك بنجاح. سأتواصل معك قريباً!'
        : 'Your message has been sent successfully. I will contact you soon!';

    echo json_encode(['success' => true, 'message' => $successMessage]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to send message. Please try again.']);
}
