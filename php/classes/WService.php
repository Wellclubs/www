<?php

/**
 * Description of WService
 */
class WService
{
 const TABLE_GRP = 'com_menu_grp';
 const TABLE_SRV = 'com_menu_srv';
 const TABLE_TIP = 'com_menu_tip';
 const TABLE_SRV_PRC = 'com_menu_srv_prc';
 const TABLE_SRV_MTR = 'com_menu_srv_master';
 const TABLE_SRV_IMG = 'com_menu_srv_img';

 const MAX_GRP_COUNT = 50;
 const MAX_SRV_COUNT = 1000;
 const MAX_TIP_COUNT = 10;

 const MAX_DURATION = 600;
 //const MAX_PRICE = 1000;
 const MAX_PRC_COUNT = 10;

 private static $current = null;

 public static function initCurrent($id)
 {
  if ($id == null)
   return false;
  if ((self::$current === null) || ($id != self::$current->id))
   self::$current = new WService($id);
  return true;
 }

 public static function id() { return self::$current ? self::$current->id : null; }
 public static function name() { return self::$current ? self::$current->name : null; }
 public static function title() { return self::$current ? self::srvTitle(self::$current->id) : null; }
 public static function centreId() { return self::$current ? self::$current->centreId : null; }
 public static function matcatId() { return self::$current ? self::$current->matcatId : null; }
 public static function groupId() { return self::$current ? self::$current->groupId : null; }
 public static function limited() { return self::$current ? self::$current->limited : null; }

 public static function descr($id = null) {  return self::getTextValue('descr', $id); }
 public static function restr($id = null) {  return self::getTextValue('restr', $id); }
 public static function notes($id = null) {  return self::getTextValue('notes', $id); }

 private $id = null;
 private $name = null;
 private $centreId = null;
 private $matcatId = null;
 private $schemaId = null;
 private $groupId = null;
 private $limited = null;
 private $keywords = null;

 private $prcs = null;

 public function getId() { return $this->id; }
 public function getName() { return $this->name; }
 public function getCentreId() { return $this->centreId; }
 public function getMatcatId() { return $this->matcatId; }
 //public function getSchemaId() { return $this->schemaId; }
 public function getGroupId() { return $this->groupId; }
 public function getLimited() { return !!$this->limited; }

 public function getPrcs() { return $this->prcs; }

 public function __construct($id)
 {
  $fields = 'name,centre_id,grp_id,limited';
  $values = DB::getDB()->queryPairs(self::TABLE_SRV, $fields, 'id=' . $id);
  if (!$values)
   return;

  $this->id = $id;
  $this->name = $values['name'];
  $this->centreId = $values['centre_id'];
  $this->groupId = $values['grp_id'];
  $this->limited = $values['limited'];

  //$this->schemaId = DB::getDB()->queryField(self::TABLE_GRP, 'schema_id', 'id=' . $this->groupId);

  $this->prcs = DB::getDB()->queryArrays(self::TABLE_SRV_PRC, 'prc_id', 'srv_id=' . $id);
 }

 public static function keywords($id = null)
 {
  if (!$id)
   $id = self::$current->id;
  if (!$id)
   return null;
  return DB::getDB()->queryField(self::TABLE_SRV, 'keywords', 'id=' . $id);
 }

 private static function getTextValue($field, $id = null)
 {
  if (!$id)
   $id = self::$current->id;
  if (!$id)
   return null;
  return Lang::getDBValue(self::TABLE_SRV . '_abc', $field, 'srv_id=' . $id);
 }

 /**
  * Get service title
  * @param int $id Service id
  * @return string  Service title
  */
 public static function getTitle($id)
 {
  return Lang::getDBTitle(self::TABLE_SRV, 'srv', $id);
 }

 public static function groups()
 {
  $rows = DB::getDB()->queryRecords(self::TABLE_GRP, 'id', 'centre_id=' . WCentre::id(), 'serial,id');
  return $rows ? array_map(array('Util', 'mapArrayItem'), $rows) : array();
 }

 public static function services($grpId)
 {
  $rows = DB::getDB()->queryRecords(self::TABLE_SRV, 'id', 'centre_id=' . WCentre::id() . ' and grp_id=' . $grpId, 'serial,id');
  return $rows ? array_map(array('Util', 'mapArrayItem'), $rows) : array();
 }

