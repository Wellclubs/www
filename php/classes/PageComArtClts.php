<?php

/**
 * Description of PageComArtClts
 */
class PageComArtClts extends PageComArt
{
 private $clts = null;
 private $ctrs = null;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->title = 'Clients';
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be shown to the current client
  */
 public function testPrivs()
 {
  return PageCom::testPriv(WPriv::PRIV_VIEW_CLT_LIST);
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be edited by the current client
  */
 public function canBeEdited()
 {
  return PageCom::testPriv(WPriv::PRIV_EDIT_CLT_DATA);
 }

 public function initData()
 {
  $this->ctrs = PageCom::centreIdsForViewClients();
  if (!count($this->ctrs))
   return;
  $cnd = (count($this->ctrs) == 1) ?
    ('centre_id=' . $this->ctrs[0]) : ('centre_id in (' . implode(',', $this->ctrs) . ')');
  $table = WClient::TABLE_CLIENT;
  $fields = 'id,trim(concat(firstname,\' \',lastname)),gender,visited';
  $where = 'id in (select client_id from com_review where ' . $cnd . ')';
  $where .= ' or id in (select b.client_id from com_review a,com_review_cavil b' .
    ' where a.' . $cnd . ' and b.review_id=a.id)';
  $where .= ' or id in (select b.client_id from com_review a,com_review_comment b' .
    ' where a.' . $cnd . ' and b.review_id=a.id)';
  $where .= ' or id in (select c.client_id from com_review a,com_review_comment b,com_review_comment_cavil c' .
    ' where a.' . $cnd . ' and b.review_id=a.id and c.comment_id=b.id)';
  $order = 'visited desc';
  $rows = DB::getDB()->queryRecords($table, $fields, $where, $order);
  $this->clts = array();
  if ($rows)
  {
   foreach ($rows as $row)
    $this->clts[$row[0]] = array('name' => $row[1], 'gender' => $row[2], 'visited' => $row[3]);
  }
 }

 /*public static function mayViewForCentre(&$ctr)
 {
  if ($ctr['owner'])
   return true;
  $privs = $ctr['privs'];
  if (is_null($privs) || !is_array($privs) || !count($privs))
   return false;
  return array_key_exists(WPriv::PRIV_VIEW_CLT_LIST, $privs);
 }*/

 public function putArtBody()
 {
  /*$cols = array
  (
   array('width' => 300, 'text' => 'Name', 'field' => 'name', 'class' => 'uri ax', 'uri' => 'clt-uri')
  ,array('width' => 100, 'text' => 'Gender', 'field' => 'gender')
  ,array('width' => 200, 'text' => 'Last visited', 'field' => 'visited')
  );
  self::putTable($cols, 'There are currently no clients to view');*/
  self::putBlocks('ctrs', 'Clients', 'There are currently no clients to view');
 }

 public function ajax()
 {
  //$this->initData();
  $data = array();
  // List of clients
  $clts = array();
  foreach ($this->clts as $id => $clt)
   $clts[] = array
   (
     'id' => $id,
     'name' => $clt['name'],
     //'clt-uri' => 'clt-' . $id . '/',
     'gender' => $clt['gender'],
     'visited' => $clt['visited']
   );
  $data['clts'] = $clts;
  // Data storing
  PageCom::addDataToAjax($data);
 }
}

?>
