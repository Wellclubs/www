<?php

/**
 * Description of PageBookSrv
 */
class PageBookSrv
{
 private static $tip = null;
 private static $date = null;
 private static $day = null;

 public static function date()
 {
  if (self::$date === null)
  {
   self::$date = Util::str2date(HTTP::get('date'));
   if (self::$date == null)
   {
    self::$date = new DateTime();
    self::$date->setTime(0, 0);
    //self::$date->setDate(intval(self::$date->format('Y')), intval(self::$date->format('m')), intval(self::$date->format('d')) + 1);
    //date_modify($date, '+1 day');
    self::$date->modify('+1 day');
   }
   //echo "<!-- Here date = " . print_r(self::$date, true) . " -->\n";
  }
  return self::$date;
 }

 /*public static function day()
 {
  if (!self::$day)
   self::$day = self::date()->format('N');
  return self::$day;
 }*/

 public static function tip()
 {
  if (self::$tip === null)
  {
   $tip = Util::intval(HTTP::get('tip'));
   $where = 'srv_id=' . WService::id() . ' and duration>0 and price>0';
   $order = ($tip > 0) ? ('case when id=' . $tip . ' then 0 else 1 end') : '';
   self::$tip = Util::intval(DB::getDB()->queryField(WService::TABLE_TIP, 'id', $where, $order));
   if (self::$tip === null)
    self::$tip = 0;
  }
  return self::$tip;
 }

 /*public static function slot()
 {
  if (self::$slot === null)
   self::$slot = intval(HTTP::get('slot'));
  return self::$slot;
 }*/

 public static function fillResult(&$result)
 {
  $result['srv'] = WService::id();
  $result['srvT'] = WService::title();
  $result['date'] = Util::date2str(self::date());
  $result['tip'] = self::tip();
  $result['tips'] = self::tips();
  $result['ctr'] = WService::centreId();
  $result['ctrT'] = WCentre::getTitle(WService::centreId());
  if (strlen(WCentre::currencyId()))
   $result['curr'] = WCurrency::makeObjs(WCentre::currencyId());
  //$result['images'] = PageBookCtr::images(); // Gather all images togeter
  $result['ratings'] = self::ratings();
  $result['reviews'] = self::reviews();
  $result['descr'] = WService::descr();
  $result['restr'] = WService::restr();
  $result['notes'] = WService::notes();
 }

 public static function images()
 {
  return WService::createGallery()->getImagesBook();
 }

 /**
  * Get the service rating information
  * @return array: {facil:{count,total},distr:[5],cats:[{title,list:[{id,title,rsum,rcnt}],rated}]}
  */
 public static function ratings()
 {
  return WCentre::listRatings(WService::id());
 }

 /**
  * Get review list information
  * @return array: [{id,written,author,name,rateT,rateA,rateS,rateC,rateV,text,prcRates:[{id,rate}],comments:[id,written,author,name,text]}]
  */
 public static function reviews()
 {
  return WCentre::listReviews(WService::id());
 }

 private static function capacity($slot)
 {
  return WService::getCapacity(WCentre::id(), WService::id(), self::date(), $slot['a']);
 }

 private static function discount($slot)
 {
  return WService::discount(self::date(), $slot['a']);
 }

 private static function bookCount($slot)
 {
  return WService::bookCount(self::date(), $slot['a'], $slot['b'] - $slot['a'], WService::id());
 }

 private static function isSlotBooked($bookings, $slot)
 {
  $a = $slot['a'];
  $b = $slot['b'];
  $ad = $slot['d'];
  foreach ($bookings as $booking)
  {
   $t = $booking['book_time'];
   $d = $booking['book_dura'];
   //if (($a == 630) && ($t == 720))
   // echo "Here a=$a, b=$b, ad=$ad, t=$t, d=$d\n";
   if (($a < ($t + $d)) && (($a + $ad) > $t))
   {
    //if (($a == 630) && ($t == 720))
    // echo $a . " < " . ($t + $d) . " && " . ($a + $ad) . " > " . $t . "\n";
    return $booking;
   }
  }
  //if ($a == 600)
  // echo "Not found\n";
  return null;
 }

