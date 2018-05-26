<?php

abstract class XPaySrvc
{
 private static $instance;
 public static function instance()
 {
  if (!self::$instance)
   self::$instance = new XPaySrvcPaytabs();
  return self::$instance;
 }

 protected function error($text)
 {
  return Base::addError(get_class($this) . ': ' . $text);
 }

 protected $api_key, $p_id, $payment_url, $result, $response;

 public function pID() { return $this->p_id; }
 public function result() { return $this->result; }
 public function paymentURL() { return $this->payment_url; }

 public abstract function initialize();
 public abstract function createPage($values);

 public static function getIPAddress($server)
 {
  $result = $_SERVER[$server ? 'SERVER_ADDR' : 'REMOTE_ADDR'];
  return ($result == '::1') ? '127.0.0.1' : $result;
 }

}

?>
