<?php

class Base
{
 const SITE_TITLE = 'Wellclubs';

 private static $msie; // Boolean

 private static $php; // Example: '/v4/index.php'
 private static $uri; // Example: '/v4/en/list.html?menu=5&a='
 private static $url; // Example: '/v4/en/list.html'
 private static $root; // Example: 'C:/xampp/htdocs'
 private static $ssl; // Example: true
 private static $pro; // Example: 'http://', 'https://'
 private static $host; // Example: 'localhost'
 private static $home; // Example: '/v4/'
 private static $path; // Example: 'en/list.html'
 private static $params; // Example: '?menu=5'
 private static $parts; // Example: array( 'en', 'list.html' )
 private static $partLangSet; // Example: true
 private static $partPageSet; // Example: true
 private static $partModeSet; // Example: true
 private static $partParSet; // Example: true
 private static $partTabSet; // Example: true
 private static $page; // Variants: 'book', 'com', 'adm'
 private static $mode; // Variants: 'home', 'list', 'ctr'
 private static $index; // Mode object index
 private static $par; // Variants: 'home', 'cdr', 'bnds', 'bnd'
 private static $parIndex; // Par object index
 private static $tab; // Variants: 'descr', 'imgs'
 private static $ajax; // Example: true
 private static $cmd;
 private static $title;// = self::SITE_TITLE; // Page title
 private static $filename;
 private static $topwMsg;
 private static $topwUri;
 private static $errors = array();

 private static $Page;

 public static function msie() { return self::$msie; }

 public static function uri() { return self::$uri; }
 public static function url() { return self::$url; }
 public static function root() { return self::$root; }
 public static function ssl() { return self::$ssl; }
 public static function pro() { return self::$pro; }
 public static function host() { return self::$host; }
 public static function home() { return self::$home; }
 public static function path() { return self::$path; }
 public static function loc() { return self::pro() . self::host() . self::uri(); }
 public static function bas() { return self::pro() . self::host() . self::home(); }
 public static function params() { return self::$params; }
 public static function parts() { return self::$parts; }
 public static function partPageSet() { return self::$partPageSet; }
 public static function partModeSet() { return self::$partModeSet; }
 public static function partParSet() { return self::$partParSet; }
 public static function partTabSet() { return self::$partTabSet; }
 //public static function lang() { return self::$lang; }
 public static function page() { return self::$page; }
 public static function mode() { return self::$mode; }
 public static function index() { return self::$index; }
 public static function par() { return self::$par; }
 public static function parIndex() { return self::$parIndex; }
 public static function tab() { return self::$tab; }
 public static function cmd() { return self::$cmd; }
 public static function ajax() { return self::$ajax; }

 public static function title() { return self::$title; }
 public static function setTitle($title) { self::$title = $title; }
 public static function fullTitle() { return self::SITE_TITLE . (self::$title ? (': ' . self::$title) : ''); }

 public static function getPage() { return self::$Page; }

 public static function topwMsg() { return self::$topwMsg; }
 public static function topwUri() { return self::$topwUri; }
 public static function setTopwMsg($text, $uri = null)
 {
  self::$topwMsg = $text;
  self::$topwUri = $uri;
 }

 public static function errors() { return self::$errors; }
 public static function addError($text)
 {
  self::$errors[] = $text;
  return false;
 }

 public static function langPath($lang = null)
 {
  if (!isset($lang))
   $lang = Lang::current();
  //return (($lang->id() != Lang::current()->id()) || self::$partLangSet) ? ($lang . '/') : '';
  return ($lang->id() != Lang::DEF()) ? ($lang . '/') : '';
 }

 public static function makeChangeLangURI($lang = null) // URI of the same resource on another language
 {
  return self::$home . self::langPath($lang) . self::$path . self::$params;
 }

 public static function pathPage()
 {
  return self::$partPageSet ? (self::$page . '/') : '';
 }

 public static function pathMode()
 {
  $result = '';
  if (self::$partModeSet)
  {
   $result .= self::$mode;
   if (is_numeric(self::$index))
    $result .= '-' . self::$index;
   $result .= '/';
  }
  return $result;
 }

 public static function pathPar()
 {
  $result = '';
  if (self::$partParSet)
  {
   $result .= self::$par;
   if (is_numeric(self::$parIndex))
    $result .= '-' . self::$parIndex;
   $result .= '/';
  }
  return $result;
 }

 public static function pathTab()
 {
  return self::$partTabSet ? (self::$tab . '/') : '';
 }

