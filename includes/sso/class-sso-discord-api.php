<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_SSO_Discord_API
{

    // TODO: detatch get_user_token_from_meta

    private const URL_TOKEN = "https://discord.com/api/oauth2/token";
    public const META_KEY_TOKEN = "vatroc_sso_discord_token";
    public const META_KEY_DATA = "vatroc_sso_discord_data";
    private const URL_API = "https://discord.com/api";
    const CONNECTED = 'CONNECTED';
    const NOT_CONNECTED = 'NOT_CONNECTED';
    const INVALID_RESPONSE = 'INVALID_RESPONSE';

    public static function init()
    {}

    public static function register_token($token, $uid = null)
    {
        // TODO4: migrate to make $uid required.
        $uid = $uid == null ? get_current_user_ID() : $uid;
        if($token){
            update_user_meta($uid, VATROC_SSO_Discord::META_KEY_TOKEN, $token);
            return true;
        }
        return false;
    }

    public static function refresh()
    {
        $tokens = VATROC_SSO_Discord::get_user_token_from_meta();
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
                    'client_id' => VATROC_SSO_Discord::get_client_key(),
                    'client_secret' => VATROC_SSO_Discord::get_client_secret(),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $tokens['refresh_token'],
                ]
            ]
        );
        if (isset($response['response'])) {
            if ($response['response']['code'] === 200) {
                update_user_meta(get_current_user_ID(), VATROC_SSO_Discord::META_KEY_TOKEN, $response['body']);
                return true;
            } else {
                VATROC::dog('[VATROC_SSO_Discord]');
                VATROC::dog($response['response']);
                VATROC::dog($response['body']);
            }
        }
        return false;
    }

    public static function remote_get($uri, $uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $tokens = json_decode(VATROC_SSO_Discord::get_user_token_from_meta($uid), true);
        if ($tokens == null) {
            return false;
        }
        return self::remote_get_from_token($uri, $tokens);
    }

    public static function remote_get_from_token($uri, $token){
        $token_type = $token["token_type"];
        $access_token = $token["access_token"];
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
        $access_token = VATROC_SSO_Discord::get_bot_token();
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

    public static function fetch_user_token($code){
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
                    'client_id' => VATROC_SSO_Discord::get_client_key(),
                    'client_secret' => VATROC_SSO_Discord::get_client_secret(),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirect_uri,
                ]
            ]
        );
        if (isset($response['response'])) {
            if ($response['response']['code'] === 200) {
                return $response['body'];
            } else {
                VATROC::dog('[VATROC_SSO_Discord]');
                VATROC::dog($response['response']);
                VATROC::dog($response['body']);
                return false;
            }
        }
    }

    public static function get_user_data($uid = null){
        $uid = $uid ?: get_current_user_ID();
        $data = get_user_meta($uid, self::META_KEY_DATA, true);
        if($data == null){
            $ret = self::fetch_user_data($uid);
            update_user_meta($uid, self::META_KEY_DATA, $ret);
        }
        return $data;
    }

    public static function fetch_user_data($uid = null)
    {
        // TODO5: migrate to `get_uesr_data` and $uid becomes required.
        $uid = $uid ?: get_current_user_ID();
        $response = self::remote_get(self::URL_API . "/users/@me", $uid);

        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        // TODO: Return and handle self::401_UNAUTHORIZED
        return self::INVALID_RESPONSE;
    }
    
    public static function fetch_user_data_from_token($token)
    {
        $response = self::remote_get_from_token(self::URL_API . "/users/@me", $token);
        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        // TODO: Return and handle self::401_UNAUTHORIZED
        return self::INVALID_RESPONSE;
    }

    public static function fetch_role_names()
    {
        $guild_id = VATROC_SSO_Discord::get_guild_id();
        $role_names = json_decode(self::bot_remote_get(self::URL_API . "/guilds/$guild_id/roles")["body"]);
        return $role_names;
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

    public static function fetch_guild($guild_id = null)
    {
        $guild_id = $guild_id ?: sVATROC_SSO_Discord::get_guild_id();
        $response = VATROC_SSO_Discord_API::bot_remote_get("https://discord.com/api/guilds/$guild_id?with_count=true");
        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        return self::INVALID_RESPONSE;
    }

    public static function fetch_channel_list($guild_id = null)
    {
        $guild_id = $guild_id ?: VATROC_SSO_Discord::get_guild_id();
        $response = VATROC_SSO_Discord_API::bot_remote_get("https://discord.com/api/guilds/$guild_id/channels");
        if (VATROC::valid_200_response($response)) {
            return json_decode($response['body'], true);
        }
        return self::INVALID_RESPONSE;
    }

    public static function add_guild_member($guild_id, $uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $tokens = json_decode(VATROC_SSO_Discord::get_user_token_from_meta($uid), true);
        if ($tokens == null) {
            return false;
        }

        $user_access_token = $tokens["access_token"];
        $user_data = self::fetch_user_data($uid);
        $discord_user_id = $user_data['id'];

        $access_token = VATROC_SSO_Discord::get_bot_token();
        $body = json_encode([
            'access_token' => $user_access_token,
            'roles' => [VATROC_SSO_Discord::get_new_joiner_role_id()],
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

        $access_token = VATROC_SSO_Discord::get_bot_token();
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
};

VATROC_SSO_Discord_API::init();
