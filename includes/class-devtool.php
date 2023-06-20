<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Devtool
{
    public static $entrypoint = 'devtool';
    public static function init()
    {
        if (in_array(get_current_user_ID(), [1, 2])) {
            add_action('init', 'VATROC_Devtool::use_as');
        }
        add_action('wp_after_admin_bar_render', 'VATROC_Devtool::debug_tool');
    }

    public function debug_tool()
    {
        if (isset($_GET["nodevtool"])) {
            return null;
        }
        if (VATROC::debug_section([1, 2, 503])) {
            echo VATROC::get_template("includes/templates/hooks/debug-tool.php");
        }
    }

    public static function use_as()
    {
        if (
            isset($_GET["devtool"]) &&
            isset($_GET["use_as"])
        ) {
            $uid = intval(sanitize_key($_GET['use_as']));
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user(0);
            wp_set_auth_cookie($uid);
            wp_set_current_user($uid);
            if (isset($_GET["redirect"])) {
                $redirect_url = $_GET["redirect"];
                wp_redirect($redirect_url);
            }
        }
    }
}
VATROC_Devtool::init();