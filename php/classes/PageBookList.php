<?php

/**
 * Description of PageBookList
 */
class PageBookList
{
 private static $cat = array();
 private static $prc = array();
 private static $soc = array();
 private static $loc = '';
 private static $locT = '';
 private static $bnd = '';
 private static $date = '';
 private static $time = '';

 public static function locT() { return self::$locT; }
 public static function bnd() { return self::$bnd; }
 public static function date() { return self::$date; }
 public static function time() { return self::$time; }

 public static function initFilter()
 {
  $parts = Base::parts();
  foreach ($parts as $part)
  {
   if (fnmatch('cat-*', $part))
    self::$cat = explode('-', substr($part, 4));
   else if (fnmatch('prc-*', $part))
    self::$prc = explode('-', substr($part, 4));
   else if (fnmatch('soc-*', $part))
    self::$soc = explode('-', substr($part, 4));
   else if (fnmatch('loc-*', $part))
    self::$loc = substr($part, 4);
   else if (fnmatch('locT-*', $part))
    self::$locT = '' . substr($part, 5);
   else if (fnmatch('bnd-*', $part))
    self::$bnd = '' . substr($part, 4);
   else if (fnmatch('date-*', $part))
    self::$date = substr($part, 5);
   else if (fnmatch('time-*', $part))
    self::$time = substr($part, 5);
  }
 }

 public static function testCat($cat)
 {
   if (array_search($cat, self::$cat) !== false)
    return true;
   if (count(self::$prc) == 0)
    return false;
   return 0 < (int)DB::getDB()->queryField('biz_menu_prc', 'count(*)', 'hidden is null and cat_id=' . $cat . ' and id in (' . implode(',', self::$prc) . ')');
 }

 public static function testPrc($prc)
 {
  return array_search($prc, self::$prc) !== false;
 }

 public static function testSoc($soc)
 {
  return array_search($soc, self::$soc) !== false;
 }

 private static function addToArray(array &$arr, $data)
 {
  if ($data)
   $arr[] = $data;
 }

 public static function subtitle()
 {
  $titles = array();
  if (count(self::$soc) == 1)
   self::addToArray($titles, Lang::getDBValue(WSGrp::TABLE_FILTER_ABC, 'title', 'filter_id=' . self::$soc[0]));
  if (count(self::$prc) == 1)
   self::addToArray($titles, Lang::getDBValue('biz_menu_prc_abc', 'title', 'prc_id=' . self::$prc[0]));
  if (count(self::$cat) == 1)
   self::addToArray($titles, Lang::getDBValue('biz_menu_cat_abc', 'title', 'cat_id=' . self::$cat[0]));
  if (strlen(self::$bnd) > 0)
   self::addToArray($titles, self::$bnd);
  if (strlen(self::$loc) > 0)
   self::addToArray($titles, self::$locT);
  if (!count($titles))
   self::addToArray($titles, Lang::getPageWord('title', 'All offers'));
  if (strlen(self::$date) > 0)
   self::addToArray($titles, self::$date);
  return count($titles) ? implode(', ', $titles) : null;
 }

 public static function filter()
 {
  $result = array();
  $result['date'] = self::$date;
  $result['time'] = self::$time;
  $result['bnd'] = self::$bnd;
  $result['loc'] = self::$loc;
  if (self::$loc != '')
   $result['locT'] = self::$locT;
  $result['cat'] = self::$cat;
  $result['prc'] = self::$prc;
  $result['soc'] = self::$soc;
  return $result;
 }