 public static function tips()
 {
  if (!array_key_exists('date', $_GET))
   return array();
  $timeSlots = WCentre::timeSlots(self::date());
  foreach ($timeSlots as $key => $timeSlot)
  {
   $timeSlots[$key]['aa'] = Util::min2str($timeSlot['a']); // Slot begin time
   //$timeSlots[$key]['bb'] = Util::min2str($timeSlot['b']); // Slot end time
   $timeSlots[$key]['dt'] = self::discount($timeSlot);
   $capacity = self::capacity($timeSlot);
   if ($capacity !== null)
   {
    //print_r($timeSlot);
    $bookCount = self::bookCount($timeSlot);
    //$bookCount = WService::bookCount(self::date(), $timeSlot['a'], $timeSlot['b'] - $timeSlot['a']);
    if ($bookCount)
     $capacity = ($bookCount < $capacity) ? ($capacity - $bookCount) : 0;
    $timeSlots[$key]['c'] = $capacity;
   }
  }
  $bookings = WClient::id() ? WService::getBookings(WClient::id(), null, null, self::date()) : array();
  $tips = WService::getTipList(WService::id());
  foreach ($tips as $key => $tip)
  {
   $dura = $tip['duration'];
   $tips[$key]['dura'] = Util::min2str($dura);
   //$tip['name'] = Lang::getDBTitle(WService::TABLE_TIP, 'tip', $tip['id'], $tip['name']);
   //$skip = 0;
   //$lastSlotIndex = null;
   $slots = array();
   foreach ($timeSlots as $timeSlot)
   {
    /*$use = ($skip == 0);
    $skip += WCentre::TIME_SLOT;
    if ($skip >= $dura)
     $skip = 0;
    if (!$use)
    {
     $slots[$lastSlotIndex]['b'] = $timeSlot['b'];
     $capacity = Util::item($timeSlot, 'c');
     if ($capacity !== null)
     {
      $capacityOld = Util::item($slots[$lastSlotIndex], 'c');
      if (($capacityOld === null) || ($capacityOld > $capacity))
       $slots[$lastSlotIndex]['c'] = $capacity;
     }
     continue;
    }*/
    $slot = array_merge($timeSlot);
    $slot['tip'] = $tip['id'];
    $slot['d'] = $dura;
    $slot['bb'] = Util::min2str($slot['a'] + $dura);
    $slot['p'] = $tip['price'];
    //$lastSlotIndex = count($slots);
    $slots[] = $slot;
   }
   $close = null;
   for ($i = count($slots) - 1; $i >= 0; --$i)
   {
    $slot = $slots[$i];
    if ($close == null)
     $close = $slot['b'];
    //
    $t = $close - $slot['a'];
    if ($t < $dura)
     $slots[$i]['x'] = 1;
    /*$t = $slot['b'] - $slot['a'];
    if ($t < $dura)
    {
     unset($slots[$i]);
     continue;
    }*/
    //
    $z = self::isSlotBooked($bookings, $slot);
    if ($z)
    {
     $z['a'] = Util::min2str($z['book_time']);
     $z['b'] = Util::min2str($z['book_time'] + $z['book_dura']);
     $z['c'] = WCentre::getTitle($z['centre_id']);
     $z['s'] = WService::getTitle($z['srv_id']);
     $slots[$i]['z'] = $z;
    }
    $discount = self::discount($slot);
    if ($discount)
    {
     $slots[$i]['p'] = WService::getPriceWithDisc($slot['p'], $discount);
     $slots[$i]['dt'] = $discount;
    }
   }
   $tips[$key]['slots'] = $slots;
  }
  return $tips;
 }

