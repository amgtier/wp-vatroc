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
          $arr[$idx]["id"] = $idx;
          $arr[$idx]["user_profile_src"] = get_avatar_url($entry["uid"]);
        }
      }
      return $arr;
    }
}