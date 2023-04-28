<?php
// https://wp-mix.com/members-only-content-shortcode/
add_shortcode( 'vatroc_login_required', 'member_check_shortcode' );
function member_check_shortcode( $atts, $content = null ) {
	$login_shortcodes = [
		'[nextend_social_login provider="facebook"]',
		'[magic_login]'
	];

	if ( is_user_logged_in() && !is_null($content) && !is_feed() ) {
		return do_shortcode( $content );
	}
	return do_shortcode( implode( '<br /> Or <br />', $login_shortcodes ) );
}


add_action( 'wp_after_admin_bar_render', 'debug_tool' );
function debug_tool() {
	if ( VATROC::debug_section() ) {
		echo VATROC::get_template( "includes/templates/hooks/debug-tool.php" );
	}
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
	echo VATROC::get_template( "includes/templates/hooks/set-self-applicant.php" );
	return do_shortcode( ob_get_clean() );
}


add_shortcode( 'vatroc_set_me_up_applicant', 'set_me_up_applicant' );
function set_me_up_applicant() {
	if ( !is_user_logged_in() ){
		return do_shortcode();
	}
	
	$ret = "";
	$uid = get_current_user_ID();
	$ret .= VATROC_My::html_my_avatar( $uid );
	if ( VATROC::debug_section() ){
		return do_shortcode( 'skipped' );
	}

	if ( VATROC::debug_section( 503 ) ){
		$ajax_url_unset_me = admin_url( 'admin-ajax.php' ) . '?action=vatroc_unset_me_applicant';
		$ret .= "<a class='btn btn-default' href='$ajax_url_unset_me'>Unset me</a>";
	}
	$new_position = -1;
	if ( VATROC_My::get_vatroc_position( $uid ) == 0 ){
		VATROC_My::set_vatroc_position( $uid, $new_position );
		$ret .= '<div class="btn btn-primary">' . VATROC_Constants::$atc_position[ $new_position ] . ' </div>';
	}
	$ret .= "<p>You are set up.</p>";

	return do_shortcode( $ret );
}

add_action( "wp_ajax_vatroc_unset_me_applicant", "unset_me_applicant" );
function unset_me_applicant() {
	VATROC_My::set_vatroc_position( get_current_user_ID(), 0 );
	wp_redirect( $_SERVER[ HTTP_REFERER ]);
}


add_action( 'wp_enqueue_scripts', 'load_customize_scripts');
function load_customize_scripts() {
    wp_enqueue_style( 'vatroc-event-calendar', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/css/event-calendar.css' );
}

add_filter( 'wp_die_ajax_handler', function( $handler ) {

    return function( $message, $title, $args ) use ( $handler ) {

        if ( isset( $args['response'] ) && $args['response'] == 500 ) {
            header( "HTTP/1.1 500 $title" );
            die( $message );
        }

        $handler( $message );
    };
});