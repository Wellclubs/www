<?php

/**
 * Description of WCentre
 */
class WCentre
{
 const TABLE_CENTRE = 'com_centre';
 const TABLE_CENTRE_IMG = 'com_centre_img';
 const TABLE_CENTRE_METRO = 'com_centre_metro';
 const TABLE_CENTRE_PLACE = 'com_centre_place';
 const TABLE_CENTRE_PHONE = 'com_centre_phone';
 const TABLE_CENTRE_SCHED = 'com_centre_sched';

 const CENTRE_TYPE_SALON = 1;

 const PRICE_TYPE_FIXED = 0;
 const PRICE_TYPE_FROM = 1;
 const PRICE_TYPE_RANGE = 2;

 const LOGO_WIDTH = 300;

 const TIME_SLOT = 30;

 private static $id = null;
 private static $member_id = null;
 private static $brand_id = null;
 private static $type_id = null;
 private static $domain_id = null;
 private static $name = null;
 private static $email = null;
 private static $serial = null;
 private static $hidden = null;
 private static $address = null;
 private static $lat = null;
 private static $lng = null;
 private static $file = null;
 private static $rate_prc = null;
 private static $rate_count = null;
 private static $currency_id = null;
 private static $currency_code = null;
 private static $capacity = null;
 private static $is_hotel = null;
 private static $tax_prc = null;
 private static $com_prc = null;
 private static $book_type = null;

 private static $title = null;
 private static $descr = null;

 private static $type_title = null;
 private static $domain_name = null;

 private static $master_id = null;

 public static function id() { return self::$id; }
 public static function memberId() { return self::$member_id; }
 public static function brandId() { return self::$brand_id; }
 public static function typeId() { return self::$type_id; }
 public static function domainId() { return self::$domain_id; }
 public static function name() { return self::$name; }
 public static function email() { return self::$email; }
 public static function serial() { return self::$serial; }
 public static function hidden() { return self::$hidden; }
 public static function address() { return self::$address; }
 public static function lat() { return self::$lat; }
 public static function lng() { return self::$lng; }
 public static function file() { return self::$file; }
 public static function rateTotal() { return self::$rate_prc / 100; }
 public static function rateCount() { return self::$rate_count; }
 public static function currencyId() { return self::$currency_id; }
 public static function currencyCode() { return self::$currency_code; }
 public static function capacity() { return self::$capacity; }
 public static function isHotel() { return self::$is_hotel != null; }
 public static function taxPrc() { return self::$tax_prc; }
 public static function comPrc() { return self::$com_prc; }
 public static function bookType() { return self::$book_type; }

 public static function currencyIdSafe() { return Util::nvl(self::$currency_id, WDomain::currencyId()); }
 public static function currencyCodeSafe() { return Util::nvl(self::$currency_code, self::$currency_id); }
 public static function currency() { return Util::nvl(self::currencyCodeSafe(), WDomain::currencyCodeSafe()); }

 public static function title() { return self::$title; }
 public static function descr() { return self::$descr; }

 public static function typeTitle() { return self::$type_title; }
 public static function domainName() { return self::$domain_name; }

 public static function masterId() { return self::$master_id; }

 public static function initCurrent($id, $adm = false)
 {
  if ($id == null)
   return false;
  if ($id == self::$id)
   return true;

  self::$id = null;

  $fields = 'member_id,brand_id,type_id,domain_id,name,email,serial,hidden' .
    ',address,lat,lng,file,rate_prc,rate_count,currency_id,capacity,is_hotel,tax_prc,com_prc,book_type_id';
  $where = "id=$id" . ($adm ? '' : ' and hidden is null');
  $values = DB::getDB()->queryPairs(self::TABLE_CENTRE, $fields, $where);
  if (!$values)
   return false;

  self::$id = $id;
  self::$member_id = Util::intval($values['member_id']);
  self::$brand_id = Util::intval($values['brand_id']);
  self::$type_id = Util::intval($values['type_id']);
  self::$domain_id = $values['domain_id'];
  self::$name = $values['name'];
  self::$email = $values['email'];
  self::$serial = Util::intval($values['serial']);
  self::$hidden = !!$values['hidden'];
  self::$address = '' . $values['address'];
  self::$lat = Util::intval(substr($values['lat'], 0, 8));
  self::$lng = Util::intval(substr($values['lng'], 0, 8));
  self::$file = $values['file'];
  self::$rate_prc = Util::intval($values['rate_prc']);
  self::$rate_count = Util::intval($values['rate_count']);
  self::$currency_id = $values['currency_id'];
  self::$capacity = Util::intval($values['capacity']);
  self::$is_hotel = Util::intval($values['is_hotel']);
  self::$tax_prc = Util::intval($values['tax_prc']);
  self::$com_prc = Util::intval($values['com_prc']);
  self::$book_type = $values['book_type_id'];

  self::$title = Lang::getDBValueDef(self::TABLE_CENTRE . '_abc', 'title', 'centre_id=' . $id, self::$name);
  self::$descr = Lang::getDBValue(self::TABLE_CENTRE . '_abc', 'descr', 'centre_id=' . $id);

  self::$type_title = Lang::getDBValue('art_centre_type_abc', 'title', 'type_id=' . self::$type_id);
  if (!self::$type_title)
   self::$type_title = DB::getDB()->queryField('art_centre_type', 'name', 'id=' . self::$type_id);

  if (self::$domain_id != '')
  {
   self::$domain_id = Util::intval(self::$domain_id);
   self::$domain_name = DB::getDB()->queryField(WDomain::TABLE_DOMAIN, 'name', 'id=' . self::$domain_id);
  }

  self::$currency_code = self::$currency_id ? WCurrency::getCode(self::$currency_id) : null;

  self::$master_id = DB::getDB()->queryField(WMaster::TABLE_MASTER, 'id', array('centre_id' => self::$id, 'client_id' => WClient::id(), 'can_connect' => 1));

  if (self::$brand_id)
   if (!WBrand::initCurrent(self::$brand_id))
    return false;

  return true;
 }

