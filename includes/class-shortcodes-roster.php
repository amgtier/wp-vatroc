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
        foreach( $rosters as $idx=>$atc ) {
            echo sprintf( "<p>VATROC%s %s %s</p>", 
                $atc[ "vatroc_staff_number" ], 
                $atc[ "display_name" ], 
                $atc[ "vatroc_staff_role" ]
            );
        }
    }


	public function output_atc() {
        $rosters = self::table_data( VATROC::$ATC );
        foreach( $rosters as $idx=>$atc ) {
            echo sprintf( "<p>%s %s %s %s</p>", 
                $atc[ "vatroc_vatsim_uid" ], 
                $atc[ "display_name" ], 
                VATROC::$vatsim_rating[ $atc[ "vatroc_vatsim_rating" ] ], 
                VATROC::$atc_position[ $atc[ "vatroc_position" ] ] 
            );
        }
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
        case VATROC::$ATC:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$meta_prefix}position" ] ) &&
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
};

VATROC_Shortcode_Roster::init();
