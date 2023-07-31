<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class VATROC_SSO
{
    const PROVIDER_LIST = ["discord" => "VATROC_SSO_Discord"];
    
    abstract public static function connect_button();
    abstract public static function get_logo_url();
    abstract public static function get_oauth_url();
    abstract public static function get_user_token_from_meta();
    abstract public static function login($code);
    abstract public static function refresh();
    abstract public static function register_connection($code);
    abstract public static function revoke();

    public static function default_redirect_page(){
        return VATROC_My::PAGE_ID;
    }
    
    public static function can_register_user(){
        return true;
    }

    public static function register_user_if_not_found(){
        return true;
    }

    public static function enabled(){
        return true;
    }

    public static function get_classname_from($classname){
        return self::PROVIDER_LIST[$classname];
    }
};