 public static function useRobots()
 {
  if (self::page() != 'book')
   return true;
  if (self::mode() == 'policy')
   return true;
  return false;
 }

 private static function parseURI()
 {
  self::$ssl = (Util::item($_SERVER, 'HTTPS') == 'on');
  self::$pro = self::$ssl ? 'https://' : 'http://';
  self::$php = urldecode(Util::item($_SERVER, 'SCRIPT_NAME')); // Example: /v4/index.php
  self::$uri = urldecode(Util::item($_SERVER, 'REQUEST_URI')); // Example: /v4/en/list.html?menu=5&a=
  $pos = strpos(self::$uri, '?');
  self::$url = ($pos === false) ? self::$uri : substr( self::$uri, 0, $pos); // Example: /v4/en/list.html
  self::$host = Util::item($_SERVER, 'HTTP_HOST'); // Example: 'localhost'
  self::$home = substr(self::$php, 0, strrpos(self::$php, '/') + 1); // Example: /v4/ (from '/v4/index.php')

  if (XSiteMap::processURL(self::$url))
   exit;

  if (substr(self::$url, 0, strlen(self::$home)) != self::$home)
   return false;//exit('Access denied');

  //if (WDomain::ssl() && !self::ssl())
  //{
  // header('Location: https://' . self::host() . self::uri());
  // exit;
  //}

  if (WClient::processHash())
   exit;
  if (WClient::processCmp())
   exit;

  self::$root = Util::item($_SERVER, 'DOCUMENT_ROOT'); // Example: C:/xampp/htdocs
  self::$path = substr(self::$url, strlen(self::$home)); // Example: en/list.html
  self::$parts = explode('/', self::$path);

  self::$params = ''; // Example: ?menu=5
  if (self::$uri != self::$url)
   foreach ($_GET as $key => $value)
    self::$params .= (strlen(self::$params) ? '&' : '?') . $key . '=' . $value;

  //exit(print_r(JSON::encode($_GET),true));
  self::$ajax = array_key_exists('a', $_GET);

  Lang::initialize();

  self::removeFolderLang(); // use and remove language folder
  self::removeFolderPage(); // use and remove page folder

  if (WDomain::ssl() && !self::ssl())
  {
   //if ((self::$page != 'book') || (WClient::logged()))
   //{
    header('Location: https://' . self::host() . self::uri());
    exit;
   //}
  }

  if (!self::$partLangSet && (self::$page == 'adm'))
   Lang::select(Lang::SYS);

  self::removeFolderMode(); // use and remove mode folder
  self::removeFolderPar(); // use and remove par folder
  self::removeFolderTab(); // use and remove tab folder

  return true;
 }

 public static function execute()

 {
  //echo 'Comment out this line for regular mode<pre>';var_dump($GLOBALS);exit;
  //echo 'Comment out this line for regular mode<pre>';var_dump($_SERVER);exit;
  //session_start();
  mb_internal_encoding("UTF-8");
  setlocale(LC_ALL, "ru_RU.UTF-8");
  self::$msie = strpos(Util::item($_SERVER, 'HTTP_USER_AGENT'), 'MSIE') !== false;
  if (self::parseURI() && self::executeParts())
  {
   if (self::justFileExists(self::$filename))
   {
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    require self::$filename;
   }
   return;
  }

  header( 'HTTP/1.0 404 Not Found');
  $GLOBALS['http_response_code'] = 404;
  echo 'Resource not found: "' . self::$url . '"';
 }

 private static function executeParts()
 {
  //print_r(self::$parts);exit;
  if (count(self::$parts) >= 2)
  {
   if (self::$parts[0] == 'img') // test for home/lang/page/mode/tab/img/* urls
    return self::executeImg();
   if (self::$parts[0] == 'pic') // test for home/lang/page/mode/tab/pic/* urls
    return self::executePic();
  }

  if (count(self::$parts) > 0)
   self::$cmd = self::$parts[0];

  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Expires: " . date("r"));
  return self::$Page->showPage();
 }

 /**
  * Test for existing file (not folder)
  * @param string $filename Filename to test
  * @return bool True if file (not folder) exists
  */
 public static function justFileExists($filename)
 {
  return file_exists($filename) && is_file($filename);
 }

 public static function executeTextFile($filename) // require existing file
 {
  $filename = self::$root . self::$home . $filename;
  if (self::justFileExists($filename))
  {
   self::$filename = $filename;
   return true;
  }
  return false;
 }

