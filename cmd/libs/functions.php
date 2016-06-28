<?php

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2016 OA Wu Design
 */

if (!function_exists ('array_2d_to_1d')) {
  function array_2d_to_1d ($array) {
    $messages = array ();
    foreach ($array as $key => $value)
      if (is_array ($value)) $messages = array_merge ($messages, $value);
      else array_push ($messages, $value);
    return $messages;
  }
}
if (!function_exists ('color')) {
  function color ($string, $foreground_color = null, $background_color = null, $is_print = false) {
    if (!strlen ($string)) return "";
    $colored_string = "";
    $keys = array ('n' => '30', 'w' => '37', 'b' => '34', 'g' => '32', 'c' => '36', 'r' => '31', 'p' => '35', 'y' => '33');
    if ($foreground_color && in_array (strtolower ($foreground_color), array_map ('strtolower', array_keys ($keys)))) {
      $foreground_color = !in_array (ord ($foreground_color[0]), array_map ('ord', array_keys ($keys))) ? in_array (ord ($foreground_color[0]) | 0x20, array_map ('ord', array_keys ($keys))) ? '1;' . $keys[strtolower ($foreground_color[0])] : null : $keys[$foreground_color[0]];
      $colored_string .= $foreground_color ? "\033[" . $foreground_color . "m" : "";
    }
    $colored_string .= $background_color && in_array (strtolower ($background_color), array_map ('strtolower', array_keys ($keys))) ? "\033[" . ($keys[strtolower ($background_color[0])] + 10) . "m" : "";

    if (substr ($string, -1) == "\n") { $string = substr ($string, 0, -1); $has_new_line = true; } else { $has_new_line = false; }
    $colored_string .=  $string . "\033[0m";
    $colored_string = $colored_string . ($has_new_line ? "\n" : "");
    if ($is_print) printf ($colored_string);
    return $colored_string;
  }
}

if (!function_exists ('merge_array_recursive')) {
  function merge_array_recursive ($files, &$a, $k = null) {
    if ($files)
      foreach ($files as $key => $file)
        if (is_array ($file)) $key . merge_array_recursive ($file, $a, ($k ? $k . DIRECTORY_SEPARATOR : '') . $key);
        else array_push ($a, ($k ? $k . DIRECTORY_SEPARATOR : '') . $file);
  }
}

