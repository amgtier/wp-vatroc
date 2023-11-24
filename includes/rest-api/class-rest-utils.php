<?php

if (!defined('ABSPATH')) {
  exit;
}

class VATROC_Rest_Utils
{
  public static function hydrate_user_info($arr)
  {
    foreach ($arr as $idx => $entry) {
      if (isset($entry["uid"])) {
        $_uid = intval($entry["uid"]);
        $arr[$idx]["id"] = $idx;
        $arr[$idx]["user_profile_src"] = get_avatar_url($_uid);
      }
      if (isset($entry["vatroc_position"])) {
        $arr[$idx]["vatroc_position_text"] = VATROC_Constants::$atc_position[$entry["vatroc_position"]];
      }
      if (isset($entry["vatroc_vatsim_rating"])) {
        $arr[$idx]["vatroc_vatsim_rating_text"] = VATROC_Constants::$vatsim_rating[$entry["vatroc_vatsim_rating"]];
      }
    }
    return $arr;
  }
}