<?php 
    /*
    * Usage example: <?php VATROC::get_template( "includes/shortcodes/templates/poll/response-buttons.php", [ "result" => $result, "option" => "2023/2/2" ] ) ?>
    */
?>

<div class="nowrap">
    <button type="button" class="btn-default" name="" id="set-self-applicant">Start application</button>
</div>

<?php
    wp_enqueue_script( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/js/hooks/set-self-applicant.js', array( 'jquery' ), null, true );
    VATROC::enqueue_ajax_object( 'vatroc-poll', $page_id );
?>