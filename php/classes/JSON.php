<?php

/**
 * Description of JSON
 */
class JSON
{
 /**
  * Analog og json_encode
  * @param mixed $source
  * @param int $level Start level (null for compact mode)
  * @return string
  */
 public static function encode($source, $level = 0)
 {
  if (array_key_exists('a', $_GET))
   return json_encode($source);
  if (is_null($source))
   return 'null';
  if (is_string($source))
   return '"' . Util::strHTMLJS($source) . '"';
  if (is_double($source))
   return Util::dbl2str($source);
  if (is_bool($source))
   return $source ? 'true' : 'false';
  if (is_array($source))
   return self::encode_array($source, $level);
  if (is_object($source))
   return self::encode_object($source, $level);
  return '' . $source;
 }

 private static function encode_array($source, $level)
 {
  $array = array();
  $array_length = count($source);
  if (!$array_length)
   return "[]";
  $fine = is_int($level);
  for ($i = 0; $i < $array_length; $i++)
   if (array_key_exists($i, $source))
    $array[] = self::encode($source[$i], $fine ? ($level + 1) : $level);
   else
    return self::encode_object($source, $level);
  $result = null;
  if ($fine)
  {
   $prefix = self::prefix($level);
   $result = "[\n  " . $prefix . implode(",\n$prefix  ", $array) . "\n$prefix]";
  }
  else
   $result = '[' . implode(',', $array) . ']';
  return $result;
 }

 private static function encode_object($source, $level)
 {
  $array = array();
  $fine = is_int($level);
  $colon = $fine ? ' : ' : ':';
  foreach ($source as $key => $value)
   $array[] = '"' . $key . '"' . $colon . self::encode($value, $fine ? ($level + 1) : $level);
  if (!count($array))
   return "{}";
  $result = null;
  if ($fine)
  {
   $prefix = self::prefix($level);
   $result = "{\n  " . $prefix . implode(",\n$prefix  ", $array) . "\n$prefix}";
  }
  else
   $result = '{' . implode(',', $array) . '}';
  return $result;
 }

 private static function prefix($level)
 {
  return str_repeat('  ', $level);
 }
}

?>
