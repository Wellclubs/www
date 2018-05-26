<?php

/**
 * Description of PageComArtBnds
 */
class PageComArtBnds extends PageComArt
{
 private $bnds = null;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->title = 'Brands';
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
  $this->bnds = WClient::me()->brands(true);
 }

 public function putArtBody()
 {
  self::putSingleAction('create', 'Add a new brand', true, true);
  self::putBlocks('bnds', 'Brand', 'There are currently no brands to view');
 }

 public function ajax()
 {
  parent::ajax();
  $data = array();
  // List of brands
  $bnds = array();
  foreach ($this->bnds as $id => $bnd)
  {
   $bnds[] = array
   (
     'id' => $id,
     'name' => $bnd['name'],
     'email' => $bnd['email'],
     'uri' => $bnd['uri']
   );
  }
  $data['bnds'] = $bnds;
  // Data storing
  PageCom::addDataToAjax($data);
 }

 protected function processActionCreate()
 {
  $name = trim(HTTP::param('name'));
  if (!strlen($name))
   return self::actionFail('No "name" parameter value set');
  $uri = HTTP::get('uri');
  $db = PageCom::db();
  $brandLimit = WClient::me()->getMember()->getBrandLimit();
  if ($brandLimit >= 0)
   if (WClient::me()->getMember()->getBrandCount() >= $brandLimit)
    return self::actionError('You already have a maximum quantity of brands');
  if ($db->queryField(WBrand::TABLE_BRAND, 'count(*)', 'name=' . DB::str($name)))
   return self::actionError('This name is already used for another brand');
  $values = array('member_id' => WClient::id(), 'name' => DB::str($name));
  if (strlen($uri))
   $values['uri'] = DB::str($uri);
  if (!$db->insertValues(WBrand::TABLE_BRAND, $values))
   return self::actionFailDBInsert();
  $brandId = $db->insert_id;
  $db->modifySerialAfterInsert(WBrand::TABLE_BRAND);
  if (!Lang::setDBTitlesOnly(WBrand::TABLE_BRAND, array('brand_id' => $brandId), true))
   return self::actionFailDBInsert();
  PageCom::addToAjax('uri', 'bnd-' . $brandId . '/', true);
  return true;
 }
}

?>
