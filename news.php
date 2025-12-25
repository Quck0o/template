<?php
$page_title = "Новости - Колледж";
require_once 'header.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$news = $pdo->query("SELECT n.*, u.full_name FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE is_published = TRUE ORDER BY created_at DESC")->fetchAll();
?>

<div class="card">
    <h2>Новости колледжа</h2>
</div>

<?php if ($news): ?>
    <?php foreach ($news as $item): ?>
    <div class="card" id="news-<?php echo $item['id']; ?>">
        <h3><?php echo escape($item['title']); ?></h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
            <?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?> | 
            Автор: <?php echo escape($item['full_name']); ?> | 
            Категория: 
            <span class="badge <?php 
                echo $item['category'] == 'important' ? 'badge-warning' : 
                     ($item['category'] == 'events' ? 'badge-success' : 'badge-primary'); 
            ?>">
                <?php
                $categories = [
                    'general' => 'Общее',
                    'academic' => 'Учебное',
                    'events' => 'Мероприятия',
                    'important' => 'Важное'
                ];
                echo $categories[$item['category']];
                ?>
            </span>
        </p>
        <div style="line-height: 1.8;">
            <?php echo nl2br(escape($item['content'])); ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <p style="text-align: center; color: #666; padding: 20px;">Новостей пока нет</p>
    </div>
<?php endif; ?>

</div>
</body>
</html>