 public static function keywords($id = null)
 {
  if (!$id)
   $id = self::id();
  if (!$id)
   return null;
  return DB::getDB()->queryField(self::TABLE_CENTRE, 'keywords', 'id=' . $id);
 }

 /**
  * Get full centre title (with a brand title if exists)
  * @param int $id Centre id
  * @return string  Full title
  */
 public static function getTitle($id = null)
 {
  if (!$id)
   $id = self::id();
  if (!$id)
   return null;
  $centre = DB::getDB()->queryFields(self::TABLE_CENTRE, 'name,brand_id', 'id=' . $id);
  return self::getTitleForData($id, $centre[0], $centre[1]);
 }

 /**
  * Get full centre title (with a brand title if exists)
  * @param int $id Centre id
  * @param string $name Centre name
  * @param int $brandId Brand id
  * @return string Full title
  */
 public static function getTitleForData($id, $name, $brandId)
 {
  $result = Lang::getDBValueDef(self::TABLE_CENTRE . '_abc', null, 'centre_id=' . $id, $name);
  if (!strlen($result) && $brandId)
   $result = WBrand::getTitle($brandId, true);
  if (!strlen($result))
   $result = '... (' . Lang::getPageWord('text', 'no name') . ' ' . $id . ') ...';
  return $result;
 }

 public static function ownerName()
 {
  if (!self::$id)
   return null;
  return WClient::getClientName(self::$member_id);
 }

 public static function getCapacity($id = null)
 {
  if (!$id)
   return self::$capacity;
  return DB::getDB()->queryField(self::TABLE_CENTRE, 'capacity', 'id=' . $id);
 }

 public static function masterCount($id = null, $srvId = null)
 {
  if (!$id)
   $id = self::$id;
  if (!$id)
   return null;
  $where = 'centre_id=' . $id;
  if ($srvId)
   $where .= ' and for_service is not null' .
     ' and (all_services is not null or id in (select master_id from ' . WService::TABLE_SRV_MTR . ' where srv_id=' . $srvId . '))';
  return DB::getDB()->queryField('com_master', 'count(*)', $where);
 }

 public static function masters($id = null, $srvId = null)
 {
  if (!$id)
   $id = self::$id;
  if (!$id)
   return null;
  $where = 'centre_id=' . $id;
  if ($srvId)
   $where .= ' and for_service is not null' .
     ' and (all_services is not null or id in (select master_id from ' . WService::TABLE_SRV_MTR . ' where srv_id=' . $srvId . '))';
  $recs = DB::getDB()->queryRecords('com_master', 'id,client_id', $where, 'serial,id');
  $result = array();
  if ($recs)
  {
   foreach ($recs as $rec)
    $result[$rec[0]] = WClient::getClientName($rec[1]);
  }
  return $result;
 }

 public static function matcatCount()
 {
  if (!self::$id)
   return null;
  return DB::getDB()->queryField('com_matcat', 'count(*)', 'centre_id=' . self::$id);
 }

 public static function matresCount()
 {
  if (!self::$id)
   return null;
  return DB::getDB()->queryField('com_matres', 'count(*)', 'matcat_id in (select id from com_matcat where centre_id=' . self::$id . ')');
 }

 public static function serviceCount()
 {
  if (!self::$id)
   return null;
  return DB::getDB()->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id=' . self::$id);
 }

 public static function reviewCount()
 {
  if (!self::$id)
   return null;
  return DB::getDB()->queryField('com_review', 'count(*)', 'centre_id=' . self::$id . ' and signaled is null');
 }

 public static function listGroups()
 {
  if (!self::$id)
   return null;
  $groups = DB::getDB()->queryRecords('com_menu_grp', 'id,name', 'centre_id=' . self::$id, 'serial');
  if (!$groups)
   return null;
  $result = array();
  foreach ($groups as $group)
  {
   $id = $group[0];
   $title = Lang::getDBValue('com_menu_grp_abc', 'title', 'grp_id=' . $id);
   if (!$title)
    $title = ucfirst($group[1]);
   $services = self::listServices($id);
   if (!$services)
    continue;
   $result[] = array('id' => $id, 'title' => $title, 'services' => $services);
  }
  return count($result) ? $result : null;
 }

