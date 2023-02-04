<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class VATROC_My {
    public static function init() {
        self::enqueue_scripts();
        wp_enqueue_style( 'my', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/css/my.css' );
    }


    function enqueue_scripts() {  
        add_action( 'wp_enqueue_scripts', 'enqueue_scripts', 1000000001 );
    }


    public static function html_my_avatar( $uid ){
        ob_start();
?>
    <img 
        title='<?php echo get_userdata( $uid )->nickname; ?>' 
        src='<?php echo get_avatar_url( $uid ); ?>'
        class='b-avatar'
    />
<?php
        return ob_get_clean();
    }

    public static function html_my_avatar_with_position( $uid, $is_avatar_clickable = false ){
        $str_pos = VATROC_My::get_pos_str( $uid, "short" );
        ob_start();
?>
        <?php if ( $is_avatar_clickable ): ?>
        <a href="<?php echo get_edit_user_link( $uid, null ); ?>" target="_blank">
        <?php endif; ?>
            <div class='position-display-wrapper'>
                <div class='avatar-rating <?php echo $str_pos;  ?>'>
                    <?php echo $str_pos; ?>
                </div>
                <?php echo VATROC_My::html_my_avatar( $uid ); ?>
            </div>
        <?php if ( $is_avatar_clickable ): ?>
        </a>
        <?php endif; ?>
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