 public static function tips($srvId)
 {
  $rows = DB::getDB()->queryRecords(self::TABLE_TIP, 'id', 'centre_id=' . WCentre::id() . ' and srv_id=' . $srvId, 'serial,id');
  return $rows ? array_map(array('Util', 'mapArrayItem'), $rows) : array();
 }

 /**
  * Get number of price options for the specified service
  * @param int $srvId
  * @return int Number of price options
  */
 public static function getTipCount($srvId)
 {
  return DB::getDB()->queryField(self::TABLE_TIP, 'count(*)', 'srv_id=' . $srvId);
 }

 /**
  * Get list of price options for the specified service (pages book/srv/, com/srvs/)
  * @param int $srvId
  * @return array List of arrays for every price option
  */
 public static function getTipList($srvId)
 {
  $tips = array();
  $fields = 'id,level_id,name,duration,price_type_id,price,max_price,rest';
  $where = 'centre_id=' . WCentre::id() . ' and srv_id=' . $srvId;
  if (Base::page() == 'book')
   $where .= ' and duration>0 and price>0';
  $rows = DB::getDB()->queryArrays(self::TABLE_TIP, $fields, $where, 'serial,id');
  if ($rows)
   foreach ($rows as $row)
   {
    $title = Lang::getDBValueDef(self::TABLE_TIP . '_abc', null, 'tip_id=' . $row['id'], $row['name']);
    if (!strlen($title))
     $title = WBrand::getLevelTitle($row['level_id']);
    $row['title'] = $title;
    $row['duration'] = Util::nvl($row['duration'],'');
    $tips[] = $row;
   }
  return $tips;
 }

 public static function getCapacity($ctrId = null, $srvId = null, $date = null, $time = null)
 {
  if (!$ctrId)
   $ctrId = WCentre::id();
  if (!$ctrId)
   return 0;

  if (!$srvId)
  {
   $srvId = WService::id();
   if (!$srvId)
    return 0;
   $grpId = WService::groupId();
  }
  else
  {
   if ($srvId == WService::id())
    $grpId = WService::groupId();
   else
    $grpId = DB::getDB()->queryField(WService::TABLE_SRV, 'grp_id', 'id=' . $srvId);
  }
  if (!$grpId)
   return 0;

  $capacities = array();

  $c = null; // capacity

  if ($date != null && $time != null)
  {
   $e = null; // schema existance
   $day = $date->format('N');
   $filter = "day$day='1' and $time>=start_time and $time<final_time and capacity>0";
   $schemaSrvId = DB::getDB()->queryField(self::TABLE_SRV, 'schema_id', 'id=' . $srvId);
   if ($schemaSrvId)
   {
    $c = DB::getDB()->queryField('com_centre_schema_interval', 'max(capacity)', "schema_id=$schemaSrvId and $filter");
    $e = DB::getDB()->queryField('com_centre_schema_interval', '1', "schema_id=$schemaSrvId and capacity>0");
   }
   if (!$e)
   {
    $schemaGrpId = DB::getDB()->queryField(self::TABLE_GRP, 'schema_id', 'id=' . $grpId);
    if ($schemaGrpId)
    {
     $c = DB::getDB()->queryField('com_centre_schema_interval', 'max(capacity)', "schema_id=$schemaGrpId and $filter");
     $e = DB::getDB()->queryField('com_centre_schema_interval', '1', "schema_id=$schemaGrpId and capacity>0");
    }
   }
   if (!$e)
   {
    $c = DB::getDB()->queryField('com_centre_schema_interval', 'max(capacity)',
      "schema_id in (select id from com_centre_schema where centre_id=$ctrId and global='1') and $filter");
    $e = DB::getDB()->queryField('com_centre_schema_interval', '1',
      "schema_id in (select id from com_centre_schema where centre_id=$ctrId and global='1') and capacity>0");
   }
   if (!$c && $e)
    return 0; // Out of quote
  }

  if ($c === null)
  {
   $c = WCentre::getCapacity($ctrId);
   //echo "Common capacity for centre $ctrId: $c<br>\n";
  }

  if ($c)
   $capacities[] = $c;

  $m = WCentre::masterCount(null, $srvId);
  //echo $srvId ? "Number of masters for service $srvId: $m<br>\n" : "Total number of masters: $m<br>\n";
  if ($m)
   $capacities[] = $m;

  return count($capacities) ? min($capacities) : null;
 }

