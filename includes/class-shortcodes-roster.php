<?php
/**
 * VATROC Shortcode Roster
 *
 * @class VATROC_Shortcode_Roster
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Roster {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_shortcode( 'vatroc_roster_atc', 'VATROC_Shortcode_Roster::output_atc' );
        add_shortcode( 'vatroc_roster_staff', 'VATROC_Shortcode_Roster::output_staff' );
    }


	public function output_staff() {
        $rosters = self::table_data( VATROC::$STAFF );
        usort( $rosters, "self::sort_staff" );
        $ret = "";
        
        $ret .= "<table>";
        $ret .= "<thead><tr><th></th><th>ROLE</th><th>NAME</th><th>UID</th><th>EMAIL</th></thead>";
        foreach( $rosters as $idx=>$atc ) {
            $ret .= sprintf( "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", 
                $atc[ "vatroc_staff_number" ] ? "VATROC{$atc[ 'vatroc_staff_number' ]}" : "", 
                $atc[ "vatroc_staff_role" ],
                $atc[ "display_name" ], 
                $atc[ "vatroc_vatsim_uid" ]
            );
        }
        $ret .= "</table>";
        return $ret;
    }


	public function output_atc() {
        $ret = "";
        $rosters = self::table_data( VATROC::$ATC_LOCAL );
        usort( $rosters, "self::sort_atc" );
        $visiting = self::table_data( VATROC::$ATC_VISITING );
        $solo = self::table_data( VATROC::$ATC_SOLO );
        if ( count( $solo ) ) {
            usort( $solo, "self::sort_atc" );
            $ret .= self::roster_table( $solo, VATROC::$ATC_SOLO );
        }
        $ret .= self::roster_table( $rosters, VATROC::$ATC_LOCAL );
        if ( count( $visiting ) ) {
            usort( $visiting, "self::sort_atc" );
            $ret .= self::roster_table( $visiting, VATROC::$ATC_VISITING );
        }
        return $ret;
    }


    private static function roster_table( $r, $title ) {
        $ret = "";
        switch ( $title ) {
        case VATROC::$ATC_LOCAL:
            $ret .= "<h1>ATC Roster</h1>"; break;
        case VATROC::$ATC_VISITING:
            $ret .= "<h1>Visiting Controller</h1>"; break;
        case VATROC::$ATC_SOLO:
            $ret .= "<h1>Solo OJT Validation</h1>"; break;
        }

        $ret .= "<table>";
        $ret .= "<thead><tr><th>UID</th><th>NAME</th><th>POSITION</th><th>RATING</th>";
        switch ( $title ) {
        case VATROC::$ATC_VISITING:
            $ret .= "<th>HOME DIVISION</th>"; break;
        case VATROC::$ATC_SOLO:
            $ret .= "<th>SOLO VALID UNTIL</th>"; break;
        }
        $ret .= "</tr></thead>";
        foreach( $r as $idx=>$atc ) {
            $ret .= sprintf( "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td>", 
                $atc[ "vatroc_vatsim_uid" ], 
                $atc[ "display_name" ], 
                VATROC::$atc_position[ $atc[ "vatroc_position" ] ],
                VATROC::$vatsim_rating[ $atc[ "vatroc_vatsim_rating" ] ]
            );
            switch ( $title ) {
            case VATROC::$ATC_VISITING:
                $ret .= "<td>{$atc[ "vatroc_home_division" ]}</td>"; break;
            case VATROC::$ATC_SOLO:
                $ret .= "<td>{$atc[ "vatroc_solo_valid_until" ]}</td>"; break;
            }

            if ( current_user_can( VATROC::$admin_options ) ) {
                $ret .= "<td><a target='_blank' href='" . get_edit_user_link( $atc[ "ID" ] ) . "'>Edit</a></td>";
            }

            $ret .= "</tr>";
        }
        $ret .= "</table>";
        return $ret;
    }


    public function table_data( $type ) {
        global $wpdb;
        $meta_prefix = self::$meta_prefix;
        $sql = "SELECT ID,display_name FROM {$wpdb->prefix}users";

        $sql_usermeta = "SELECT user_id,meta_key,meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE '{$meta_prefix}%'";

        $data = $wpdb->get_results( $sql, 'ARRAY_A' );
        $usermeta = $wpdb->get_results( $sql_usermeta, 'ARRAY_A' );

        // Preserve for future use.
        // for( $i = 0; $i < count( $data ); $i += 1 ) {
        //     $data[ $i ][ "display_name" ] = "<a href='" . get_edit_user_link( $data[ $i ][ "ID" ] ) . "#profile-vatroc-tool' target='_balnk'>{$data[ $i ][ "display_name" ]}</a>";
        // }


        foreach( $usermeta as $idx=>$val ) {
            $entry = null;
            $i = 0;
            for ( ; $i < count( $data ); $i += 1 ){
                if ( $data[ $i ][ "ID" ] == $val[ "user_id" ] ) {
                    break;
                }
            }
            if ( $i == count( $data ) ) continue;

            $data[ $i ][ $val[ "meta_key" ] ] = $val[ "meta_value" ];
        }

        $rosters = array();
        switch ( $type ) {
        case VATROC::$ATC_LOCAL:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$meta_prefix}position" ] ) &&
                    ( !isset( $data[ $idx ][ "${meta_prefix}home_division" ] ) || $data[ $idx ][ "${meta_prefix}home_division" ] == "VATROC" ) &&
                    $data[ $idx ][ "{$meta_prefix}position" ] > 0 ) {
                    array_push( $rosters, $data[ $idx ] );
                }
            } break;
        case VATROC::$ATC_VISITING:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$meta_prefix}position" ] ) &&
                    ( isset( $data[ $idx ][ "${meta_prefix}home_division" ] ) && $data[ $idx ][ "${meta_prefix}home_division" ] != "VATROC" ) &&
                    $data[ $idx ][ "{$meta_prefix}position" ] > 0 ) {
                    array_push( $rosters, $data[ $idx ] );
                }
            } break;
        case VATROC::$ATC_SOLO:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$meta_prefix}position" ] ) &&
                    isset( $data[ $idx ][ "{$meta_prefix}solo_valid_until" ] ) &&
                    $data[ $idx ][ "{$meta_prefix}position" ] > 0 ) {
                    array_push( $rosters, $data[ $idx ] );
                }
            } break;
        case VATROC::$STAFF:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$meta_prefix}staff_role" ] ) ) {
                    array_push( $rosters, $data[ $idx ] );
                }
            } break;
        }

        return $rosters;
    }


    private static function sort_atc($a, $b) {
        $meta_prefix = self::$meta_prefix;
        if ( $a[ "${meta_prefix}vatsim_rating" ] == $b[ "${meta_prefix}vatsim_rating" ] ) {
            if ( $a[ "${meta_prefix}position" ] == $b[ "${meta_prefix}position" ] ) {
                return intval( $a[ "${meta_prefix}vatsim_uid" ] ) > intval( $b[ "${meta_prefix}vatsim_uid" ] );
            }
            return intval( $a[ "${meta_prefix}position" ] ) < intval( $b[ "${meta_prefix}position" ] );
        } else {
            return intval( $a[ "${meta_prefix}vatsim_rating" ] ) < intval( $b[ "${meta_prefix}vatsim_rating" ] );
        }
    }


    private static function sort_staff($a, $b) {
        $meta_prefix = self::$meta_prefix;
        if ( $a[ "${meta_prefix}staff_number" ] == NULL ) {
            return true;
        }
        if ( $b[ "${meta_prefix}staff_number" ] == NULL ) {
            return false;
        }
        return intval( $a[ "${meta_prefix}staff_number" ] ) > intval( $b[ "${meta_prefix}staff_number" ] );
    }
};

VATROC_Shortcode_Roster::init();
