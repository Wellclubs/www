<?php

/**
 * Description of PageComArtMtr
 */
class PageComArtMtr extends PageComArt
{
 private $mtrId;
 private $master;
 private $client;
 
 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->useParIndex = true;
  $this->tabs = array('def' => 'General', 'privs' => 'Rights');
 }

 public function testPrivs()
 {
  if (WClient::id() == WCentre::memberId())
   return true;
  if (!WCentre::masterId())
   return false;
  return true;
 }

 public function canBeEdited()
 {
  if (WClient::id() == WCentre::memberId())
   return true;
  if (!WCentre::masterId())
   return false;
  if (!PageCom::testPriv(WPriv::PRIV_ADD_MASTER))
   return false;
  switch (Base::tab())
  {
  case 'privs' :
   return PageCom::testPriv(WPriv::PRIV_EDIT_PRIVS);
  }
  return true;
 }

 private function isFireEnabled()
 {
  return (($this->client->getId() != WClient::id()) && self::canBeEdited());
 }

 public function init()
 {
  $this->mtrId = Base::parIndex();
 }

 public function initData()
 {
  if ($this->master)
   return;
  $this->master = new WMaster($this->mtrId);
  $this->client = $this->master->getClient();
 }

 protected function getParTitle()
 {
  return Lang::getObjTitle('Employee') . ' "' . $this->client->getName() . '"';
 }

 protected function putTabBodyDef()
 {
  $rows = array();
  $rows[] = array('field' => 'name', 'name' => 'Name', 'class' => 'uri ax');
  $rows[] = array('field' => 'email', 'name' => 'E-mail');
  $rows[] = array('field' => 'job_title', 'name' => 'Job title', 'class' => 'optional', 'edit' => 1);
  $rows[] = array('field' => 'for_service', 'name' => 'Provides services', 'class' => 'bool right', 'edit' => 1);
  $rows[] = array('field' => 'all_services', 'name' => 'All services', 'class' => 'bool right', 'edit' => 1);
  $rows[] = array('field' => 'sel_by_name', 'name' => 'Selectable by name', 'class' => 'bool right', 'edit' => 1);
  $rows[] = array('field' => 'level', 'name' => 'Level', 'edit' => 1, 'class' => 'select level');
  self::putForm($rows);
  self::putSingleAction('delete', 'Fire this employee', false, false);
 }

 protected function ajaxTabDef()
 {
  $data = array();
  $data['name'] = array('text' => '' . $this->client->getName(), 'uri' => 'ctr-' . WCentre::id() . '/clt-' . $this->client->getId() . '/');
  $data['email'] = '' . $this->client->getEmail();
  $data['job_title'] = '' . $this->master->getJobTitle();
  if ($this->master->getForService())
  {
   $data['for_service'] = '1';
   $data['all_services'] = $this->master->getAllServices();
   $data['sel_by_name'] = $this->master->getSelByName();
   $data['level'] = array('id' => $this->master->getLevelId(), 'text' => $this->master->getLevelName());
  }
  else
  {
   $data['for_service'] = '';
   $text = Lang::getSiteWord('msg', 'User does not provide services');
   $data['all_services'] = $text;
   $data['sel_by_name'] = $text;
   $data['level'] = $text;
  }
  PageCom::addToAjaxData('values', $data);
  if (!self::isFireEnabled())
   PageCom::addToAjaxData('cant_fire', true);
 }

 protected function processActionTabDefLevels()
 {
  PageCom::addDataToAjax(WBrand::getLevels());
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
  if (array_search($field, array('job_title', 'level_id')) !== false)
  {
   if (!$db->modifyFields(WMaster::TABLE_MASTER, array($field => DB::str($value)), 'id=' . $this->mtrId))
    return self::actionFailDB('Error updating DB table');
   PageCom::addToAjax('value', $db->queryField(WMaster::TABLE_MASTER, $field, 'id=' . $this->mtrId), true);
  }
  else if (array_search($field, array('for_service', 'all_services', 'sel_by_name')) !== false)
  {
   if (!$db->modifyFields(WMaster::TABLE_MASTER, array($field => ($value ? '1' : 'null')), 'id=' . $this->mtrId))
    return self::actionFailDBUpdate();
   PageCom::addToAjax('value', !!$db->queryField(WMaster::TABLE_MASTER, $field, 'id=' . $this->mtrId), true);
  }
  else if ($field == 'level')
  {
   if (!is_numeric($value))
    $value = 'null';
   if (!$db->modifyFields(WMaster::TABLE_MASTER, array('level_id' => $value), 'id=' . $this->mtrId))
    return self::actionFailDBUpdate();
   $value = $db->queryField(WMaster::TABLE_MASTER, 'level_id', 'id=' . $this->mtrId);
   PageCom::addToAjax('value', array('id' => $value, 'text' => WBrand::getLevelTitle($value)), true);
  }
  else
   return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  if ($field == 'for_service')
   PageCom::addToAjax('uri', '', true);
  return true;
 }

 protected function processActionTabDefDelete()
 {
  $this->initData();
  if (!self::isFireEnabled())
   return self::actionErrorAccess();
  $db = PageCom::db();
  if (!$db->deleteRecords(WMaster::TABLE_MASTER, 'id=' . $this->master->getId()))
   return self::actionFailDBDelete();
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/mtrs/', true);
  return true;
 }

 protected function putTabBodyPrivs()
 {
  $rows = array();
  $rows[] = array('field' => 'can_connect', 'name' => 'Operator', 'class' => 'bool right', 'edit' => 1);
  $rows[] = array('field' => 'role', 'name' => 'Role', 'class' => 'select role', 'edit' => 1);
  self::putForm($rows);
  self::putBlocksBegin();
  foreach (WPriv::getListPrGr() as $grId => $prgr)
  {
   echo '<div class="block">' . "\n";
   echo '<div class="title">';
   echo htmlspecialchars(Lang::getDBTitle(WPriv::TABLE_PRGR, 'prgr', $grId, $prgr['name']));
   echo '</div>' . "\n";
   foreach ($prgr['privs'] as $prId => $prName)
   {
    echo '<div class="priv">';
    echo '<input type="checkbox" priv="' . $prId . '">&nbsp;';
    echo htmlspecialchars(Lang::getDBTitle(WPriv::TABLE_PRIV, 'priv', $prId, $prName));
    echo '</div>' . "\n";
   }
   echo '</div>' . "\n";
  }
  self::putBlocksEnd();
 }

 protected function ajaxTabPrivs()
 {
  $data = array();
  $data['can_connect'] = ($this->master->getClientId() == WCentre::memberId()) ? Lang::getSiteWord('msg', 'User is an owner') : $this->master->getCanConnect();
  $data['role'] = array('id' => $this->master->getRoleId(), 'text' => $this->master->getRoleName());
  $privs = array();
  foreach (WPriv::getMasterListPriv($this->master->getId()) as $prId => $prName)
   $privs[] = $prId;
  $data['privs'] = $privs;
  PageCom::addToAjaxData('values', $data);
 }

 protected function processActionTabPrivsRoles()
 {
  PageCom::addDataToAjax(WPriv::getRoles());
  return true;
 }

 protected function processActionTabPrivsModify()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  $field = HTTP::param('field');
  $value = HTTP::param('value');
  if (!strlen($field))
   return self::actionFail('No "field" parameter value set');
  $db = PageCom::db();
  if ($field == 'role')
  {
   if (!is_numeric($value))
    $value = 'null';
   $oldRole = $db->queryField('com_master', 'role_id', 'id=' . $this->mtrId);
   if (!$db->modifyFields('com_master', array('role_id' => $value), 'id=' . $this->mtrId))
    return self::actionFailDBUpdate();
   $db->deleteRecords('com_master_priv', 'master_id=' . $this->mtrId);
   if ($oldRole && ($value == 'null'))
    $db->query('insert into com_master_priv(master_id,priv_id)' .
      ' select ' . $this->mtrId . ',priv_id from art_master_role_priv where role_id=' . $oldRole);
   $value = $db->queryField('com_master', 'role_id', 'id=' . $this->mtrId);
   PageCom::addToAjax('value', array('id' => $value, 'text' => WPriv::getRoleTitle($value)), true);
   PageCom::addToAjax('uri', '', true);
  }
  else if ($field == 'privs')
  {
   $privs = explode(',', $value);
   if (!is_array($privs))
    return self::actionFail('Invalid "value" parameter value format');
   if (!$db->modifyFields('com_master', array('role_id' => 'null'), 'id=' . $this->mtrId))
    return self::actionFailDBUpdate();
   $db->deleteRecords('com_master_priv', 'master_id=' . $this->mtrId);
   foreach ($privs as $privId)
    $db->insertValues('com_master_priv', array('master_id' => $this->mtrId, 'priv_id' => $privId));
  }
  else if (array_search($field, array('can_connect')) !== false)
  {
   if (!$db->modifyFields('com_master', array($field => ($value ? '1' : 'null')), 'id=' . $this->mtrId))
    return self::actionFailDBUpdate();
   PageCom::addToAjax('value', !!$db->queryField('com_master', $field, 'id=' . $this->mtrId), true);
  }
  else
   return self::actionFail('Invalid "field" parameter specified: "' . $field . '"');
  return true;
 }

 /*protected function putTabBodyPrivs()
 {
  echo '<div class="role">' . "\n";
  echo '<select><option />' . "\n";
  $privs = DB::getDB()->queryMatrix(WPriv::TABLE_ROLE, 'id,name', 'id>1', 'id');
  if ($privs)
  {
   foreach ($privs as $id => $name)
   {
    echo '<option value="' . $id . '">' .
    htmlspecialchars(Lang::getDBValueDef(WPriv::TABLE_ROLE . '_abc', null, 'role_id=' . $id, $name)) .
    '</option>' . "\n";
   }
  }
  echo '</select>' . "\n";
  echo '</div>' . "\n";
 }*/
}
?>
