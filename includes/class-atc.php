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
        wp_enqueue_script( 'vatroc-atc', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/js/atc.js', array( 'jquery' ), null, true );
        VATROC::enqueue_ajax_object( 'vatroc-atc' );
    }


    public static function get_timeline_link( $vatsim_id = null, $uid = null ) {
        $vatsim_id = $vatsim_id ?: $_GET[ "who" ];
        $uid = $uid ?: $_GET[ "u" ];
        return "?timeline&who=" . $vatsim_id . ($uid != null ? "&u=" . $uid : null) . "";
    }


    public static function get_activity_link( $vatsim_id = null, $uid = null ) {
        $vatsim_id = $vatsim_id ?: $_GET[ "who" ];
        $uid = $uid ?: $_GET[ "u" ];
        return "?who=$vatsim_id&u=$uid";
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
        ob_start();
?>
        <h1> <?php echo $uid; ?> - <?php echo get_user_by( 'ID', $u )->display_name; ?> </h1>
        <a href='/atc/' class='btn btn-success'>ATC List</a>
        <a href='?who=<?php echo $uid; ?>&refresh=true' class='btn btn-success'>Refresh</a>
        <a href='<?php echo self::get_timeline_link(); ?>' class='btn btn-success'>Timeline</a>
        <?php if ( $events_show_all ): ?>
            <a href='?who=<?php echo $uid; ?>' class='btn btn-success'>Show Active Events</a>
        <?php else: ?>
            <a href='?who=<?php echo $uid; ?>&event=all' class='btn btn-success'>Show All Events</a>
<?php 
        endif;
        echo self::print_hours_at( $hours_at );
        echo self::print_events_list( $sessions, $events_show_all );
        echo self::print_sessions( $sessions );

        return ob_get_clean();
    }


    public static function atc_timeline( $uid, $u = null ) {
        self::enqueue_script();
        $sessions = array_reverse( self::get_sessions( $uid, $load_from_db ) );
        ob_start();
?>
        <a href='/atc/' class='btn btn-success'>ATC List</a>
        <a href='<?php echo self::get_activity_link(); ?>' class='btn btn-success'>Activity</a>
<?php 
        if( $u != null ){
            echo self::get_timeline_from_metadata( $u );
        }
        echo self::gen_timeline_from_sessions( $sessions );
        
        return ob_get_clean();
    }


    private static function sort_event( $evt1, $evt2 ){
        return strtotime( get_post_meta( $evt1->ID, "_EventStartDate", true ) ) < strtotime( get_post_meta( $evt2->ID, "_EventStartDate", true ) );
    }


    private static function print_events_list( $sessions, $events_show_all=false ) {
        $events = array_reverse( get_posts( [ 
            "post_type" => "tribe_events",
            "numberposts" => -1
        ] ) );

        ob_start();
?>
        <h1 id='event-list'>Event List</h1>
        <table>
            <thead>
                <th>date</th>
                <th>time</th>
                <th>title</th>
                <th>(hr)callsign [ho recv/init]</th>
            </thead>
<?php
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
?>
                <tr>
                    <td><?php echo date( "Y-m-d", $t_evt_start ); ?></td>
                    <td><?php echo date( "H:i-", $t_evt_start ) . date( "H:i", $t_evt_end ); ?></td>
                    <td><a href='<?php echo get_permalink( $evt ); ?>'><?php echo get_the_title( $evt ) ; ?></a></td>
<?php
                if ( $event_active ) {
                    $evt_count_active[ date( "Y", $t_evt_start ) ] += 1;
?>
                    <td>
<?php
                    $first = true;
                    while( $ptr_sess < $len_sess && strtotime( $sessions[ $ptr_sess ]->start ) <= $t_evt_end && 
                        strtotime( $sessions[ $ptr_sess]->end ) >= $t_evt_start ) {
                        if ( !$first ){
?>
                                <br />
<?php
                        }
                        $first = false;
?>
                            (<?php echo 
                            round( ( strtotime( $sessions[ $ptr_sess ]->end ) - strtotime( $sessions[ $ptr_sess ]->start ) ) / 3600, 1);
                            ?>)<a 
                                href='https://stats.vatsim.net/connection/atc-details/<?php echo $sessions[ $ptr_sess ]->connection_id; ?>' 
                                target='_blank'
                                ><?php echo $sessions[ $ptr_sess ]->callsign; ?> </a>[<?php echo $sessions[ $ptr_sess ]->handoffsreceived; 
                                ?>/<?php echo $sessions[ $ptr_sess ]->handoffsinitiated; ?>]",
<?php
                        $ptr_sess += 1;
                    }
?>
                    </td>
<?php
                } else {
?>
                    <td></td>
<?php
                }
?>
                    </tr>
<?php
            }
        }
