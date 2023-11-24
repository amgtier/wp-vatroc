<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_My
{
    const PAGE_ID = 3889;

    public static function init()
    {
        self::enqueue_scripts();
        wp_enqueue_style('my', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/css/my.css');
    }


    private static function enqueue_scripts()
    {
        add_action('wp_enqueue_scripts', 'enqueue_scripts', 1000000001);
    }


    public static function html_my_avatar($uid)
    {
        ob_start();
?>
        <img title='<?php echo get_userdata($uid)->nickname; ?>' src='<?php echo get_avatar_url($uid); ?>' class='b-avatar' />
    <?php
        return ob_get_clean();
    }

    public static function html_my_avatar_with_position($uid, $is_avatar_clickable = false)
    {
        $str_pos = VATROC_My::get_pos_str($uid, "short");
        ob_start();
    ?>
        <?php if ($is_avatar_clickable) : ?>
            <a href="<?php echo get_edit_user_link($uid, null); ?>" target="_blank">
            <?php endif; ?>
            <div class='position-display-wrapper'>
                <div class='avatar-rating <?php echo $str_pos; ?>'>
                    <?php echo $str_pos; ?>
                </div>
                <?php echo VATROC_My::html_my_avatar($uid); ?>
            </div>
            <?php if ($is_avatar_clickable) : ?>
            </a>
        <?php endif; ?>
<?php
        return ob_get_clean();
    }

    public static function get_pos_str($uid, $type = "long")
    {
        $pos = get_user_meta($uid, "vatroc_position", true);
        $str_pos = '';
        if ($pos >= 10) {
            $str_pos = 'C';
        } else if ($pos >= 8) {
            $str_pos = 'A';
        } else if ($pos >= 6) {
            $str_pos = 'T';
        } else if ($pos >= 3) {
            $str_pos = 'G';
        } else if ($pos >= 16) {
            $str_pos = 'D';
        }
        return $str_pos;
    }


    public static function get_nickname()
    {
        return get_userdata(get_current_user_id())->nickname;
    }


    public static function set_nickname($uid, $value)
    {
        update_user_meta($uid, "nickname", $value);
        return self::get_nickname();
    }


    public static function get_vatsim_uid($uid = null)
    {
        $uid = $uid ?: get_current_user_id();
        return get_user_meta($uid, "vatroc_vatsim_uid", true) ?: 0;
    }


    public static function get_first_name($uid = null)
    {
        $uid = $uid ?: get_current_user_id();
        return get_user_meta($uid, "first_name", true) ?: 0;
    }


    public static function get_last_name($uid = null)
    {
        $uid = $uid ?: get_current_user_id();
        return get_user_meta($uid, "last_name", true) ?: 0;
    }

    public static function set_vatsim_uid($uid, $value)
    {
        update_user_meta($uid, "vatroc_vatsim_uid", $value);
        return self::get_vatsim_uid();
    }

    public static function set_vatsim_rating($uid, $value)
    {
        update_user_meta($uid, "vatroc_vatsim_rating", $value);
        return self::get_vatsim_rating();
    }

    public static function get_vatsim_rating($uid = null)
    {
        $uid = $uid ?: get_current_user_id();
        return get_user_meta($uid, "vatroc_vatsim_rating", true) ?: 0;
    }

    public static function get_vatsim_rating_string($uid = null)
    {
        return VATROC_Constants::$vatsim_rating[self::get_vatsim_rating($uid)];
    }

    public static function get_vatroc_position($uid = null)
    {
        $uid = $uid ?: get_current_user_id();
        return get_user_meta($uid, "vatroc_position", true) ?: 0;
    }

    public static function get_vatroc_position_string($uid = null)
    {
        return VATROC_Constants::$atc_position[self::get_vatroc_position($uid)];
    }


    public static function set_vatroc_position($uid, $new_position)
    {
        update_user_meta($uid, "vatroc_position", $new_position);
    }


    public static function set_first_name($uid, $value)
    {
        wp_update_user([
            'ID' => $uid,
            'first_name' => $value,
            'display_name' => $value . ' ' . (get_user_meta($uid, "last_name", true) ?: '')
        ]);
    }


    public static function set_last_name($uid, $value)
    {
        wp_update_user([
            'ID' => $uid,
            'last_name' => $value,
            'display_name' => (get_user_meta($uid, "last_name", true) ?: '') . ' ' . $value
        ]);
    }


    public static function field_value($field_name, $uid = null)
    {
        switch ($field_name) {
            case "nickname":
                return VATROC_My::get_nickname($uid);
            case "vatroc_position":
                return VATROC_My::get_vatroc_position($uid);
            case "vatsim_uid":
                return VATROC_My::get_vatsim_uid($uid);
            case "first_name":
                return VATROC_My::get_first_name($uid);
            case "last_name":
                return VATROC_My::get_last_name($uid);
        }
    }


    public static function set_field_value($uid, $field_name, $value)
    {
        switch ($field_name) {
            case "nickname":
                return VATROC_My::set_nickname($uid, $value);
            case "vatroc_position":
                return VATROC_My::set_vatroc_position($uid, $value);
            case "vatsim_uid":
                return VATROC_My::set_vatsim_uid($uid, $value);
            case "first_name":
                return VATROC_My::set_first_name($uid, $value);
            case "last_name":
                return VATROC_My::set_last_name($uid, $value);
        }
    }

    public static function set_del_ojt($uid, $vatsim_id)
    {
        $old_position = self::get_vatroc_position($uid);
        if ($old_position < 1) {
            $new_position = self::set_vatroc_position($uid, 1);
            VATROC::log(
                "$uid vatroc position changed from $old_position to $new_position",
            );
        } else {
            VATROC::dog("$uid vatroc position $old_position");
        }
        $old_vatsim_uid = self::get_vatsim_uid($uid);
        if ($old_vatsim_uid !== 0) {
            $new_vatsim_uid = self::set_vatsim_uid($uid, $vatsim_id);
            VATROC::log(
                "$uid vatsim uid changed from $old_vatsim_uid to $new_vatsim_uid",
            );
        } else {
            VATROC::dog("$uid vatsim uid $old_vatsim_uid");
        }
        $old_vatsim_rating = self::get_vatsim_rating($uid);
        if ($old_vatsim_rating < 2) {
            $new_vatsim_rating = self::set_vatsim_rating($uid, 2);
            VATROC::log(
                "$uid vatsim uid changed from $old_vatsim_rating to $new_vatsim_rating",
            );
        } else {
            VATROC::dog("$uid vatsim rating $old_vatsim_rating");
        }
    }

    public static function set_revert_del_ojt($uid, $vatsim_id)
    {
        $old_position = self::get_vatroc_position($uid);
        if ($old_position === 1) {
            $new_position = self::set_vatroc_position($uid, 0);
            VATROC::log(
                "$uid vatroc position changed from $old_position to $new_position",
            );
        } else {
            VATROC::dog("$uid vatroc position $old_position");
        }
        $old_vatsim_rating = self::get_vatsim_rating($uid);
        if ($old_vatsim_rating === 2) {
            $new_vatsim_rating = self::set_vatsim_rating($uid, null);
            VATROC::log(
                "$uid vatsim uid changed from $old_vatsim_rating to $new_vatsim_rating",
            );
        } else {
            VATROC::dog("$uid vatsim rating $old_vatsim_rating");
        }
    }
};

VATROC_My::init();
