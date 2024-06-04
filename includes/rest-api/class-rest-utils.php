<?php

if (!defined('ABSPATH')) {
  exit;
}

class VATROC_Rest_Utils
{
  public static function hydrate_user_info($arr, $map = [])
  {
    foreach ($arr as $idx => $entry) {
      // TODO: merge $entry["uid"], what would happend when intval(null or not set)?
      if (isset($entry[$map["uid"] ?: "uid"])) {
        $_uid = intval($entry[$map["uid"] ?: "uid"]);
        $arr[$idx]["id"] = $idx;
        $arr[$idx]["user_profile_src"] = get_avatar_url($_uid);

        // TODO: Can I select field?
        $user = get_userdata($_uid);
        $arr[$idx]["display_name"] = $user->nickname;
      }
      if (isset($entry[$map["vatroc_position"] ?: "vatroc_position"])) {
        $arr[$idx]["vatroc_position_text"] = VATROC_Constants::$atc_position[$entry["vatroc_position"]];
      }
      if (isset($entry[$map["vatroc_vatsim_rating"] ?: "vatroc_vatsim_rating"])) {
        $arr[$idx]["vatroc_vatsim_rating_text"] = VATROC_Constants::$vatsim_rating[$entry["vatroc_vatsim_rating"]];
      }
    }
    return $arr;
  }

  public static function hydrate_comment_counts($arr, $map = [], $post_id)
  {
    foreach ($arr as $idx => $entry) {
      $arr[$idx]['comment_count'] = VATROC_Form::get_comment_count($post_id, $entry['uuid']);
    }
    return $arr;
  }
}
