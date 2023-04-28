<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_Form extends VATROC_Form {
    public static function init() {
        add_shortcode( 'vatroc_form', 'VATROC_Shortcode_Form::output_form' );
        add_shortcode( 'vatroc_form_field', 'VATROC_Shortcode_Form::output_form_field' );
        add_shortcode( 'vatroc_form_field_card', 'VATROC_Shortcode_Form::output_form_field' );
        add_shortcode( 'vatroc_form_field_internal', 'VATROC_Shortcode_Form::output_form_field_internal' );
        add_shortcode( 'vatroc_form_field_option', 'VATROC_Shortcode_Form::output_form_field_option' );
        add_shortcode( 'vatroc_form_field_option_internal', 'VATROC_Shortcode_Form::output_form_field_option_internal' );
        add_shortcode( 'vatroc_form_field_card_internal', 'VATROC_Shortcode_Form::output_form_field_internal' );
        add_action( 'wp_enqueue_script', 'VATROC_Shortcode_Form::enqueue_script', 1000000001 );
    }


    public static function enqueue_script() {
        // wp_enqueue_script( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/form.js', array( 'jquery' ), null, true );
        // wp_enqueue_style( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/form.css' );
    }


    public static function output_form( $atts, $content = null ) {
        $is_autosave = in_array( "autosave", $atts );
        $str_autosave = $is_autosave ? "autosave" : null;
        $form_name = "shortcode-form";

        $content = preg_replace( '@\[vatroc_form_field_card @', "[vatroc_form_field_card_internal ", $content );
        $content = preg_replace( '@\[/vatroc_form_field_card]@', "[/vatroc_form_field_card_internal]", $content );
        if ( VATROC::debug_section() ) {
            $ret = '';
            if ( isset( $_GET[ "view_all" ] ) || isset( $_GET[ "view" ]) ){
                // $ret .= self::output_submission_list();
                if ( isset( $_GET[ "view" ] ) ){
                    $read_only_version = isset( $_GET[ "v" ] ) ? intval( $_GET[ "v" ] ) : 1;
                    $read_only_uid = isset( $_GET[ "u" ] ) && intval( $_GET[ "u" ] ) > 0 ? intval( $_GET[ "u" ]) : get_current_user_ID();
                    $content = preg_replace( '@\[vatroc_form_field @', "[vatroc_form_field_internal read_version=$read_only_version read_uid=$read_only_uid form=$form_name $str_autosave ", $content );
                    $form_data = VATROC_Form::get_submission( get_the_ID(), $read_only_uid, $read_only_version - 1 );
                    $timestamp = date( "Y/m/d H:i:s T", $form_data[ "timestamp" ]);
                    $read_only_uid_avatar = VATROC_My::html_my_avatar( $read_only_uid );
                    $ret_view_form = "";
                    if ( $form_data != null ) {
                        $ret_view_form .= "<div class='view-form'>";
                        $ret_view_form .= "<p>Submssion version: $read_only_version </p>";
                        $ret_view_form .= "<p>Submitted <b>$timestamp</b> by$read_only_uid_avatar</p>";
                        $ret_view_form .= do_shortcode( $content );
                        $ret_view_form .= "</div>";
                    }
                }

                $ret .= self::output_submission_list( $atts, $ret_view_form );

                wp_enqueue_style( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/form.css' );
                wp_enqueue_script( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/form.js', array( 'jquery' ), null, true );
                VATROC::enqueue_ajax_object( 'vatroc-form' );
                return $ret;
            }
            // } else {
            //     $content = preg_replace( '@\[vatroc_form_field@', "[vatroc_form_field_internal form=$form_name $str_autosave ", $content );
            //     $ret .= do_shortcode( $content );
            // }
        }

        $content = preg_replace( '@\[vatroc_form_field @', "[vatroc_form_field_internal form=$form_name $str_autosave ", $content );
        $content = preg_replace( '@\[/vatroc_form_field]@', "[/vatroc_form_field_internal]", $content );
        ob_start();
        ?>
        <div class="form-submit-message hidden">
            <h2><?php echo $atts[ "submit_message" ] ?: "The form has been submitted." ?></h2>
        </div>
        <form name=<?php echo $form_name; ?> class="vatroc-form">
            <i>This form is autosaved.</i>
            <?php echo do_shortcode( $content ); ?>
            <button><?php echo $atts[ "submit_label" ] ?: "Submit"; ?></button>
        </form>
        <?php
        $ret = ob_get_clean();
        wp_enqueue_style( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/form.css' );
        wp_enqueue_script( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/form.js', array( 'jquery' ), null, true );
        VATROC::enqueue_ajax_object( 'vatroc-form' );
        return $ret;
    }


    public static function output_submission_list( $atts = null, $content = null ) {
        $uid = isset( $_GET[ "u" ] ) && intval( $_GET[ "u" ]) > 0 ? intval( $_GET[ "u" ] ) : 0;
        $all_submissions = VATROC_Form::get_all_submissions( get_the_ID(), $uid );
        $keys = [];
        foreach( $all_submissions as $idx => $fields ) {
            foreach( array_keys( $fields ) as $dix => $key ) {
                $keys[ $key ] = true;
            }
        }
        unset( $keys[ "timestamp" ] );
        unset( $keys[ "uid" ] );

        return VATROC::get_template( "includes/shortcodes/templates/form/response-list.php", [
            "count" => count( $all_submissions ),
            "submissions" => $all_submissions,
            "field_names" => $keys,
            "view_all" => $uid == 0,
            "view_form" => $content,
            "version" => intval($_GET[ "v" ]) ?: -1
        ] );
    }


    public static function output_form_field( $atts ) {
        $is_admin = VATROC::is_admin();

        ob_start();
        if( VATROC::debug_section() ){
            echo "<div>";
            echo "Please enclose within `[vatroc_form][/vatroc_form]`.";
            echo "</div>";
        }
        return ob_get_clean();
    }

    public static function output_form_field_option( $atts ) {
        $is_admin = VATROC::is_admin();

        ob_start();
        if( VATROC::debug_section() ){
            echo "<div>";
            echo "Please enclose within `[vatroc_form_field_card][/vatroc_form_field_card]`.";
            echo "</div>";
        }
        return ob_get_clean();
    }            
                 
    public static function output_form_field_internal( $atts, $content = null ) {
        $page_id = get_the_ID();
        $uid = get_current_user_ID();
        $str_autosave = in_array( "autosave", $atts ) ? "autosave" : null;
        $is_read_only = isset( $atts[ "read_version" ] );
        $read_only_idx = 0;
        $read_only_uid = $uid;

        $form_data = VATROC_Form::get_draft( $page_id, $uid );
        if ( $is_read_only != null ) {
            $read_only_idx = intval( $atts[ "read_version" ] ) - 1;
            $read_only_uid = isset( $atts[ "read_uid" ]) &&  
                intval( $atts[ "read_uid" ] ) > 0 ? intval( $atts[ "read_uid" ] ) : $uid;
            $form_data = VATROC_Form::get_submission( $page_id, $read_only_uid, $read_only_idx );

        }

        $name = $atts[ "name" ];
        $content = preg_replace( '@\[vatroc_form_field_option @', "[vatroc_form_field_option_internal name=$name ", $content );

        ob_start();
        $input_classes = [
            "input-box"
        ];
        echo "<div class='" . implode( ' ', $input_classes ) . "'>";
            switch ( $atts[ "type" ] ) {
                case "text":
                    echo VATROC::get_template( "includes/shortcodes/templates/form/text.php", [
                        "label" => $atts[ "label" ],
                        "placeholder" => $atts[ "placeholder" ],
                        "autosave" => $str_autosave,
                        "read_only" => $is_read_only,
                        "form" => $atts[ "form" ],
                        "name" => $atts[ "name" ],
                        "value" => $form_data[  $atts[ "name" ] ],
                        "disabled" => $is_read_only ? "disabled" : null,
                    ] );
                    break;
                case "textarea":
                    echo VATROC::get_template( "includes/shortcodes/templates/form/textarea.php", [
                        "label" => $atts[ "label" ],
                        "placeholder" => $atts[ "placeholder" ],
                        "autosave" => $str_autosave,
                        "read_only" => $is_read_only,
                        "form" => $atts[ "form" ],
                        "name" => $atts[ "name" ],
                        "value" => $form_data[  $atts[ "name" ] ],
                        "disabled" => $is_read_only ? "disabled" : null,
                    ] );
                    break;
                case "number":
                    echo VATROC::get_template( "includes/shortcodes/templates/form/number.php", [
                        "label" => $atts[ "label" ],
                        "placeholder" => $atts[ "placeholder" ],
                        "autosave" => $str_autosave,
                        "read_only" => $is_read_only,
                        "form" => $atts[ "form" ],
                        "name" => $atts[ "name" ],
                        "value" => $form_data[  $atts[ "name" ] ],
                        "disabled" => $is_read_only ? "disabled" : null,
                    ] );
                    break;
                case "options":
                    echo VATROC::get_template( "includes/shortcodes/templates/form/options.php", [
                        "label" => $atts[ "label" ],
                        "placeholder" => $atts[ "placeholder" ],
                        "autosave" => $str_autosave,
                        "read_only" => $is_read_only,
                        "form" => $atts[ "form" ],
                        "name" => $atts[ "name" ],
                        "value" => $form_data[  $atts[ "name" ] ],
                        "disabled" => $is_read_only ? "disabled" : null,
                        "options" => $atts[ "options "],
                        "choices" => $atts[ "choices"],
                    ] );
                    echo do_shortcode( $content );
                    break;
                case "date":
                    echo VATROC::get_template( "includes/shortcodes/templates/form/date.php", [
                        "label" => $atts[ "label" ],
                        "placeholder" => $atts[ "placeholder" ],
                        "autosave" => $str_autosave,
                        "read_only" => $is_read_only,
                        "form" => $atts[ "form" ],
                        "name" => $atts[ "name" ],
                        "value" => $form_data[  $atts[ "name" ] ],
                        "disabled" => $is_read_only ? "disabled" : null,
                    ] );
                    break;
                case "toggle":
                    echo VATROC::get_template( "includes/shortcodes/templates/form/toggle.php", [
                        "label" => $atts[ "label" ],
                        "placeholder" => $atts[ "placeholder" ],
                        "autosave" => $str_autosave,
                        "read_only" => $is_read_only,
                        "form" => $atts[ "form" ],
                        "name" => $atts[ "name" ],
                        "value" => $form_data[  $atts[ "name" ] ] == "true",
                        "disabled" => $is_read_only ? "disabled" : null,
                    ] );
                    break;
                default: 
                    VATROC::dog( $atts );
                    break;
            }
        echo "</div>";

        return ob_get_clean();
    }

    public static function output_form_field_option_internal( $atts, $content = null ) {
        return VATROC::get_template( "includes/shortcodes/templates/form/option/option.php", [
            "label" => $atts[ "label" ],
            "name" => $atts[ "name" ],
            "value" => null,
        ] );
    }

};

VATROC_Shortcode_Form::init();