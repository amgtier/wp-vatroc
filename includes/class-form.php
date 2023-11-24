<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
 * Forms is being filled by single or multiple(WIP) use
 */
class VATROC_Form
{
    public static function init()
    {
        add_action("wp_ajax_vatroc_form_save_draft", "VATROC_Form::ajax_save_draft");
        add_action("wp_ajax_vatroc_form_submit", "VATROC_Form::ajax_submit");
    }

    public static function ajax_submit()
    {
        $post_id = $_POST["id"];
        $time = time();
        $uid = get_current_user_ID();
        $data = wp_unslash($_POST["data"] . "&timestamp=$time&uid=$uid");
        if (!VATROC_Form_DAO::create_submission($post_id, $uid, $data)) {
            VATROC::log($data, "fatal", "form");
        }
        if (!isset($_GET["no_delete"])) {
            if (!VATROC_Form_DAO::delete_draft($post_id, $uid)) {
                VATROC::log("$post_id $uid draft delete faile.", "fatal", "form-draft");
            }
        }
        wp_die();
    }

    public static function ajax_save_draft()
    {
        $post_id = $_POST["id"];
        $data = wp_unslash($_REQUEST["data"]);
        $uid = get_current_user_ID();
        if (!VATROC_Form_DAO::upsert_draft($post_id, $uid, $data)) {
            VATROC::log($data, "fatal", "form-draft");
        }
        wp_die();
    }

    public static function get_last_submissions($post_id, $uid)
    {
        return VATROC_Form_DAO::get_last_submissions($post_id, $uid);
    }

    public static function get_submission_from_uuid($post_id, $uuid)
    {
        return VATROC_Form_DAO::get_submission_from_uuid($post_id, $uuid);
    }

    public static function get_all_submissions($post_id, $uid)
    {
        $ret = VATROC_Form_DAO::get_all_submissions($post_id, $uid);
        usort($ret, 'self::sort_timestamp');

        return apply_filters("vatroc_form_get_all_submissions_after", $ret);
    }

    public static function sort_timestamp($a, $b)
    {
        $result = intval($a['timestamp']) > intval($b['timestamp']);
        if (isset($_GET['desc'])) {
            return intval($a['timestamp']) < intval($b['timestamp']);
        }
        return $result;
    }

    public static function get_submission($post_id, $uid, $version_number)
    {
        return self::get_all_submissions($post_id, $uid)[$version_number];
    }

    public static function get_draft($post_id, $uid)
    {
        return VATROC_Form_DAO::get_draft($post_id, $uid);
    }

    public static function submission_list($atts = null, $content = null, $is_view_all = false)
    {
        $current_uid = get_current_user_ID();
        // TODO: refactor privacy setting for rest to use
        $can_view_all_list = explode(",", $atts["can_view_all"] ?: []);
        $can_view_all = VATROC::is_admin() || in_array($current_uid, $can_view_all_list);
        $uid = isset($_GET["u"]) && intval($_GET["u"]) > 0 ? intval($_GET["u"]) : ($is_view_all && $can_view_all ? 0 : $current_uid);
        $post_id = $atts["form"] ?: get_the_ID();
        $all_submissions = self::get_all_submissions($post_id, $uid);
        $keys = ["status" => true];
        // TODO: add controller here if the shape is not fixed
        // foreach ($all_submissions as $idx => $fields) {
        if (count($all_submissions) > 0) {
            $fields = $all_submissions[0];
            foreach (array_keys($fields) as $dix => $key) {
                $keys[$key] = true;
            }
        }
        // }
        unset($keys["timestamp"]);
        unset($keys["uid"]);
        unset($keys["uuid"]);
        return [
            "all_submissions" => $all_submissions,
            "keys" => $keys,
            "can_view_all" => $can_view_all,
            "uid" => $uid,
            "post_id" => $post_id,
        ];
    }

    public static function count_user_submission($post_id, $uid)
    {
        return count(VATROC_Form_DAO::get_uid_uuids($post_id, $uid));
    }

    public static function update_submission_status($post_id, $uuid, $next_status)
    {
        $submission = VATROC_Form_DAO::get_submission_from_uuid($post_id, $uuid);
        $submission["status"] = $next_status;
        VATROC_Form_DAO::update_submission($post_id, $uuid, $submission);
    }

    public static function create_comment($post_id, $uuid, $uid, $content){
        VATROC_Form_DAO::create_comment($post_id, $uuid, $uid, $content);
        return true;
    }
};

VATROC_Form::init();
