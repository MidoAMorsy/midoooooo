<?php
/**
 * QR Generator - AJAX Handlers
 */

require_once '../../includes/config.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$lang = getCurrentLanguage();

switch ($action) {
    case 'generate':
        generateQR();
        break;
    case 'batch':
        batchGenerate();
        break;
    case 'history':
        getHistory();
        break;
    case 'delete':
        deleteQR();
        break;
    default:
        jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
}

function generateQR()
{
    $data = sanitize($_POST['data'] ?? '');
    $type = sanitize($_POST['type'] ?? 'url');
    $size = (int) ($_POST['size'] ?? 300);
    $color = sanitize($_POST['color'] ?? '000000');

    if (empty($data)) {
        jsonResponse(['success' => false, 'error' => 'Data is required'], 400);
    }

    // Use Google Charts API for QR generation (for demonstration)
    // In production, use phpqrcode library for server-side generation
    $qrUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($data) . "&choe=UTF-8&chco=" . str_replace('#', '', $color);

    // Generate unique code
    $code = 'QR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    try {
        // Save to database
        db()->prepare("INSERT INTO qr_codes (certificate_code, verification_url, color) VALUES (?, ?, ?)")
            ->execute([$code, $data, $color]);

        jsonResponse([
            'success' => true,
            'code' => $code,
            'qr_url' => $qrUrl,
            'data' => $data
        ]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

function batchGenerate()
{
    global $lang;

    if (empty($_FILES['file'])) {
        jsonResponse(['success' => false, 'error' => 'No file uploaded'], 400);
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'csv') {
        jsonResponse(['success' => false, 'error' => $lang === 'ar' ? 'يدعم CSV فقط' : 'Only CSV supported'], 400);
    }

    $handle = fopen($file['tmp_name'], 'r');
    fgetcsv($handle); // Skip header

    $generated = 0;
    $codes = [];

    // Create batch record
    db()->prepare("INSERT INTO qr_history (batch_name, generation_mode, total_generated) VALUES (?, 'batch', 0)")
        ->execute(['Batch ' . date('Y-m-d H:i')]);
    $batchId = db()->lastInsertId();

    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row[0]))
            continue;

        $data = trim($row[0]);
        $code = 'QR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        try {
            db()->prepare("INSERT INTO qr_codes (certificate_code, verification_url, batch_id) VALUES (?, ?, ?)")
                ->execute([$code, $data, $batchId]);

            $codes[] = [
                'code' => $code,
                'data' => $data,
                'qr_url' => "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($data)
            ];
            $generated++;
        } catch (Exception $e) {
            // Skip errors
        }
    }

    fclose($handle);

    // Update batch count
    db()->prepare("UPDATE qr_history SET total_generated = ? WHERE id = ?")->execute([$generated, $batchId]);

    jsonResponse([
        'success' => true,
        'generated' => $generated,
        'codes' => $codes
    ]);
}

function getHistory()
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    try {
        $total = db()->query("SELECT COUNT(*) FROM qr_codes")->fetchColumn();
        $stmt = db()->query("SELECT * FROM qr_codes ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

        jsonResponse([
            'success' => true,
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

function deleteQR()
{
    $id = (int) ($_POST['id'] ?? 0);

    try {
        db()->prepare("DELETE FROM qr_codes WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
