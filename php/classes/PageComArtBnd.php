<?php

/**
 * Description of PageComArtBnd
 */
class PageComArtBnd extends PageComArt
{
 public function __construct($id)
 {
  parent::__construct($id);
  $this->useParIndex = true;
  $this->tabs = array
  (
   'def' => 'General',
   'ofrs' => 'Site Offers',
   'lvls' => 'Price levels',
   'descr' => 'Description',
   'imgs' => 'Images'
  );
 }

 public function testPrivs()
 {
  if (WBrand::memberId() == WClient::id())
   return true;
  $ctrs = WClient::me()->centres();
  foreach ($ctrs as $id => $ctr)
  {
   if ($ctr['bnd'] != WBrand::id())
    continue;
   if ($ctr['owner'])
    return true;
   if (array_key_exists(WPriv::PRIV_EDIT_CTR_DATA, $ctr['privs']))
    return true;
  }
  return false;
 }

 public function canBeEdited()
 {
  return (WBrand::memberId() == WClient::id());
 }

 public function init()
 {
  WBrand::initCurrent(Base::parIndex());
 }

 protected function getParTitle()
 {
  return Lang::getObjTitle('Brand') . ' "' . WBrand::getTitle(Base::parIndex()) . '"';
 }

 protected function putTabBodyDef()
 {
  $rows = array();
  $rows[] = array('field' => 'name', 'name' => 'Name', 'edit' => 1);
  $rows[] = array('field' => 'email', 'name' => 'E-mail', 'class' => 'optional', 'edit' => 1);
  $rows[] = array('field' => 'uri', 'name' => 'Website', 'class' => 'uri ext optional', 'edit' => 1);
  $rows[] = array('field' => 'ctr-count', 'name' => 'Number of centres', 'class' => 'right');
  $rows[] = array('field' => 'lvl-count', 'name' => 'Number of levels', 'class' => 'right');
  self::putForm($rows);
  self::putLogo();
  self::putSingleAction('delete', 'Permanently delete this brand', false, true);
 }

 protected function ajaxTabDef()
 {
  $data = array();
  $data['name'] = Lang::getDBTitles(WBrand::TABLE_BRAND, 'brand', WBrand::id());
  $data['email'] = '' . WBrand::email();
  $data['uri'] = '' . WBrand::uri();
  $data['ctr-count'] = '' . DB::getDB()->queryField(WCentre::TABLE_CENTRE, 'count(*)', 'brand_id=' . WBrand::id());
  $data['lvl-count'] = '' . DB::getDB()->queryField('com_level', 'count(*)', 'brand_id=' . WBrand::id());
  $data['is-owner'] = (WBrand::memberId() == WClient::id());
  PageCom::addToAjaxData('values', $data);

  PageCom::addToAjaxData('logo', $this->ajaxLogoFromDB());
 }

