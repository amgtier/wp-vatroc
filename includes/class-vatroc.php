<?php
class VATROC extends VATROC_Constants {
    public $version = '0.0.1';
    public static $log = false;
    public static $log_enabled = false;
    protected static $_instance = null;


    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $this->define( 'VATROC_ABSPATH', dirname( VATROC_PLUGIN_FILE ) . '/' );
        add_action( 'init', array( $this, 'includes' ), 8 );
    }


    public function includes() {
        include_once( VATROC_ABSPATH . 'admin/class-admin.php' );
        include_once( VATROC_ABSPATH . 'includes/class-my.php' );
        include_once( VATROC_ABSPATH . 'includes/class-poll.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcodes-roster.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcodes-homepage.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcodes-atc.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcodes-event.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcodes-poll.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcodes-my.php' );
        include_once( VATROC_ABSPATH . 'includes/vatroc-hook-functions.php' );
    }


    public static function actionLog( $user_id, $actionKey, $actionValue ) {
        global $wpdb;
        $result = $wpdb->insert( "{$wpdb->prefix}vatroc_log", array(
            'user'  => $user_id,
            'key'   => $actionKey,
            'value' => $actionValue
        ) );
        return $result;
    }


    public static function debug_section( $level = null ) {
        $level = $level ?: self::$admin_options;
        return current_user_can( $level );
    }


    public static function dlog(){
        $log_path = plugin_dir_path( __DIR__ ) . "deLogVATROC.txt";
        $prefix = 'debug';
        $identifier = null;
        if ( !file_exists( $log_path ) ) { fopen( $log_path, 'w' ); }
        foreach (func_get_args() as $param) {
            if ( is_array( $param ) ){
                error_log( sprintf( "[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, urldecode( http_build_query( $param ) ) ), 3, $log_path);
            } else {
                error_log( sprintf( "[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, $param ), 3, $log_path);
            }
        }
    }


    public static function log( $message, $level = 'info', $prefix = null, $identifier = null ) {
        $log_path = plugin_dir_path( __DIR__ ) . "LogVATROC.txt";
        if ( $level = 'debug' ){
            if ( $prefix ) {
                $prefix = "[" . $prefix . "]";
            }
            if ( $identifier ) {
                $identifier = "[" . $identifier. "]";
            }
            if ( !file_exists( $log_path ) ) { fopen( $log_path, 'w' ); }
            if ( is_array( $message ) ){
                error_log( sprintf( "[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, urldecode( http_build_query( $message ) ) ), 3, $log_path);
            } else {
                error_log( sprintf( "[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, $message ), 3, $log_path);
            }
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                     self::$log = wc_get_logger();
                }
                 self::$log -> log($level, $message, array('source' => 'vatroc'));
            }
        }
    }


    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }


    public static function get_template( $path ) {
        include(plugin_dir_path( __DIR__ ) . $path);
    }
}
