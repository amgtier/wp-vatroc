<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Poll {
    protected static $meta_prefix = "vatroc_";
    protected static $meta_key = "vatroc_vote";


    public static function init() {
        add_action( "wp_ajax_vatroc_poll_toggle_hide", "VATROC_Poll::ajax_toggle_hide" );
        add_action( "wp_ajax_vatroc_poll_update_description", "VATROC_Poll::ajax_update_description" );
        add_action( "wp_ajax_vatroc_poll_create_option", "VATROC_Poll::ajax_create_option" );
        add_action( "wp_ajax_vatroc_poll_vote", "VATROC_Poll::ajax_set_vote" );
        add_action( "wp_ajax_nopriv_vatroc_poll_vote", "VATROC_Poll::ajax_set_vote" ); // for not logged-in users
    }


    public static function ajax_set_vote() {
        $post_id = $_POST[ "id" ];
        $name = $_POST[ "name" ];
        $value = $_POST[ "value" ];
        $user_id = get_current_user_id();

        if( self::vote_check_dup( $post_id, $user_id, $name, $value ) ){
            wp_send_json_error( "", 400 );
            wp_die();
        }
        
        $meta = add_post_meta( $post_id, self::$meta_key, [
            "user" => $user_id, 
            "name" => $name,
            "value" => $value,
            "timestamp" => time()
            ] );

        $votes = self::make_votes( $post_id );
        echo wp_json_encode( array(
            "name" => $name,
            "value" => [
                "accept" => self::get_vote_by_name( $user_id, $votes, $name, "accept" ),
                "tentative" => self::get_vote_by_name( $user_id, $votes, $name, "tentative" ),
                "reject" => self::get_vote_by_name( $user_id, $votes, $name, "reject" ),
            ]
        ));
        
        wp_die();
    }


    public static function ajax_create_option() {
        $post_id = $_POST[ "id" ];
        $name = $_POST[ "name" ];
        $type = $_POST[ "type" ];

        $name = str_replace( "-", "/", $name );
        
        if( $type == "date" ){
            echo wp_json_encode(
                self::add_vote_date_option( $post_id, $name )
            );
        }
        wp_die();
    }


    public static function ajax_toggle_hide() {
        if ( !VATROC::is_admin() ) { wp_die(); }
        
        $post_id = $_POST[ "id" ];
        $name = $_POST[ "name" ];

        $option_meta = self::toggle_option_meta( $post_id, $name, "hidden");
        echo wp_json_encode( $option_meta );

        wp_die();
    }


    public static function ajax_update_description() {
        if ( !VATROC::is_admin() ) { wp_die(); }
        
        $post_id = $_POST[ "id" ];
        $name = $_POST[ "name" ];
        $description = $_POST[ "value" ];
        VATROC::dlog($_POST);

        $option_meta = self::update_option_meta( $post_id, $name, "description", $description );
        echo wp_json_encode( $option_meta );

        wp_die();
    }


    private static function add_vote_date_option( $post_id, $name ){
        $meta_key = self::$meta_key . '-added-options';
        $curr_meta = get_post_meta( $post_id, $meta_key, true);
        if ( $curr_meta == null ){
            $curr_meta = [];
        }
        array_unshift( $curr_meta, $name );
        update_post_meta( $post_id, $meta_key, array_unique( $curr_meta ) );
        return $curr_meta;
    }


    protected static function get_added_vote_date_option( $post_id ){
        $meta_key = self::$meta_key . '-added-options';
        return get_post_meta( $post_id, $meta_key, true) ?: [];
    }


    private static function vote_check_dup( $post_id, $user_id, $name, $value ){
        $post_meta = array_reverse( get_post_meta( $post_id, self::$meta_key ) );
        foreach ( $post_meta as $idx => $vote ){
            if ($user_id == $vote[ "user" ] && $name == $vote[ "name" ]){
                if($value == $vote[ "value" ]){
                    return true;
                } else {
                    return false;
                }
            }
        }
    }


    protected static function make_votes( $post_id ){
        $post_meta = array_reverse( get_post_meta( $post_id, self::$meta_key ) );
        $votes = [];
        foreach ( $post_meta as $idx => $vote ){
            $has_vote = false;
            foreach( @$votes[ $vote[ "name" ] ] ?? [] as $value => $curr_votes ){
                if(isset($curr_votes[ $vote[ "user" ] ])){
                    $has_vote = true;
                }
            }
            if(!$has_vote){
                $votes[ $vote[ "name" ] ][ $vote[ "value" ] ][ $vote[ "user" ] ] = true;
            }
        }
        return $votes;
    }


    protected static function get_vote_display( $uid ){
        ob_start();
?>
        <div class='vote-display-wrapper'>
            <?php echo VATROC_My::html_my_avatar_with_position( $uid, VATROC::is_admin() ); ?>
        </div>
<?php
        return ob_get_clean();
    }


    protected static function get_vote_by_name( $uid, $votes, $date, $value ){
        $keys = array_keys( isset( $votes[ $date ][ $value ] ) ? $votes[ $date ][ $value ] : [] );
        usort( $keys, function ( $a, $b ) {
            $a_pos = get_user_meta( $a, "vatroc_position", true);
            $b_pos = get_user_meta( $b, "vatroc_position", true);
            return $a_pos <= $b_pos;
        } );
        $ret = array_map(
            "self::get_vote_display",
            $keys
        );
        return $ret;
    }


    private static function get_option_meta_key( $option ) {
        return self::$meta_key . "-" . $option;
    }


    private static function update_option_meta( $post_id, $option, $field, $value ) {
        $curr_meta = get_post_meta( $post_id, self::get_option_meta_key( $option ), true);
        if ( $curr_meta == null ) {
            $curr_meta = [];
        }
        $curr_meta[ $field ] = $value;
        update_post_meta( $post_id, self::get_option_meta_key( $option ), $curr_meta );
        return $curr_meta;
    }


    private static function toggle_option_meta( $post_id, $option, $field ) {
        $curr_meta = get_post_meta( $post_id, self::get_option_meta_key( $option ), true);
        return self::update_option_meta( $post_id, $option, "hidden", !( $curr_meta[ $field ] ?? false ));
    }


    public static function get_option_meta( $post_id, $option ) {
        $curr_meta = get_post_meta( $post_id, self::get_option_meta_key( $option ), true);
        return $curr_meta ?? [];
    }


    public static function is_option_hidden( $post_id, $option ) {
        $curr_meta = self::get_option_meta( $post_id, $option );
        return $curr_meta[ "hidden" ] ?? false;
    }


    public static function get_description( $post_id, $option ) {
        $curr_meta = self::get_option_meta( $post_id, $option );
        return $curr_meta[ "description" ] ?? null;
    }
};

VATROC_Poll::init();
