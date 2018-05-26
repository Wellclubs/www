<?php

// http://htmlbook.ru/html/value/lang

// http://www.skype-emoticons.com/country-flags.htm
// https://support.skype.com/en/faq/FA12330/what-is-the-full-list-of-emoticons

class Lang
{
 const SYS = 'en';
 const SYS_TITLE = 'English';
 const SYS_CHAR_DEC = '.';
 const SYS_CHAR_MIL = ',';

 const TABLE_ABC = 'art_abc';

 private static $def = null; // char(2) default language id
 public static function DEF()
 {
  if (self::$def == null)
  {
   //if (fnmatch('*/adm/*', substr($_SERVER['REQUEST_URI'], 0, 10)))
   // self::$def = self::SYS;
   /*else*/ if ((WDomain::id() != null) && (WDomain::abcId() != null) &&
     DB::getDB()->queryField(self::TABLE_ABC, 'count(*)', array('id' => DB::str(WDomain::abcId()))))
    self::$def = WDomain::abcId();
   else
   {
    $host = $_SERVER['HTTP_HOST'];
    $ru = fnmatch('*.ru', $host) || ($host == 'localhost') || fnmatch('*.dyndns.*', $host);
    $ae = $host == 'wellclubs.com.ae';
    self::$def = $ru ? 'ru' : ($ae ? 'ae' : self::SYS);
   }
  }
  return self::$def;
 }

 public static function DEF_TITLE()
 {
  if (self::DEF() == self::SYS)
   return self::SYS_TITLE;
  if (self::DEF() == 'ru')
   return 'Русский';
  $result = DB::getDB()->queryField(self::TABLE_ABC, 'title', array('id' => self::DEF()));
  if (!$result)
   $result = 'Default';
  return $result;
 }

 public static function DEF_CHAR_DEC()
 {
  if (self::DEF() == self::SYS)
   return self::SYS_CHAR_DEC;
  if (self::DEF() == 'ru')
   return ',';
  return self::SYS_CHAR_DEC;
 }

 public static function DEF_CHAR_MIL()
 {
  if (self::DEF() == self::SYS)
   return self::SYS_CHAR_MIL;
  if (self::DEF() == 'ru')
   return ' ';
  return self::SYS_CHAR_MIL;
 }

 private static $map; // map<char(2), Lang> assoc-array of object instances
 private static $current; // Lang current object instance

 public static function map() { return self::$map; }
 public static function current() { return self::$current; }
 public static function used() { return count(self::$map) > 1; }

 public static function select($lang)
 {
  self::$current = self::$map[$lang];
 }

 public static function extractTitle($lang)
 {
  return $lang->title;
 }

 public static function titles()
 {
  return array_map(array('Lang', 'extractTitle'), self::$map);
 }

 private $id;
 public $title;
 private $image;
 private $charDec;
 private $charMil;
 private $hidden;

 public function id() { return $this->id; }
 public function title() { return $this->title; }
 public function image() { return $this->image; }
 public function charDec() { return $this->charDec; }
 public function charMil() { return $this->charMil; }
 public function hidden() { return $this->hidden; }

 public function __toString() { return $this->id; }

 public function imageFilename()
 {
  return 'lang-' . $this->id . '.png';
 }

 public static function isFilenameImage($filename)
 {
  return fnmatch('lang-??.png', $filename);
 }

 //public static function getIdFromImageFilename($filename)
 //{
 // return substr($filename, 5, 2);
 //}

 //private function downloadImage()
 //{
 // Base::downloadFile($this->image(), $this->imageFilename(), 'image/gif');
 //}

 public function htmlImage()
 {
  return '<img src="' . Base::home() . 'img/' . $this->imageFilename() . '" width="16" height="11"/>';
 }

 public static function downloadImage($filename)
 {
  $id = substr($filename, 5, 2);
  if (array_key_exists($id, self::$map))
  {
   $lang = self::$map[$id];
   $image = $lang->image();
   if (!isset($image))
    return false;
   Base::downloadFile($image, $filename, 'image/png');
   return true;
  }
  return DB::getDB()->downloadFile(self::TABLE_ABC, 'image', "id='$id'", $filename, 'image/png');
 }

 private function __construct()
 {
 }

 private static function create($record)
 {
  $result = new Lang();
  $result->id = $record[0];
  $result->title = $record[1];
  $result->image = $record[2];
  $result->charDec = strlen($record[3]) ? $record[3] : '.';
  $result->charMil = strlen($record[4]) ? $record[4] : ' ';
  $result->hidden = !!$record[5];
  return $result;
 }

