<?php

/**
 * Class HTTP incapsulates a set of methods for communicate with the client via HTTP
 */
class HTTP
{
 /**
  * Get the value of the HTTP 'GET' parameter
  * @param string $key Name of the parameter
  * @param string $def Default returning value
  * @param bool $useDefIsEmpty Use default value instead of ''
  * @return string Parameter value or null
  */
 public static function get($key, $def = null, $useDefIsEmpty = null)
 {
  $def = Util::nvl($def, '');
  $result = array_key_exists($key, $_GET) ? $_GET[$key] : $def;
  return (($result == '') && $useDefIsEmpty) ? $def : $result;
 }

 /**
  * Get the value of the HTTP 'POST' parameter
  * @param string $key Name of the parameter
  * @param string $def Default returning value
  * @param bool $useDefIsEmpty Use default value instead of ''
  * @return string Parameter value or empty string
  */
 public static function post($key, $def = null, $useDefIsEmpty = null)
 {
  $def = Util::nvl($def, '');
  $result = array_key_exists($key, $_POST) ? $_POST[$key] : $def;
  return (($result == '') && $useDefIsEmpty) ? $def : $result;
 }

 /**
  * Get the value of the HTTP 'GET' or 'POST' parameter
  * @param string $key Name of the parameter
  * @return string Parameter value or empty string
  */
 public static function prm($key)
 {
  return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : '';
 }

 /**
  * Get the value of the HTTP 'GET' or 'POST' parameter
  * @param string $key Name of the parameter
  * @param string $default Default value of the parameter
  * @return string Parameter value or empty string
  */
 public static function param($key, $default = null, $required = null)
 {
  if (array_key_exists($key, $_REQUEST))
  {
   $result = $_REQUEST[$key];
   if ($required && !strlen($result))
    exit("No parameter '$key' value set");
   return $result;
  }
  if (isset($default))
   return $default;
  exit("No parameter '$key' sent");
 }

 /**
  * Get the integer value of the HTTP 'GET' or 'POST' parameter
  * @param string $key Name of the parameter
  * @param integer $default Default value of the parameter
  * @return integer Parameter value or empty string
  */
 public static function paramInt($key, $default = null, $required = null)
 {
  return intval(self::param($key, $default, $required));
 }

 public static function addParam($uri, $param, $value)
 {
  return $uri . ((strpos($uri, '?') === false) ? '?' : '&') . $param . '=' . self::encodeURIComponent($value);
 }

 public static function hasCookie($name)
 {
  return array_key_exists($name, $_COOKIE);
 }

 public static function getCookie($name)
 {
  return Util::item($_COOKIE, $name);
 }

 public static function cookie($name)
 {
  return $_COOKIE[$name];
 }

 public static function setCookie($name, $value, $expire = null)
 {
  setcookie($name, $value, $expire, Base::home(), null, WDomain::ssl(), true);
 }

 public static function clearCookie($name)
 {
  setcookie($name, null, time() - 1000, Base::home(), null, WDomain::ssl(), true);
 }

 public static function uriWithoutParam($param)
 {
  $uri = Base::url();
  foreach ($_GET as $key => $value)
  {
   if ($key != $param)
    $uri = self::addParam($uri, $key, $value);
  }
  return $uri;
 }

 private static $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
 public static function encodeURIComponent($str)
 {
  return strtr(rawurlencode($str), self::$revert);
 }

 public static function tlimit($def = null, $min = null)
 {
  if (($min == null) || !is_numeric($min) || ($min < 1))
   $min = 10;
  if (($def == null) || !is_numeric($def) || ($def < 1))
   $def = 20;
  if ($def < $min)
   $def = $min;
  if (!array_key_exists('tlimit', $_GET))
   return $def;
  $tlimit = $_GET('tlimit');
  if (!is_numeric($tlimit) || ($tlimit < 1))
   return $def;
  $tlimit = intval($tlimit);
  if ($tlimit < $min)
   $tlimit = $min;
  return $tlimit;
 }

 public static function tstart()
 {
  if (!array_key_exists('tstart', $_GET))
   return null;
  $tstart = $_GET('tstart');
  if (!is_numeric($tstart) || ($tstart < 1))
   return null;
  return intval($tstart);
 }

