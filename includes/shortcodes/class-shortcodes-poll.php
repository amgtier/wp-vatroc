<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Poll extends VATROC_Poll {
    public static function init() {
        add_shortcode( 'vatroc_poll', 'VATROC_Shortcode_Poll::output_poll' );
    }


    public static function enqueue_script() {
        add_action( 'wp_enqueue_script', 'VATROC_Shortcode_Poll::enqueue_script' );
        wp_enqueue_script( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/poll.js', array( 'jquery' ), null, true );
        wp_enqueue_style( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/poll.css' );
        wp_localize_script( 
            'vatroc-poll', 
            'ajax_object', 
            [ 
                'ajax_url' => admin_url( 'admin-ajax.php' ), 
                'page_id' => get_the_ID() 
            ],
        );
    }


    public static function output_poll($attributes) {
        self::enqueue_script();

        if (isset( $_GET[ 'log' ] )){
            return self::output_log( get_the_ID() );
        }

        $is_admin = self::is_admin();
        if(VATROC::debug_section()){
            echo "<div>";
            echo "is_admin:" . $is_admin . "<br/>";
            echo "page_id:" . get_the_ID() . "<br/>";
            echo "</div>";
        }

        ob_start();
        VATROC::get_template( "includes/shortcodes/templates/poll.php" );
        return ob_get_clean();
    }


    public static function is_admin() {
        return VATROC::is_admin() && isset( $_GET[ 'm' ] );
    }


    private static function output_log( $page_id ){
        $ret = "";
        $ret .= "<div class='result log'>";
            $post_meta = array_reverse( get_post_meta( $page_id, VATROC_Poll::$meta_key ) );
            foreach( $post_meta as $idx => $vote ){
                    $ret .= sprintf( "<p>%s %s %s %s</p>", 
                    date( DATE_RFC2822, $vote[ 'timestamp' ] ),
                    VATROC_Poll::get_vote_display( $vote[ 'user' ] ),
                    $vote[ 'name' ], 
                    $vote[ 'value' ],
                );
            }
        $ret .= "</div>";
        return $ret;
    }


    public static function get_options() {
        $post_id = get_the_ID();
        $votes = VATROC_Poll::make_votes( $post_id );
        $uid = get_current_user_id();

        $ret = [];
        $dates = array_merge(
            VATROC_Poll::get_added_vote_date_option( $post_id ),
            self::get_dates( self::get_curr_month(), self::get_curr_year() ),
            self::get_dates( self::get_next_month(), self::get_next_year() ),
        );
        $dates = array_unique( $dates );
        foreach( $dates as $k => $date ){
            $is_option_hidden = VATROC_Poll::is_option_hidden( $post_id, $date );
            if( !VATROC_Shortcode_Poll::is_admin() && $is_option_hidden ){ continue; }
            $ret[ $date ] = [
                "hidden" => $is_option_hidden,
                "description" => VATROC_Poll::get_description( $post_id, $date ),
                "user_accept" => array_key_exists( $uid, @( $votes[ $date ][ "accept" ] ?: [] ) ),
                "user_tentative" => array_key_exists( $uid, @( $votes[ $date ][ "tentative" ] ?: [] ) ),
                "user_reject" => array_key_exists( $uid, @( $votes[ $date ][ "reject" ] ?: [] ) ),
                "accept" => self::get_vote_by_name( $uid, $votes, $date, "accept" ),
                "tentative" => self::get_vote_by_name( $uid, $votes, $date, "tentative" ),
                "reject" => self::get_vote_by_name( $uid, $votes, $date, "reject" ),
                "unknown" => self::get_vote_by_name( $uid, $votes, $date, "unknown" ),
            ];
        };
        return $ret;
    }


    private static function get_curr_month() {
        return date( 'm' ) % 12;
    }


    private static function get_curr_year() {
        return date( 'Y' );
    }


    private static function get_next_month() {
        $next_month = ( date( 'm' ) + 1 ) % 12;
        return $next_month == 0 ? 12 : $next_month;
    }


    private static function get_next_year() {
        return date( 'Y' ) + ( date( 'm' ) + 1 > 12 ? 1 : 0 );
    }


    private static function get_dates( $month, $year ) {
        $days = cal_days_in_month( CAL_GREGORIAN, $month, $year );
        $ret = [];
        $now = time();
        for ( $d = 1; $d <= $days; $d++ ){
            $t_date = strtotime( "$year-$month-$d" );
            if( $now < $t_date && in_array( date( 'w', $t_date ), [0, 6] ) ){
                $ret[] = "$year/$month/" . sprintf( "%02d", $d );
            }
        }
        return $ret;
    }
};

VATROC_Shortcode_Poll::init();
