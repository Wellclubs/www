<?php

class PageCom extends Page
{
 private static $privs;

 public function __construct()
 {
  $this->modes = array($this->getDefaultMode());
  $this->indexes = array('ctr');
  self::initDB(true);
 }

 public function getDefaultMode() { return 'all'; }

 public function getDefaultPar()
 {
  switch (Base::mode())
  {
  case 'ctr' :
   return 'brief';
  }
  return 'home';
 }

 public function validatePar($par)
 {
  $art = &PageComArt::art($par);
  return is_object($art) && !$art->useParIndex();
 }

 public function validateParWithIndex($par)
 {
  $art = &PageComArt::art($par);
  return is_object($art) && $art->useParIndex();
 }

 public function getDefaultTab()
 {
  return 'def';
 }

 public function validateTab($tab)
 {
  $art = &PageComArt::art(Base::par());
  $tabs = &$art->tabs();
  if (!count($tabs))
   return false;
  if (!strlen($tab))
   $tab = $this->getDefaultTab();
  return array_key_exists($tab, $tabs) !== false;
 }

 public static function &app() { return self::$app; }

 private static $ctrs;
 private static $ctrId;

 public static function &ctrs()
 {
  if (!self::$ctrs)
   self::$ctrs = WClient::me()->centres(WDomain::id());
  return self::$ctrs;
 }

 const COOKIE_CTR = 'ctr';

 public static function ctrId()
 {
  if (self::$ctrId === null)
  {
   $ctrs = self::ctrs();
   if (!count($ctrs))
    self::$ctrId == false;
   else
   {
    $ids = array_keys($ctrs);
    if (Base::mode() == 'ctr')
    {
     self::$ctrId = Base::index();
     if (array_search(self::$ctrId, $ids) === false)
      self::$ctrId = false;
    }
    else
    {
     $id = HTTP::getCookie(self::COOKIE_CTR);
     if ($id && (array_search($id, $ids) !== false))
      self::$ctrId = $id;
     else
      self::$ctrId = $ids[0];
    }
   }
   if (self::$ctrId === false)
   {
    if (HTTP::hasCookie(self::COOKIE_CTR))
     HTTP::clearCookie(self::COOKIE_CTR);
   }
   else
   {
    if (HTTP::getCookie(self::COOKIE_CTR) != self::$ctrId)
     HTTP::setCookie(self::COOKIE_CTR, self::$ctrId);
   }
  }
  return self::$ctrId;
 }

 public static function &ctr()
 {
  $ctrId = self::ctrId();
  if ($ctrId === false)
   return null;
  return self::$ctrs[$ctrId];
 }

 /**
  * Used for page header (always non-empty)
  * @return string
  */
 public static function modeTitle()
 {
  switch (Base::mode())
  {
  case 'ctr' :
   return PageComArt::getModeTitle();
  }
  return Lang::getPageWord('title', 'All centres');
 }

 /**
  * Test current master's privilege for the current centre
  * @param int $priv Testing privilege id
  * @return bool True if privilege is granted
  */
 public static function testPriv($priv = 0)
 {
  //if ((Base::mode() != 'ctr') || !WCentre::id())
  // return false;
  if (!self::ctr() || !WCentre::id())
   return false;
  if (WClient::id() == WCentre::memberId())
   return true;
  if (!WCentre::masterId())
   return false;
  if (!$priv)
   return true;
  if (!self::$privs)
   $privs = WPriv::getMasterListPriv(WCentre::masterId());
  return array_key_exists($priv, $privs);
 }

 public function showPage()
 {
  /// Action processing goes here
  if (array_key_exists('fixup', $_REQUEST) && array_key_exists('key', $_REQUEST))
  {
   WClient::fixup($_REQUEST['fixup'], $_REQUEST['key']);
  }

  // Access control
  WClient::initCurrent();
  if (!WClient::id() || !WClient::me()->isMember() && !WClient::me()->isMaster())
  {
   header('Location: ' . Base::bas() . 'biz/');
   return true;
  }

  // DB Repair
  if (WClient::me()->isMaster() && !WClient::me()->isMember())
  {
   if (!DB::getAdminDB()->insertValues(WMember::TABLE_MEMBER, array('client_id' => WClient::id())))
   {
    $error = 'Error inserting a record to a database table';
    if (Base::ajax())
     echo "{'error':'$error'}";
    else
     echo "$error<br/>\n";
    return false;
   }
  }

  $ctrId = self::ctrId();
  if ($ctrId !== false)
   WCentre::initCurrent($ctrId, true);

  /// Mode processing goes here
  if (Base::mode() == 'ctr')
  {
   if (!WCentre::id())
    return false;
   if ((WCentre::memberId() != WClient::id()) && !WCentre::masterId())
    return false;
   if (Base::par() == 'srv')
   {
    WService::initCurrent(Base::parIndex());
    if ((WCentre::memberId() != WClient::id()) && !WCentre::masterId())
     return false;
   }
  }
  else
  {
   if (Base::par() == 'bnd')
   {
    WBrand::initCurrent(Base::parIndex());
    if ((WBrand::memberId() != WClient::id()))
     return false;
   }
  }

  /// Common command processing goes here
  if (array_key_exists('cmd', $_REQUEST))
  {
   return self::processCmd($_REQUEST['cmd']);
  }

  /// Par processing goes here
  $art = &PageComArt::art(Base::par());
  $art->init();

  /// Action processing goes here
  if (array_key_exists('action', $_REQUEST))
  {
   if (!$art->canBeShown())
    self::ajaxErrorAccessDenied();
   else if ($art->processAnyAction($_REQUEST['action']))
    $this->addToAjax('result', 'OK');
   echo JSON::encode(self::$ajax);
   return true;
  }

  /// Autocomplete query processing goes here
  if (array_key_exists('ac', $_REQUEST))
  {
   if (!$art->canBeShown())
    self::ajaxErrorAccessDenied();
   else if (!$art->processAnyAcQuery($_REQUEST['ac'], $_REQUEST['term']))
    return false;
   echo JSON::encode(self::$ajax['data']);
   return true;
  }

  if (Base::ajax())
  {
   echo JSON::encode(self::getAjaxData($art));
   return true;
  }

  if (!$art->canBeShown())
   return false;

  $art->initData(); ///< Must be called before getArtTitle()
  Base::setTitle($art->getArtTitle(true));

  return Base::executeTextFile('php/com.php');
 }

