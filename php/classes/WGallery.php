<?php

/**
 * Description of WGallery
 */
class WGallery
{
 const IMAGE_HEIGHT = 400;

 private $imgTable;
 private $keyField;
 private $uriPrefix;
 private $ownerId;
 
 public function __construct($table, $key, $prefix, $id)
 {
  $this->imgTable = $table;
  $this->keyField = $key;
  $this->uriPrefix = $prefix;
  $this->ownerId = $id;
 }

 private function whereArr($serial)
 {
  return array($this->keyField => $this->ownerId, 'serial' => $serial);
 }

 private function whereStr()
 {
  return $this->keyField . '=' . $this->ownerId;
 }
 
 private function makeImageURI($serial, $filename)
 {
  return 'img/' . $this->uriPrefix . '-' . $this->ownerId . '/' . $serial . '/' . $filename;
 }

 /**
  * Get an image information for mode=='book'
  * @param type $serial Image index
  * @param type $row DB record data
  * @return array: {href,width,height}
  */
 private function makeImageInfoBook($serial, $row)
 {
  return array
  (
    'href' => $this->makeImageURI($serial, $row['filename']),
    'width' => $row['width'],
    'height' => $row['height']
  );
 }

 /**
  * Get images information for mode=='book'
  * @return array: [{href,width,height}]
  */
 public function getImagesBook()
 {
  $result = array();
  $fields = 'serial,filename,width,height';
  $where = $this->whereStr() . ' and serial between 1 and 5 and hidden is null';
  $rows = DB::getDB()->queryMatrix($this->imgTable, $fields, $where, 'serial');
  if ($rows)
   foreach ($rows as $serial => $row)
    $result[] = $this->makeImageInfoBook($serial, $row);
  return $result;
 }

 /**
  * Get an image information for mode=='com'
  * @param type $serial Image index
  * @param type $row DB record data
  * @return array: {uri,file,size,rect,titles}
  */
 private function makeImageInfoCom($serial, $row)
 {
  $img = array();
  if ($row)
   $img = array
   (
    'uri' => $this->makeImageURI($serial, $row['filename'])
   ,'file' => $row['filename']
   ,'size' => $row['size']
   ,'rect' => $row['width'] . 'x' . $row['height']
   ,'titles' => Lang::getDBTitlesOnly($this->imgTable, $this->whereArr($serial))
   );
  return $img;
 }

 /**
  * Get images information for mode=='com'
  * @return array: [{uri,file,size,rect,titles}]
  */
 public function getImagesCom()
 {
  $imgs = array();
  $fields = 'serial,filename,size,width,height';
  $where = $this->whereStr() . ' and serial between 1 and 5';
  $rows = DB::getDB()->queryMatrix($this->imgTable, $fields, $where, 'serial');
  if ($rows)
   foreach ($rows as $rowid => $row)
    $imgs[$rowid] = $this->makeImageInfoCom($rowid, $row);
  PageCom::addToAjaxData('imgs', $imgs);
 }

 public function getImageCom($serial)
 {
  $fields = 'serial,filename,size,width,height';
  $row = DB::getDB()->queryPairs($this->imgTable, $fields, $this->whereArr($serial));
  return $this->makeImageInfoCom($serial, $row);
 }

 public function modifyImageCom()
 {
  $rowid = HTTP::param('rowid');
  $field = HTTP::param('field');
  if (!strlen($rowid))
   return PageComArt::actionFail('No "rowid" parameter value set');
  if (array_search($rowid, array('1', '2', '3', '4', '5')) === false)
   return PageComArt::actionFail('Invalid "rowid" parameter specified: "' . $rowid . '"');
  if (!strlen($field))
   return PageComArt::actionFail('No "field" parameter value set');
  if ($field == 'titles')
  {
   $where = $this->whereArr($rowid);
   if (!Lang::setDBTitlesOnly($this->imgTable, $where))
    return PageComArt::actionFailDBUpdate();
   $titles = Lang::getDBTitlesOnly($this->imgTable, $where);
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
  }
  else
   return PageComArt::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  return true;
 }

 public function deleteImageCom()
 {
  $rowid = HTTP::param('rowid');
  if (!$this->deleteImage($rowid))
   return PageComArt::actionFailDB('Error deleting an image');
  return true;
 }

 public function downloadImage($serial)
 {
  return DB::getDB()->downloadFile($this->imgTable, 'image', $this->whereArr($serial), 'filename', 'mimetype');
 }

 public function uploadImage($serial)
 {
  $tmp_name1 = $_FILES['image']['tmp_name'];
  $tmp_name2 = $tmp_name1 . 'x';
  $result = true;
  // Resize to IMAGE_HEIGHT
  if (XImage::resizeImageHeight($tmp_name1, $tmp_name2, self::IMAGE_HEIGHT))
  {
   $_FILES['image']['tmp_name'] = $tmp_name2;
   $_FILES['image']['size'] = filesize($tmp_name2);
   $fields = DB::uploadFields('image', '', '', '', '', '');
   $result &= DB::getAdminDB()->uploadFile('image', $this->imgTable, $fields, $this->whereArr($serial));
   unlink($tmp_name2);
  }
  return $result;
 }
 
 public function deleteImage($serial)
 {
  return DB::getAdminDB()->deleteRecords($this->imgTable, $this->whereArr($serial));
 }

}

?>
