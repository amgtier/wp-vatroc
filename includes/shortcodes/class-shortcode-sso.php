<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Shortcode_SSO
{
    const PAGE_ID = 4223;
    public static function init()
    {
        add_shortcode('vatroc_sso', 'VATROC_Shortcode_SSO::router');
        add_shortcode('vatroc_sso_status', 'VATROC_Shortcode_SSO::render_status');
        add_shortcode('vatroc_sso_avatar', 'VATROC_Shortcode_SSO::render_avatar');
        add_shortcode('vatroc_sso_connection_list', 'VATROC_Shortcode_SSO::connection_list');
        add_shortcode('vatroc_sso_login', 'VATROC_Shortcode_SSO::render_login');
    }

    public static function router($atts)
    {
        $uid = get_current_user_ID();
        $redirect_path = null;

        if (isset($_REQUEST['source'])) {
            switch ($_REQUEST['source']) {
                case 'discord':
                    // TODO2: modularize logged in flow and non-logged in flow
                    if (is_user_logged_in()) {
                        if (isset($_REQUEST['action']) && $_REQUEST['action'] === "revoke") {
                            VATROC_SSO_Discord::revoke();
                            $redirect_path = isset($_REQUEST['next']) ? $_REQUEST['next'] : get_permalink(VATROC_My::PAGE_ID);
                            break;
                        }
                        if (isset($_REQUEST['code']) && VATROC_SSO_Discord::register($_REQUEST['code'])) {
                            // TODO: should use more proper approach for sso next
                            $next = get_user_meta($uid, "vatroc_sso_next", true);
                            if ($next) {
                                delete_user_meta($uid, "vatroc_sso_next");
                                $redirect_path = $next;
                                break;
                            }
                            $redirect_path = get_permalink(VATROC_My::PAGE_ID);
                            break;
                        }
                    } else {
                        if (isset($_REQUEST['code']) && VATROC_SSO_Discord::login_or_register($_REQUEST['code'])) {
                            $redirect_path = isset($_REQUEST['next']) ? $_REQUEST['next'] : get_permalink(VATROC_My::PAGE_ID);
                            break;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        if ($atts == null && $redirect_path != null) {
            wp_redirect($redirect_path);
            exit();
        }

        $ret = VATROC_SSO_Discord::connect_button();
        if (VATROC::debug_section([1, 2])) {
            // $res = VATROC_SSO_Discord::remote_get("https://discord.com/api/guilds/1113138347121057832"); // VATROC
            // $res = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/1113138347121057832"); // Test VATROC
            // $res = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/1113138347121057832/members/561538672776708096"); // Test VATROC
            // $res = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/1113138347121057832/members"); // Test VATROC
            $ret .= VATROC_SSO_Discord::get_user_token_from_meta();
            $ret .= json_encode(VATROC_SSO_Discord_API::fetch_user_data());
            $ret .= VATROC_SSO_Discord::render_channel_list('1113138347121057832');
            // $ret .= VATROC_SSO_Discord::delete_guild_member('1113138347121057832', 1);
            // $ret .= VATROC_SSO_Discord::add_guild_member('1113138347121057832', 1);
            $ret .= json_encode(VATROC_SSO_Discord_API::fetch_guild_user_data('1113138347121057832', 1));
        }
        return $ret;
    }

    public static function render_avatar($atts)
    {
        $uid = isset($atts["uid"]) ? $atts["uid"] : get_current_user_ID();
        if (isset($atts["provider"])) {
            switch ($atts["provider"]) {
                case 'discord':
                    return VATROC_SSO_Discord::render_avatar($uid);
                default:
            }
        }
    }

    public static function render_status($atts)
    {
        $uid = isset($atts["uid"]) ? $atts["uid"] : get_current_user_ID();
        if (isset($atts["provider"])) {
            switch ($atts["provider"]) {
                case 'discord':
                    return VATROC_SSO_Discord::render_status_with_avatar($uid);
                default:
            }
        }
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

    public static function render_login($atts)
    {
        $logo = plugin_dir_url(VATROC_PLUGIN_FILE) . 'assets/images/discord-white.png';
        $oauth_url = VATROC_SSO_Discord::get_oauth_url();
        if (is_user_logged_in()) {
            // TODO3: centralize / generalize logged in render (or flow).
            return null;
        }
        return VATROC::get_template("includes/shortcodes/templates/sso/login-button.php", [
            "logo" => $logo,
            "oauth_url" => $oauth_url,
        ]);
    }
}

VATROC_Shortcode_SSO::init();