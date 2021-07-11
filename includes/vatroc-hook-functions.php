<?php
// https://wp-mix.com/members-only-content-shortcode/
function member_check_shortcode($atts, $content = null) {
	if (is_user_logged_in() && !is_null($content) && !is_feed()) {
		return do_shortcode($content);
	}
	return do_shortcode( '[nextend_social_login provider="facebook"]' );
}
add_shortcode('login_required', 'member_check_shortcode');
