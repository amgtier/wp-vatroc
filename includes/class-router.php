<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Router
{
    public static $routes = [
        [
            "key" => "sso",
            "path" => "sso",
            "callback" => "VATROC_Shortcode_SSO::router"
        ]
    ];

    public static function init()
    {
        // add_action('init', 'VATROC_Router::add_rewrite_rule');
        // add_filter('query_vars', 'VATROC_Router::add_query_vars');
        add_filter('template_include', 'VATROC_Router::template_intercept', 100);
    }

    public static function add_rewrite_rule()
    {
        /**
         * After changes, go `https://www.vatroc.net/wp-admin/options-permalink.php` and `Save Changed`
         */
        foreach (self::$routes as $_ => $route) {
            $key = $route['key'];
            $path = $route['path'];
            if ($key && $path) {
                add_rewrite_rule("$path/(.+)", "index.php?$key=\$matches[1]", 'top');
            }
        }
    }

    public static function add_query_vars($query_vars)
    {
        foreach (self::$routes as $_ => $route) {
            $query_vars[] = $route['key'];
        }
        return $query_vars;
    }

    public static function template_intercept($template)
    {
        global $wp_query;

        /**
         * Login not tested!
         */
        // foreach (self::$routes as $_ => $route) {
        //     $key = $route['key'];
        //     $callback = $route['callback'];
        //     if ($key && $callback) {
        //         if (isset($wp_query->query_vars[$key])) {
        //             return ($callback)(null);
        //         }
        //     }
        // }

        if ($wp_query->queried_object->ID == VATROC_Shortcode_SSO::PAGE_ID) {
            VATROC_Shortcode_SSO::router(null);
        }
        return $template;
    }
}
;

VATROC_Router::init();