<?php

/**
 * Description of WPurchase
 */
class WPurchase
{
 const TABLE_BOOK = 'com_srv_book';
 const TABLE_BOOK_LOG = 'com_srv_book_log';

 public static function encodeRefId($id)
 {
  return 2011 * $id + (($id % 37) * ($id % 53));
 }

 public static function decodeRefId($refId)
 {
  $rest = $refId % 2011;
  $result = ($refId - $rest) / 2011;
  if ((($result % 37) * ($result % 53)) != $rest)
   return null;
  return $result;
 }

 public static function getDataFromHttpParams($full = null)
 {
  $result = array();
  $db = DB::getDB();
  $refData = null;
  $tipId = HTTP::paramInt('tip',0);
  if (!$tipId && WClient::id())
  {
   $refId = HTTP::paramInt('ref',0);
   if (!$refId)
    return null;
   $bookId = WPurchase::decodeRefId($refId);
   if (!$bookId)
    return null;
   $fields = 'tip_id,book_date,book_time,book_dura,book_type_id,qty,total,curr,cmp_clnt_id';
   $where = 'id=' . $bookId .
     ' and client_id=' . WClient::id() .
     ' and domain_id=' . WDomain::id();
   $refData = $db->queryPairs(self::TABLE_BOOK, $fields, $where);
   if (!$refData)
    return null;
   $tipId = $refData['tip_id'];
   $result['ref'] = $refId;
  }
  if (!$tipId)
   return null;
  if ($full)
  {
   $fields = 'centre_id,srv_id,duration,price';
   $tipData = $db->queryPairs(WService::TABLE_TIP, $fields, 'id=' . $tipId . ' and duration>0 and price>0');
   if (!$tipData)
    return null;
   // Centre
   $ctrId = intval($tipData['centre_id']);
   $result['ctr'] = $ctrId;
   $result['ctrT'] = WCentre::getTitle($ctrId);
   $result['ctrBookType'] = Util::nvls($db->queryField('com_centre', 'book_type_id', 'id=' . $ctrId), 'A');
   // Service
   $srvId = intval($tipData['srv_id']);
   $result['srv'] = $srvId;
   $result['srvT'] = WService::getTitle($srvId);
   // Price option
   $count = $db->queryField(WService::TABLE_TIP, 'count(*)', 'srv_id=' . $tipData['srv_id'] . ' and duration>0 and price>0');
   if ($count > 1)
   {
    $result['tip'] = $tipId;
    $result['tipT'] = WService::tipTitle($tipId);
   }
  }
  // Date
  $date = $refData ? DB::str2date($refData['book_date']) : Util::str2date(HTTP::get('date'));
  if (!$date)
   return null;
  $result['date'] = $date;
  if ($full)
   $result['dateT'] = Util::date2strDDMMMYY($date);
  // Time
  $time = Util::intval($refData ? $refData['book_time'] : HTTP::param('time'));
  if (($time == null) || ($time < 0) || ($time >= 1440))
   return null;
  $result['time'] = $time;
  if ($full)
   $result['timeT'] = Util::min2str($time);
  // Duration
  $dura = $full ? intval($refData ? $refData['book_dura'] : $tipData['duration']) : Util::intval(HTTP::param('dura'));
  if ($dura < 1)
   return null;
  $result['dura'] = $dura;
  $result['exit'] = $time + $dura;
  if ($full)
   $result['exitT'] = Util::min2str($time + $dura);
  // Type
  $result['type'] = $refData ? $refData['book_type_id'] : null;
  if ($full)
  {
   // Price
   $price = intval($tipData['price']);
   if ($price < 1)
    return null;
   $result['price'] = $price;
   // Discount
   if (WService::id() != $srvId)
    WService::initCurrent($srvId);
   $disc = WService::discount($date, $time);
   $result['disc'] = $disc;
   // Fact price
   $fact = WService::getPriceWithDisc($price, $disc);
   if ($fact < 1)
    return null;
   $result['fact'] = $fact;
   // Quantity
   $qty = intval($refData ? $refData['qty'] : HTTP::get('qty'));
   if ($qty < 1)
    $qty = 1;
   $result['qty'] = $qty;
   if ($full)
   {
    // Capacity
    if (WCentre::id() != $ctrId)
     WCentre::initCurrent ($ctrId);
    $capa = WCentre::capacity();
    if ($capa > 0)
    {
     $busy = WService::bookCount($date, $time, $dura, $srvId);
     if ($busy > 0)
      $capa -= $busy;
     $result['capa'] = $capa;
    }
   }
   // Total amount
   $result['total'] = $refData ? intval($refData['total']) : $fact * $qty;
   // Total discount
   $result['totalDiscount'] = ($price * $qty) - ($fact * $qty);
   // Currency
   if (strlen(WCentre::currencyId()))
    $result['curr'] = WCurrency::makeObjs(WCentre::currencyId());
   // HREF for 'Change' button
   $result['hrefChg'] = 'srv-' . $result['srv'] . '/?date=' . Util::date2str($result['date']) . Util::str(Util::item($result, 'tip'), '&tip=');
  }
  else
  {
   // Total amount
   $total = floatval($refData ? $refData['total'] : HTTP::param('total'));
   if ($total < 1)
    return null;
   $result['total'] = $total;
   // Currency
   $curr = $refData ? $refData['curr'] : HTTP::param('curr');
   if (strlen($curr) != 3)
    return null;
   if (strlen(WCentre::currencyId()))
    $result['curr'] = WCurrency::makeObjs($curr);
  }

  WDsc::addDscData($refData, $result, $db);

  $result['status'] = 'OK';
  // Return result
  return $result;
 }

