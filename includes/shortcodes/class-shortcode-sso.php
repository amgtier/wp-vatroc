<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Shortcode_SSO
{
    const PAGE_ID = 4223;
    public static function init()
    {
        add_shortcode('vatroc_sso_avatar', 'VATROC_Shortcode_SSO::render_avatar');
        add_shortcode('vatroc_sso_connection_list', 'VATROC_Shortcode_SSO::connection_list');
        add_shortcode('vatroc_sso_login', 'VATROC_Shortcode_SSO::render_login');
        add_shortcode('vatroc_sso_status', 'VATROC_Shortcode_SSO::render_status');
        add_shortcode('vatroc_sso', 'VATROC_Shortcode_SSO::router');
    }

    public static function router($atts)
    {
        $uid = get_current_user_ID();
        $redirect_path = null;
        if (isset($_REQUEST['source'])) {
            $classname = VATROC_SSO::get_classname_from($_REQUEST['source']);

            // TODO: try
            if ($classname != null) {
                if (is_user_logged_in()) {
                    if ($_REQUEST['action'] === "revoke") {
                        $classname::revoke();
                    } else if (isset($_REQUEST['code']) && $classname::register_connection($_REQUEST['code'])) {
                        // TODO: should use more proper approach for sso next
                        $redirect_path = get_user_meta($uid, "vatroc_sso_next", true);
                        delete_user_meta($uid, "vatroc_sso_next");
                    }
                } else {
                    // TODO: add action=login
                    if (isset($_REQUEST['code'])) {
                        $classname::login($_REQUEST['code']);
                    }
                }
                $redirect_path = isset($_REQUEST['next']) ? $_REQUEST['next'] : get_permalink(VATROC_SSO::default_redirect_page());
            }

            // catch: log error and render fallback page
            if ($atts == null && $redirect_path != null) {
                wp_redirect($redirect_path);
                exit();
            }
        }
        $ret = "";
        $ret = VATROC_SSO_Discord::api_test();
        return $ret;
    }

    public static function render_avatar($atts)
    {
        $uid = isset($atts["uid"]) ? $atts["uid"] : get_current_user_ID();
        $classname = VATROC_SSO::get_classname_from($atts["provider"]);
        if($classname == null){
            return null;
        }
        return $classname::render_avatar($uid);
    }

    public static function render_status($atts)
    {
        $uid = isset($atts["uid"]) ? $atts["uid"] : get_current_user_ID();
        $classname = VATROC_SSO::get_classname_from($atts["provider"]);
        if($classname == null){
            return null;
        }
        return $classname::render_status_with_avatar($uid);
    }

    public static function render_login($atts)
    {
        if (is_user_logged_in()) {
            return VATROC_Login::render_logged_in();
        }

        ob_start();
        $cnt = 0;
        foreach(VATROC_SSO::PROVIDER_LIST as $key => $classname){
            if(!$classname::enabled()){
                continue;
            }
            if($cnt > 0){
                echo VATROC_Login::DELIMITER;
            }
            $cnt += 1;
            VATROC::get_template("includes/shortcodes/templates/sso/login-button.php", [
                "logo" => $classname::get_logo_url(),
                "oauth_url" => $classname::get_oauth_url(),
            ]);
        }
        return ob_get_clean();
    }

    public static function connection_list($atts)
    {
        $users = get_users(['fields' => ['ID']]);

        $role_names = VATROC_SSO_Discord_API::fetch_role_names();
        $role_name_map = [];
        foreach ($role_names as $idx => $role) {
            $role_name_map[$role->id] = $role->name;
        }

        $ret = "";
        if (isset($atts['source'])) {
            switch ($atts['source']) {
                case 'discord':
                    $ret .= "<h1>Discord Admin</h1>";
                    foreach ($users as $user) {
                        $uid = $user->ID;
                        if (VATROC_SSO_Discord::check_user($uid) === VATROC_SSO_Discord::CONNECTED) {
                            $ret .= "<p>$uid";
                            $ret .= VATROC_SSO_Discord::render_avatar($uid);
                            $ret .= implode(
                                ", ",
                                array_map(
                                    fn($role_id) => $role_name_map[$role_id],
                                    VATROC_SSO_Discord::render_guild_user_data($uid, 'roles')
                                )
                            );
                            $ret .= "</p>";
                        }
                    }
                    break;
            }
            ;
        }
        return $ret;
    }
}

VATROC_Shortcode_SSO::init();