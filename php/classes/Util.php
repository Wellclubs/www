<?php

class Util
{
 public static function nvl($first, $second)
 {
  return ($first !== null) ? $first : $second;
 }

 public static function nvls($first, $second)
 {
  return $first ? $first : $second;
 }

 public static function str($value, $prefix = null, $suffix = null)
 {
  return strlen($value) ? ($prefix . $value . $suffix) : '';
 }

 public static function isAssoc($array)
 {
  return is_array($array) && (array_keys($array) !== range(0, count($array) - 1));
 }

 /**
  * Safe extract a value from an assoc_array
  * @param array $array Array to search in
  * @param string $name Key value to search
  * @return string Extracted value or null
  */
 public static function item($array, $name)
 {
  if (($array != null) && (strlen($name) > 0))
  {
   if (is_array($array))
    return array_key_exists($name, $array) ? $array[$name] : null;
   if (is_object($array))
    return property_exists($array, $name) ? $array->$name : null;
  }
  return null;
 }

 public static function parseUrlParams($params)
 {
  if (is_string($params))
  {
   $ps = explode('&', $params);
   $params = array();
   foreach ($ps as $p)
   {
    $v = explode('=', $p);
    if (count($v) == 1)
     $params[$v[0]] = '';
    elseif (count($v) == 2)
     $params[$v[0]] = $v[1];
   }
  }
  elseif (!is_array($params))
   $params = array();
  else if (!self::isAssoc($params))
  {
   $p = array();
   foreach ($params as $param)
    $p[$param] = '';
   $params = $p;
  }
  return $params;
 }

 public static function parseUrl($url)
 {
  $url = parse_url($url);
  $url['params'] = self::parseUrlParams(self::item($url, 'query'));
  return $url;
 }

 public static function buildUrl($url)
 {
  $result = '';
  $host = self::item($url, 'host');
  if ($host)
  {
   $scheme = self::item($url, 'scheme');
   $port = self::item($url, 'port');
   $result = (($scheme ? $scheme : 'http') . '://') . $host . ($port ? (':' . $port) : '');
  }
  $result .= self::item($url, 'path');
  if (count($url['params']))
  {
   $params = array();
   foreach ($url['params'] as $key => $value)
    $params[] = urlencode($key) . '=' . urlencode($value);
   $result .= '?' . implode('&', $params);
  }
  $fragment = self::item($url, 'fragment');
  if ($fragment)
   $result .= '#' . $fragment;
  return $result;
 }

 public static function addUrlParam($url, $param, $value)
 {
  if (!is_array($url))
   $url = self::parseUrl($url);
  $url['params'][$param] = $value;
  return $url;
 }

 public static function removeUrlParam($url, $param)
 {
  if (!is_array($url))
   $url = self::parseUrl($url);
  unset($url['params'][$param]);
  return $url;
 }

 public static function setUrlParam($url, $param, $value)
 {
  if (($value === null) || ($value === ''))
   return self::removeUrlParam($url, $param);
  return self::addUrlParam($url, $param, $value);
 }

 public static function repeatUrlParam($url, $param)
 {
  return self::setUrlParam($url, $param, HTTP::prm($param));
 }

 public static function addUrlParams($url, $params)
 {
  if (!is_array($url))
   $url = self::parseUrl($url);
  $params = self::parseUrlParams($params);
  foreach ($params as $key => $value)
   $url['params'][$key] = $value;
  return $url;
 }

 public static function removeUrlParams($url, $params)
 {
  if (!is_array($url))
   $url = self::parseUrl($url);
  $params = self::parseUrlParams($params);
  foreach ($params as $key => $value)
   unset($url['params'][$key]);
  return $url;
 }

 public static function removeParam(&$url, $param, $value)
 {// urlencode()
  $text = $param . '=' . $value;
  $len = strlen($text) + 1;
  $pos = strpos($url, '&' . $text);
  if ($pos !== false)
   $url = substr($url, 0, $pos) . substr($url, $pos + $len);
  else
  {
   $pos = strpos($url, '?' . $text);
   if ($pos !== false)
    $url = substr($url, 0, $pos) . substr($url, $pos + $len);
  }
 }

 public static function href($uri)
 {
  if (!strlen($uri))
   return '';
  if (substr($uri, 0, 7) == 'http://')
   return $uri;
  if (substr($uri, 0, 8) == 'https://')
   return $uri;
  return 'http://' . $uri;
 }

