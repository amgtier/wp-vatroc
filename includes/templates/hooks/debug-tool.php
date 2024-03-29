<?php
$current_url = VATROC::get_current_url();
$entrypoint = VATROC_Devtool::$entrypoint;
$use1 = "/?$entrypoint&use_as=1&redirect=$current_url";
$use2 = "/?$entrypoint&use_as=2&redirect=$current_url";
$use503 = "/?$entrypoint&use_as=503&redirect=$current_url";
?>
<div class="debug-tool" style="border: solid 3px #d2d2d2; border-radius: 5px;" id="debug-tool-float">
    <p>Is Admin:
        <?php echo VATROC::is_admin() ? "yes" : "no"; ?>
    </p>
    <p>Page ID: <a href="<?php echo get_permalink(); ?>"><?php echo get_the_ID(); ?></a></p>
    <a href="<?php echo $use1; ?>">Use as 1</a>
    <a href="<?php echo $use2; ?>">Use as 2</a>
    <a href="<?php echo $use503; ?>">Use as 503</a>

    <a href="#" id="debug-tool-close">Close</a>
</div>

<script>
jQuery(document).ready(($) => {
    $("#debug-tool-close").on("click", (event) => {
        $("#debug-tool-float").remove();
    });
});
</script>

<?php
wp_enqueue_style('debug-tool', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/css/hooks/debug-tool.css');