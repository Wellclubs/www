<?php

/**
 * Description of PageAdmCtrEmp
 */
class PageAdmCtrEmp
{
 private static function processAct($act)
 {
  $centreId = WCentre::id();
  $table = 'com_master';
  $entity = 'master';
  switch ($act)
  {
  case 'createMaster' :
   $clientId = intval(HTTP::param('client_id'));
   $client = PageAdm::db()->queryField(WClient::TABLE_CLIENT, 'id', 'id=' . $clientId);
   if (!$client)
    echo "Invalid or nonexistent client id: '$clientId'";
   else
   {
    $id = PageAdm::db()->queryField($table, 'ifnull(max(id),0)+1');
    $where = array('id' => $id, 'centre_id' => $centreId, 'client_id' => $clientId, 'serial' => $id);
    if ($clientId == WCentre::memberId())
     $where['role_id'] = WPriv::ROLE_OWNER;
    PageAdm::db()->insertValues($table, $where);
    if (PageAdm::db()->affected_rows == 1)
     echo 'OK';
    else
     echo "Error adding the new $entity $id record to the database: " . DB::lastQuery();
   }
   break;

  case 'deleteMaster' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity('com_master', $entity, $id);
   break;

  case 'changeSerial' :
   PageAdm::changeSerial('com_master', $entity);
   break;

  case 'changeClient' :
   $id = intval(HTTP::param('id'));
   $client = intval(HTTP::param('client'));
   if (!$client)
    echo "Empty client";
   else if (!PageAdm::db()->queryField('biz_client', 'id', 'id=' . $client))
    echo "Invalid client: '$client'";
   else if (!PageAdm::db()->queryField($table, 'id', 'centre_id=' . $centreId . ' and client_id<>' . $client))
    echo "Duplicated client: '$client'";
   else if (!PageAdm::db()->modifyFields($table, array('client_id' => $client), 'id=' . $id))
    echo "Error changing the $entity $id record in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeLevel' :
   $id = HTTP::param('id');
   $levelId = HTTP::param('value');
   $level = 'null';
   if ($levelId)
    $level = PageAdm::db()->queryField('com_level', 'id', "id=$levelId and brand_id=" . WBrand::id());
   if (!$level)
    echo "Invalid or nonexistent level id: '$levelId'";
   else
   {
    PageAdm::db()->modifyFields($table, array('level_id' => $level), 'id=' . $id);
    if (PageAdm::db()->affected_rows == 1)
     echo 'OK';
    else
     echo "Error changing the $entity $id record in the database: " . DB::lastQuery();
   }
   break;

  case 'changeRole' :
   $id = HTTP::param('id');
   $roleId = HTTP::param('value');
   $clientId = PageAdm::db()->queryField($table, 'client_id', 'id=' . $id);
   if ($clientId == WCentre::memberId())
    $roleId = WPriv::ROLE_OWNER;
   PageAdm::db()->deleteRecords('com_master_priv', 'master_id=' . $id);
   if (!$roleId)
   {
    $role = 'null';
    $roleOld = PageAdm::db()->queryField($table, 'role_id', 'id=' . $id);
    if ($roleOld)
     PageAdm::db()->query('insert into com_master_priv(master_id,priv_id)' .
       ' select ' . $id . ',priv_id from art_master_role_priv where role_id=' . $roleOld);
   }
   else
    $role = PageAdm::db()->queryField('art_master_role', 'id', 'id=' . $roleId);
   if (!$role)
    echo "Invalid or nonexistent role id: '$roleId'";
   else
   {
    PageAdm::db()->modifyFields($table, array('role_id' => $role), 'id=' . $id);
    if (PageAdm::db()->affected_rows == 1)
     echo 'OK';
    else
     echo "Error changing the $entity $id record in the database: " . DB::lastQuery();
   }
   break;

  case 'changeJob' :
   $id = HTTP::param('id');
   $job = HTTP::param('job');
   PageAdm::db()->modifyFields($table, array('job_title' => DB::str($job)), 'id=' . $id);
   if (PageAdm::db()->affected_rows == 1)
    echo 'OK';
   else
    echo "Error changing the $entity $id record in the database: " . DB::lastQuery();
   break;

  case 'changeFlag' :
   $id = intval(HTTP::param('id'));
   $flag = HTTP::param('flag');
   $value = HTTP::param('value');
   $field = ($flag == 'A') ? 'can_connect' : (($flag == 'M') ? 'for_service' : (($flag == 'F') ? 'sel_by_name' : null));
   $whereMtr = "srv_id in (select id from " . WService::TABLE_SRV . " where centre_id=$centreId) and master_id=$id";
   if (($flag == 'M') && !$value && PageAdm::db()->queryField(WService::TABLE_SRV_MTR, 'count(*)', $whereMtr))
     echo "Master $id is assigned to some services";
   else
    PageAdm::changeFlag($table, $entity, $field, $value);
   break;

  case 'grantPriv' :
   $id = intval(HTTP::param('id'));
   $priv = intval(HTTP::param('priv'));
   if (PageAdm::db()->insertValues('com_master_priv', array('master_id' => $id, 'priv_id' => $priv)))
    echo 'OK';
   else
    echo "Error granting the privilege $priv to the $entity $id: " . DB::lastQuery();
   break;

  case 'revokePriv' :
   $id = intval(HTTP::param('id'));
   $priv = intval(HTTP::param('priv'));
   if (PageAdm::db()->deleteRecords('com_master_priv', "master_id=$id and priv_id=$priv"))
    echo 'OK';
   else
    echo "Error revoking the privilege $priv from the $entity $id: " . DB::lastQuery();
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
  $centreId = WCentre::id();
  $prgrs = WPriv::getListPrGr();
  $privs = WPriv::getListPriv();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
table.internal,table.internal th { border-top:none; }
</style>
<script>
var entity='master';
function createMaster()
{
 var clientId=prompt('Input a new '+entity+'\'s client id');
 if(clientId==null)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createMaster&client_id='+clientId),false);
 req.send(null);
 var error='Error adding the new '+entity+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteMaster(id)
{
 A.deleteEntity(id,entity,'deleteMaster');
}
function changeSerial(node,id)
{
 A.changeSerial(node,id,entity);
}
function changeClient(node,id)
{
 A.changeField(node,id,entity,'client','changeClient');
}
<?php
$levels = PageAdm::db()->queryRecords('com_level', 'id,name', 'brand_id=' . WBrand::id(), 'serial');
$levelsObj = $levels ? implode(',', array_map(array('Util', 'mapJsonObject'), $levels)) : null;
$levelsStr = $levels ? implode(', ', array_map(array('Util', 'mapJsonString'), $levels)) : null;
?>
var levelsObj={<?php echo $levelsObj;?>};
var levelsStr="<?php echo $levelsStr;?>";
function changeLevel(node,id)
{
 A.changeItem(node,id,entity,'level','changeLevel',levelsStr,levelsObj);
}
<?php
$roles = PageAdm::db()->queryRecords('art_master_role', 'id,name', null, 'serial');
$rolesObj = $roles ? implode(',', array_map(array('Util', 'mapJsonObject'), $roles)) : null;
$rolesStr = $roles ? implode(', ', array_map(array('Util', 'mapJsonString'), $roles)) : null;
?>
var rolesObj={<?php echo $rolesObj;?>};
var rolesStr="<?php echo $rolesStr;?>";
function changeRole(node,id)
{
 A.changeItem(node,id,entity,'role','changeRole',rolesStr,rolesObj,{reload:true});
}
function changeJob(node,id)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new job title:', oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeJob&id='+id+'&job='+newValue,false);
 req.send(null);
 var error='Error changing the '+entity+' '+id+' job title on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function changeFlag(node,id,flag)
{
 var name={'A':'Active','M':'Master','F':'Famous'}[flag];
 var oldValue=(node.className=='checked');
 var F='the flag "'+name+'"';
 var M=entity+' '+id;
 var text=oldValue?('Reset '+F+' for '+M):('Set '+F+' for '+M);
 if(!confirm(text+'?'))
  return;
 var value=oldValue?'':'1';
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeFlag&id='+id+'&flag='+flag+'&value='+value,false);
 req.send(null);
 var error='Error changing '+F+' for '+M+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.className=oldValue?'':'checked';
}
function changePriv(node,id,name,priv,privName)
{
 var oldValue=(node.className=='checked');
 var P='the privilege "'+privName+'"';
 var M=entity+' '+id+' ("'+name+'")';
 var text=oldValue?('Revoke '+P+' from '+M):('Grant '+P+' to '+M);
 if(!confirm(text+'?'))
  return;
 var act=oldValue?'revokePriv':'grantPriv';
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act='+act+'&id='+id+'&priv='+priv,false);
 req.send(null);
 var error='Error changing '+P+' for '+M+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.className=oldValue?'':'checked';
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main block">
<caption><?php echo "<a href='ctr-$centreId'>" . PageAdm::title() . '</a>';?></caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='50'>Client</th>
<th width='200'>Name</th>
<th width='200'>Level</th>
<th width='200'>Role</th>
<th width='200'>Job title</th>
<th width='50'>Active</th>
<th width='50'>Master</th>
<th width='50'>Famous</th>
<?php
 /*foreach ($privs as $id => $name)
 {
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }*/
?>
<th><input type="button" value="Create" onclick="createMaster()"/></th>
</tr>
<?php
 $fields = 'id,client_id,level_id,role_id,job_title,serial,can_connect,for_service,sel_by_name';
 $masters = PageAdm::db()->queryMatrix('com_master', $fields, 'centre_id=' . $centreId, 'serial,id');
 if ($masters)
 {
  foreach ($masters as $id => $master)
  {
   $serial = $master['serial'];
   $clientId = $master['client_id'];
   $clientName = htmlspecialchars(WClient::getClientName($clientId));
   $levelId = $master['level_id'];
   $levelName = $levelId ? htmlspecialchars(PageAdm::db()->queryField('com_level', 'name', 'id=' . $levelId)) : null;
   $roleId = $master['role_id'];
   $roleName = $roleId ? htmlspecialchars(PageAdm::db()->queryField('art_master_role', 'name', 'id=' . $roleId)) : null;
   $jobTitle = htmlspecialchars($master['job_title']);
   $can_connect = !!$master['can_connect'];
   $for_service = !!$master['for_service'];
   $sel_by_name = !!$master['sel_by_name'];

   echo "<tr>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<td class='right' onclick='changeSerial(this,$id)'>$serial</td>\n";
   echo "<td class='right' onclick='changeClient(this,$id)'>$clientId</td>\n";
   echo "<th class='left'><a href='clt-$clientId/'>$clientName</a></th>\n";
   echo "<td class='left' onclick='changeLevel(this,$id)'>$levelName</td>\n";
   if (($clientId == WCentre::memberId()) && ($roleId == WPriv::ROLE_OWNER))
    echo "<td class='left''>$roleName</td>\n";
   else
    echo "<td class='left' onclick='changeRole(this,$id)'>$roleName</td>\n";
   echo "<td class='left' onclick='changeJob(this,$id)'>$jobTitle</td>\n";
   echo "<td" . ($can_connect ? " class='checked'" : null) . " onclick='changeFlag(this,$id,\"A\")'></td>\n";
   echo "<td" . ($for_service ? " class='checked'" : null) . " onclick='changeFlag(this,$id,\"M\")'></td>\n";
   echo "<td" . ($sel_by_name ? " class='checked'" : null) . " onclick='changeFlag(this,$id,\"F\")'></td>\n";
   echo "<th><input type='button' value='Delete' onclick='deleteMaster($id)'/></th>\n";
   echo "</tr>\n";

   echo "<tr><td colspan='11'><table class='internal small'><colgroup>\n";
   for ($i = 0; $i < count($privs); $i++)
    echo "<col width='100'>";
   echo "\n</colgroup><tr>\n";
   foreach ($prgrs as $prgr)
    echo "<th colspan='" . count($prgr['privs']) . "'>" . htmlspecialchars($prgr['name']) . "</th>\n";
   echo "</tr><tr class='small'>\n";
   foreach ($prgrs as $prgr)
    foreach ($prgr['privs'] as $priv)
     echo "<th>" . htmlspecialchars($priv) . "</th>\n";
   echo "</tr><tr>\n";
   $masterListPriv = WPriv::getMasterListPriv($id);
   foreach ($privs as $priv => $privName)
   {
    $checked = array_key_exists($priv, $masterListPriv) ? " class='checked'" : '';
    $onclick = $roleId ? null : " onclick='changePriv(this,$id,\"$clientName\",$priv,\"$privName\")'";
    echo "<td$checked$onclick>&nbsp;</td>\n";
   }
   echo "</tr></table></td></tr>\n";
  }
 }
?></table>
</body>
</html>
<?php
  return true;
 }
}

?>