 public static function result()
 {
  if (Base::mode() != 'list')
   return null;

  $result = array();

  $skip = (int)HTTP::param('skip', 0);

  $db = DB::getAdminDB();

  // Base query
  $query = 'select a.id,b.prc_id,a.centre_id' .
    ',(select cat_id from biz_menu_prc where id=b.prc_id)cat_id' .
    ' from com_menu_srv a,com_menu_srv_prc b' .
    ' where b.srv_id=a.id and exists (select null from ' . WService::TABLE_TIP . ' where srv_id=b.srv_id and duration>0 and price>0)';

  // User filter (except of cat and prc filters)
  $query .= ' and a.centre_id in (select id from com_centre where hidden is null';
  $query .= WDomain::filter();
  if (self::$bnd != '')
  {
   $whereName = PageBook::makeACWhere('name', self::$bnd);
   $whereTitle = PageBook::makeACWhere('title', self::$bnd);
   $query .= ' and (' .
     "brand_id in (select brand_id from com_brand_abc where $whereTitle)" .
     ' or ' .
     "brand_id in (select id from com_brand where $whereName)" .
     ' or ' .
     "id in (select centre_id from com_centre_abc where $whereTitle)" .
     ' or ' .
     $whereName .
     ')';
  }
  if (self::$loc != '')
  {
   $bounds = explode('|', self::$loc);
   if ((count($bounds) == 4) && is_numeric($bounds[0]) && is_numeric($bounds[1]) && is_numeric($bounds[2]) && is_numeric($bounds[3]))
   {
    $y0 = $bounds[0] - $bounds[1];
    $y1 = $bounds[0] + $bounds[1];
    $x0 = $bounds[2] - $bounds[3];
    $x1 = $bounds[2] + $bounds[3];
    $query .= " and lat between $y0 and $y1 and lng between $x0 and $x1";
   }
  }
  $query .= ')';

  if ($skip == 0)
  {
   // Prc counts
   $counts = $db->queryRecords('(' . $query . ') a group by prc_id', 'prc_id,count(distinct centre_id)');
   //return "[/* " . DB::lastQuery() . " */]\n";
  }

  // Add sgrp filters
  if (count(self::$soc))
  {
   $sgrps = WSGrp::filterGroups(self::$soc);
   foreach ($sgrps as $sgrpId => $sgrp)
   {
    $expr = "coalesce(" .
      "(select active from com_menu_srv_sgrp where srv_id=a.id and sgrp_id=$sgrpId)," .
      "(select active from com_centre_sgrp where centre_id=a.centre_id and sgrp_id=$sgrpId)" .
      ($sgrp['active'] ? ",'1'" : '') .
      ")";
    $query .= " and $expr=" . ($sgrp['include'] == 'Y' ? '1' : '0');
   }
  }

  // Add cat and prc filters
  if ((count(self::$cat) > 0) || (count(self::$prc) > 0))
  {
   $query .= ' and (';
   $query .= (count(self::$prc) > 1) ? ('prc_id in(' . implode(',', self::$prc) . ')') :
    ((count(self::$prc) == 1) ? ('prc_id=' . self::$prc[0]) : '');
   if ((count(self::$cat) > 0) && (count(self::$prc) > 0))
    $query .= ' or ';
   if (count(self::$cat) > 0)
    $query .= 'prc_id in (select id from biz_menu_prc where ' .
     ((count(self::$cat) > 1) ? ('cat_id in(' . implode(',', self::$cat) . ')') : ('cat_id=' . self::$cat[0])) . ')';
   $query .= ')';
  }

  //echo '<!-- ' . $query . ' -->' . "\n";

  // Build a result table
  $query = 'create temporary table if not exists result as ' . $query;

  if ($db->query($query) === false)
   return "Error creating table: $query";

  if ($skip == 0)
  {
   if ($counts)
   {
    $totals = $db->queryFields('result', 'count(*),count(distinct centre_id)');
    $header = Lang::getPageWord('title', 'Choose from $OFFERS$ offers and $VENUES$ venues');
    $result['header'] = str_replace(array('$OFFERS$', '$VENUES$'), array($totals[0], $totals[1]), $header);
    $result['count'] = $totals[1];

    $cnts = array();
    foreach ($counts as $count)
     $cnts['p' . $count[0]] = $count[1];
    $result['counts'] = $cnts;
   }
   else
   {
    $result['header'] = Lang::getPageWord('title', 'No offers correspond to the search criteria');
    $result['count'] = 0;
   }
  }
  else
   $result['skip'] = $skip;

  //$minTitle = Lang::getPageWord('time', 'min.');

  $centres = $db->queryRecords('result group by centre_id', 'centre_id', '', 'count(*) desc', 20, $skip);
  //$result['sql'] = . DB::lastQuery();
  if ($centres)
  {
   foreach ($centres as $i => $centre)
   {
    $ctrId = $centre[0];
    $currId = $db->queryField(WCentre::TABLE_CENTRE, 'currency_id', 'id=' . $ctrId);
    //$currCode = WCurrency::getCode($currId);
    //$currCode = WCentre::currency();
    //if (!strlen($currCode))
    // $currCode = $currId;
    $srvs = $db->queryRecords('result', 'id', 'centre_id=' . $ctrId, '', 3);
    if ($srvs)
    {
     foreach ($srvs as $j => $srv)
     {
      $srvId = $srv[0];
      //$title = Lang::getDBValue('com_menu_srv_abc', 'title', 'srv_id=' . $id);
      $title = WService::srvTitle($srvId);
      if (!$title)
      {
       $prcId = DB::getDB()->queryField(WService::TABLE_SRV_PRC, 'prc_id', 'srv_id=' . $srvId, 'serial');
       if ($prcId)
        $title = Lang::getDBTitle('biz_menu_prc', 'prc', $prcId);
      }
      $tip = DB::getDB()->queryFields(WService::TABLE_TIP, 'price,duration',
        'srv_id=' . $srvId . ' and duration>0 and price>0', 'price');
      if (!$tip)
       continue;
      $price = $tip[0];
      $srv = array('id' => $srvId, 'title' => $title, 'price' => Lang::strInt($price));
      if ($tip[1])
       $srv['dura'] = $tip[1];
      // Discount?
      $grpId = DB::getDB()->queryField(WService::TABLE_SRV, 'grp_id', 'id=' . $srvId);
      $disc = WService::getMaxDisc($srvId, $grpId, $ctrId);
      if ($disc)
       $srv['fact'] = Lang::strInt(WService::getPriceWithDisc($price, $disc));
      $srvs[$j] = $srv;
     }
    }
    else
     $srvs = array();
    //
    $centre = array('id' => $ctrId, 'srvs' => $srvs);
    if (strlen($currId))
     $centre['curr'] = WCurrency::makeObjs($currId);
    //
    $title = WCentre::getTitle($ctrId);
    if ($title)
     $centre['title'] = $title;
    //
    $addr = DB::getDB()->queryField('com_centre', 'address', 'id=' . $ctrId);
    if ($addr)
     $centre['addr'] = $addr;
    //
    $image = WCentre::logoInfo($ctrId);
    if ($image)
     $centre['image'] = $image;
    //
    $centres[$i] = $centre;
   }
  }
  $result['centres'] = $centres ? $centres : array();
  return $result;
 }
}

?>
