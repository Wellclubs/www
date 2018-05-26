<?php

/**
 * Description of PageComArtHome
 */
class PageComArtHome extends PageComArt
{
 public function __construct($id)
 {
  parent::__construct($id);
  $this->title = 'My business';
  $this->tabs = array
  (
   'def' => 'Alerts',
   'msgs' => 'Inbox',
   'ofrs' => 'Site Offers',
   'pmts' => 'Payments'
  );
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
  if (Base::tab() == 'ofrs')
   return false;
  return true; ///< Every client sees only it's own information
 }

 public function putTabBodyDef()
 {
  echo '<div class="ui-widget-content">There will be some alerts in this window</div>';
 }

 protected function putTabBodyMsgs()
 {
  self::putMultiActions(array
  (
   array('action' => 'newmsg', 'title' => 'New message')
  ), true);
  self::putBlocks('msgs', 'Message', 'There are currently no messages to view');
 }

 protected function ajaxTabMsgs()
 {
  $msgs = WMessage::getMessagesForClient();
  // Data storing
  PageCom::addDataToAjax(array('msgs' => $msgs));
 }

 protected function processActionTabMsgsDelete()
 {
  $msgid = HTTP::param('msgid');
  if (!strlen($msgid))
   return self::actionFail('No "msgid" parameter value set');
  return self::processResult(WMessage::actionDeleteByClient($msgid));
 }

 protected function putTabBodyOfrs()
 {
  self::putBlocks('ofrs', 'offer', 'No site offers available');
 }

 protected function ajaxTabOfrs()
 {
  $ofrs = WOffer::getOffers(false, false);
  PageCom::addDataToAjax(array('ofrs' => $ofrs));
 }

 public function putTabBodyPmts()
 {
  $cols = array
  (
   array('width' => '10%', 'text' => 'Date', 'field' => 'date'),
   array('width' => '20%', 'text' => 'Offer', 'field' => 'offer'),
   array('width' => '20%', 'text' => 'Centre', 'field' => 'centre'),
   array('width' => '20%', 'text' => 'Brand', 'field' => 'brand'),
   array('width' => '10%', 'text' => 'Start', 'field' => 'start_date'),
   array('width' => '10%', 'text' => 'Period', 'field' => 'days'),
   array('width' => '10%', 'text' => 'Price', 'field' => 'price')
  );
  $cols[] = array('width' => 20, 'class' => 'btn', 'action' => 'delete', 'object' => 'Payment');
  self::putTable($cols, 'There are no payments to view');
 }

 protected function ajaxTabPmts()
 {
  $pmts = array();
  $fields = 'id,pay_date,offer_title,centre_id,centre_name,brand_id,brand_name,' .
    'start_date,offer_days,offer_price,offer_currency_id';
  $where = 'member_id=' . WClient::id() . ' and hidden is null';
  $rows = DB::getDB()->queryRecords('com_payment', $fields, $where, 'id desc');
  if ($rows)
  {
   foreach ($rows as $row)
   {
    $pmts[] = array
    (
     'id' => $row[0]
    ,'date' => $row[1]
    ,'offer' => Util::strJS($row[2])
    ,'centre' => $row[3] ? array('id' => $row[3], 'title' => Util::strJS($row[4])) : ''
    ,'brand' => $row[5] ? array('id' => $row[5], 'title' => Util::strJS($row[6])) : ''
    ,'start_date' => $row[7]
    ,'days' => $row[8]
    ,'price' => $row[9] . ' ' . $row[10]
    );
   }
  }
  // Data storing
  PageCom::addDataToAjax(array('pmts' => $pmts));
 }

 protected function processActionTabPmtsDelete()
 {
  if (!self::canBeEdited())
   return self::actionErrorAccess();
  $rowid = HTTP::param('rowid');
  if (!strlen($rowid))
   return self::actionFail('No "rowid" parameter value set');
  PageCom::db()->modifyField('com_payment', 'hidden', 's', '1');
  return true;
 }
}

?>
