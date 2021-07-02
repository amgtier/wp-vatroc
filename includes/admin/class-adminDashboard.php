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
    public static function output() {
?>
<div class="wpcontent">
<div class="wpbody">
    <div class="wpbody-content">
        <div class="welcome-panel">
        <div class="welcome-panel-contet">
            <h2>VATROC Tool Dashboard</h2>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                </div>
                <div class="welcome-panel-column">
                </div>
                <div class="welcome-panel-column">
                </div>
            </div>
        </div>
        </div>
        <!-- <div class="postbox-container">
            <div class="postbox">
                <div class="postbox-header">Charts</div>
                <div class="inside">
                </div>
            </div>
        </div> -->
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
    </div>
</div>
</div>
<?php
    }
    public static function getMetar() {
        $curl = new WP_Http_Curl();
        $metarJson = $curl->request( "https://aiss.anws.gov.tw/aes/AwsClientMetar?stations=RCTP,RCKH,RCSS,RCBS,RCCM,RCDC,RCFG,RCFN,RCGI,RCKU,RCKW,RCLY,RCMQ,RCMT,RCNN,RCQC,RCWA,RCYU" );
        $data = json_decode( $metarJson[ "body" ] )->data;
        return $data;
    }
};

return new VATROC_AdminDashboard();
