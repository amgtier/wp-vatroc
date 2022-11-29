<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_My {
    public static function init() {
    }


    public static function html_my_avatar( $uid ){
        ob_start();
?>
    <img 
        title='<?php echo get_userdata( $uid )->nickname; ?>' 
        src='<?php echo get_avatar_url( $uid ); ?>'
    />
<?php
        return ob_get_clean();
    }

    public static function get_pos_str( $uid, $type = "long" ){
        $pos = get_user_meta( $uid, "vatroc_position", true);
        $str_pos = '';
        if( $pos >= 10 ){
            $str_pos = 'C';
        } else if ( $pos >= 8 ){
            $str_pos = 'A';
        } else if ( $pos >= 6 ){
            $str_pos = 'T';
        } else if ( $pos >= 3 ){
            $str_pos = 'G';
        } else if ( $pos >= 16 ){
            $str_pos = 'D';
        }
        return $str_pos;
    }
};

VATROC_My::init();
