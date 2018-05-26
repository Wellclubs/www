<?php

/**
 * Description of PageAdmZero
 */
class PageAdmZero
{
 private static function putErrors()
 {
  echo JSON::encode(array('errors' => Base::errors()));
 }
 private static function defValues()
 {
  return array
  (
   'client_id' => '007',
   'email' => 'Natalie.Koroleva@gmail.com',//'gavriliuk@gmail.com',
   'first_name' => 'Natalya', //'Alexander', //WClient::name(),
   'last_name' => 'Koroleva',//'Gavriliuc', //WClient::name(),
   'phone' => '971566099838',//'380952175507',
   'address' => 'Villa 79, Sas Al Nakheel', // 'Chernyakhovskogo str. 12-V, apt 13',
   'state' => '',//'Odesskaya oblast',
   'city' => 'Abu Dhabi',//'Odessa',
   'country' => 'ARE',//'UKR', // http://userpage.chemie.fu-berlin.de/diverse/doc/ISO_3166.html
   'postal_code' => '',//'65009',
   'invoice' => 'test-007',
   'title' => 'Test payment',
   'amount' => '3.5',
   'discount' => '1.5',
   'currency' => 'AED', // http://www.xe.com/iso4217.php
   'return' => 'https://' . Base::host() . Base::url()
  );
 }
 private static function processAct($act)
 {
  switch ($act)
  {
  case 'queryInit' :
   print_r($_SERVER);
   if (XPaySrvc::instance()->initialize())
    echo 'OK';
   else
    self::putErrors();
   break;

  case 'queryPage' :
   $values = self::defValues();
   if (!XPaySrvc::instance()->createPage($values))
    self::putErrors();
   else
    echo '<a target="_blank" href="' . XPaySrvc::instance()->paymentURL() . '">' .
      htmlspecialchars(XPaySrvc::instance()->result()) . ' ' .
      XPaySrvc::instance()->pID() . '</a><br/>' .
      htmlspecialchars(XPaySrvc::instance()->paymentURL());
   break;

  case 'queryParse' :
   $values = self::defValues();
   if (!XPaySrvc::instance()->createPage($values))
   {
    self::putErrors();
    break;
   }
   //$uri = Base::loc();
   $uri = XPaySrvc::instance()->paymentURL();
   echo $uri . '<hr/>';
   //$html = HTTP::ssl(urlencode(XPaySrvc::instance()->paymentURL()));
   $html = HTTP::ssl($uri);
   echo htmlspecialchars($html);
   // http://php.net/manual/en/function.simplexml-load-string.php
   $xml = simplexml_load_string($html);
   break;

  default :
   echo "Unsupported action: '$act'";
  }

  return true;
 }

 public static function showPage()
 {
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
</style>
<script>
function result(text)
{
 el('memo-result').innerHTML=text;
}
function query(btn)
{
 result('');
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=query'+btn.value,false);
 document.body.style.cursor='pointer !important';
 req.send(null);
 document.body.style.cursor='auto';
 if (req.status!=200)
  return alert('Error executing the request on the server: status '+req.status);
 if(!req.responseText.length)
  return alert('No response');
 //if((req.responseText!='OK')&&('<{['.indexOf(req.responseText[0])<0))
 // alert('Invalid response:\n'+req.responseText);
 result(req.responseText);
 return true;
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>

<input type="button" value="Clear" onclick="result('')"/>
<input type="button" value="Init" onclick="query(this)"/>
<input type="button" value="Page" onclick="query(this)"/>

<pre id="memo-result"><?php
$text = "Первая строка\nВторая строка";
print_r($text);
echo "<hr/>\n";
print_r(json_encode($text));
echo "<hr/>\n";
print_r(JSON::encode($text));
//print_r(WSGrp::filterGroups(array(2)));
//print_r($_SERVER);
?></pre>


</body>
</html>
<?php
  return true;
 }
}
?>
