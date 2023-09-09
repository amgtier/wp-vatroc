<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_Rest_Mission_Control
{
    const FORM_PAGE_ID = 4198;
    public static function init()
    {
        add_action('rest_api_init', 'VATROC_Rest_Mission_Control::add_api_routes');
    }
    public static function add_api_routes()
    {
        $uuidv4 = VATROC::uuidv4_regex();
        register_rest_route(VATROC_Rest_API::$namespace, 'application_submissions', [
            'methods' => 'GET',
            'callback' => "VATROC_Rest_Mission_Control::application_submissions",
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(VATROC_Rest_API::$namespace, "application_submissions/(?P<uuid>$uuidv4)/status/(?P<next_status>\d+)", [
            'methods' => 'POST',
            'callback' => "VATROC_Rest_Mission_Control::archive_application",
            'permission_callback' => '__return_true',
        ]);
    }
    public static function application_submissions()
    {
        add_filter("vatroc_form_get_all_submissions_after", 'VATROC_Rest_Mission_Control::filter_new_applications', 10, 1);
        add_filter("vatroc_form_get_all_submissions_after", 'VATROC_Rest_Utils::hydrate_user_info', 10, 1);
        return VATROC_Form::submission_list(["form" => self::FORM_PAGE_ID], null, true);
    }

    public static function archive_application($data)
    {
        $uuid = $data["uuid"];
        $next_status = $data["next_status"];
        if (VATROC_Form::update_submission_status(self::FORM_PAGE_ID, $uuid, $next_status)) {
            return "ok";
        }
    }

    public static function filter_new_applications($arr)
    {
        $arr = array_filter($arr, function ($entry) {
            return !isset($entry["status"]) || $entry["status"] === 1;
        });
        return array_values($arr);
    }
}

VATROC_Rest_Mission_Control::init();