<?php
/**
 * VATROC Admin
 *
 * @class VATROC_Admin
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VATROC_Admin {
    public function __construct() {
        $this -> init();
        include_once( VATROC_ABSPATH . '/admin/class-adminDashboard.php' );
        include_once( VATROC_ABSPATH . '/admin/class-adminRoster.php' );
        include_once( VATROC_ABSPATH . '/admin/includes/class-adminCurrStatus.php' );
        include_once( VATROC_ABSPATH . '/admin/includes/class-adminMagicCharts.php' );
        include_once( VATROC_ABSPATH . '/admin/vatroc-admin-hook-functions.php' );
    }


    private function init() {
        $this -> add_admin_menu();
    }


    private function add_admin_menu() {
        $privilege = 'publish_posts';
        add_menu_page(
            __( 'VATROC', 'vatroc' ),
            __( 'VATROC', 'vatroc' ),
            $privilege,
            'vatroc',
            array( $this, 'dashboard'),
            'dashicons-superhero',
            1
        );
        add_submenu_page(
            'vatroc',
            __('Magic Charts', 'vatroc'),
            __('Magic Charts', 'vatroc'),
            $privilege,
            'vatroc-magiccharts',
            array( $this, 'magic_charts')
        );
        add_submenu_page(
            'vatroc',
            __('ATC Roster', 'vatroc'),
            __('ATC Roster', 'vatroc'),
            $privilege,
            'vatroc-atcroster',
            array( $this, 'atc_roster')
        );
        add_submenu_page(
            'vatroc',
            __('Staff Roster', 'vatroc'),
            __('Staff Roster', 'vatroc'),
            $privilege,
            'vatroc-staffroster',
            array( $this, 'staff_roster')
        );
    }


    public function dashboard() {
        VATROC_AdminDashboard::output();
    }


    public function atc_roster() {
        VATROC_AdminRoster::output( VATROC::$ATC );
    }


    public function staff_roster() {
        VATROC_AdminRoster::output( VATROC::$STAFF );
    }


    public function magic_charts() {
        VATROC_AdminMagicCharts::output();
        // $am = new VATROC_AdminMagicCharts;
        // $am->output();
    }
};

return new VATROC_Admin();