 public static function getBookings($cltId, $ctrId, $srvId, $date)
 {
  $table = WPurchase::TABLE_BOOK;
  $fields = 'id,book_time,book_dura,centre_id,srv_id';
  $where = array('client_id' => $cltId, 'status' => DB::str('a'));
  if ($ctrId)
   $where['centre_id'] = $ctrId;
  if ($srvId)
   $where['srv_id'] = $srvId;
  if ($date)
   $where['book_date'] = DB::str(DB::date2str($date));
  $order = 'book_date,book_time,centre_id,srv_id,created desc';
  if (WService::id())
   $order = 'case when srv_id=' . WService::id() . ' then 0 else 1 end,created desc';
  $records = DB::getDB()->queryArrays($table, $fields, $where, $order);
  //print_r(DB::lastQuery());
  //print_r($records);
  return $records ? $records : array();
 }

 public static function grpTitle($id)
 {
  return Lang::getDBTitle(self::TABLE_GRP, 'grp', $id);
 }

 public static function srvTitle($id)
 {
  return Lang::getDBTitle(self::TABLE_SRV, 'srv', $id);
 }

 public static function tipTitle($id)
 {
  $db = DB::getDB();
  $tip = $db->queryFields(self::TABLE_TIP, 'level_id,name', 'id=' . $id);
  $title = Lang::getDBValueDef(self::TABLE_TIP . '_abc', null, 'tip_id=' . $id, $tip[1]);
  if (!strlen($title))
   $title = WBrand::getLevelTitle($tip[0]);
  return $title;
 }

 public static function grpTitles($id)
 {
  return Lang::getDBTitles(self::TABLE_GRP, 'grp', $id);
 }

 public static function srvTitles($id)
 {
  return Lang::getDBTitles(self::TABLE_SRV, 'srv', $id);
 }

 public static function tipTitles($id)
 {
  return Lang::getDBTitles(self::TABLE_TIP, 'tip', $id);
 }

 public static function setGrpTitles($id)
 {
  return Lang::setDBTitles(self::TABLE_GRP, 'grp', $id);
 }

 public static function setSrvTitles($id)
 {
  return Lang::setDBTitles(self::TABLE_SRV, 'srv', $id);
 }

 public static function setTipTitles($id)
 {
  return Lang::setDBTitles(self::TABLE_TIP, 'tip', $id);
 }

 protected static function actionError($text)
 {
  PageCom::addToAjax('error', Lang::getPageWord('error', $text));
  return false;
 }

 protected static function actionErrorAccess()
 {
  PageCom::addToAjax('error', Lang::getPageWord('error', 'Access denied'));
  return false;
 }

 protected static function actionFail($text)
 {
  PageCom::addToAjax('failure', $text);
  return false;
 }

 public static function createGrp($name)
 {
  if (!strlen($name))
   return self::actionFail('No "name" parameter value set');
  $db = DB::getAdminDB();
  if ($db->queryField(self::TABLE_GRP, 'count(*)', 'centre_id=' . WCentre::id()) >= self::MAX_GRP_COUNT)
   return self::actionError('You already have a maximum quantity of service groups');
  if ($db->queryField(self::TABLE_GRP, 'count(*)', 'centre_id=' . WCentre::id() . ' and name=' . DB::str($name)))
   return self::actionError('This name is already used for another service group');
  if (!$db->insertValues(self::TABLE_GRP, array('centre_id' => WCentre::id(), 'name' => DB::str($name))))
   return self::actionFailDBInsert();
  $groupId = $db->insert_id;
  $db->modifySerialAfterInsert(self::TABLE_GRP);
  if(!Lang::setDBTitlesOnly(self::TABLE_GRP, array('grp_id' => $groupId), true))
   return self::actionFailDBInsert();
  return true;
 }

 public static function deleteGrp($id)
 {
  if (!strlen($id))
   return self::actionFail('No "id" parameter value set');
  $where = 'id=' . $id;
  $db = PageCom::db();
  if (intval($db->queryField(WService::TABLE_SRV, 'count(*)', 'grp_' . $where)) > 0)
   return self::actionError('Service group has some services linked and can\'t be deleted');
  if (!$db->deleteRecords(WService::TABLE_GRP, $where))
   return self::actionFailDBDelete();
  return true;
 }

