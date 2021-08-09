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


	public function output_atc() {
        $rosters = self::table_data( VATROC::$ATC );
        $ret = "";
        foreach( $rosters as $idx=>$atc ) {
            $ret .= sprintf( "<p>%s %s %s %s</p>", 
                $atc[ "vatroc_vatsim_uid" ], 
                $atc[ "display_name" ], 
                VATROC::$vatsim_rating[ $atc[ "vatroc_vatsim_rating" ] ], 
                VATROC::$atc_position[ $atc[ "vatroc_position" ] ] 
            );
        }
        return $ret;
    }


    // public function table_data( $type ) {
    //     global $wpdb;
    //     $meta_prefix = self::$meta_prefix;
    //     $sql = "SELECT ID,post_title FROM {$wpdb->prefix}wp_posts WHERE post_type='tribe_events'";

    //     $sql_usermeta = "SELECT user_id,meta_key,meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE '{$meta_prefix}%'";

    //     $data = $wpdb->get_results( $sql, 'ARRAY_A' );
    //     $usermeta = $wpdb->get_results( $sql_usermeta, 'ARRAY_A' );

    //     // Preserve for future use.
    //     // for( $i = 0; $i < count( $data ); $i += 1 ) {
    //     //     $data[ $i ][ "display_name" ] = "<a href='" . get_edit_user_link( $data[ $i ][ "ID" ] ) . "#profile-vatroc-tool' target='_balnk'>{$data[ $i ][ "display_name" ]}</a>";
    //     // }


    //     foreach( $usermeta as $idx=>$val ) {
    //         $entry = null;
    //         $i = 0;
    //         for ( ; $i < count( $data ); $i += 1 ){
    //             if ( $data[ $i ][ "ID" ] == $val[ "user_id" ] ) {
    //                 break;
    //             }
    //         }
    //         if ( $i == count( $data ) ) continue;

    //         $data[ $i ][ $val[ "meta_key" ] ] = $val[ "meta_value" ];
    //     }

    //     $rosters = array();
    //     switch ( $type ) {
    //     case VATROC::$ATC:
    //         foreach ( $data as $idx=>$val ) {
    //             if ( isset( $data[ $idx ][ "{$meta_prefix}position" ] ) &&
    //                 $data[ $idx ][ "{$meta_prefix}position" ] > 0 ) {
    //                 array_push( $rosters, $data[ $idx ] );
    //             }
    //         } break;
    //     case VATROC::$STAFF:
    //         foreach ( $data as $idx=>$val ) {
    //             if ( isset( $data[ $idx ][ "{$meta_prefix}staff_role" ] ) ) {
    //                 array_push( $rosters, $data[ $idx ] );
    //             }
    //         } break;
    //     }

    //     return $rosters;
    // }
};

VATROC_Shortcode_Homepage::init();
