<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class VATROC_SSO
{
    abstract public static function connect_button();
    abstract public static function register($code);
    abstract public static function refresh();
    abstract public static function revoke();
    abstract public static function get_user_token_from_meta();
};