 private static function executeImg() // count(self::$parts) >= 2
 {
  if (count(self::$parts) == 2)
  {
   $filename = self::$parts[1];
   if (Lang::isFilenameImage($filename))
    return Lang::downloadImage($filename);
   if (fnmatch('menu-bg-?*.jpg', $filename))
    return self::downloadMenuBgImg($filename, substr($filename, 8, strlen($filename) - 12));
   if (fnmatch('menu-catid-?*.png', $filename))
    return self::downloadMenuCatImgById($filename, 0 + substr($filename, 11, strlen($filename) - 15));
   if (fnmatch('menu-cat-?*.png', $filename))
    return self::downloadMenuCatImgByName($filename, substr($filename, 9, strlen($filename) - 13));
   if (fnmatch('clt-?*.png', $filename))
    return WClient::downloadImage(substr($filename, 4, strlen($filename) - 8));
   if (fnmatch('clt-?*.jpg', $filename))
    return WClient::downloadImage(substr($filename, 4, strlen($filename) - 8), true);
   if (fnmatch('ctr-?*.jpg', $filename))
    return WCentre::downloadLogo(substr($filename, 4, strlen($filename) - 8));
   if (fnmatch('bnd-?*.jpg', $filename))
    return WBrand::downloadLogo(substr($filename, 4, strlen($filename) - 8));
  }
  else if (count(self::$parts) > 2)
  {
   $folder = self::$parts[1];
   $filename = self::$parts[2];
   if (fnmatch('top-*', $folder))
    return WTop::downloadImage(substr($folder, 4), $filename);
   if (fnmatch('ctr-*', $folder))
    return WCentre::downloadImage(substr($folder, 4), $filename);
   if (fnmatch('bnd-*', $folder))
    return WBrand::downloadImage(substr($folder, 4), $filename);
   if (fnmatch('srv-*', $folder))
    return WService::downloadImage(substr($folder, 4), $filename);
   if (fnmatch('clt-*', $folder))
    return null;
  }
  return false;
 }

 private static function executePic() // count(self::$parts) >= 2
 {
  $filename = self::$root . self::$home . 'pic';
  foreach (self::$parts as $index => $part)
   if ($index)
    $filename .= '/' . $part;
  if (file_exists($filename) && is_file($filename))
  {
   header('Content-Type: image/gif');
   readfile($filename); // http://php.net/manual/en/function.readfile.php
   return true;
  }
  return false;
 }

 /**
  * Remove language folder from URI (if exists)
  */
 private static function removeFolderLang() // use and remove language folder
 {
  if (count(self::$parts))
  {
   $lang = self::$parts[0];
   if (array_key_exists($lang, Lang::map()))
   {
    Lang::select($lang);
    self::$path = substr(self::$path, strlen($lang) + 1);
    array_splice(self::$parts, 0, 1);
    self::$partLangSet = true;
   }
  }
 }

 /**
  * Remove page folder from URI (if exists)
  */
 private static function removeFolderPage() // use and remove page folder
 {
  self::$Page = null;
  if (count(self::$parts))
  {
   $folder = self::$parts[0];
   switch ($folder)
   {
    case 'book' :
     self::$Page = new PageBook();
     break;
    case 'com' :
     self::$Page = new PageCom();
     break;
    case 'adm' :
     self::$Page = new PageAdm();
     break;
   }
   if (isset(self::$Page))
   {
     array_splice(self::$parts, 0, 1);
     self::$partPageSet = true;
     self::$page = $folder;
   }
  }
  if (!isset(self::$Page))
  {
   self::$Page = new PageBook();
   self::$page = 'book';
  }
 }

 /**
  * Remove mode folder from URI (if exists)
  */
 private static function removeFolderMode() // use and remove mode folder
 {
  self::$mode = self::$Page->getDefaultMode();
  if (count(self::$parts))
  {
   $folder = self::$parts[0];
   if (strlen($folder) == 0)
   {
    array_splice(self::$parts, 0, 1);
   }
   else if (self::$Page->validateMode($folder)) ///< Test for simple folder
   {
    self::$mode = $folder;
    array_splice(self::$parts, 0, 1);
    self::$partModeSet = true;
   }
   else
   {
    /// Test for indexed folder
    $pos = strpos($folder,'-');
    if (($pos !== false) && ($pos > 0) && ($pos < strlen($folder)))
    {
     $mode = substr($folder, 0, $pos);
     if (self::$Page->validateModeWithIndex($mode))
     {
      $index = substr($folder, $pos + 1);
      if (self::isIndexNatural($index))
      {
       self::$mode = $mode;
       self::$index = intval($index);
       array_splice(self::$parts, 0, 1);
       self::$partModeSet = true;
      }
     }
    }
   }
  }
 }

