<?php

class PageBook extends Page
{
 public function __construct()
 {
  $this->modes = array('home', 'list', 'pay', 'bookings', 'biz', 'about', 'faq', 'policy');
  $this->indexes = array('ctr', 'bnd', 'srv', 'clt', 'art');
 }

 public function showPage()
 {
  WClient::initCurrent(); ///< In any mode

  // Prepare data
  if (Base::mode() == 'list')
  {
   PageBookList::initFilter();
  }
  else if (Base::mode() == 'ctr')
  {
   WCentre::initCurrent(Base::index());
   if (!WCentre::id() || WCentre::hidden())
    return false;
  }
  else if (Base::mode() == 'bnd')
  {
   WBrand::initCurrent(Base::index());
   if (!WBrand::id())
    return false;
  }
  else if (Base::mode() == 'srv')
  {
   WService::initCurrent(Base::index());
   if (!WService::id())
    return false;
   WCentre::initCurrent(WService::centreId());
   if (!WCentre::id() || WCentre::hidden())
    return false;
  }
  else if (Base::mode() == 'pay')
  {
  }
  else if (Base::mode() == 'clt')
  {
   if (!WClient::initView(Base::index()))
    return false;
  }
  else if (Base::mode() == 'biz')
  {
   if (WClient::id() && (WClient::me()->isMaster() || WClient::me()->isMember()))
   {
    $uri = Base::bas() . 'com/';
    if (Base::ajax())
     echo "{'uri':'$uri'}";
    else
     header("Location: $uri");
    return true;
   }
  }
  else if (Base::mode() == 'about')
  {
  }
  else if (Base::mode() == 'faq')
  {
  }
  else if (Base::mode() == 'policy')
  {
  }

  /// Commands available in any mode
  switch (Base::cmd())
  {
   case 'auth' :
    if (WClient::auth(HTTP::get('net')))
     return true;
    break;
   case 'signup' :
    echo json_encode(WClient::signup(HTTP::post('firstname'), HTTP::post('lastname'), HTTP::post('email'), HTTP::post('pass'), HTTP::post('href')));
    return true;
   case 'restore' :
    echo json_encode(WClient::restore(HTTP::post('email'), HTTP::post('href')));
    return true;
   case 'passwd' :
    echo json_encode(WClient::passwd(HTTP::post('old'), HTTP::post('pass')));
    return true;
   case 'login' :
    echo json_encode(WClient::login(HTTP::post('email'), HTTP::post('pass')));
    return true;
   case 'listed' :
    echo json_encode(WClient::listed(HTTP::get('centre'), HTTP::get('addr'), HTTP::get('phone')));
    return true;
   case 'logout' :
    echo json_encode(WClient::logout());
    return true;
   case 'ac_brand' :
    echo self::autocompleteBrand(HTTP::get('q'));
    return true;
   case 'ac_mproc' :
    echo self::autocompleteMenuProc(HTTP::get('q'));
    return true;
   case 'meta1' :
    echo json_encode(self::metadata1());
    return true;
   case 'meta2' :
    echo json_encode(self::metadata2());
    return true;
   //default :
   // return false;
  }

  // Reviews and comments to them
  if ((Base::mode() == 'ctr') || (Base::mode() == 'srv'))
  {
   switch (Base::cmd())
   {
    case 'review' :
     echo json_encode(WCentre::review());
     return true;
    case 'comment' :
     echo json_encode(WCentre::comment());
     return true;
    case 'cavil' :
     echo json_encode(WCentre::cavil());
     return true;
   }
  }

  // Commands available in mode 'srv'
  if (Base::mode() == 'srv')
  {
   switch (Base::cmd())
   {
    case 'tips' :
     //echo json_encode(array('result' => 'OK', 'tips' => PageBookSrv::tips()));
     echo JSON::encode(array('result' => 'OK', 'tips' => PageBookSrv::tips()));
     return true;
    case 'unbook' :
     echo json_encode(PageBookSrv::unbook());
     return true;
   }
  }

  // Commands available in mode 'pay'
  if (Base::mode() == 'pay')
  {
   switch (Base::cmd())
   {
    case 'order' :
     echo json_encode(PageBookSrv::book(false));
     return true;
    case 'book' :
     echo json_encode(PageBookSrv::book(true));
     return true;
    case 'cb' :
     return PageBookSrv::setPayStatus();
    case 'unbook' :
     echo json_encode(PageBookSrv::unbook());
     return true;
   }
  }

  // Commands available in mode 'clt'
  if (Base::mode() == 'clt')
  {
   switch (Base::cmd())
   {
    case 'list' :
     PageBookClt::getList(HTTP::get('field'));
     return true;
    case 'change' :
     PageBookClt::changeField(HTTP::get('field'), HTTP::get('value'));
     return true;
    case 'upload' :
     PageBookClt::uploadField(HTTP::get('field'));
     return true;
    case 'clear' :
     PageBookClt::clearField(HTTP::get('field'));
     return true;
   }
  }

  // Make title (optional)
  $title = '';
  switch (Base::mode())
  {
   case 'home' :
    $title = Lang::getPageWord('title', 'Booking for Spa & Beauty Treatments');
    break;
   case 'list' :
    $title = PageBookList::subtitle();
    break;
   case 'biz' :
    $title = Lang::getPageWord('title', 'Free salon catalogue');
    break;
   case 'ctr' :
    $title = WCentre::typeTitle() . ' ' . WCentre::title();
    break;
   case 'bnd' :
    $title = WBrand::title();
    break;
   case 'srv' :
    $title = WService::title();
    //$title = Lang::getPageWord('title', 'Service') . ' ' . Base::index();
    break;
   case 'clt' :
    $title = WClient::view()->getName();
    break;
   /*case 'art' :
    $title = Lang::getPageWord('title', 'Article') . ' ' . Base::index();
    break;*/
   case 'about' :
    $title = Lang::getPageWord('title', 'The company');
    break;
   case 'faq' :
    $title = Lang::getPageWord('title', 'Frequently asked questions');
    break;
   case 'policy' :
    $title = Lang::getPageWord('title', 'Terms and conditions');
    break;
   default :
  }
  // Specify full title
  Base::setTitle($title);

  if (Base::ajax())
  {
   echo JSON::encode(self::getAjaxData());
   return true;
  }

  return Base::executeTextFile('php/book.php');
 }

