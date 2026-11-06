<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'SMS' ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>

<body>
    <header>
        <div class="title">
            <h2><?= isset($title) ? $title : 'SMS' ?></h2>
        </div>
        <div class="action">
            <a href="<?= isset($user) ? '/logout' : '/login' ?>"><?= isset($user) ? 'Logout' : 'Login' ?></a>
        </div>
    </header>
    <main>
        <?php if (isset($user) && isset($links)): ?>
            <div class="sidebar">
                <?php foreach ($links as $link): ?>
                    <a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['label']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="main">

            <h3><?= isset($moduleTitle) ? $moduleTitle : '--' ?></h3>
            <?php require_once __DIR__ . '/alert.php'; ?>

            <?php if (isset($content)): ?>
                <?php require_once __DIR__ . '/../Views/' . $content . '.php'; ?>
            <?php else: ?>
                No content set
            <?php endif; ?>
        </div>
    </main>
    <footer>
    </footer>
</body>

</html>