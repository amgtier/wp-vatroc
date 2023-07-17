<?php

if (!defined('ABSPATH')) {
    exit;
}

class VATROC_My_SSO
{
    public static function init()
    {
    }

    public static function list($uid = null)
    {
        $uid = $uid ?: get_current_user_ID();
        $ret = "";
        ob_start();
?>
        <p>Discord <?php echo VATROC_SSO_Discord::render_status_with_avatar($uid) ?></p>
<?php
        $ret .= ob_get_clean();
        return $ret;
    }
}

VATROC_My_SSO::init();
