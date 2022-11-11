<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_My {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_shortcode( 'vatroc_my_vatroc', 'VATROC_Shortcode_My::output_My' );
    }


    public static function output_my() {
        $ret = "";
        $ret .= self::applicant();
        return $ret;
    }


    private static function applicant() {
        $ret = "";
        $ret .= "<h1>Applicant section</h1>";
        return $ret;
    }

};

VATROC_Shortcode_My::init();
