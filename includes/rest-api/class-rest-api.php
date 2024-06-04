<?php

if (!defined('ABSPATH')) {
  exit;
}

class VATROC_Rest_API
{
  static string $namespace = "vatroc/v1";

  public static function init()
  {
    include_once(VATROC_ABSPATH . 'includes/rest-api/mission-control/class-rest-mission-control.php');
    include_once(VATROC_ABSPATH . 'includes/rest-api/class-rest-who.php');
    include_once(VATROC_ABSPATH . 'includes/rest-api/class-rest-utils.php');
    add_action('rest_api_init', 'VATROC_Rest_API::add_api_routes');  
    VATROC_Rest_Mission_Control::init();
    VATROC_Rest_Who::init();
  }
  
  public static function add_api_routes()
  {
    register_rest_route(self::$namespace, 'who_am_i', [
      'methods' => 'GET',
      'callback' => "VATROC_Rest_API::who_am_i",
      'permission_callback' => '__return_true',
    ]);
  }

  public static function who_am_i()
  {
    return ["user" => wp_get_current_user(), "user_profile_src" => get_avatar_url(get_current_user_ID())];
  }
}