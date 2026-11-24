<?php
require_once '_init.php';

$title = 'Contact Messages';
$message = '';
$notificationInfo = '';
$statuses = ['Received', 'Under Review', 'Replied', 'Resolved'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $delete = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
        $delete->execute([$id]);
        $message = 'Message deleted.';
    } elseif ($action === 'save' && $id > 0) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['message'] ?? '');
        $status = trim($_POST['status'] ?? 'Received');
        $reply = trim($_POST['admin_reply'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $body === '') {
            $message = 'Enter a valid name, email address and message.';
        } elseif (!in_array($status, $statuses, true)) {
            $message = 'Choose a valid message status.';
        } else {
            $find = $pdo->prepare('SELECT status, admin_reply, replied_at FROM contact_messages WHERE id = ?');
            $find->execute([$id]);
            $old = $find->fetch(PDO::FETCH_ASSOC);
            if (!$old) {
                $message = 'Message not found.';
            } else {
                $replyChanged = $old['admin_reply'] !== ($reply ?: null);
                if ($replyChanged && $reply !== '' && in_array($status, ['Received', 'Under Review'], true)) {
                    $status = 'Replied';
                }
                $repliedAt = $replyChanged ? ($reply !== '' ? date('Y-m-d H:i:s') : null) : $old['replied_at'];
                $update = $pdo->prepare(
                    'UPDATE contact_messages SET name=?, email=?, subject=?, message=?, status=?, '
                    . 'admin_reply=?, replied_at=?, updated_at=NOW() WHERE id=?'
                );
                $update->execute([$name, $email, $subject, $body, $status, $reply ?: null, $repliedAt, $id]);

                if ($old['status'] !== $status || $replyChanged) {
                    $history = $pdo->prepare(
                        'INSERT INTO contact_message_history (message_id, old_status, new_status, reply) VALUES (?, ?, ?, ?)'
                    );
                    $history->execute([$id, $old['status'], $status, $reply ?: null]);
                }

                if ($reply !== '' && $replyChanged) {
                    $sent = @mail($email, 'Reply from Mt Zion Secondary School',
                        "Dear {$name},\n\n{$reply}\n\nYour message reference is "
                        . (string)$pdo->query('SELECT reference_code FROM contact_messages WHERE id=' . $id)->fetchColumn()
                        . ".\nMt Zion Secondary School",
                        "From: " . setting('email', 'no-reply@example.com') . "\r\nContent-Type: text/plain; charset=UTF-8"
                    );
                    $notificationInfo = $sent
                        ? 'Reply saved and handed to the server mail service.'
                        : 'Reply saved in the website. Email delivery was not confirmed; configure SMTP/mail hosting to send it reliably.';
                }
                $message = 'Message details updated.';
            }
        }
    }
}

$edit = null;
$history = [];
if (isset($_GET['edit'])) {
    $query = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $query->execute([(int)$_GET['edit']]);
    $edit = $query->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($edit) {
        $query = $pdo->prepare('SELECT * FROM contact_message_history WHERE message_id = ? ORDER BY created_at DESC');
        $query->execute([$edit['id']]);
        $history = $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
require '_layout.php';
?>
<h1>Contact Messages</h1>
<p>Review a message, update its status, edit its details, and send a reply. Every message has a tracking reference.</p>
<?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>
<?php if ($notificationInfo): ?><div class="notice"><?= e($notificationInfo) ?></div><?php endif; ?>

<?php if ($edit): ?>
<section class="admin-form">
    <h2>Review message <?= e($edit['reference_code']) ?></h2>
    <p><strong>Received:</strong> <?= e($edit['created_at']) ?></p>
    <form method="post" class="form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
        <label>Sender name<input name="name" required value="<?= e($edit['name']) ?>"></label>
        <label>Email<input type="email" name="email" required value="<?= e($edit['email']) ?>"></label>
        <label>Subject<input name="subject" value="<?= e($edit['subject']) ?>"></label>
        <label>Original message<textarea name="message" rows="6" required><?= e($edit['message']) ?></textarea></label>
        <label>Message status<select name="status"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $edit['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
        <label>Reply to sender<textarea name="admin_reply" rows="6" placeholder="Write the school's response here"><?= e($edit['admin_reply'] ?? '') ?></textarea></label>
        <button class="btn btn-dark" type="submit">Save changes and send reply</button>
        <a class="btn" href="messages.php">Close review</a>
    </form>
    <h3>Message activity</h3>
    <?php if ($history): ?><ul class="timeline"><?php foreach ($history as $entry): ?><li><strong><?= e($entry['old_status'] ?: 'New') ?> → <?= e($entry['new_status']) ?></strong><br><?= nl2br(e($entry['reply'] ?? '')) ?><br><small><?= e($entry['created_at']) ?></small></li><?php endforeach; ?></ul><?php else: ?><p>No updates recorded yet.</p><?php endif; ?>
</section>
<?php endif; ?>

<h2>All contact messages (<?= count($messages) ?>)</h2>
<div class="table-wrap"><table>
    <thead><tr><th>Received</th><th>Reference</th><th>Sender</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($messages as $item): ?>
        <tr>
            <td><?= e($item['created_at']) ?></td>
            <td><strong><?= e($item['reference_code']) ?></strong></td>
            <td><?= e($item['name']) ?><br><?= e($item['email']) ?></td>
            <td><strong><?= e($item['subject']) ?></strong><br><?= nl2br(e($item['message'])) ?></td>
            <td><?= e($item['status']) ?></td>
            <td>
                <a class="text-link" href="messages.php?edit=<?= (int)$item['id'] ?>">Review / Reply / Edit</a>
                <form method="post" class="inline-form" onsubmit="return confirm('Permanently delete this message?')">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                    <button class="danger-link" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$messages): ?><tr><td colspan="6">No contact messages have been received.</td></tr><?php endif; ?>
    </tbody>
</table></div>

<h2>Admission enquiries</h2>
<div class="table-wrap"><table><thead><tr><th>Date</th><th>Name</th><th>Phone</th><th>Class</th><th>Message</th></tr></thead><tbody>
<?php foreach ($pdo->query('SELECT * FROM admission_enquiries ORDER BY created_at DESC') as $enquiry): ?>
<tr><td><?= e($enquiry['created_at']) ?></td><td><?= e($enquiry['full_name']) ?></td><td><?= e($enquiry['phone']) ?></td><td><?= e($enquiry['class_applying']) ?></td><td><?= e($enquiry['message']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php require '_end.php'; ?>
