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


    public function __construct() {
    }

    public function output() {
?>
<div class="wpcontent">
<div class="wpbody">
    <div class="wpbody-content">
        <div class="welcome-panel">
        <div class="welcome-panel-contet">
            <h2>VATROC Tool Dashboard</h2>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                C1
                </div>
                <div class="welcome-panel-column">
                C2
                </div>
                <div class="welcome-panel-column">
                C3
                </div>
            </div>
        </div>
        </div>
        <div class="postbox-container">
            <div class="postbox">
                <div class="postbox-header">Metars</div>
                <div class="inside">Metars</div>
            </div>
        </div>
    </div>
</div>
</div>
<?php
    }

};

return new VATROC_AdminDashboard();
