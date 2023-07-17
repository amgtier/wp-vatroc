<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_Shortcode_Devtool
{
    private static $meta_prefix = "vatroc_";

    public static function init()
    {
        add_shortcode( 'vatroc_debug_section', 'VATROC_Shortcode_Devtool::render_debug_section' );
    }

    public static function render_debug_section( $atts, $content ){
        if (VATROC::debug_section()){
            return $content;
        }
        return null;
    }
}

VATROC_Shortcode_Devtool::init();
