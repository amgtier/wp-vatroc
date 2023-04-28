<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_My {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_shortcode( 'vatroc_my_vatroc', 'VATROC_Shortcode_My::output_my' );
        add_shortcode( 'vatroc_my_editable_field', 'VATROC_Shortcode_My::editable_my' );
        add_shortcode( 'vatroc_my_avatar', 'VATROC_Shortcode_My::my_avatar' );
        add_shortcode( 'vatroc_live', 'VATROC_Shortcode_My::vatroc_live_router' );
        add_action( "wp_ajax_vatroc_my_editable_field", "VATROC_Shortcode_My::ajax_set_field" );
        add_action( "wp_ajax_vatroc_my_set_atc_date_from_sess", "VATROC_Shortcode_My::ajax_set_atc_date" );
        add_action( "wp_ajax_vatroc_set_self_applicant", "VATROC_Shortcode_My::ajax_set_self_applicant" );
        wp_enqueue_style( 'vatroc-poll', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/poll.css' );
    }


    public static function output_my() {
        VATROC::dog(VATROC_My::get_vatroc_position());
        if ( VATROC_My::get_vatroc_position() != -1 && ( !is_user_logged_in() || 
            get_user_meta( get_current_user_id(), "vatroc_vatsim_uid", true ) == null ) 
        ) {
            return null;
        }

        $ret = "";
        if ( VATROC::debug_section( 503 ) ){
            $ret .= self::trainee();
        }
        $ret .= self::atc();
        return $ret;
    }


    public static function my_avatar( $atts ) {
        $uid = get_current_user_id();
        switch ( $atts[ "type" ]) {
            case "with_position":
                return VATROC_My::html_my_avatar_with_position( $uid );
                break;
        }
        return VATROC_My::html_my_avatar( $uid );
    }


    private static function atc() {
        $uid = get_current_user_id();
        ob_start();
        VATROC::get_template( "includes/shortcodes/templates/my/atc-section.php", [ 
            "vatsim_uid" => get_user_meta( $uid, "vatroc_vatsim_uid", true ),
            "vatsim_rating" => get_user_meta( $uid, "vatroc_vatsim_rating", true ),
            "vatroc_position" => get_user_meta( $uid, "vatroc_position", true ),
            ] );
        return ob_get_clean();
    }


    private static function trainee() {
        ob_start();
        VATROC::get_template( "includes/shortcodes/templates/my/trainee-section.php" );
        return ob_get_clean();
    }

    /*
    * Getter and setter mapping needs to be defined first.
    */
    public static function editable_my( $atts ) {
        
        $frontend_field_name = $atts[ "display_name" ];
        $backend_field_name = $atts[ "name" ];
        $is_autosave = in_array( "autosave", $atts );
        $str_autosave = $is_autosave ? "autosave" : null;
        $current_value = VATROC_My::field_value( $backend_field_name );
        
        wp_enqueue_script( 'vatroc-my', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/my.js', array( 'jquery' ), null, true );
        VATROC::enqueue_ajax_object( 'vatroc-my', null );

        ob_start();
?>
    <form id="edit-<?php echo $backend_field_name; ?>" method="get" action="#">
        <label for="<?php echo $backend_field_name; ?>"><?php echo $frontend_field_name; ?>
            <input 
                type="text" id="<?php echo $backend_field_name; ?>" 
                class="editable-my <?php echo $str_autosave; ?>" name="<?php echo $backend_field_name; ?>" 
                value="<?php echo $current_value; ?>" 
                required 
            />
            <?php if ( !$is_autosave ): ?>
                <button id="submit-<?php echo $backend_field_name; ?>" hidden>送出修改</button>
            <?php endif; ?>
        </label>
    </form>
<?php
        $ret = ob_get_clean();
        return $ret;
    }



    public static function ajax_set_field() {
        $uid = get_current_user_id();
        $field_name = $_POST[ "field_name" ];
        $value = $_POST[ "value" ];

        echo VATROC_My::set_field_value( $uid, $field_name, $value );
        wp_die();
    }


    public static function ajax_set_atc_date() {
        $uid = $_POST[ "user" ];
        $date = $_POST[ "date" ];
        $key = $_POST[ "key" ];
        
        VATROC::log( sprintf( "%s updated %s %s. old: %s; new: %s.",
            get_current_user_id(), 
            $uid,
            $key,
            get_user_meta( $uid, "vatroc_date_" . $key, true ),
            $date)
        );

        update_user_meta( $uid, "vatroc_date_" . $key, $date );
        
        wp_die();
    }


    public static function ajax_set_self_applicant() {
        $new_position = -1; // Applicant
        $uid = get_current_user_id();
        $curr_position = VATROC_My::get_vatroc_position( $uid );

        if ( $curr_position > 0 ){
            wp_die( "User has position higher than Applicant", "Error", 500 );
        }

        VATROC::log( sprintf( "%s set themself %s; was %s",
            $uid,
            VATROC::$atc_position[ $new_position ],
            VATROC::$atc_position[ $curr_position ],
            )
        );

        VATROC_My::set_vatroc_position( $uid, $new_position );

        wp_die();
    }
};

VATROC_Shortcode_My::init();
