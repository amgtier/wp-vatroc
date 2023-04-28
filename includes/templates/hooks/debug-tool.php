<div class="debug-tool">
    <p>Is Admin: <?php echo VATROC::is_admin() ? "yes" : "no"; ?></p>
    <p>Page ID: <?php echo get_the_ID(); ?></p>
</div>

<?php
    wp_enqueue_style( 'debug-tool', plugin_dir_url( VATROC_PLUGIN_FILE) . 'includes/css/hooks/debug-tool.css' );