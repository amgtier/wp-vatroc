<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_ATC {
    private static $meta_prefix = "vatroc_";


    public static function init() {
    }


    public static function output_atc() {
        if ( count( $_GET ) < 1 ) { return; }

        if ( isset( $_GET[ "who" ] ) && is_numeric( $_GET[ "who" ] ) ) {
            $uid = $_GET[ "who" ];
            $load_from_db = true;
            $last_save_t = get_post_meta( get_the_ID(), VATROC::$session_t_meta_prefix . $uid, true );
            if ( isset( $_GET[ "refresh" ] ) && $_GET[ "refresh" ] == true || strlen( $last_save_t ) == 0 || intval( $last_save_t ) + 3600 * 12 * 30 < time() ) {
                $load_from_db = false;
            }

            $events_show_all = true;
            if ( isset( $_GET[ "event" ] ) && $_GET[ "event" ] == "active" ) {
                $events_show_all = false;
            }

            $raw_data = self::load_data( $uid, $load_from_db );
            $try_cnt = 0;
            while ( !is_string( $raw_data ) && $try_cnt < 10 ){
                sprintf( "VATSIM API connection failed. Retrying(%s).", $try_cnt );
                $try_cnt += 1;
                $raw_data = self::load_data( $uid, $load_from_db );
                if ( !is_string( $raw_data ) ) {
                    $ret = "<h1>Load failed. Please refresh.</h1>";
                }
            }
            $json_sessions = json_decode( $raw_data );
            if ( $json_sessions->detail ){ return sprintf( "<h1>%s</h1>", $json_sessions->detail ); }

            $sessions = $json_sessions->results;
            $hours_at = [];
            foreach ( $sessions as $idx=>$sess ) {
                if ( $sess->minutes_on_callsign < 10 ) { continue; }

                $hours_at[ $sess->rating ][ "total" ] += $sess->minutes_on_callsign;
                if ( strpos( $sess->callsign, "O" ) ) {
                    $hours_at[ $sess->rating ][ "OJT" ] += $sess->minutes_on_callsign;
                }
            }

            $ret .= sprintf( "<a href='/atc/' class='btn btn-success'>ATC List</a>", $uid );
            $ret .= sprintf( "<a href='?who=%s&refresh=true' class='btn btn-success'>Refresh</a>", $uid );
            if ( $events_show_all ) {
                $ret .= sprintf( "<a href='?who=%s&event=active' class='btn btn-success'>Show Active Events Only</a>", $uid );
            } else {
                $ret .= sprintf( "<a href='?who=%s' class='btn btn-success'>Show All Events</a>", $uid );
            }
            $ret .= self::print_hours_at( $hours_at );
            $ret .= self::print_events( $sessions, $events_show_all );
            $ret .= self::print_sessions( $sessions );

        } else {
            $ret = "nonono";
        }
        return $ret;
    }


    private static function print_events( $sessions, $events_show_all=true ) {
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
        $ret .= "<th>callsign</th>";
        $ret .= "</thead>";
        $ptr_evt = 0;
        $ptr_sess = 0;
        $len_evt = count( $events );
        $len_sess = count( $sessions );
        $evt_count = [];
        $evt_count_active = [];
        while ( $ptr_evt < $len_evt ) {
            $evt = $events[ $ptr_evt ];
            $ptr_evt += 1;
            $evt_start = get_post_meta( $evt->ID, "_EventStartDate", true );
            $evt_end = get_post_meta( $evt->ID, "_EventEndDate", true );
            $t_evt_start = strtotime( $evt_start );
            $t_evt_end = strtotime( $evt_end );

            $evt_count[ date( "Y", $t_evt_start ) ] += 1;

            while ( $ptr_sess < $len_sess && strtotime( $sessions[ $ptr_sess ]->start ) > $t_evt_end ) {
                $ptr_sess += 1;
            }

            $sess_mid = ( strtotime( $sessions[ $ptr_sess ]->start ) + strtotime( $sessions[ $ptr_sess ]->end ) ) / 2;
            $event_active = $sess_mid >= $t_evt_start && $sess_mid <= $t_evt_end;

            if ( $events_show_all || $event_active ) {
                $ret .= "<tr>";
                $ret .= sprintf( "<td>%s</td><td>%s</td><td><a href='%s'>%s</a></td>", 
                    date( "Y-m-d", $t_evt_start ) ,
                    date( "H:i-", $t_evt_start ) . date( "H:i", $t_evt_end ),
                    get_permalink( $evt ), 
                    get_the_title( $evt ) 
                );
                if ( $event_active ) {
                    $evt_count_active[ date( "Y", $t_evt_start ) ] += 1;
                    $ret .= sprintf( "<td><a href='https://stats.vatsim.net/connection/atc-details/%s' target='_blank'>%s</a></td>", 
                        $sessions[ $ptr_sess ]->connection_id, 
                        $sessions[ $ptr_sess ]->callsign 
                    );
                } else {
                    $ret .= "<td></td>";
                }
                $ret .= "</tr>";
            }
        }
        $ret .= "</table>";

        $stats = "<h1 id='event-stats'>Event Statistics</h1>";
        $stats .= "<table class='bordered'>";
        $stats .= "<thead>";
        $stats .= "<th></th>";
        foreach( $evt_count as $year=>$cnt ) {
            // $stats .= sprintf( "<td><tr>%s</tr><tr>%s</tr><tr>%s</tr></td>", $year, $cnt );
            $stats .= sprintf( "<th>%s</th>", $year );
        }
        $stats .= "</thead>";
        $stats .= "<tr>";
        $stats .= "<th>active</th>";
        foreach( $evt_count as $year=>$cnt ) {
            $stats .= sprintf( "<td>%s</td>", $evt_count_active[ $year ] );
        }
        $stats .= "</tr>";
        $stats .= "<tr>";
        $stats .= "<th>total</th>";
        foreach( $evt_count as $year=>$cnt ) {
            $stats .= sprintf( "<td>%s</td>", $cnt );
        }
        $stats .= "</tr>";
        $stats .= "</table>";

        return $stats . $ret;
    }

    
    private static function print_hours_at( $hours_at ) {
        $heads = [];
        $ret = "<h1>Hour Statistics</h1>";
        $ret .= "<table>";
        $ret .= "<thead>";
        $ret .= "<th></th>";
        foreach( $hours_at as $rating=>$data ) {
            $ret .= sprintf( "<th>%s</th>", VATROC::$vatsim_rating[ $rating ] );
        }
        $ret .= "</thead>";
        $ret .= "<tr>";
        $ret .= "<th>Total</th>";
        foreach( $hours_at as $rating=>$data ) {
            $ret .= sprintf( "<td>%s (hr)</td>", intval( $data[ "total" ] / 60 ) );
        }
        $ret .= "</tr>";
        $ret .= "<tr>";
        $ret .= "<th>OJT</th>";
        foreach( $hours_at as $rating=>$data ) {
            $ret .= sprintf( "<td>%s (hr)</td>", intval( $data[ "OJT" ] / 60 ) );
        }
        $ret .= "</tr>";
        $ret .= "</table>";
        return $ret;
    }


    private static function print_sessions( $sessions ) {
        $ret = "<h1 id='sessions'>Sessions</h1>";
        $ret .= "<table>";
        $ret .= "<thead>";
        $ret .= "<th>date</th>";
        $ret .= "<th>time</th>";
        $ret .= "<th>duration</th>";
        $ret .= "<th>rating</th>";
        $ret .= "<th>callsign</th>";
        $ret .= "<th>tracked</th>";
        $ret .= "<th>h/o sent</th>";
        $ret .= "<th>h/o recv</th>";
        $ret .= "</thead>";
        foreach ( $sessions as $idx=>$sess ) {
            if ( $sess->minutes_on_callsign < 10 ) { continue; }
            $ret .= sprintf( "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", 
                date( "Y-m-d", strtotime( $sess->start ) ),
                date( "h:i-", strtotime( $sess->start ) ) .
                date( "h:i", strtotime( $sess->end ) ),
                round( $sess->minutes_on_callsign / 60, 1 ), 
                VATROC::$vatsim_rating[ $sess->rating ],
                $sess->callsign,
                $sess->aircrafttracked,
                $sess->handoffsinitiated,
                $sess->handoffsreceived - $sess->handoffsrefused
            );
        }
        $ret .= "</table>";
        return $ret;
    }


    private static function load_data( $uid, $load=false ) {
        if ( $load ) {
            // $path = dirname( __FILE__ ) . '/' . $uid;
            // $fp = fopen( $path, "r" );
            // $rawtxt = "";
            // if ( $fp ) {
            //     $rawtxt = fread( $fp, filesize( $path ) );
            //     fclose( $fp );
            // }
            $res = get_post_meta( get_the_ID(), VATROC::$session_meta_prefix . $uid, true );
            if ( !$res ) { return self::load_data( $uid ); }
            return $res;
        } else {
            $curl = new WP_Http_Curl();
            $res = $curl->request( "https://api.vatsim.net/api/ratings/" . $uid . "/atcsessions/" );
            if ( is_a( $res, "WP_Error" ) ) {
                return $res;
            }
            update_post_meta( get_the_ID(), VATROC::$session_meta_prefix . $uid , $res[ "body" ] );
            update_post_meta( get_the_ID(), VATROC::$session_t_meta_prefix . $uid , time() );
            return $res[ "body" ];
        }
        return $rawtxt;
    }
};

VATROC_Shortcode_ATC::init();
