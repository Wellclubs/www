<?php

/**
 * Description of PageComArtSrv
 */
class PageComArtSrv extends PageComArt
{
 private $srvId;
 private $service;
 private $canViewMaster;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->useParIndex = true;
  $this->tabs = array
  (
   'def' => 'General'
  ,'tips' => 'Prices'
  ,'mtrs' => 'Team'
  ,'descr' => 'Description'
  ,'restr' => 'Restrictions'
  ,'notes' => 'Good to know'
  ,'imgs' => 'Images'
  );
 }

 public function init()
 {
  $this->srvId = Base::parIndex();
 }

 public function initData()
 {
  $this->service = new WService($this->srvId);
  if (Base::tab() == 'mtrs')
   $this->canViewMaster = PageCom::testPriv(WPriv::PRIV_ADD_MASTER);
 }

 public function testPrivs()
 {
  if (!$this->srvId)
   return false;
  if (WClient::id() == WCentre::memberId())
   return true;
  if (!WCentre::masterId())
   return false;
  return PageCom::testPriv(WPriv::PRIV_VIEW_MENU);
 }

 public function canBeEdited()
 {
  if (WClient::id() == WCentre::memberId())
   return true;
  if (!WCentre::masterId())
   return false;
  return PageCom::testPriv(WPriv::PRIV_EDIT_MENU);
 }

 protected function getParTitle()
 {
  if (!$this->service)
   $this->service = new WService($this->srvId);
  $title = (count($this->service->getPrcs()) > 1) ? 'Package' : 'Service';
  return Lang::getObjTitle($title) . ' "' . WService::srvTitle($this->srvId) . '"';
 }

 protected function putTabBodyDef()
 {
  $rows = array();
  $rows[] = array('field' => 'name', 'name' => 'Name', 'class' => 'optional', 'edit' => 1);
  self::putForm($rows);

  echo '<div class="block action edit">';

  echo '<div class="title">';
  echo htmlspecialchars(Lang::getPageWord('title', 'Procedures'));
  echo '</div>' . "\n";

  echo '<div class="button left" action="addprc">';
  echo htmlspecialchars(Lang::getPageWord('button', 'Add a procedure'));
  echo '</div>' . "\n";

  echo '<div class="wrapper">';
  $cols = array(array('width' => 800, 'text' => 'Procedure', 'field' => 'prc', 'class' => 'edit select prc'));
  $cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'delprc', 'object' => 'Procedure');
  self::putTable($cols, 'There are no procedures attached to the service', false, true);
  echo '</div>' . "\n";

  echo '</div>' . "\n";

  //$rows = array();
  //$rows[] = array('field' => 'limited', 'name' => 'Limited inventory', 'class' => 'bool right', 'edit' => 1);
  //self::putForm($rows);

  self::putSingleAction('delete', 'Permanently delete this service', false, true);
 }

 protected function ajaxTabDef()
 {
  $data = array();
  $data['name'] = WService::srvTitles($this->srvId);
  //$data['limited'] = $this->service->getLimited();
  // Data storing
  PageCom::addToAjaxData('values', $data);
  $prcs = array();
  $fields = 'prc_id,(select name from biz_menu_prc where id=prc_id)';
  $rows = DB::getDB()->queryRecords(WService::TABLE_SRV_PRC, $fields, 'srv_id=' . $this->srvId, 'serial');
  if ($rows)
   foreach ($rows as $row)
    $prcs[] = array
    (
     'id' => $row[0]
    ,'prc' => array('id' => $row[0], 'text' => Lang::getDBValueDef(WProc::TABLE_PRC . '_abc', null, 'prc_id=' . $row[0], $row[1]))
    //, 'title' => Lang::getDBValueDef(WProc::TABLE_PRC . '_abc', null, 'prc_id=' . $row[0], $row[1])
    );
  // Data storing
  PageCom::addToAjaxData('prcs', $prcs);
 }

 protected function processAcQueryTabDefPrc($term)
 {
  PageCom::addDataToAjax(WProc::acQuery($term));
  return true;
 }

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
   if (!WService::setSrvTitles($this->srvId))
    return self::actionFailDBUpdate();
   $titles = WService::srvTitles($this->srvId);
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
   $titleChanged = true;
  }
  else if (array_search($field, array('limited')) !== false)
  {
   if (!$db->modifyFields(WService::TABLE_SRV, array($field => ($value ? '1' : 'null')), 'id=' . $this->srvId))
    return self::actionFailDBUpdate();
   PageCom::addToAjax('value', !!$db->queryField(WService::TABLE_SRV, $field, 'id=' . $this->srvId), true);
  }
  else if (array_search($field, array('prc')) !== false)
  {
   $rowid = HTTP::param('rowid');
   if (!strlen($rowid))
    return self::actionFail('No "rowid" parameter value set');
   if($field == 'prc')
   {
    if ($value != $rowid)
    {
     if (!$db->queryField(WProc::TABLE_PRC, 'count(*)', 'id=' . $value . ' and hidden is null'))
      return self::actionFail('Invalid procedure id: ' . $value);
     if ($db->queryField(WService::TABLE_SRV_PRC, 'count(*)', 'srv_id=' . $this->srvId . ' and prc_id=' . $value) > 0)
      return self::actionError('This procedure is already attached to the current service');
     if (!$db->modifyFields(WService::TABLE_SRV_PRC, array('prc_id' => $value), array('srv_id' => $this->srvId, 'prc_id' => $rowid)))
      return self::actionFailDBUpdate();
     $title = Lang::getDBTitle(WProc::TABLE_PRC, 'prc', $value);
     PageCom::addToAjax('value', array('id' => $value, 'title' => $title), true);
    }
   }
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

 protected function processActionTabDefAddprc()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  $prc = intval(HTTP::param('prc'));
  if (!$prc)
   return self::actionFail('No "prc" parameter set');
  $db = PageCom::db();
  if ($db->queryField(WService::TABLE_SRV_PRC, 'count(*)', 'srv_id=' . $this->srvId) >= WService::MAX_PRC_COUNT)
   return self::actionError('You already have a maximum quantity of procedures in this service');
  if (!$db->insertValues(WService::TABLE_SRV_PRC, array('srv_id' => $this->srvId, 'prc_id' => $prc, 'serial' => $prc)))
   return self::actionFailDBInsert();
  return true;
 }

 protected function processActionTabDefDelprc()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (!PageCom::db()->queryField(WService::TABLE_SRV_PRC, 'count(*)', 'srv_id=' . $this->srvId . ' and prc_id<>' . $rowid))
   return self::actionFail('You can\'t delete the last procedure in the service');
  if (!PageCom::db()->deleteRecords(WService::TABLE_SRV_PRC, 'srv_id=' . $this->srvId . ' and prc_id=' . $rowid))
   return self::actionFailDBDelete();
  PageCom::addToAjax('uri', '', true);
  return true;
 }

 protected function processActionTabDefDelete()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  if (!PageCom::db()->deleteRecords(WService::TABLE_SRV, 'id=' . $this->srvId))
   return self::actionFailDBDelete();
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/srvs/', true);
  return true;
 }

 protected function putTabBodyTips()
 {
  self::putSingleAction('create', 'Add a new price option', true, true);
  $cols = array
  (
   array('width' => '30%', 'text' => 'Level', 'field' => 'level', 'class' => 'edit select level')
  ,array('width' => '40%', 'text' => 'Name', 'field' => 'name', 'class' => 'edit')
  ,array('width' => '10%', 'text' => 'Duration', 'field' => 'duration', 'class' => 'edit right img time')
  ,array('width' => '10%', 'text' => 'Price', 'field' => 'price', 'class' => 'edit right img money')
  ,array('width' => '10%', 'class' => 'btn', 'action' => 'delete', 'object' => 'Price option')
  );
  self::putTable($cols, 'There are currently no price options to view');
 }

 protected function ajaxTabTips()
 {
  $data = array();
  $rows = DB::getDB()->queryRecords(WService::TABLE_TIP, 'id,level_id,duration,price', 'srv_id=' . $this->srvId, 'serial');
  if ($rows)
  {
   foreach ($rows as $row)
   {
    $data[] = array
    (
     'id' => $row[0]
    ,'level' => array('id' => $row[1], 'title' => WBrand::getLevelTitle($row[1]))
    ,'name' => Lang::getDBTitles(WService::TABLE_TIP, 'tip', $row[0])
    ,'duration' => $row[2]
    ,'price' => $row[3]
    );
   }
  }
  // Data storing
  PageCom::addDataToAjax(array('tips' => $data));
 }

 protected function processActionTabTipsLevels()
 {
  PageCom::addDataToAjax(WBrand::getLevels());
  return true;
 }

 protected function processActionTabTipsCreate()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  return WService::createTip($this->srvId);
 }

 protected function processActionTabTipsModify()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
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
  if ($field == 'name')
  {
   if (!Lang::setDBTitles(WService::TABLE_TIP, 'tip', $rowid))
    return self::actionFailDBUpdate();
   $titles = Lang::getDBTitles(WService::TABLE_TIP, 'tip', $rowid);
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
  }
  else if ($field == 'level')
  {
   if (!is_numeric($value))
    $value = 'null';
   if (!$db->modifyFields(WService::TABLE_TIP, array('level_id' => $value), 'id=' . $rowid))
    return self::actionFailDBUpdate();
   $value = $db->queryField(WService::TABLE_TIP, 'level_id', 'id=' . $rowid);
   PageCom::addToAjax('value', array('id' => $value, 'title' => WBrand::getLevelTitle($value)), true);
  }
  else if (array_search($field, array('duration', 'price')) !== false)
  {
   if (!is_numeric($value))
    return self::actionFail('Invalid numeric value specified');
   if (!$db->modifyFields(WService::TABLE_TIP, array($field => intval($value)), 'id=' . $rowid))
    return self::actionFailDBUpdate();
   PageCom::addToAjax('value', $db->queryField(WService::TABLE_TIP, $field, 'id=' . $rowid), true);
  }
  else
   return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  return true;
 }

 protected function processActionTabTipsDelete()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (!PageCom::db()->deleteRecords(WService::TABLE_TIP, 'id=' . $rowid))
   return self::actionFailDBDelete();
  return true;
 }

 protected function putTabBodyMtrs()
 {
  self::putSingleAction('addmtr', 'Add a master', true, true);
  $cols = array(array('width' => 400, 'text' => 'Name', 'field' => 'name', 'class' => 'uri ax', 'uri' => 'mtr-uri'));
  $cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'delete', 'object' => 'Master');
  self::putTable($cols, 'There are no masters to view');
 }

 protected function ajaxTabMtrs()
 {
  $data = array();
  $fields = 'id,(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id),all_services';
  $where = 'centre_id=' . WCentre::id() . ' and for_service is not null' .
    ' and (all_services is not null or id in (select master_id from ' . WService::TABLE_SRV_MTR . ' where srv_id=' . $this->srvId . '))';
  $rows = DB::getDB()->queryRecords('com_master a', $fields, $where, 'serial');
  if ($rows)
   foreach ($rows as $row)
   {
    $mtr = array
    (
     'id' => $row[0]
    ,'name' => $row[1]//array('text' => $row[1], 'uri' => 'srv-' . WCentre::id() . '/mtr-' . $row[0] . '/')
    );
    if ($row[2])
     $mtr['all-srv'] = 'true';
    if ($this->canViewMaster)
     $mtr['mtr-uri'] = 'ctr-' . WCentre::id() . '/mtr-' . $row[0] . '/';
    $data[] = $mtr;
   }
  // Data storing
  PageCom::addDataToAjax(array('mtrs' => $data));
 }

 protected function processActionTabMtrsMtrs()
 {
  PageCom::addDataToAjax(WService::getFreeMtrs(WCentre::id(), $this->srvId));
  return true;
 }

 protected function processActionTabMtrsAddmtr()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $mtr = intval(HTTP::param('mtr'));
  if (!$mtr)
   return self::actionFail('No "mtr" parameter value set');
  return WService::addMtr($this->srvId, $mtr);
 }

 protected function processActionTabMtrsDelete()
 {
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  if (!PageCom::db()->deleteRecords(WService::TABLE_SRV_MTR, 'srv_id=' . $this->srvId . ' and master_id=' . $rowid))
   return self::actionFailDBDelete();
  return true;
 }

 protected function ajaxTextArea($field)
 {
  $text = array();
  foreach (Lang::map() as $id => $lang)
  {
   $value = DB::getDB()->queryField(WService::TABLE_SRV . '_abc', $field, 'srv_id=' . $this->srvId . " and abc_id='$id'");
   if ($value && strlen($value))
    $text[$id] = base64_encode($value);
  }
  PageCom::addDataToAjax(array('text' => $text));
 }

 protected function processActionTextAreaSave($field)
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $lang = HTTP::param('lang');
  $text = HTTP::param('text');
  $where = array('srv_id' => $this->srvId, 'abc_id' => DB::str($lang));
  if (!PageAdm::db()->mergeField(WService::TABLE_SRV . '_abc', $field, DB::str($text), $where))
   return self::actionFailDB('Error merging DB table');
  $value = PageAdm::db()->queryField(WService::TABLE_SRV . '_abc', $field, $where);
  PageCom::addToAjax('text', base64_encode($value));
  return true;
 }

 protected function putTabBodyDescr()
 {
  self::putTextArea();
 }

 protected function ajaxTabDescr()
 {
  $this->ajaxTextArea('descr');
 }

 protected function processActionTabDescrSave()
 {
  return $this->processActionTextAreaSave('descr');
 }

 protected function putTabBodyRestr()
 {
  self::putTextArea();
 }

 protected function ajaxTabRestr()
 {
  $this->ajaxTextArea('restr');
 }

 protected function processActionTabRestrSave()
 {
  return $this->processActionTextAreaSave('restr');
 }

 protected function putTabBodyNotes()
 {
  self::putTextArea();
 }

 protected function ajaxTabNotes()
 {
  $this->ajaxTextArea('notes');
 }

 protected function processActionTabNotesSave()
 {
  return $this->processActionTextAreaSave('notes');
 }

 protected function createGallery()
 {
  return WService::createGallery();
 }
}

?>
