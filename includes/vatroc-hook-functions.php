<?php
// https://wp-mix.com/members-only-content-shortcode/
add_shortcode( 'vatroc_login_required', 'member_check_shortcode' );
function member_check_shortcode( $atts, $content = null ) {
	if ( is_user_logged_in() && !is_null($content) && !is_feed() ) {
		return do_shortcode( $content );
	}
	return do_shortcode( '[nextend_social_login provider="facebook"]' );
}


add_shortcode( 'vatroc_collapse' , 'collapse_section' );
function collapse_section( $atts, $content ) {
	$wrapper_start = $wrapper_end = "";
	switch ( $atts[ "wrapper" ] ) {
		case "card":
			$wrapper_start = "<div class='card card-body'>";
			$wrapper_end = "</div>";
			break;
	}

	ob_start()
?>
		<p>
			<button class="btn btn-primary" data-toggle="collapse" data-target="#collapseSection" aria-expanded="false" aria-controls="collapseSection">
				<?php echo $atts[ "label" ] ?>
			</button>
		</p>
		<div class='collapse' id='collapseSection'>
			<?php echo $wrapper_start; ?>
				<?php echo $content; ?>
			<?php echo $wrapper_end; ?>
		</div>
<?php
	return do_shortcode( ob_get_clean() );
}


add_shortcode( 'vatroc_set_self_applicant', 'set_self_applicant' );
function set_self_applicant() {
	ob_start();
	if(VATROC::debug_section( 503 )){
		VATROC::dog("hiya");
		echo VATROC::get_template( "includes/templates/hooks/set-self-applicant.php" );
	}
	return do_shortcode( ob_get_clean() );
}


add_action( 'wp_enqueue_scripts', 'load_customize_scripts');
function load_customize_scripts() {
    wp_enqueue_style( 'vatroc-event-calendar', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/css/event-calendar.css' );
}

