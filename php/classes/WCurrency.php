<?php

class WCurrency
{
 const DEF_ID = 'USD';
 const DEF_CODE = '$';
 const DEF_ID_POS = 0;
 const DEF_CODE_POS = 1;
 const DEF_TITLE = 'Рубль';

 const COOKIE_KEY = 'currency';

 const TABLE_CURRENCY = 'art_currency';

 private $id = null;
 private $code = null;
 private $idPos = null;
 private $codePos = null;
 private $rate = null;
 private $title = null;

 //public function __toString() { return $this->code; }

 private function __construct($row)
 {
  if ($row)
  {
   $this->id = $row[0];
   $this->code = $row[1];
   $this->rate = $row[2];
   $this->idPos = $row[3];
   $this->codePos = $row[4];
   $this->title = Lang::getDBValueDef(self::TABLE_CURRENCY . '_abc', 'title', 'currency_id=' . $this->id, $this->id);
  }
  else
  {
   $this->id = self::DEF_ID;
   $this->code = self::DEF_CODE;
   $this->rate = 1;
   $this->idPos = self::DEF_ID_POS;
   $this->codePos = self::DEF_CODE_POS;
   $this->title = $this->id ? Lang::getDBValue(self::TABLE_CURRENCY . '_abc', 'title', 'id=' . $id) : self::DEF_TITLE;
  }
  //if ($this->title == null)
  // $this->title = '';
 }

 public static function create($id, $canBeNull = false)
 {
  $fields = 'id,code,rate,id_pos,code_pos';
  $where = 'id=' . DB::str($id);
  $row = DB::getDB()->queryFields(WCurrency::TABLE_CURRENCY, $fields, $where);
  if (!$row && $canBeNull)
   return null;
  return new WCurrency($row);
 }

 private static $current = null;

 public static function current()
 {
  if (!self::$current)
  {
   //if (HTTP::hasCookie(self::COOKIE_KEY))
   // self::$current = self::create(HTTP::cookie(self::COOKIE_KEY));
   //else
    self::$current = self::create(WDomain::currencyId());
  }
  return self::$current;
 }

 public static function code() { return self::current()->code; }
 public static function title() { return self::current()->title; }
 public static function value($price) { return self::current()->rate * $price; }

 public static function obj($id, $pos)
 {
  $result = array('id' => $id);
  $pos = intval($pos);
  if ($pos)
   $result['pos'] = $pos;
  return $result;
 }

 public function objs()
 {
  $result = array(self::obj($this->id, $this->idPos));
  if (strlen($this->code))
   $result[] = self::obj($this->code, $this->codePos);
  return $result;
 }

 public static function makeObjs($id)
 {
  if (!strlen($id))
   return null;
  if (self::$current && (self::$current->id == $id))
   return self::$current->objs();
  $curr = self::create($id, true);
  if (!$curr)
   return null;
  return $curr->objs();
 }

 public static function addObjs($text, $objs, $code = false, $nbsp = false)
 {
  if ($objs && count($objs))
  {
   $obj = $objs[($code && (count($objs) > 1)) ? 1 : 0];
   $curr = $obj['id'];
   $pos = intval(Util::nvl(Util::item($obj, 'pos'), 0));
   if ($pos < 1)
    $text = $curr . ' ' . $text;
   else if ($pos == 1)
    $text = $curr . $text;
   else if ($pos == 2)
    $text .= $curr;
   else
    $text = $text . ' ' . $curr;
  }
  if ($nbsp)
   $text = str_replace(' ', '&nbsp;', $text);
  return $text;
 }

 public static function makeLabel($id, $title = null)
 {
  if ($title == null)
   $title = Lang::getDBValue(self::TABLE_CURRENCY . '_abc', null, 'currency_id=' . DB::str($id));
  return $id . ' : ' . $title;
 }

 public static function exists($id)
 {
  return DB::getDB()->queryField(self::TABLE_CURRENCY, '1', 'id=' . DB::str($id)) == '1';
 }

 public static function getCode($id = null)
 {
  if (($id == null) && self::current())
   return self::current()->code;
  return DB::getDB()->queryField(self::TABLE_CURRENCY, 'code', 'id=' . DB::str($id));
 }

 public static function acQueryDef()
 {
  $result = array();
  $ids = array('RUB', 'USD');
  foreach ($ids as $id)
   $result[] = array('id' => $id, 'value' => self::makeLabel($id));
  return $result;
 }

 public static function acQuery($term, $limit = null)
 {
  if (!strlen($term))
   return self::acQueryDef();
  $result = array();
  $term = addslashes($term);
  $condId = "((currency_id like '$term%') or (currency_id like '% $term%') or (currency_id like '%-$term%'))";
  $condTitle = "((title like '$term%') or (title like '% $term%') or (title like '%-$term%'))";

  $db = DB::getDB();
  $table = self::TABLE_CURRENCY . '_abc';
  $fields = 'distinct currency_id,title';
  $where = $condId . ' and abc_id=' . DB::str(Lang::current()) . ' or ' . $condTitle . ' and not ' . $condId;
  $rows = $db->queryRecords($table, $fields, $where, null, $limit);
  if ($rows)
  {
   foreach ($rows as $row)
    $result[] = array('id' => $row[0], 'value' => self::makeLabel($row[0], $row[1]));
  }
  return $result;
 }

}

?>
