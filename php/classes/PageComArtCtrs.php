<?php

/**
 * Description of PageComArtCtrs
 */
class PageComArtCtrs extends PageComArt
{
 const MAX_COUNT = 10;
 const MAX_HIDDEN = 3;

 private $bnds = null;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->title = 'Centres';
 }

 /**
  * Does the article have to be shown in menu (just now)
  * @return bool True if the article has to be shown in menu
  */
 public function showInMenu()
 {
  $ctrs = PageCom::ctrs();
  return $ctrs && (count($ctrs) > 1);
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be shown to the current client
  */
 public function testPrivs()
 {
  return true; ///< Every client sees only it's own information
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be edited by the current client
  */
 public function canBeEdited()
 {
  return true; ///< Every client sees only it's own information
 }

 public function initData()
 {
  $this->bnds = WClient::me()->brands(false);
 }

 public function putArtBody()
 {
  self::putSingleAction('create', 'Add a new centre', true, true);
  self::putBlocks('ctrs', 'Centre', 'There are currently no centres to view');
 }

 /*protected function putSideBody()
 {
  // Add a centre
  parent::putPaneBegin('btn-create');
  echo '<div class="button create">' . Lang::getPageWord('button','Add a new centre') . '</div>' . "\n";
  parent::putPaneEnd();
 }*/

 public function ajax()
 {
  parent::ajax();
  $data = array();
  // List of centres
  $ctrs = array();
  foreach (PageCom::ctrs() as $id => $ctr)
  {
   $bnd = $ctr['bnd'];
   $bndT = $ctr['bndT'];
   if ($bnd && !strlen($bndT) && array_key_exists($bnd, $this->bnds))
    $bndT = $this->bnds[$bnd]['name'];
   $item = array
   (
     'id' => $id,
     'name' => $ctr['name'],
     'bndId' => $bnd,
     'bndName' => $bndT,
     'addr' => $ctr['addr'],
     'owner' => $ctr['owner']
   );
   if (!$ctr['owner'])
   {
    $item['ownerId'] = $ctr['ownerId'];
    $item['ownerName'] = WClient::getClientName($ctr['ownerId']);
   }
   $ctrs[] = $item;
  }
  $data['ctrs'] = $ctrs;
  // Data storing
  PageCom::addDataToAjax($data);
 }

 protected function processActionCreate()
 {
  $name = HTTP::param('name');
  if (!strlen($name))
   return self::actionFail('No "name" parameter value set');
  $db = PageCom::db();
  if ($db->queryField(WCentre::TABLE_CENTRE, 'count(*)', 'member_id=' . WClient::id()) >= self::MAX_COUNT)
   return self::actionError('You already have a maximum quantity of centres');
  if ($db->queryField(WCentre::TABLE_CENTRE, 'count(*)', 'member_id=' . WClient::id() . ' and hidden is not null') >= self::MAX_HIDDEN)
   return self::actionError('You already have a maximum quantity of unverified centres');
  if ($db->queryField(WCentre::TABLE_CENTRE, 'count(*)', 'name=' . DB::str($name)))
   return self::actionError('This name is already used for another centre');
  if (!$db->insertValues(WCentre::TABLE_CENTRE, array('member_id' => WClient::id(), 'type_id' => 1,  'domain_id' => DB::str(WDomain::id()), 'name' => DB::str($name))))
   return self::actionFailDBInsert();
  $centreId = $db->insert_id;
  $db->modifySerialAfterInsert(WCentre::TABLE_CENTRE);
  if(!Lang::setDBTitlesOnly(WCentre::TABLE_CENTRE, array('centre_id' => $centreId), true))
   return self::actionFailDBInsert();
  PageCom::addToAjax('uri', 'ctr-' . $centreId . '/', true);
  return true;
 }
}

?>
