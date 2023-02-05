<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_ATC {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_action( 'wp_enqueue_script', 'VATROC_ATC::enqueue_script', 1000000001 );
        self::enqueue_script();
    }


    public static function enqueue_script() {
        wp_enqueue_script( 'vatroc-atc', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/atc.js', array( 'jquery' ), null, true );
        VATROC::enqueue_ajax_object( 'vatroc-atc' );
    }


    public static function atc_activity( $uid, $u = null ){
        $load_from_db = true;
        $last_save_t = get_post_meta( get_the_ID(), VATROC::$session_t_meta_prefix . $uid, true );
        if ( isset( $_GET[ "refresh" ] ) && $_GET[ "refresh" ] == true || strlen( $last_save_t ) == 0 || intval( $last_save_t ) + 3600 * 12 * 30 < time() ) {
            $load_from_db = false;
        }

        $events_show_all = false;
        if ( isset( $_GET[ "event" ] ) && $_GET[ "event" ] == "all" ) {
            $events_show_all = true;
        }

        $sessions = self::get_sessions( $uid, $load_from_db );
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
        $ret .= sprintf( "<a href='?who=%s%s&timeline' class='btn btn-success'>Timeline</a>", 
            $uid, 
            $u != null ? "&u=" . $u : null
        );
        if ( $events_show_all ) {
            $ret .= sprintf( "<a href='?who=%s' class='btn btn-success'>Show Active Events</a>", $uid );
        } else {
            $ret .= sprintf( "<a href='?who=%s&event=all' class='btn btn-success'>Show All Events</a>", $uid );
        }
        $ret .= self::print_hours_at( $hours_at );
        $ret .= self::print_events_list( $sessions, $events_show_all );
        $ret .= self::print_sessions( $sessions );

        return $ret;
    }


    public static function atc_timeline( $uid, $u = null ) {
        self::enqueue_script();
        $sessions = array_reverse( self::get_sessions( $uid, $load_from_db ) );
        $ret = "";
        $ret .= sprintf( "<a href='/atc/' class='btn btn-success'>ATC List</a>", $uid );
        $ret .= sprintf( "<a href='?who=%s' class='btn btn-success'>Activity</a>", $uid );
        if( $u != null ) {
            $ret .= self::get_timeline_from_metadata( $u );
        }
        $ret .= self::gen_timeline_from_sessions( $sessions );
        
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
        $ret .= "<th>(hr)callsign [ho recv/init]</th>";
        $ret .= "</thead>";
        $ptr_evt = 0;
        $ptr_sess = 0;
        $len_evt = count( $events );
        $len_sess = count( $sessions );
        $evt_count = [];
        $evt_count_active = [];
        usort( $events, [ "self", "sort_event" ] );
        while ( $ptr_evt < $len_evt ) {
            $evt = $events[ $ptr_evt ];
            $ptr_evt += 1;
            $t_evt_start = strtotime( get_post_meta( $evt->ID, "_EventStartDate", true ) );
            $t_evt_end = strtotime( get_post_meta( $evt->ID, "_EventEndDate", true ) );

            $evt_count[ date( "Y", $t_evt_start ) ] += 1;

            while ( $ptr_sess < $len_sess && strtotime( $sessions[ $ptr_sess ]->start ) > $t_evt_end ) {
                $ptr_sess += 1;
            }

            $event_active = strtotime( $sessions[ $ptr_sess ]->start ) < $t_evt_end && 
                strtotime( $sessions[ $ptr_sess ]->end ) > $t_evt_start;

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
                    $ret .= "<td>";
                    $first = true;
                    while( $ptr_sess < $len_sess && strtotime( $sessions[ $ptr_sess ]->start ) <= $t_evt_end && 
                        strtotime( $sessions[ $ptr_sess]->end ) >= $t_evt_start ) {
                        if ( !$first ){
                            $ret .= "<br/>";
                        }
                        $first = false;
                        $ret .= sprintf( "(%s)<a href='https://stats.vatsim.net/connection/atc-details/%s' target='_blank'>%s </a>[%s/%s]", 
                            round( ( strtotime( $sessions[ $ptr_sess ]->end ) - strtotime( $sessions[ $ptr_sess ]->start ) ) / 3600, 1),
                            $sessions[ $ptr_sess ]->connection_id, 
                            $sessions[ $ptr_sess ]->callsign,
                            $sessions[ $ptr_sess ]->handoffsreceived,
                            $sessions[ $ptr_sess ]->handoffsinitiated,
                        );
                        $ptr_sess += 1;
                    }
                    $ret .= "</td>";
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

        $atc_dates = self::get_atc_dates( $_GET[ "u" ]);

        ob_start();
?>
        <h1 id='sessions'>Sessions</h1>
        <table>
            <thead>
                <th>date</th>
                <th>time</th>
                <th>duration</th>
                <th>rating</th>
                <th>callsign</th>
                <th>tracked</th>
                <th>h/o sent</th>
                <th>h/o recv</th>
                <th>action</th>
            </thead>
<?php
        foreach ( $sessions as $idx=>$sess ) {
            if ( $sess->minutes_on_callsign < 10 ) { continue; }
            $sess_date = date( "Y-m-d", strtotime( $sess->start ) );
?>
        <tr>
            <td><?php echo $sess_date; ?></td>
            <td><?php echo date( "h:i-", strtotime( $sess->start ) ) .
            date( "h:i", strtotime( $sess->end ) ); ?></td>
            <td><?php echo round( $sess->minutes_on_callsign / 60, 1 ); ?></td>
            <td><?php echo VATROC::$vatsim_rating[ $sess->rating ]; ?></td>
            <td><?php echo $sess->callsign; ?></td>
            <td><?php echo $sess->aircrafttracked; ?></td>
            <td><?php echo $sess->handoffsinitiated; ?></td>
            <td><?php echo $sess->handoffsreceived - $sess->handoffsrefused; ?></td>
            <td><?php echo self::sess_action( $sess, $sess_date, $atc_dates ); ?></td>
        </tr>
<?php
        }
        $ret = ob_get_clean() . "</table>";
        return $ret;
    }


    private static function sess_action( $sess, $date, $atc_dates = null ) {
        ob_start();
?>
        <select class='sess-set-as' name='sess-set-as' data-user='<?php echo  $_GET[ "u" ]; ?>' data-date='<?php echo $date; ?>'>
            <option disabled selected>Set As</option>
<?php
        foreach( VATROC_Constants::$atc_dates_in_sess as $key => $value ){
            $selected = $atc_dates != null ? $atc_dates[ $key ] == $date : false;
?>
            <option value='<?php echo $key; ?>' <?php echo !$selected ?: 'selected'; ?>
            ><?php echo  $value; ?></option>
<?php
        }
        return  ob_get_clean() . "</select>";
    }


    private static function gen_timeline_from_sessions( $sessions ) {
        $positions = [ "DEL", "GND", "TWR", "APP", "CTR" ];
        $ratings = ["S1", "S2", "S+", "C1" ];
        $visibility = [];
        foreach( $positions as $idx => $pos ){
            $visibility[ $pos ] = 0;
            $visibility[ "O_" . $pos ] = 0;
        };

        ob_start();
?>
        <h1 id='generated-timeline-sessions'>Generated Timeline Sessions</h1>
        <table>
            <thead>
                <th>date</th>
                <th>time</th>
                <th>duration</th>
                <th>rating</th>
                <th>callsign</th>
                <th>count</th>
            </thead>
<?php
        foreach ( $sessions as $idx=>$sess ) {
            if ( $sess->minutes_on_callsign < 10 ) { continue; }
            $suffix = substr( $sess->callsign, count( $sess->callsign ) - 6, 5 );
            if( !array_key_exists( $suffix, $visibility ) ){
                $suffix = substr( $sess->callsign, count( $sess->callsign ) - 4, 3 );
            }
            if ( ++$visibility[ $suffix ] == 1 ){
?>
            <tr>
                <td><?php echo date( "Y-m-d", strtotime( $sess->start ) ); ?></td>
                <td><?php echo date( "h:i-", strtotime( $sess->start ) ) . date( "h:i", strtotime( $sess->end ) ); ?></td>
                <td><?php echo round( $sess->minutes_on_callsign / 60, 1 ); ?></td>
                <td><?php echo VATROC::$vatsim_rating[ $sess->rating ]; ?></td>
                <td><?php echo $sess->callsign; ?></td>
                <td><?php echo $visibility[ $suffix ]; ?></td>
            </tr>
<?php
            }
        }
        return ob_get_clean() . "</table>";
    }


    private static function get_timeline_from_metadata( $uid ) {
        ob_start();
?>
        <table>
        <thead>
        <th></th><th>date</th>
        </thead>
        <?php foreach ( VATROC::$atc_dates_in_sess as $key => $value ){
            ?>
            <tr>
                <td><?php echo $value; ?></td>
                <td><?php echo get_user_meta( $uid, "vatroc_date_" . $key , true );?></td>
            </tr>
            <?php
        }
        return ob_get_clean() . "</table>";

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


    private static function get_sessions( $uid, $load_from_db=false ){
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

        return $json_sessions->results;
    }

    
    private static function get_atc_dates( $uid ) {
        $ret = [];
        foreach( VATROC_Constants::$atc_dates_in_sess as $key => $val ) {
            $ret[ $key ] = get_user_meta( $uid, "vatroc_date_" . $key , true );
        }
        return $ret;
    }
};

VATROC_ATC::init();
