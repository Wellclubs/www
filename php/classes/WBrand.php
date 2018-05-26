<?php

/**
 * Description of WBrand
 */
class WBrand
{
 const TABLE_BRAND = 'com_brand';
 const TABLE_BRAND_IMG = 'com_brand_img';
 const TABLE_LEVEL = 'com_level';

 private static $id = null;
 private static $member_id = null;
 private static $name = null;
 private static $email = null;
 private static $uri = null;
 private static $file = null;

 private static $title = null;
 private static $count = null;
 private static $total = null;

 private static $descr = null;

 public static function id() { return self::$id; }
 public static function memberId() { return self::$member_id; }
 public static function name() { return self::$name; }
 public static function email() { return self::$email; }
 public static function uri() { return self::$uri; }
 public static function file() { return self::$file; }

 public static function title() { return self::$title; }
 public static function count() { return self::$count; }
 public static function total() { return self::$total; }

 public static function descr() { return self::$descr; }

 public static function initCurrent($id)
 {
  //echo "WBrand:initCurrent($id)";
  if ($id == null)
   return false;
  if ($id == self::$id)
   return true;

  self::$id = null;

  $fields = 'member_id,name,email,uri,file';
  $values = DB::getDB()->queryPairs(self::TABLE_BRAND, $fields, 'id=' . $id);
  if (!$values)
   return false;

  self::$id = $id;
  self::$member_id = $values['member_id'];
  self::$name = $values['name'];
  self::$email = $values['email'];
  self::$uri = Util::href($values['uri']);
  self::$file = $values['file'];

  self::$title = Lang::getDBValueDef(self::TABLE_BRAND . '_abc', 'title', 'brand_id=' . $id, self::$name);

  self::$descr = Lang::getDBValue(self::TABLE_BRAND . '_abc', 'descr', 'brand_id=' . $id);

  self::$count = DB::getDB()->queryField('com_centre', 'count(*)', "brand_id=$id and hidden is null");
  self::$total = DB::getDB()->queryField('com_centre', 'count(*)', "brand_id=$id");

  return true;
 }

 public static function getTitle($id, $real = false)
 {
  $name = DB::getDB()->queryField(self::TABLE_BRAND, 'name', 'id=' . $id);
  return self::getTitleForData($id, $name, $real);
 }

 public static function getTitleForData($id, $name, $real = false)
 {
  $result = Lang::getDBValueDef(self::TABLE_BRAND . '_abc', null, 'brand_id=' . $id, $name);
  if (!strlen($result) && !$real)
   $result = '... (' . Lang::getPageWord('text', 'no name') . ' ' . $id . ') ...';
  return $result;
 }

 public static function getLevels()
 {
  $result = array(array('id' => '', 'title' => self::getLevelTitle(null)));
  $rows = DB::getDB()->queryRecords(self::TABLE_LEVEL, 'id,name', 'brand_id=' . self::id(), 'serial');
  if ($rows)
   foreach ($rows as $row)
    $result[] = array('id' => $row[0], 'title' => self::getLevelTitle($row[0], $row[1]));
  return $result;
 }

 public static function getLevelTitle($id, $name = null)
 {
  if (!$id)
   return Lang::getSiteWord('title', 'Single price');
  if (is_null($name))
   $name = DB::getDB()->queryField(self::TABLE_LEVEL, 'name', 'id=' . $id);
  return Lang::getDBValueDef(self::TABLE_LEVEL . '_abc', null, 'level_id=' . $id, $name);
 }

 public static function logoURI($id = null)
 {
  if (!$id)
   $id = self::id();
  if ($id)
  {
   $logo = DB::getDB()->queryFields(WBrand::TABLE_BRAND, 'logo_width,logo_height', 'id=' . $id . ' and logo is not null');
   if ($logo)
    return 'img/bnd-' . $id . '.jpg';
  }
  return null;
 }

