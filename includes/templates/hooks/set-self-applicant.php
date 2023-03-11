<?php 
    /*
    * Usage example: <?php VATROC::get_template( "includes/shortcodes/templates/poll/response-buttons.php", [ "result" => $result, "option" => "2023/2/2" ] ) ?>
    */
?>

<div class="nowrap">
    <?php if (VATROC_My::get_vatroc_position() == 0): ?>
        <button type="button" class="btn-primary" name="" id="set-self-applicant">Click me to register!</button>
    <?php else: ?>
        <button type="button" class="btn-default" name="" disabled>You are registerd!</button>
    <?php endif; ?>
</div>

<?php
    wp_enqueue_script( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/js/hooks/set-self-applicant.js', array( 'jquery' ), null, true );
    VATROC::enqueue_ajax_object( 'vatroc-poll', $page_id );
?>