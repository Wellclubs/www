<?php

/**
 * Social groups and filters by them
 */
class WSGrp
{
 //const TABLE_GROUP = 'art_social_group'; // WSGrp::TABLE_GROUP
 //const TABLE_GROUP_ABC = 'art_social_group_abc'; // WSGrp::TABLE_GROUP_ABC
 const TABLE_GROUP = 'biz_menu_sgrp'; // WSGrp::TABLE_GROUP
 const TABLE_GROUP_ABC = 'biz_menu_sgrp_abc'; // WSGrp::TABLE_GROUP_ABC

 const TABLE_FILTER = 'biz_menu_sgrp_filter';
 const TABLE_FILTER_ABC = 'biz_menu_sgrp_filter_abc';
 const TABLE_FILTER_GRP = 'biz_menu_sgrp_filter_group';

 public static function where($all = null)
 {
  $domainId = WDomain::id();
  $where = 'domain_id is null';
  if ($domainId)
   $where = "($where or domain_id=$domainId)";
  if ($all !== true)
   $where = ($where ? "$where and " : '') . 'hidden is null';
  return $where;
 }

 /**
  * Get list of social groups
  * @param bool $sys Do not translate titles
  * @param bool $all Include hidden items
  * @param int $ctr Calculate 'active' for this centre
  * @param int $srv Calculate 'active' for this service
  * @return array[{id:,name:,active:}] List of social groups
  */
 public static function groups($sys = null, $all = null, $ctr = null, $srv = null)
 {
  $fields = 'id,name,';
  if ($ctr && $srv)
   $fields .= "(coalesce((select active from com_menu_srv_sgrp where srv_id=$srv and sgrp_id=a.id)" .
     ",(select active from com_centre_sgrp where centre_id=$ctr and sgrp_id=a.id),popular))";
  else if ($ctr)
   $fields .= "(ifnull((select active from com_centre_sgrp where centre_id=$ctr and sgrp_id=a.id),popular))";
  else
   $fields .= 'popular ';
  $fields .= 'active';
  $groups = DB::getDB()->queryArrays(self::TABLE_GROUP . ' a', $fields, self::where($all), 'serial,id');
  if (!$groups)
   $groups = array();
  else if ($sys !== true)
   foreach ($groups as $id => $group)
    $groups[$id]['name'] = Lang::getDBValueDef(self::TABLE_GROUP_ABC, 'title', 'group_id=' . $group['id'], $group['name']);
  return $groups;
 }

 /**
  * Get list of social filters
  * @param bool $sys Do not translate titles
  * @param bool $all Include hidden items
  * @param bool $grp Retrieve group relations
  * @param array $groups Precalculated list of groups
  * @return array[{id:,name:}] List of social filters
  */
 public static function filters($sys = null, $all = null, $grp = null, $groups = null)
 {
  $fields = 'id,name';
  if ($grp)
  {
   if (!$groups)
    $groups = self::groups($sys, $all);
   foreach ($groups as $group)
   {
    $groupId = $group['id'];
    $fields .= ',(select include from ' . self::TABLE_FILTER_GRP . ' where filter_id=a.id and group_id=' . $groupId . ')include_' . $groupId;
   }
  }
  $filters = DB::getDB()->queryArrays(self::TABLE_FILTER . ' a', $fields, self::where($all), 'serial,id');
  if (!$filters)
   $filters = array();
  else if ($sys !== true)
   foreach ($filters as $id => $filter)
    $filters[$id]['name'] = Lang::getDBValueDef(self::TABLE_FILTER_ABC, 'title', '$filter_id=' . $filter['id'], $filter['name']);
  return $filters;
 }

 /**
  * Calculate the summary social filter
  * @param array[int] $soc List of selected social filters
  * @return array{X:Y} List of group relations (X: group id, Y: one of -1,0,1)
  */
 public static function filterGroups($soc)
 {
  $sgrps = array();
  if (!$soc || !is_array($soc) || !count($soc))
   return $sgrps;
  $groups = self::groups(true);
  foreach ($groups as $group)
   $sgrps[$group['id']] = -1;
  $filters = self::filters(true, false, true, $groups);
  foreach ($filters as $filter)
  {
   $filterId = $filter['id'];
   if (array_search($filterId, $soc) !== false)
   {
    foreach ($groups as $group)
    {
     $groupId = $group['id'];
     $include = $filter['include_' . $groupId];
     $value = ($include == 'Y') ? 1 : (($include == 'N') ? -1 : 0);
     if ($value > $sgrps[$groupId])
      $sgrps[$groupId] = $value;
    }
   }
  }
  $result = array();
  foreach ($groups as $group)
  {
   $id = $group['id'];
   $value = $sgrps[$id];
   if ($value != 0)
    $result[$id] = array('active' => $group['active'], 'include' => ($value == 1) ? 'Y' : 'N');
  }
  return $result;
 }
}

?>
