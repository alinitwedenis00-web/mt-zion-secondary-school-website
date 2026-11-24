<?php
require_once '_init.php';

$sections = [
    'slides' => [
        'label' => 'Homepage Slides', 'table' => 'slides',
        'fields' => [
            'title' => 'Title', 'subtitle' => 'Subtitle', 'button_text' => 'Button text',
            'button_link' => 'Button link', 'sort_order' => 'Display order',
        ],
        'image' => true,
    ],
    'achievers' => [
        'label' => 'Academic Giants', 'table' => 'achievers',
        'fields' => [
            'category' => 'Examination (UACE or UCE)', 'name' => 'Student name',
            'achievement' => 'Achievement / points', 'sort_order' => 'Display order',
        ],
        'image' => true,
    ],
    'staff' => [
        'label' => 'Staff Profiles', 'table' => 'staff',
        'fields' => [
            'name' => 'Full name', 'role' => 'Position / role',
            'bio' => 'Biography', 'sort_order' => 'Display order',
        ],
        'image' => true,
    ],
    'board' => [
        'label' => 'Board Members', 'table' => 'board_members',
        'fields' => [
            'name' => 'Full name', 'role' => 'Board position',
            'bio' => 'Biography', 'sort_order' => 'Display order',
        ],
        'image' => true,
    ],
    'services' => [
        'label' => 'Academic Services', 'table' => 'services',
        'fields' => [
            'title' => 'Title', 'description' => 'Description', 'sort_order' => 'Display order',
        ],
        'image' => false,
    ],
    'portfolio' => [
        'label' => 'School Portfolio / Graduates', 'table' => 'portfolio',
        'fields' => [
            'title' => 'Graduate name',
            'description' => 'Course / qualification / description',
            'sort_order' => 'Display order',
        ],
        'image' => true,
    ],
    'stats' => [
        'label' => 'Student Statistics', 'table' => 'student_statistics',
        'fields' => [
            'class_name' => 'Class', 'boys' => 'Number of boys',
            'girls' => 'Number of girls', 'total' => 'Total students',
            'sort_order' => 'Display order',
        ],
        'image' => false,
    ],
];

$type = $_GET['type'] ?? 'slides';
if (!isset($sections[$type])) {
    http_response_code(404);
    exit('Invalid management section.');
}

