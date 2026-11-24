<?php
require_once '_init.php';
$title = 'Manage Gallery';
$message = '';
$categories = ['students' => 'Students', 'agriculture' => 'Agriculture', 'sports' => 'Sports'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete') {
        $delete = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
        $delete->execute([$id]);
        $message = 'Photo deleted.';
    } else {
        $caption = trim($_POST['caption'] ?? '');
        $category = $_POST['category'] ?? 'students';
        if (!isset($categories[$category])) $category = 'students';
        try {
            $image = upload_image('image');
            if ($id > 0) {
                $sql = 'UPDATE gallery SET caption = ?, category = ?' . ($image ? ', image = ?' : '') . ' WHERE id = ?';
                $params = [$caption, $category];
                if ($image) $params[] = $image;
                $params[] = $id;
                $pdo->prepare($sql)->execute($params);
                $message = 'Photo updated.';
            } else {
                if (!$image) throw new RuntimeException('Choose an image first.');
                $pdo->prepare('INSERT INTO gallery (image, caption, category) VALUES (?, ?, ?)')->execute([$image, $caption, $category]);
                $message = 'Photo added to the selected gallery category.';
            }
        } catch (Throwable $error) {
            $message = $error->getMessage();
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $query = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $query->execute([(int)$_GET['edit']]);
    $edit = $query->fetch(PDO::FETCH_ASSOC) ?: null;
}
$photos = $pdo->query('SELECT * FROM gallery ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
require '_layout.php';
?>
<h1>Manage Gallery</h1>
<?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form admin-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Caption<input name="caption" required maxlength="200" value="<?= e($edit['caption'] ?? '') ?>"></label>
    <label>Gallery category<select name="category"><?php foreach ($categories as $value => $label): ?><option value="<?= e($value) ?>" <?= ($edit['category'] ?? 'students') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
    <label>Photo<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $edit ? '' : 'required' ?>></label>
    <?php if (!empty($edit['image'])): ?><img class="admin-preview" src="../<?= e($edit['image']) ?>" alt="Current photo"><?php endif; ?>
    <button class="btn btn-dark" type="submit"><?= $edit ? 'Update photo' : 'Upload photo' ?></button>
    <?php if ($edit): ?><a class="btn" href="gallery.php">Cancel edit</a><?php endif; ?>
</form>
<div class="gallery-grid">
<?php foreach ($photos as $photo): ?>
    <div>
        <img src="../<?= e($photo['image']) ?>" alt="<?= e($photo['caption']) ?>">
        <p><strong><?= e($photo['caption']) ?></strong><br><?= e($categories[$photo['category'] ?? 'students'] ?? 'Students') ?></p>
        <a href="gallery.php?edit=<?= (int)$photo['id'] ?>">Edit</a>
        <form method="post" class="inline-form" onsubmit="return confirm('Delete this photo?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$photo['id'] ?>">
            <button class="danger-link" type="submit">Delete</button>
        </form>
    </div>
<?php endforeach; ?>
</div>
<?php require '_end.php'; ?>
