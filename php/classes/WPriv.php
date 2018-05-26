<?php

/**
 * Description of WPriv
 */
class WPriv
{
 const ROLE_OWNER = 1;

 const PRIV_VIEW_BOOK_LIST = 1;
 const PRIV_EDIT_CTR_DATA = 2;
 const PRIV_VIEW_FIN_DATA = 3;
 const PRIV_EDIT_OWN_CDR = 4;
 const PRIV_VIEW_OTR_CDR = 5;
 const PRIV_EDIT_OTR_CDR = 6;
 const PRIV_VIEW_MENU = 7;
 const PRIV_EDIT_MENU = 8;
 const PRIV_VIEW_CLT_LIST = 9;
 const PRIV_EDIT_CLT_DATA = 10;
 const PRIV_ADD_MASTER = 11;
 const PRIV_EDIT_PRIVS = 12;

 const TABLE_ROLE = 'art_master_role';
 const TABLE_PRGR = 'art_master_prgr';
 const TABLE_PRIV = 'art_master_priv';
 
 /**
  * Get list of privileges grouped by privilege group
  * @param bool $includeEmpty
  * @return assoc_array List of privilege groups ( id => array( name, privs ) )
  */
 public static function getListPrGr($includeEmpty = false)
 {
  echo "<!-- getListPrGr() -->\n";
  $prgrs = array();
  $records = DB::getDB()->queryRecords(self::TABLE_PRGR, 'id,name', '', 'serial,id');
  if ($records)
  {
   foreach ($records as $record)
   {
    $prgr_id = $record[0];
    $privs = self::getListPriv($prgr_id);
    if ($includeEmpty || count($privs))
     $prgrs[$prgr_id] = array('name' => $record[1], 'privs' => $privs);
   }
  }
  return $prgrs;
 }
 
 /**
  * Get list of privileges related to specified privilege group
  * @param int $prgr_id Privilege group ID
  * @return assoc_array List of privileges ( id => name )
  */
 public static function getListPriv($prgr_id = null)
 {
  $privs = array();
  $where = $prgr_id ? ('prgr_id=' . $prgr_id) : null;
  $order = $prgr_id ? 'serial,id' :
    ('(select serial from ' . self::TABLE_PRGR . ' where id=prgr_id),prgr_id,serial,id');
  $records = DB::getDB()->queryRecords(self::TABLE_PRIV, 'id,name', $where, $order);
  if ($records)
   foreach ($records as $record)
    $privs[$record[0]] = $record[1];
  return $privs;
 }
 
 /**
  * Get list of privileges of master
  * @param type $masterId Master ID
  * @return assoc_array List of privileges ( id => name )
  */
 public static function getMasterListPriv($masterId)
 {
  $roleId = DB::getDB()->queryField('com_master', 'role_id', 'id=' . $masterId);
  return self::getMasterListPrivByRole($masterId, $roleId);
 }
 
 /**
  * Get list of privileges of master
  * @param int $masterId Master ID
  * @param int $roleId Role ID
  * @return assoc_array List of privileges ( id => name )
  */
 public static function getMasterListPrivByRole($masterId, $roleId)
 {
  $where = null;
  if (!$roleId)
   $where = 'id in (select priv_id from com_master_priv where master_id=' . $masterId . ')';
  else if ($roleId != 1)
   $where = 'id in (select priv_id from art_master_role_priv where role_id=' . $roleId . ')';
  $privs = DB::getDB()->queryArray('art_master_priv', 'id', 'name', $where, 'serial,id');
  return $privs ? $privs : array();
 }

 public static function getRoles()
 {
  $result = array(array('id' => '', 'title' => self::getRoleTitle(null)));
  $rows = DB::getDB()->queryRecords(self::TABLE_ROLE, 'id,name', null, 'serial');
  if ($rows)
   foreach ($rows as $row)
    $result[] = array('id' => $row[0], 'title' => self::getRoleTitle($row[0], $row[1]));
  return $result;
 }

 public static function getRoleTitle($id, $name = null)
 {
  if (!$id)
   return Lang::getSiteWord('title', 'Custom role');
  if (is_null($name))
   $name = DB::getDB()->queryField(self::TABLE_ROLE, 'name', 'id=' . $id);
  return Lang::getDBValueDef(self::TABLE_ROLE . '_abc', null, 'role_id=' . $id, $name);
 }
}

?>