 private static function listServices($grp_id)
 {
  $fields = 'id,name,limited';
  $services = DB::getDB()->queryRecords(WService::TABLE_SRV, $fields, 'grp_id=' . $grp_id, 'serial');
  if (!$services)
   return null;
  $result = array();
  foreach ($services as $service)
  {
   $id = $service[0];
   $title = Lang::getDBValueDef(WService::TABLE_SRV . '_abc', null, 'srv_id=' . $id, $service[1]);
   $limited = !!$service[2];
   $tips = self::listTips($id, $limited);
   if (!$tips)
    continue;
   $result[] = array('id' => $id, 'title' => $title, 'tips' => $tips);
  }
  return count($result) ? $result : null;
 }

 private static function listTips($srv_id, $limited)
 {
  $fields = 'id,level_id,duration,price_type_id,price,max_price,rest';
  $where = 'srv_id=' . $srv_id . ' and duration>0 and price>0';
  $order = 'case when level_id is null then 0 else 1 end,serial';
  $tips = DB::getDB()->queryRecords(WService::TABLE_TIP, $fields, $where, $order);
  if (!$tips)
   return null;
  $result = array();
  foreach ($tips as $tip)
  {
   $id = $tip[0];
   //$levelId = $tip[1];
   $duration = $tip[2];
   $priceTypeId = $tip[3];
   $price = $tip[4];
   $maxPrice = $tip[5];
   $rest = $tip[6];
   if ($price < 1)
    continue;
   if (($priceTypeId == self::PRICE_TYPE_RANGE) && ($maxPrice < 1))
    continue;
   if ($limited && ($rest < 1))
    continue;
   $title = WService::tipTitle($id);
   $result[] = array
   (
    'id' => $id,
    //'level_id' => $levelId,
    'title' => $title,
    'price_type_id' => $priceTypeId,
    'price' => $price,
    'max_price' => $maxPrice,
    'rest' => $rest
   );
   if (isset($duration))
    $result[count($result) - 1]['duration'] = $duration;
  }
  return $result;
 }

 /**
  * Get the centre rating information
  * @return array: {facil:{count,total,ambie,staff,clean,value},distr:[5],cats:[{title,list:[{id,title,rsum,rcnt}],rated}]}
  */
 public static function listRatings($srvId = null)
 {
  $table = 'com_review';
  $where = 'centre_id=' . self::$id . ' and signaled is null';
  if ($srvId)
  {
   $where .= ' and id in (select review_id from com_review_prc' .
             ' where prc_id in (select prc_id from ' . WService::TABLE_SRV_PRC .
             ' where srv_id=' . $srvId . '))';
  }
  $count = intval(DB::getDB()->queryField($table, 'count(*)', $where));
  $facil = array('count' => $count, 'total' => 0, 'ambie' => 0, 'staff' => 0, 'clean' => 0, 'value' => 0);
  $distr = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
  $cats = array();
  if ($count)
  {
   $fields = 'sum(rate_total),' .
     'sum(case when rate_total=1 then 1 end),' .
     'sum(case when rate_total=2 then 1 end),' .
     'sum(case when rate_total=3 then 1 end),' .
     'sum(case when rate_total=4 then 1 end),' .
     'sum(case when rate_total=5 then 1 end),' .
     'sum(rate_ambie),' .
     'sum(rate_staff),' .
     'sum(rate_clean),' .
     'sum(rate_value)';
   $values = DB::getDB()->queryFields($table, $fields, $where);
   if ($values)
   {
    $facil = array('count' => $count,
        'total' => round(intval($values[0]) / $count, 2),
        'ambie' => round(intval($values[6]) / $count, 2),
        'staff' => round(intval($values[7]) / $count, 2),
        'clean' => round(intval($values[8]) / $count, 2),
        'value' => round(intval($values[9]) / $count, 2));
    $distr = array(
        1 => intval($values[1]),
        2 => intval($values[2]),
        3 => intval($values[3]),
        4 => intval($values[4]),
        5 => intval($values[5]));
   }
  }
  $prcQuery = '(select' .
    ' c.id cat_id,c.serial cat_nr,c.name cat_name' .
    ',b.id prc_id,b.serial prc_nr,b.name prc_name' .
    ',(select sum(rate) from com_review_prc where prc_id=b.id and review_id in (select id from com_review where ' . $where . '))rsum' .
    ',(select count(*)  from com_review_prc where prc_id=b.id and review_id in (select id from com_review where ' . $where . '))rcnt' .
    ' from biz_menu_prc b,biz_menu_cat c' .
    ' where b.id in (select prc_id from com_menu_srv_prc where srv_id in (select id from com_menu_srv where centre_id=' . self::$id . '))' .
    ' and c.id=b.cat_id and b.hidden is null and c.hidden is null)a';
  $prcFields = 'cat_id,cat_nr,cat_name,prc_id,prc_nr,prc_name,rsum,rcnt';
  $prcOrder = 'cat_nr,cat_id,prc_nr,prc_id';
  $prcValues = DB::getDB()->queryRecords($prcQuery, $prcFields, '', $prcOrder);
  if ($prcValues)
  {
   $lastCatId = null;
   $cat = null;
   foreach ($prcValues as $prc)
   {
    $catId = $prc[0];
    if ($catId != $lastCatId)
    {
     if ($lastCatId)
      $cats[] = $cat;
     $lastCatId = $catId;
     $title = Lang::getDBValueDef('biz_menu_cat_abc', null, 'cat_id=' . $catId, $prc[2]);
     $cat = array('title' => $title, 'list' => array(), 'rated' => 0);
    }
    $prcId = $prc[3];
    $title = Lang::getDBValueDef('biz_menu_prc_abc', null, 'prc_id=' . $prcId, $prc[5]);
    $rsum = intval($prc[6]);
    $rcnt = intval($prc[7]);
    $cat['list'][] = array('id' => $prcId, 'title' => $title, 'rsum' => $rsum, 'rcnt' => $rcnt);
    if ($rcnt && !$cat['rated'])
     $cat['rated'] = 1;
   }
   if ($lastCatId)
    $cats[] = $cat;
  }
  return array('facil' => $facil, 'distr' => $distr, 'cats' => $cats);
 }

