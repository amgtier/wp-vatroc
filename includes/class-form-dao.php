<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
 * DAO for Forms implementation
 */
class VATROC_Form_DAO
{
    protected static $meta_prefix = "vatroc_";
    protected static $meta_key = "vatroc_form";


    public static function init()
    {
    }

    public static function create_submission($post_id, $uid, $data)
    {
        $meta_key = self::make_submission_meta_key(VATROC::generate_uuidv4());
        self::add_uuid_index($post_id, $uid, $meta_key);
        // TODO:: save as object?
        return add_post_meta(
            $post_id,
            $meta_key,
            self::form_to_backend($data)
        );
    }

    private static function add_uuid_index($post_id, $uid, $entry)
    {
        $data = self::get_uid_uuids($post_id, $uid);
        array_push($data, $entry);
        update_post_meta($post_id, self::make_uuid_index_meta_key($uid), $data);
    }

    public static function get_uid_uuids($post_id, $uid)
    {
        $data = get_post_meta($post_id, self::make_uuid_index_meta_key($uid), true);
        // $data returns an array when properly set
        if ($data == null) {
            $data = [];
        }
        return $data;
    }

    public static function delete_draft($post_id, $uid)
    {
        delete_post_meta($post_id, self::draft_meta_key($post_id, $uid));
    }

    public static function upsert_draft($post_id, $uid, $data)
    {
        return update_post_meta($post_id, self::draft_meta_key($post_id, $uid), self::form_to_backend($data));
    }

    public static function get_last_submissions($post_id, $uid)
    {
        $meta_key = self::last_submission_meta_key($post_id, $uid);
        return self::backend_to_arr(get_post_meta($post_id, $meta_key, true));
    }

    public static function get_submission_from_uuid($post_id, $uuid)
    {
        $prefix = "vatroc_form-submission-" . $uuid;
        return self::backend_to_arr(get_post_meta($post_id, $prefix, true));
    }

    public static function get_all_submissions($post_id, $uid)
    {
        $ret = [];
        if ($uid < 1) {
            $post_meta = get_post_meta($post_id);
            $prefix = "vatroc_form-submission-";
            $keys = array_filter(
                array_keys($post_meta),
                fn ($val) => str_starts_with($val, $prefix),
            );
            foreach ($keys as $idx => $k) {
                $_submission = $post_meta[$k][0];
                $_arr_submission = self::backend_to_arr($_submission);
                $_arr_submission["uuid"] = substr($k, strlen($prefix));
                array_push($ret, $_arr_submission);
            }
        } else {
            $meta_key = self::last_submission_meta_key($post_id, $uid);
            if ($meta_key == null) {
                return $ret;
            }
            $submissions = get_post_meta($post_id, $meta_key);
            $ret = array_map(fn ($entry) => self::backend_to_arr($entry, $uid), $submissions);
        }
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

    public static function get_draft($post_id, $uid)
    {
        $meta_key = self::draft_meta_key($post_id, $uid);
        $str_curr_meta = get_post_meta($post_id, $meta_key, true);
        return self::backend_to_arr($str_curr_meta, $uid);
    }

    private static function make_submission_meta_key($uuid)
    {
        return self::$meta_key . "-submission-$uuid";
    }

    private static function make_uuid_index_meta_key($uid)
    {
        return self::$meta_key . "-$uid";
    }

    public static function draft_meta_key($post_id, $uid)
    {
        return self::$meta_key . '-draft-' . $uid;
    }

    public static function last_submission_meta_key($post_id, $uid)
    {
        $uuids = self::get_uid_uuids($post_id, $uid);
        if (count($uuids) == 0) {
            // VATROC::dog("User $uid has no UUIDs");
            // throw new Exception("User $uid has no UUIDs");
            return null;
        }
        return end($uuids);
    }

    private static function form_to_backend($str)
    {
        $obj = [];
        parse_str($str, $obj);
        return json_encode($obj, JSON_UNESCAPED_UNICODE);
    }

    private static function backend_to_arr($str, $uid = null)
    {
        // [Must fix] unable to parse \'
        $arr = json_decode($str, true);
        // $arr = htmlspecialchars($arr, ENT_QUOTES, 'UTF-8');
        return $arr;
    }

    public static function update_submission($post_id, $uuid, $data)
    {
        update_post_meta($post_id, self::make_submission_meta_key($uuid), json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
;

VATROC_Form_DAO::init();