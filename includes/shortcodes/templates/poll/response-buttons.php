<?php 
    /*
    * Usage example: <?php VATROC::get_template( "includes/shortcodes/templates/poll/response-buttons.php", [ "result" => $result, "option" => "2023/2/2" ] ) ?>
    */
?>

<div class="nowrap">
    <?php echo $page_id; ?>
    <button type="button" class="res btn-default <?php echo $result[ "user_accept"] ? "active" : ""; ?>" value="accept" name="<?php echo $option; ?>">v</button>
    <button type="button" class="res btn-default <?php echo $result[ "user_tentative"] ? "active" : ""; ?>" value="tentative" name="<?php echo $option; ?>">?</button>
    <button type="button" class="res btn-default <?php echo $result[ "user_reject"] ? "active" : ""; ?>" value="reject" name="<?php echo $option; ?>">x</button>
</div>

<?php
    wp_enqueue_script( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/poll.js', array( 'jquery' ), null, true );
    wp_enqueue_style( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/poll.css' );
    VATROC::enqueue_ajax_object( 'vatroc-poll', $page_id );
?>