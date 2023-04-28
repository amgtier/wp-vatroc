<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Forms is being filled by single or multiple(WIP) use
 */
class VATROC_Form {
    protected static $meta_prefix = "vatroc_";
    protected static $meta_key = "vatroc_form";


    public static function init() {
        add_action( "wp_ajax_vatroc_form_save_draft", "VATROC_Form::ajax_save_draft" );
        add_action( "wp_ajax_vatroc_form_submit", "VATROC_Form::ajax_submit" );
    }

    public static function ajax_submit() {
        $post_id = $_POST[ "id" ];
        $time = time();
        $data = $_POST[ "data" ] . "&timestamp=$time";
        $uid = get_current_user_ID();
        $meta_key = self::submission_meta_key( $post_id, $uid );
        add_post_meta( $post_id, $meta_key, self::form_to_backend( $data ) );
        if ( !isset( $_GET[ "no_delete" ] ) ) {
            delete_post_meta( $post_id, self::draft_meta_key( $post_id, $uid ) );
        }
        wp_die();
    }

    public static function ajax_save_draft() {
        $post_id = $_POST[ "id" ];
        $data = $_POST[ "data" ];
        $uid = get_current_user_ID();
        $meta_key = self::draft_meta_key( $post_id, $uid );

        // $obj_curr_meta = self::get_draft( $post_id, $uid );
        // foreach( $obj_data as $key => $val ) {
        //     $obj_curr_meta[ $key ] = $val;
        // }

        update_post_meta( $post_id, $meta_key, self::form_to_backend( $data) );
        wp_die();
    }

    public static function get_last_submissions( $post_id, $uid ) {
        $meta_key = self::submission_meta_key( $post_id, $uid );
        return self::backend_to_arr( 
            get_post_meta( $post_id, $meta_key, true ),
            $uid
        );
    }

    public static function get_all_submissions( $post_id, $uid ) {
        $ret = [];
        if ( $uid < 1 ) {
            $post_meta = get_post_meta( $post_id );
            $prefix = "vatroc_form-submission-";
            $keys = array_filter( 
                array_keys( $post_meta ),
                fn( $val ) => substr( $val, 0, strlen( $prefix ) ) === $prefix, 
            );
            foreach( $keys as $idx => $k ) {
                $_uid = intval( substr( $k, strlen( $prefix ) ) );
                foreach( $post_meta[ $k ] as $_idx => $_submission ) {
                    $_arr_submission = self::backend_to_arr( $_submission, $_uid );
                    array_push( $ret, $_arr_submission );
                }
            }
        } else {
            $meta_key = self::submission_meta_key( $post_id, $uid );
            $submissions = get_post_meta( $post_id, $meta_key );
            $ret = array_map( fn($entry) => self::backend_to_arr( $entry, $uid ), $submissions );
        }
        return $ret;

        
    }

    public static function get_submission( $post_id, $uid, $version_number ) {
        return self::get_all_submissions( $post_id, $uid )[ $version_number ];
    }

    public static function get_draft( $post_id, $uid ) {
        $meta_key = self::draft_meta_key( $post_id, $uid );
        $str_curr_meta = get_post_meta( $post_id, $meta_key, true );
        return self::backend_to_arr( $str_curr_meta, $uid );
    }

    public static function submission_meta_key( $post_id, $uid ) {
        return self::$meta_key . '-submission-' . $uid;
    }

    public static function draft_meta_key( $post_id, $uid ) {
        return self::$meta_key . '-draft-' . $uid;
    }

    private static function form_to_backend( $str ) {
        $obj = [];
        parse_str( $str, $obj );
        return json_encode( $obj );
    }

    private static function backend_to_arr( $str, $uid ) {
        $arr = json_decode( $str, true );
        $arr[ "uid" ] = $uid;
        return $arr;
    }
};

VATROC_Form::init();