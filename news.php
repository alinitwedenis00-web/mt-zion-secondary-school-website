<?php
require_once 'includes/functions.php';
$pageTitle = 'News & Announcements | Mt Zion Secondary School';
$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$article = null;
if ($articleId) {
    $query = $pdo->prepare('SELECT * FROM news WHERE id = ? AND is_published = 1 LIMIT 1');
    $query->execute([$articleId]);
    $article = $query->fetch(PDO::FETCH_ASSOC) ?: null;
}
require 'includes/header.php';
?>
<section class="page-banner"><h1>News &amp; Announcements</h1></section>
<section class="section">
    <?php if ($article): ?>
        <article class="article news-full-article">
            <?php if (!empty($article['image'])): ?><img src="<?= e($article['image']) ?>" alt="<?= e($article['title']) ?>"><?php endif; ?>
            
            <h2><?= e($article['title']) ?></h2>
            <?php if (!empty($article['summary'])): ?><p class="news-standfirst"><?= nl2br(e($article['summary'])) ?></p><?php endif; ?>
            <div class="news-body"><?= nl2br(e($article['body'])) ?></div>
            <p><a href="news.php">Back to all news</a></p>
        </article>
    <?php else: ?>
        <div class="cards">
            <?php foreach ($pdo->query('SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC, id DESC') as $item): ?>
                <article class="news-card">
                    <?php if (!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>"><?php endif; ?>
                    <div><h3><?= e($item['title']) ?></h3><p><?= e($item['summary']) ?></p><a class="text-link" href="news.php?id=<?= (int)$item['id'] ?>">Read full story</a></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
