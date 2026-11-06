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
            <?php if (isset($user)): ?>
                <form action="/logout" method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login">Login</a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <?php if (isset($user) && isset($links)): ?>
            <nav class="sidebar-navigation" aria-label="Main navigation">
                <button class="sidebar-toggle" type="button" aria-expanded="false" aria-controls="sidebar">
                    <span class="hamburger-icon" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span>Menu</span>
                </button>
                <div class="sidebar" id="sidebar">
                    <?php foreach ($links as $link): ?>
                        <a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['label']) ?></a>
                    <?php endforeach; ?>
                </div>
            </nav>
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
        <p>&copy; <?= date('Y') ?> SMS &mdash; CBE Senior School Assessment Management System</p>
    </footer>
    <script>
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('#sidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                const isExpanded = sidebarToggle.getAttribute('aria-expanded') === 'true';
                sidebarToggle.setAttribute('aria-expanded', String(!isExpanded));
                sidebar.classList.toggle('is-open', !isExpanded);
            });
        }
    </script>
</body>

</html>