 /**
  * Get review list information
  * @return array: [{id,written,author,name,rateT,rateA,rateS,rateC,rateV,text,prcRates:[{id,rate}],comments:[id,written,author,name,text]}]
  */
 public static function listReviews($srvId = null)
 {

  $result = array();
  if (!self::$id)
   return $result;
  $table = 'com_review';
  $fields = 'id,written,client_id,client_name,text,rate_total,rate_ambie,rate_staff,rate_clean,rate_value';
  $where = 'centre_id=' . self::$id . ' and signaled is null';
  if ($srvId)
  {
   $where .= ' and id in (select review_id from com_review_prc' .
             ' where prc_id in (select prc_id from com_menu_srv_prc' .
             ' where srv_id=' . $srvId . '))';
  }
  $reviews = DB::getDB()->queryRecords($table, $fields, $where, 'id desc');
  //echo DB::lastQuery() . "<br><br>\n";
  if (!$reviews)
   return $result;
  foreach ($reviews as $review)
  {
   $id = $review[0];
   $written = $review[1];
   $clientId = $review[2];
   $name = $review[3];
   $text = $review[4];
   $rateTotal = $review[5];
   $rateAmbie = $review[6];
   $rateStaff = $review[7];
   $rateClean = $review[8];
   $rateValue = $review[9];
   $item = array(
     'id' => $id,
     'written' => $written,
     'author' => $clientId,
     'name' => $name,
     'rateT' => $rateTotal,
     'rateA' => $rateAmbie,
     'rateS' => $rateStaff,
     'rateC' => $rateClean,
     'rateV' => $rateValue,
     'text' => $text
   );
   $prcRates = self::listPrcRates($id);
   if ($prcRates)
    $item['prcRates'] = $prcRates;
   $comments = self::listComments($id);
   if ($comments)
    $item['comments'] = $comments;
   $result[] = $item;
  }
  return $result;
 }

 public static function listPrcRates($reviewId)
 {
  if (!self::$id)
   return null;
  $fields = 'prc_id,rate';
  $rates = DB::getDB()->queryRecords('com_review_prc', $fields, 'review_id=' . $reviewId, 'prc_id');
  if (!$rates)
   return null;
  $result = array();
  foreach ($rates as $rate)
   $result[] = array('id' => $rate[0], 'rate' => $rate[1]);
  return count($result) ? $result : null;
 }

 public static function listComments($reviewId)
 {
  $fields = 'id,written,client_id,client_name,text';
  $comments = DB::getDB()->queryRecords('com_review_comment', $fields, 'review_id=' . $reviewId . ' and signaled is null', 'id');
  if (!$comments)
   return null;
  $result = array();
  foreach ($comments as $comment)
  {
   $id = $comment[0];
   $written = $comment[1];
   $clientId = $comment[2];
   $name = $comment[3];
   $text = $comment[4];
   $result[] = array(
     'id' => $id,
     'written' => $written,
     'author' => $clientId,
     'name' => $name,
     'text' => $text
   );
  }
  return count($result) ? $result : null;
 }

 public static function logoURI($id = null)
 {
  if (!$id)
   $id = self::id();
  if ($id)
  {
   $logo = DB::getDB()->queryFields(self::TABLE_CENTRE, 'logo_width,logo_height', 'id=' . $id . ' and logo is not null');
   if ($logo)
    return 'img/ctr-' . $id . '.jpg';
  }
  return null;
 }

 /**
  * Get a logo image info of the specified centre
  * @param id Centre ID (if is null then the current centre ID is used)
  * @return array: [src,w,h]
  */
 public static function logoInfo($id = null)
 {
  if (!$id)
   $id = self::id();
  $logoInfo = null;
  // Centre logo
  if (!$logoInfo && $id)
  {
   $logo = DB::getDB()->queryFields(self::TABLE_CENTRE, 'logo_width,logo_height', 'id=' . $id . ' and logo is not null');
   if ($logo)
    $logoInfo = array('src' => 'img/ctr-' . $id . '.jpg', 'w' => $logo[0], 'h' => $logo[1]);
  }
  // Brand logo
  if (!$logoInfo && $id)
  {
   $bndId = ($id == self::id()) ? self::brandId() : DB::getDB()->queryField(WCentre::TABLE_CENTRE, 'brand_id', 'id=' . $id);
   if ($bndId)
   {
    $logo = DB::getDB()->queryFields(WBrand::TABLE_BRAND, 'logo_width,logo_height', 'id=' . $bndId . ' and logo is not null');
    if ($logo)
     return array('src' => 'img/bnd-' . $bndId . '.jpg', 'w' => $logo[0], 'h' => $logo[1]);
   }
  }
  // Default logo
  if (!$logoInfo)
  {
   $src = 'pic/no-centre-' . rand(0, 9) . '.jpg';
   $file = Base::root() . Base::home() . $src;
   if (file_exists($file))
   {
    $size = getimagesize($file);
    if ($size && is_array($size) && (count($size) > 1))
     $logoInfo = array('src' => $src, 'w' => $size[0], 'h' => $size[1]);
   }
  }
  if (!$logoInfo)
   $logoInfo = array('src' => '', 'w' => 0, 'h' => 0);
  return $logoInfo;
 }

