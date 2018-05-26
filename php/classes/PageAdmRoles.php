<?php

/**
 * Description of PageAdmRoles
 */
class PageAdmRoles
{
 const TABLE_PRGR = 'art_master_prgr';
 const TABLE_PRIV = 'art_master_priv';
 const TABLE_ROLE = 'art_master_role';
 const TABLE_ROPR = 'art_master_role_priv';

 private static function processAct($act)
 {
  $entityPrGr = 'privilege group';
  $entityPriv = 'privilege';
  $entityRole = 'role';
  $entityRoPr = 'role privilege';
  switch ($act)
  {
  case 'changePrGrSerial' :
   PageAdm::changeSerial(self::TABLE_PRGR, $entityPrGr);
   break;

  case 'changePrGrTitle' :
   PageAdm::changeTitle(self::TABLE_PRGR, 'prgr_id', $entityPrGr);
   break;

  case 'changePrivSerial' :
   PageAdm::changeSerial(self::TABLE_PRIV, $entityPriv);
   break;

  case 'changePrivTitle' :
   PageAdm::changeTitle(self::TABLE_PRIV, 'priv_id', $entityPriv);
   break;

  case 'createRoleEntity' :
   PageAdm::createEntity(self::TABLE_ROLE, $entityRole);
   break;

  case 'deleteRoleEntity' :
   $id = intval(HTTP::param('id'));

   if ($id == 1)
    echo ucfirst($entityRole) . " $id can't be deleted";
   else if (intval(PageAdm::db()->queryField('com_master', 'count(*)', "role_id=$id")))
    echo ucfirst($entityRole) . " $id has some masters linked";
   else
    PageAdm::deleteEntity(self::TABLE_ROLE, $entityRole, $id);
   break;

  case 'changeRoleSerial' :
   PageAdm::changeSerial(self::TABLE_ROLE, $entityRole);
   break;

  case 'changeRoleName' :
   PageAdm::changeName(self::TABLE_ROLE, $entityRole);
   break;

  case 'changeRoleTitle' :
   PageAdm::changeTitle(self::TABLE_ROLE, 'role_id', $entityRole);
   break;

  case 'grantRolePriv' :
   $role = intval(HTTP::param('role'));
   $priv = intval(HTTP::param('priv'));
   if (PageAdm::db()->insertValues(self::TABLE_ROPR, array('role_id' => $role, 'priv_id' => $priv)))
    echo 'OK';
   else
    echo "Error granting the $entityPriv $priv to the $entityRole $role: " . DB::lastQuery();
   break;

  case 'revokeRolePriv' :
   $role = intval(HTTP::param('role'));
   $priv = intval(HTTP::param('priv'));
   if (PageAdm::db()->deleteRecords(self::TABLE_ROPR, "role_id=$role and priv_id=$priv"))
    echo 'OK';
   else
    echo "Error revoking the $entityPriv $priv from the $entityRole $role: " . DB::lastQuery();
   break;

  default :
   echo "Unsupported action: '$act'";
  }

  return true;
 }

 public static function showPage()
 {
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
  $langs = Lang::map();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
</style>
<script>
var entityPrGr='privilege group';
var entityPriv='privilege';
var entityRole='role';
var entityRoPr='role privilege';
function changePrGrSerial(node,id)
{
 A.changeSerial(node,id,entityPrGr,'changePrGrSerial');
}
function changePrGrTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entityPrGr,'changePrGrTitle');
}
function changePrivSerial(node,id)
{
 A.changeSerial(node,id,entityPriv,'changePrivSerial');
}
function changePrivTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entityPriv,'changePrivTitle');
}
function createRoleEntity()
{
 A.createEntity(entityRole,'createRoleEntity');
}
function deleteRoleEntity(id)
{
 A.deleteEntity(id,entityRole,'deleteRoleEntity');
}
function changeRoleSerial(node,id)
{
 A.changeSerial(node,id,entityRole,'changeRoleSerial');
}
function changeRoleName(node,id)
{
 A.changeName(node,id,entityRole,'changeRoleName');
 document.location.reload(true);
}
function changeRoleTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entityRole,'changeRoleTitle');
}
function changeRolePriv(node,priv,privName,role,roleName)
{
 var oldValue=(node.className=='checked');
 var P='the '+entityPriv+' '+priv+' ("'+privName+'")';
 var R='the '+entityRole+' '+role+' ("'+roleName+'")';
 var text=oldValue?('Revoke '+P+' from '+R):('Grant '+P+' to '+R);
 if(!confirm(text+'?'))
  return;
 var act=oldValue?'revokeRolePriv':'grantRolePriv';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+'&role='+role+'&priv='+priv),false);
 req.send(null);
 var error='Error changing '+P+' for '+R+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.className=oldValue?'':'checked';
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main" cellspacing="0" cellpadding="0">
<caption>Privilege groups</caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='200'>Name</th>
<?php
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
</tr>
<?php
 $fields = 'id,serial,name';
 foreach ($langs as $lang => $title)
  $fields .= ',(select title from ' . self::TABLE_PRGR . '_abc where prgr_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $prgrs = PageAdm::db()->queryRecords(self::TABLE_PRGR . ' a', $fields, null, 'serial,id');
 if ($prgrs)
  foreach ($prgrs as $prgr)
  {
   $id = $prgr[0];
   $name = htmlspecialchars($prgr[2]);
   echo "<tr>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<td class='right' onclick='changePrGrSerial(this,$id)'>$prgr[1]</td>\n";
   echo "<th class='lang'>$name</th>\n";
   $i = 3;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($prgr[$i++]);
    echo "<td onclick='changePrGrTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   echo "</tr>\n";
  }