 /**
  * Convert string to JS-safe form
  * @param string $value Source string
  * @return string Converted string
  */
 public static function strJS($value)
 {
  $result = addslashes($value);
  return $result;
 }

 public static function strHTML($value)
 {
  $result = array($value);
  $result[0] = str_replace("<br>", "\n", $result[0]);
  $result[0] = str_replace("<br/>", "\n", $result[0]);
  $result[0] = str_replace("<br />", "\n", $result[0]);
  $result[0] = htmlspecialchars($result[0]);
  $result[0] = str_replace("\n", "<br/>", $result[0]);
  $result[0] = str_replace("\r", '', $result[0]);
  return $result[0];
 }

 public static function strHTMLJS($value)
 {
  $result = array($value);
  $result[0] = str_replace("<br>", "\n", $result[0]);
  $result[0] = str_replace("<br/>", "\n", $result[0]);
  $result[0] = str_replace("<br />", "\n", $result[0]);
  $result[0] = addslashes($result[0]);
  $result[0] = str_replace("\n", "<br/>\"+\n\"", $result[0]);
  $result[0] = str_replace("\r", '', $result[0]);
  return $result[0];
 }

 public static function pureNumber($number)
 {
  $result = null;
  for ($i = 0; $i < strlen($number); $i++)
  {
   $c = substr($number, $i, 1);
   if (strpos('0123456789', $c) !== false)
    $result .= $c;
  }
  return $result;
 }

 public static function intval($value)
 {
  return is_numeric($value) ? intval($value) : null;
 }

 public static function dbl2str($value)
 {
  return ($value === null) ? null : str_replace(',', '.', $value);
 }

 public static function date2str($value, $def = '')
 {
  return ($value instanceof DateTime) ? $value->format('d-m-Y') : $def;
 }

 public static function datetime2str($value, $def = '')
 {
  return ($value instanceof DateTime) ? $value->format('d-m-Y H:i:s') : $def;
 }

 public static function date2strDDMMMYYYY($value, $def = '')
 {
  $result = $def;
  if ($value instanceof DateTime)
  {
   $d = $value->format('d');
   $m = Lang::getPageWord('mon', $value->format('M'));
   $y = $value->format('Y');
   $result = "$d $m $y";
  }
  return $result;
 }

 public static function date2strDDMMMYY($value, $def = '')
 {
  $result = $def;
  if ($value instanceof DateTime)
  {
   $d = $value->format('d');
   $m = Lang::getPageWord('mon', $value->format('M'));
   $y = $value->format('y');
   $result = "$d $m $y";
  }
  return $result;
 }

 public static function date2strDDMMM($value, $def = '')
 {
  $result = $def;
  if ($value instanceof DateTime)
  {
   $d = $value->format('d');
   $m = Lang::getPageWord('mon', $value->format('M'));
   $result = "$d $m";
  }
  return $result;
 }

 public static function date2strWEEKDDMMM($value, $def = '')
 {
  $result = $def;
  if ($value instanceof DateTime)
  {
   $D = Lang::getPageWord('weekdaylong', $value->format('l'));
   $d = $value->format('d');
   $m = Lang::getPageWord('month', $value->format('F'));
   $result = "$D, $d $m";
  }
  return $result;
 }

 public static function str2date($value, $def = null)
 {
  if (($value == null) || !is_string($value) || (strlen($value) != 10))
   return $def;
  if (fnmatch('??-??-????', $value))
  {
   $date = new DateTime();
   $date->setDate(intval(substr($value, 6, 4)), intval(substr($value, 3, 2)), intval(substr($value, 0, 2)));
   return $date;
  }
  return $def;
 }

 public static function min2str($min)
 {
  //echo 'min2str(' . $min . ') ';
  if ($min === null)
   return null;
  $m = $min % 60;
  $h = ($min - $m) / 60;
  return substr(100 + $h, 1) . ':' . substr(100 + $m, 1);
 }

 public static function str2min($str)
 {
  //echo 'str2min(' . $str . ') ';
  if (!strlen($str))
   return null;
  $h = 0;
  $m = 0;
  $pos = strpos($str, ':');
  if ($pos === false)
   $h = is_numeric($str) ? intval($str) : -1;
  else
  {
   $m = substr($str, $pos + 1);
   $h = is_numeric($m) ? intval($m) : -1;
   if (($m < 0) && ($m > 59))
    return -1;
   $h = substr($str, 0, $pos);
   $h = is_numeric($h) ? intval($h) : -1;
  }
  if (($h < 0) || ($h > 23))
   return -1;
  return $h * 60 + $m;
 }

