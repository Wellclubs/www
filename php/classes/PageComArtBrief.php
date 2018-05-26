<?php

/**
 * Description of PageComArtBrief
 */
class PageComArtBrief extends PageComArt
{
 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->title = 'Details';
  $this->tabs = array
  (
   'def' => 'General',
   'ofrs' => 'Site Offers',
   'hours' => 'Opening hours',
   'lvls' => 'Price levels',
   'descr' => 'Description',
   'imgs' => 'Images'
  );
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be shown to the current client
  */
 public function testPrivs()
 {
  return PageCom::testPriv();
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be edited by the current client
  */
 public function canBeEdited()
 {
  return PageCom::testPriv(WPriv::PRIV_EDIT_CTR_DATA);
 }

 protected function putTabBodyDef()
 {
  $rows = array();
  $rows[] = array('field' => 'name', 'name' => 'Name', 'edit' => 1);
  $rows[] = array('field' => 'email', 'name' => 'E-mail', 'class' => 'optional', 'edit' => 1);
  $rows[] = array('field' => 'address', 'name' => 'Address', 'edit' => 1);
  $rows[] = array('field' => 'geo', 'name' => 'Location');
  $rows[] = array('field' => 'bnd', 'name' => 'Brand', 'class' => 'uri ax select bnd', 'edit' => 1);
  $rows[] = array('field' => 'owner', 'name' => 'Owner', 'class' => 'uri ax');
  $rows[] = array('field' => 'currency_id', 'name' => 'Currency', 'class' => 'curr', 'edit' => 1);
  $rows[] = array('field' => 'capacity', 'name' => 'Capacity', 'class' => 'right', 'edit' => 1);
  $rows[] = array('field' => 'srv-count', 'name' => 'Services', 'class' => 'right');
  $rows[] = array('field' => 'mtr-count', 'name' => 'Staff', 'class' => 'right');
  echo '<div class="block general">';
  self::putForm($rows, null, false);
  echo '</div>';

  echo '<div class="block action edit">';

  echo '<div class="title">';
  echo htmlspecialchars(Lang::getPageWord('title', 'Phone numbers'));
  echo '</div>' . "\n";

  echo '<div class="button left" action="addtel">';
  echo htmlspecialchars(Lang::getPageWord('button', 'Add a phone number'));
  echo '</div>' . "\n";

  echo '<div class="wrapper">';
  $cols = array(array('width' => 400, 'text' => 'Phone number', 'field' => 'phone', 'class' => 'edit'));
  $cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'deltel', 'object' => 'Phone number');
  self::putTable($cols, "Example", false, true, ": +1 (234) 567-89-00");
  echo '</div>' . "\n";

  echo '</div>' . "\n";

  self::putLogo();

  self::putSingleAction('delctr', 'Permanently delete this centre', false, true);
 }

 protected function ajaxTabDef()
 {
  $data = array();
  $data['name'] = Lang::getDBTitles(WCentre::TABLE_CENTRE, 'centre', WCentre::id());
  $data['email'] = '' . WCentre::email();
  $data['address'] = '' . WCentre::address();
  $data['geo'] = '' . WCentre::lat() . ' / ' . WCentre::lng();
  $uri = (WBrand::memberId() == WClient::id()) ? 'bnd-' . WBrand::id() . '/' : '';
  $data['bnd'] = array('id' => '' . WBrand::id(), 'text' => '' . WBrand::title(), 'uri' => $uri);
  $data['owner'] = array('text' => '' . WCentre::ownerName(), 'uri' => 'ctr-' . WCentre::id() . '/clt-' . WCentre::memberId() . '/');
  $data['currency_id'] = '' . WCurrency::makeLabel(WCentre::currencyId());
  $data['capacity'] = '' . WCentre::capacity();
  $data['srv-count'] = '' . DB::getDB()->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id=' . WCentre::id());
  $data['mtr-count'] = '' . DB::getDB()->queryField('com_master', 'count(*)', 'centre_id=' . WCentre::id());
  $data['is-owner'] = (WCentre::memberId() == WClient::id());
  PageCom::addToAjaxData('values', $data);

  $tels = DB::getDB()->queryArrays('com_centre_phone', 'serial id,phone', 'centre_id=' . WCentre::id(), 'serial');
  if ($tels)
   PageCom::addToAjaxData('tels', $tels);

  PageCom::addToAjaxData('logo', $this->ajaxLogoFromDB());
 }

 protected function processAcQueryTabDefCurr($term)
 {
  PageCom::addDataToAjax(WCurrency::acQuery($term));
  return true;
 }

