<?php
/**
 * Admin Panel - Messages Management
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$pageTitle = __('messages');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $id = (int) ($_POST['id'] ?? 0);

    if (isset($_POST['mark_read'])) {
        db()->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Message marked as read');
    }

    if (isset($_POST['delete'])) {
        db()->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        setFlash('success', 'Message deleted');
    }

    redirect(SITE_URL . '/admin/messages.php');
}

// Get messages
$status = $_GET['status'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $where = $status !== 'all' ? "WHERE status = '$status'" : '';
    $total = db()->query("SELECT COUNT(*) FROM contact_messages $where")->fetchColumn();

    $stmt = db()->query("SELECT * FROM contact_messages $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $messages = [];
    $total = 0;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1><?php echo __('messages'); ?></h1>
            <p><?php echo $lang === 'ar' ? 'رسائل الاتصال الواردة' : 'Incoming contact messages'; ?></p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div style="margin-bottom:1.5rem;display:flex;gap:0.5rem;">
        <a href="?status=all" class="btn <?php echo $status === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'الكل' : 'All'; ?>
        </a>
        <a href="?status=unread" class="btn <?php echo $status === 'unread' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'غير مقروءة' : 'Unread'; ?>
        </a>
        <a href="?status=read" class="btn <?php echo $status === 'read' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'مقروءة' : 'Read'; ?>
        </a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px;"></th>
                    <th><?php echo $lang === 'ar' ? 'المرسل' : 'Sender'; ?></th>
                    <th><?php echo __('subject'); ?></th>
                    <th><?php echo $lang === 'ar' ? 'الرسالة' : 'Message'; ?></th>
                    <th><?php echo $lang === 'ar' ? 'التاريخ' : 'Date'; ?></th>
                    <th style="width:120px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding:3rem;">
                            <?php echo __('no_results'); ?>
                        </td>
                    </tr>
                <?php else:
                    foreach ($messages as $msg): ?>
                        <tr style="<?php echo $msg['status'] === 'unread' ? 'background:rgba(0,102,204,0.03);' : ''; ?>">
                            <td>
                                <?php if ($msg['status'] === 'unread'): ?>
                                    <span
                                        style="display:inline-block;width:10px;height:10px;background:var(--primary);border-radius:50%;"></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo e($msg['name']); ?></strong>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    <a href="mailto:<?php echo e($msg['email']); ?>"><?php echo e($msg['email']); ?></a>
                                    <?php if ($msg['phone']): ?>
                                        <br><?php echo e($msg['phone']); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo e($msg['subject'] ?: '-'); ?></td>
                            <td style="max-width:300px;">
                                <span title="<?php echo e($msg['message']); ?>">
                                    <?php echo e(truncate($msg['message'], 80)); ?>
                                </span>
                            </td>
                            <td>
                                <span title="<?php echo $msg['created_at']; ?>">
                                    <?php echo timeAgo($msg['created_at']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-icon btn-secondary"
                                        onclick="viewMessage(<?php echo htmlspecialchars(json_encode($msg), ENT_QUOTES); ?>)"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($msg['status'] === 'unread'): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" name="mark_read" class="btn btn-icon btn-secondary"
                                                title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete this message?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-icon btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    $pagination = paginate($total, $perPage, $page);
    echo paginationHTML($pagination, SITE_URL . '/admin/messages.php?status=' . $status . '&');
    ?>
</div>

<!-- Message Modal -->
<div id="message-modal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:0.75rem;width:90%;max-width:600px;max-height:90vh;overflow:auto;">
        <div
            style="padding:1.5rem;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;">
            <h3 id="modal-subject"><?php echo __('message'); ?></h3>
            <button onclick="closeModal()"
                style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--gray-500);">&times;</button>
        </div>
        <div style="padding:1.5rem;">
            <div style="margin-bottom:1rem;">
                <strong><?php echo $lang === 'ar' ? 'المرسل' : 'From'; ?>:</strong>
                <span id="modal-sender"></span>
            </div>
            <div style="margin-bottom:1rem;">
                <strong><?php echo __('email'); ?>:</strong>
                <a id="modal-email" href=""></a>
            </div>
            <div id="modal-phone-wrapper" style="margin-bottom:1rem;">
                <strong><?php echo __('phone'); ?>:</strong>
                <span id="modal-phone"></span>
            </div>
            <div style="margin-bottom:1rem;">
                <strong><?php echo $lang === 'ar' ? 'التاريخ' : 'Date'; ?>:</strong>
                <span id="modal-date"></span>
            </div>
            <hr style="margin:1.5rem 0;">
            <div id="modal-message" style="white-space:pre-wrap;line-height:1.7;"></div>
        </div>
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-200);display:flex;gap:0.5rem;">
            <a id="modal-reply" href="" class="btn btn-primary">
                <i class="fas fa-reply"></i>
                <?php echo $lang === 'ar' ? 'رد' : 'Reply'; ?>
            </a>
            <button onclick="closeModal()"
                class="btn btn-secondary"><?php echo $lang === 'ar' ? 'إغلاق' : 'Close'; ?></button>
        </div>
    </div>
</div>

<script>
    function viewMessage(msg) {
        document.getElementById('modal-subject').textContent = msg.subject || '<?php echo $lang === "ar" ? "بدون عنوان" : "No Subject"; ?>';
        document.getElementById('modal-sender').textContent = msg.name;
        document.getElementById('modal-email').textContent = msg.email;
        document.getElementById('modal-email').href = 'mailto:' + msg.email;
        document.getElementById('modal-reply').href = 'mailto:' + msg.email + '?subject=Re: ' + (msg.subject || '');

        if (msg.phone) {
            document.getElementById('modal-phone').textContent = msg.phone;
            document.getElementById('modal-phone-wrapper').style.display = '';
        } else {
            document.getElementById('modal-phone-wrapper').style.display = 'none';
        }

        document.getElementById('modal-date').textContent = msg.created_at;
        document.getElementById('modal-message').textContent = msg.message;

        document.getElementById('message-modal').style.display = 'flex';

        // Mark as read via AJAX
        if (msg.status === 'unread') {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '<?php echo CSRF_TOKEN_NAME; ?>=<?php echo getCSRFToken(); ?>&id=' + msg.id + '&mark_read=1'
            });
        }
    }

    function closeModal() {
        document.getElementById('message-modal').style.display = 'none';
    }

    document.getElementById('message-modal').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
</script>

<?php include 'includes/admin-footer.php'; ?>