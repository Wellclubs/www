<?php

class WCountry
{
 const TABLE_COUNTRY = 'art_country';

 public static function exists($id)
 {
  return DB::getDB()->queryField(self::TABLE_COUNTRY, '1', 'id=' . DB::str($id)) == '1';
 }

 public static function getTitle($id)
 {
  if (!strlen($id))
   return null;
  return Lang::getDBTitle(self::TABLE_COUNTRY, 'country', DB::str($id));
 }

 public static function getList()
 {
  $result = DB::getDB()->queryMatrix(self::TABLE_COUNTRY, 'id,name', 'hidden is null', 'serial,id');
  return $result ? $result : array();
 }

 /*public static function acQueryDef()
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
 }*/

}

?>
