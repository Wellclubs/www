<?php

/**
 * Description of PageComArtMtrs
 */
class PageComArtMtrs extends PageComArt
{
 const MAX_COUNT = 10;

 private $canAddMaster = null;
 private $canViewLevel = null;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->title = 'Staff';
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
  if ($this->canAddMaster === null)
   return PageCom::testPriv(WPriv::PRIV_ADD_MASTER);
  return $this->canAddMaster;
 }

 public function initData()
 {
  $this->canAddMaster = PageCom::testPriv(WPriv::PRIV_ADD_MASTER);
  // Non-trivial algorithm
  $this->canViewLevel = (WBrand::memberId() == WCentre::memberId()); // Is the centre owner the brand owner?
  if ($this->canViewLevel) // The centre owner is the brand owner
  {
   if (WCentre::memberId() != WClient::id()) // Is the client the centre owner?
    $this->canViewLevel &= PageCom::testPriv(WPriv::PRIV_EDIT_CTR_DATA);
  }
 }

 public function putArtBody()
 {
  $buttons = array
  (
   array('class' => 'add', 'title' => 'Add registered user')
  ,array('class' => 'create', 'title' => 'Register new user')
  );
  self::putMultiActions($buttons, true);
  echo '<div class="blocks mtrs"></div>' . "\n";
 }

 public function ajax()
 {
  $data = array();
  // List of masters
  $mtrs = array();
  $fields = 'id,level_id,job_title';
  $fields .= ',(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id)name';
  $fields .= ',(select email from biz_client where id=a.client_id)email';
  $fields .= ',(select name from com_level where id=a.level_id)level';
  $rows = PageCom::db()->queryArrays('com_master a', $fields, 'centre_id=' . WCentre::id());
  if ($rows)
  {
   $viewMtr = $this->canAddMaster;
   $viewLvl = $this->canViewLevel;
   foreach ($rows as $row)
   {
    $mtr = array
    (
      'id' => $row['id'],
      'name' => $row['name'],
      'email' => $row['email'],
      'job' => '' . $row['job_title']
    );
    if ($viewMtr)
     $mtr['mtr-uri'] = 'ctr-' . WCentre::id() . '/mtr-' . $row['id'] . '/';
    $lvlId = $row['level_id'];
    if ($lvlId)
    {
     $mtr['lvl'] = Lang::getDBValueDef('com_level_abc', 'title', 'level_id=' . $lvlId, $row['level']);
     if ($viewLvl)
      $mtr['lvl-uri'] = 'bnd-' . WBrand::id() . '/lvl-' . $row['level_id'] . '/';
    }
    $mtrs[] = $mtr;
   }
  }
  $data['mtrs'] = $mtrs;
  // Data storing
  PageCom::addDataToAjax($data);
 }

 const ERROR_MAX_COUNT = 'You already have a maximum number of staff';

 protected function processActionAdd()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $email = HTTP::param('email');
  if (!strlen($email))
   return self::actionFail('No "email" parameter value set');
  $db = PageCom::db();
  if ($db->queryField('com_master', 'count(*)', 'centre_id=' . WCentre::id()) >= self::MAX_COUNT)
   return self::actionError(ERROR_MAX_COUNT);
  $client = $db->queryField(WClient::TABLE_CLIENT, 'id', 'email=' . DB::str($email));
  if (!$client)
   return self::actionError('User with this E-mail address is not registered');
  $master = $db->queryField('com_master', 'id', 'centre_id=' . WCentre::id() . ' and client_id=' . $client);
  if ($master)
   return self::actionError('Employee with this E-mail address is already added');
  if (!$db->insertValues('com_master', array('centre_id' => WCentre::id(), 'client_id' => $client)))
   return self::actionFailDBInsert();
  $mtrId = $db->insert_id;
  $db->modifySerialAfterInsert('com_master');
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/mtr-' . $mtrId . '/', true);
  return true;
 }

 protected function processActionCreate()
 {
  if (!$this->canBeEdited())
   return self::actionErrorAccess();
  $firstname = HTTP::param('firstname');
  if (!strlen($firstname))
   return self::actionFail('No "firstname" parameter value set');
  $lastname = HTTP::param('lastname');
  if (!strlen($lastname))
   return self::actionFail('No "lastname" parameter value set');
  $email = HTTP::param('email');
  if (!strlen($email))
   return self::actionFail('No "email" parameter value set');
  $db = PageCom::db();
  if ($db->queryField('com_master', 'count(*)', 'centre_id=' . WCentre::id()) >= self::MAX_COUNT)
   return self::actionError(ERROR_MAX_COUNT);
  $client = $db->queryField(WClient::TABLE_CLIENT, 'id', 'email=' . DB::str($email));
  if ($client)
   return self::actionError('Person with this E-mail address is already registered');
  $client = WClient::createClientForMaster($firstname, $lastname, $email);
  if (!$client)
   return self::actionFailDBInsert();
  if (!$db->insertValues('com_master', array('centre_id' => WCentre::id(), 'client_id' => $client)))
   return self::actionFailDBInsert();
  $mtrId = $db->insert_id;
  $db->modifySerialAfterInsert('com_master');
  PageCom::addToAjax('uri', 'ctr-' . WCentre::id() . '/mtr-' . $mtrId . '/', true);
  return true;
 }
}

?>
