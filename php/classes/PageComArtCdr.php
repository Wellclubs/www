<?php

/**
 * Description of PageComArtCdr
 */
class PageComArtCdr extends PageComArt
{
 public function __construct($id)
 {
  parent::__construct($id);
  $this->useForCtr = true; ///< Show an article in 'ctr' mode only
  $this->title = 'Bookings';
  $this->tabs = array('def' => 'List view', 'week' => 'Week view'/*, 'day' => 'Day view'*/);
  $this->tabPosition = self::TABS_UPPER_LINE;
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be shown to the current client
  */
 public function testPrivs()
 {
  return PageCom::testPriv(WPriv::PRIV_VIEW_BOOK_LIST);
 }

 /**
  * Test the article for special client privileges (can be overridden)
  * @return bool True if the article can be edited by the current client
  */
 public function canBeEdited()
 {
  return true;//PageCom::testPriv(WPriv::PRIV_VIEW_BOOK_LIST);
 }

 public static function titleAnyDate()
 {
  return Lang::getSiteWord('title','Any date');
 }

 public static function titleAnyTime()
 {
  return Lang::getSiteWord('title','Any time');
 }

 public static function titleAllGroups()
 {
  return Lang::getSiteWord('title', 'All groups');
 }

 public static function titleAllMasters()
 {
  return Lang::getSiteWord('title', 'All masters');
 }

 public static function titleAllResources()
 {
  return Lang::getSiteWord('title', 'All resources');
 }

 public static function titleAllServices()
 {
  return Lang::getSiteWord('title', 'All services');
 }

 public static function titleAllClients()
 {
  return Lang::getSiteWord('title', 'All clients');
 }

 public static function mtrs()
 {
  $mtrs = WCentre::masters(null, HTTP::get('srv'));
  return $mtrs ? $mtrs : array();
 }

 public static function grps()
 {
  $ctr = PageCom::ctr();
  if (!$ctr)
   return array();
  $grps = DB::getDB()->queryArrays(WService::TABLE_GRP, 'id,name', 'centre_id=' . $ctr['id'], 'serial,id');
  if (!$grps)
   return array();
  $result = array();
  foreach ($grps as $i => $grp)
   $result[$grp['id']] = WService::grpTitle($grp['id']);
  return $result;
 }

 public static function srvs()
 {
  $ctr = PageCom::ctr();
  if (!$ctr)
   return array();
  $srvs = DB::getDB()->queryArrays(WService::TABLE_SRV, 'id,grp_id,name', 'centre_id=' . $ctr['id'], 'serial,id');
  if (!$srvs)
   return array();
  $result = array();
  foreach ($srvs as $i => $srv)
   $result[$srv['id']] = array('grp' => $srv['grp_id'], 'name' => WService::srvTitle($srv['id']));
  return $result;
 }

 protected function putSideBody()
 {
  // Datepicker
  parent::putPaneBegin('filter date ui-widget-header');
  echo '<div class="widget ui-widget-content" ani="1">';
  echo '<div class="empty text">' . self::titleAnyDate() . '</div>';
  echo '<div class="using">';
  echo '<div class="view">';
  echo '<span class="icon clear right block"><span class="ui-icon ui-icon-closethick"></span></span>';
  echo '<div class="text value"></div>';
  echo '</div>'; // view
  echo '<div class="ctrl">';
  echo '<div class="nav ui-state-default">';
  echo '<a class="icon prev left block" title="' . htmlspecialchars(Lang::getPageWord('button', 'Backward')) . '"><span class="ui-icon ui-icon-circle-triangle-w"></span></a>';
  echo '<a class="icon next right block" title="' . htmlspecialchars(Lang::getPageWord('button', 'Forward')) . '"><span class="ui-icon ui-icon-circle-triangle-e"></span></a>';
  echo '<a class="icon pin-on right block"><span class="ui-icon ui-icon-pin-s"></span></a>';
  echo '<a class="icon pin-off right block"><span class="ui-icon ui-icon-pin-w"></span></a>';
  echo '<div class="button today">' . htmlspecialchars(Lang::getPageWord('button', 'Today')) . '</div>';
  echo '</div>'; // nav
  echo '<div class="dp"></div>';
  echo '</div>'; // ctrl
  echo '</div>'; // using
  echo '</div>' . "\n"; // widget
  parent::putPaneEnd();

  // Time
  parent::putPaneBegin('filter time ui-widget-header');
  echo '<div class="widget ui-widget-content">';
  echo '<div class="empty text">' . self::titleAnyTime() . '</div>';
  echo '<div class="using">';
  echo '<span class="icon clear right block"><span class="ui-icon ui-icon-closethick"></span></span>';
  echo '<div class="text value"></div>';
  echo '</div>'; // using
  echo '</div>' . "\n"; // widget
  parent::putPaneEnd();

  // Master
  parent::putPaneBegin('filter mtr ui-widget-header');
  echo '<div class="widget ui-widget-content">';
  echo '<span class="icon clear-select right block"><span class="ui-icon ui-icon-closethick"></span></span>';
  echo '<div class="select value"><select><option value="">' . self::titleAllMasters() . '</option></select></div>';
  echo '</div>' . "\n"; // widget
  parent::putPaneEnd();

  // Service
  parent::putPaneBegin('filter srv ui-widget-header');
  echo '<div class="widget ui-widget-content">';
  echo '<div class="empty text">' . self::titleAllServices() . '</div>';
  echo '<div class="using">';
  echo '<span class="icon clear right block"><span class="ui-icon ui-icon-closethick"></span></span>';
  echo '<div class="text value"></div>';
  echo '</div>'; // using
  echo '</div>' . "\n"; // widget
  parent::putPaneEnd();

  // Client
  parent::putPaneBegin('filter clt ui-widget-header');
  echo '<div class="widget ui-widget-content">';
  echo '<div class="empty text">' . self::titleAllClients() . '</div>';
  echo '<div class="using">';
  echo '<span class="icon clear right block"><span class="ui-icon ui-icon-closethick"></span></span>';
  echo '<div class="text value"></div>';
  echo '</div>'; // using
  echo '</div>' . "\n"; // widget
  parent::putPaneEnd();
 }

 /**
  * Add to the URI path a tab-specific suffix part
  * @param string $path Base part for all tabs of this article
  * @param string $id Id of the tab to make a specific href
  * @return string Correct tab-specific href
  */
 protected function getTabHref($path, $id)
 {
  $href = parent::getTabHref($path, $id);
  $uri = Util::parseUrl($href);
  $uri = Util::repeatUrlParam($uri, 'date');
  //$uri = Util::repeatUrlParam($uri, 'tmin');
  //$uri = Util::repeatUrlParam($uri, 'tmax');
  //$uri = Util::repeatUrlParam($uri, 'srv');
  //$uri = Util::repeatUrlParam($uri, 'grp');
  //$uri = Util::repeatUrlParam($uri, 'clt');
  return Util::buildUrl($uri);
 }

 private static $dbdate0str;
 private static $dbdate1str;
 protected static function makeWhere(&$data)
 {
  $where = 'centre_id=' . WCentre::id();
  $date = Util::str2date(HTTP::get('date'));
  if (!$date && (Base::tab() != 'def'))
   $date = new DateTime();
  if ($date)
   $data['date'] = Util::date2str($date);
  if (Base::tab() == 'week')
  {
   $day = intval($date->format('N'));
   $firstDay = WDomain::firstDay();
   if ($day > $firstDay)
    $date->sub(new DateInterval('P' . ($day - $firstDay) . 'D'));
   $date1 = new DateTime($date->format('Y-m-d'));
   $date1->add(new DateInterval('P6D'));
   self::$dbdate0str = DB::str(DB::date2str($date));
   self::$dbdate1str = DB::str(DB::date2str($date1));
   $where .= ' and book_date between ' . self::$dbdate0str . ' and ' . self::$dbdate1str;
   $data['date0'] = Util::date2str($date);
   $data['date1'] = Util::date2str($date1);
  }
  else if ($date)
  {
   $where .= ' and book_date=' . DB::str(DB::date2str($date));
  }
  $tmin = Util::intval(HTTP::get('tmin'));
  if ($tmin != null)
   $where .= ' and (book_time+book_dura)>' . $tmin;
  $tmax = Util::intval(HTTP::get('tmax'));
  if ($tmax != null)
  {
   if (($tmin != null) && ($tmax < $tmin + 30))
    $tmax = $tmin + 30;
   $where .= ' and book_time<' . $tmax;
  }
  $srv = Util::intval(HTTP::get('srv'));
  if ($srv != null)
   $where .= ' and srv_id=' . $srv;
  else
  {
   $grp = Util::intval(HTTP::get('grp'));
   if ($grp != null)
    $where .= ' and (select grp_id from ' . WService::TABLE_SRV . ' where id=srv_id)=' . $grp;
  }
  $clt = Util::intval(HTTP::get('clt'));
  if ($clt != null)
   $where .= ' and client_id=' . $clt;
  $where .= ' and status=\'a\'';
  $where .= ' and showup is null';
  if ($clt !== null)
   $data['cltT'] = WClient::getClientNameAndEmail($clt);
  return $where;
 }

 private static function times()
 {
  $result = array();
  $ptmin = HTTP::get('tmin');
  $ptmax = HTTP::get('tmax');
  if (($ptmin != '') && ($ptmax != '') && ($ptmax <= $ptmin))
   $ptmax = $ptmin + 30;
  //$where .= Util::str($ptmin, ' and book_time>=');
  //$where .= Util::str($ptmax, ' and book_time<');
  //$flds = DB::getDB()->queryFields(WPurchase::TABLE_BOOK, 'min(book_time),max(book_time+book_dura)', $where);
  $flds = DB::getDB()->queryFields(WCentre::TABLE_CENTRE_SCHED, 'min(open_min),max(close_min)', 'centre_id=' . WCentre::id());
  $tmin = intval($flds[0]);
  $tmax = intval($flds[1]);
  if (($ptmax != '') && ($ptmax < $tmax))
   $tmax = $ptmax;
  for ($t = $tmin; $t < $tmax; $t += 30)
   $result[] = intval($t);
  return $result;
 }

 private static function bgcolor($q)
 {
  if ($q <= 2)
   return 'f0f4f8';
  if ($q <= 5)
   return 'ecf2f7';
  if ($q <= 10)
   return 'e8f0f6';
  if ($q <= 20)
   return 'e4eef5';
  return 'e0ecf4';
 }

 protected function putTabBodyDef()
 {
  $cols = array
  (
   array('width' => 100, 'text' => 'Date', 'field' => 'date', 'class' => '')
  ,array('width' => 50, 'text' => 'Time', 'field' => 'time')
  ,array('width' => 50, 'text' => 'Dur.', 'field' => 'dura')
  ,array('width' => 50, 'text' => 'Show up', 'class' => 'btn', 'action' => 'showup')
  ,array('width' => 200, 'text' => 'Service', 'field' => 'service')
  ,array('width' => 200, 'text' => 'Client', 'field' => 'name')
  ,array('width' => 50, 'text' => 'Qty', 'field' => 'qty')
  ,array('width' => 50, 'text' => 'No Show', 'class' => 'btn', 'action' => 'noshow')
  );
  self::putTable($cols, 'No bookings meet your filter criteria', true, false, null, 'hdr-left two-color row-click');
 }

 protected function ajaxTabDef()
 {
  $db = DB::getDB();
  $data = array();
  $where = self::makeWhere($data);
  $fields = 'id,client_id,client_name,srv_id,book_date,book_time,book_dura,price,disc,fact,qty,total,curr';
  $order = 'book_date,book_time,srv_id,client_id,created desc';
  $rows = $db->queryArrays(WPurchase::TABLE_BOOK, $fields, $where, $order, HTTP::tlimit(50), HTTP::tstart());
  //$data['sql'] = DB::lastQuery();
  $bookings = array();
  if ($rows)
  {
   foreach ($rows as $row)
   {
    $booking = array();
    $booking['id'] = $row['id'];
    $booking['date'] = Util::date2str(DB::str2date($row['book_date']));
    $booking['time'] = Util::min2str($row['book_time']);
    $booking['dura'] = Util::min2str($row['book_dura']);
    $booking['service'] = WService::getTitle($row['srv_id']);
    $booking['name'] = $row['client_name'];
    $booking['price'] = $row['price'];
    $booking['disc'] = $row['disc'];
    $booking['fact'] = $row['fact'];
    $booking['qty'] = $row['qty'];
    $booking['total'] = $row['total'];
    $booking['curr'] = $row['curr'];
    $bookings[] = $booking;
   }
  }
  $data['bookings'] = $bookings;
  $data['masters'] = self::mtrs();
  PageCom::addDataToAjax($data);
 }

 private static function processActionShow($value)
 {
  $rowid = HTTP::paramInt('rowid');
  $table = WPurchase::TABLE_BOOK;
  $field = 'showup';
  $values = array($field => DB::str($value));
  $where = 'id=' . $rowid;
  if (!DB::getAdminDB()->modifyFields($table, $values, $where) ||
    (DB::getAdminDB()->queryField($table, $field, $where) != $value))
   return PageComArt::actionFailDBUpdate();
  return true;
 }

 protected function processActionTabDefShowup()
 {
  return self::processActionShow('Y');
 }

 protected function processActionTabDefNoshow()
 {
  return self::processActionShow('N');
 }

 protected function putTabBodyWeek()
 {
  $cols = array();
  $firstDay = WDomain::firstDay();
  $cols[] = array('width' => '', 'text' => 'Time', 'field' => 't', 'class' => 'center nw');
  for ($i = 1; $i <= 7; ++$i)
   $cols[] = array('width' => '13%', 'title' => Lang::dayOfWeekLong($i - 1 + $firstDay), 'field' => 'd' . $i,
     'class' => 'bg center nw uri ax databtn img time', 'uri' => 'u' . $i, //'bg' => 'b' . $i,
     'databtn-style' => 'width:50%', 'databtn-text' => 't' . $i);
  self::putTable($cols, null, true, false, null, 'book-sheet');
 }

 protected function ajaxTabWeek()
 {
  $db = DB::getDB();
  $data = array();
  $where1 = self::makeWhere($data);
  $where2 = 'centre_id=' . WCentre::id() .
    ' and book_date between ' . self::$dbdate0str . ' and ' . self::$dbdate1str;
  $times = self::times();
  //$data['sql'] = DB::lastQuery();
  $capacity = WService::getCapacity(WCentre::id(), HTTP::get('srv'));
  $sched = WCentre::times();
  //print_r(DB::lastQuery());
  //print_r($sched);
  //$uri0 = 'ctr-' . WCentre::id() . '/cdr/day/?date=';
  $uri1 = 'ctr-' . WCentre::id() . '/cdr/?date=';
  $uri2 = Util::str(HTTP::get('mtr'), '&mtr=');
  $uri2 .= Util::str(HTTP::get('grp'), '&grp=');
  $uri2 .= Util::str(HTTP::get('srv'), '&srv=');
  $uri2 .= Util::str(HTTP::get('clt'), '&clt=');
  $date0 = $data['date0'];
  $dates = array('d1' => $date0/*, 'u1' => $uri0 . $date0 . $uri2*/);
  $date = Util::str2date($date0);
  $interval = new DateInterval('P1D');
  for ($d = 2; $d <= 7; ++$d)
  {
   $dates['d' . $d] = Util::date2str($date->add($interval));
   //$dates['u' . $d] = $uri0 . $dates['d' . $d] . $uri2;
  }
  $bookings = array();
  $bookings[] = $dates;
  $headers = array();
  for ($d = 1; $d <= 7; ++$d)
   $headers['d' . $d] = Lang::getWord('title', 'existing / available');
  $bookings[] = $headers;
  foreach ($times as $t)
  {
   $booking = array('id' => $t, 't' => Util::min2str($t) . ' - ' . Util::min2str($t + 30));
   $query1 = self::getQueryForWeek($where1, $t);
   //$data['sql' . $t] = DB::lastQuery();
   $query2 = self::getQueryForWeek($where2, $t);
   $row1 = $db->queryPairs($query1, 'd1,d2,d3,d4,d5,d6,d7');
   $row2 = $db->queryPairs($query2, 'd1,d2,d3,d4,d5,d6,d7');
   //$data['sql' . $t] = DB::lastQuery();
   for ($d = 1; $d <= 7; ++$d)
   {
    $q1 = intval($row1['d' . $d]);
    $q2 = intval($row2['d' . $d]);
    if ($q1)
    {
     $booking['d' . $d] = $q1;
     $booking['u' . $d] = $uri1 . $dates['d' . $d] . '&tmin=' . $t . '&tmax=' . $t . $uri2;
    }
    //$booking['b' . $d] = '41bfb6';//self::bgcolor($q);
    $schedRow = Util::item($sched, $d);
    if (($capacity === null) || !$schedRow ||
      ($schedRow['o'] != null) && ($t < $schedRow['o']) ||
      ($schedRow['c'] != null) && ($t > $schedRow['c']) ||
      ($capacity > 0) && ($q2 >= $capacity))
     $booking['t' . $d] = '';
    else
     //$booking['t' . $d] = Lang::getWord('button', 'Book') . ' (' . ($capacity - $q2) . ')';
     $booking['t' . $d] = $capacity - $q2;
   }
   $bookings[] = $booking;
  }
  $data['bookings'] = $bookings;
  $data['masters'] = self::mtrs();
  PageCom::addDataToAjax($data);
 }

 private function getQueryForWeek($where, $t)
 {
   return "(select" .
     " sum(case when d=0 then q end)d1" .
     ",sum(case when d=1 then q end)d2" .
     ",sum(case when d=2 then q end)d3" .
     ",sum(case when d=3 then q end)d4" .
     ",sum(case when d=4 then q end)d5" .
     ",sum(case when d=5 then q end)d6" .
     ",sum(case when d=6 then q end)d7" .
     " from(" .
     "select book_date-cast(" . self::$dbdate0str . " as date)d,qty q" .
     " from " . WPurchase::TABLE_BOOK . " a" .
     " where " . $where . ' and book_time<=' . $t . ' and (book_time+book_dura)>' . $t .
     ")b)c";
 }

 protected function putTabBodyDay()
 {
  $cols = array();
  $cols[] = array('width' => 50, 'text' => 'Time', 'field' => 't', 'class' => 'center nw');
  $grps = self::grps();
  foreach ($grps as $id => $name)
   $cols[] = array('width' => '50%', 'title' => $name, 'field' => 'g' . $id,
     'class' => 'bg center nw uri ax', 'uri' => 'u' . $id, 'bg' => 'b' . $id);
  self::putTable($cols, null, true, false, null, '');
 }

 protected function ajaxTabDay()
 {
  $db = DB::getDB();
  $data = array();
  $where = self::makeWhere($data);
  $times = self::times();
  //$data['sql'] = DB::lastQuery();
  $grps = self::grps();
  $capacity = WService::getCapacity(WCentre::id(), HTTP::get('srv'));
  if ($capacity !== null)
   $capacity = ' / ' . $capacity;
  $query1 = '(select ';
  $fields = '';
  $comma = '';
  foreach ($grps as $id => $name)
  {
   $query1 .= $comma . 'sum(case when g=' . $id . ' then q end)g' . $id;
   $fields .= $comma . 'g' . $id;
   $comma = ',';
  }
  $query1 .= ' from(' .
    'select (select grp_id from ' . WService::TABLE_SRV . ' where id=a.srv_id) g,qty q' .
    ' from ' . WPurchase::TABLE_BOOK . ' a' .
    ' where ' . $where;
  $query2 = ')b)c';
  //$rows = $db->queryMatrix($query, $fields);
  $data['sql'] = DB::lastQuery();
  $uri1 = 'ctr-' . WCentre::id() . '/cdr/?date=' . $data['date'];
  $uri2 = Util::str(HTTP::get('clt'), '&clt=');
  $bookings = array();
  foreach ($times as $t)
  {
   $booking = array('id' => $t, 't' => Util::min2str($t) . ' - ' . Util::min2str($t + 30));
   $query = $query1 . ' and book_time<=' . $t . ' and (book_time+book_dura)>' . $t . $query2;
   $row = $db->queryPairs($query, $fields);
   $data['sql' . $t] = DB::lastQuery();
   foreach ($grps as $id => $name)
   {
    $q = $row['g' . $id];
    if (!intval($q))
     continue;
    $booking['g' . $id] = $q . $capacity;
    $booking['b' . $id] = self::bgcolor($q);
    $booking['u' . $id] = $uri1 . '&tmin=' . $t . '&tmax=' . $t . '&grp=' . $id . $uri2;
   }
   $bookings[] = $booking;
  }
  $data['bookings'] = $bookings;
  $data['masters'] = self::mtrs();
  PageCom::addDataToAjax($data);
 }

 protected function processActionTips()
 {
  $data = array();
  WService::initCurrent(HTTP::get('srv'));
  if (!WService::id())
   return self::actionError('No service specified');
  $date = Util::str2date(HTTP::get('date'));
  if (!$date)
   return self::actionError('No valid date specified');
  $time = Util::intval(HTTP::get('time'));
  if (($time == null) || ($time < 0) || ($time >= 1440) || (($time % 30) != 0))
   return self::actionError('No valid time specified');
  $tips = PageBookSrv::tips();
  foreach ($tips as $tip)
  {
   $dura = intval($tip['duration']);
   /*
   $slot = null;
   foreach ($tip['slots'] as $s)
   {
    if ($s['a'] != $time)
     continue;
    $slot = $s;
    break;
   }
   if (!$slot)
   {
    echo json_encode(array('result' => 'error', 'error' => 'Invalid time specified'));
    return true;
   }
   $title = $slot['aa'] . '-' . $slot['bb'] . ' ' . $slot['p'] . ' ' . $tip['title'];
   */
   $title = Util::min2str($time) . ' - ' . Util::min2str($time + $dura) . ' | ' .
     $tip['price'] . ' ' . WCentre::currency() . ' | "' . $tip['title'] . '"';
   $pos = array('name' => $title);
   $data[$tip['id']] = $pos;
  }
  PageCom::addToAjaxData('tips', $data, true);
  return true;
 }

 protected function processActionBook()
 {
  $dateT = HTTP::param('date');
  $date = Util::str2date($dateT);
  if (!$date)
   return self::actionError("Invalid date specified: '$date'");
  $time = HTTP::paramInt('time');
  $name = HTTP::param('name');
  $phone = HTTP::param('phone');
  $tipId = HTTP::paramInt('tip');
  $tipData = DB::getDB()->queryPairs(WService::TABLE_TIP, 'centre_id,srv_id,duration,price', 'id=' . $tipId);
  if (!$tipData)
   return self::actionError("Invalid tip specified: '$tipId'");
  $ctrId = $tipData['centre_id'];
  if ($ctrId != WCentre::id())
   return self::actionError("Invalid centre: '$ctrId' vs '" . WCentre::id()) . "'";
  $srvId = $tipData['srv_id'];
  $dura = $tipData['duration'];
  $type = 'M';
  $price = $tipData['price'];
  $qty = 1;
  $masterId = null;
  $matresId = null;
  // Check for availability
  //
  $disc = 0;
  $fact = $price;
  $total = $price;
  $curr = WCentre::currency();
  $timeT = Util::min2str($time);
  $ctrT = WCentre::getTitle($ctrId);
  $srvT = WService::getTitle($srvId);
  //var desc=app.pay.ctrT+'; '+app.pay.srvT+'; '+app.pay.dateT+'; '+app.pay.timeT+'; '+
  // app.pay.dura+'; '+app.pay.qty+'x'+app.pay.fact+'; '+app.pay.total+' '+app.pay.curr;
  $descr = "$ctrT $srvT $dateT $timeT $dura {$qty}x{$fact} $total $curr";
  // book!
  $bookId = WService::book(null, $name, $phone, $tipId, $srvId, $ctrId, $date, $time, $dura,
    $type, $price, $curr, $disc, $fact, $qty, $total, $masterId, $matresId, $descr, null);
  if (!$bookId)
   return self::actionFailDBInsert('Error adding booking record');
  if ($bookId < 0)
  {
   $text = ($qty > 1) ?
     Lang::getWord('error', 'The number of bookings exceeds the maximum', 'pay') :
     Lang::getWord('error', 'There are no slots available', 'pay');
   return self::actionError($text);
  }
  return true;
 }

}

?>
