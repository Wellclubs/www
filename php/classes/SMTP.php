<?php

/**
 * Class SMTP incapsulates a set of methods for sending messages via SMTP
 */
class SMTP
{
 const ERROR_SEND = 'Error sending a message via E-mail';

 private static $error;
 private static $dialog;
 private static $socket;

 public static function error() { return self::$error; }
 public static function dialog() { return self::$dialog; }

 /**
  * Send an message via E-mail
  * @param string $name Name of the recipient
  * @param string $address E-mail address of the recipient
  * @param string $subject Subject of the message
  * @param string $message Text of the message
  * @param array $headers Additional headers as assoc_array
  * @return bool result of the operation
  */
 public static function send($name, $address, $subject, $message, $headers = null)
 {
  //echo Base::htmlComment("Send to $name <$address> $subject", true);
  self::$error = '';
  self::$dialog = '';

  $host = '173.254.28.53';
  $port = 26;
  self::$socket = fsockopen($host, $port);
  if (!self::$socket)
   return self::raise("Error connecting to $host:$port");

  $domain = 'wellclubs.com';
  $user = 'support@wellclubs.com';
  $pass = 'P@ssw0rd';
  $from = "Wellclubs User Support <$user>";
  $to = "$name <$address>";

  $data =
    "To: $to\r\n" .
    "From: $from\r\n" .
    "Subject: $subject\r\n" .
    "MIME-Version: 1.0\r\n" .
    "Content-type: text/html; charset=utf-8\r\n";

  if ($headers && is_array($headers))
  {
   foreach ($headers as $key => $value)
    $data .= "$key: $value\r\n";
  }

  $data .=
    "\r\n" .
    "\r\n" .
    "$message" .
    "\r\n" .
    "." .
    "\r\n";

  $result =
    self::ask("EHLO $domain", 1024, 220) &&
    self::ask("AUTH LOGIN", 1024, 220) &&
    self::ask(base64_encode($user), 1024, 220) &&
    self::ask(base64_encode($pass), 256, 250) &&
    self::ask("MAIL FROM: $from", 1024, 250) &&
    self::ask("RCPT TO: $to", 1024, 250) &&
    self::ask("DATA", 1024, 250) &&
    self::ask($data, 256, 250) &&
    self::ask("QUIT", 0, 0);

  self::close();

  //echo Base::htmlComment("Result: " . print_r($result, true), true);
  return $result;
 }

 private static function ask($cmd, $size, $code)
 {
  fputs(self::$socket, "$cmd\r\n");
  if ($size)
  {
   $answer = fgets(self::$socket, $size);
   $received = intval(substr($answer, 0, 3));
   if ($received != $code)
   {
    self::$dialog[] = array($cmd, $answer);
    $error = "Invalid response code received: $received ($code waited)\n";
    $error .= "Request: $cmd\n";
    $error .= "Response: $answer\n";
    return self::raise($error);
   }
  }
  return true;
 }

 private static function raise($error)
 {
  $file = __FILE__;
  self::$error = "Error in module $file: $error";
  self::close();
  return false;
 }

 private static function close()
 {
  if (!self::$socket)
   return;
  fclose(self::$socket);
  self::$socket = null;
 }
}

?>
