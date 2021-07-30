<?php
// https://wp-mix.com/members-only-content-shortcode/
function member_check_shortcode($atts, $content = null) {
	if (is_user_logged_in() && !is_null($content) && !is_feed()) {
		return do_shortcode($content);
	}
	return do_shortcode( '[nextend_social_login provider="facebook"]' );
}
add_shortcode('login_required', 'member_check_shortcode');

add_action( 'wp_enqueue_scripts', 'load_customize_scripts');
function load_customize_scripts() {
    wp_enqueue_style( 'vatroc-event-calendar', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/css/event-calendar.css' );
}

