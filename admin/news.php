<?php
require '_init.php';
$title = 'Manage News';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id=?");
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: news.php?deleted=1');
        exit;
    }
    try {
        $id = (int)($_POST['id'] ?? 0);
        $titleText = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $published = isset($_POST['is_published']) ? 1 : 0;
        if ($titleText === '' || $body === '') throw new RuntimeException('News title and full story are required.');
        $img = upload_image('image');
        if ($id > 0) {
            $sql = "UPDATE news SET title=?, summary=?, body=?, is_published=?, published_at=NOW()" . ($img ? ", image=?" : "") . " WHERE id=?";
            $args = [$titleText, $summary, $body, $published];
            if ($img) $args[] = $img;
            $args[] = $id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($args);
        } else {
            $stmt = $pdo->prepare("INSERT INTO news(title,summary,body,image,is_published) VALUES(?,?,?,?,?)");
            $stmt->execute([$titleText, $summary, $body, $img, $published]);
        }
        header('Location: news.php?saved=1');
        exit;
    } catch (Throwable $e) {
        $msg = $e->getMessage();
    }
}
if (isset($_GET['saved'])) $msg = 'News saved successfully.';
if (isset($_GET['deleted'])) $msg = 'News item deleted.';
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$newsItems = $pdo->query("SELECT * FROM news ORDER BY published_at DESC, id DESC")->fetchAll();
require '_layout.php';
?>
<h1>Manage News</h1>
<?php if ($msg): ?><div class="success"><?=e($msg)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form admin-form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="id" value="<?=(int)($edit['id'] ?? 0)?>">
<label>Title<input required maxlength="200" name="title" value="<?=e($edit['title'] ?? '')?>"></label>
<label>Summary<textarea name="summary"><?=e($edit['summary'] ?? '')?></textarea></label>
<label>Full story<textarea required name="body" rows="6"><?=e($edit['body'] ?? '')?></textarea></label>
<label>Image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
<?php if (!empty($edit['image'])): ?><img class="admin-preview" src="../<?=e($edit['image'])?>" alt="Current news image"><?php endif; ?>
<label><input type="checkbox" name="is_published" value="1" <?=(!isset($edit['is_published']) || $edit['is_published'])?'checked':''?>> Published</label>
<button class="btn btn-dark" type="submit"><?= $edit ? 'Update news' : 'Save news' ?></button>
<?php if ($edit): ?><a class="btn" href="news.php">Cancel edit</a><?php endif; ?>
</form>
<h2>Existing news</h2>
<div class="table-wrap"><table><thead><tr><th>Title</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($newsItems as $item): ?>
<tr><td><?=e($item['title'])?></td><td><?=$item['is_published']?'Published':'Draft'?></td><td>
<a href="news.php?edit=<?=(int)$item['id']?>">Edit</a>
<form method="post" class="inline-form" onsubmit="return confirm('Delete this news item?')">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?=(int)$item['id']?>">
<button class="danger-link" type="submit">Delete</button>
</form></td></tr>
<?php endforeach; ?>
<?php if (!$newsItems): ?><tr><td colspan="3">No news articles yet.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require '_end.php'; ?>
