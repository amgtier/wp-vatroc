<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_SSO_Discord extends VATROC_SSO
{
    private const URL_TOKEN = "https://discord.com/api/oauth2/token";
    public const META_KEY_TOKEN = "vatroc_sso_discord_token";
    public const META_KEY_USERDATA = "vatroc_sso_discord_userdata";
    private const URL_API = "https://discord.com/api";
    const CONNECTED = 'CONNECTED';
    const NOT_CONNECTED = 'NOT_CONNECTED';
    const INVALID_RESPONSE = 'INVALID_RESPONSE';
    private const DISCORD_TEST_MODE = 'vatroc_discord_test_mode';
    private const DISCORD_OAUTH_URL = 'vatroc_discord_oauth_url';
    private const DISCORD_CLIENT_KEY = 'vatroc_discord_client_key';
    private const DISCORD_CLIENT_SECRET = 'vatroc_discord_client_secret';
    private const DISCORD_BOT_TOKEN = 'vatroc_discord_bot_token';
    private const DISCORD_GUILD_ID = 'vatroc_discord_guild_id';
    private const DISCORD_TEST_GUILD_ID = 'vatroc_discord_test_guild_id';
    private const DISCORD_JOINER_ROLE_ID = 'vatroc_discord_joiner_role_id';
    private const DISCORD_TEST_JOINER_ROLE_ID = 'vatroc_discord_test_joiner_role_id';

    public static function init()
    {
        add_filter("vatroc_sso_settings", "VATROC_SSO_Discord::settings");
    }

    public static function connect_button()
    {
        ob_start();
?>
        <p>
            <a href=<?php echo self::get_oauth_url(); ?> class="btn btn-primay">Connect Discord</a>
            <?php
            if (VATROC::debug_section([1, 2])) {
                ob_start();
            ?>
                <a href="<?php echo get_permalink(); ?>?source=discord&action=revoke" class="btn btn-primay">Clean Discord</a>
            <?php
            }
            ?>
        </p>
    <?php
        return ob_get_clean();
    }

    public static function register()
    {
        $code = $_REQUEST['code'];
        $redirect_uri = get_permalink() . '?' . http_build_query([
            'source' => 'discord',
        ]);

        $response = wp_remote_post(
            self::URL_TOKEN,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => [
                    'client_id' => self::get_client_key(),
                    'client_secret' => self::get_client_secret(),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirect_uri,
                ]
            ]
        );
        if (isset($response['response'])) {
            if ($response['response']['code'] === 200) {
                update_user_meta(get_current_user_ID(), self::META_KEY_TOKEN, $response['body']);
                return true;
            } else {
                VATROC::dog('[VATROC_SSO_Discord]');
                VATROC::dog($response['response']);
                VATROC::dog($response['body']);
            }
        }
        return false;
    }

    public static function refresh()
    {
        $tokens = self::get_user_token_from_meta();
        if (!isset($tokens['refresh_tokens'])) {
            return false;
        }

        $response = wp_remote_post(
            self::URL_TOKEN,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => [
                    'client_id' => self::get_client_key(),
                    'client_secret' => self::get_client_secret(),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $tokens['refresh_token'],
                ]
            ]
        );
        if (isset($response['response'])) {
            if ($response['response']['code'] === 200) {
                update_user_meta(get_current_user_ID(), self::META_KEY_TOKEN, $response['body']);
                return true;
            } else {
                VATROC::dog('[VATROC_SSO_Discord]');
                VATROC::dog($response['response']);
                VATROC::dog($response['body']);
            }
        }
        return false;
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
            <span><b><?php echo $display_name; ?></b></span>
            <img src=<?php echo $img_src; ?> alt="<?php echo $username; ?>" class='b-avatar' />
            # <?php echo $discriminator; ?>
        </span>
        <?php
        return ob_get_clean();
    }

    public static function render_avatar($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();

        switch (self::check_user($uid)) {
            case self::NOT_CONNECTED:
                return self::connect_button();
            case self::INVALID_RESPONSE:
                return self::INVALID_RESPONSE;
        }
        $discord_userdata = self::fetch_user_data($uid);
        $guild_userdata = self::fetch_guild_user_data(self::get_guild_id(), $uid);
        $avatar = $discord_userdata['avatar'];
        $user_id = $discord_userdata['id'];
        $username = $discord_userdata['username'];
        $discriminator = $discord_userdata['discriminator'];
        $nick = $guild_userdata['nick'] ?: $username;
        return self::render_avatar_internal($avatar, $user_id, $username, $discriminator, $nick);
    }

    public static function remote_get($uri, $uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $tokens = json_decode(self::get_user_token_from_meta($uid), true);
        if ($tokens == null) {
            return false;
        }

        $token_type = $tokens["token_type"];
        $access_token = $tokens["access_token"];
        $response = wp_remote_get(
            $uri,
            [
                'headers' => [
                    'Authorization' => "$token_type $access_token"
                ],
            ]
        );
        return $response;
    }

    public static function bot_remote_get($uri)
    {
        $access_token = self::get_bot_token();
        $response = wp_remote_get(
            $uri,
            [
                'headers' => [
                    'Authorization' => "Bot $access_token"
                ],
            ]
        );
        return $response;
    }

    public static function fetch_user_data($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $response = self::remote_get(self::URL_API . "/users/@me", $uid);

        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        return self::INVALID_RESPONSE;
    }

    public static function fetch_role_names()
    {
        $guild_id = self::get_guild_id();
        $role_names = json_decode(self::bot_remote_get(self::URL_API . "/guilds/$guild_id/roles")["body"]);
        return $role_names;
    }

    public static function render_guild_user_data($uid = null, $field = null)
    {
        $uid = $uid ?: get_current_user_ID();

        if ($field != null) {
            return self::fetch_guild_user_data(self::get_guild_id(), $uid)[$field];
        }
        return json_encode(self::fetch_guild_user_data(self::get_guild_id(), $uid));
    }

    public static function fetch_guild_user_data($guild_id = null, $uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $user_data = self::fetch_user_data($uid);
        $discord_user_id = $user_data['id'];
        $response = self::bot_remote_get(self::URL_API . "/guilds/$guild_id/members/$discord_user_id", $uid);

        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        if(VATROC::valid_response_code($response, 404)){
            return null;
        }
        return self::INVALID_RESPONSE;
    }

    public static function fetch_channel_list($guild_id)
    {
        $response = VATROC_SSO_Discord::bot_remote_get("https://discord.com/api/guilds/$guild_id/channels");
        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        return self::INVALID_RESPONSE;
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
                Channel ID: <?php echo $channel['id']; ?>
                Channel Name: <?php echo $channel['name']; ?>
                Channel Position: <?php echo $channel['position']; ?>
            </p>
<?php
                    return ob_get_clean();
                },
                VATROC_SSO_Discord::fetch_channel_list("1113138347121057832")
            )
        );
    }

    public static function add_guild_member($guild_id, $uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $tokens = json_decode(self::get_user_token_from_meta($uid), true);
        if ($tokens == null) {
            return false;
        }

        $user_access_token = $tokens["access_token"];
        $user_data = self::fetch_user_data($uid);
        $discord_user_id = $user_data['id'];

        $access_token = self::get_bot_token();
        $body = json_encode([
            'access_token' => $user_access_token,
            'roles' => [self::get_new_joiner_role_id()],
        ]);
        $length = strlen($body);
        $response = wp_remote_request(self::URL_API . "/guilds/$guild_id/members/$discord_user_id", [
            "method" => "PUT",
            "headers" => [
                "Content-Length" => $length,
                "Content-Type" => "application/json",
                "Authorization" => "Bot $access_token",
            ],
            "body" => $body,
        ]);
        return $response["response"]["code"];
    }


    public static function delete_guild_member($guild_id, $uid = null)
    {
        $uid = $uid ?: get_current_user_ID();

        $user_data = self::fetch_user_data($uid);
        $discord_user_id = $user_data['id'];

        $access_token = self::get_bot_token();
        $response = wp_remote_request(self::URL_API . "/guilds/$guild_id/members/$discord_user_id", [
            "method" => "DELETE",
            "headers" => [
                "Authorization" => "Bot $access_token",
            ],
        ]);
        VATROC::dog($response["response"]);
        VATROC::dog($response["body"]);
        return $response["response"]["code"];
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

    private static function get_test_mode()
    {
        return get_option(self::DISCORD_TEST_MODE, false);
    }

    private static function get_client_key()
    {
        return get_option(self::DISCORD_CLIENT_KEY);
    }

    private static function get_client_secret()
    {
        return get_option(self::DISCORD_CLIENT_SECRET);
    }

    private static function get_bot_token()
    {
        return get_option(self::DISCORD_BOT_TOKEN);
    }

    private static function get_guild_id()
    {
        return !self::get_test_mode() ? get_option(self::DISCORD_GUILD_ID) : get_option(self::DISCORD_TEST_GUILD_ID);
    }

    private static function get_new_joiner_role_id()
    {
        return !self::get_test_mode() ? get_option(self::DISCORD_JOINER_ROLE_ID) : get_option(self::DISCORD_TEST_JOINER_ROLE_ID);
    }

    private static function get_oauth_url()
    {
        return get_option(self::DISCORD_OAUTH_URL);
    }
};

VATROC_SSO_Discord::init();