 private function getAjaxData()
 {
  switch (Base::mode())
  {
  case 'list' :
   if (array_key_exists('skip', $_REQUEST))
    return PageBookList::result();
   break;
  }
  // Go to page
  $result = array();
  $result['title'] = Base::fullTitle();
  $result['subtitle'] = Base::title();
  $result['mode'] = Base::mode();
  switch (Base::mode())
  {
  case 'list' :
   $result['filter'] = PageBookList::filter();
   $listResult = PageBookList::result();
   if (is_array($listResult))
    $result['result'] = $listResult;
   else
    $result['failure'] = $listResult;
   break;
  case 'ctr' :
   PageBookCtr::fillResult($result);
   break;
  case 'bnd' :
   PageBookBnd::fillResult($result);
   break;
  case 'srv' :
   PageBookSrv::fillResult($result);
   break;
  case 'pay' :
   $result['pay'] = WPurchase::getDataFromHttpParams(true);
   break;
  case 'clt' :
   $result['clt'] = PageBookClt::getPageData();
   break;
  case 'bookings' :
   $result['bookings'] = PageBookClt::getActiveBookings();
   break;
  case 'faq' :
   $result['faq'] = self::getFAQs();
   break;
  case 'policy' :
   foreach (array('terms', 'booking', 'privacy') as $topic)
    $result[$topic] = self::getPolicyTopicText($topic);
   //$result['terms'] = self::getPolicyTopicText('terms');
   break;
  }
  return $result;
 }

 public static function getDescr()
 {
  if (Base::mode() == 'ctr')
   return WCentre::descr();
  if (Base::mode() == 'srv')
   WService::descr();
  if (Base::mode() == 'bnd')
   WBrand::descr();
  return Lang::getText('meta', 'description');
 }

 public static function getKeyWords()
 {
  if (Base::mode() == 'ctr')
   return DB::getDB()->queryField(WCentre::TABLE_CENTRE, 'keywords', 'id=' . Base::index());
  if (Base::mode() == 'srv')
   return DB::getDB()->queryField(WService::TABLE_SRV, 'keywords', 'id=' . Base::index());
  if (Base::mode() == 'bnd')
   return DB::getDB()->queryField(WBrand::TABLE_BRAND, 'keywords', 'id=' . Base::index());
  return Lang::getText('meta', 'keywords');
 }