 public static function downloadLogo($id = null)
 {
  if (!$id)
  {
   self::initCurrent();
   $id = self::id();
  }
  if (!Base::isIndexNatural($id))
   return false;
  return DB::getDB()->downloadFile(self::TABLE_CENTRE, 'logo', 'id=' . $id, 'logo_filename', 'logo_mimetype') ||
    Base::downloadFile(file_get_contents(Base::root() . Base::home() . 'pic/no-centre.jpg'), 'no-centre.jpg', 'image/jpeg');
 }

 public static function uploadLogo($id)
 {
  $table = self::TABLE_CENTRE;
  $where = array('id' => $id);
  $tmp_name1 = $_FILES['image']['tmp_name'];
  $tmp_name2 = $tmp_name1 . 'x';
  $result = true;
  // Resize to 300
  if (XImage::resizeImageWidth($tmp_name1, $tmp_name2, self::LOGO_WIDTH))
  {
   $_FILES['image']['tmp_name'] = $tmp_name2;
   $_FILES['image']['size'] = filesize($tmp_name2);
   $fields = DB::uploadFields('logo', 'logo_filename', 'logo_mimetype', 'logo_width', 'logo_height', 'logo_size');
   $result &= DB::getAdminDB()->uploadFile('image', $table, $fields, $where);
   unlink($tmp_name2);
  }
  return $result;
 }

 public static function uploadLogoFromDB($id, $uri)
 {
  return false;
 }

 public static function uploadLogoFromURI($id, $uri)
 {
  $filename = $uri;
  $pos = strpos($filename, '?');
  if ($pos !== false)
   $filename = substr ($filename, 0, $pos);
  $parts = explode('/', $filename);
  if (count($parts) > 1)
   $filename = $parts[count($parts) - 1];
  $tmp_name = tempnam(null, "img");
  $table = self::TABLE_CENTRE;
  $where = array('id' => $id);
  $result = true;
  // Resize to 300
  $image1 = XImage::resizeImageWidth($uri, $tmp_name, self::LOGO_WIDTH);
  if ($image1)
  {
   $size = filesize($tmp_name);
   unlink($tmp_name);
   $data = $image1->getData();
   $mimetype = $image1->mimetype();
   $values = array('logo' => DB::str($data), 'logo_filename' => DB::str($filename), 'logo_mimetype' => DB::str($mimetype),
     'logo_width' => $image1->width(), 'logo_height' => $image1->height(), 'logo_size' => $size);
   $result &= DB::getAdminDB()->mergeFields($table, $values, $where);
  }
  return $result;
 }

 public static function clearLogo($id)
 {
  return DB::getAdminDB()->modifyFields(self::TABLE_CENTRE,
    array('logo' => 'null', 'logo_filename' => 'null', 'logo_mimetype' => 'null',
        'logo_size' => 'null', 'logo_width' => 'null', 'logo_height' => 'null'),
    array('id' => $id));
 }

 public static function createGallery($id = null)
 {
  if ($id == null)
   $id = self::id();
  return new WGallery(self::TABLE_CENTRE_IMG, 'centre_id', 'ctr', $id);
 }

 public static function downloadImage($id, $serial)
 {
  if (!Base::isIndexNatural($id) || !Base::isIndexNatural($serial))
   return false;
  return self::createGallery($id)->downloadImage($serial);
 }

 public static function location()
 {
  return Util::dbl2str(self::$lat / 1000000) . ',' . Util::dbl2str(self::$lng / 1000000);
 }

 public static function viewBounds()
 {
  if (!self::$id || !self::$lat || !self::$lng)
   return null;
  return 'loc-' . self::$lat . '|10000|' . self::$lng . '|10000';
 }

 public static function locURI()
 {
  if (!self::$id || !self::$lat || !self::$lng)
   return null;
  return 'list/' . self::viewBounds() . '/locT-' . base64_encode(self::$address) . '/';
 }

 public static function getStaticMapURI($width = null, $height = null)
 {
  if (!isset(self::$lat) || !isset(self::$lng))
   return '';
  if (!$width)
   $width = 300;
  if (!$height)
   $height = $width;
  return Base::pro() . 'maps.googleapis.com/maps/api/staticmap' .
    '?size=' . $width .'x' . $height . '&sensor=false&format=jpg&zoom=15' .
    '&markers=color:red%7Clabel:S%7C' . self::location();
 }

 public static function getOldDynamicMapURI()
 {
  if (!isset(self::$lat) || !isset(self::$lng))
   return '';
  return 'http://www.google.com/maps/place/' .
    str_replace(' ', '+', self::$address) . '/@' . self::location() . ',15z';
 }