 /**
  * Add a new booking record to a database table
  * @return int Last inserted id
  */
 public static function book($pay)
 {
  if (!$pay)
  {
   $phone = HTTP::param('phone');
   if ($phone && $phone != WClient::phone())
   {
    $number = Util::pureNumber($phone);
    $values = array('phone' => DB::str($phone), 'number' => $number);
    DB::getAdminDB()->modifyFields(WClient::TABLE_CLIENT, $values, 'id=' . WClient::id());
   }
  }
  $ctrId = HTTP::paramInt('ctr');
  if (!$ctrId)
   return array('failure' => "No 'ctr' parameter value set");
  $srvId = HTTP::paramInt('srv');
  if (!$srvId)
   return array('failure' => "No 'srv' parameter value set");
  $tipId = HTTP::paramInt('tip');
  if (!$tipId)
   return array('failure' => "No 'tip' parameter value set");
  $date_ = HTTP::param('date');
  if (!$date_)
   return array('failure' => "No 'date' parameter value set");
  $date = Util::str2date($date_);
  if (!$date)
   return array('failure' => "Invalid 'date' parameter value set");
  $time = HTTP::paramInt('time');
  if (!$time)
   return array('failure' => "No 'time' parameter value set");
  if ($time % 30)
   return array('failure' => "Invalid 'time' parameter value set");
  $dura = HTTP::paramInt('dura');
  if (!$dura)
   return array('failure' => "No 'dura' parameter value set");
  $type = $pay ? 'P' : 'B'; // 'B' - no payment, 'H' - hold, 'P' - payment
  //$type = HTTP::param('type');
  //if (!$type)
  // return array('failure' => "No 'type' parameter value set");
  if (array_search($type, array('P', 'H')))
   return array('failure' => "Invalid 'type' parameter value set");
  $price = HTTP::param('price');
  if (!$price)
   return array('failure' => "No 'price' parameter value set");
  $curr = HTTP::param('curr');
  if (!strlen($curr))
   return array('failure' => "No 'curr' parameter value set");
  $disc = intval(HTTP::get('disc'));
  $fact = HTTP::param('fact');
  if (!isset($fact))
   return array('failure' => "No 'fact' parameter value set");
  $qty = intval(HTTP::get('qty'));
  if ($qty < 1)
   $qty = 1;
  $total = HTTP::param('total');
  if (!isset($total))
   return array('failure' => "No 'total' parameter value set");
  $masterId = Util::item($_REQUEST, 'master');
  $matresId = Util::item($_REQUEST, 'matres');
  $tipRow = DB::getDB()->queryPairs(WService::TABLE_TIP, 'duration', 'id=' . $tipId . ' and duration>0 and price>0');
  if (!$tipRow)
   return array('failure' => "Invalid 'tip' parameter value");
  // $dura?
  if ($dura != $tipRow['duration'])
   return array('failure' => "Invalid 'dura' parameter value");
  $name = WClient::getClientNameAndEmail();
  $descr = HTTP::param('descr');

  $cmpCltId = intval(HTTP::get('cmp_clt_id'));
  $cmpMsgId = intval(HTTP::get('msg'));

  // book!
  $bookId = WService::book(WClient::id(), $name, WClient::phone(), $tipId, $srvId, $ctrId, $date, $time, $dura, $type, $price, $curr,
    $disc, $fact, $qty, $total, $masterId, $matresId, $descr, $cmpCltId, $cmpMsgId);
  if (!$bookId)
   return array('failure' => 'Error adding booking record: ' . DB::lastQuery());
  if ($bookId < 0)
  {
   $text = ($qty > 1) ?
     Lang::getWord('error', 'The number of bookings exceeds the maximum', 'pay') :
     Lang::getWord('error', 'There are no slots available', 'pay');
   return array('error' => $text);
  }
  $result = array('result' => 'OK');
  // payment!
  if ($pay)
  {
   $me = WClient::me();
   $title = $me->getTitle();
   $fname = $me->getFirstName();
   $sname = $me->getLastName();
   $email = $me->getEmail();
   $phone = $me->getPhone();
   $address = $me->getAddress();
   $city = $me->getCity();
   $region = $me->getRegion();
   $postcode = $me->getPostCode();
   $countryId = $me->getCountryId();
   $uri1 = Base::bas() . 'pay/?ref=' . WPurchase::encodeRefId($bookId);
   $uri2 = Base::bas() . "pay/?tip=$tipId&date=$date_&time=$time&qty=$qty";
   $uri3 = Base::bas() . 'srv-' . $srvId . '/?date=' . $date_;
   if (WService::getTipCount($srvId) > 1)
    $uri3 .= '&tip=' . $tipId;
   if ($qty > 1)
    $uri3 .= '&qty=' . $qty;
   $uri3 .= '&paystatus=c';
   // Make a payment using $bookId
   $pay = WPurchase::pay($bookId, $total, $curr, $descr, $title, $fname, $sname, $email,
     $phone, $address, $city, $region, $postcode, $countryId, $uri1, $uri2, $uri3);
   //$pay = WPurchase::payTest($uri);
   $result['pay'] = $pay;
  }
  else
  {
   $result['ref'] = WPurchase::encodeRefId($bookId);
  }

  if (!$pay || WDomain::local())
   $result = array_merge($result, WClient::sendBookNotice($bookId));
  return $result;
 }

 /**
  * Update a booking record in a database table
  * @return bool Success
  */
 public static function setPayStatus()
 {
  $status = HTTP::get('paystatus');
  if (array_search($status, array('a', 'd', 'c')) === false)
   return false;
  $bookId = HTTP::get('book');
  if (!is_numeric($bookId))
   return false;
  $db = DB::getAdminDB();
  $bookData = $db->queryFields(WPurchase::TABLE_BOOK, 'current_timestamp', 'id=' . $bookId);
  if (!$bookData)
   return false;
  $values = array('status' => DB::str($status), 'answered' => DB::str($bookData[0]));
  $db->modifyFields(WPurchase::TABLE_BOOK, $values, 'id=' . $bookId);
  //echo DB::lastQuery();
  //echo "Here\n";
  $values['book_id'] = $bookId;
  $db->insertValues(WPurchase::TABLE_BOOK_LOG, $values);
  if ($status == 'a')
   WClient::sendBookNotice($bookId);
  return true;
 }

 /**
  * Remove a booking record from a database table
  */
 public static function unbook()
 {
  $time = HTTP::param('time', null, false);
  if (is_null($time))
   return array('failure' => "No 'id' parameter value set");
  // unbook!
  if (!WService::unbook(self::date(), $time))
   return array('failure' => 'Error deleting booking record: ' . DB::lastQuery());
  return array('result' => 'OK', 'tips' => self::tips());
 }
}
?>