 /**
  * Remove par folder from URI (if exists)
  */
 private static function removeFolderPar() // use and remove par folder
 {
  self::$par = self::$Page->getDefaultPar();
  if (count(self::$parts))
  {
   $folder = self::$parts[0];
   if (strlen($folder) == 0)
   {
    array_splice(self::$parts, 0, 1);
   }
   else if (self::$Page->validatePar($folder)) ///< Test for simple folder
   {
    self::$par = $folder;
    self::$partParSet = true;
    array_splice(self::$parts, 0, 1);
   }
   else
   {
    /// Test for indexed folder
    $pos = strpos($folder,'-');
    if (($pos !== false) && ($pos > 0) && ($pos < strlen($folder)))
    {
     $par = substr($folder, 0, $pos);
     if (self::$Page->validateParWithIndex($par))
     {
      $index = substr($folder, $pos + 1);
      if (self::isIndexNatural($index))
      {
       self::$par = $par;
       self::$parIndex = intval($index);
       self::$partParSet = true;
       array_splice(self::$parts, 0, 1);
      }
     }
    }
   }
  }
 }

 /**
  * Remove tab folder from URI (if exists)
  */
 private static function removeFolderTab() // use and remove tab folder
 {
  self::$tab = self::$Page->getDefaultTab();
  if (count(self::$parts))
  {
   $folder = self::$parts[0];
   if ((strlen($folder) == 0) && self::$Page->validateTab(self::$tab))
   {
    array_splice(self::$parts, 0, 1);
   }
   else if (self::$Page->validateTab($folder))
   {
    self::$tab = $folder;
    array_splice(self::$parts, 0, 1);
    self::$partTabSet = true;
   }
  }
 }

 private static function downloadMenuBgImg($filename, $id) // 'img/menu-bg-N.jpg'
 {
  return DB::getDB()->downloadFile('biz_menu_bg', 'image', "id=$id", $filename, 'image/jpeg');
 }

 private static function downloadMenuCatImgById($filename, $id) // 'img/menu-cat-N.png'
 {
  return DB::getDB()->downloadFile('biz_menu_cat', 'image', "id=$id", $filename, 'image/png');
 }

 private static function downloadMenuCatImgByName($filename, $name) // 'img/menu-cat-N.png'
 {
  return DB::getDB()->downloadFile('biz_menu_cat', 'image', "name='$name'", $filename, 'image/png');
 }

 public static function isIndexNatural($index)
 {
  if (!is_numeric($index))
   return false;
  $index = 0 + $index;
  return is_integer($index) && ($index > 0);
 }

 public static function htmlComment($text, $lf = null)
 {
  return '<!-- ' . str_replace("-->", "-- >", $text) . ' -->' . ($lf ? "\n" : '');
 }

 public static function htmlConst()
 {
  return
    '<div style="padding:50px;text-align:center">' .
    '<a href="' . Base::home() . Base::langPath() . '">' .
    '<img width="282" height="350" src="' . Base::home() . 'pic/adm/const.jpg"/>' .
    '</a>' .
    '</div>' .
    "\n";
 }

 public static function downloadFile($content, $filename, $mimetype)
 {
/*
  if (isset($_REQUEST['If-Modified-Since']))
  {
   $GLOBALS['http_response_code'] = 304;
   header('HTTP/1.1 304 Not Modified');
   exit();
  }

  //header('Pragma:	cache');
  header("Cache-Control: public");
  //header('Cache-Control: max-age=3600');

  //header('Last-Modified: Mon, 26 Jul 1997 05:00:00 GMT');
  //header("Last-Modified: " . date('r', '1300000000'));
  //header("Last-Modified: " . gmdate( "D, d M Y H:i:s", time() - 86400) . " GMT");
  header("Last-Modified: " . gmdate( "D, d M Y H:i:s", '1300000000') . " GMT");

  //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  //header("Expires: " . date("r"));
  //header("Expires: " . date('r', '1420000000'));
  //header("Expires: " . gmdate('D, d M Y H:i:s', '1420000000') . " GMT");
  header("Expires: " . gmdate('D, d M Y H:i:s', time() + 86400) . " GMT");
*/
  header('Content-Type: ' . $mimetype);
  header('Content-Filename: ' . $filename);
  header('Content-Length: ' . strlen($content));
  echo $content;
  return true;
 }

 // http://www.xpro.su/archives/123
 // http://webo.in/articles/all/http-caching/
 // http://www.php.su/articles/?cat=protocols&page=012
 // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
}

?>