 protected function processActionTabDefBnds()
 {
  $brands = array(array('id' => 0, 'title' => ''));
  foreach (WClient::me()->brands(false) as $key => $value)
   $brands[] = array('id' => $key, 'title' => $value['name']);
  PageCom::addDataToAjax($brands);
  return true;
 }

 /**
  * Modify the value of some field
  * @return bool True if success
  */
 protected function processActionTabDefModify()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  $field = HTTP::param('field');
  $value = HTTP::param('value');
  if (!strlen($field))
   return self::actionFail('No "field" parameter value set');
  $db = PageCom::db();
  $titleChanged = false;
  if ($field == 'name')
  {
   if (!Lang::setDBTitles(WCentre::TABLE_CENTRE, 'centre', WCentre::id()))
    return self::actionFailDBUpdate();
   $titles = Lang::getDBTitles(WCentre::TABLE_CENTRE, 'centre', WCentre::id());
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
   $titleChanged = true;
  }
  else if (array_search($field, array('email', 'address', 'currency_id', 'capacity')) !== false)
  {
   if (($field == 'currency_id') && strlen($value))
   {
    $id = strtoupper(substr(trim($value), 0, 3));
    if (!WCurrency::exists($id))
     return self::actionFail('Invalid currency code: "' . $value . '"');
   }
   if (!$db->modifyFields(WCentre::TABLE_CENTRE, array($field => DB::str($value)), 'id=' . WCentre::id()))
    return self::actionFailDB('Error updating DB table');
   if ($field == 'address')
   {
    WCentre::queryLocation();
    $rec = $db->queryFields(WCentre::TABLE_CENTRE, 'lat,lng', 'id=' . WCentre::id());
    if ($rec)
     PageCom::addToAjax('values', array('geo' => $rec[0] . ' / ' . $rec[1]), true);
   }
   $result = $db->queryField(WCentre::TABLE_CENTRE, $field, 'id=' . WCentre::id());
   if ($field == 'currency_id')
    $result = WCurrency::makeLabel($result);
   PageCom::addToAjax('value', $result, true);
  }
  else if ($field == 'bnd')
  {
   $oldvalue = $db->queryField(WCentre::TABLE_CENTRE, 'brand_id', 'id=' . WCentre::id());
   if ($value != $oldvalue)
   {
    if ($oldvalue)
    {
     PageAdm::db()->modifyFields('com_master', array('level_id' => 'null'), 'centre_id=' . WCentre::id());
     PageAdm::db()->modifyFields(WService::TABLE_TIP, array('level_id' => 'null'), 'centre_id=' . WCentre::id());
    }
    if (!$db->modifyFields(WCentre::TABLE_CENTRE, array('brand_id' => ($value ? $value : 'null')), 'id=' . WCentre::id()))
     return self::actionFailDB('Error updating DB table');
    $value = $db->queryField(WCentre::TABLE_CENTRE, 'brand_id', 'id=' . WCentre::id());
   }
   $uri = $value ? ('bnd-' . $value . '/') : '';
   $text = $value ? ('' . WBrand::getTitle($value, true)) : '';
   PageCom::addToAjax('value', array('id' => $value, 'uri' => $uri, 'text' => $text), true);
  }
  else if ($field == 'phone')
  {
   $rowid = HTTP::param('rowid');
   if (!strlen($rowid))
    return self::actionFail('No "rowid" parameter value set');
   $number = Util::pureNumber($value);
   $centreId = WCentre::id();
   $table = WCentre::TABLE_CENTRE_PHONE;
   $fields = "(select count(*) from $table where centre_id=$centreId and serial=$rowid)," .
     "(select count(*) from $table where centre_id=$centreId and number='$number' and serial<>$rowid)";
   $values = $db->queryFields(null, $fields);
   if (!$values[0])
    self::actionError('Changing phone number is not found');
   else if ($values[1])
    self::actionError('New phone number is already entered');
   else if (!$db->modifyFields($table, array('phone' => DB::str($value), 'number' => DB::str($number)), "centre_id=$centreId and serial=$rowid"))
    return self::actionFailDBUpdate();
   PageCom::addToAjax('value', $db->queryField($table, $field, "centre_id=$centreId and serial=$rowid"), true);
  }
  else
   return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  if ($titleChanged)
  {
   Base::setTitle($this->getArtTitle(true));
   PageCom::addToAjax('title', Base::fullTitle());
   PageCom::addToAjax('pretitle', WCentre::getTitle());
   PageCom::addToAjax('subtitle', $this->getArtTitle(false));
  }
  return true;
 }

 /**
  * Add a phone number
  * @return bool True if success
  */
 protected function processActionTabDefAddtel()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $phone = HTTP::param('phone');
  if (!strlen($phone))
   return self::actionFail('No "name" parameter value set');
  $number = Util::pureNumber($phone);
  if (!strlen($number))
   return self::actionError('Phone number does not contain any digit');
  $db = PageCom::db();
  $centreId = WCentre::id();
  $table = WCentre::TABLE_CENTRE_PHONE;
  $fields = "(select count(*) from $table where centre_id=$centreId and number='$number')," .
    "(select ifnull(max(serial),0)+1 from $table where centre_id=$centreId)";
  $values = $db->queryFields(null, $fields);
  if ($values[0])
   return self::actionError('Appending phone number is already appended');
  else if (!$db->insertValues('com_centre_phone', array('centre_id' => $centreId, 'serial' => $values[1], 'phone' => DB::str($phone), 'number' => DB::str($number))))
   return self::actionFailDBInsert();
  return true;
 }

 /**
  * Delete a single phone number
  * @return bool True if success
  */
 protected function processActionTabDefDeltel()
 {
  if (!WCentre::id() || (WCentre::memberId() != WClient::id()))
   return self::actionErrorAccess();
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  $where = 'centre_id=' . WCentre::id() . ' and serial=' . $rowid;
  if (!PageAdm::db()->deleteRecords(WCentre::TABLE_CENTRE_PHONE, $where))
   return self::actionFailDBDelete();
  return true;
 }

 private function ajaxLogo($data)
 {
  $logo = array();
  if ($data)
   $logo = array
   (
    'uri' => 'img/ctr-' . WCentre::id() . '.jpg'
   ,'file' => $data['logo_filename']
   ,'size' => $data['logo_size']
   ,'rect' => $data['logo_width'] . 'x' . $data['logo_height']
   );
  return $logo;
 }

 private function ajaxLogoFromDB()
 {
  $fields = 'logo_filename,logo_size,logo_width,logo_height';
  $data = DB::getDB()->queryPairs(WCentre::TABLE_CENTRE, $fields, 'id=' . WCentre::id() . ' and logo is not null');
  return $this->ajaxLogo($data);
 }

 protected function processActionTabDefUpload()
 {
  if (!WCentre::uploadLogo(WCentre::id()))
   return self::actionFailDB('Error uploading a logo');
  PageCom::addToAjaxData('logo', $this->ajaxLogoFromDB());
  return true;
 }

 protected function processActionTabDefClear()
 {
  if (!WCentre::clearLogo(WCentre::id()))
   return self::actionFailDB('Error deleting a logo');
  return true;
 }

 /**
  * Delete the whole centre
  * @return bool True if success
  */
 protected function processActionTabDefDelete()
 {
  if (!WCentre::id() || (WCentre::memberId() != WClient::id()))
   return self::actionErrorAccess();
  $where = 'id=' . WCentre::id();
  $db = PageCom::db();
  if (intval($db->queryField('com_centre', 'member_id', $where)) != WClient::id())
   return self::actionError('You can\t delete this centre because you\'re not the owner');
  if (intval($db->queryField(WService::TABLE_SRV, 'count(*)', 'centre_' . $where)) > 0)
   return self::actionError('Centre has some services linked and can\'t be deleted');
  if (intval($db->queryField('com_master', 'count(*)', 'centre_' . $where)) > 0)
   return self::actionError('Centre has some masters linked and can\'t be deleted');
  if (!$db->deleteRecords(WCentre::TABLE_CENTRE, $where))
   return self::actionFailDB("Error deleting the centre record from the database");
  PageCom::addToAjax('uri', 'ctrs/', true);
  return true;
 }

 protected function putTabBodyOfrs()
 {
  self::putBlocks('ofrs', 'offer', 'No site offers available');
 }

 protected function ajaxTabOfrs()
 {
  $ofrs = WOffer::getOffers(true, false);
  PageCom::addDataToAjax(array('ofrs' => $ofrs));
 }

 protected function putTabBodyHours()
 {
  $cols = array(array('width' => 200, 'text' => 'Day', 'field' => 'name'));
  $cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'copy', 'object' => 'hours to');
  $cols[] = array('width' => 200, 'text' => 'Open time', 'field' => 'open', 'class' => 'edit');
  $cols[] = array('width' => 200, 'text' => 'Close time', 'field' => 'close', 'class' => 'edit');
  $cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'close', 'object' => 'every');
  self::putTable($cols);
 }

 protected function ajaxTabHours()
 {
  $db = DB::getDB();
  $table = WCentre::TABLE_CENTRE_SCHED;
  $rows = $db->queryMatrix($table, 'week_day,open_min,close_min', 'centre_id=' . WCentre::id());
  $hours = array();
  // Data storing
  for ($i = 1; $i <= 7; $i++)
  {
   $row = array('id' => $i, 'name' => Lang::dayOfWeekLong($i));
   if ($rows && array_key_exists($i, $rows))
   {
    $row['open'] = Util::min2str($rows[$i]['open_min']);
    $row['close'] = Util::min2str($rows[$i]['close_min']);
   }
   $hours[] = $row;
  }
  PageCom::addDataToAjax(array('hours' => $hours));
 }

 protected function processActionTabHoursModify()
 {
  $rowid = HTTP::param('rowid');
  $field = HTTP::param('field');
  $value = HTTP::param('value');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (array_search($rowid, array('1', '2', '3', '4', '5', '6', '7')) === false)
   return self::actionFail('Invalid "rowid" parameter specified: "' . $rowid . '"');
  if (!strlen($field))
   return self::actionFail('No "field" parameter value set');
  if (array_search($field, array('open', 'close')) === false)
   return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  $db = PageCom::db();
  $table = WCentre::TABLE_CENTRE_SCHED;
  $field .= '_min';
  $where = array('centre_id' => WCentre::id(), 'week_day' => $rowid);
  if (strlen($value))
  {
   $time = Util::str2min($value);
   if ($time == -1)
    return self::actionError('Invalid time value specified: "' . $value . '"');
   $value = DB::str($time);
  }
  else
   $value = 'null';
  if (!$db->mergeField($table, $field, $value, $where))
   return self::actionFailDB('Error updating DB table');
  $value = $db->queryField($table, $field, $where);
  PageCom::addToAjax('value', strlen($value) ? Util::min2str($value) : '', true);
  return true;
 }

 protected function processActionTabHoursCopy()
 {
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (array_search($rowid, array('1', '2', '3', '4', '5', '6', '7')) === false)
   return self::actionFail('Invalid "rowid" parameter specified: "' . $rowid . '"');
  $db = PageCom::db();
  $table = WCentre::TABLE_CENTRE_SCHED;
  //$prev = (intval($rowid) + 5) % 7 + 1;
  $where = array('centre_id' => WCentre::id(), 'week_day' => (intval($rowid) + 5) % 7 + 1);
  $row = $db->queryPairs($table, 'open_min,close_min', $where);
  $where['week_day'] = $rowid;
  if ($row && strlen($row['open_min']) && strlen($row['close_min']))
  {
   if (!$db->mergeFields($table, $row, $where))
    return self::actionFailDB('Error merging DB table');
  }
  else
   if (!$db->deleteRecords($table, $where))
    return self::actionFailDB('Error deleting record from DB table');
  return true;
 }

 protected function processActionTabHoursDayoff()
 {
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (array_search($rowid, array('1', '2', '3', '4', '5', '6', '7')) === false)
   return self::actionFail('Invalid "rowid" parameter specified: "' . $rowid . '"');
  $db = PageCom::db();
  $table = WCentre::TABLE_CENTRE_SCHED;
  $where = array('centre_id' => WCentre::id(), 'week_day' => $rowid);
  if (!$db->deleteRecords($table, $where))
   return self::actionFailDB('Error deleting record from DB table');
  return true;
 }

 protected function putTabBodyLvls()
 {
  self::art('bnd')->putTabBodyLvls();
 }

 protected function ajaxTabLvls()
 {
  self::art('bnd')->ajaxTabLvls();
 }

 protected function processActionTabLvlsCreate()
 {
  return self::art('bnd')->processActionTabLvlsCreate();
 }

 protected function processActionTabLvlsModify()
 {
  return self::art('bnd')->processActionTabLvlsModify();
 }

 protected function processActionTabLvlsDelete()
 {
  return self::art('bnd')->processActionTabLvlsDelete();
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
   $text = DB::getDB()->queryField('com_centre_abc', 'descr', 'centre_id=' . WCentre::id() . " and abc_id='$id'");
   if ($text && strlen($text))
    $descr[$id] = base64_encode($text);
  }
  PageCom::addDataToAjax(array('text' => $descr));
 }

 protected function processActionTabDescrSave()
 {
  $lang = HTTP::param('lang');
  $text = HTTP::param('text');
  $where = array('centre_id' => WCentre::id(), 'abc_id' => DB::str($lang));
  if (!PageCom::db()->mergeField('com_centre_abc', 'descr', DB::str($text), $where))
   return self::actionFailDB('Error merging DB table');
  $descr = PageCom::db()->queryField('com_centre_abc', 'descr', $where);
  PageCom::addToAjax('text', base64_encode($descr));
  return true;
 }

 protected function createGallery()
 {
  return WCentre::createGallery();
 }
}

?>