 public static function randomString64()
 {
  return rtrim(base64_encode(md5(microtime())), '=');
 }

 public static function randomString52()
 {
  return str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
 }

 public static function randomString($len)
 {
  return substr(self::randomString52(), mt_rand(0, 52 - $len), $len);
 }

 /**
  * Convert an array to its first item for using in << array_map(array('Util', 'mapArrayItem'), $rows) >>
  * @param assoc_array $row Array of kind << array(value) >>
  */
 public static function mapArrayItem(array $row)
 {
  return $row[0];
 }

 /**
  * Convert a value pair to a JSON pair for using in << implode(',', array_map(array('Util', 'mapJsonObject'), $array)) >>
  * @param assoc_array $pair Array of kind << array(key, value) >>
  */
 public static function mapJsonObject(array $pair)
 {
  return DB::str($pair[0]) . ':' . DB::str($pair[1]);
 }

 /**
  * Convert a value pair to a JSON pair for using in << implode(',', array_map(array('Util', 'mapJsonString'), $array)) >>
  * @param assoc_array $pair Array of kind << array(key, value) >>
  */
 public static function mapJsonString(array $pair)
 {
  return $pair[0] . ': ' . DB::str($pair[1]);
 }

 /**
  * Remove the file directory (with it's content)
  * @param string $filename Path to the file or directory
  * @return bool TRUE on success or FALSE on failure
  */
 public static function deleteFile($filename)
 {
  if (is_file($filename))
   return unlink ($filename);
  if (!is_dir($filename))
   return false;
  foreach (glob($filename . '/*') as $file)
   if (!self::deleteFile($file))
    return false;
  return rmdir($filename);
 }

 public static function rrmdir($dir)
 {
  foreach (glob($dir . '/*') as $file)
   if (!(is_dir($file) ? self::rrmdir($file) : unlink($file)))
    return false;
  return rmdir($dir);
 }

 public static function cutUniqueMarkers($text, $begin, $end, $keepContent)
 {
  if (!strlen($text))
   return ''; // Nothing to do
  $len1 = strlen($begin);
  $len2 = strlen($end);
  if ($len1 && $len2)
  { // Both markers are non-empty strings
   $pos1 = strpos($text, $begin);
   if (($pos1 !== null) && (strpos($text, $begin, $pos1 + $len1) === null))
   { // The begin marker exists and is unique
    $pos2 = strpos($text, $end);
    if (($pos2 !== null) && ($pos2 > $pos1) && (strpos($text, $end, $pos2 + $len2) === null))
    { // The end marker exists, is placed after the begin marker and is unique
     $result = '';
     if ($pos1)
      $result .= substr($text, 0, $pos1);
     if ($keepContent)
     {
      $pos1 += $len1;
      if ($pos2 > $pos1)
       $result += substr ($text, $pos1, $pos2 - $pos1);
     }
     $result .= substr($text, $pos2 + $len2);
     return $result;
    }
   }
  }
  // If any error happens we just simply cut all the markers
  return str_replace(array($begin, $end), array('', ''), $text);
 }

 public static function cutMarkers($text, $begin, $end, $keepContent)
 {
  if (!strlen($text))
   return ''; // Nothing to do
  if (!strlen($begin) || !strlen($end))
   return $text; // Nothing to do
  $parts = explode($begin, $text);
  for ($i = 1; $i < count($parts); ++$i)
  {
   $pies = explode($end, $parts[$i]);
   $parts[$i] = $keepContent ? implode('', $pies) : (count($pies) ? $pies[count($pies) - 1] : '');
  }
  return implode('', $parts);
 }

 public static function cutTag($text, $tag, $keepContent)
 {
  return self::cutMarkers($text, "<$tag>", "</$tag>", $keepContent);
 }

 public static function cutTags($text, $tags)
 {
  $result = $text;
  foreach ($tags as $tag => $keep)
   $result = self::cutTag($result, $tag, $keep);
  return $result;
 }

 // http://php.net/manual/de/function.com-create-guid.php
 public static function guid()
 {
  if (function_exists('com_create_guid') === true)
   return trim(com_create_guid(), '{}');
  return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
    mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535),
    mt_rand(16384, 20479), mt_rand(32768, 49151),
    mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
 }

 public static function guidv4()
 {
  if (function_exists('com_create_guid') === true)
   return trim(com_create_guid(), '{}');
  $data = openssl_random_pseudo_bytes(16);
  $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
  $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
 }
}

?>