 private static function signData($post_data, $secretKey, $fieldList)
 {
  $signatureParams = explode(',', $fieldList);
  $signatureString = $secretKey;
  foreach ($signatureParams as $param)
  {
   $signatureString .= ':';
   if (array_key_exists($param, $post_data))
    $signatureString .= trim($post_data[$param]);
  }
  return sha1($signatureString);
 }

 public static function pay($bookId, $amount, $curr, $desc, $title, $fname, $sname, $email,
   $phone, $address, $city, $region, $postcode, $countryId, $uri1, $uri2, $uri3)
 {
  if ($curr != 'AED')
   exit('Invalid currency: ' . $curr);
  // Build up the parameters needed by the gateway
  $post_data = array
  (
   'ivp_store' => '14485',
   'ivp_cart' => '' . $bookId,
   'ivp_amount' => $amount . '.00',
   'ivp_currency' => $curr,
   'ivp_test' => (PAY_TEST_MODE == '0' ? '0' : '1'),
   'ivp_timestamp' => '0',
   'ivp_desc' => $desc,
   'ivp_extra' => 'bill,return',
   'bill_title' => $title,
   'bill_fname' => $fname,
   'bill_sname' => $sname,
   'bill_addr1' => $address,
   //'bill_addr2' => '',
   //'bill_addr3' => '',
   'bill_city' => $city,
   'bill_region' => $region,
   'bill_zip' => $postcode,
   'bill_country' => $countryId,
   'bill_email' => $email,
   'bill_phone1' => $phone,
   'return_cb_auth' => WDomain::local() ? 'none' : (Base::bas() . 'pay/cb?paystatus=a&book=' . $bookId),
   'return_cb_decl' => WDomain::local() ? 'none' : (Base::bas() . 'pay/cb?paystatus=d&book=' . $bookId),
   'return_cb_can' => WDomain::local() ? 'none' : (Base::bas() . 'pay/cb?paystatus=c&book=' . $bookId),
   'return_auth' => 'auto:' . $uri1,
   'return_decl' => $uri2,
   'return_can' => 'auto:' . $uri3,
  );
  $secret_key = 'NXMm4$X9'; // This must never be shown as part of the HTML
  // First create the signature for the main purchase details, as this used both to authenticate
  // the request and in creating the other signatures.
  $post_data['ivp_signature'] = self::signData($post_data, $secret_key,
   'ivp_store,ivp_amount,ivp_currency,ivp_test,ivp_timestamp,ivp_cart,ivp_desc,ivp_extra');
  // Now create the signature for the billing details (uses the ivp_signature created first)
  $post_data['bill_signature'] = self::signData($post_data, $secret_key,
   'bill_title,bill_fname,bill_sname,bill_addr1,bill_addr2,bill_addr3,bill_city,bill_region,'.
   'bill_country,bill_zip,ivp_signature');
  // Now create the signature for the return/call-back URLs (also uses the ivp_signature)
  $post_data['return_signature'] = self::signData($post_data, $secret_key,
   'return_cb_auth,return_cb_decl,return_cb_can,'.
   'return_auth,return_decl,return_can,ivp_signature');

  return array('uri' => 'https://secure.innovatepayments.com/gateway/index.html', 'data' => $post_data);
  //$url = 'https://secure.innovatepayments.com/gateway/index.html';
  //$result = HTTP::ssl($url, $post_data, true);
  //return $result;
 }

