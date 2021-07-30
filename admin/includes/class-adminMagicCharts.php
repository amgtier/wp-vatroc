<?php
/**
 * VATROC Admin Magic Charts
 *
 * @class VATROC_AdminMagicCharts
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VATROC_AdminMagicCharts {
    static public $curr_status;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'script_load' ] );
    }


    static public function output() {
        self::$curr_status = new VATROC_CurrStatusTable();
        self::$curr_status->prepare_items( VATROC::$PILOT );
        
        // wordaround to get jquery-ui css
        echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';
        echo "<div id='before-chart-box' class='wrap'>";

        self::suggestion_box();
        self::selection_box();

        echo "<div id='dashboard-widgets' class='metabox-holder'>";
        self::chart_box( 'https://charts.vatroc.net/2021-07-01/263172.pdf-1.jpg' );
        // self::chart_box( 'https://charts.vatroc.net/2021-07-01/262365.pdf-1.jpg' );
        echo "</div>";

        echo "</div>";
    }


    static private function selection_box() {
        $curl = new WP_Http_Curl();
        $res = $curl->request( "https://charts.vatroc.net/2021-07-01/charts.csv" );
        $charts = array(
            "" => array(),
            "RCTP" => array(),
            "RCKH" => array(),
            "RCSS" => array()
        );
        foreach( explode( "\n", $res[ "body" ] ) as $key=>$val ) {
            $c = explode( ",", $val );
            if ( !array_key_exists( $c[1], $charts ) ) {
                $charts[ $c[1] ] = array();
            } 
            array_push( $charts[ $c[1] ], [ $c[3], $c[2] ] );
        }
        echo '<div class="">';
        foreach( $charts as $ap=>$_charts ) {
            usort( $_charts, [ self, 'chart_compare' ] );
            echo "<select class='toggle-charts'>";
            echo "<option disabled selected>$ap</option>";
            foreach( $_charts as $key=>$c ) {
                echo "<option data-target='$c[0]'>$c[1]</option>";
            }
            echo '</select>';
        }
        echo '</div>';
    } 


    static private function chart_compare( $e1, $e2 ) {
        return $e1[1] < $e2[1];
    }


    static private function suggestion_box() {
?>
    <div class="">
        <h1>Suggestions</h1>
<?php foreach( self::$curr_status->get_active_aerodrome() as $key=>$val ) {
        echo "<b>$key</b> ";
    } 
?>
    </div>
<?php
    }


    static private function chart_box( $url ) {
?>
    <div style="position: absolute; border: 1px dashed #000; overflow: auto; z-index: 9991;" class="postbox-container magic-charts chart-box ui-widget-header">
        <span class="close-chart" style="position: absolute; right: 0; cursor: pointer;">X</span>
        <span class="chart-move-up" style="position: absolute; right: 20px; cursor: pointer;">^</span>
        <span class="chart-move-down" style="position: absolute; right: 40px; cursor: pointer;">v</span>
        <h1>Chart</h1>
        <img style="width: 100%;" src="<?php echo $url; ?>" />
        <!-- <iframe style="resize: both;" src="<?php echo $url; ?>"></iframe> -->
    </div>
<?php

    }


    static private function search_form() {
?>
   <form role="search" method="get" class="search-form" action="https://www.vatroc.net/">
       <label>
           <span class="screen-reader-text">Search for:</span>
           <input type="search" class="search-field" placeholder="Search …" value="" name="s">
       </label>
       <input type="submit" class="search-submit" value="Search">
   </form> 
<?php
    }


    public function script_load () {
        wp_enqueue_script( 'magic-charts', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'admin/js/magic_charts.js', array( 'jquery' ), null, true );
        wp_enqueue_script( 'magic-charts', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'admin/css/magic_charts.css' );
        wp_enqueue_script( 'jquery-ui-core', false, array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-resizable', false, array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-selectmenu', false, array( 'jquery') );
        wp_enqueue_script( 'jquery-ui-touch-punch', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'admin/js/jquery.ui.touch-punch.min.js', array( 'jquery') );
    }


    public function script_load_jquery () {
        wp_enqueue_script( 'jquery' );
    }
};

return new VATROC_AdminMagicCharts();