 public static function createTip($srvId)
 {
  $name = HTTP::get('title');
  $level = intval(HTTP::get('level'));
  $duration = intval(HTTP::param('duration'));
  if (($duration <= 0) || ($duration > self::MAX_DURATION))
   return self::actionFail('Invalid "duration" parameter value');
  $price = intval(HTTP::param('price'));
  if ($price < 0) //|| ($price > self::MAX_PRICE))
   return self::actionFail('Invalid "price" parameter value');
  $db = DB::getAdminDB();
  $values = array
  (
   'centre_id' => WCentre::id()
  ,'srv_id' => $srvId
  ,'level_id' => ($level ? $level : 'null')
  ,'name' => DB::str($name)
  ,'duration' => $duration
  ,'price' => $price
  );
  if (!$db->insertValues(self::TABLE_TIP, $values, true))
   return self::actionFailDBInsert();
  return true;
 }

 public static function getFreeMtrs($ctrId, $srvId)
 {
  $result = array();
  $fields = 'id,(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id)';
  $where = 'centre_id=' . $ctrId . ' and for_service is not null and all_services is null' .
    ' and id not in (select master_id from ' . self::TABLE_SRV_MTR . ' where srv_id=' . $srvId . ')';
  $rows = DB::getDB()->queryRecords('com_master a', $fields, $where, 'serial');
  if ($rows)
   foreach ($rows as $row)
    $result[] = array('id' => $row[0], 'title' => $row[1]);
  return $result;
 }

 public static function addMtr($srvId, $mtrId)
 {
  $values = array
  (
   'srv_id' => $srvId
  ,'master_id' => $mtrId
  ,'serial' => $mtrId
  );
  if (!DB::getAdminDB()->insertValues(self::TABLE_SRV_MTR, $values))
   return self::actionFailDBInsert();
  return true;
 }

 public static function createGallery($id = null)
 {
  if ($id == null)
   $id = self::id();
  return new WGallery(self::TABLE_SRV_IMG, 'srv_id', 'srv', $id);
 }

 public static function downloadImage($id, $serial)
 {
  if (!Base::isIndexNatural($id) || !Base::isIndexNatural($serial))
   return false;
  return self::createGallery($id)->downloadImage($serial);
 }

 public static function discount($date, $time)
 {
  if (!$date)
   return 0;
  return self::getMaxDisc(self::id(), self::groupId(), self::centreId(), $date, $time);
 }

 public static function getMaxDisc($srvId, $grpId, $ctrId, $date = null, $time = null)
 {
  $schemaSrvId = $srvId ? DB::getDB()->queryField(self::TABLE_SRV, 'schema_id', 'id=' . $srvId) : null;
  $schemaGrpId = $grpId ? DB::getDB()->queryField(self::TABLE_GRP, 'schema_id', 'id=' . $grpId) : null;
  $filter1 = "";
  $filter2 = "";
  if ($date)
  {
   $dbdate = DB::str(DB::date2str($date));
   $filter1 = " and (start_date is null or start_date<=$dbdate)";
   $filter1 .= " and (final_date is null or final_date>=$dbdate)";
   $day = $date->format('N');
   $filter2 = " and day$day='1'";
  }
  else
  {
   $filter1 = " and (final_date is null or final_date>=current_date)";
  }
  if ($time)
  {
   $filter2 .= " and $time>=start_time and $time<final_time";
  }
  $where = "schema_id in (select id from com_centre_schema where centre_id=$ctrId and global='1'{$filter1})";
  if ($schemaSrvId && $schemaGrpId)
   $where = "($where or schema_id in ($schemaSrvId,$schemaGrpId))";
  else if ($schemaSrvId && !$schemaGrpId)
   $where = "($where or schema_id=$schemaSrvId)";
  else if (!$schemaSrvId && $schemaGrpId)
   $where = "($where or schema_id=$schemaGrpId)";
  $where .= ' and discount>0 and discount<100';
  $where .= " and exists (select null from com_centre_schema where id=schema_id{$filter1})";
  $where .= $filter2;
  $discount = DB::getDB()->queryField('com_centre_schema_interval', 'max(discount)', $where);
  //echo DB::lastQuery() . "<hr>\n";
  return Util::nvl($discount, 0);
 }

 public static function getPriceWithDisc($price, $disc)
 {
  return intval(ceil($price * (100 - $disc) / 100));
 }

 public static function bookCount($date, $time, $dura, $srvId = null)
 {
  $where = Util::str($srvId, 'srv_id=', ' and ') .
    'book_date=' . DB::str(DB::date2str($date)) .
    ' and ' . ($time + $dura) . '>book_time' .
    ' and ' . $time . '<(book_time + book_dura)' .
    ' and status=\'a\'';
  $count = DB::getDB()->queryField(WPurchase::TABLE_BOOK, 'sum(greatest(1,ifnull(qty,1)))', $where);
  return $count;
 }

