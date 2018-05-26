<?php

/**
 * Description of PageComArtSrvs
 */
class PageComArtSrvs extends PageComArt
{
 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->title = 'Services';
 }

 public function testPrivs()
 {
  return PageCom::testPriv(WPriv::PRIV_VIEW_MENU);
 }

 public function canBeEdited()
 {
  return PageCom::testPriv(WPriv::PRIV_EDIT_MENU);
 }

 public function putArtBody()
 {
  self::putMultiActions(array
  (
   array('action' => 'addgrp', 'title' => 'Add new group')
  ), true);
  echo '<div class="blocks grps"></div>' . "\n";
 }

 public function ajax()
 {
  $dataGrps = array();
  $grps = WService::groups();
  foreach ($grps as $grpId)
  {
   $dataGrp = array('id' => $grpId, 'title' => WService::grpTitle($grpId));
   $srvs = WService::services($grpId);
   $dataSrvs = array();
   foreach ($srvs as $srvId)
    $dataSrvs[] = array('id' => $srvId, 'name' => WService::srvTitles($srvId), 'tips' => WService::getTipList($srvId));
   $dataGrp['srvs'] = $dataSrvs;
   $dataGrps[] = $dataGrp;
  }
  $data['grps'] = $dataGrps;
  // Data storing
  PageCom::addDataToAjax($data);
 }

 protected function processAcQueryPrc($term)
 {
  PageCom::addDataToAjax(WProc::acQuery($term));
  return true;
 }

 protected function processActionLevels()
 {
  PageCom::addDataToAjax(WBrand::getLevels());
  return true;
 }

 protected function processActionAddgrp()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  if (!WService::createGrp(HTTP::param('name')))
   return false;
  PageCom::addToAjax('uri', '', true);
  return true;
 }

 protected function processActionModify()
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
  if ($field == 'grp-name')
  {
   if (!Lang::setDBTitles('com_menu_grp', 'grp', $rowid))
    return self::actionFailDBUpdate();
   $titles = Lang::getDBTitles('com_menu_grp', 'grp', $rowid);
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
   return true;
  }
  return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
 }

 protected function processActionDelgrp()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  if (!WService::deleteGrp(HTTP::param('grpid')))
   return false;
  PageCom::addToAjax('uri', '', true);
  return true;
 }

 protected function processActionAddsrv()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $name = HTTP::param('name');
  if (!strlen($name))
   return self::actionFail('No "name" parameter value set');
  $grp = intval(HTTP::param('grp'));
  if (!$grp)
   return self::actionFail('No "grp" parameter value set');
  $prc = intval(HTTP::param('prc'));
  if (!$prc)
   return self::actionFail('No "prc" parameter value set');
  $db = PageCom::db();
  if ($db->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id=' . WCentre::id()) >= WService::MAX_SRV_COUNT)
   return self::actionError('You already have a maximum quantity of services in this group');
  if ($db->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id=' . WCentre::id() . ' and name=' . DB::str($name)))
   return self::actionError('This name is already used for another service');
  if (!$db->insertValues(WService::TABLE_SRV, array('centre_id' => WCentre::id(), 'grp_id' => $grp, 'name' => DB::str($name))))
   return self::actionFailDBInsert();
  $serviceId = $db->insert_id;
  $db->modifySerialAfterInsert(WService::TABLE_SRV);
  if (!$db->insertValues(WService::TABLE_SRV_PRC, array('srv_id' => $serviceId, 'prc_id' => $prc, 'serial' => $prc)))
   return self::actionFailDBInsert();
  if(!Lang::setDBTitlesOnly(WService::TABLE_SRV, array('srv_id' => $serviceId), true))
   return self::actionFailDBInsert();
  if (!WService::createTip($serviceId))
   return false;
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/srv-' . $serviceId . '/', true);
  return true;
 }

 protected function processActionAddpkg()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $name = HTTP::param('name');
  if (!strlen($name))
   return self::actionFail('No "name" parameter value set');
  $grp = intval(HTTP::param('grp'));
  if (!$grp)
   return self::actionFail('No "grp" parameter value set');
  $prcs = explode(',', HTTP::param('prcs'));
  if (!count($prcs))
   return self::actionFail('No "prcs" parameter value set');
  $db = PageCom::db();
  if ($db->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id=' . WCentre::id()) >= WService::MAX_SRV_COUNT)
   return self::actionError('You already have a maximum quantity of services in this group');
  if ($db->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id=' . WCentre::id() . ' and name=' . DB::str($name)))
   return self::actionError('This name is already used for another service');
  if (!$db->insertValues(WService::TABLE_SRV, array('centre_id' => WCentre::id(), 'grp_id' => $grp, 'name' => DB::str($name))))
   return self::actionFailDBInsert();
  $serviceId = $db->insert_id;
  $db->modifySerialAfterInsert(WService::TABLE_SRV);
  foreach ($prcs as $prc)
   if (!$db->insertValues(WService::TABLE_SRV_PRC, array('srv_id' => $serviceId, 'prc_id' => $prc, 'serial' => $prc)))
    return self::actionFailDBInsert();
  if(!Lang::setDBTitlesOnly(WService::TABLE_SRV, array('srv_id' => $serviceId), true))
   return self::actionFailDBInsert();
  if (!WService::createTip($serviceId))
   return false;
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/srv-' . $serviceId . '/', true);
  return true;
 }

}

?>