 public static function getBgSize()
 {
  $fields = DB::getDB()->queryFields('biz_menu_bg', 'width,height', 'width>0 and height>0 and hidden is null', 'id');
  return $fields ? $fields : array(1000, 600);
 }

 public static function makeACWhere($field, $value)
 {
  return "(($field like '$value%') or ($field like '% $value%') or ($field like '%-$value%'))";
 }

 private static function autocompleteBrand($query, $count = 50)
 {
  $result = '';
  if (strlen($query))
  {
   $whereName = self::makeACWhere('name', $query);
   $whereTitle = self::makeACWhere('title', $query);
   $table = '(' .
     'select name from com_brand b1 where ' . $whereName .
     ' and exists (select null from com_centre c where c.brand_id=b1.id and c.hidden is null' . WDomain::filter('c') . ' and exists (select null from com_menu_srv where centre_id=c.id))' .
     ' union all ' .
     'select title from com_brand_abc b2 where ' . $whereTitle .
     ' and exists (select null from com_centre c where c.brand_id=b2.brand_id and c.hidden is null' . WDomain::filter('c') . ' and exists (select null from com_menu_srv where centre_id=c.id))' .
     ' union all ' .
     'select name from com_centre c1 where ' . $whereName .
     ' and c1.hidden is null' . WDomain::filter('c1') . ' and exists (select null from com_menu_srv where centre_id=c1.id)' .
     ' union all ' .
     'select title from com_centre_abc c2 where ' . $whereTitle .
     ' and exists (select null from com_centre c where c.id=c2.centre_id and c.hidden is null' . WDomain::filter('c') . ' and exists (select null from com_menu_srv where centre_id=c.id))' .
     ') a';
   $records = DB::getDB()->queryRecords($table, 'distinct name', null, '1', $count);
   if(isset($records))
    foreach ($records as $record)
    {
     $value = addslashes($record[0]);
     $result .= ",{\"value\":\"$value\"}\n";
    }
   //print_r(DB::lastQuery());
  }
  return "[\n " . mb_substr($result, 1) . "]";
 }

 /**
  * Common Autocomplete function
  * @param string $table
  * @param string $field
  * @param string $where
  * @param string $order
  * @param string $query
  * @param int $count
  * @return string
  */
 /*private static function autocomplete($table, $field, $where, $order, $query, $count)
 {
  $result = '';
  $length = mb_strlen($query);
  if ($length)
  {
   $where = ($where ? ($where . ' and ') : '') . Lang::acFilter();
   $query = addslashes($query);
   $where .= " and ((title like '$query%') or (title like '% $query%') or (title like '%-$query%'))";
  }
  else
  {
   $lang = Lang::current()->id();
   $where = ($where ? ($where . ' and ') : '') . "abc_id='$lang'";
  }
  $records = DB::getDB()->queryRecords($table, $field . ',title', $where, $order, $count);
  if(isset($records))
   foreach ($records as $record)
   {
    $id = $record[0];
    $title = $record[1];
    $value = addslashes($title);
    $result .= ",{\"id\":\"$id\",\"value\":\"$value\"}\n";
   }
  //print_r(DB::queries());
  return "[\n " . mb_substr($result, 1) . "]";
 }*/

 private static function metadata1()
 {
  $data = array('curr' => WDomain::currencyId(), 'fday' => WDomain::firstDay());
  // Languages
  $all = array();
  $langs = Lang::map();
  foreach ($langs as $key => $lang)
   $all[$key] = $lang->title();
  $data['langs'] = array('all' => $all, 'def' => Lang::DEF());
  return $data;
 }

