<?php

/**
 * Description of WProc
 */
class WProc
{
 const TABLE_CAT = 'biz_menu_cat';
 const TABLE_PRC = 'biz_menu_prc';

 public static function acQueryAll($limit = null)
 {
  $result = array();
  $db = DB::getDB();
  $cats = $db->queryRecords(self::TABLE_CAT, 'id,name', 'hidden is null', 'serial,id');
  if ($cats)
   foreach ($cats as $cat)
   {
    $result[] = array('id' => 'c' . $cat[0], 'value' => Lang::getDBValueDef(self::TABLE_CAT . '_abc', null, 'cat_id=' . $cat[0], $cat[1]));
    $prcs = $db->queryRecords(self::TABLE_PRC, 'id,name', 'cat_id=' . $cat[0] . ' and hidden is null', 'serial,id', $limit);
    if ($prcs)
     foreach ($prcs as $prc)
      $result[] = array('id' => 'p' . $prc[0], 'value' => Lang::getDBValueDef(self::TABLE_PRC . '_abc', null, 'prc_id=' . $prc[0], $prc[1]));
   }
  return $result;
 }

 public static function acQuery($term, $limit = null)
 {
  if (!strlen($term))
   return self::acQueryAll($limit);
  $result = array();
  $term = addslashes($term);
  $condName = "((name like '$term%') or (name like '% $term%') or (name like '%-$term%'))";
  $condTitle = "((title like '$term%') or (title like '% $term%') or (title like '%-$term%'))";

  $db = DB::getDB();
  $cats = $db->queryRecords(self::TABLE_CAT, 'id,name', 'hidden is null', 'serial,id');
  if ($cats)
   foreach ($cats as $cat)
   {
    $table = '(select id,name from ' . self::TABLE_PRC .
     ' where cat_id=' . $cat[0] . ' and ' . $condName . ' and hidden is null' .
     ' union ' .
     'select prc_id,title from ' . self::TABLE_PRC . '_abc where ' . $condTitle .
     ' and prc_id in (select id from ' . self::TABLE_PRC .
     ' where cat_id=' . $cat[0] . ' and hidden is null))a';
    $prcs = $db->queryRecords($table, 'distinct id,name', null, null, $limit);
    if ($prcs)
    {
     $result[] = array('id' => 'c' . $cat[0], 'value' => Lang::getDBValueDef(self::TABLE_CAT . '_abc', null, 'cat_id=' . $cat[0], $cat[1]));
     foreach ($prcs as $prc)
      $result[] = array('id' => 'p' . $prc[0], 'value' => $prc[1]);
    }
   }
  return $result;
 }

}

?>