 /**
  * Add a new booking record to a database table
  * @param int $cltId Client
  * @param string $cltName Client name
  * @param string $phone Client phone number
  * @param int $tipId Tip id
  * @param int $srvId Service id
  * @param int $ctrId Centre id
  * @param DateTime $date Booking date
  * @param int $time Booking time (in minutes after the midnight)
  * @param int $dura Booking duration
  * @param char(1) $type Booking type ('B' - no payment, 'H' - hold, 'P' - payment)
  * @param number(18,2) $price Booking price
  * @param int $qty Booking quantity
  * @param int $masterId Booked master id
  * @param int $matresId Booked resource id
  * @return int Last inserted id
  */
 public static function book($cltId, $cltName, $phone, $tipId, $srvId, $ctrId, $date, $time, $dura,
   $type, $price, $curr, $disc, $fact, $qty, $total, $masterId, $matresId, $descr, $cmpCltId, $cmpMsgId = null)
 {//insert into com_srv_book
  //(client_id,centre_id,master_id,matres_id,srv_id,tip_id,book_date,book_time,book_dura,book_type_id,total)
  // values (46,1141,,,14623,14367,'2015-07-15',690,,B,10
  $capacity = self::getCapacity($ctrId, $srvId);
  //echo "Here $capacity<be>\n";
  if ($capacity)
  {
   $bookCount = self::bookCount($date, $time, $dura);
   $left = $capacity - ($bookCount + $qty);
   if ($left < 0)
    return $left; // Negative result means overbooking
  }
  if ($cmpMsgId)
  {
   $cmpClt = WDsc::createCmpClt($cltId, null, $cmpMsgId, $price);
   $cmpCltId = $cmpClt['cmp_clnt_id'];
  }
  if ($ctrId == WCentre::id())
  {
   $taxPrc = WCentre::taxPrc();
   $comPrc = WCentre::comPrc();
  }
  else
  {
   $ctrData = DB::getDB()->queryPairs(WCentre::TABLE_CENTRE, 'tax_prc,com_prc', 'id=' . $ctrId);
   $taxPrc = Util::intval($ctrData['tax_prc']);
   $comPrc = Util::intval($ctrData['com_prc']);
  }
  $values = array
  (
   'creator_id' => DB::int(WClient::id()),
   'domain_id' => DB::intn(WDomain::id()),
   'home' => DB::str(Base::home()),
   'client_id' => DB::intn($cltId),
   'client_name' => DB::str($cltName),
   'phone' => DB::str($phone),
   'number' => DB::strn(Util::pureNumber($phone)),
   'centre_id' => DB::intn($ctrId),
   'master_id' => DB::intn($masterId),
   'matres_id' => DB::intn($matresId),
   'srv_id' => DB::intn($srvId),
   'tip_id' => DB::intn($tipId),
   'currency_id' => DB::str($curr),
   'book_date' => DB::daten($date),
   'book_time' => DB::intn($time),
   'book_dura' => DB::intn($dura),
   'book_type_id' => DB::strn($type),
   'price' => DB::money($price),
   'disc' => DB::intn($disc),
   'fact' => DB::money($fact),
   'qty' => DB::intn($qty),
   'total' => DB::money($total),
   'ctr_tax_prc' => max(array(0, min(array(99, intval($taxPrc))))),
   'ctr_com_prc' => max(array(0, min(array(99, intval($comPrc))))),
   'descr' => DB::str($descr),
   'cmp_clnt_id' => DB::intn($cmpCltId)
  );
  if ($type == 'B')
  {
   $values['status'] = DB::str('a');
  }
  if (WDomain::local())
  {
   $values['status'] = DB::str('a');
   $values['answered'] = 'current_timestamp';
  }
  //exit(print_r($values, true));
  $db = DB::getAdminDB();
  if (!$db->insertValues(WPurchase::TABLE_BOOK, $values))
   return null;
  return $db->insert_id;
 }

 /**
  * Remove a booking record from a database table
  * @param DateTime $date Booking date
  * @param int $time Booking time (in minutes after the midnight)
  * @return boolean Success flag
  */
 public static function unbook($date, $time)
 {
  $where = array
  (
   'client_id' => WClient::id(),
   'centre_id' => self::centreId(),
   'srv_id' => self::id(),
   'book_date' => DB::str(DB::date2str($date)),
   'book_time' => $time
  );
  return DB::getAdminDB()->deleteRecords(WPurchase::TABLE_BOOK, $where);
 }
}

?>
