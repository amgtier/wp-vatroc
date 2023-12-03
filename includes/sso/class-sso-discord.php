<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_SSO_Discord extends VATROC_SSO
{
    public const META_KEY_TOKEN = "vatroc_sso_discord_token";
    public const META_KEY_USERDATA = "vatroc_sso_discord_userdata";
    private const DISCORD_TEST_MODE = 'vatroc_discord_test_mode';
    private const DISCORD_OAUTH_URL = 'vatroc_discord_oauth_url';
    private const DISCORD_CLIENT_KEY = 'vatroc_discord_client_key';
    private const DISCORD_CLIENT_SECRET = 'vatroc_discord_client_secret';
    private const DISCORD_BOT_TOKEN = 'vatroc_discord_bot_token';
    private const DISCORD_GUILD_ID = 'vatroc_discord_guild_id';
    private const DISCORD_TEST_GUILD_ID = 'vatroc_discord_test_guild_id';
    private const DISCORD_JOINER_ROLE_ID = 'vatroc_discord_joiner_role_id';
    private const DISCORD_TEST_JOINER_ROLE_ID = 'vatroc_discord_test_joiner_role_id';
    const CONNECTED = 'CONNECTED';
    const NOT_CONNECTED = 'NOT_CONNECTED';
    const INVALID_RESPONSE = 'INVALID_RESPONSE';

    public static function init()
    {
        add_filter("get_avatar_data", "VATROC_SSO_Discord::get_avatar_data", 100, 2);
        add_filter("vatroc_sso_settings", "VATROC_SSO_Discord::settings");
    }

    public static function register_connection($code)
    {
        $token = VATROC_SSO_Discord_API::fetch_user_token($code);
        return VATROC_SSO_DISCORD_API::register_token($token);
    }

    public static function login($code)
    {
        $token_raw = VATROC_SSO_Discord_API::fetch_user_token($code);
        if (!$token_raw) {
            return false;
        }
        $token = json_decode($token_raw, true);
        $user_data = VATROC_SSO_Discord_API::fetch_user_data_from_token($token);
        if ($user_data == VATROC_SSO_Discord_API::INVALID_RESPONSE || !isset($user_data['email'])) {
            return false;
        }

        $email = $user_data['email'];
        $user = get_user_by_email($email);
        if ($user) {
            //TODO: if multiple email points to the same user
            $uid = $user->ID;
        } else {
            $username = $user_data['username'];
            $cnt = 0;
            while (get_user_by('login', $username)) {
                $cnt += 1;
                $username = $user_data['username'] . "-$cnt";
            }
            $display_name = $user_data['global_name'];
            VATROC::log("[discord][add user] $username $email $display_name", "info", "sso");
            $uid = wp_create_user($username, VATROC::generateRandomString(20), $email);
            $userdata = array(
                'ID' => $uid,
                'display_name' => $display_name,
            );
            wp_update_user($userdata);
        }
        VATROC::dangerously_login($uid);
        VATROC_SSO_DISCORD_API::register_token($token_raw);
        return true;
    }

    public static function refresh()
    {
        return VATROC_SSO_DISCORD_API::refresh();
    }

    public static function connect_button($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $redirect_url = VATROC::get_current_url();

        switch (self::check_user($uid)) {
            case self::NOT_CONNECTED:
            case self::INVALID_RESPONSE:
                self::revoke($uid);
                // TODO: should use more proper approach for sso next
                update_user_meta($uid, "vatroc_sso_next", VATROC::get_current_url());
                ob_start();
?>
                <a href=<?php echo self::get_oauth_url(); ?> class="btn btn-primay">Connect Discord</a>
            <?php
                break;
            default:
                ob_start();
            ?>
                <a href="<?php echo get_permalink(VATROC_Shortcode_SSO::PAGE_ID) . "?next=$redirect_url"; ?>&source=discord&action=revoke" class="btn btn-primay">Clean Discord</a>
        <?php
                break;
        }
        return ob_get_clean();
    }

    public static function revoke($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        delete_user_meta($uid, self::META_KEY_TOKEN);
    }


    public static function get_user_token_from_meta($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $token = get_user_meta($uid, self::META_KEY_TOKEN, true);
        return $token;
    }

    public static function check_user($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $tokens = json_decode(self::get_user_token_from_meta($uid), true);
        if ($tokens == null) {
            return self::NOT_CONNECTED;
        }
        return self::CONNECTED;
    }

    private static function render_avatar_internal($avatar, $user_id, $username, $discriminator, $display_name)
    {
        $img_src = null;
        if ($avatar != null) {
            $img_src = "https://cdn.discordapp.com/avatars/$user_id/$avatar.png";
        } else {
            $img_src = "https://www.vatroc.net/wp-content/uploads/2023/05/icon_clyde_blurple_RGB.png";
        }
        ob_start();
        ?>
        <span>
            <span><b>
                    <?php echo $display_name; ?>
                </b></span>
            <img src=<?php echo $img_src; ?> alt="<?php echo $username; ?>" class='b-avatar' />
            <?php echo $discriminator ? '# ' . $discriminator : null; ?>
        </span>
        <?php
        return ob_get_clean();
    }

    public static function render_status_with_avatar($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();

        return self::render_avatar($uid) . self::connect_button($uid);
    }

    public static function render_avatar($uid = null, $show_message = false)
    {
        $uid = $uid ?: get_current_user_ID();

        switch (self::check_user($uid)) {
            case self::NOT_CONNECTED:
                return VATROC::show_message_at_render($show_message, self::NOT_CONNECTED);
            case self::INVALID_RESPONSE:
                return VATROC::show_message_at_render($show_message, self::INVALID_RESPONSE);
        }
        $discord_userdata = VATROC_SSO_Discord_API::get_user_data($uid);
        if ($discord_userdata == VATROC_SSO_Discord_API::INVALID_RESPONSE) {
            self::revoke($uid);
            return VATROC::show_message_at_render($show_message, VATROC_SSO_Discord_API::INVALID_RESPONSE);
        }
        $guild_userdata = VATROC_SSO_Discord_API::fetch_guild_user_data(self::get_guild_id(), $uid);
        $avatar = $discord_userdata['avatar'];
        $user_id = $discord_userdata['id'];
        $username = $discord_userdata['username'];
        $discriminator = $discord_userdata['discriminator'];
        $nick = $guild_userdata['nick'] ?: $username;
        return self::render_avatar_internal($avatar, $user_id, $username, $discriminator, $nick);
    }

    public static function render_guild_user_data($uid = null, $field = null)
    {
        $uid = $uid ?: get_current_user_ID();

        if ($field != null) {
            return VATROC_SSO_Discord_API::fetch_guild_user_data(self::get_guild_id(), $uid)[$field];
        }
        return json_encode(VATROC_SSO_Discord_API::fetch_guild_user_data(self::get_guild_id(), $uid));
    }

    public static function render_channel_list($guild_id)
    {
        return implode(
            "",
            array_map(
                function ($channel) {
                    ob_start();
        ?>
            <p>
                Channel ID:
                <?php echo $channel['id']; ?>
                Channel Name:
                <?php echo $channel['name']; ?>
                Channel Position:
                <?php echo $channel['position']; ?>
            </p>
<?php
                    return ob_get_clean();
                },
                VATROC_SSO_Discord_API::fetch_channel_list("1113138347121057832")
            )
        );
    }

    public static function settings($content = null)
    {
        $option_keys = [
            "DISCORD_TEST_MODE" => self::DISCORD_TEST_MODE,
            "DISCORD_OAUTH_URL" => self::DISCORD_OAUTH_URL,
            "DISCORD_CLIENT_KEY" => self::DISCORD_CLIENT_KEY,
            "DISCORD_CLIENT_SECRET" => self::DISCORD_CLIENT_SECRET,
            "DISCORD_BOT_TOKEN" => self::DISCORD_BOT_TOKEN,
            "DISCORD_GUILD_ID" => self::DISCORD_GUILD_ID,
            "DISCORD_TEST_GUILD_ID" => self::DISCORD_TEST_GUILD_ID,
            "DISCORD_JOINER_ROLE_ID" => self::DISCORD_JOINER_ROLE_ID,
            "DISCORD_TEST_JOINER_ROLE_ID" => self::DISCORD_TEST_JOINER_ROLE_ID,
        ];

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            foreach ($option_keys as $_ => $key) {
                if (isset($_REQUEST[$key])) {
                    update_option($key, $_REQUEST[$key]);
                } else {
                    delete_option($key);
                }
            }
        }
        ob_start();
        VATROC::get_template("admin/templates/settings.php", $option_keys);
        $content = ob_get_clean();
        return $content;
    }

    public static function get_test_mode()
    {
        return get_option(self::DISCORD_TEST_MODE, false);
    }

    public static function get_client_key()
    {
        return get_option(self::DISCORD_CLIENT_KEY);
    }

    public static function get_client_secret()
    {
        return get_option(self::DISCORD_CLIENT_SECRET);
    }

    public static function get_bot_token()
    {
        return get_option(self::DISCORD_BOT_TOKEN);
    }

    public static function get_guild_id()
    {
        return !self::get_test_mode() ? get_option(self::DISCORD_GUILD_ID) : get_option(self::DISCORD_TEST_GUILD_ID);
    }

    public static function get_new_joiner_role_id()
    {
        return !self::get_test_mode() ? get_option(self::DISCORD_JOINER_ROLE_ID) : get_option(self::DISCORD_TEST_JOINER_ROLE_ID);
    }

    public static function get_avatar_data($args, $id_or_email)
    {
        // https://stackoverflow.com/questions/13911452/change-user-avatar-programmatically-in-wordpress
        $id = $id_or_email;
        if (!is_int($id_or_email)) {
            $id = get_user_by('email', $id_or_email)->ID;
        }
        $user_data = VATROC_SSO_Discord_API::get_user_data($id);
        if ($user_data == VATROC_SSO_Discord_API::INVALID_RESPONSE) {
            return $args;
        }
        if (isset($user_data['avatar']) && isset($user_data['id'])) {
            $avatar = $user_data['avatar'];
            $user_id = $user_data['id'];
            // TODO8: centralize this link
            $args['url'] = "https://cdn.discordapp.com/avatars/$user_id/$avatar.png";
        }
        return $args;
    }

    public static function api_test()
    {
        $ret = "";
        if (VATROC::debug_section([1, 2])) {
            $ret .= VATROC_SSO_Discord::connect_button();
            // $res = VATROC_SSO_Discord::remote_get("https://discord.com/api/guilds/1113138347121057832"); // VATROC
            // $res = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/1113138347121057832"); // Test VATROC
            // $res = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/1113138347121057832/members/561538672776708096"); // Test VATROC
            // $res = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/1113138347121057832/members"); // Test VATROC
            $ret .= VATROC_SSO_Discord::get_user_token_from_meta();
            $ret .= json_encode(VATROC_SSO_Discord_API::get_user_data(get_current_user_ID()));
            $ret .= VATROC_SSO_Discord::render_channel_list('1113138347121057832');
            // $ret .= VATROC_SSO_Discord::delete_guild_member('1113138347121057832', 1);
            // $ret .= VATROC_SSO_Discord::add_guild_member('1113138347121057832', 1);
            $ret .= json_encode(VATROC_SSO_Discord_API::fetch_guild_user_data('1113138347121057832', 1));
        }
        return $ret;
    }

    public static function get_oauth_url()
    {
        global $wp;
        $url = home_url($wp->request);
        // TODO: json-fy state
        $oauth_url = VATROC::url_add_param(get_option(self::DISCORD_OAUTH_URL), "state", $url);
        return $oauth_url;
    }

    public static function get_logo_url()
    {
        return plugin_dir_url(VATROC_PLUGIN_FILE) . 'assets/images/discord-white.png';
    }
}

VATROC_SSO_Discord::init();
