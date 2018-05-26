<?php

/**
 * Description of PageAdmSrv
 */
class PageAdmSrv
{
 private static function processAct($srvId, $act)
 {
  $table = WService::TABLE_SRV;
  $entity = 'service';
  switch ($act)
  {

  case 'changeKeywords' :
   $_REQUEST['id'] = $srvId;
   PageAdm::changeField($table, $entity, 'keywords');
   break;

  case 'changeSgrp' :
   $field = HTTP::param('field');
   $param = HTTP::param('value', '');
   $value = ($param == '1') ? '1' : '0';
   $where = array('srv_id' => $srvId, 'sgrp_id' => $field);
   $def = PageAdm::db()->queryField('com_centre_sgrp', 'active', array('centre_id' => $srvId, 'sgrp_id' => $field));
   if ($def == $value)
    $del = true;
   else
   {
    $def = PageAdm::db()->queryField(WSGrp::TABLE_GROUP, 'popular', array('id' => $field));
    $del = ($def == $param);
   }
   if ($del)
   {
    PageAdm::db()->deleteRecords('com_menu_srv_sgrp', $where);
    $ok = (PageAdm::db()->queryField('com_menu_srv_sgrp', 'count(*)', $where) == '0');
   }
   else
   {
    PageAdm::db()->mergeField('com_menu_srv_sgrp', 'active', $value, $where);
    $ok = (PageAdm::db()->queryField('com_menu_srv_sgrp', 'active', $where) == $value);
   }
   if ($ok)
    echo 'OK';
   else
    echo "Error changing the $entity $srvId record in the database";
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'srv_id', $entity, $srvId);
   break;

  case 'uploadImage' :
   $serial = HTTP::param('nr');
   if (!WService::createGallery()->uploadImage($serial))
    return Base::addError('Error uploading an image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'changeImageTitle' :
   $serial = HTTP::param('id');
   $lang = HTTP::param('lang');
   $title = HTTP::param('title');
   if (Lang::setDBValue($title, $table . '_img_abc', null, array('srv_id' => $srvId, 'serial' => $serial), $lang))
    echo 'OK';
   else
    echo "Error changing $entity $id language '$lang' title to '$title': " . DB::lastQuery();
   break;

  case 'deleteImage' :
   $serial = HTTP::get('nr');
   if (!WService::createGallery()->deleteImage($serial))
    echo 'Error deleting an image';
   else
    echo 'OK';
   break;

  case 'changeDescr' :
   $lang = HTTP::param('lang');
   $table = WService::TABLE_SRV . '_abc';
   $where = array('srv_id' => $srvId, 'abc_id' => DB::str($lang));
   PageAdm::changeText($table, $where, 'service description', 'descr');
   break;

  case 'changeRestr' :
   $lang = HTTP::param('lang');
   $table = WService::TABLE_SRV . '_abc';
   $where = array('srv_id' => $srvId, 'abc_id' => DB::str($lang));
   PageAdm::changeText($table, $where, 'service restrictions', 'restr');
   break;

  case 'changeNotes' :
   $lang = HTTP::param('lang');
   $table = WService::TABLE_SRV . '_abc';
   $where = array('srv_id' => $srvId, 'abc_id' => DB::str($lang));
   PageAdm::changeText($table, $where, 'service notes', 'notes');
   break;

  default :
   echo "Unsupported action: '$act'";
  }

  return true;
 }

