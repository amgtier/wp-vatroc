<?php
class VATROC extends VATROC_Constants {
    public $version = '0.0.1';
    protected static $_instance = null;


    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $this -> define( 'VATROC_ABSPATH', dirname( VATROC_PLUGIN_FILE ) . '/' );
        add_action( 'init', array( $this, 'includes' ), 8 );
    }


    public function includes() {
        include_once( VATROC_ABSPATH . 'includes/admin/class-admin.php' );
        include_once( VATROC_ABSPATH . 'includes/vatroc-filter-functions.php' );
    }


    public static function actionLog( $user_id, $actionKey, $actionValue ) {
        global $wpdb;
        $result = $wpdb->insert( "{$wpdb->prefix}vatroc_log", array(
            'user'  => $user_id,
            'key'   => $actionKey,
            'value' => $actionValue
        ) );
        var_dump( $result );
        return $result;
    }


    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }
}