 public static function getDynamicMapURI()
 {
  if (!isset(self::$lat) || !isset(self::$lng))
   return '';
  return 'http://www.google.com/maps/place/' .
    self::location() . '/@' . self::location() . ',15z';
 }

 public static function changeAddress($db, $id, $address)
 {
  return $db->modifyFields(self::TABLE_CENTRE, array('address' => DB::str($address)), 'id=' . $id);
 }

 /**
  * Get a geolocation info of the current centre
  * @return null or array: [lat,lng,bounds,staticURI,dynamicURI]
  */
 public static function loc()
 {
  if (!self::$id || !self::$lat || !self::$lng)
   return null;
  $result = array(
    'lat' => Util::dbl2str(self::$lat / 1000000),
    'lng' => Util::dbl2str(self::$lng / 1000000),
    //'latlng' => Util::dbl2str(self::$lat / 1000000) . ',' . Util::dbl2str(self::$lng / 1000000),//self::location(),
    'bounds' => 'loc-' . self::$lat . '|10000|' . self::$lng . '|10000',
    'staticURI' => self::getStaticMapURI(),
    'dynamicURI' => self::getDynamicMapURI());
  return $result;
 }

 /**
  * Get a metro station list of the centre
  * @return array: [""]
  */
 public static function metros()
 {
  if (!self::$id)
   return null;
  $result = array();
  $query = '(select' .
    ' (select title from biz_menu_ter_metro_abc where metro_id=a.id and abc_id=' . DB::str(Lang::current()) . ')title1' .
    ',(select min(title) from biz_menu_ter_metro_abc where metro_id=a.id and length(title)>0)title2' .
    ' from biz_menu_ter_metro a' .
    ' where hidden is null' .
    ' and id in (select metro_id from com_centre_metro where centre_id=' . self::id() . ')' .
    ') q';
  $metros = DB::getDB()->queryRecords($query, 'coalesce(title1,title2)', null, '1');
  if ($metros)
   foreach ($metros as $metro)
    if ($metro[0])
     $result[] = $metro[0];
  return $result;
 }

 /**
  * Get a phone number list of the centre
  * @return array: [""]
  */
 public static function phones()
 {
  if (!self::$id)
   return null;
  $result = array();
  $phones = DB::getDB()->queryRecords('com_centre_phone', 'phone', 'centre_id=' . self::$id, 'serial');
  if ($phones)
   foreach ($phones as $phone)
    if ($phone[0])
     $result[] = $phone[0];
  return $result;
 }

 public static function times()
 {
  $firstDay = WDomain::firstDay();
  $query = '(select mod(week_day-' . $firstDay . ',7)+1 d,open_min o,close_min c from ' .
    WCentre::TABLE_CENTRE_SCHED . ' where centre_id=' . WCentre::id() . ') a';
  $times = DB::getDB()->queryMatrix($query, 'd,o,c', null, '1');
  return $times;
 }

 /**
  * Get a opening time list of the centre
  * @return array: [{"",["",""],""}]
  */
 public static function sched()
 {
  if (!self::$id)
   return null;
  $result = array();
  $sched = DB::getDB()->queryMatrix(self::TABLE_CENTRE_SCHED, 'week_day,open_min,close_min', 'centre_id=' . self::$id, '1');
  if ($sched)
  {
   $lastStart = 0;
   $lastStop = 0;
   $lastOpen = -1;
   $lastClose = -1;
   for ($i = 1; $i <= 7; $i++)
   {
    if (array_key_exists($i, $sched))
    {
     $open = $sched[$i]['open_min'];
     $close = $sched[$i]['close_min'];
    }
    else
    {
     $open = 0;
     $close = 0;
    }
    if ($lastStart && ($open == $lastOpen) && ($close == $lastClose))
    {
     $lastStop = $i;
    }
    else
    {
     if ($lastStart)
      $result[] = self::schedRow($lastStart, $lastStop, $lastOpen, $lastClose);
     $lastStart = $i;
     $lastStop = $i;
     $lastOpen = $open;
     $lastClose = $close;
    }
   }
   $result[] = self::schedRow($lastStart, $lastStop, $lastOpen, $lastClose);
  }
  return $result;
 }

 /**
  * Make an opening time info
  * @return array: {"",["",""],""}
  */
 private static function schedRow($start, $stop, $open, $close)
 {
  $open = Util::min2str($open);
  $close = Util::min2str($close);
  $label = Lang::dayOfWeek($start);
  $days = Lang::dayOfWeekEn($start);
  if ($stop > $start)
  {
   $label .= ' - ' . Lang::dayOfWeek($stop);
   $days .= '-' . Lang::dayOfWeekEn($stop);
  }
  return array($label, ($open != $close) ? array($open, $close) : null, $days);
 }

