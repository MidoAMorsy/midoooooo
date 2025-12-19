<?php
/**
 * Certificate Creator - Generate Image
 */

require_once '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['student_name'] ?? '');
    $course = sanitize($_POST['course_name'] ?? '');
    $date = sanitize($_POST['date'] ?? date('Y-m-d'));

    if (empty($name) || empty($course)) {
        die('Missing required fields');
    }

    // Settings (Positions) - Could be dynamic, using hardcoded for now
    $imgWidth = 2000;
    $imgHeight = 1414;
    $templatePath = UPLOADS_PATH . 'certificates/template.jpg';
    $fontPath = ASSETS_PATH . 'fonts/Cairo-Bold.ttf'; // Ensure this exists

    if (!file_exists($templatePath)) {
        // Fallback to create a blank white image if template doesn't exist
        $image = imagecreatetruecolor($imgWidth, $imgHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $imgWidth, $imgHeight, $white);
        // Draw a border
        $border = imagecolorallocate($image, 0, 0, 0);
        imagerectangle($image, 50, 50, $imgWidth - 50, $imgHeight - 50, $border);
    } else {
        $image = imagecreatefromjpeg($templatePath);
    }

    $color = imagecolorallocate($image, 0, 0, 0); // Black text

    // ARABIC SUPPORT
    // Require I18N_Arabic library if available
    // Ensure you have downloaded Ar-PHP and placed it in includes/libs/Ar-PHP/
    $arLibPath = INCLUDES_PATH . 'libs/Ar-PHP/I18N/Arabic.php';

    if (file_exists($arLibPath)) {
        require_once $arLibPath;
        $Arabic = new I18N_Arabic('Glyphs');

        // Reshape text
        $name = $Arabic->utf8Glyphs($name);
        $course = $Arabic->utf8Glyphs($course);
    }

    // Centering Logic
    function drawCenteredText($image, $size, $angle, $x, $y, $color, $font, $text)
    {
        $bbox = imagettfbbox($size, $angle, $font, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $centeredX = $x - ($textWidth / 2);
        imagettftext($image, $size, $angle, $centeredX, $y, $color, $font, $text);
    }

    // Verify font exists, else use built-in font (no Arabic support in built-in)
    if (file_exists($fontPath)) {
        // Draw Name
        drawCenteredText($image, 60, 0, $imgWidth / 2, 700, $color, $fontPath, $name);

        // Draw Course
        drawCenteredText($image, 40, 0, $imgWidth / 2, 900, $color, $fontPath, $course);

        // Draw Date
        imagettftext($image, 30, 0, 1500, 1200, $color, $fontPath, $date);
    } else {
        // Fallback
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 5, $imgWidth / 2 - 50, 700, $name, $textColor);
        imagestring($image, 5, $imgWidth / 2 - 50, 900, $course, $textColor);
        imagestring($image, 5, 1500, 1200, $date, $textColor);
    }

    // Output
    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="certificate.jpg"');
    imagejpeg($image);
    imagedestroy($image);
    exit;
}
