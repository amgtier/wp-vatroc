<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Devtool
{
    public static $entrypoint = 'devtool';
    public static function init()
    {
        if (self::is_priviledged()) {
            add_action('init', 'VATROC_Devtool::use_as');
        }
        add_action('wp_after_admin_bar_render', 'VATROC_Devtool::debug_tool');
    }

    public function debug_tool()
    {
        if (isset($_GET["nodevtool"])) {
            return null;
        }
        if (self::is_priviledged()) {
            echo VATROC::get_template("includes/templates/hooks/debug-tool.php");
        }
    }

    public static function use_as()
    {
        if (!self::is_priviledged()) {
            if (isset($_GET["redirect"])) {
                $redirect_url = $_GET["redirect"];
                wp_redirect($redirect_url);
            } else {
                wp_redirect(get_home_url());
            }
        }

        if (
            isset($_GET["devtool"]) &&
            isset($_GET["use_as"])
        ) {
            $uid = intval(sanitize_key($_GET['use_as']));
            VATROC::dangerously_login($uid);
            if (isset($_GET["redirect"])) {
                $redirect_url = $_GET["redirect"];
                wp_redirect($redirect_url);
            }
        }
    }

    private static function is_priviledged()
    {
        return in_array(get_current_user_ID(), [1, 2, 503]);
    }
}
VATROC_Devtool::init();
