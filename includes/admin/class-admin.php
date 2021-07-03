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
        include_once( VATROC_ABSPATH . '/includes/admin/class-adminDashboard.php' );
        include_once( VATROC_ABSPATH . '/includes/admin/class-adminRoster.php' );
    }


    private function init() {
        $this -> add_admin_menu();
    }


    private function add_admin_menu() {
        add_menu_page(
            __( 'VATROC', 'vatroc' ),
            __( 'VATROC', 'vatroc' ),
            'manage_options',
            'vatroc',
            array( $this, 'dashboard'),
            null,
            100
        );
        add_submenu_page(
            'vatroc',
            __('ATC Roster', 'vatroc'),
            __('ATC Roster', 'vatroc'),
            'manage_options',
            'vatroc-atcroster',
            array( $this, 'atc_roster')
        );
        add_submenu_page(
            'vatroc',
            __('STAFF Roster', 'vatroc'),
            __('STAFF Roster', 'vatroc'),
            'manage_options',
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
};

return new VATROC_Admin();