?></table>
<hr>
<table class="main" cellspacing="0" cellpadding="0">
<caption>Privileges</caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='200'>Name</th>
<?php
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
</tr>
<?php
 $fields = 'id,serial,name,prgr_id';
 $fields .= ',(select serial from ' . self::TABLE_PRGR . ' where id=a.prgr_id)prgr_serial';
 foreach ($langs as $lang => $title)
  $fields .= ',(select title from ' . self::TABLE_PRIV . '_abc where priv_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $privs = PageAdm::db()->queryRecords(self::TABLE_PRIV . ' a', $fields, null, '5,4,2,1');
 if ($privs)
 {
  $prgr_id = null;
  $colspan = 3 + count($langs);
  foreach ($privs as $priv)
  {
   if ($priv[3] != $prgr_id)
   {
    $prgr_id = $priv[3];
    $prgr_name = null;
    foreach ($prgrs as $prgr)
     if ($prgr[0] == $prgr_id)
      $prgr_name = htmlspecialchars($prgr[2]);
    echo "<tr><th class='lang' colspan='$colspan'>$prgr_name</th></tr>\n";
   }
   $id = $priv[0];
   $name = htmlspecialchars($priv[2]);
   echo "<tr>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<td class='right' onclick='changePrivSerial(this,$id)'>$priv[1]</td>\n";
   echo "<th class='lang'>$name</th>\n";
   $i = 5;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($priv[$i++]);
    echo "<td onclick='changePrivTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   echo "</tr>\n";
  }
 }
?></table>
<hr>
<table class="main" cellspacing="0" cellpadding="0">
<caption>Roles</caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='200'>Name</th>
<?php
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
<th width='1'><input type="button" value="Create new role" onclick="createRoleEntity()"/></th>
</tr>
<?php
 $skip_owner = 0;
 $fields = 'id,serial,name';
 foreach ($langs as $lang => $title)
  $fields .= ',(select title from ' . self::TABLE_ROLE . '_abc where role_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $roles = PageAdm::db()->queryRecords(self::TABLE_ROLE . ' a', $fields, null, 'serial,id');
 if ($roles)
  foreach ($roles as $role)
  {
   $id = $role[0];
   $name = htmlspecialchars($role[2]);
   echo "<tr>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<td class='right' onclick='changeRoleSerial(this,$id)'>$role[1]</td>\n";
   echo "<td class='left' onclick='changeRoleName(this,$id)'>$name</td>\n";
   $i = 3;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($role[$i++]);
    echo "<td onclick='changeRoleTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   if ($id == 1)
    echo "<th></th>\n";
   else
    echo "<th><input type='button' value='Delete role $id' onclick='deleteRoleEntity($id)'/></th>\n";
   echo "</tr>\n";
   if ($id == 1)
    $skip_owner = 1;
  }
?></table>
<hr>
<table class="main" cellspacing="0" cellpadding="0">
<caption>Role privileges</caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='200'>Privilege</th>
<?php
 foreach ($roles as $role)
  if ($role[0] > 1)
   echo "<th width='100' class='lang'>" . htmlspecialchars($role[2]) . "</th>\n";
?>
</tr>
<?php
 if ($privs && $roles)
 {
  $fields = 'priv_id,role_id';
  $records = PageAdm::db()->queryRecords(self::TABLE_ROPR, $fields, null, $fields);
  $prgr_id = null;
  $colspan = 3 + count($roles) - $skip_owner;
  foreach ($privs as $priv)
  {
   if ($priv[3] != $prgr_id)
   {
    $prgr_id = $priv[3];
    $prgr_name = null;
    foreach ($prgrs as $prgr)
     if ($prgr[0] == $prgr_id)
      $prgr_name = htmlspecialchars($prgr[2]);
    echo "<tr><th class='lang' colspan='$colspan'>$prgr_name</th></tr>\n";
   }
   $id = $priv[0];
   $name = htmlspecialchars($priv[2]);
   echo "<tr>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<th class='right'>$priv[1]</th>\n";
   echo "<th class='lang'>$name</th>\n";
   $i = 4;
   foreach ($roles as $role)
   {
    if ($role[0] == 1)
     continue;
    $checked = null;
    if ($records)
     foreach ($records as $record)
     {
      if (($record[0] == $priv[0]) && ($record[1] == $role[0]))
      {
       $checked = " class='checked'";
       break;
      }
     }
    echo "<td$checked onclick='changeRolePriv(this,$id,\"$name\",$role[0],\"$role[2]\")'></td>\n";
   }
   echo "</tr>\n";
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
