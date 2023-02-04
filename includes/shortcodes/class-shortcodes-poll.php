<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Poll extends VATROC_Poll {
    public static function init() {
        add_shortcode( 'vatroc_poll', 'VATROC_Shortcode_Poll::output_poll' );
    }


    public static function enqueue_script() {
        add_action( 'wp_enqueue_script', 'VATROC_Shortcode_Poll::enqueue_script', 1000000001 );
        wp_enqueue_script( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/poll.js', array( 'jquery' ), null, true );
        wp_enqueue_style( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/poll.css' );
        VATROC::enqueue_ajax_object();
    }


    public static function output_poll( $atts ) {
        self::enqueue_script();

        if (isset( $_GET[ 'log' ] )){
            return self::output_log( get_the_ID() );
        }

        $is_admin = self::is_admin();

        ob_start();
        if( VATROC::debug_section() ){
            echo "<div>";
            echo "is_admin:" . $is_admin . "<br/>";
            echo "page_id:" . get_the_ID() . "<br/>";
            echo "</div>";
        }
        if ( VATROC::debug_section() ){
            echo "<div>Poll WIP</div>";
        }
        VATROC::get_template( "includes/shortcodes/templates/poll.php", [ 
            "type" => $atts[ "type" ], 
            "post_id" => $atts[ "post_id" ],
            "params" => [
                "show_all" => isset( $_GET[ "show_all" ] ),
        ] ] );
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


    public static function get_options( $type = null, $post_id, $params = [] ) {
        $post_id = $post_id ?: get_the_ID();
        switch ( $type ) {
            case "monthly_availability":
                return VATROC_Poll::get_options_monthly_availability( $post_id, $params );
        }
        return [];
    }
};

VATROC_Shortcode_Poll::init();
