<?php
/**
 * VATROC Shortcode Homepage
 *
 * @class VATROC_Shortcode_Homepage
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Homepage {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        // add_shortcode( 'vatroc_homepage_event', 'VATROC_Shortcode_Homepage::shortcode_event' );
        add_shortcode( 'vatroc_homepage_event', 'VATROC_Shortcode_Homepage::output_event' );
        add_shortcode( 'vatroc_homepage_metar', 'VATROC_Shortcode_Homepage::output_metar' );
        add_shortcode( 'vatroc_homepage_atc', 'VATROC_Shortcode_Homepage::output_atc' );
        add_action( "wp_ajax_vatroc_homepage_metar", "VATROC_Shortcode_Homepage::ajax_metar" );
        add_action( "wp_ajax_nopriv_vatroc_homepage_metar", "VATROC_Shortcode_Homepage::ajax_metar" );
        add_action( "wp_ajax_vatroc_homepage_atc", "VATROC_Shortcode_Homepage::ajax_atc" );
        add_action( "wp_ajax_nopriv_vatroc_homepage_atc", "VATROC_Shortcode_Homepage::ajax_atc" );
    }


    public static function output_atc() {
        wp_enqueue_script( 'vatroc-homepage', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/js/homepage.js', array( 'jquery' ), null, true );
        wp_localize_script( 'vatroc-homepage', 'ajax_object', [ 'ajax_url' => admin_url( 'admin-ajax.php' ), 'shortcode' => 'atc' ] );

        $ret = "<div id='vatroc-homepage-atc'></div>";
        return $ret;
    }

    public static function ajax_atc( ) {
        $ret = "";
        $status_table = new VATROC_CurrStatusTable();
        foreach( $status_table->getVatsimStatus( VATROC::$ATC ) as $idx=>$atc ) {
            $cnt += 1;
            $ret .= "<p>${atc['frequency']} ${atc['callsign']} ${atc['name']}</p>";
        }
        if ( $cnt == 0 ) {
            $ret .= "<p>No ATC in TPE FIR</p>";
        }
        echo $ret;
        wp_die();
    }


    public static function output_metar( $attrs ) {
        if ( ! ( $attrs && isset( $attrs[ "icao" ] ) ) ) {
            return NULL;
        }

        wp_enqueue_script( 'vatroc-homepage', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/js/homepage.js', array( 'jquery' ), null, true );
        wp_localize_script( 'vatroc-homepage', 'ajax_object', [ 'ajax_url' => admin_url( 'admin-ajax.php' ), 'shortcode' => 'metar' ] );

        $ret = "<div id='vatroc-homepage-metar'></div>";
        return $ret;
    }
        
    public static function ajax_metar( ) {
        $ret = "";
        $metar = VATROC_AdminDashboard::getMetar( $_POST[ "icaos" ] );
        foreach( $metar as $idx=>$obj ) {
            $ret .= sprintf( "<p><b>%s</b> %s</p>", $obj->station, str_replace( "\/", "/", $obj->message ) );
        }
        echo $ret;
        wp_die();
    }


    public function shortcode_event () {
        // add_action( 'wp_head', 'VATROC_Shortcode_Homepage::output_event', 10, 0 );
    }


	public static function output_event() {
        $events = get_posts( [ "post_type" => "tribe_events" ] );
        $smallest_closest_idx = NULL;
        $smallest_closest_tm = NULL;
        $smallest_closest_date = NULL;
        foreach( $events as $idx=>$evt ) {
            $date = get_post_meta( $evt->ID, "_EventStartDate", true );
            $tm = strtotime($date);
            if ( $tm > time() && ( $smallest_closest_tm == NULL || $tm < $smallest_closest_tm ) ) {
                $smallest_closest_tm = $tm;
                $smallest_closest_idx = $idx;
            }
        }
        $ret = "";
        if ( $smallest_closest_idx >= 0 ) {
            $evt = $events[ $smallest_closest_idx ];
            $ret .= "<div id='vatroc-next-event'>";
            $ret .= "<p class='vatroc next-event-title' >Next Event</p>";
            $ret .= "<p class='vatroc next-event-subtitle'>" . get_the_title( $evt ) . " - " . date( "Y-m-d", $smallest_closest_tm ). "</p>";
            $ret .= "</div>";
        }
        return $ret;
    }
};

VATROC_Shortcode_Homepage::init();