 public static function showPage()
 {
  if (!WService::initCurrent(Base::index()))
   return false;
  $srvId = WService::id();
  if (!WCentre::initCurrent(WService::centreId(), true))
   return false;
  //$centreId = WCentre::id();
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($srvId, $_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
table.main th.sgrp { background:#ccc; }
.block { display:block;float:left;margin:0 10px 10px 0; }
.block th { padding:1px 4px; }
.block td { padding:1px 4px; }
</style>
<script>
var entity='service';
function changeKeywords(node)
{
 A.changeField(node,null,entity,'keywords','changeKeywords');
}
function changeSgrp(node,id,title)
{
 A.changeFlag(node,'',entity,id,'changeSgrp',title);
}
function changeTitle(node,lang)
{
 A.changeTitle(node,null,lang,entity);
}
function changeImageTitle(node,nr,lang)
{
 A.changeTitle(node,nr,lang,'image','changeImageTitle');
}
function deleteImage(nr)
{
 if(!confirm('Delete image '+nr+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=deleteImage&nr='+nr,false);
 req.send(null);
 var error='Error deleting an image '+nr+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<table class="main block">
<caption>General</caption>
<tr><th class="right">Owner:</th><th class="left"><?php
$memberId = WCentre::memberId();
$memberName = WClient::getClientName($memberId);
echo '<a href="mbr-' . $memberId . '/">' . PageAdm::makeEntityText($memberId, $memberName) . '</a>';
?></th></tr>
<tr><th class="right">Brand:</th><th class="left"><?php
$brandId = WCentre::brandId();
$brandName = $brandId ? WBrand::title() : '';
if ($brandId)
 echo '<a href="bnd-' . $brandId . '/">' . PageAdm::makeEntityText($brandId, $brandName) . '</a>';
?></th></tr>
<tr><th class="right">Centre:</th><th class="left"><?php
$centreId = WCentre::id();
$centreName = WCentre::title();
echo '<a href="ctr-' . $centreId . '/">' . PageAdm::makeEntityText($centreId, $centreName) . '</a>';
?></th></tr>
<tr><th class="right">Group:</th><th class="left"><?php
$groupId = WService::groupId();
$groupName = PageAdm::db()->queryField(WService::TABLE_GRP, 'name', 'id=' . $groupId);
echo PageAdm::makeEntityText($groupId, $groupName);
?></th></tr>
<tr><th class="right">Name:</th>
<td id='name' align="left" onclick="changeName(this)"><?php echo htmlspecialchars(WService::name());?></td></tr>
<tr><th class="right">Keywords:</th>
<td id='type' colspan="2" onclick="changeKeywords(this)"><?php echo htmlspecialchars(WService::keywords());?></td></tr>
<?php
 $sgrps = WSGrp::groups(true, false, $centreId, $srvId);
 if ($sgrps)
 {
  foreach ($sgrps as $sgrp)
  {
   $id = $sgrp['id'];
   $name = $sgrp['name'];
   $class = ($sgrp['active'] == '1') ? ' class="checked"' : '';
   echo "<tr><th class='left sgrp'>$name:</th>\n";
   echo "<td$class colspan='2' onclick='changeSgrp(this,$id,\"$name\")'></td></tr>";
  }
 }
 else
 {
  echo "<tr><td colspan='3'>" . DB::lastQuery() . "</td></tr>\n";
 }
?>
</table>
<table class="main block">
<caption>Procedure(s)</caption>
<tr><th>Id</th><th>Nr</th><th>Category</th><th>Procedure</th></tr>
<?php
$rows = PageAdm::db()->queryRecords(WService::TABLE_SRV_PRC, 'prc_id,serial', 'srv_id=' . $srvId, 'serial');
if ($rows)
{
 foreach ($rows as $row)
 {
  $prcId = $row[0];
  $serial = $row[1];
  $prc = PageAdm::db()->queryFields('biz_menu_prc', 'name,cat_id', 'id=' . $prcId);
  $prcName = $prc[0];
  $catId = $prc[1];
  $catName = PageAdm::db()->queryField('biz_menu_cat', 'name', 'id=' . $catId);
  echo "<tr>\n";
  echo "<th class='right'>$prcId</th>\n";
  echo "<th class='right'>$serial</th>\n";
  echo "<th>" . htmlspecialchars(PageAdm::makeEntityText($catId, $catName)) . "</th>\n";
  echo "<td>" . htmlspecialchars($prcName) . "</td>\n";
  echo "</tr>\n";
 }
}
?></table>
<table class="main block">
<caption>Titles</caption>
<tr><th>Language</th><th>Title</th></tr>
<?php
$langs = Lang::map();
foreach ($langs as $lang => $Lang)
{
 $field = PageAdm::db()->queryField(WService::TABLE_SRV . '_abc', 'title', 'srv_id=' . $srvId . ' and abc_id=' . DB::str($lang));
 echo "<tr>\n";
 echo "<th>$lang</th>\n";
 echo "<td onclick='changeTitle(this,\"$lang\")'>" . htmlspecialchars($field) . "</td>\n";
 echo "</tr>\n";
}
?></table>
<hr/>
<table class="main" width="100%">
<caption>Images</caption>
<tr>
<th width="50">Nr</th>
<th width="300">Picture</th>
<th width="200">Filename</th>
<th width="100">Size</th>
<?php
 foreach ($langs as $lang => $Lang)
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
?>
</tr>
<?php
$fields = 'image,filename,size,width,height';
foreach ($langs as $lang => $Lang)
 $fields .= ',(select title from com_menu_srv_img_abc where srv_id=a.srv_id and serial=a.serial and abc_id=\'' . $lang . '\')title_' . $lang;
for ($i = 1; $i <= 5; $i++)
{
 $where = "srv_id=$srvId and serial=$i";
 $record = PageAdm::db()->queryFields(WService::TABLE_SRV_IMG . ' a', $fields, $where, 'serial');
 $filename = $record ? $record[1] : 'No data';
 $img = ($record && $record[0]) ? "<img width='300' src='img/srv-$srvId/$i/$filename'/>" : '';
 $size = $record ? ($record[3] . 'x' . $record[4] . '<br>' . number_format($record[2])) : '';
 $form =
   "<form method='post' enctype='multipart/form-data'>" .
   "<input type='hidden' name='act' value='uploadImage' />" .
   "<input type='hidden' name='nr' value='$i' />" .
   "<input type='file' name='image' size='1' onchange='submit()' />" .
   "</form>";
 $delete = $record ? "<input type='button' value='Delete' onclick='deleteImage($i)'/>" : '';
 echo "<tr><th rowspan='2'>$i</th><th rowspan='2'>$img</th><th>$filename</th><th>$size</th>\n";
 $fieldIndex = 4;
 foreach ($langs as $lang => $Lang)
  if ($record)
   echo "<td rowspan='2' onclick='changeImageTitle(this,$i,\"$lang\")'>" . htmlspecialchars($record[++$fieldIndex]) . "</td>\n";
  else
   echo "<th rowspan='2'></th>\n";
 echo "</tr>\n";
 echo "<tr><th>$form</th><th>$delete</th></tr>\n";
}
?></table>
<hr/>
<table class="main" width="100%">
<caption>Description</caption>
<colgroup><col width="100"><col></colgroup>
<tr><th>Language</th><th>Text</th></tr>
<?php
foreach ($langs as $lang => $Lang)
{
 echo '<tr><th>' . $Lang->title() . '</th>';
 $descr = PageAdm::db()->queryField(WService::TABLE_SRV . '_abc', 'descr', 'srv_id=' . $srvId . ' and abc_id=' . DB::str($lang));
 echo "<td>\n";
 PageAdm::echoTextArea("descr-$lang", $descr, "service description ($lang)", 'changeDescr', 'descr', "lang=$lang");
 echo "</td></tr>\n";
}
?></table>
<hr/>
<table class="main" width="100%">
<caption>Restrictions</caption>
<colgroup><col width="100"><col></colgroup>
<tr><th>Language</th><th>Text</th></tr>
<?php
foreach ($langs as $lang => $Lang)
{
 echo '<tr><th>' . $Lang->title() . '</th>';
 $restr = PageAdm::db()->queryField(WService::TABLE_SRV . '_abc', 'restr', 'srv_id=' . $srvId . ' and abc_id=' . DB::str($lang));
 echo "<td>\n";
 PageAdm::echoTextArea("restr-$lang", $restr, "service restrictions ($lang)", 'changeRestr', 'restr', "lang=$lang");
 echo "</td></tr>\n";
}
?></table>
<hr/>
<table class="main" width="100%">
<caption>Notes</caption>
<colgroup><col width="100"><col></colgroup>
<tr><th>Language</th><th>Text</th></tr>
<?php
foreach ($langs as $lang => $Lang)
{
 echo '<tr><th>' . $Lang->title() . '</th>';
 $notes = PageAdm::db()->queryField(WService::TABLE_SRV . '_abc', 'notes', 'srv_id=' . $srvId . ' and abc_id=' . DB::str($lang));
 echo "<td>\n";
 PageAdm::echoTextArea("notes-$lang", $notes, "service notes ($lang)", 'changeNotes', 'notes', "lang=$lang");
 echo "</td></tr>\n";
}
?></table>
</body>
</html>
<?php
  return true;
 }
}
?>