 public static function timeSlots($date)
 {
  $slots = array();
  $day = $date->format('N');
  //echo "<!-- Here day = " . print_r($day, true) . " -->\n";
  $where = 'centre_id=' . self::$id . ' and week_day=' . $day;
  $sched = DB::getDB()->queryFields('com_centre_sched', 'open_min,close_min', $where, '1');
  //echo "<!-- Here sql = " . DB::lastQuery() . " -->\n";
  //echo "<!-- Here sched = " . print_r($sched, true) . " -->\n";
  if ($sched)
  {
   $openMin = $sched[0];
   $closeMin = $sched[1];
   if (($openMin != null) || ($closeMin != null))
   {
    if ($closeMin <= $openMin)
     $closeMin += 1440; // 24 * 60
    while ($openMin < $closeMin)
    {
     $slot = array('a' => $openMin % 1440);
     $openMin += self::TIME_SLOT;
     if (!count($slots))
      $openMin = floor($openMin / self::TIME_SLOT) * self::TIME_SLOT;
     if ($openMin > $closeMin)
      $openMin = $closeMin;
     $slot['b'] = $openMin % 1440;
     // TODO: $slot['c'] = count, $slot['d'] = discount
     $slots[] = $slot;
    }
   }
  }
  return $slots;
 }

 public static function calcRate($db, $id)
 {
  $values = array(
    'rate_prc' => '(select avg(rate_total)*100 from com_review' .
    ' where centre_id=a.id and signaled is null)',
    'rate_count' => '(select count(*) from com_review' .
    ' where centre_id=a.id and signaled is null)');
  $db->modifyFields(self::TABLE_CENTRE . ' a', $values, 'id=' . $id);
  if ($id == self::id())
  {
   $fields = DB::getDB()->queryFields(self::TABLE_CENTRE, 'rate_prc,rate_count', 'id=' . $id);
   if ($fields)
   {
    self::$rate_prc = $fields[0];
    self::$rate_count = $fields[1];
   }
  }
 }

 /**
  * Add a review
  * @return assoc_array Set of the result values
  */
 public static function review()
 {
  if (Base::page() != 'book')
   return array('error' => 'Error adding a review: Invalid page: ' . Base::page());
  if ((Base::mode() != 'ctr') && (Base::mode() != 'srv'))
   return array('error' => 'Error adding a review: Invalid mode: ' . Base::mode());
  $values = array
  (
   'centre_id' => self::id(),
   'client_id' => WClient::id(),
   'client_name' => DB::str(WClient::name()),
   'rate_total' => HTTP::get('total'),
   'rate_ambie' => HTTP::get('ambie'),
   'rate_clean' => HTTP::get('clean'),
   'rate_staff' => HTTP::get('staff'),
   'rate_value' => HTTP::get('value'),
   'text' => DB::str(HTTP::get('text')),
   'notifier' => (HTTP::get('notif') ? '1' : 'null')
  );
  $db = DB::getAdminDB();
  if (!$db->insertValues('com_review', $values))
   return array('error' => 'Error adding a review: ' . DB::lastQuery());
  $reviewId = $db->insert_id;
  self::calcRate($db, self::id());
  foreach ($_GET as $key => $value)
  {
   if (fnmatch('prc-*', $key))
    if (!$db->insertValues('com_review_prc', array('review_id' => $reviewId, 'prc_id' => intval(substr($key, 4)), 'rate' => intval($value))))
     return array('error' => 'Error adding a procedure rate: ' . DB::lastQuery());
  }
  $owner = new WClient(self::memberId());
  if ($owner->getId())
  {
   $name = $owner->getName();
   $email = self::email() ? self::email() : $owner->getEmail();
   $title = self::getTitleForData(self::id(), self::name(), self::brandId());
   $hrefCentre = Base::bas() . 'ctr-' . self::id() . '/#review-' . $reviewId;
   $hrefClient = Base::bas() . 'clt-' . $owner->id() . '/';
   $subject = 'A new review has been added to your page';
   $message = $subject . ': <a target="_blank" href="' . $hrefCentre . '">' . $title . ' - Click here to view it</a><br>' . "\n";
   $message .= 'Author: <a target="_blank" href="' . $hrefClient . '">' . $owner->name() . '</a><br>' . "\n";
   $message .= "\n<hr>\n" . str_replace("\n", "<br>\n", HTTP::get('text'));
   WMessage::sendToClient($owner->getId(), $subject, $message);
   SMTP::send($name, $email, $subject, $message);
  }
  return array('result' => 'OK');
 }