 public static function logoInfo($id = null)
 {
  if (!$id)
   $id = self::id();
  $defLogoInfo = null;
  // Brand logo
  if (!$logoInfo && $id)
  {
   $logo = DB::getDB()->queryFields(WBrand::TABLE_BRAND, 'logo_width,logo_height', 'id=' . $id . ' and logo is not null');
   if ($logo)
    return array('src' => 'img/bnd-' . $id . '.jpg', 'w' => $logo[0], 'h' => $logo[1]);
  }
  // Default logo
  if (!$logoInfo)
  {
   $src = 'pic/no-centre-' . rand(0, 9) . '.jpg';
   $file = Base::root() . Base::home() . $src;
   if (file_exists($file))
   {
    $size = getimagesize($file);
    if ($size && is_array($size) && (count($size) > 1))
     $logoInfo = array('src' => $src, 'w' => $size[0], 'h' => $size[1]);
   }
  }
  if (!$logoInfo)
   $logoInfo = array('src' => '', 'w' => 0, 'h' => 0);
  return $logoInfo;
 }

 public static function downloadLogo($id = null)
 {
  if (!$id)
  {
   self::initCurrent();
   $id = self::id();
  }
  if (!Base::isIndexNatural($id))
   return false;
  return DB::getDB()->downloadFile(self::TABLE_BRAND, 'logo', 'id=' . $id, 'logo_filename', 'logo_mimetype') ||
    Base::downloadFile(file_get_contents(Base::root() . Base::home() . 'pic/no-centre.jpg'), 'no-centre.jpg', 'image/jpeg');
 }

 public static function uploadLogo($id)
 {
  $table = self::TABLE_BRAND;
  $where = array('id' => $id);
  $tmp_name1 = $_FILES['image']['tmp_name'];
  $tmp_name2 = $tmp_name1 . 'x';
  $result = true;
  // Resize to 300
  if (XImage::resizeImageWidth($tmp_name1, $tmp_name2, WCentre::LOGO_WIDTH))
  {
   $_FILES['image']['tmp_name'] = $tmp_name2;
   $_FILES['image']['size'] = filesize($tmp_name2);
   $fields = DB::uploadFields('logo', 'logo_filename', 'logo_mimetype', 'logo_width', 'logo_height', 'logo_size');
   $result &= DB::getAdminDB()->uploadFile('image', $table, $fields, $where);
   unlink($tmp_name2);
  }
  return $result;
 }

 public static function uploadLogoFromDB($id, $uri)
 {
  return false;
 }

 public static function uploadLogoFromURI($id, $uri)
 {
  $filename = $uri;
  $pos = strpos($filename, '?');
  if ($pos !== false)
   $filename = substr ($filename, 0, $pos);
  $parts = explode('/', $filename);
  if (count($parts) > 1)
   $filename = $parts[count($parts) - 1];
  $tmp_name = tempnam(null, "img");
  $table = self::TABLE_BRAND;
  $where = array('id' => $id);
  $result = true;
  // Resize to 300
  $image1 = XImage::resizeImageWidth($uri, $tmp_name, WCentre::LOGO_WIDTH);
  if ($image1)
  {
   $size = filesize($tmp_name);
   unlink($tmp_name);
   $data = $image1->getData();
   $mimetype = $image1->mimetype();
   $values = array('logo' => DB::str($data), 'logo_filename' => DB::str($filename), 'logo_mimetype' => DB::str($mimetype),
     'logo_width' => $image1->width(), 'logo_height' => $image1->height(), 'logo_size' => $size);
   $result &= DB::getAdminDB()->mergeFields($table, $values, $where);
  }
  return $result;
 }

 public static function clearLogo($id)
 {
  return DB::getAdminDB()->modifyFields(self::TABLE_BRAND,
    array('logo' => 'null', 'logo_filename' => 'null', 'logo_mimetype' => 'null',
        'logo_size' => 'null', 'logo_width' => 'null', 'logo_height' => 'null'),
    array('id' => $id));
 }

 public static function createGallery($id = null)
 {
  if ($id == null)
   $id = self::id();
  return new WGallery(self::TABLE_BRAND_IMG, 'brand_id', 'bnd', $id);
 }

 public static function downloadImage($id, $serial)
 {
  if (!Base::isIndexNatural($id) || !Base::isIndexNatural($serial))
   return false;
  return self::createGallery($id)->downloadImage($serial);
 }
}

?>