 public static function ajaxErrorAccessDenied()
 {
  self::addToAjax('deny', Lang::getPageWord('error', 'Data access denied'));
 }

 private function getAjaxData(&$art)
 {
  self::addToApp('mode', Base::mode());
  self::addToApp('par', Base::par());
  self::addToApp('tab', $art->usesTabs() ? Base::tab() : '');
  //self::addToApp('ctr', (Base::mode() == 'ctr') ? Base::index() : '');
  self::addToApp('pretitle', self::pretitle());
  if (Base::mode() == 'ctr') //if (PageCom::ctr())
  {
   self::addToApp('ctr', PageCom::ctrId());
   $ctrs = array();
   foreach (PageCom::ctrs() as $id => $ctr)
    $ctrs[$id] = $ctr['name'];
   self::addToApp('ctrs', $ctrs);
   self::addToApp('curr', WCentre::currency());
  }

  if ($art->canBeShown())
  {
   $art->initData(); ///< Must be called before getArtTitle()
   Base::setTitle($art->getArtTitle(true));
   self::addToApp('title', Base::fullTitle());
   self::addToApp('subtitle', $art->getArtTitle(false));
   $art->ajax();
   if ($art->canBeEdited())
    self::addToAjaxData('edit', true);
  }
  else
  {
   self::ajaxErrorAccessDenied();
  }

  // Ctr-depended menu items
  $menu = array();
  $arts = &PageComArt::arts();
  foreach ($arts as $id => $a)
   if ($a->useForCtr() && $a->showInMenu())
    $menu[$id] = $a->getMenuHref();
  self::addToAjax('menu', $menu);
  if (self::ctr())
  {
   $menuCtr = self::$ctrs[self::$ctrId]['name'];
   if (mb_strlen($menuCtr) > 20)
    $menuCtr = trim(mb_substr($menuCtr, 0, 20)) . '...';
   self::addToAjax('menuCtr', $menuCtr);
  }

  // Go to page
  $result = array();
  $result['app'] = self::$app;
  foreach (self::$ajax as $key => $value)
   $result[$key] = $value;
  if (!array_key_exists('error', $result) && !array_key_exists('failure', $result))
   $result['result'] = 'OK';
  return $result;
 }

 public static function pretitle()
 {
  if (Base::mode() == 'ctr')
   return WCentre::getTitle();
  return Lang::getSiteWord('title','Business profile');
 }

 public static function subtitle()
 {
  $art = &PageComArt::art(Base::par());
  return $art->getArtTitle(false);
 }

 public static function processCmd($cmd)
 {
  if ($cmd == 'clts')
  {
   $where = 'visited is not null';
   $email = HTTP::get('email');
   if (strlen($email))
    $where = '(email like \'' . addslashes($email) . '%\') and ' . $where;
   $phone = HTTP::get('phone');
   if (strlen($phone))
    $where = '(100000000000000+number like \'%' . Util::pureNumber($phone) . '\') and ' . $where;
   $sname = HTTP::get('sname');
   if (strlen($sname))
    $where = '(lastname like \'' . addslashes($sname) . '%\') and ' . $where;
   $fname = HTTP::get('fname');
   if (strlen($fname))
    $where = '(firstname like \'' . addslashes($fname) . '%\') and ' . $where;
   $fields = 'id,firstname,lastname,phone,email';
   $clts = DB::getDB()->queryArrays(WClient::TABLE_CLIENT, $fields, $where, 'visited desc', 20);
   //echo DB::lastQuery();
   echo json_encode(array('result' => 'OK', 'data' => $clts ? $clts : array()));
   return true;
  }
  return false;
 }

 /**
  * Function returns an array of centre ids available for the client list view
  * @return array of int List of centre ids available for the client list view
  */
 public static function centreIdsForViewClients()
 {
  $result = array();
  if (Base::mode() === 'ctr')
  {
   if (WCentre::memberId() !== WClient::id())
   {
    if (!WCentre::masterId())
     return;
    $privs = WPriv::getMasterListPriv(WCentre::masterId());
    if (is_null($privs) || !is_array($privs) || !count($privs))
     return;
    if (!array_key_exists(WPriv::PRIV_VIEW_CLT_LIST, $privs))
     return;
   }
   $result[] = WCentre::id();
  }
  else
  {
   $ctrs = WClient::me()->centres();
   foreach ($ctrs as $id => $ctr)
   {
    if ($ctr['owner'] !== true)
    {
     $privs = $ctr['privs'];
     if (is_null($privs) || !is_array($privs) || !count($privs))
      continue;
     if (!array_key_exists(WPriv::PRIV_VIEW_CLT_LIST, $privs))
      continue;
    }
    $result[] = $id;
   }
  }
  return $result;
 }


}

?>