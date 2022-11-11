<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Poll {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_shortcode( 'vatroc_poll', 'VATROC_Shortcode_Poll::output_poll' );
    }


    public static function output_poll() {
        $ret = "";
        $ret .= sprintf( "<h1>VATROC Poll</h1>" );
        return $ret;
    }

};

VATROC_Shortcode_Poll::init();
