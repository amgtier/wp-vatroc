<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_Rest_Who
{
    public static function init()
    {
        add_action('rest_api_init', 'VATROC_Rest_Who::add_api_routes');
    }
    public static function add_api_routes()
    {
        register_rest_route(VATROC_Rest_API::$namespace, 'who/(?P<uid>\d+)', [
            'methods' => 'GET',
            'callback' => "VATROC_Rest_Who::whoisV1",
            'permission_callback' => '__return_true',
        ]);
    }
    public static function whoisV1($request)
    {
        $uid = $request["uid"];
        return [
            "user" => get_user_by("id", $uid),
            "user_profile_src" => get_avatar_url($uid),
            "rating" => VATROC_My::get_vatsim_rating_string($uid),
            "position" => VATROC_My::get_vatroc_position_string($uid),
            "hour_stats" => null, // ["S1" => VATROC_My::get] class-atc.php:38
        ];
    }
}

VATROC_Rest_Who::init();