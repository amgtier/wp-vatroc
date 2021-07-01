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
        include_once( VATROC_ABSPATH . '/includes/admin/class-Constants.php' );
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
            __('Roster', 'vatroc'),
            __('Roster', 'vatroc'),
            'manage_options',
            'vatroc-roster',
            array( $this, 'roster')
        );

    }


    public function dashboard() {
        VATROC_AdminDashboard::output();
    }


    public function roster() {
        VATROC_AdminRoster::output();
    }
};

return new VATROC_Admin();
