<?php

/**
 * Description of PageComArtClt
 */
class PageComArtClt extends PageComArt
{
 private $client = null;
 private $member = null;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->useParIndex = true;
  $this->tabs = array
  (
   'def' => 'General'
  ,'book' => 'Bookings'
  ,'cmnt' => 'Comments'
  ,'ctrs' => 'Centres'
  ,'bnds' => 'Brands'
  );
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be shown to the current client
  */
 public function testPrivs()
 {
  return true;
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be edited by the current client
  */
 public function canBeEdited()
 {
  return false;
 }

 /**
  * Common article initialization (must be as quick as possible)
  */
 public function init()
 {
  parent::init();
 }

 /**
  * Full article initialization (is being called for active article only)
  */
 public function initData()
 {
  $this->member = new WMember(Base::parIndex());
  $this->client = $this->member->getClient();
  if (!$this->client)
  {
   $this->client = new WClient(Base::parIndex());
   $this->member = null;
  }
 }

 protected function getParTitle()
 {
  return Lang::getObjTitle('Client') . ' "' . $this->client->getName() . '"';
 }

 protected function putTabBodyDef()
 {
 }

 protected function ajaxTabDef()
 {
 }

 protected function putTabBodyBook()
 {
  echo '<div class="block view-all"><a class="ax simple" href="clt-' . Base::parIndex() . '/book/">' .
    htmlspecialchars(Lang::getPageWord('title', 'View bookings for all your centres')) .
    '</a></div>' . "\n";
  self::putBlocks('bookings', 'Booking', 'There are currently no bookings to view');
 }

 protected function ajaxTabBook()
 {
 }

 protected function putTabBodyCmnt()
 {
  echo '<div class="block view-all"><a class="ax simple" href="clt-' . Base::parIndex() . '/cmnt/">' .
    htmlspecialchars(Lang::getPageWord('title', 'View comments for all your centres')) .
    '</a></div>' . "\n";
  self::putBlocks('comments', 'Comment', 'There are currently no comments to view');
 }

 protected function ajaxTabCmnt()
 {
  $texts = array();
  $ctrs = PageCom::centreIdsForViewClients();
  if (count($ctrs))
  {
   $where = ' where client_id=' . Base::parIndex();
   $query1 = 'select 1 kind,id,centre_id,written,verified f1,signaled f2,text from com_review' . $where;
   $query2 = 'select 2,review_id,(select centre_id from com_review where id=a.review_id),written,violation,falsehood,text from com_review_cavil a' . $where;
   $query3 = 'select 3,id,(select centre_id from com_review where id=a.review_id),written,verified,signaled,text from com_review_comment a' . $where;
   $query4 = 'select 4,comment_id,(select a.centre_id from com_review a,com_review_comment b where a.id=b.review_id and b.id=c.comment_id),written,violation,falsehood,text from com_review_comment_cavil c' . $where;
   $query = '(' . $query1 . ' union all ' . $query2 . ' union all ' . $query3 . ' union all ' . $query4 . ') a';
   $fields = 'kind,id,centre_id,written,f1,f2,text';
   $where = (count($ctrs) == 1) ?
     ('centre_id=' . $ctrs[0]) : ('centre_id in (' . implode(',', $ctrs) . ')');
   $order = 'written desc,kind,id desc';
   $data = DB::getDB()->queryArrays($query, $fields, $where, $order);
   //$query = DB::lastQuery();
   if ($data)
   {
    foreach ($data as $row)
    {
     $id = $row['id'];
     $kind = $row['kind'];
     if (($kind == '1') || ($kind == '2'))
      $row['mark'] = 'review-' . $id;
     else if (($kind == '3') || ($kind == '4'))
      $row['mark'] = 'comment-' . $id;
     if (Base::mode() != 'ctr')
      $row['centre_name'] = WCentre::getTitle($row['centre_id']);
     $texts[] = $row;
    }
   }
  }
  // Data storing
  PageCom::addDataToAjax(array('texts' => $texts/*, 'query' => $query*/));
 }

 protected function putTabBodyCtrs()
 {
  self::putBlocks('ctrs', 'Centre', 'There are currently no centres to view');
 }

 protected function ajaxTabCtrs()
 {
 }

 protected function putTabBodyBnds()
 {
  self::putBlocks('bnds', 'Brand', 'There are currently no brands to view');
 }

 protected function ajaxTabBnds()
 {
 }
}

?>
