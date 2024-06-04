<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_Rest_Mission_Control
{
    const APPLICATION_FORM_PAGE_ID = 4198;

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
            'callback' => "VATROC_Rest_Mission_Control::update_application_status",
            'permission_callback' => '__return_true',
        ]);
        register_rest_route(VATROC_Rest_API::$namespace, "application_submissions/(?P<uuid>$uuidv4)/comment", [
            'methods' => 'POST',
            'callback' => "VATROC_Rest_Mission_Control::submit_comment",
        ]);
        register_rest_route(VATROC_Rest_API::$namespace, "application_submissions/(?P<uuid>$uuidv4)/comments", [
            'methods' => 'GET',
            'callback' => "VATROC_Rest_Mission_Control::get_comments",
        ]);
        register_rest_route(VATROC_Rest_API::$namespace, 'roster', [
            'methods' => 'GET',
            'callback' => "VATROC_Rest_Mission_Control::roster",
            'permission_callback' => '__return_true',
        ]);
    }

    public static function application_submissions()
    {
        if (!isset($_GET['all'])) {
            add_filter("vatroc_form_get_all_submissions_after", 'VATROC_Rest_Mission_Control::filter_new_applications', 10, 1);
        }
        add_filter("vatroc_form_get_all_submissions_after", 'VATROC_Rest_Utils::hydrate_user_info', 10, 1);
        $ret = VATROC_Form::submission_list(["form" => self::APPLICATION_FORM_PAGE_ID], null, true);
        return $ret;
    }

    public static function update_application_status($request)
    {
        $uuid = $request["uuid"];
        $next_status = $request["next_status"];
        // next status = 3: close application and proceed with del ojt
        if (intval($next_status) === 3) {
            $submission = VATROC_Form::get_submission_from_uuid(self::APPLICATION_FORM_PAGE_ID, $uuid);
            $uid = $submission["uid"];
            $vatsim_id = $submission["vatsimid"];
            VATROC_My::set_del_ojt($uid, $vatsim_id);
        }
        VATROC::dog("Making comment");
        $uuid = $request["uuid"];
        $actorid = get_current_user_ID();
        $next_status_text = VATROC_Constants::$application_status[$next_status];
        VATROC_Form::create_comment(self::APPLICATION_FORM_PAGE_ID, $uuid, $actorid, "> 更改狀態至 $next_status_text 。");
        if (VATROC_Form::update_submission_status(self::APPLICATION_FORM_PAGE_ID, $uuid, $next_status)) {
            return "ok";
        }
    }

    public static function filter_new_applications($arr)
    {
        $arr = array_filter($arr, function ($entry) {
            return !isset($entry["status"]) || intval($entry["status"]) === 1;
        });
        return array_values($arr);
    }

    public static function roster()
    {
        add_filter("vatroc_form_get_all_submissions_after", 'VATROC_Rest_Utils::hydrate_user_info', 10, 1);
        $all_submissions = VATROC_Form::get_all_submissions(self::APPLICATION_FORM_PAGE_ID, 0);
        $archive = array_values(array_filter($all_submissions, function ($ele) {
            return isset($ele["status"]) && intval($ele["status"]) === 0;
        }));
        $applications = array_values(array_filter($all_submissions, function ($ele) {
            return !isset($ele["status"]) || intval($ele["status"]) === 1;
        }));
        $shortlist = array_values(array_filter($all_submissions, function ($ele) {
            return intval($ele["status"]) === 2;
        }));
        $closed = array_values(array_filter($all_submissions, function ($ele) {
            return intval($ele["status"]) > 2;
        }));


        $rosters = VATROC_Shortcode_Roster::table_data(VATROC::$ATC_LOCAL);
        $rosters = VATROC_Rest_Utils::hydrate_user_info($rosters);
        usort($rosters, "VATROC_Shortcode_Roster::sort_atc");
        // $visiting = VATROC_Shortcode_Roster::table_data(VATROC::$ATC_VISITING);
        // $solo = VATROC_Shortcode_Roster::table_data(VATROC::$ATC_SOLO);
        $atc = array_reverse($rosters);

        $data = [
            "archive" => $archive,
            "applications" => $applications,
            "shortlist" => $shortlist,
            "atc" => $atc,
            "closed_applications" => $closed,
        ];
        return $data;
    }

    public static function submit_comment($request)
    {
        $json_params = $request->get_json_params();
        if (!is_null($json_params)) {
            $comment = $json_params["comment"];
            if (empty($comment)) {
                return null;
            }
            $uuid = $request["uuid"];
            $uid = get_current_user_ID();
            VATROC_Form::create_comment(self::APPLICATION_FORM_PAGE_ID, $uuid, $uid, $comment);
            return "ok";
        }
        return null;
    }

    public static function get_comments($request)
    {
        $uuid = $request["uuid"];
        $arr = VATROC_Form::get_comments(self::APPLICATION_FORM_PAGE_ID, $uuid);
        $arr = VATROC_Rest_Utils::hydrate_user_info($arr, ["uid" => "author"]);
        return $arr;
    }
}