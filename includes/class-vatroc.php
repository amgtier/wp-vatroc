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
        self::enqueue_scripts();
    }
    

    public function includes() {
        include_once( VATROC_ABSPATH . 'admin/class-admin.php' );
        include_once( VATROC_ABSPATH . 'includes/class-atc.php' );
        include_once( VATROC_ABSPATH . 'includes/class-event.php' );
        include_once( VATROC_ABSPATH . 'includes/class-form.php' );
        include_once( VATROC_ABSPATH . 'includes/class-my.php' );
        include_once( VATROC_ABSPATH . 'includes/class-poll.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-event.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-form.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-roster.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-homepage.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-poll.php' );
        include_once( VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-my.php' );
        include_once( VATROC_ABSPATH . 'includes/vatroc-hook-functions.php' );
    }


    function enqueue_scripts() {  
        add_action( 'wp_enqueue_scripts', 'enqueue_scripts', 1000000001 );
        wp_enqueue_script( 'boot2','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
        wp_enqueue_script( 'boot3','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
        
        wp_enqueue_style( 'styles', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/css/styles.css' );
        wp_enqueue_style( 'flex', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/css/flex.css' );
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


    public static function is_admin() {
        return current_user_can( self::$admin_options );
    }


    public static function is_staff( $uid ) {
        $user = new WP_User( $uid );
        return ! empty( $user->roles ) && 
        is_array( $user->roles ) && 
        in_array( 'editor', $user->roles );
    }


    public static function is_priviledged_atc( $uid ) {
        $user = new WP_User( $uid );
        return ! empty( $user->roles ) && 
        is_array( $user->roles ) && 
        in_array( 'author', $user->roles );
    }


    public static function debug_section( $uid = 1 ) {
        return get_current_user_id() == $uid;
    }


    public static function dog(){
        $log_path = plugin_dir_path( __DIR__ ) . "dog.txt";
        $prefix = 'debug';
        $identifier = null;
        if ( !file_exists( $log_path ) ) { fopen( $log_path, 'w' ); }

        error_log( sprintf( "[%s]%s%s: ", date("Y/m/d H:i:s", time()), $prefix, $identifier ), 3, $log_path);
        $s = null;
        foreach (func_get_args() as $param) {
            if ( is_array( $param ) ){
                $s = sprintf( "(array)  %s ", urldecode( http_build_query( $param ) ) );
            } else if ( is_bool( $param ) ) {
                $s = sprintf( "(bool)   %s ", $param ? "true" : "false" );
            } else if ( is_object( $param ) ) {
                $s = sprintf( "(object) %s ", urldecode( http_build_query( $param ) ) );
            } else {
                $s = sprintf( "%s ", $param );
            }
            error_log( $s, 3, $log_path);
        }
        error_log( "\n", 3, $log_path);
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
            } else if ( is_bool( $message ) ) {
                error_log( sprintf( "[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, $message ? "true" : "false" ), 3, $log_path);
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


    public static function get_template( $path, $variables = [] ) {
        extract( $variables );
        include( plugin_dir_path( __DIR__ ) . $path );
    }

    
    public static function enqueue_ajax_object( $handler, $page_id = null, $variables = [] ) {
        $arguments = [ 
            'ajax_url' => admin_url( 'admin-ajax.php' ), 
            'page_id' => $page_id ?: get_the_ID() 
        ];

        foreach( $variables as $key => $val ){
            $arguments[ $key ] = $val;
        }

        wp_localize_script( 
            $handler,
            'ajax_object',
            $arguments,
        );
    }


    public static function get_today() {
        return self::get_date( date( 'Y' ), date( 'm' ), date( 'd' ) );
    }


    public static function get_date( $y, $m, $d ) {
        return sprintf( "%04d/%d/%02d", $y, $m % 12, $d );
    }
}
