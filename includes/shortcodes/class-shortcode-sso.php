<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Shortcode_SSO
{
    public static function init()
    {
        add_shortcode('vatroc_sso', 'VATROC_Shortcode_SSO::router');
        add_shortcode('vatroc_sso_connection_list', 'VATROC_Shortcode_SSO::connection_list');
    }

    public static function router()
    {
        $ret = VATROC_SSO_Discord::connect_button();

        $redirect = $_REQUEST['next'];

        if (isset($_REQUEST['source'])) {
            switch ($_REQUEST['source']) {
                case 'discord':
                    if (isset($_REQUEST['action']) && $_REQUEST['action'] === "revoke") {
                        VATROC_SSO_Discord::revoke();
                        return VATROC::return_redirect(get_permalink(VATROC_My::PAGE_ID));
                    }
                    if (VATROC_SSO_Discord::register()) {
                        return VATROC::return_redirect(get_permalink(VATROC_My::PAGE_ID));
                    }
                    break;
                default:
                    break;
            }
        }

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
                                    fn ($role_id) => $role_name_map[$role_id],
                                    VATROC_SSO_Discord::render_guild_user_data($uid, 'roles')
                                )
                            );
                            $ret .= "</p>";
                        }
                    }
                    break;
            };
        }
        return $ret;
    }
}

VATROC_Shortcode_SSO::init();
