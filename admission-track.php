<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Track Admission Application | Mt Zion Secondary School';
$application = null;
$history = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $reference = trim($_POST['application_no'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($reference === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter your application reference and the applicant email used during submission.';
    } else {
        $query = $pdo->prepare(
            'SELECT application_no, full_name, class_applying, status, created_at, updated_at, admin_comment '
            . 'FROM admission_enquiries WHERE application_no = ? AND email = ? LIMIT 1'
        );
        $query->execute([$reference, $email]);
        $application = $query->fetch(PDO::FETCH_ASSOC) ?: null;

        if ($application) {
            $historyQuery = $pdo->prepare(
                'SELECT new_status, comment, created_at FROM admission_status_history '
                . 'WHERE application_id = (SELECT id FROM admission_enquiries WHERE application_no = ? LIMIT 1) '
                . 'ORDER BY created_at DESC'
            );
            $historyQuery->execute([$reference]);
            $history = $historyQuery->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = 'We could not find an application matching those details. Check the reference and email address.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <h1>Track Your Admission</h1>
    <p>Enter the application reference and applicant email address to view the latest progress.</p>
</section>
<section class="section">
    <div class="form admin-form" style="max-width:720px;margin:auto">
        <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label>Application reference<input name="application_no" required placeholder="e.g. MZS-2026-1A2B3C4D"></label>
            <label>Applicant email address<input type="email" name="email" required></label>
            <button class="btn" type="submit">Check Progress</button>
        </form>
        <?php if ($application): ?>
            <hr>
            <h2>Application found</h2>
            <p><strong>Reference:</strong> <?= e($application['application_no']) ?></p>
            <p><strong>Applicant:</strong> <?= e($application['full_name']) ?></p>
            <p><strong>Class:</strong> <?= e($application['class_applying']) ?></p>
            <p><strong>Current status:</strong> <span class="status-badge"><?= e($application['status']) ?></span></p>
            <?php if (!empty($application['admin_comment'])): ?><p><strong>School message:</strong> <?= e($application['admin_comment']) ?></p><?php endif; ?>
            <h3>Progress history</h3>
            <ol class="timeline">
                <?php foreach ($history as $item): ?>
                    <li><strong><?= e($item['new_status']) ?></strong><br><?= e($item['comment']) ?><br><small><?= e($item['created_at']) ?></small></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
