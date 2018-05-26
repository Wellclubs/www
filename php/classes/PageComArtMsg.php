<?php

/**
 * Description of PageComArtMsg
 */
class PageComArtMsg extends PageComArt
{
 private $sent;
 private $incoming;
 private $subject;
 private $message;

 public function __construct($id)
 {
  parent::__construct($id);
  $this->useParIndex = true;
 }

 public function testPrivs()
 {
  return intval(PageCom::db()->queryField('biz_mail', 'client_id', 'id=' . Base::parIndex())) === WClient::id();
 }

 public function init()
 {
  $data = PageCom::db()->queryFields('biz_mail', 'sent,dir,subject,message', 'id=' . Base::parIndex());
  $this->sent = $data[0];
  $this->incoming = ($data[1] == 'i');
  $this->subject = $data[2];
  $this->message = $data[3];
 }

 protected function getParTitle()
 {
  return Lang::getObjTitle($this->incoming ? 'Incoming message' : 'Outgoing message') . ' "' . htmlspecialchars($this->subject) . '"';
 }

 protected function putArtBody()
 {
  echo "<div class=\"subject\" style=\"display:none\"></div>\n";

  echo "<div class=\"block action\">\n";
  echo "<a href=\"msgs/\" class=\"ax button left\">" . Lang::getPageWord('button', 'Go to messages') . "</a>\n";
  echo "<div class=\"button right\" action=\"answer\">" . Lang::getPageWord('button', 'Answer this message') . "</div>\n";
  echo "</div>\n";

  echo "<div class=\"viewer\"><div class=\"text html\"></div></div>\n";
  echo "<div class=\"block action\"><div class=\"button right\" action=\"delete\">" . Lang::getPageWord('button', 'Delete this message') . "</div></div>\n";
 }

 public function ajax()
 {
  PageCom::addDataToAjax(array
  (
   'incoming' => $this->incoming
  ,'subject' => base64_encode($this->subject)
  ,'message' => base64_encode($this->message)
  ));
 }

 protected function processActionDelete()
 {
  if (!self::processResult(WMessage::actionDeleteByClient(Base::parIndex())))
   return false;
  PageCom::addToAjax('uri', 'msgs/', true);
  return true;
 }

}

?>
