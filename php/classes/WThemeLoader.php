<?php

/**
 * Description of PageAdmThemesLoader
 */
class WThemeLoader
{
 const FILENAME = 'jquery-ui-1.9.2.custom';

 private static function tmp()
 {
  return Base::root() . '/tmp/';
 }
 
 private static function error($text)
 {
  Base::addError($text);
  return false;
 }
 
 public static function execute($name, $theme)
 {
  $tmpFileName = $_FILES[$name]['tmp_name'];
  if (!strlen($tmpFileName))
   return self::error('No uploaded filename set');
  if (!file_exists($tmpFileName))
   return self::error("File '$tmpFileName' not found");
  $zip = new ZipArchive;
  if (!isset($zip))
  {
   //copy($tmpFileName, class WTheme::path() . $_FILES[$name]['name']);
   move_uploaded_file($tmpFileName, WTheme::path());
   return self::error('ZipArchive is not supported');
  }
  $res = $zip->open($tmpFileName);
  if ($res !== true)
   return self::error("Error opening file '$tmpFileName'");
  $result = self::unzip($zip, $theme);
  Util::deleteFile(self::tmp() . self::FILENAME);
  $zip->close();
  return $result;
 }
 
 private static function unzip(&$zip, $themeName)
 {
  $cssFile = self::FILENAME . '.css';
  $cssPath = self::FILENAME . '/css/';

  /// http://www.php.net/manual/ru/ziparchive.locatename.php
  $stat = $zip->statName($cssFile, ZipArchive::FL_NODIR);
  if ($stat === false)
   return self::error('CSS file not found in the archive');

  $name = $stat['name'];
  if (!fnmatch($cssPath . '*/' . $cssFile, $name))
   return self::error('Invalid CSS file name found: ' . $name);

  $theme = substr($name, strlen($cssPath), strlen($name) - strlen($cssPath) - strlen($cssFile) - 1);
  if (!strlen($themeName))
   $themeName = $theme;
  $themePath = WTheme::path() . $themeName . '/';
  if (file_exists($themePath))
   return self::error((is_dir($themePath) ? 'Directory' : 'File') . " '$themePath' already exists");

  if (!mkdir($themePath))
   return self::error("Error creating the directory '$themePath'");
  
  $tmp = self::tmp();
  if ($zip->extractTo($tmp, $name) !== true)
   return self::error("CSS file extraction error");
  
  if (!rename($tmp . $name, $themePath . 'jquery-ui.css'))
   return self::error("CSS file moving error");
  
  return self::unzipImages($zip, $cssPath . $theme . '/images', $themePath . '/images');
 }

 private static function unzipImages(&$zip, $srcPath, $dstPath)
 {
  $tmp = self::tmp();

  $index = -1;
  while (true)
  {
   /// http://www.php.net/manual/ru/ziparchive.statindex.php
   $stat = $zip->statIndex(++$index);
   if ($stat === false)
    break;

   $name = $stat['name'];
   if (!fnmatch($srcPath . '/*', $name))
    continue;

   if ($zip->extractTo($tmp, $name) !== true)
    return self::error("Error extracting file '$name'");
  }
  
  $src = self::tmp() . $srcPath;
  if (is_dir($src))
   rename($src, $dstPath);

  return true;
 }
 
}

?>
