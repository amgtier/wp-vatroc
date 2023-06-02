<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Event {
    protected static $meta_prefix = "vatroc_";


    public static function init() {
    }


    public static function get_next_events($uid = null) {
        $vote_post_id = 3897;
        $uid = $uid ?: get_current_user_ID();
        $options = VATROC_Shortcode_Poll::get_options( "monthly_availability", $vote_post_id, [], $uid );
        $next_events = [];
        foreach( $options as $date=> $result ){
            if ( VATROC_Poll::get_description( $vote_post_id, $date ) ){
                $next_events[ $date ] = $result;
            }
        }
        return $next_events;
    }
};

VATROC_Event::init();
