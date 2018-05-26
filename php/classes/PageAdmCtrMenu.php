<?php

/**
 * Description of PageAdmCtrMenu
 */
class PageAdmCtrMenu
{
 private static function processAct($act)
 {
  $centreId = WCentre::id();
  $tableGrp = WService::TABLE_GRP;
  $entityGrp = 'service group';
  $tableSrv = WService::TABLE_SRV;
  $entitySrv = 'service';
  $tablePrc = WService::TABLE_SRV_PRC;
  $entityPrc = 'procedure';
  $tableMtr = WService::TABLE_SRV_MTR;
  $entityMtr = 'master';
  $tableTip = WService::TABLE_TIP;
  $entityTip = 'price option';

  switch ($act)
  {
  case 'createGrpEntity' :
   PageAdm::createEntity($tableGrp, $entityGrp, null, array('centre_id' => $centreId));
   break;

  case 'deleteGrpEntity' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField($tableSrv, 'count(*)', "grp_id=$id")))
    echo ucfirst($entityGrp) . " $id has some {$entitySrv}s linked";
   else
    PageAdm::deleteEntity($tableGrp, $entityGrp, $id);
   break;

  case 'changeGrpSerial' :
   PageAdm::changeSerial($tableGrp, $entityGrp);
   break;

  case 'changeGrpName' :
   PageAdm::changeName($tableGrp, $entityGrp);
   break;

  case 'changeGrpTitle' :
   PageAdm::changeTitle($tableGrp, 'grp_id', $entityGrp);
   break;

  case 'changeGrpSchema' :
   PageAdm::changeField($tableGrp, $entityGrp, 'schema_id', 'value');
   break;

  case 'createSrvEntity' :
   $grpId = intval(HTTP::param('grp_id'));
   $prcId = intval(HTTP::param('prc_id'));
   if (!intval(PageAdm::db()->queryField('biz_menu_prc', 'count(*)', "id=$prcId")))
    echo ucfirst($entityPrc) . " $prcId not found";
   else
   {
    $srvId = PageAdm::createEntity($tableSrv, $entitySrv, null, array('centre_id' => $centreId, 'grp_id' => $grpId), array('centre_id' => $centreId));
    if ($srvId)
    {
     PageAdm::db()->insertValues ($tablePrc, array('srv_id' => $srvId, 'prc_id' => $prcId, 'serial' => $prcId));
     $id = PageAdm::db()->queryField($tableTip, 'ifnull(max(id),0)+1');
     $values = array();
     $values['id'] = $id;
     $values['centre_id'] = $centreId;
     $values['srv_id'] = $srvId;
     $values['serial'] = $id;
     $values['price_type_id'] = 0;
     $values['price'] = 0;
     $values['max_price'] = 0;
     $values['rest'] = 0;
     PageAdm::db()->insertValues($tableTip, $values);
    }
   }
   break;

  case 'deleteSrvEntity' :
   $id = intval(HTTP::param('id'));

   PageAdm::deleteEntity($tableSrv, $entitySrv, $id);
   break;

  case 'changeSrvSerial' :
   PageAdm::changeSerial($tableSrv, $entitySrv);
   break;

  case 'changeSrvMatcat' :
   PageAdm::changeField($tableSrv, $entitySrv, 'matcat_id', 'value');
   break;

  case 'changeSrvName' :
   PageAdm::changeName($tableSrv, $entitySrv);
   break;

  case 'changeSrvTitle' :
   PageAdm::changeTitle($tableSrv, 'srv_id', $entitySrv);
   break;

  case 'changeSrvSchema' :
   PageAdm::changeField($tableSrv, $entitySrv, 'schema_id', 'value');
   break;

  case 'changeSrvLimited' :
   PageAdm::changeFlag($tableSrv, $entitySrv, 'limited');
   break;

