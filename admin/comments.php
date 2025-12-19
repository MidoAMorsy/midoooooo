<?php
/**
 * Admin Panel - Comments Management
 */

require_once '../includes/config.php';
requireLogin();

$lang = getCurrentLanguage();
$pageTitle = $lang === 'ar' ? 'التعليقات' : 'Comments';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    $id = (int) ($_POST['id'] ?? 0);

    if (isset($_POST['approve'])) {
        db()->prepare("UPDATE comments SET status = 'approved' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Comment approved');
    }

    if (isset($_POST['spam'])) {
        db()->prepare("UPDATE comments SET status = 'spam' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Comment marked as spam');
    }

    if (isset($_POST['delete'])) {
        db()->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
        setFlash('success', 'Comment deleted');
    }

    if (isset($_POST['bulk_action']) && !empty($_POST['selected'])) {
        $action = $_POST['bulk_action'];
        $ids = array_map('intval', $_POST['selected']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        if ($action === 'approve') {
            db()->prepare("UPDATE comments SET status = 'approved' WHERE id IN ($placeholders)")->execute($ids);
        } elseif ($action === 'spam') {
            db()->prepare("UPDATE comments SET status = 'spam' WHERE id IN ($placeholders)")->execute($ids);
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM comments WHERE id IN ($placeholders)")->execute($ids);
        }
        setFlash('success', 'Bulk action completed');
    }

    redirect(SITE_URL . '/admin/comments.php');
}

// Get comments
$status = $_GET['status'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $where = $status !== 'all' ? "WHERE c.status = '$status'" : '';
    $total = db()->query("SELECT COUNT(*) FROM comments c $where")->fetchColumn();

    $stmt = db()->query("SELECT c.*, p.title_en as post_title, p.slug as post_slug 
                         FROM comments c 
                         LEFT JOIN posts p ON c.post_id = p.id 
                         $where 
                         ORDER BY c.created_at DESC 
                         LIMIT $perPage OFFSET $offset");
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    $comments = [];
    $total = 0;
}

// Get counts
try {
    $counts = [
        'all' => db()->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
        'pending' => db()->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn(),
        'approved' => db()->query("SELECT COUNT(*) FROM comments WHERE status = 'approved'")->fetchColumn(),
        'spam' => db()->query("SELECT COUNT(*) FROM comments WHERE status = 'spam'")->fetchColumn(),
    ];
} catch (PDOException $e) {
    $counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'spam' => 0];
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="content-header">
        <h1><?php echo $lang === 'ar' ? 'إدارة التعليقات' : 'Comments Management'; ?></h1>
    </div>

    <!-- Filter Tabs -->
    <div style="margin-bottom:1.5rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="?status=all" class="btn <?php echo $status === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'الكل' : 'All'; ?> (<?php echo $counts['all']; ?>)
        </a>
        <a href="?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'قيد المراجعة' : 'Pending'; ?> (<?php echo $counts['pending']; ?>)
        </a>
        <a href="?status=approved" class="btn <?php echo $status === 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'موافق عليها' : 'Approved'; ?> (<?php echo $counts['approved']; ?>)
        </a>
        <a href="?status=spam" class="btn <?php echo $status === 'spam' ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $lang === 'ar' ? 'سبام' : 'Spam'; ?> (<?php echo $counts['spam']; ?>)
        </a>
    </div>

    <form method="POST" id="bulk-form">
        <?php echo csrfField(); ?>

        <!-- Bulk Actions -->
        <div style="margin-bottom:1rem;display:flex;gap:0.5rem;align-items:center;">
            <select name="bulk_action" class="form-control" style="width:auto;">
                <option value=""><?php echo $lang === 'ar' ? 'إجراءات جماعية' : 'Bulk Actions'; ?></option>
                <option value="approve"><?php echo $lang === 'ar' ? 'موافقة' : 'Approve'; ?></option>
                <option value="spam"><?php echo $lang === 'ar' ? 'سبام' : 'Mark as Spam'; ?></option>
                <option value="delete"><?php echo $lang === 'ar' ? 'حذف' : 'Delete'; ?></option>
            </select>
            <button type="submit" class="btn btn-secondary"><?php echo $lang === 'ar' ? 'تطبيق' : 'Apply'; ?></button>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                        <th><?php echo $lang === 'ar' ? 'المعلق' : 'Author'; ?></th>
                        <th><?php echo $lang === 'ar' ? 'التعليق' : 'Comment'; ?></th>
                        <th><?php echo $lang === 'ar' ? 'المقال' : 'Post'; ?></th>
                        <th><?php echo $lang === 'ar' ? 'التاريخ' : 'Date'; ?></th>
                        <th style="width:150px;"><?php echo $lang === 'ar' ? 'إجراءات' : 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding:3rem;">
                                <?php echo __('no_results'); ?>
                            </td>
                        </tr>
                    <?php else:
                        foreach ($comments as $comment): ?>
                            <tr
                                style="<?php echo $comment['status'] === 'pending' ? 'background:rgba(255,193,7,0.1);' : ''; ?>">
                                <td><input type="checkbox" name="selected[]" value="<?php echo $comment['id']; ?>"
                                        class="item-checkbox"></td>
                                <td>
                                    <strong><?php echo e($comment['author_name']); ?></strong>
                                    <div class="text-muted" style="font-size:0.8rem;">
                                        <?php echo e($comment['author_email']); ?>
                                    </div>
                                </td>
                                <td style="max-width:300px;">
                                    <div style="margin-bottom:0.25rem;">
                                        <span
                                            class="badge badge-<?php echo $comment['status'] === 'approved' ? 'success' : ($comment['status'] === 'spam' ? 'danger' : 'warning'); ?>">
                                            <?php echo $comment['status']; ?>
                                        </span>
                                    </div>
                                    <?php echo e(truncate($comment['content'], 100)); ?>
                                </td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo e($comment['post_slug']); ?>"
                                        target="_blank">
                                        <?php echo e(truncate($comment['post_title'], 30)); ?>
                                    </a>
                                </td>
                                <td><?php echo timeAgo($comment['created_at']); ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($comment['status'] !== 'approved'): ?>
                                            <form method="POST" style="display:inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit" name="approve" class="btn btn-icon btn-success"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($comment['status'] !== 'spam'): ?>
                                            <form method="POST" style="display:inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit" name="spam" class="btn btn-icon btn-warning" title="Spam">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
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
    </form>

    <?php
    $pagination = paginate($total, $perPage, $page);
    echo paginationHTML($pagination, SITE_URL . '/admin/comments.php?status=' . $status . '&');
    ?>
</div>

<script>
    document.getElementById('select-all').addEventListener('change', function () {
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
    });
</script>

<?php include 'includes/admin-footer.php'; ?>