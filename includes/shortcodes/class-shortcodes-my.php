<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_Shortcode_My {
    private static $meta_prefix = "vatroc_";


    public static function init() {
        add_shortcode( 'vatroc_my_vatroc', 'VATROC_Shortcode_My::output_My' );
        add_shortcode( 'vatroc_my_editable_nickname', 'VATROC_Shortcode_My::editable_nickname' );
        add_action( "wp_ajax_vatroc_my_set_nickname", "VATROC_Shortcode_My::ajax_set_nickname" );
    }


    public static function output_my() {
        $ret = "";
        $ret .= self::applicant();
        $ret .= self::atc();
        return $ret;
    }


    private static function atc() {
        $vote_post_id = 3897;
        $options = VATROC_Shortcode_Poll::get_options( $vote_post_id );
        $next_events = [];
        $html_next_events = "";
        foreach( $options as $date=> $result ){
            $desc = VATROC_Poll::get_description( $vote_post_id, $date );
            if ( $desc ){
                $next_events[ $date ] = $result;
                $html_next_events .= sprintf("<div class='flexbox-row flexbox-start'><div class='flexbox-column flexbox-nogap'>%1s %2s</div><div class='flexbox-column flexbox-nogap'>", $date, $desc);
                ob_start();
                VATROC::get_template( "includes/shortcodes/templates/poll/response-buttons.php", [ "result" => $result, "option" => $date, "page_id" => $vote_post_id ] );
                $html_next_events .= ob_get_clean();
                $html_next_events .= "</div></div>";
                $html_next_events .= get_the_ID();
            }
        }
        $html_next_events .= "";


        $ret = "";
        $ret .= "<h1>ATC section</h1>";
        $ret .= $html_next_events;
        return $ret;
    }


    private static function applicant() {
        $ret = "";
        $ret .= "<h1>Applicant section</h1>";
        return $ret;
    }


    public static function editable_nickname() {
        wp_enqueue_script( 'vatroc-my', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/my.js', array( 'jquery' ), null, true );

        ob_start();
?>
    <form id="edit-nickname" method="get" action="#">
        <label for="nickname">顯示名稱
            <input type="text" id="nickname" name="nickname" value="<?php echo VATROC_Shortcode_My::get_nickname(); ?>" required />
            <button id="submit-nickname" hidden>送出修改</button>
        </label>
    </form>
<?php
        $ret = ob_get_clean();
        return $ret;
    }


    public static function get_nickname(){
        return get_userdata( get_current_user_id() )->nickname;
    }


    public static function ajax_set_nickname() {
        $uid = get_current_user_id();
        update_user_meta( $uid, "nickname", $_POST[ "nickname"] );
        echo self::get_nickname();
        wp_die();
    }
};

VATROC_Shortcode_My::init();
