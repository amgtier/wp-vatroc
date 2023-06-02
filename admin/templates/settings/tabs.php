<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <nav class="nav-tab-wrapper">
        <?php foreach( $tabs as $key => $name): ?>
            <a href="?page=<?php echo $page; ?>&tab=<?php echo $key; ?>" class="nav-tab <?php if ($tab === $key) : ?>nav-tab-active<?php endif; ?>">
                <?php echo $name; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="tab-content">
        <?php echo $content; ?>
    </div>
</div>