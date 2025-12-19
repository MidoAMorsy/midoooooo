<?php
/**
 * Certificate QR Code Generator
 */

require_once '../../includes/config.php';

$lang = getCurrentLanguage();

$seoOptions = [
    'title' => __('qr_generator'),
    'description' => $lang === 'ar' ? 'إنشاء رموز QR للشهادات' : 'Generate QR codes for certificates',
];

include '../../includes/header.php';
?>

<section class="hero" style="min-height:40vh;">
    <div class="container">
        <div class="text-center" style="color:#fff;padding-top:4rem;">
            <div class="hero-badge"><i class="fas fa-qrcode"></i></div>
            <h1 class="hero-title"><?php echo __('qr_generator'); ?></h1>
            <p class="hero-subtitle"><?php echo $lang === 'ar' ? 'إنشاء رموز QR مخصصة' : 'Create custom QR codes'; ?>
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-2" style="gap:3rem;">
            <div class="card" style="padding:1.5rem;">
                <h3><?php echo $lang === 'ar' ? 'إنشاء رمز QR' : 'Generate QR Code'; ?></h3>
                <form id="qr-form" style="margin-top:1.5rem;">
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'النوع' : 'Type'; ?></label>
                        <select id="qr-type" class="form-control">
                            <option value="url">URL</option>
                            <option value="text"><?php echo $lang === 'ar' ? 'نص' : 'Text'; ?></option>
                            <option value="certificate"><?php echo $lang === 'ar' ? 'شهادة' : 'Certificate'; ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'البيانات' : 'Data'; ?></label>
                        <textarea id="qr-data" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'الحجم' : 'Size'; ?></label>
                        <select id="qr-size" class="form-control">
                            <option value="200">200x200</option>
                            <option value="300" selected>300x300</option>
                            <option value="400">400x400</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-qrcode"></i> <?php echo $lang === 'ar' ? 'إنشاء' : 'Generate'; ?>
                    </button>
                </form>
            </div>
            <div class="card" style="padding:1.5rem;text-align:center;">
                <h3><?php echo $lang === 'ar' ? 'معاينة' : 'Preview'; ?></h3>
                <div id="qr-preview"
                    style="padding:2rem;margin-top:1rem;background:var(--gray-50);border-radius:var(--radius-lg);">
                    <i class="fas fa-qrcode" style="font-size:6rem;color:var(--gray-300);"></i>
                </div>
                <div id="qr-actions" style="margin-top:1rem;display:none;">
                    <button class="btn btn-primary" onclick="downloadQR()"><i class="fas fa-download"></i>
                        <?php echo $lang === 'ar' ? 'تحميل' : 'Download'; ?></button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const API_URL = 'ajax.php';

    document.getElementById('qr-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = document.getElementById('qr-data').value.trim();
        const size = document.getElementById('qr-size').value;
        const type = document.getElementById('qr-type').value;

        if (!data) { showToast('Please enter data', 'error'); return; }

        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;

        // 1. Generate QR URL
        const qrUrl = `https://chart.googleapis.com/chart?chs=${size}x${size}&cht=qr&chl=${encodeURIComponent(data)}&choe=UTF-8`;

        // 2. Save to history via AJAX
        const formData = new FormData();
        formData.append('action', 'save_qr');
        formData.append('data', data);
        formData.append('type', type);
        formData.append('url', qrUrl);

        fetch(API_URL, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                btn.innerHTML = originalText;
                btn.disabled = false;

                if (res.status === 'success') {
                    document.getElementById('qr-preview').innerHTML = `<img src="${qrUrl}" alt="QR Code" id="generated-qr">`;
                    document.getElementById('qr-actions').style.display = 'block';
                    showToast('QR Code generated and saved to history', 'success');
                } else {
                    showToast(res.message || 'Error saving QR code', 'error');
                }
            })
            .catch(err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                // Fallback: Show QR even if save failed
                document.getElementById('qr-preview').innerHTML = `<img src="${qrUrl}" alt="QR Code" id="generated-qr">`;
                document.getElementById('qr-actions').style.display = 'block';
                showToast('Generated, but failed to save history', 'warning');
            });
    });

    function downloadQR() {
        const img = document.getElementById('generated-qr');
        if (img) {
            const a = document.createElement('a');
            a.href = img.src;
            a.download = 'qr-code.png';
            a.click();
        }
    }

    function showToast(msg, type) {
        const div = document.createElement('div');
        div.style.cssText = `position:fixed;bottom:20px;right:20px;background:${type === 'error' ? '#d32f2f' : '#333'};color:#fff;padding:1rem 2rem;border-radius:8px;z-index:9999;animation:fadeIn 0.3s;`;
        div.textContent = msg;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
</script>

<?php include '../../includes/footer.php'; ?>