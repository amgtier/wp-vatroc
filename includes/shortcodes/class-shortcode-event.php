<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Event {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_shortcode( 'vatroc_events', 'VATROC_Shortcode_Event::output_events' );
    }


    public static function output_events() {

        $events_show_all = false;
        if ( isset( $_GET[ "event" ] ) && $_GET[ "event" ] == "all" ) {
            $events_show_all = true;
        }

        $ret = "";
        $ret .= sprintf( "<a href='?event=all' class='btn btn-success'>Show All</a>" );
        $ret .= sprintf( "<a href='?show=stats' class='btn btn-success'>Show Stats</a>" );
        $ret .= self::print_events_list( null, $events_show_all );
        return $ret;
    }

    private static function sort_event( $evt1, $evt2 ){
        return strtotime( get_post_meta( $evt1->ID, "_EventStartDate", true ) ) < strtotime( get_post_meta( $evt2->ID, "_EventStartDate", true ) );
    }

    private static function print_events_list( $sessions, $events_show_all=false ) {
        $events = array_reverse( get_posts( [ 
            "post_type" => "tribe_events",
            "numberposts" => -1
        ] ) );
        $ret = "<h1 id='event-list'>Event List</h1>";
        $ret .= "<table>";
        $ret .= "<thead>";
        $ret .= "<th>date</th>";
        $ret .= "<th>time</th>";
        $ret .= "<th>title</th>";
        $ret .= "<th>CTR</th>";
        $ret .= "<th>APP</th>";
        $ret .= "<th>TWR</th>";
        $ret .= "<th>GND</th>";
        $ret .= "<th>DEL</th>";
        $ret .= "</thead>";
        $ptr_evt = 0;
        $len_evt = count( $events );
        $evt_count = [];
        $evt_count_active = [];
        usort( $events, [ "self", "sort_event" ] );
        $start_of_the_year = strtotime( date( 'Y' ) . '-01-01' );
        while ( $ptr_evt < $len_evt ) {
            $evt = $events[ $ptr_evt ];

            if ( !$events_show_all && $t_evt_start && $t_evt_start < $start_of_the_year ) {
                break;
            }

            $ptr_evt += 1;

            $t_evt_start = strtotime( get_post_meta( $evt->ID, "_EventStartDate", true ) );
            $t_evt_end = strtotime( get_post_meta( $evt->ID, "_EventEndDate", true ) );

            $evt_count[ date( "Y", $t_evt_start ) ] += 1;

            if ( $events_show_all || $event_active ) {
                $ret .= "<tr>";
                $ret .= sprintf( "<td>%s</td><td>%s</td><td><a href='%s'>%s</a></td>", 
                    date( "Y-m-d", $t_evt_start ) ,
                    date( "H:i-", $t_evt_start ) . date( "H:i", $t_evt_end ),
                    get_permalink( $evt ), 
                    get_the_title( $evt ) 
                );
                $ret .= "</tr>";
            }
        }
        $ret .= "</table>";

        return $stats . $ret;
    }
};

VATROC_Shortcode_Event::init();
