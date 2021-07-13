<?php
/**
 * VATROC Admin Dashboard
 *
 * @class VATROC_AdminDashboard
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VATROC_AdminDashboard {
    static public $status_table;
    static public $status_table_atc;
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'script_load' ] );
    }


    public static function output() {
        $maxlen_route = 100;
        self::$status_table = new VATROC_CurrStatusTable();
        self::$status_table->prepare_items( VATROC::$PILOT );
        self::$status_table_atc = new VATROC_CurrStatusTable();
?>
<div class="wpcontent">
<div class="wpbody">
    <div class="wpbody-content">
        <div class="welcome-panel">
        <div class="welcome-panel-contet">
            <h2>VATROC Tool Dashboard</h2>
            <h6><?php echo self::$status_table->get_update_timestamp(); ?></h6>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                <h3><?php echo __( 'Statistics', 'vatroc' ) ?></h3>
<?php
        foreach( self::$status_table->get_active_aerodrome() as $icao=>$dep_arr) {
            $dep = count( $dep_arr[0] );
            $arr = count( $dep_arr[1] );
            echo "<p><b>{$icao}</b><span class='dashicons dashicons-arrow-up-alt2'></span> {$dep}";
            echo "<span class='dashicons dashicons-arrow-down-alt2'></span> {$arr}</p>";
        }
?>
                </div>
                <div class="welcome-panel-column">
                    <div data-sec="120" class="timer-box disabled">2 Min</div>
                    <div data-sec="180" class="timer-box disabled">3 Min</div>
                </div>
                <div class="welcome-panel-column">
                </div>
            </div>
        </div>
        </div>
        <div class="postbox-container">
            <div class="postbox">
                <div class="postbox-header">Metars</div>
                <div class="inside">
<?php
        foreach( self::getMetar() as $idx=>$data ) {
            echo "<p><b>$data->station</b> <i>$data->message</i></p>";
        }
?>
                </div>
            </div>
        </div>
        <div class="postbox-container">
            <div class="postbox">
                <div class="postbox-header">ATC</div>
                <div class="inside">
<?php
        $cnt = 0;
        foreach( self::$status_table_atc->getVatsimStatus( VATROC::$ATC ) as $idx=>$atc ) {
            $cnt += 1;
            echo "<p>${atc['frequency']} ${atc['callsign']} ${atc['name']}</p>";
        }
        if ( $cnt == 0 ) {
            echo "<p>No ATC in TPE FIR</p>";
        }
?>
                </div>
            </div>
        </div>
        <div class="postbox-container">
            <div class="postbox">
                <div class="postbox-header">Active Flights</div>
                <div class="inside">
<?php
        self::$status_table->display();
?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php
    }


    public static function getMetar() {
        // optimization plan: save in DB and check validality in time
        $curl = new WP_Http_Curl();
        // $metarJson = $curl->request( "https://aiss.anws.gov.tw/aes/AwsClientMetar?stations=RCTP,RCKH,RCSS,RCBS,RCCM,RCDC,RCFG,RCFN,RCGI,RCKU,RCKW,RCLY,RCMQ,RCMT,RCNN,RCQC,RCWA,RCYU" );
        // default add RCTP, RCSS, RCKH
        $active_aerodrome = self::$status_table->get_active_aerodrome();
        $active_aerodrome[ "RCTP" ] = NULL;
        $active_aerodrome[ "RCSS" ] = NULL;
        $active_aerodrome[ "RCKH" ] = NULL;
        $icaos = implode( array_keys( $active_aerodrome ),',' );
        $metarJson = $curl->request( "https://aiss.anws.gov.tw/aes/AwsClientMetar?stations=" . $icaos );
        $data = json_decode( $metarJson[ "body" ] )->data;
        return $data;
    }


    public function script_load() {
        wp_enqueue_style( 'vatroc-dashboard', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'admin/css/dashboard.css' );
        wp_enqueue_script( 'vatroc-dashboard', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'admin/js/dashboard.js', [ 'jquery' ], null, true );
    }
};


return new VATROC_AdminDashboard();