 protected function processActionTabDefModify()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  $field = HTTP::param('field');
  $value = HTTP::param('value');
  if (!strlen($field))
   return self::actionFail('No "field" parameter value set');
  WBrand::initCurrent(Base::parIndex());
  $db = PageCom::db();
  $titleChanged = false;
  if ($field == 'name')
  {
   if (!Lang::setDBTitles(WBrand::TABLE_BRAND, 'brand', WBrand::id()))
    return self::actionFailDBUpdate();
   $titles = Lang::getDBTitles(WBrand::TABLE_BRAND, 'brand', WBrand::id());
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
   $titleChanged = true;
  }
  else if (array_search($field, array('email', 'uri')) !== false)
  {
   if (!$db->modifyFields(WBrand::TABLE_BRAND, array($field => DB::str($value)), 'id=' . WBrand::id()))
    return self::actionFailDBUpdate();
   PageCom::addToAjax('value', $db->queryField(WBrand::TABLE_BRAND, $field, 'id=' . WBrand::id()), true);
  }
  else
   return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  if ($titleChanged)
  {
   Base::setTitle($this->getArtTitle(true));
   PageCom::addToAjax('title', Base::fullTitle());
   PageCom::addToAjax('subtitle', $this->getArtTitle(false));
  }
  return true;
 }

 private function ajaxLogo($data)
 {
  $logo = array();
  if ($data)
   $logo = array
   (
    'uri' => 'img/bnd-' . WBrand::id() . '.jpg'
   ,'file' => $data['logo_filename']
   ,'size' => $data['logo_size']
   ,'rect' => $data['logo_width'] . 'x' . $data['logo_height']
   );
  return $logo;
 }

 private function ajaxLogoFromDB()
 {
  $fields = 'logo_filename,logo_size,logo_width,logo_height';
  $data = DB::getDB()->queryPairs(WBrand::TABLE_BRAND, $fields, 'id=' . WBrand::id() . ' and logo is not null');
  return $this->ajaxLogo($data);
 }

 protected function processActionTabDefUpload()
 {
  if (!WBrand::uploadLogo(WBrand::id()))
   return self::actionFailDB('Error uploading a logo');
  PageCom::addToAjaxData('logo', $this->ajaxLogoFromDB());
  return true;
 }

 protected function processActionTabDefClear()
 {
  if (!WBrand::clearLogo(WBrand::id()))
   return self::actionFailDB('Error deleting a logo');
  return true;
 }

 protected function processActionTabDefDelete()
 {
  WBrand::initCurrent(Base::parIndex());
  if (!WBrand::id() || (WBrand::memberId() != WClient::id()))
   return self::actionErrorAccess();
  $where = 'id=' . WBrand::id();
  $db = PageCom::db();
  if (intval($db->queryField(WCentre::TABLE_CENTRE, 'count(*)', 'brand_' . $where)) > 0)
   return self::actionError('Brand has some centres linked and can\'t be deleted');
  if (intval($db->queryField('com_level', 'count(*)', 'brand_' . $where)) > 0)
   return self::actionError('Brand has some levels linked and can\'t be deleted');
  if (!$db->deleteRecords(WBrand::TABLE_BRAND, $where))
   return self::actionFailDBDelete();
  PageCom::addToAjax('uri', 'bnds/', true);
  return true;
 }

 protected function putTabBodyOfrs()
 {
  self::putBlocks('ofrs', 'offer', 'No site offers available');
 }

 protected function ajaxTabOfrs()
 {
  $ofrs = WOffer::getOffers(false, true);
  PageCom::addDataToAjax(array('ofrs' => $ofrs));
 }

 // public for access from PageComArtBrief
 public function putTabBodyLvls()
 {
  self::putSingleAction('create', 'Add a new level', true, true);
  //$cols = array(array('width' => 400, 'text' => 'Name', 'field' => 'name', 'class' => 'edit'));
  //$cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'delete', 'object' => 'Level');
  self::putBlocks('lvls', 'Level', 'There are currently no levels to view');
 }

 // public for access from PageComArtBrief
 public function ajaxTabLvls()
 {
  $data = array();
  $rows = DB::getDB()->queryRecords('com_level', 'id', 'brand_id=' . WBrand::id(), 'serial,id');
  if ($rows)
   foreach ($rows as $row)
    $data[] = array('id' => $row[0], 'name' => Lang::getDBTitles('com_level', 'level', $row[0]));
  // Data storing
  PageCom::addDataToAjax(array('lvls' => $data));
 }

 // public for access from PageComArtBrief
 public function processActionTabLvlsCreate()
 {
  $name = HTTP::param('name');
  if (!strlen($name))
   return self::actionFail('No "name" parameter value set');
  $db = PageCom::db();
  WBrand::initCurrent(Base::parIndex());
  if (!$db->insertValues('com_level', array('brand_id' => WBrand::id(), 'name' => DB::str($name))))
   return self::actionFailDB('Error inserting new record');
  $levelId = $db->insert_id;
  $db->modifySerialAfterInsert('com_level');
  if (!Lang::setDBTitlesOnly('com_level', array('level_id' => $levelId), true))
   return self::actionFailDBInsert();
  return true;
 }

 // public for access from PageComArtBrief
 public function processActionTabLvlsModify()
 {
  $rowid = HTTP::param('rowid');
  $field = HTTP::param('field');
  $value = HTTP::param('value');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (!strlen($field))
   return self::actionFail('No "field" parameter value set');
  if (($field == 'name') && !strlen($value))
   return self::actionFail('No "value" parameter value set');
  $db = PageCom::db();
  WBrand::initCurrent(Base::parIndex());
  if ($field == 'name')
  {
   if (!Lang::setDBTitles('com_level', 'level', $rowid))
    return self::actionFailDBUpdate();
   $titles = Lang::getDBTitles('com_level', 'level', $rowid);
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
   return true;
  }
  return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
 }

 // public for access from PageComArtBrief
 public function processActionTabLvlsDelete()
 {
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  $where = 'id=' . $rowid;
  $db = PageCom::db();
  WBrand::initCurrent(Base::parIndex());
  if (intval($db->queryField('com_master', 'count(*)', 'level_' . $where)) > 0)
   return self::actionError('Level has some masters linked and can\'t be deleted');
  if (intval($db->queryField(WService::TABLE_TIP, 'count(*)', 'level_' . $where)) > 0)
   return self::actionError('Level has some services linked and can\'t be deleted');
  if (!$db->deleteRecords('com_level', $where))
   return self::actionFailDBDelete();
  return true;
 }

 protected function putTabBodyDescr()
 {
  self::putTextArea();
 }

 protected function ajaxTabDescr()
 {
  $descr = array();
  foreach (Lang::map() as $id => $lang)
  {
   $text = DB::getDB()->queryField('com_brand_abc', 'descr', 'brand_id=' . WBrand::id() . " and abc_id='$id'");
   if ($text && strlen($text))
    $descr[$id] = base64_encode($text);
  }
  PageCom::addDataToAjax(array('text' => $descr));
 }

 protected function processActionTabDescrSave()
 {
  WBrand::initCurrent(Base::parIndex());
  $lang = HTTP::param('lang');
  $text = HTTP::param('text');
  $where = array('brand_id' => WBrand::id(), 'abc_id' => DB::str($lang));
  if (!PageAdm::db()->mergeField('com_brand_abc', 'descr', DB::str($text), $where))
   return self::actionFailDB('Error merging DB table');
  $descr = PageAdm::db()->queryField('com_brand_abc', 'descr', $where);
  PageCom::addToAjax('text', base64_encode($descr));
  return true;
 }

 protected function createGallery()
 {
  return WBrand::createGallery();
 }
}

?>