 public static function initialize($all = null)
 {
  //exit(WDomain::id());
  self::$map = array();

  $db = DB::getDB();
  $table = self::TABLE_ABC;
  $fields = 'id,title,image,char_dec,char_mil,hidden';

  $def = $db->queryFields($table, $fields, 'id=\'' . self::DEF() . '\'');
  if (!$def || !$def[5] || $all)
  {
   if (!$def)
    $def = array(self::DEF(), self::DEF_TITLE(), null, self::DEF_CHAR_DEC(), self::DEF_CHAR_MIL(), null);
   self::$map[self::DEF()] = self::create($def);
  }

  if (self::DEF() != self::SYS)
  {
   $sys = $db->queryFields($table, $fields, 'id=\'' . self::SYS . '\'');
   if (!$sys || !$sys[5] || $all)
   {
    if (!$sys)
     $sys = array(self::SYS, self::SYS_TITLE, null, self::SYS_CHAR_DEC, self::SYS_CHAR_MIL, null);
    self::$map[self::SYS] = self::create($sys);
   }
  }
  //exit(print_r(self::$map, true));

  $where = null;
  if (!$all)
  {
   $where = 'hidden is null';
   if (WDomain::id() != null)
    $where .= ' and id in (select abc_id from biz_domain_abc where domain_id=' . WDomain::id() . ' and used=\'1\')';
  }
  $records = $db->queryRecords($table, $fields, $where, 'id');
  //exit(DB::lastQuery());
  if ($records)
   foreach ($records as $record)
    if (!array_key_exists($record[0], self::$map))
     self::$map[$record[0]] = self::create($record);

  self::$current = self::$map[self::DEF()];
  //exit(print_r(self::$map, true));
 }

 public static function strNum($value, $dec = 2)
 {
  if (!is_numeric($value))
   return '0';
  $charMil = self::current()->charMil;
  $charDec = $dec ? self::current()->charDec : null;
  return number_format($value, $dec, $charDec, $charMil);
 }

 public static function strInt($value)
 {
  if (!is_numeric($value))
   return '0';
  return number_format($value, 0, null, self::current()->charMil);
 }

 /**
  * Get a field value for a current language
  * @param string $table Database table name
  * @param string $field Database table field name
  * @param string $where Database table filter value
  * @param string $order Records sort order
  * @param string $lang Language id
  * @return mixed Field value or null
  */
 public static function getDBValue($table, $field = null, $where = null, $order = null, $lang = null)
 {
  if (!$field)
   $field = 'title';
  $cnd = DB::makeWhere($where);
  $where = "($cnd) and length($field)>0";
  if (!$lang)
   $lang = self::$current;
  $sys = self::SYS;
  $where .= " and abc_id in('$lang','$sys')";
  $sort = "case abc_id when '$lang' then 0 else 1 end";
  if ($order != null)
   $sort .= ',' . $order;
  $result = DB::getDB()->queryField($table, $field, $where, $sort);
  return $result;
 }

 /**
  * Set a field value for a current language
  * @param string $text Stored text value
  * @param string $table Database table name
  * @param string $field Database table field name
  * @param mixed $where Database table filter value (array, string or null)
  * @param string $lang Language ID
  * @return bool Success
  */
 public static function setDBValue($text, $table, $field = null, $where = null, $lang = null)
 {
  if (!$field)
   $field = 'title';
  if (!$lang)
   $lang = self::$current;
  if (is_array($where))
   $where['abc_id'] = DB::str($lang);
  elseif (is_string($where) && strlen($where))
   $where = "($where) and abc_id=" . DB::str($lang);
  else
   $where = array('abc_id' => DB::str($lang));
  $db = DB::getAdminDB();
  return DB::getAdminDB()->mergeField($table, $field, DB::str($text), $where);
 }

 /**
  * Get a translated value
  * @param string $table
  * @param string $field
  * @param string $where
  * @param string $def Default value
  * @param string $order
  * @param string $lang
  * @return string Translated value or default value
  */
 public static function getDBValueDef($table, $field, $where, $def, $order = null, $lang = null)
 {
  $db = DB::getDB();
  if (!$field)
   $field = 'title';
  if ($lang == null)
   $lang = self::$current;
  /*if ($lang->id() == self::SYS)
  {
   $cond = "(abc_id='$lang')";
   if ($where != null)
    $cond = "($where) and $cond";
   $where = $cond;
  }
  $sort = "case abc_id when '" . self::$current . "' then 0 when '" . self::DEF() . "' then 1 when '" . self::SYS . "' then 2 else 3 end,abc_id";
  if ($order != null)
   $sort = $cond . ',' . $order;
  $result = $db->queryField($table, $field, $where, $sort);*/
  if (is_array($where))
  {
   $where['abc_id'] = DB::str($lang);
   $where["sign(length($field))"] = 1;
  }
  elseif (is_string($where) && strlen($where))
   $where = "($where) and abc_id=" . DB::str($lang) . " and length($field)>0";
  else
   $where = array('abc_id' => DB::str($lang), "sign(length($field))" => 1);
  $result = $db->queryField($table, $field, $where);
  if ($result == null)
   $result = $def;
  return $result;
 }

