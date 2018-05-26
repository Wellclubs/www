<?php

/**
 * Description of WMessage
 */
class WMessage
{
 const TABLE_MAIL = 'biz_mail';

 public static function getMessagesForClient()
 {
  $fields = 'id,sent,dir,subject';
  $where = 'client_id=' . WClient::id() . ' and hidden is null';
  $order = 'id desc';
  $msgs = DB::getDB()->queryArrays(self::TABLE_MAIL, $fields, $where, $order);
  return $msgs ? $msgs : array();
 }

 public static function actionSendFromClient()
 {
  if (!WClient::id())
   return array('fail', 'Client is not registered');
  if (!array_key_exists('subject', $_REQUEST))
   return array('fail', 'No parameter "subject" sent');
  if (!array_key_exists('message', $_REQUEST))
   return array('fail', 'No parameter "message" sent');
  $subject = base64_decode($_REQUEST['subject']);
  if (!strlen($subject))
   return array('fail', 'No parameter "subject" value set');
  $message = base64_decode($_REQUEST['message']);
  if (!strlen($message))
   return array('fail', 'No parameter "message" value set');
  $db = DB::getAdminDB();
  $values = array
  (
   'client_id' => WClient::id()
   , 'dir' => '\'o\''
   , 'subject' => DB::str($subject)
   , 'message' => DB::str($message)
  );
  if (Base::par() == 'msg')
   $values['src_id'] = Base::parIndex();
  if (!$db->insertValues(self::TABLE_MAIL, $values))
   return array('faildb', 'Error adding new record to DB table');
  $msgid = $db->insert_id;
  $subject = 'WC client ' . WClient::id() . ', ' . WClient::name() . '. ' . $subject;
  $message = 'Hi ' . SUPPORT_NAME . '! This message is available on ' .
    Base::bas() . 'adm/msg-' . $msgid . "/\n<br><br>\n" . $message;
  SMTP::send(SUPPORT_NAME, SUPPORT_EMAIL, $subject, $message);
  return array($msgid);
 }

 public static function actionSendToClient()
 {
  if (!array_key_exists('client', $_REQUEST))
   return array('fail', 'No parameter "client" sent');
  if (!array_key_exists('subject', $_REQUEST))
   return array('fail', 'No parameter "subject" sent');
  if (!array_key_exists('message', $_REQUEST))
   return array('fail', 'No parameter "message" sent');
  $client = intval($_REQUEST['client']);
  $Client = new WClient($client);
  if (!$Client->getId())
   return array('fail', 'Invalid parameter "client" value set ' . $client);
  $subject = base64_decode($_REQUEST['subject']);
  if (!strlen($subject))
   return array('fail', 'No parameter "subject" value set');
  $message = base64_decode($_REQUEST['message']);
  if (!strlen($message))
   return array('fail', 'No parameter "message" value set');
  /*$db = DB::getAdminDB();
  $values = array
  (
   'client_id' => $Client->getId()
   , 'dir' => '\'i\''
   , 'subject' => DB::str($subject)
   , 'message' => DB::str($message)
  );
  if (array_key_exists('src', $_REQUEST))
   $values['src_id'] = $_REQUEST['src'];
  if (!$db->insertValues(self::TABLE_MAIL, $values))
   return array('faildb', 'Error adding new record to DB table');
  $msgid = $db->insert_id;*/
  $msgid = self::sendToClient($Client->getId(), $subject, $message, HTTP::prm('src'));
  if (!$msgid)
   return array('faildb', 'Error adding new record to DB table');
  //$subject = 'Wellclubs. ' . $subject;
  $message = 'Hi ' . $Client->getName() . '! This message is available on ' .
    Base::bas() . 'com/msg-' . $msgid . "/\n<br><br>\n" . $message;
  SMTP::send($Client->getName(), $Client->getEmail(), $subject, $message);
  return array($msgid);
 }

 public static function sendToClient($clientId, $subject, $message, $sourceId = null)
 {
  $db = DB::getAdminDB();
  $values = array
  (
   'client_id' => intval($clientId)
   , 'dir' => '\'i\''
   , 'subject' => DB::str($subject)
   , 'message' => DB::str($message)
  );
  if ($sourceId)
   $values['src_id'] = intval($sourceId);
  if (!$db->insertValues(self::TABLE_MAIL, $values))
   return null;
  return $db->insert_id;
 }

  public static function actionDeleteByClient($msgid)
 {
  $db = DB::getAdminDB();
  $where = array('id' => $msgid);
  if (intval($db->queryField(self::TABLE_MAIL, 'client_id', $where)) != WClient::id())
   return array('fail', 'Message ' . $msgid . ' not found');
  if (!$db->modifyFields(self::TABLE_MAIL, array('hidden' => '1'), $where))
   return array('faildb', 'Error deleting the message record from a DB table');
  return array(true);
 }
}

?>