 public static function file($url, $params = null, $post = false)
 {
  if ($post)
  {
   if (!($curl = @curl_init())) // Иницализация библиотеки curl
    exit('CURL module is not supported');
   @curl_setopt($curl, CURLOPT_URL, $url);
   @curl_setopt($curl, CURLOPT_POST, true);
   if ($params)
    @curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
   @curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   @curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
   @curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // Помогает выключение опций CURLOPT_SSL_VERIFYPEER и CURLOPT_SSL_VERIFYHOST
   @curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); // Максимальное время ожидания в секундах
   $result = @curl_exec($curl);
   @curl_close($curl);
  }
  else
  {
   if ($params)
    $url .= (strstr($url, '?') ? '&' : '?') . urldecode(http_build_query($params));
   $result = @file_get_contents($url);
  }
  return $result;
 }

 /**
  * Get HTTPS answer
  * @param type $uri URI to get
  * @return bool Success execution
  */
 public static function ssl($url, $params = null, $post = false)
 {
  if (!($curl = @curl_init())) // Initialize library curl
   exit('CURL module is not supported');
  @curl_setopt($curl, CURLOPT_URL, $url); // Specify the requesting URL
  if ($post)
   @curl_setopt($curl, CURLOPT_POST, true);
  if ($params)
   if ($post)
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
   else
    $url .= (strstr($url, '?') ? '&' : '?') . urldecode(http_build_query($params));
  @curl_setopt($curl, CURLOPT_HEADER, false); // With a value true CURL includes headers to output
  @curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return the request result from the function curl_exec
  @curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // http://zliypes.com.ua/blog/2008/01/25/php-win32-curl-https
  @curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // Can help switching off options CURLOPT_SSL_VERIFYPEER and CURLOPT_SSL_VERIFYHOST
  @curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); // Maximum time of waiting (in seconds)
  @curl_setopt($curl, CURLOPT_USERAGENT, 'PHP Bot ' . Base::pro() . Base::host()); // Specify the field User-agent
  $result = @curl_exec($curl); // Execute the request
  @curl_close($curl); // Free the resource
  if (!$result)
   exit('Error executing HTTPS request: ' . $url);
  return $result;
 }

 /**
  * Make an HTTP embedded data representation
  * @param binary $data Image raw data
  * @param string $mimetype Image MIME type
  * @return string Image data in base64 format
  */
 public static function embedData(&$data, $mimetype = null)
 {
  if ($data == null)
   return null;
  if (!$mimetype)
   $mimetype = 'image/gif';
  return 'data:' . $mimetype . ';base64,' . base64_encode($data);
 }

 /**
  * Make an img tag text with an embedded image data
  * @param binary $data Image raw data
  * @param string $mimetype Image MIME type
  * @param string $class Img tag 'class' attribute value
  * @param string $style Img tag 'style' attribute value
  * @param string $attrs Img tag other attributes (name=value pairs)
  * @return string Img tag with an embedded image data
  */
 public static function embedImage(&$data, $mimetype = null, $class = null, $style = null, $attrs = null)
 {
  $result = '<img';
  if ($data)
   $result .= ' src="' . self::embedData($data, $mimetype) . '"';
  if ($class)
   $result .= ' class="' . $class . '"';
  if ($style)
   $result .= ' style="' . $style . '"';
  $result .= $attrs . '/>';
  return $result;
 }

 /**
  * Make an img tag text with an embedded image data
  * @param string $filename Image file name
  * @param string $mimetype Image MIME type
  * @param string $class Img tag 'class' attribute value
  * @param string $style Img tag 'style' attribute value
  * @param string $attrs Img tag other attributes (name=value pairs)
  * @return string Img tag with an embedded image data
  */
 public static function embedImageFile($filename, $mimetype = null, $class = null, $style = null, $attrs = null)
 {
  $filename = Base::root() . Base::home() . $filename;
  if (!Base::justFileExists($filename))
   return '';
  $image = file_get_contents($filename);
  return self::embedImage($image, $mimetype, $class, $style, $attrs);
 }

 /**
  * Make an HTTP embedded file data representation
  * @param string $filename File name
  * @param string $mimetype File MIME type
  * @return string File data in base64 format
  */
 public static function embedFile($filename, $mimetype = null)
 {
  $filename = Base::root() . Base::home() . $filename;
  if (!Base::justFileExists($filename))
   return '';
  $image = file_get_contents($filename);
  return self::embedData($image, $mimetype);
 }

}

?>