 /**
  * Get entity title or name
  * @param string $table Database table name
  * @param string $field Database table field name
  * @param string $id Entity ID
  * @param string $name Default name value
  * @param string $lang Requested language ID
  * @return string Entity title or name
  */
 public static function getDBTitle($table, $field, $id, $name = null, $lang = null)
 {
  if ($name === null)
   $name = DB::getDB()->queryField($table, 'name', 'id=' . $id);
  return Lang::getDBValueDef($table . '_abc', null, $field . '_id=' . $id, $name, null, $lang);
 }

 public static function getDBTitles($table, $field, $id)
 {
  $name = array('name' => DB::getDB()->queryField($table, 'ifnull(name,\'\')', 'id=' . $id));
  return array_merge($name, self::getDBTitlesOnly($table, array($field . '_id' => $id)));
 }

 public static function getDBTitlesOnly($table, $where)
 {
  $result = array();
  $rows = DB::getDB()->queryRecords($table . '_abc', 'abc_id,title', $where);
  if ($rows)
   foreach ($rows as $row)
    if (strlen($row[1]))
     $result['title-' . $row[0]] = $row[1];
  return $result;
 }

 public static function setDBTitles($table, $field, $id, $delete = false)
 {
  $db = DB::getAdminDB();
  $name = HTTP::get(array_key_exists('field', $_GET) ? 'value' : 'name');
  if (!$db->modifyField($table, 'name', 's', $name, 'id=' . $id))
   return false;
  return self::setDBTitlesOnly($table, array($field . '_id' => $id), $delete);
 }

 public static function setDBTitlesOnly($table, $where, $delete = false)
 {
  if (!is_array($where))
   throw new Exception('The value of $where must be an array');
  $db = DB::getAdminDB();
  $table .= '_abc';
  foreach (self::$map as $key => $lang)
  {
   $title = HTTP::get('title-' . $key);
   //$where = array('abc_id' => DB::str($key), $field . '_id' => $id);
   $where['abc_id'] = DB::str($key);
   if (strlen($title))
   {
    if (!$db->mergeField($table, 'title', DB::str($title), $where))
     return false;
   }
   else if ($delete)
   {
    if (!$db->deleteRecords($table, $where))
     return false;
   }
   else
   {
    if (!$db->modifyFields($table, array('title' => 'null'), $where))
     return false;
   }
  }
  return true;
 }


 // WORDS

 private static $words = array();

 /**
  * Get title for some page and mode
  * @param string $kind Word kind
  * @param string $name Word key (and default value)
  * @param string $mode Mode condition (null - current mode)
  * @param string $page Page condition (null - current page)
  * @return string Found word for current language (or name)
  */
 public static function getWord($kind, $name, $mode = null, $page = null)
 {
  $result = null;
  if ($page === null)
  {
   $page = Base::page();
   if ($mode === null)
    $mode = Base::mode();
  }
  $key = $page . '|' . $mode . '|' . $kind . '|' . $name;
  if (array_key_exists($key, self::$words))
   $result = self::$words[$key];
  else
  {
   $db = DB::getAdminDB();
   $dbpage = DB::str($page);
   $dbmode = DB::str($mode);
   $dbkind = DB::str($kind);
   $dbname = DB::str($name);
   // Search for a key record
   $id = $db->queryField('art_word', 'id', "page=$dbpage and mode=$dbmode and kind=$dbkind and name=$dbname");
   if ($id == null) // Add a key record if it does not exist
    $db->insertValues('art_word', array('page' => $dbpage, 'mode' => $dbmode, 'kind' => $dbkind, 'name' =>$dbname));
   else
   {
    $result = self::getDBValue('art_word_abc', 'title', 'word_id=' . $id);
    // If the title is not found for a specified mode then search it for an empty mode:
    if ((($result == null) || ($result == '')) && ($page !== '') && ($mode !== ''))
    {
     $id0 = $db->queryField('art_word', 'id', "page=$dbpage and mode='' and kind=$dbkind and name=$dbname");
     if ($id0 != null)
     { // ... and save it for specified mode! :-)
      $result = self::getDBValue('art_word_abc', 'title', 'word_id=' . $id0);
      if ($result)
       $db->insertValues('art_word_abc', array('word_id' => $id, 'abc_id' => DB::str(self::$current), 'title' => DB::str($result)));
     }
    }
   }
   if (($result == null) || ($result == ''))
    $result = $name;
   self::$words[$key] = $result;
  }
  return $result;
 }

