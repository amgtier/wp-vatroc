<?php 
    /*
    * Usage example: <?php VATROC::get_template( "includes/shortcodes/templates/poll/response-buttons.php", [ "result" => $result, "option" => "2023/2/2" ] ) ?>
    */
    $is_superuser = get_current_user_ID() == 1;
?>

<div class="nowrap">
    <?php echo $page_id; ?>
    <button 
        type="button" 
        class="res btn-default 
        <?php echo $result[ "user_accept" ] ? "active" : ""; ?>
        <?php echo ($is_superuser || $result[ "read_only" ]) ? "disabled" : null; ?>
        " 
        value="accept" 
        name="<?php echo $option; ?>"
        <?php echo ($is_superuser || $result[ "read_only" ]) ? "disabled" : null; ?>
    >v</button>
    <button 
        type="button" 
        class="res btn-default 
        <?php echo $result[ "user_tentative" ] ? "active" : ""; ?>
        <?php echo  ($is_superuser || $result[ "read_only" ]) ? "disabled" : null; ?>
        " 
        value="tentative" 
        name="<?php echo $option; ?>"
        <?php echo ($is_superuser || $result[ "read_only" ]) ? "disabled" : null; ?>
    >?</button>
    <button 
        type="button" 
        class="res btn-default 
        <?php echo $result[ "user_reject" ] ? "active" : ""; ?>
        <?php echo ($is_superuser || $result[ "read_only" ]) ? "disabled" : null; ?>
        " 
        value="reject" 
        name="<?php echo $option; ?>"
        <?php echo ($is_superuser || $result[ "read_only" ]) ? "disabled" : null; ?>
    >x</button>
</div>

<?php
    wp_enqueue_script( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/poll.js', array( 'jquery' ), null, true );
    wp_enqueue_style( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/poll.css' );
    VATROC::enqueue_ajax_object( 'vatroc-poll', $page_id );
?>