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
      <input type="text" id="nickname" name="nickname" value="<?php echo VATROC_Shortcode_My::get_nickname(); ?>" />
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
