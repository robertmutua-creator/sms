<?php if (isset($_SESSION['flash'])): ?>
    <?php
    $type = $_SESSION['flash']['type'] ?? 'error';
    $color = (strtolower($type) === 'success') ? 'green' : 'red';
    ?>
    <p style="color: <?= $color ?>; font-style: italic; margin-bottom: 1rem;">
        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
    </p>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>