$config = $sections[$type];
$table = $config['table'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $delete = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $delete->execute([$id]);
        $message = 'Record deleted.';
    } else {
        $values = [];
        foreach ($config['fields'] as $field => $label) {
            $values[$field] = trim((string)($_POST[$field] ?? ''));
        }

        if ($type === 'achievers' && !in_array($values['category'], ['UACE', 'UCE'], true)) {
            $message = 'Select UACE or UCE for the examination category.';
        }

        if ($type === 'stats') {
            $values['boys'] = max(0, (int)$values['boys']);
            $values['girls'] = max(0, (int)$values['girls']);
            $values['total'] = $values['boys'] + $values['girls'];
        }

        $image = null;
        if ($config['image'] && !$message) {
            try {
                $image = upload_image('image');
            } catch (Throwable $error) {
                $message = $error->getMessage();
            }
        }

        if ($message === '') {
            if ($id > 0) {
                $assignments = [];
                $parameters = [];
                foreach ($values as $field => $value) {
                    $assignments[] = "`{$field}` = ?";
                    $parameters[] = $value;
                }
                if ($image !== null) {
                    $assignments[] = 'image = ?';
                    $parameters[] = $image;
                }
                if ($type === 'slides') {
                    $assignments[] = 'is_active = ?';
                    $parameters[] = isset($_POST['is_active']) ? 1 : 0;
                }
                $parameters[] = $id;
                $sql = "UPDATE `{$table}` SET " . implode(', ', $assignments) . ' WHERE id = ?';
                $pdo->prepare($sql)->execute($parameters);
            } else {
                if ($config['image']) {
                    $values['image'] = $image ?? '';
                }
                if ($type === 'slides') {
                    $values['is_active'] = isset($_POST['is_active']) ? 1 : 0;
                }
                $columns = array_keys($values);
                $columnSql = implode(', ', array_map(static fn($column) => "`{$column}`", $columns));
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $sql = "INSERT INTO `{$table}` ({$columnSql}) VALUES ({$placeholders})";
                $pdo->prepare($sql)->execute(array_values($values));
            }
            $message = 'Record saved successfully. Open the public website to confirm the change.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $find = $pdo->prepare("SELECT * FROM `{$table}` WHERE id = ?");
    $find->execute([(int)$_GET['edit']]);
    $edit = $find->fetch(PDO::FETCH_ASSOC) ?: null;
}

$orderBy = isset($config['fields']['sort_order']) ? 'sort_order, id DESC' : 'id DESC';
$rows = $pdo->query("SELECT * FROM `{$table}` ORDER BY {$orderBy}")->fetchAll(PDO::FETCH_ASSOC);
$title = $config['label'];
require '_layout.php';
?>
<h1><?= e($config['label']) ?></h1>
<p>Manage records that are displayed on the public website.</p>
<?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form admin-form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <input type="hidden" name="action" value="save">
    <h2><?= $edit ? 'Edit record' : 'Add new record' ?></h2>

    <?php foreach ($config['fields'] as $field => $label): ?>
        <label>
            <?= e($label) ?>
            <?php if (in_array($field, ['subtitle', 'bio', 'description'], true)): ?>
                <textarea name="<?= e($field) ?>" rows="4"><?= e($edit[$field] ?? '') ?></textarea>
            <?php elseif ($field === 'category' && $type === 'achievers'): ?>
                <select name="category"><option value="UACE" <?= ($edit['category'] ?? '') === 'UACE' ? 'selected' : '' ?>>UACE</option><option value="UCE" <?= ($edit['category'] ?? '') === 'UCE' ? 'selected' : '' ?>>UCE</option></select>
            <?php else: ?>
                <input
                    type="<?= in_array($field, ['sort_order', 'boys', 'girls', 'total'], true) ? 'number' : 'text' ?>"
                    name="<?= e($field) ?>"
                    value="<?= e($edit[$field] ?? '') ?>"
                    <?= in_array($field, ['title', 'name', 'class_name'], true) ? 'required' : '' ?>
                >
            <?php endif; ?>
        </label>
    <?php endforeach; ?>

    <?php if ($config['image']): ?>
        <label>Image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
        <?php if (!empty($edit['image'])): ?><img class="admin-preview" src="../<?= e($edit['image']) ?>" alt="Current image"><?php endif; ?>
    <?php endif; ?>

    <?php if ($type === 'slides'): ?>
        <label><input type="checkbox" name="is_active" value="1" <?= (!isset($edit['is_active']) || $edit['is_active']) ? 'checked' : '' ?>> Publish this slide</label>
    <?php endif; ?>

    <button class="btn btn-dark" type="submit">Save</button>
    <?php if ($edit): ?><a class="btn" href="manage.php?type=<?= e($type) ?>">Cancel edit</a><?php endif; ?>
</form>

<h2>Existing records</h2>
<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Preview</th><th>Name / title</th><th>Details</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?php if (!empty($row['image'])): ?><img class="table-thumb" src="../<?= e($row['image']) ?>" alt=""><?php endif; ?></td>
                <td><?= e($row['title'] ?? $row['name'] ?? $row['class_name'] ?? '') ?></td>
                <td><?= e($row['role'] ?? $row['achievement'] ?? $row['description'] ?? $row['subtitle'] ?? '') ?></td>
                <td>
                    <a class="text-link" href="manage.php?type=<?= e($type) ?>&amp;edit=<?= (int)$row['id'] ?>">Edit</a>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this record?')">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button class="danger-link" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5">No records yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require '_end.php'; ?>