 /*function SignData($post_data,$secretKey,$fieldList) {
  $signatureParams = explode(',', $fieldList);
  $signatureString = $secretKey;
  foreach ($signatureParams as $param) {
   if (array_key_exists($param, $post_data)) {
    $signatureString .= ':' . trim($post_data[$param]);
   } else {
    $signatureString .= ':';
   }
  }
  return sha1($signatureString);
 }*/

 public static function payTest($uri)
 {
  // Get the main part of the current URI
  //$uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  $pos = strpos($uri, '#');
  if ($pos !== false)
   $uri = substr($uri, 0, $pos);
  if (strpos($uri, '?') !== false)
   $uri .= '&';
  else
  {
   $uri .= '?';
  }

  // Build up the parameters needed by the gateway
  $post_data = Array (
   'ivp_store' => '14485',
   'ivp_cart' => 'Cart123',
   'ivp_amount' => '299.00',
   'ivp_currency' => 'AED',
   'ivp_test' => '1',
   'ivp_timestamp' => '0',
   'ivp_desc' => 'Test Purchase',
   //'ivp_extra' => 'return',
   'ivp_extra' => 'bill,return',
   //'bill_title' => 'Mr',
   'bill_fname' => 'Test',
   'bill_sname' => 'Customer',
   'bill_addr1' => 'Address',
   //'bill_addr2' => 'Street name',
   //'bill_addr3' => 'Town',
   'bill_city' => 'City',
   //'bill_region' => 'Region',
   //'bill_zip' => 'PostCode',
   'bill_country' => 'AE',
   'bill_email' => 'support@wellclubs.com',
   //'bill_phone1' => '04 123 4567',
   'return_cb_auth' => 'none',
   'return_cb_decl' => 'none',
   'return_cb_can' => 'none',
  // 'return_auth' => 'auto:https://wellclubs.com/telr.php?status=auth',
  // 'return_decl' => 'auto:https://wellclubs.com/telr.php?status=decl',
  // 'return_can' => 'auto:https://wellclubs.com/telr.php?status=can',
   'return_auth' => "auto:http://{$uri}status=auth",
   'return_decl' => "auto:http://{$uri}status=decl",
   'return_can' => "auto:http://{$uri}status=can",
  );
  $secret_key='NXMm4$X9'; // This must never be shown as part of the HTML
  // First create the signature for the main purchase details, as this used both to authenticate
  // the request and in creating the other signatures.
  $post_data['ivp_signature']=self::signData($post_data,$secret_key,
   'ivp_store,ivp_amount,ivp_currency,ivp_test,ivp_timestamp,ivp_cart,ivp_desc,ivp_extra');
  // Now create the signature for the billing details (uses the ivp_signature created first)
  $post_data['bill_signature']=self::signData($post_data,$secret_key,
   'bill_title,bill_fname,bill_sname,bill_addr1,bill_addr2,bill_addr3,bill_city,bill_region,'.
   'bill_country,bill_zip,ivp_signature');
  // Now create the signature for the return/call-back URLs (also uses the ivp_signature)
  $post_data['return_signature']=self::signData($post_data,$secret_key,
   'return_cb_auth,return_cb_decl,return_cb_can,'.
   'return_auth,return_decl,return_can,ivp_signature');

  return array('uri' => 'https://secure.innovatepayments.com/gateway/index.html', 'data' => $post_data);
 }

}

?>
