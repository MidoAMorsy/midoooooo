<?php
/**
 * Pro Certificate Creator
 * Version 2.0 - Client Side Power
 */
require_once '../../includes/config.php';
$lang = getCurrentLanguage();
$pageTitle = $lang === 'ar' ? 'صانع الشهادات الاحترافي' : 'Pro Certificate Creator';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- External Libs -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Tajawal:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- PDF & ZIP Libs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- App CSS -->
    <link rel="stylesheet" href="../../assets/css/certificate-creator.css">

    <style>
        /* Temporary override to ensure full screen mode works well with existing header if any */
        body {
            margin: 0;
            padding: 0;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            opacity: 0.7;
        }

        .back-btn:hover {
            opacity: 1;
        }
    </style>
</head>

<body class="certificate-app-body <?php echo $lang === 'ar' ? 'rtl' : ''; ?>">

    <a href="../../index.php" class="back-btn">
        <i class="fas fa-arrow-<?php echo $lang === 'ar' ? 'right' : 'left'; ?>"></i>
        <?php echo $lang === 'ar' ? 'العودة للرئيسية' : 'Back to Home'; ?>
    </a>

    <div class="app-container">
        <!-- LEFT SIDEBAR: Controls -->
        <aside class="glass-pane controls-sidebar">
            <div style="text-align:center;margin-bottom:20px;">
                <h2
                    style="margin:0;font-weight:700;background:var(--primary-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    Pro Creator
                </h2>
                <p style="margin:5px 0 0;font-size:0.8em;color:var(--text-muted);">v2.0 Client-Side</p>
            </div>

            <!-- 1. Template -->
            <div class="control-group">
                <h4 style="margin-bottom:10px;color:var(--accent-teal);"><i class="fas fa-layer-group"></i>
                    <?php echo $lang === 'ar' ? 'القالب' : 'Template'; ?></h4>
                <div
                    style="border: 2px dashed var(--glass-border); padding: 20px; text-align: center; border-radius: 8px; cursor: pointer; position: relative;">
                    <input type="file" id="upload-template" accept="image/*,application/pdf"
                        style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: var(--text-muted);"></i>
                    <p style="margin: 5px 0 0; font-size: 0.8em;">
                        <?php echo $lang === 'ar' ? 'سحب وإفلات صورة أو PDF' : 'Drop Image or PDF here'; ?>
                    </p>
                </div>
            </div>

            <!-- 2. Text Styling -->
            <div class="control-group">
                <h4 style="margin-bottom:10px;color:var(--accent-teal);"><i class="fas fa-font"></i>
                    <?php echo $lang === 'ar' ? 'تنسيق الاسم' : 'Name Style'; ?></h4>

                <div style="display:grid; grid-template-columns: 1fr 50px; gap: 10px; margin-bottom: 10px;">
                    <select id="font-family" class="glass-input">
                        <option value="Arial">Arial</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Cairo">Cairo (Arabic)</option>
                        <option value="Tajawal">Tajawal (Arabic)</option>
                        <!-- Special Font -->
                        <option value="TheYearofHandicrafts-Regular">Handicrafts Era</option>
                    </select>
                    <div class="color-wrapper" style="position:relative;overflow:hidden;border-radius:8px;">
                        <input type="color" id="font-color" value="#000000"
                            style="position:absolute;top:-50%;left:-50%;width:200%;height:200%;cursor:pointer;border:none;padding:0;margin:0;">
                    </div>
                </div>

                <label
                    style="font-size: 0.8em; display:block; margin-bottom:5px;"><?php echo $lang === 'ar' ? 'حجم الخط' : 'Font Size'; ?></label>
                <input type="range" id="font-size" min="20" max="200" value="50">

                <div style="margin-top: 10px;">
                    <label for="upload-font" class="glass-btn btn-sm"
                        style="font-size:0.8em; padding: 5px 10px; width: fit-content;">
                        <i class="fas fa-plus"></i> <?php echo $lang === 'ar' ? 'ارفع خط خاص' : 'Upload Font'; ?>
                    </label>
                    <input type="file" id="upload-font" accept=".ttf,.otf,.woff" style="display:none;">
                </div>
            </div>

            <!-- 4. Names Input -->
            <div class="control-group" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h4 style="margin:0;color:var(--accent-teal);"><i class="fas fa-users"></i>
                        <?php echo $lang === 'ar' ? 'قائمة الأسماء' : 'Names List'; ?></h4>
                    <div style="display:flex;gap:5px;">
                        <button class="icon-btn" id="btn-select-all" title="Select All"><i
                                class="fas fa-check-double"></i></button>
                        <button class="icon-btn" id="btn-deselect-all" title="Deselect All"><i
                                class="fas fa-minus-square"></i></button>
                        <span id="count-badge"
                            style="background:var(--accent-purple); padding: 2px 8px; border-radius: 10px; font-size: 0.8em; font-weight: bold;">0</span>
                    </div>
                </div>

                <textarea id="names-input" class="glass-input"
                    placeholder="<?php echo $lang === 'ar' ? 'الصق الأسماء هنا (اسم في كل سطر)' : 'Paste names here'; ?>"
                    style="flex:0 0 80px; resize:none; margin-bottom: 10px;"></textarea>

                <!-- Quick Preview List -->
                <div id="names-list-preview" class="names-list">
                    <!-- Selectable Items -->
                </div>
            </div>

            <!-- Actions -->
            <div style="margin-top: auto; padding-top: 20px;">
                <div style="display:flex;gap:10px;margin-bottom:10px;">
                    <select id="export-format" class="glass-input" style="flex:1;">
                        <option value="jpg">Format: JPG Images</option>
                        <option value="pdf">Format: PDF Files</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;">
                    <button id="btn-export-selected" class="glass-btn primary" style="flex:1;">
                        <i class="fas fa-file-export"></i>
                        <?php echo $lang === 'ar' ? 'تصدير المحدد' : 'Export Selected'; ?>
                    </button>
                    <button id="btn-export-current" class="glass-btn" style="width:50px;"
                        title="Export Current Preview">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- RIGHT MAIN: Canvas -->
        <main class="preview-area">
            <div class="canvas-wrapper">
                <canvas id="cert-canvas"></canvas>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../../assets/js/certificate-app.js"></script>

</body>

</html>