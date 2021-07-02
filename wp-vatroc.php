<?php
/**
* Plugin Name: VATROC Tools
* Plugin URI: https://github.com/amgtier/wp-vatroc
* Author: Tzu-Hsiang Chao
* Author URI: https://github.com/amgtier/
* Description: Developing: roster/event, Future: event
* Version: 0.0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
}

if ( ! class_exists ( 'VATROC' ) ){
        include_once dirname( __FILE__ ) . '/includes/class-Constants.php';
        include_once dirname( __FILE__ ) . '/includes/class-vatroc.php';
}

if ( ! defined( 'VATROC_PLUGIN_FILE' ) ) {
        define( 'VATROC_PLUGIN_FILE', __FILE__ );
}

function vatroc(){
        return VATROC::instance();
}

function installer() {
    include( "installer.php" );
}

$GLOBALS[ 'VATROC' ] = vatroc();
register_activation_hook( __file__, "installer" );