 /**
  * Get title for all modes
  * @param string $kind Word kind
  * @param string $name Word key (and default value)
  * @param string $page Page condition (null - current page)
  * @return string Found word for current language (or name)
  */
 public static function getPageWord($kind, $name, $page = null)
 {
  return self::getWord($kind, $name, '', $page);
 }

 /**
  * Get title for all pages
  * @param string $kind Word kind
  * @param string $name Word key (and default value)
  * @return string Found word for current language (or name)
  */
 public static function getSiteWord($kind, $name)
 {
  return self::getWord($kind, $name, '', '');
 }

 /**
  * Get object title for all pages
  * @param string $name Word key (and default value)
  * @return string Found word for current language (or name)
  */
 public static function getObjTitle($name)
 {
  return self::getWord('obj', $name, '', '');
 }

 // TEXTS

 /**
  * Get title for some page and mode
  * @param string $kind Word kind
  * @param string $name Word key
  * @param string $mode Mode condition (null - current mode)
  * @param string $page Page condition (null - current page)
  * @return string Found word for current language (or name)
  */
 public static function getText($kind, $name, $mode = null, $page = null)
 {
  if (is_null(WDomain::id()))
   return null;
  if ($page === null)
  {
   $page = Base::page();
   if ($mode === null)
    $mode = Base::mode();
  }
  $db = DB::getDB();
  $where1 = array('page' => DB::str($page), 'mode' => DB::str($mode), 'kind' => DB::str($kind), 'name' => DB::str($name));
  $id = $db->queryField('biz_text', 'id', $where1);
  if (is_null($id))
  {
   $dba = DB::getAdminDB();
   $dba->insertValues('biz_text', $where1);
   $id = $dba->insert_id;
  }
  $lang = Lang::used() ? Lang::current()->id() : WDomain::abcId();
  $where2 = array('text_id' => $id, 'domain_id' => WDomain::id(), 'abc_id' => DB::str($lang));
  $value = $db->queryField('biz_text_abc', 'value', $where2);
  if (!strlen($value) && ($lang != Lang::DEF()))
  {
   $where2['abc_id'] = DB::str(Lang::DEF());
   $value = $db->queryField('biz_text_abc', 'value', $where2);
  }
  if (!strlen($value) && ($lang != Lang::SYS) && (Lang::DEF() != Lang::SYS))
  {
   $where2['abc_id'] = DB::str(Lang::SYS);
   $value = DB::getDB()->queryField('biz_text_abc', 'value', $where2);
  }
  return $value;
 }

 /**
  * Get title for some page and mode
  * @param string $text New value for the system language
  * @param string $kind Word kind
  * @param string $name Word key (and default value)
  * @param string $mode Mode condition (null - current mode)
  * @param string $page Page condition (null - current page)
  * @return string Found word for current language (or name)
  */
 public static function setText($text, $kind, $name, $mode = null, $page = null)
 {
  if (is_null(WDomain::id()))
   return null;
  if ($page === null)
  {
   $page = Base::page();
   if ($mode === null)
    $mode = Base::mode();
  }
  $where1 = array('page' => DB::str($page), 'mode' => DB::str($mode), 'kind' => DB::str($kind), 'name' => DB::str($name));
  $id = DB::getDB()->queryField('biz_text', 'id', $where1);
  if (is_null($id))
   $id = DB::getAdminDB()->insertValues('biz_text', $where1);
  $lang = Lang::SYS;
  $where2 = array('text_id' => $id, 'domain_id' => WDomain::id(), 'abc_id' => DB::str($lang));
  $result = DB::getAdminDB()->mergeField('biz_text_abc', 'value', DB::str($text), $where2);
  return $result;
 }


// WEEKDAYS

 private static $weekDays = array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');

 public static function dayOfWeekEn($day)
 {
  return self::$weekDays[$day % 7];
 }

 public static function dayOfWeek($day)
 {
  return self::getPageWord('weekday', self::$weekDays[$day % 7], '');
 }

 private static $weekDaysLong = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

 public static function dayOfWeekLong($day)
 {
  return self::getPageWord('weekdaylong', self::$weekDaysLong[$day % 7], '');
 }

 // AUTOCOMPLETION

 public static function acFilter()
 {
  $lang1 = self::current()->id();
  $lang2 = self::DEF();
  $lang3 = self::SYS;
  return (($lang1 == $lang2) || ($lang1 == $lang3)) ? "abc_id in ('$lang2','$lang3')" : "abc_id in ('$lang1','$lang2','$lang3')";
 }
}

?>