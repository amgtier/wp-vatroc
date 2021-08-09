<?php
/**
 * VATROC Admin Current Status Table
 *
 * @class VATROC_AdminCurrStatusTable
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class VATROC_CurrStatusTable extends WP_List_Table {
    public $meta_prefix = "vatroc_";
    public $perPage = 1000;
    private $list_type;
    private $active_aerodrome = array();
    private $update_timestamp;


    public function get_update_timestamp() {
        return $this->update_timestamp;
    }


    public function get_active_aerodrome() {
        return $this->active_aerodrome;
    }


	public function prepare_items( $type ) {
        $this->$active_aerodrome = array();
        $this->list_type = $type;

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $this->perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$this->perPage),$this->perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }


    public function get_columns() {
        switch ( $this->list_type ){
        case VATROC::$PILOT:
            $columns = array(
                "callsign"  => __( 'Callsign', 'vatroc' ),
                "departure"   =>  __( "Dep", "vatroc" ),
                "arrival"   =>  __( "Arr", "vatroc" ),
                "altitude"   =>  __( "Alt", "vatroc" ),
                "name"   =>  __( "Name", "vatroc" ),
                "route"   =>  __( "Route", "vatroc" )
            ); break;
        }
        return $columns;
    }

    
    public function get_hidden_columns() {
        return array();
    }


    public function get_sortable_columns() {
        switch ( $this->list_type ) {
        case VATROC::$PILOT:
            return array(
                'name' => array( 'name', false ),
                'departure' => array( 'departure', false ),
                'arrival' => array( 'arrival', true ),
                'altitude' => array( 'altitude', true )
            );
        }
    }


    public function column_default( $item, $column_name ) {
        // todo: rolling eaip_ver
        $eaip_ver = "2021-07-01";
        $eaip_sec="AD-2.24";
        $maxlen_route = 50;
        switch( $column_name ) {
            case "callsign":
            case "name":
            case "altitude":
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : NULL;
                // return isset( $item[ $column_name ] ) ? $item[ $column_name ] : NULL;
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : NULL;
            case "route":
                $route = $item[ "route" ];
                $route_prefix = "";
                $route_suffix = "";
                if ( substr( $item[ "departure" ], 0, 2 ) == VATROC::$icao_prefix ) {
                    $route = "<i>" . substr( $route, 0, $maxlen_route ) . "</i>";
                    if ( strlen( $route ) > $maxlen_route ) $route_suffix = "<b>... </b>" . $route_suffix;
                } else if ( substr( $item[ "arrival" ], 0, 2 ) == VATROC::$icao_prefix ) {
                    if ( strlen( $route ) > $maxlen_route ) $route_prefix .= "<b>... </b>";
                    $route = "<i>" . substr( $route, strlen( $route ) - $maxlen_route, strlen( $route ) ) . "</i>";
                }
                return $route_prefix . $route . $route_suffix;
            case "departure":
            case "arrival":
                $icao_code = strtoupper( $item[ $column_name ] );
                if ( substr( $icao_code, 0, 2 ) == VATROC::$icao_prefix ) {
                    $icao_code = "<a href='http://eaip.caa.gov.tw/eaip/history/{$eaip_ver}/html/eAIP/RC-AD-2.{$icao_code}-en-TW.html#{$icao_code}-{$eaip_sec}' target=_blank>{$icao_code}</a>";
                }
                return $icao_code;
            default:
                return print_r( $item, true );
        }
    }


    public function table_data() {
        return $this->getVatsimStatus( $this->list_type );
    }


    // saved for future implementation
    // protected function column_display_name( $item ) {
    //    $actions = array(
    //        'edit'      => sprintf('<a href="' . get_edit_user_link( $item[ "ID" ] ) . '">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
    //    );
    //    // return '<a href="/"><span class="dashicons dashicons-media-text"></span></a>' . // personal report
    //    return $item[ 'display_name' ] . $this->row_actions($actions);
    // }


    private function getRawVatsimStatus( $real_time ) {
        // optimization: save in db and check if db saved is recently enough.
        if ( $real_time ) {
            // from curl
            $curl = new WP_Http_Curl();
            return $curl->request( "https://data.vatsim.net/v3/vatsim-data.json" );
        } else {
            // from file
            $log_dir = "/home/amgtier/VATROC_Logs/logs";
            $latest_file = scandir( $log_dir, 1 );
            if ( $latest_file ) {
                $latest_file = $log_dir . '/' . $latest_file[0];
                $fp = fopen( $latest_file, "r" );
                if ( $fp ) {
                    $status = fread( $fp , filesize( $latest_file ) );
                    fclose( $fp );
                    return [ "body" =>  $status ];
                }
            }
            return $this->getRawVatsimStatus( true );
        }
    }


    public function getVatsimStatus( $type ) {
        // $status = $this->getRawVatsimStatus( false );
        $status = $this->getRawVatsimStatus( true );
        $pilots = null;
        $result = array();
        $data = json_decode( $status[ "body" ] );
        if ( $type == VATROC::$PILOT ) {
            $pilots = $data->pilots;
            $this->update_timestamp = $data->general->update_timestamp;
            foreach ( $pilots as $idx=>$pilot ) {
                if ( substr( $pilot->flight_plan->departure, 0, 2 ) == VATROC::$icao_prefix ||
                    substr( $pilot->flight_plan->arrival, 0, 2 ) == VATROC::$icao_prefix ) {

                    if ( substr( $pilot->flight_plan->departure, 0, 2 ) == VATROC::$icao_prefix ) {
                        if ( !isset( $this->active_aerodrome[ $pilot->flight_plan->departure ] ) )  $this->active_aerodrome[ $pilot->flight_plan->departure ] = array(array(), array());
                        array_push( $this->active_aerodrome[ $pilot->flight_plan->departure ][ 0 ], $pilot->callsign );
                    }

                    if ( substr( $pilot->flight_plan->arrival, 0, 2 ) == VATROC::$icao_prefix ) {
                        if ( !isset( $this->active_aerodrome[ $pilot->flight_plan->arrival ] ) )  $this->active_aerodrome[ $pilot->flight_plan->arrival ] = array(array(), array());
                        array_push( $this->active_aerodrome[ $pilot->flight_plan->arrival ][ 1 ], $pilot->callsign );
                    }

                    array_push( $result, array(
                        "callsign"  => $pilot->callsign,
                        "departure" => $pilot->flight_plan->departure,
                        "arrival"   => $pilot->flight_plan->arrival,
                        "altitude"  => $pilot->altitude,
                        "name"      => $pilot->name,
                        "route"     => $pilot->flight_plan->route
                    ) );
                }
            }
        } else if ( $type == VATROC::$ATC ) {
            $atcs = $data->controllers;
            foreach( $atcs as $idx=>$atc ) {
                if ( $atc->facility > 0 && ( substr( $atc->callsign, 0, 2 ) == VATROC::$icao_prefix || substr( $atc->callsign, 0, 3 ) == "TPE" ) ) {
                    # array_push( $result, $atc );
                    array_push( $result, array(
                        "callsign" => $atc->callsign,
                        "name"     => $atc->name,
                        "frequency"=> $atc->frequency
                    ) );
                }
            }
        }
        return $result;
    }


	private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
};