 private static function metadata2()
 {
  $data = array();
  // Top menu
  $dwhere = (WDomain::ok() ? (' and (domain_id is null or domain_id=' . WDomain::id() . ')') : ' and domain_id is null') . ' and hidden is null';
  $menu = DB::getDB()->queryArrays('biz_hmenu', 'id,name,addr', 'parent_id is null' . $dwhere, 'serial,id');
  if (!$menu)
   $menu = array();
  foreach ($menu as $menuId => $menuItem)
  {
   unset($menu[$menuId]['id']);
   $menu[$menuId]['name'] = Lang::getDBTitle('biz_hmenu_abc', 'hmenu_id', $menuId, $menuItem['name']);
   if (!$menuItem['addr'])
   {
    unset($menu[$menuId]['addr']);
    $items = DB::getDB()->queryArrays('biz_hmenu', 'id,name,addr', 'parent_id=' . $menuId . $dwhere, 'serial,id');
    if ($items)
    {
     foreach ($items as $itemId => $item)
     {
      unset($items[$itemId]['id']);
      $items[$itemId]['name'] = Lang::getDBTitle('biz_hmenu_abc', 'hmenu_id', $itemId, $item['name']);
      if (!$item['addr'])
       unset($items[$itemId]['addr']);
     }
     $menu[$menuId]['items'] = $items;
    }
   }
  }
  $data['menu'] = $menu;
  // Procedures
  $cats = DB::getDB()->queryMatrix(WProc::TABLE_CAT, 'id,name', 'hidden is null', 'serial,id');
  if (!$cats)
   $cats = array();
  foreach ($cats as $catId => $catName)
  {
   $cats[$catId] = array('name' => Lang::getDBValueDef(WProc::TABLE_CAT . '_abc', 'title', 'cat_id=' . $catId, $catName));
   $prcs = DB::getDB()->queryMatrix(WProc::TABLE_PRC, 'id,name', "cat_id=$catId and hidden is null", 'serial,id');
   if (!$prcs)
    $prcs = array();
   foreach ($prcs as $prcId => $prcName)
    $prcs[$prcId] = Lang::getDBValueDef(WProc::TABLE_PRC . '_abc', 'title', 'prc_id=' . $prcId, $prcName);
   $cats[$catId]['prcs'] = $prcs;
  }
  $data['cats'] = $cats;
  // Social groups
  $filters = WSGrp::filters();
  $sgrps = array();
  foreach ($filters as $filter)
   $sgrps[$filter['id']] = $filter['name'];
  $data['sgrps'] = $sgrps;
  // Result
  return $data;
 }

 public static function getMenuCatImageNames()
 {
  $result = array();
  $cats = DB::getDB()->queryRecords('biz_menu_cat', 'id,name', 'image is not null and hidden is null', 'serial,id');
  if ($cats)
   foreach ($cats as $cat)
    $result[] = "'c" . $cat[0] . "':" . DB::str($cat[1]);
  return count($result) ? "{\n " . implode(',', $result) . "}" : '{}';
 }

 public static function getMenuCatImages()
 {
  $result = array();
  $cats = DB::getDB()->queryRecords('biz_menu_cat', 'id,image', 'image is not null and hidden is null', 'serial,id');
  if ($cats)
   foreach ($cats as $cat)
    $result[] = "'c" . $cat[0] . "':'" . base64_encode($cat[1]) . "'\n";
  return count($result) ? "{\n " . implode(',', $result) . "}" : '{}';
 }

 /*public static function getDefListTer()
 {
  $result = '';
  $records = DB::getDB()->queryRecords(WCentre::TABLE_CENTRE_PLACE, 'distinct place_id,address', 'type=\'locality\'', 'address');
  if ($records)
  {
   foreach ($records as $i => $record)
   {
    $id = $record[0];
    $title = htmlspecialchars($record[1]);
    $records[ $i ] = "{id:'$id',label:'$title'}\n";
   }
   $result = "\n[\n " . implode(',', $records) . "]";
  }
  else
   $result = "[]";
  return $result;
 }*/

