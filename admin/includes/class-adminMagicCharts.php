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

        echo "<div class='wrap'>";
        self::search_form();

        self::suggestion_box();

        echo "<div id='dashboard-widgets' class='metabox-holder'>";
        self::chart_box( 'https://eaip.caa.gov.tw/eaip/history/2021-07-01/graphics/263172.pdf' );
        self::chart_box( 'https://www.vatroc.net/' );
        echo "</div>";

        echo "</div>";
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
    <div class="postbox-container">
        <h1>Chart</h1>
        <iframe src="<?php echo $url; ?>"></iframe>
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
        wp_enqueue_script( 'magic-charts', plugin_dir_url( VATROC_PLUGIN_FILE ) . '/admin/js/magic_charts.js', array( 'jquery' ), null, true );
    }


    public function script_load_jquery () {
        wp_enqueue_script( 'jquery' );
    }
};

return new VATROC_AdminMagicCharts();
