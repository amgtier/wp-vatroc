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
        $meta_key = self::make_submission_meta_key(VATROC::generate_uudiv4());
        self::add_uuid_index($post_id, $uid, $meta_key);
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

    private static function get_uid_uuids($post_id, $uid)
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
        $meta_key = self::submission_meta_key($post_id, $uid);
        return self::backend_to_arr(
            get_post_meta($post_id, $meta_key, true),
            $uid
        );
    }

    public static function get_submission_from_uid($post_id, $uid)
    {
        $prefix = "vatroc_form-submission-" . $uid;
        $post_meta = get_post_meta($post_id, $prefix);
    }

    public static function get_all_submissions($post_id, $uid)
    {
        $ret = [];
        if ($uid < 1) {
            $post_meta = get_post_meta($post_id);
            $prefix = "vatroc_form-submission-";
            $keys = array_filter(
                array_keys($post_meta),
                fn($val) => substr($val, 0, strlen($prefix)) === $prefix,
            );
            foreach ($keys as $idx => $k) {
                $_uid = intval(substr($k, strlen($prefix)));
                foreach ($post_meta[$k] as $_idx => $_submission) {
                    $_arr_submission = self::backend_to_arr($_submission, $_uid);
                    array_push($ret, $_arr_submission);
                }
            }
        } else {
            $meta_key = self::submission_meta_key($post_id, $uid);
            $submissions = get_post_meta($post_id, $meta_key);
            $ret = array_map(fn($entry) => self::backend_to_arr($entry, $uid), $submissions);
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

    public static function submission_meta_key($post_id, $uid)
    {
        $uuids = self::get_uid_uuids($post_id, $uid);
        if(count($uuids) == 0){
            VATROC::dog("User $uid has no UUIDs");
            // throw new Exception("User $uid has no UUIDs");
        }
        return end($uuids);
    }

    public static function draft_meta_key($post_id, $uid)
    {
        return self::$meta_key . '-draft-' . $uid;
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
        return $arr;
    }
}
;

VATROC_Form_DAO::init();