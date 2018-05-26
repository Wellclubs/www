<?php

class WTop
{
 const TABLE_CENTRE = 'com_top_centre';
 const WIDTH1 = 406;
 const HEIGHT1 = 260;
 const WIDTH2 = 289;
 const HEIGHT2 = 198;

 public static function downloadImage($row, $id)
 {
  if (!Base::isIndexNatural($row) || !Base::isIndexNatural($id))
   return false;
  return DB::getDB()->downloadFile(self::TABLE_CENTRE, 'image', array('id' => $id), 'filename', 'mimetype');
 }

 public static function uploadImage($row, $id)
 {
  $result = true;
  $tmp_name1 = $_FILES['image']['tmp_name'];
  $tmp_name2 = $tmp_name1 . 'x';
  $width = ($row == 1) ? self::WIDTH1 : self::WIDTH2;
  // Resize to IMAGE_HEIGHT
  if (XImage::resizeImageWidth($tmp_name1, $tmp_name2, $width))
  {
   $_FILES['image']['tmp_name'] = $tmp_name2;
   $_FILES['image']['size'] = filesize($tmp_name2);
   $fields = DB::uploadFields('image', '', '', '', '', '');
   $result &= DB::getAdminDB()->uploadFile('image', self::TABLE_CENTRE, $fields, array('id' => $id));
   unlink($tmp_name2);
  }
  return $result;
 }

 public static function clearImage($id)
 {
  return DB::getAdminDB()->modifyFields(self::TABLE_CENTRE,
    array('image' => 'null', 'filename' => 'null', 'mimetype' => 'null',
    'size' => 'null', 'width' => '0', 'height' => '0'), array('id' => $id));
 }

}

?>