?>
        </table>

        <h1 id='event-stats'>Event Statistics</h1>
        <table class='bordered'>
            <thead>
            <th></th>
        <?php foreach( $evt_count as $year=>$cnt ):
            // $stats .= sprintf( "<td><tr>%s</tr><tr>%s</tr><tr>%s</tr></td>", $year, $cnt ); ?> 
            <th><?php echo $year; ?></th>
        <?php endforeach; ?>
        </thead>
        <tr>
        <th>active</th>
        <?php foreach( $evt_count as $year=>$cnt ): ?>
            <td><?php echo $evt_count_active[ $year ]; ?></td>
        <?php endforeach; ?>
        </tr>
        <tr>
        <th>total</th>
        <?php foreach( $evt_count as $year=>$cnt ): ?>
            <td><?php echo $cnt; ?></td>
        <?php endforeach; ?>
        </tr>
        </table>
<?php

        return ob_get_clean();
    }

    
    private static function print_hours_at( $hours_at ) {
        $heads = [];
        ob_start();
    ?>
        <h1>Hour Statistics</h1>
        <table>
        <thead>
        <th></th>
        <?php foreach( $hours_at as $rating=>$data ): ?>
            <th><?php echo VATROC::$vatsim_rating[ $rating ]; ?></th>
        <?php endforeach; ?>
        </thead>
        <tr>
        <th>Total</th>
        <?php foreach( $hours_at as $rating=>$data ): ?>
            <td><?php echo intval( $data[ "total" ] / 60 ); ?> (hr)</td>
        <?php endforeach; ?>
        </tr>
        <tr>
        <th>OJT</th>
        <?php foreach( $hours_at as $rating=>$data ): ?>
            <td><?php echo intval( $data[ "OJT" ] / 60 ); ?> (hr)</td>
        <?php endforeach; ?>
        </tr>
        </table>
    <?php
        return ob_get_clean();
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


    private static function get_timeline_from_metadata( $uids ) {
        $arr_uids = explode( ",", $uids );
        $uid = $arr_uids[0];

        ob_start();
        $last_dates = [];
?>
        <table>
        <thead>
        <th></th>
        <?php foreach( $arr_uids as $_ => $uid): ?>
            <th>date ( <?php echo get_user_meta( $uid, "vatroc_vatsim_uid", true ); ?>)</th>
        <?php endforeach; ?>
        </thead>
        <?php foreach ( VATROC::$atc_dates_in_sess as $key => $value ): 
?>
            <tr>
                <td><?php echo $value; ?></td>
<?php
                foreach ( $arr_uids as $_ => $uid ):
                    $date = get_user_meta( $uid, "vatroc_date_" . $key , true );
                    $date_timestamp = strtotime( $date );
                    $delta = 0;
                    if ( $date != null ){
                        if( $last_dates[ $uid ] > 0 ){
                            $delta = ( $date_timestamp - $last_dates[ $uid ] ) / 86400;
                        }
                        $last_dates[ $uid ] = $date_timestamp;
                    } else {
                        $last_dates[ $uid ] = 0;
                    }
?>
                    <td><?php echo $date; ?> <?php echo $delta > 0 ? "(" . $delta . " days )" : ""; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach;
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
