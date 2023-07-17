<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Shortcode_Redirect
{
    public static function init()
    {
        add_shortcode('vatroc_redirect', 'VATROC_Shortcode_Redirect::render');
    }

    public static function render($atts)
    {
        $url = $_GET["redirect_to"];
        if(isset($atts["key"])){
            $url = $_GET[ $atts["key"] ];
        }

        if($url){
            if(isset($atts["nonce_key"])){
                $query = parse_url($url, PHP_URL_QUERY);
                if($query){
                    $url .= "&_wpnonce=" . wp_create_nonce($atts["nonce_key"]);
                } else {
                    $url .= "?_wpnonce=" . wp_create_nonce($atts["nonce_key"]);
                }
            }
            if(array_key_exists("pause", $_GET)){
                return;
            }
            return VATROC::return_redirect($url);
        }
    }
}

VATROC_Shortcode_Redirect::init();