 /**
  * Add a review comment
  * @return assoc_array Set of the result values
  */
 public static function comment()
 {
  if (Base::page() != 'book')
   return array('error' => 'Error adding a review comment: Invalid page: ' . Base::page());
  if ((Base::mode() != 'ctr') && (Base::mode() != 'srv'))
   return array('error' => 'Error adding a review comment: Invalid mode: ' . Base::mode());
  $values = array
  (
   'review_id' => HTTP::param('review'),
   'client_id' => WClient::id(),
   'client_name' => DB::str(WClient::name()),
   'text' => DB::str(HTTP::param('text')),
   'notifier' => (HTTP::param('notif') ? '1' : 'null')
  );
  $db = DB::getAdminDB();
  if (!$db->insertValues('com_review_comment', $values))
   return array('error' => 'Error adding a review comment: ' . DB::lastQuery());
  $commentId = $db->insert_id;
  $owner = new WClient(self::memberId());
  if ($owner->getId())
  {
   $name = $owner->getName();
   $email = self::email() ? self::email() : $owner->getEmail();
   $title = self::getTitleForData(self::id(), self::name(), self::brandId());
   $hrefCentre = Base::bas() . 'ctr-' . self::id() . '/#comment-' . $commentId;
   $hrefClient = Base::bas() . 'clt-' . WClient::id() . '/';
   $subject = 'A new comment has been added to review';
   $message = $subject . ': <a target="_blank" href="' . $hrefCentre . '">' . $title . ' - Click here to view it</a><br>' . "\n";
   $message .= 'Author: <a target="_blank" href="' . $hrefClient . '">' . WClient::name() . '</a><br>' . "\n";
   $message .= "\n<hr>\n" . str_replace("\n", "<br>\n", HTTP::get('text'));
   WMessage::sendToClient($owner->getId(), $subject, $message);
   $reviewId = $db->queryField('com_review_comment', 'client_id', 'id=' . $commentId);
   $authorId = $db->queryField('com_review', 'client_id', 'id=' . $reviewId . ' and notifier is not null');
   if ($authorId)
    WMessage::sendToClient($authorId, $subject, $message);
   $authors = $db->queryRecords('com_review_comment', 'distinct client_id',
     'review_id=' . $reviewId . ' and client_id<>' . WClient::id() . ' and notifier is not null');
   if ($authors)
    foreach ($authors as $rec)
     WMessage::sendToClient($rec[0], $subject, $message);
   SMTP::send($name, $email, $subject, $message);
  }
  return array('result' => 'OK');
 }

 /**
  * Add a cavil for review or review comment
  * @return assoc_array Set of the result values
  */
 public static function cavil()
 {
  if (Base::page() != 'book')
   return array('error' => 'Error adding a report: Invalid page: ' . Base::page());
  if ((Base::mode() != 'ctr') && (Base::mode() != 'srv'))
   return array('error' => 'Error adding a report: Invalid mode: ' . Base::mode());
  $values = array
  (
   'client_id' => WClient::id(),
   'client_name' => DB::str(WClient::name()),
   'text' => DB::str(HTTP::param('text')),
   'notifier' => (HTTP::param('notif') ? '1' : 'null'),
   'violation' => (HTTP::param('viol') ? '1' : 'null'),
   'falsehood' => (HTTP::param('ilgl') ? '1' : 'null')
  );
  $db = DB::getAdminDB();
  if (array_key_exists('comment', $_GET))
  {
   $commentId = HTTP::param('comment');
   $values['comment_id'] = $commentId;
   if (!$db->insertValues('com_review_comment_cavil', $values))
    return array('error' => 'Error adding a report to review comment: ' . DB::lastQuery());
   $verified = $db->queryField('com_review_comment', '1', "id=$commentId and verified is not null");
   if (!$verified)
    $db->modifyField('com_review_comment', 'signaled', 's', '1', 'id=' . $commentId);
  }
  else
  {
   $reviewId = HTTP::param('review');
   $values['review_id'] = $reviewId;
   if (!$db->insertValues('com_review_cavil', $values))
    return array('error' => 'Error adding a report to review: ' . DB::lastQuery());
   $verified = $db->queryField('com_review', '1', "id=$reviewId and verified is not null");
   if (!$verified)
    $db->modifyField('com_review', 'signaled', 's', '1', 'id=' . $reviewId);
  }
  if (!$verified)
   self::calcRate($db, self::id());
  return array('result' => 'OK');
 }

 public static function changeLocation($db, $id, $lat, $lng)
 {
  return $db->modifyFields(self::TABLE_CENTRE, array('lat' => $lat, 'lng' => $lng), 'id=' . $id);
 }

 /**
  * Function queries the Google API for stored centre address and receives the location and regions
  * @param type $id Centre Id
  * @return bool Success
  */
 public static function queryLocation($id = null)
 {
  if ($id == null)
   $id = self::$id;
  $db = DB::getAdminDB();
  if (!$db->deleteRecords(self::TABLE_CENTRE_PLACE, 'centre_id=' . $id))
   return false;
  $address = $db->queryField(self::TABLE_CENTRE, 'address', 'id=' . $id);
  if (!strlen($address))
   return $db->modifyFields(self::TABLE_CENTRE, array('lat' => '0', 'lng' => '0'), 'id=' . $id);
  if (!$db->modifyFields(self::TABLE_CENTRE, array('lat' => 'null', 'lng' => 'null'), 'id=' . $id))
   return false;
  $info = GAPI::queryFirstDetailsAndRegions($address, true);
  $lat = $info ? $info['lat'] : 0;
  $lng = $info ? $info['lng'] : 0;
  if (!$db->modifyFields(self::TABLE_CENTRE, array('lat' => $lat, 'lng' => $lng), 'id=' . $id))
   return false;
  if (!$info)
   return true;
  $regions = $info['regions'];
  if ($regions)
  {
   foreach ($regions as $i => $region)
   {
    $values = array();
    $values['centre_id'] = $id;
    $values['serial'] = $i + 1;
    $values['place_id'] = DB::str($region['id']);
    $values['type'] = DB::str($region['type']);
    $values['name'] = DB::str($region['name']);
    $values['address'] = DB::str($region['addr']);
    if (!$db->insertValues(self::TABLE_CENTRE_PLACE, $values))
    {
     echo DB::lastQuery() . "\n";
     return false;
    }
   }
  }
  return true;
 }
}

?>