if (!function_exists ('directory_list')) {
  function directory_list ($source_dir, $hidden = false) {
    if ($fp = @opendir ($source_dir = rtrim ($source_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
      $filedata = array ();

      while (false !== ($file = readdir ($fp)))
        if (!(!trim ($file, '.') || (($hidden == false) && ($file[0] == '.'))))
          array_push($filedata, $file);

      closedir ($fp);
      return $filedata;
    }
    return array ();
  }
}

if (!function_exists ('params')) {
  function params ($params, $keys) {
    $ks = $return = $result = array ();

    if (!$params) return $return;
    if (!$keys) return $return;

    foreach ($keys as $key)
      if (is_array ($key)) foreach ($key as $k) array_push ($ks, $k);
      else  array_push ($ks, $key);

    $key = null;

    foreach ($params as $param)
      if (in_array ($param, $ks)) if (!isset ($result[$key = $param])) $result[$key] = array (); else ;
      else if (isset ($result[$key])) array_push ($result[$key], $param); else ;

    foreach ($keys as $key)
      if (is_array ($key))  foreach ($key as $k) if (isset ($result[$k])) $return[$key[0]] = isset ($return[$key[0]]) ? array_merge ($return[$key[0]], $result[$k]) : $result[$k]; else;
      else if (isset ($result[$key])) $return[$key] = isset ($return[$key]) ? array_merge ($return[$key], $result[$key]) : $result[$key]; else;

    return $return;
  }
}

if (!function_exists ('directory_map')) {
  function directory_map ($source_dir, $directory_depth = 0, $hidden = false) {
    if ($fp = @opendir ($source_dir)) {
      $filedata = array ();
      $new_depth  = $directory_depth - 1;
      $source_dir = rtrim ($source_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

      while (false !== ($file = readdir ($fp))) {
        if (!trim ($file, '.') || (($hidden == false) && ($file[0] == '.')))
          continue;

        if ((($directory_depth < 1) || ($new_depth > 0)) && @is_dir ($source_dir . $file))
          $filedata[$file] = directory_map ($source_dir . $file . DIRECTORY_SEPARATOR, $new_depth, $hidden);
        else
          array_push ($filedata, $file);
      }

      closedir ($fp);
      return $filedata;
    }

    return false;
  }
}

if (!function_exists ('write_file')) {
  function write_file ($path, $data, $mode = 'wb') {
    if (!$fp = @fopen ($path, $mode)) return false;

    flock($fp, LOCK_EX);
    fwrite($fp, $data);
    flock($fp, LOCK_UN);
    fclose($fp);

    return true;
  }
}
if (!function_exists ('column_array')) {
  function column_array ($objects, $key) {
    return array_map (function ($object) use ($key) {
      return !is_array ($object) ? is_object ($object) ? $object->$key : $object : $object[$key];
    }, $objects);
  }
}

if (!function_exists ('datetime')) {
  function datetime ($time) {
    return preg_replace_callback ('/(\d{4})\/(\d{1,2})\/(\d{1,2})\s+(\S+)\s+(\d{1,2}):(\d{1,2}):(\d{1,2})/', function ($matches) {
        return date ('Y-m-d H:i:s', strtotime ($matches[1] . '-' . sprintf ('%02d', $matches[2]) . '-' . sprintf ('%02d', $matches[3]) . ' ' . sprintf ('%02d', $matches[5] + ($matches[4] == '下午' ? 12 : ($matches[4] =='上午' && $matches[5] == 12 ? -12 : 0))) . ':' . sprintf ('%02d', $matches[6]) . ':' . sprintf ('%02d', $matches[7])));
      }, $time);
  }
}

if (!function_exists ('make_click_enable_link')) {
  function make_click_enable_link ($text, $maxLength = 0, $linkText = '', $attributes = 'target="_blank"') {
    return preg_replace_callback ('/(https?:\/\/)[~\S]+/u', function ($matches) use ($maxLength, $linkText, $attributes) {
      $text = $linkText ? $linkText : $matches[0];
      $text = $maxLength > 0 ? mb_strimwidth ($text, 0, $maxLength, '…','UTF-8') : $text;
      return '<a href="' . $matches[0] . '"' . ($attributes ? ' ' . $attributes : '') . '>' . $text . '</a>';
    }, $text);
  }
}
if (!function_exists ('remove_ckedit_tag')) {
  function remove_ckedit_tag ($text) {
    return preg_replace ("/\s+/u", ' ', trim (strip_tags ($text)));
  }
}
if (!function_exists ('make_click_enable_link')) {
  function make_click_enable_link ($text, $maxLength = 0, $linkText = '', $attributes = 'target="_blank"') {
    return preg_replace_callback ('/(https?:\/\/)[~\S]+/u', function ($matches) use ($maxLength, $linkText, $attributes) {
      $text = $linkText ? $linkText : $matches[0];
      $text = $maxLength > 0 ? mb_strimwidth ($text, 0, $maxLength, '…','UTF-8') : $text;
      return '<a href="' . $matches[0] . '"' . ($attributes ? ' ' . $attributes : '') . '>' . $text . '</a>';
    }, $text);
  }
}
if (!function_exists ('load_view')) {
  function load_view ($__o__p__ = '', $__o__d__ = array ()) {
    if (!$__o__p__) return '';

    extract ($__o__d__);
    ob_start ();
    if (((bool)@ini_get ('short_open_tag') === FALSE) && (false == TRUE)) echo eval ('?>'.preg_replace ("/;*\s*\?>/", "; ?>", str_replace ('<?=', '<?php echo ', file_get_contents ($__o__p__))));
    else include $__o__p__;
    $buffer = ob_get_contents ();
    @ob_end_clean ();

    return $buffer;
  }
}

if (!function_exists ('oa_meta')){
  function oa_meta ($attributes = array ()) {
    return $attributes ? '<meta ' . implode (' ', array_map (function ($attribute, $value) { return $attribute . '="' . $value . '"'; }, array_keys ($attributes), $attributes)) . ' />' : '';
  }
}