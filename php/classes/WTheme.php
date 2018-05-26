<?php

/**
 * Description of WTheme
 */
class WTheme
{
 const TABLE_THEME = 'art_theme';

 const DEFAULT_THEME = 'base';

 public static function active($page = null)
 {
  if (!strlen($page))
   $page = Base::page();
  $where = 'active_' . $page . ' is not null and hidden is null';
  $result = DB::getDB()->queryField(self::TABLE_THEME, 'name', $where, 'name');
  return ($result == '') ? self::DEFAULT_THEME : $result;
 }
 
 public static function path()
 {
  return Base::root() . Base::home() . 'css/ui/';
 }

 public static function extractURI($name)
 {
  $uri = 'http://jqueryui.com/themeroller/';
  if ($name == WTheme::DEFAULT_THEME)
   return $uri;
  $filename = WTheme::path() . $name . '/jquery-ui.css';
  if (!file_exists($filename) || !is_file($filename))
   return null;
  $file = fopen($filename, 'rt');
  if ($file == null)
   return null;
  $contents = fread($file, filesize($filename));
  fclose($file);
  $start = strpos($contents, $uri);
  if ($start === false)
   return null;
  $stop = strpos($contents, "\n", $start);
  if ($stop === false)
   return null;
  return substr($contents, $start, $stop - $start);
 }

}

?>
