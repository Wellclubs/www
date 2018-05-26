<?php

/**
 * Description of PageComArtSgr
 */
class PageComArtSgr extends PageComArt
{
 private $id;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->useParIndex = true;
  $this->tabs = array
  (
   'def' => 'General'
  ,'time' => 'Schemes'
  );
 }

 public function init()
 {
  if (PageCom::db()->queryField(WService::TABLE_GRP, 'count(*)', 'id=' . Base::parIndex()))
   $this->id = intval(Base::parIndex());
 }

 public function testPrivs()
 {
  if (!$this->id)
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
  return Lang::getObjTitle('Service group') . ' "' . WService::grpTitle(Base::parIndex()) . '"';
 }

 protected function putTabBodyDef()
 {
  $rows = array();
  $rows[] = array('field' => 'name', 'name' => 'Name', 'edit' => 1);
  self::putForm($rows);
  self::putSingleAction('delete', 'Permanently delete this service group', false, true);
 }

 protected function ajaxTabDef()
 {
  $data = array();
  $data['name'] = WService::grpTitles($this->id);
  PageCom::addToAjaxData('values', $data);
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
   if (!WService::setGrpTitles($this->id))
    return self::actionFailDBUpdate();
   $titles = WService::grpTitles($this->id);
   foreach ($titles as $key => $value)
    PageCom::addToAjax($key, $value);
   $titleChanged = true;
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

 protected function processActionTabDefDelete()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  if (!PageCom::db()->deleteRecords(WService::TABLE_GRP, 'id=' . $this->id))
   return self::actionFailDBDelete();
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/srvs/', true);
  return true;
 }

 protected function putTabBodyTime()
 {
 }

 protected function ajaxTabTime()
 {
 }
}

?>