  case 'moveSrvEntity' :
   $id = intval(HTTP::param('id'));
   $grpId = intval(HTTP::param('value'));
   if (!intval(PageAdm::db()->queryField('com_menu_grp', 'count(*)', "id=$grpId and centre_id=$centreId")))
    echo ucfirst($entityGrp) . " $grpId not found";
   else
   {
    PageAdm::db()->modifyFields($tableSrv, array('grp_id' => $grpId), 'id=' . $id);
    if (PageAdm::db()->affected_rows != 1)
     echo "Error changing the $entitySrv $id record in the database: " . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  case 'appendPrc' :
   $srvId = HTTP::param('srv_id');
   $prcId = HTTP::param('prc_id');
   if (!intval(PageAdm::db()->queryField('biz_menu_prc', 'count(*)', "id=$prcId")))
    echo ucfirst($entityPrc) . " $prcId not found";
   else if (intval(PageAdm::db()->queryField($tablePrc, 'count(*)', "srv_id=$srvId and prc_id=$prcId")))
    echo ucfirst($entitySrv) . " $srvId has already included $entityPrc $prcId";
   else
   {
    PageAdm::db()->insertValues($tablePrc, array('srv_id' => $srvId, 'prc_id' => $prcId, 'serial' => $prcId));
    if (PageAdm::db()->affected_rows != 1)
     echo "Error adding the $entityPrc $prcId to the $entitySrv $srvId in the database: " . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  case 'removePrc' :
   $srvId = HTTP::param('srv_id');
   $prcId = HTTP::param('prc_id');
   PageAdm::db()->deleteRecords($tablePrc, array('srv_id' => $srvId, 'prc_id' => $prcId));
   if (PageAdm::db()->affected_rows != 1)
    echo "Error removing the $entityPrc $prcId from the $entitySrv $srvId in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changePrcId' :
   $srvId = HTTP::param('srv_id');
   $prcId = HTTP::param('prc_id');
   $newId = HTTP::param('new_id');
   $prc = null;
   if ($newId)
    $prc = PageAdm::db()->queryField('biz_menu_prc', 'id', "id=$newId");
   if (!$prc)
    echo "Invalid or nonexistent procedure id: '$newId'";
   else
   {
    if (!PageAdm::db()->deleteRecords($tablePrc, array('srv_id' => $srvId)))
     echo "Error removing {$entityPrc}s from the the $entitySrv $srvId in the database: " . DB::lastQuery();
    else
    {
     PageAdm::db()->insertValues($tablePrc, array('srv_id' => $srvId, 'prc_id' => $newId, 'serial' => $newId));
     if (PageAdm::db()->affected_rows != 1)
      echo "Error adding the $entityPrc $newId to the $entitySrv $srvId in the database: " . DB::lastQuery();
     else
      echo 'OK';
    }
   }
   break;

  case 'changePrcSerial' :
   $srvId = HTTP::param('srv_id');
   $prcId = HTTP::param('prc_id');
   $serial = HTTP::param('serial');
   $field = 'serial';
   $where = array('srv_id' => $srvId, 'prc_id' => $prcId);
   if (strlen($serial))
    PageAdm::db()->modifyField($tablePrc, $field, 'i', intval($serial), $where);
   else
    PageAdm::db()->modifyFields($tablePrc, array($field => 'null'), $where);
   $query = DB::lastQuery();
   if (PageAdm::db()->queryField($tablePrc, $field, $where) != $serial)
    echo "Error changing $entityPrc $prcId serial number to '$serial': " . $query;
   else
    echo 'OK';
   break;

  case 'appendMtr' :
   $srvId = HTTP::param('srv_id');
   $mtrId = HTTP::param('mtr_id');
   if (!intval(PageAdm::db()->queryField('com_master', 'count(*)', "id=$mtrId and centre_id=$centreId")))
    echo ucfirst($entityMtr) . " $mtrId not found";
   else if (!intval(PageAdm::db()->queryField('com_master', 'count(*)', "id=$mtrId and centre_id=$centreId and for_service is not null")))
    echo "Employee $mtrId can't be assigned to a service";
   else if (intval(PageAdm::db()->queryField($tableMtr, 'count(*)', "srv_id=$srvId and master_id=$mtrId")))
    echo ucfirst($entitySrv) . " $srvId has already included $entityMtr $mtrId";
   else
   {
    PageAdm::db()->insertValues($tableMtr, array('srv_id' => $srvId, 'master_id' => $mtrId, 'serial' => $mtrId));
    if (PageAdm::db()->affected_rows != 1)
     echo "Error adding the $entityMtr $mtrId to the $entitySrv $srvId in the database: " . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  case 'removeMtr' :
   $srvId = HTTP::param('srv_id');
   $mtrId = HTTP::param('mtr_id');
   PageAdm::db()->deleteRecords($tableMtr, array('srv_id' => $srvId, 'master_id' => $mtrId));
   if (PageAdm::db()->affected_rows != 1)
    echo "Error removing the $entityMtr $mtrId from the $entitySrv $srvId in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeMtrSerial' :
   $srvId = HTTP::param('srv_id');
   $mtrId = HTTP::param('mtr_id');
   $serial = HTTP::param('serial');
   $field = 'serial';
   $where = array('srv_id' => $srvId, 'master_id' => $mtrId);
   if (strlen($serial))
    PageAdm::db()->modifyField($tableMtr, $field, 'i', intval($serial), $where);
   else
    PageAdm::db()->modifyFields($tableMtr, array($field => 'null'), $where);
   $query = DB::lastQuery();
   if (PageAdm::db()->queryField($tableMtr, $field, $where) != $serial)
    echo "Error changing $entityMtr $mtrId serial number to '$serial': " . $query;
   else
    echo 'OK';
   break;

  case 'createTipEntity' :
   $brandId = WBrand::id();
   $srvId = intval(HTTP::param('srv_id'));
   $lvlId = intval(HTTP::param('lvl_id'));
   if ($lvlId && !intval(PageAdm::db()->queryField('com_level', 'count(*)', "id=$lvlId and brand_id=$brandId")))
    echo ucfirst($entityTip) . " $lvlId not found";
   //else if ($lvlId && intval(PageAdm::db()->queryField(WService::TABLE_TIP, 'count(*)', "srv_id=$srvId and level_id=$lvlId")))
   // echo ucfirst($entityTip) . " $lvlId already exists in $entitySrv $srvId";
   //else if (!$lvlId && intval(PageAdm::db()->queryField(WService::TABLE_TIP, 'count(*)', "srv_id=$srvId and level_id is null")))
   // echo "Default $entityTip already exists in $entitySrv $srvId";
   else
   {
    $id = PageAdm::db()->queryField($tableTip, 'ifnull(max(id),0)+1');
    $values['id'] = $id;
    $values['centre_id'] = $centreId;
    $values['srv_id'] = $srvId;
    $values['level_id'] = ($lvlId ? $lvlId : 'null');
    $values['serial'] = $id;
    $values['price_type_id'] = 0;
    $values['price'] = 0;
    $values['max_price'] = 0;
    $values['rest'] = 0;
    PageAdm::db()->insertValues($tableTip, $values);
    if (PageAdm::db()->affected_rows != 1)
     echo "Error adding the $entityTip $lvlId record to the database: " . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  case 'deleteTipEntity' :
   PageAdm::deleteEntity($tableTip, $entityTip);
   break;

  case 'changeTipSerial' :
   PageAdm::changeSerial($tableTip, $entityTip);
   break;

  case 'changeTipName' :
   PageAdm::changeName($tableTip, $entityTip, 'centre_id=' . $centreId);
   break;

  case 'changeTipTitle' :
   PageAdm::changeTitle($tableTip, 'tip_id', $entityTip);
   break;

  case 'changeTipLevel' :
   $brandId = WBrand::id();
   $id = intval(HTTP::param('id'));
   $lvlId = intval(HTTP::param('lvl_id'));
   $newId = intval(HTTP::param('new_id'));
   $srvId = intval(PageAdm::db()->queryField(WService::TABLE_TIP, 'srv_id', "id=$id"));
   if ($newId == $lvlId)
    echo 'OK';
   else if (!$srvId)
    echo ucfirst($entitySrv) . " $entityTip $id not found";
   else if ($newId && !intval(PageAdm::db()->queryField('com_level', 'count(*)', "id=$newId and brand_id=$brandId")))
    echo ucfirst($entityTip) . " $newId not found";
   else
   {
    if ($newId && intval(PageAdm::db()->queryField(WService::TABLE_TIP, 'count(*)', "id != $id and srv_id=$srvId and level_id=$newId")))
     echo ucfirst($entityTip) . " $newId already exists in $entitySrv $srvId";
    else if (!$newId && intval(PageAdm::db()->queryField(WService::TABLE_TIP, 'count(*)', "id != $id and srv_id=$srvId and level_id is null")))
     echo "Default $entityTip already exists in $entitySrv $srvId";
    else
    {
     PageAdm::db()->modifyFields($tableTip, array('level_id' => ($newId ? $newId : 'null')), 'id=' . $id);
     if (PageAdm::db()->affected_rows != 1)
      echo "Error changing the $entityTip $id record in the database: " . DB::lastQuery();
     else
      echo 'OK';
    }
   }
   break;

  case 'changeTipField' :
   $id = intval(HTTP::param('id'));
   $field = HTTP::param('field');
   $value = HTTP::param('value');
   PageAdm::db()->modifyFields($tableTip, array($field => ($value ? $value : 'null')), 'id=' . $id);
   if (PageAdm::db()->affected_rows != 1)
    echo "Error changing the $entityTip $id record in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeTipPriceType' :
   $id = intval(HTTP::param('id'));
   $typeId = intval(HTTP::param('value'));
   if (!PageAdm::db()->queryField('art_price_type', 'count(*)', "id=$typeId"))
    echo "Price type $typeId not found";
   else
   {
    if (!PageAdm::db()->modifyFields($tableTip, array('price_type_id' => $typeId), 'id=' . $id))
     echo "Error changing the $entityTip $id record in the database: " . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  if (!WCentre::initCurrent(Base::index(), true))
   return false;
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
  $langs = Lang::map();
  $centreId = WCentre::id();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
td.table { padding:0 2px 4px; }
</style>
<script>
var entityGrp='service group';
var entitySrv='service';
var entityPrc='procedure';
var entityMtr='master';
var entityTip='tip';
var entityLvl='level';
function createGrpEntity()
{
 A.createEntity(entityGrp,'createGrpEntity');
}
function deleteGrpEntity(id)
{
 var rowid2='grprow2-'+id;
 if(A.deleteEntity(id,entityGrp,'deleteGrpEntity','grprow-'+id))
  el(rowid2).parentNode.removeChild(el(rowid2));
}
function changeGrpSerial(node,id)
{
 A.changeSerial(node,id,entityGrp,'changeGrpSerial')
}
function changeGrpName(node,id)
{
 A.changeName(node,id,entityGrp,'changeGrpName');
}
function changeGrpTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entityGrp,'changeGrpTitle');
}
<?php
$schemes = PageAdm::db()->queryRecords('com_centre_schema', 'id,name', "centre_id=$centreId", 'serial,id');
$schemaObj = $schemes ? implode(',', array_map(array('Util', 'mapJsonObject'), $schemes)) : null;
$schemaStr = $schemes ? implode(', ', array_map(array('Util', 'mapJsonString'), $schemes)) : null;
?>
var schemaObj={<?php echo $schemaObj;?>};
var schemaStr="<?php echo $schemaStr;?>";
function changeGrpSchema(node,id)
{
 A.changeItem(node,id,entityGrp,'schema','changeGrpSchema',schemaStr,schemaObj);
}
function inputNewPrcId(srv)
{
 return A.inputEntityId('new '+entityPrc,'service '+srv);
}
function createSrvEntity(grp)
{
 var prc=A.inputEntityId('a '+entityPrc,entityGrp+' "'+grp+'"');
 if(prc)
  A.createEntity(entitySrv,'createSrvEntity','grp_id='+grp+'&prc_id='+prc);
}
function deleteSrvEntity(id)
{
 var levels=el('srvrow-'+id).nextElementSibling;
 A.deleteEntity(id,entitySrv,'deleteSrvEntity','srvrow-'+id);
 levels.parentNode.removeChild(levels);
}
function changeSrvSerial(node,id)
{
 A.changeSerial(node,id,entitySrv,'changeSrvSerial')
}
<?php
$matcats = PageAdm::db()->queryRecords('com_matcat', 'id,name', "centre_id=$centreId", 'serial,id');
$matcatObj = $matcats ? implode(',', array_map(array('Util', 'mapJsonObject'), $matcats)) : null;
$matcatStr = $matcats ? implode(', ', array_map(array('Util', 'mapJsonString'), $matcats)) : null;
?>
var matcatObj={<?php echo $matcatObj;?>};
var matcatStr="<?php echo $matcatStr;?>";
function changeSrvMatcat(node,id)
{
 A.changeItem(node,id,entitySrv,'resource category','changeSrvMatcat',matcatStr,matcatObj);
}
function changeSrvName(node,id)
{
 A.changeName(node,id,entitySrv,'changeSrvName');
}
function changeSrvTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entitySrv,'changeSrvTitle');
}
function changeSrvSchema(node,id)
{
 A.changeItem(node,id,entitySrv,'schema','changeSrvSchema',schemaStr,schemaObj);
}
function changeSrvLimited(node,id)
{
 if(A.changeFlag(node,id,entitySrv,'limited','changeSrvLimited'))
  document.location.reload(true);
}
<?php
$groups = PageAdm::db()->queryRecords('com_menu_grp', 'id,name', "centre_id=$centreId", 'serial,id');
$groupsObj = $groups ? implode(',', array_map(array('Util', 'mapJsonObject'), $groups)) : null;
$groupsStr = $groups ? implode(', ', array_map(array('Util', 'mapJsonString'), $groups)) : null;
?>
var groupsObj={<?php echo $groupsObj;?>};
var groupsStr="<?php echo $groupsStr;?>";
function moveSrvEntity(id)
{
 A.changeItem(null,id,entitySrv,'group','moveSrvEntity',groupsStr,groupsObj);
}
function appendPrc(srv)
{
 var prc=inputNewPrcId(srv);
 if(!prc)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=appendPrc&srv_id='+srv+'&prc_id='+prc),false);
 req.send(null);
 var error='Error adding new '+entityPrc+' to the '+entitySrv+' '+srv+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function removePrc(srv,prc)
{
 if(!confirm('Remove the '+entityPrc+' '+prc+' from the '+entitySrv+' '+srv+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=removePrc&srv_id='+srv+'&prc_id='+prc),false);
 req.send(null);
 var error='Error deleting the '+entityPrc+' '+prc+' from the '+entitySrv+' '+srv+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changePrcId(srv,oldValue)
{
 var newValue=inputNewPrcId(srv);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changePrcId&srv_id='+srv+'&prc_id='+oldValue+'&new_id='+newValue,false);
 req.send(null);
 var error='Error changing the '+entitySrv+' '+srv+' '+entityPrc+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changePrcSerial(node,srv,prc)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new serial number for the '+entityPrc+' '+prc+':',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changePrcSerial&srv_id='+srv+'&prc_id='+prc+'&serial='+newValue),false);
 req.send(null);
 var error='Error changing the '+entityPrc+' '+prc+' serial number on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function appendMtr(srv)
{
 var mtr=A.inputEntityId('new '+entityMtr,entitySrv+' '+srv);
 if(!mtr)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=appendMtr&srv_id='+srv+'&mtr_id='+mtr),false);
 req.send(null);
 var error='Error adding new '+entityMtr+' to the '+entitySrv+' '+srv+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function removeMtr(srv,mtr)
{
 if(!confirm('Remove the '+entityMtr+' '+mtr+' from the '+entitySrv+' '+srv+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=removeMtr&srv_id='+srv+'&mtr_id='+mtr),false);
 req.send(null);
 var error='Error deleting the '+entityMtr+' '+mtr+' from the '+entitySrv+' '+srv+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeMtrSerial(node,srv,mtr)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new serial number for the '+entityMtr+' '+mtr+':',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeMtrSerial&srv_id='+srv+'&mtr_id='+mtr+'&serial='+newValue),false);
 req.send(null);
 var error='Error changing the '+entityMtr+' '+mtr+' serial number on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function createTipEntity(srv)
{
 var lvl=A.inputEntityId('a '+entityLvl,entitySrv+' "'+srv+'"',0);
 if((lvl===null)||(lvl===false))
 //if(!lvl&&(lvl!=='')&&(lvl!=='0'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createTipEntity&srv_id='+srv+'&lvl_id='+lvl),false);
 req.send(null);
 var error='Error adding the '+entityLvl+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteTipEntity(id)
{
 A.deleteEntity(id,entityLvl,'deleteTipEntity','lvlrow-'+id);
}
function changeTipSerial(node,id)
{
 A.changeSerial(node,id,entityLvl,'changeTipSerial')
}
function changeTipName(node,id)
{
 A.changeName(node,id,entityTip,'changeTipName',true);
}
function changeTipTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entityTip,'changeTipTitle');
}
function changeTipLevel(srv,id,oldValue)
{
 var newValue=A.inputEntityId('new '+entityLvl,entitySrv+' "'+srv+'"',0);
 if((newValue===null)||(newValue===false))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeTipLevel&id='+id+'&lvl_id='+oldValue+'&new_id='+newValue,false);
 req.send(null);
 var error='Error changing the row '+id+' '+entityLvl+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeTipField(node,id,field)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new '+field+' for the '+entityLvl+' '+id+':',oldValue);
 if((newValue===null)||(newValue==oldValue))
  return;
 if((newValue==='')&&(field!='duration'))
  return alert('Invalid '+field+' value: it must be a number');
 if((newValue!=='')&&(newValue!=='0')&&(newValue!=(1*newValue)))
  return alert('Invalid '+field+' value: it must be a number');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeTipField&id='+id+'&field='+field+'&value='+newValue),false);
 req.send(null);
 var error='Error changing the '+entityLvl+' '+id+' '+field+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
<?php
$priceTypes = PageAdm::db()->queryRecords('art_price_type', 'id,name', null, 'id');
$priceTypesObj = $priceTypes ? implode(',', array_map(array('Util', 'mapJsonObject'), $priceTypes)) : null;
$priceTypesStr = $priceTypes ? implode(', ', array_map(array('Util', 'mapJsonString'), $priceTypes)) : null;
?>
var priceTypesObj={<?php echo $priceTypesObj;?>};
var priceTypesStr="<?php echo $priceTypesStr;?>";
function changeTipPriceType(node,id)
{
 A.changeItem(node,id,entityTip,'price type','changeTipPriceType',priceTypesStr,priceTypesObj,{def:0});
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo "<a href='ctr-$centreId/'>" . PageAdm::title() . '</a>';?></h1>
<table class="main block">
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='300'>Service group</th>
<?php
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="200" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
<th width='100'><a href='ctrd-<?php echo $centreId; ?>/'>Schema</a></th>
<th width='1'><input type="button" value="Create group" onclick="createGrpEntity()"/></th>
</tr>
<?php
 $fields = 'id,serial,name';
 $fields .= ',schema_id,(select name from com_centre_schema where id=a.schema_id)schema_name';
 foreach ($langs as $lang => $title)
  $fields .= ',(select title from ' . WService::TABLE_GRP . '_abc where grp_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $grps = PageAdm::db()->queryArrays(WService::TABLE_GRP . ' a', $fields, 'centre_id=' . $centreId, 'serial,id');
 if ($grps)
 {
  foreach ($grps as $grp)
  {
   $grpId = $grp['id'];
   $grpSerial = $grp['serial'];
   $grpName = htmlspecialchars($grp['name']);
   $schemaId = $grp['schema_id'];
   $schemaName = htmlspecialchars($grp['schema_name']);
   echo "<tr id='grprow-$grpId' style='background:#ddf;font-weight:bold;'>\n";
   echo "<th class='right' rowspan='2'>$grpId</th>\n";
   echo "<td class='right' onclick='changeGrpSerial(this,$grpId)'>$grpSerial</td>\n";
   echo "<td class='left' onclick='changeGrpName(this,$grpId)'>$grpName</td>\n";
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($grp['title_' . $lang]);
    echo "<td class='left' onclick='changeGrpTitle(this,$grpId,\"$lang\")'>$value</td>\n";
   }
   echo "<td class='left' onclick='changeGrpSchema(this,$grpId)'>" . htmlspecialchars($schemaName) . "</td>\n";
   echo "<th><input type='button' value='Delete group' onclick='deleteGrpEntity($grpId)'/></th>\n";
   echo "</tr>\n";

   $colspan = 4 + count($langs);
   echo "<tr id='grprow2-$grpId'><td class='table' colspan='$colspan'>\n";
   self::showSrv($langs, $grpId, $grpName);
   echo "</td></tr>\n";
  }
 }
 else
  echo DB::lastQuery();
?></table>
</body>
</html>
<?php
  return true;
 }

 private static function  showSrv($langs, $grpId, $grpName)
 {
  $centreId = WCentre::id();
  $grp = PageAdm::makeEntityText($grpId, $grpName);
  echo "<table class='main'>\n";
  echo "<caption>Services of the group \"$grp\"</caption>\n";
  echo "<tr>\n";
  echo "<th width='50'>Id</th>\n";
  echo "<th width='50'>Nr</th>\n";
  echo "<th width='300'><a href='mprcs/'>Procedures</a></th>\n";
  echo "<th width='300'><a href='ctrf-$centreId/'>Masters</a></th>\n";
  echo "<th width='200'><a href='ctrr-$centreId/'>Res. group</a></th>\n";
  echo "<th width='200'>Service</th>\n";
  foreach ($langs as $lang => $Lang)
   echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
  echo "<th width='50'><a href='ctrd-$centreId/'>Schema</a></th>\n";
  echo "<th width='50'>Limited</th>\n";
  echo "<th width='1'><input type='button' value='Create' onclick='createSrvEntity(\"$grp\")'/></th>\n";
  echo "</tr>\n";
  $fields = 'id,serial,name,limited';
  $fields .= ',matcat_id,(select name from com_matcat where id=a.matcat_id)matcat_name';
  $fields .= ',schema_id,(select name from com_centre_schema where id=a.schema_id)schema_name';
  foreach ($langs as $lang => $title)
   $fields .= ',(select title from ' . WService::TABLE_SRV . '_abc where srv_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
  $where = 'centre_id=' . $centreId . ' and grp_id=' . $grpId;
  $srvs = PageAdm::db()->queryArrays(WService::TABLE_SRV . ' a', $fields, $where, 'serial,id');
  if ($srvs)
  {
   foreach ($srvs as $srv)
   {
    $srvId = $srv['id'];
    $srvSerial = $srv['serial'];
    $srvName = htmlspecialchars($srv['name']);
    $srvLimited = !!$srv['limited'];
    $srvMatcatId = $srv['matcat_id'];
    $srvMatcatName = htmlspecialchars($srv['matcat_name']);
    $srvSchemaId = $srv['schema_id'];
    $srvSchemaName = htmlspecialchars($srv['schema_name']);
    echo "<tr id='srvrow-$srvId'>\n";
    echo "<th class='right' rowspan='2'><a href='srv-$srvId/'>$srvId</a></th>\n";
    echo "<td class='right' onclick='changeSrvSerial(this,$srvId)'>$srvSerial</td>\n";
    echo "<td style='background:#dfd;'>\n";
    self::showPrc($srvId);
    echo "</td>\n";
    echo "<td style='background:#fdd;'>\n";
    self::showMtr($srvId);
    echo "</td>\n";
    echo "<td onclick='changeSrvMatcat(this,$srvId)'>" . htmlspecialchars($srvMatcatName) . "</td>\n";
    echo "<td class='left' onclick='changeSrvName(this,$srvId)'>$srvName</td>\n";
    foreach ($langs as $lang => $title)
     echo "<td class='left' onclick='changeSrvTitle(this,$srvId,\"$lang\")'>" . htmlspecialchars($srv['title_' . $lang]) . "</td>\n";
    echo "<td onclick='changeSrvSchema(this,$srvId)'>" . htmlspecialchars($srvSchemaName) . "</td>\n";
    echo "<td" . ($srvLimited ? " class='checked'" : null) . " onclick='changeSrvLimited(this,$srvId)'></td>\n";
    echo "<th>" .
      "<input type='button' value='Delete' onclick='deleteSrvEntity($srvId)'/>" .
      "<input type='button' value='Move...' onclick='moveSrvEntity($srvId)'/>" .
      "</th>\n";
    echo "</tr>\n";

    $colspan = 8 + count($langs);
    echo "<tr><td colspan='$colspan' class='table' style='padding-left:50px;padding-bottom:15px;background:#ffd;'>\n";
    self::showTip($langs, $srvId, $srvName, $srvLimited);
    echo "</td></tr>\n";
   }
  }
  echo "</table>\n";
 }

 private static function showPrc($srvId)
 {
  $fields = 'prc_id,serial' .
    ',(select name from biz_menu_prc where id=a.prc_id)prc_name' .
    ',(select c.name from biz_menu_cat c,biz_menu_prc p where c.id=p.cat_id and p.id=a.prc_id)cat_name';
  $prcs = PageAdm::db()->queryRecords(WService::TABLE_SRV_PRC . ' a', $fields, 'srv_id=' . $srvId, 'serial,prc_id');
  if (!$prcs)
  {
   echo "<div onclick='appendPrc($srvId)'>Click to add a procedure</div>\n";
  }
  else if (count($prcs) == 1)
  {
   $prc = $prcs[0];
   $prcId = $prc[0];
   $prcName = htmlspecialchars($prc[2]);
   $catName = htmlspecialchars($prc[3]);
   echo "<table class='main' width='100%'><tr>\n";
   echo "<td class='left' onclick='changePrcId($srvId,$prcId)'>" . PageAdm::makeEntityText($prcId, $prcName, $catName) . "</td>\n";
   echo "<th width='1'><input type='button' value='+' onclick='appendPrc($srvId)' /></th>\n";
   echo "</tr></table>\n";
  }
  else
  {
   echo "<table class='main small' width='100%'>\n";
   echo "<tr><th width='1'>Nr</th><th>Package</th><th width='1'><input type='button' value='+' onclick='appendPrc($srvId)' /></th></tr>\n";
   foreach ($prcs as $prc)
   {
    $prcId = $prc[0];
    $prcName = htmlspecialchars($prc[2]);
    $catName = htmlspecialchars($prc[3]);
    echo "<tr>\n";
    echo "<th class='right' onclick='changePrcSerial(this,$srvId,$prcId)'>$prc[1]</th>\n";
    echo "<td class='left'>" . PageAdm::makeEntityText($prcId, $prcName, $catName) . "</td>\n";
    echo "<th><input type='button' value='X' onclick='removePrc($srvId,$prcId)' /></th>\n";
    echo "</tr>\n";
   }
   echo "</table>\n";
  }
 }

 private static function showMtr($srvId)
 {
  $centreId = WCentre::id();
  $fields = 'master_id,serial' .
    ',(select trim(concat(c.firstname,\' \',c.lastname)) from biz_client c,com_master m where c.id=m.client_id and m.id=a.master_id)master_name' .
    ',(select job_title from com_master where id=a.master_id)job_title';
  $where = "srv_id=$srvId and master_id in (select id from com_master where centre_id=$centreId and for_service is not null)";
  $masters = PageAdm::db()->queryRecords(WService::TABLE_SRV_MTR . ' a', $fields, $where, 'serial,master_id');
  if (!$masters)
  {
   echo "<div onclick='appendMtr($srvId)'>Click to add a master</div>\n";
  }
  else
  {
   echo "<table class='main small' width='100%'>\n";
   echo "<tr><th width='1'>Nr</th><th>Name</th><th width='1'><input type='button' value='+' onclick='appendMtr($srvId)' /></th></tr>\n";
   foreach ($masters as $master)
   {
    $masterId = $master[0];
    $masterName = htmlspecialchars($master[2]);
    $jobTitle = htmlspecialchars($master[3]);
    echo "<tr>\n";
    echo "<th class='right' onclick='changeMtrSerial(this,$srvId,$masterId)'>$master[1]</th>\n";
    echo "<td class='left'>" . PageAdm::makeEntityText($masterId, $masterName, $jobTitle) . "</td>\n";
    echo "<th><input type='button' value='X' onclick='removeMtr($srvId,$masterId)' /></th>\n";
    echo "</tr>\n";
   }
   echo "</table>\n";
  }
 }

 private static function showTip($langs, $srvId, $srvName, $srvLimited)
 {
  $brandId = WBrand::id();
  $centreId = WCentre::id();
  $srv = PageAdm::makeEntityText($srvId, $srvName);
  echo "<table class='main' style='background:#fff;'>\n";
  echo "<caption>Price options of the service \"$srv\"</caption>\n";
  echo "<tr>\n";
  echo "<th width='50'>Id</th>\n";
  echo "<th width='50'>Nr</th>\n";
  echo "<th width='200'>Name</th>\n";
  foreach ($langs as $lang => $Lang)
   echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
  echo "<th width='200'>Level</th>\n";
  echo "<th width='100'>Duration</th>\n";
  echo "<th width='100'>Type</th>\n";
  echo "<th width='100'>Price</th>\n";
  echo "<th width='100'>MaxPrice</th>\n";
  if ($srvLimited)
   echo "<th width='50'>Rest</th>\n";
  echo "<th width='1'><input type='button' value='Create option' onclick='createTipEntity(\"$srv\")'/></th>\n";
  echo "</tr>\n";
  $fields = 'id,serial,name,level_id,duration,price_type_id,price,max_price,rest' .
    ",(" . ($brandId ? "select name from com_level where id=a.level_id and brand_id=$brandId" : "''" ) . ")level_name" .
    ",(select name from art_price_type where id=a.price_type_id)price_type_name";
  foreach ($langs as $lang => $title)
   $fields .= ',(select title from ' . WService::TABLE_TIP . '_abc where tip_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
  $where = 'centre_id=' . $centreId . ' and srv_id=' . $srvId;
  $tips = PageAdm::db()->queryArrays(WService::TABLE_TIP . ' a', $fields, $where, 'serial,id');
  //echo DB::lastQuery();
  if ($tips)
  {
   foreach ($tips as $tip)
   {
    $id = $tip['id'];
    $nr = $tip['serial'];
    $name = $tip['name'];
    $levelId = intval($tip['level_id']);
    $duration = $tip['duration'];
    $priceTypeId = $tip['price_type_id'];
    $price = $tip['price'];
    $maxPrice = $tip['max_price'];
    $rest = $tip['rest'];
    $levelName = htmlspecialchars($tip['level_name']);
    $priceTypeName = htmlspecialchars($tip['price_type_name']);
    $level = $levelId ? PageAdm::makeEntityText($levelId, $levelName) : '0, Default';

    echo "<tr id='lvlrow-$id'>\n";
    echo "<th class='right'>$id</th>\n";
    echo "<td class='right' onclick='changeTipSerial(this,$id)'>$nr</td>\n";
    echo "<td class='left' onclick='changeTipName(this,$id)'>$name</td>\n";
    foreach ($langs as $lang => $Lang)
     echo "<td class='left' onclick='changeTipTitle(this,$id,\"$lang\")'>" . htmlspecialchars($tip['title_' . $lang]) . "</td>\n";
    echo "<td class='left' onclick='changeTipLevel($srvId,$id,$levelId)'>$level</td>\n";
    echo "<td class='right' onclick='changeTipField(this,$id,\"duration\")'>$duration</td>\n";
    echo "<td class='left' onclick='changeTipPriceType(this,$id)'>$priceTypeName</td>\n";
    echo "<td class='right' onclick='changeTipField(this,$id,\"price\")'>$price</td>\n";

    if ($priceTypeId == WCentre::PRICE_TYPE_RANGE)
     echo "<td class='right' onclick='changeTipField(this,$id,\"max_price\")'>$maxPrice</td>\n";
    else
     echo "<th></th>\n";

    if ($srvLimited)
     echo "<td class='right' onclick='changeTipField(this,$id,\"rest\")'>$rest</td>\n";

    if (count($tips) > 1)
     echo "<th><input type='button' value='Delete option' onclick='deleteTipEntity($id)'/></th>\n";
    else
     echo "<th></th>\n";

    echo "</tr>\n";
   }
  }
  echo "</table>\n";
 }

}

?>