 public static function autocompleteMenuProc($query, $full = false)
 {
  $result = '';
  $term = ($query == '') ? '' : addslashes($query);
  $cond = ($query == '') ? '' : "((title like '$term%') or (title like '% $term%') or (title like '%-$term%'))";

  $cats = DB::getDB()->queryRecords('biz_menu_cat', 'id,name', 'hidden is null', 'serial,id');
  if (isset($cats))
   foreach ($cats as $cat)
   {
    $prcs = null;
    $id = $cat[0];
    $title = null;
    if ($query == '')
    {
     $prcs = DB::getDB()->queryRecords('biz_menu_prc', 'id,name', "cat_id=$id and hidden is null", 'serial,id', $full ? null : 3);
    }
    else
    {
     $tables = 'biz_menu_prc_abc a,biz_menu_prc b';
     $fields = 'distinct b.id,a.title';
     $where = "cat_id=$id and " . Lang::acFilter() . ' and b.id=a.prc_id and b.hidden is null';
     $query = addslashes($query);
     $where .= " and " . $cond;
     $order = 'a.title,b.serial,b.id';
     $prcs = DB::getDB()->queryRecords($tables, $fields, $where, $order);
    }
    if ($prcs || ($query == ''))
    {
     //$title = Lang::getDBValue('biz_menu_cat_abc', 'title', "cat_id=$id");
     $title = Lang::getDBValueDef('biz_menu_cat_abc', 'title', "cat_id=$id", $cat[1]);
    }
    else
    {
     $title = DB::getDB()->queryField('biz_menu_cat_abc', 'title', "cat_id=$id and " . $cond);
     if ($title == null)
      continue;
    }
    $value = addslashes($title);
    $result .= ",{\"id\":\"c$id\",\"value\":\"$value\"}\n";
    if ($prcs)
     foreach ($prcs as $prc)
     {
      $id = $prc[0];
      $title = $prc[1];
      if ($query == '')
       $title = Lang::getDBValueDef('biz_menu_prc_abc', 'title', "prc_id=$id", $title);
      $value = addslashes($title);
      $result .= ",{\"id\":\"p$id\",\"value\":\"$value\"}\n";
     }
   }
  //print_r(DB::queries());
  return "[\n " . mb_substr($result, 1) . "]";
 }

 public static function autocompleteMenuTerr()
 {
  $result = array();
  $where = 'wid is not null and hidden is null and domain_id=' . WDomain::id();
  $ters = DB::getDB()->queryArrays('biz_menu_ter', 'id,wid,name', $where, 'serial,id');
  if ($ters)
  {
   foreach ($ters as $ter)
   {
    $id = $ter['id'];
    $wid = $ter['wid'];
    $name = $ter['name'];
    $label = Lang::getDBTitle('biz_menu_ter', 'ter', $id, $name);
    $result[] = array('id' => $wid, 'label' => $label);
   }
  }
  return json_encode($result);
 }

 public static function stars($rate, $big = false)
 {
  $rate_prc = max(array(0, min(array(100, $rate * 20))));
  return
    '<div class="stars ' . ($big ? 'big' : 'small') . '">' .
    '<div class="' . (($rate_prc > 40) ? 'light' : 'dark') . '" style="width:' . $rate_prc . '%">' .
    '</div>' .
    '</div>';
 }

 public static function reviewCountText($count)
 {
  return '<span>' . $count . '</span> ' . Lang::getPageWord('text', 'reviews');
 }

 public static function getFAQs()
 {
  $tables = 'art_faq a,art_faq_abc b';
  $fields = 'a.id,a.name,b.title,b.reply';
  $where = 'b.faq_id=a.id and a.hidden is null and b.reply is not null and b.abc_id=' . DB::str(Lang::current());
  $faqs = DB::getDB()->queryArrays($tables, $fields, $where, 'serial,id');
  //echo DB::lastQuery();
  $result = array();
  if ($faqs)
  {
   foreach ($faqs as $faq)
   {
    $reply = trim($faq['reply']);
    if (!strlen($reply))
     continue;
    $id = intval($faq['id']);
    $title = trim($faq['title']);
    if (!strlen($title))
     $title = trim($faq['name']);
    if (!strlen($title))
     continue;
    $result[] = array('id' => $id, 'title' => $title, 'reply' => $reply);
   }
  }
  return $result;
 }

 /**
  * Get text of the policy topic
  * @param int $topic Topic index ('terms', 'booking' or 'privacy')
  * @return Topic text
  */
 public static function getPolicyTopicText($topic)
 {
  $keys = array
  (
    'terms' => '1. Terms and Conditions',
    'booking' => '2. Booking Terms and Conditions',
    'privacy' => '3. Privacy and cookie policy'
  );
  return Lang::getText('topic', $keys[$topic], 'policy');
 }
}

?>