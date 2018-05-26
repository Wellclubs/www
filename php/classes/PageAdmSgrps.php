<?php

class PageAdmSgrps
{
 private static function processAct($act)
 {
  $tableGrp = WSGrp::TABLE_GROUP;
  $entityGrp = 'social group';
  $tableFlt = WSGrp::TABLE_FILTER;
  $entityFlt = 'social filter';
  $tableFltGrp = WSGrp::TABLE_FILTER_GRP;
  switch ($act)
  {
  case 'createGrpEntity' :
   PageAdm::createEntity($tableGrp, $entityGrp);
   break;

  case 'deleteGrpEntity' :
   PageAdm::deleteEntity($tableGrp, $entityGrp);
   break;

  case 'changeGrpSerial' :
   PageAdm::changeSerial($tableGrp, $entityGrp);
   break;

  case 'changeGrpName' :
   PageAdm::changeName($tableGrp, $entityGrp);
   break;

  case 'changeGrpTitle' :
   PageAdm::changeTitle($tableGrp, 'group_id', $entityGrp);
   break;

  case 'changeGrpFlagPopular' :
   PageAdm::changeFlag($tableGrp, $entityGrp);
   break;

  case 'changeGrpFlagLocal' :
   $_REQUEST['field'] = 'domain_id';
   $_REQUEST['value'] = $_REQUEST['value'] ? WDomain::id() : null;
   PageAdm::changeField($tableGrp, $entityGrp);
   break;

  case 'hideGrpEntity' :
   PageAdm::hideEntity($tableGrp, $entityGrp);
   break;

  case 'createFltEntity' :
   PageAdm::createEntity($tableFlt, $entityFlt);
   break;

  case 'deleteFltEntity' :
   PageAdm::deleteEntity($tableFlt, $entityFlt);
   break;

  case 'changeFltSerial' :
   PageAdm::changeSerial($tableFlt, $entityFlt);
   break;

  case 'changeFltName' :
   PageAdm::changeName($tableFlt, $entityFlt);
   break;

  case 'changeFltTitle' :
   PageAdm::changeTitle($tableFlt, 'filter_id', $entityFlt);
   break;

  case 'changeFltFlagLocal' :
   $_REQUEST['field'] = 'domain_id';
   $_REQUEST['value'] = $_REQUEST['value'] ? WDomain::id() : null;
   PageAdm::changeField($tableFlt, $entityFlt);
   break;

  case 'hideFltEntity' :
   PageAdm::hideEntity($tableFlt, $entityFlt);
   break;

  case 'changeFltInclude' :
   $id = HTTP::get('id');
   $grp = HTTP::get('grp');
   $value = HTTP::get('value');
   $where = array('filter_id' => $id, 'group_id' => $grp);
   if (($value == 'Y') || ($value == 'N'))
    PageAdm::db()->mergeFields($tableFltGrp, array('include' => DB::str($value)), $where);
   else
    PageAdm::db()->deleteRecords($tableFltGrp, $where);
   echo 'OK';
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
    exit;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
table.main th.title { text-align:left;padding:0 5px; }
table.main th a { display:block; }
</style>
<script>
var entityGrp='social group';
var entityFlt='social filter';
function changeFltInclude(node,id,name,grp,fld)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Enter new "'+fld+'" value for filter '+id+' "'+name+'" ("Y", "N" or empty):',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 newValue=newValue.toUpperCase();
 if((newValue!='')&&(newValue!='Y')&&(newValue!='N'))
 {
  alert('Invalid value: "'+newValue+'"');
  return;
 }
 var params='act=changeFltInclude&id='+id+'&grp='+grp+'&value='+newValue;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI(params),false);
 req.send(null);
 var error='Error changing the "'+fld+'" value on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();

 $langs = Lang::map();
 $where = WSGrp::where(true); // Common for 2 tables

 // Social groups
 echo '<table class="main" cellspacing="0" cellpadding="0">' . "\n";
 echo '<caption>' . PageAdm::title() . '</caption>' . "\n";

 echo '<tr>' . "\n";
 echo '<th width="50">Id</th>' . "\n";
 echo '<th width="50">Nr</th>' . "\n";
 echo '<th width="200">Name</th>' . "\n";
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
 if (WDomain::ok())
  echo "<th width='50'>Local</th>\n";
 echo "<th width='50'>Popular</th>\n";
 echo "<th width='1' colspan='2'><input type='button' value='Create item' onclick='A.createEntity(entityGrp,\"createGrpEntity\")'/></th>\n";
 echo "</tr>\n";

 $tableGrp = WSGrp::TABLE_GROUP;
 $fieldsGrp = 'id,name,popular,domain_id,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fieldsGrp .= ',(select title from ' . WSGrp::TABLE_GROUP_ABC . ' where group_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $itemsGrp = PageAdm::db()->queryArrays($tableGrp . ' a', $fieldsGrp, $where, 'serial,id');
 if (!$itemsGrp)
  $itemsGrp = array();
 foreach ($itemsGrp as $itemGrp)
 {
  $id = $itemGrp['id'];
  $name = htmlspecialchars($itemGrp['name']);
  $local = $itemGrp['domain_id'] != '';
  $popular = $itemGrp['popular'] != '';
  $serial = htmlspecialchars($itemGrp['serial']);
  $hidden = $itemGrp['hidden'] != '';
  $class = $hidden ? (" class='hidden'") : '';
  echo "<tr id='grow-$id'$class>\n";
  echo "<th class='right'>$id</th>\n";
  echo "<td class='right' onclick='A.changeSerial(this,$id,entityGrp,\"changeGrpSerial\")'>$serial</td>\n";
  echo "<td class='left' onclick='A.changeName(this,$id,entityGrp,\"changeGrpName\")'>$name</td>\n";
  foreach ($langs as $lang => $title)
  {
   $value = htmlspecialchars($itemGrp['title_' . $lang]);
   echo "<td class='left' onclick='A.changeTitle(this,$id,\"$lang\",entityGrp,\"changeGrpTitle\")'>$value</td>\n";
  }
  if (WDomain::ok())
  {
   $checked = $local ? " class='checked'" : '';
   echo "<td$checked onclick='A.changeFlag(this,$id,entityGrp,\"local\",\"changeGrpFlagLocal\")'></td>\n";
  }
  $checked = $popular ? " class='checked'" : '';
  echo "<td$checked onclick='A.changeFlag(this,$id,entityGrp,\"popular\",\"changeGrpFlagPopular\")'></td>\n";
  $value = $hidden ? 'Show' : 'Hide';
  $arg = $hidden ? 'false' : 'true';
  echo "<th><input type='button' value='$value' onclick='A.hideEntity(this,$id,$arg,entityGrp,\"hideGrpEntity\")'/></th>\n";
  echo "<th><input type='button' value='Delete' onclick='A.deleteEntity($id,entityGrp,\"deleteGrpEntity\")'/></th>\n";
  echo "</tr>\n";
 }
 echo '</table>' . "\n";

 // Social filters
 echo '<table class="main" cellspacing="0" cellpadding="0">' . "\n";
 echo '<caption>Social filters</caption>' . "\n";

 echo '<tr>' . "\n";
 echo '<th width="50">Id</th>' . "\n";
 echo '<th width="50">Nr</th>' . "\n";
 echo '<th width="200">Name</th>' . "\n";
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
 if (WDomain::ok())
  echo "<th width='50'>Local</th>\n";
 foreach ($itemsGrp as $itemGrp)
  echo '<th width="100">' . $itemGrp['name'] . "</th>\n";
 echo "<th width='1' colspan='2'><input type='button' value='Create item' onclick='A.createEntity(entityFlt,\"createFltEntity\")'/></th>\n";
 echo "</tr>\n";

 $tableFlt = WSGrp::TABLE_FILTER;
 $fieldsFlt = 'id,name,domain_id,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fieldsFlt .= ',(select title from ' . WSGrp::TABLE_FILTER_ABC . ' where filter_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 foreach ($itemsGrp as $itemGrp)
  $fieldsFlt .= ',(select include from ' . WSGrp::TABLE_FILTER_GRP . ' where filter_id=a.id and group_id=' . $itemGrp['id'] . ')include_' . $itemGrp['id'];
 $itemsFlt = PageAdm::db()->queryArrays($tableFlt . ' a', $fieldsFlt, $where, 'serial,id');
 if (!$itemsFlt)
  $itemsFlt = array();
 foreach ($itemsFlt as $itemFlt)
 {
  $id = $itemFlt['id'];
  $name = htmlspecialchars($itemFlt['name']);
  $local = $itemFlt['domain_id'] != '';
  $serial = htmlspecialchars($itemFlt['serial']);
  $hidden = $itemFlt['hidden'] != '';
  $class = $hidden ? (" class='hidden'") : '';
  echo "<tr id='frow-$id'$class>\n";
  echo "<th class='right'>$id</th>\n";
  echo "<td class='right' onclick='A.changeSerial(this,$id,entityFlt,\"changeFltSerial\")'>$serial</td>\n";
  echo "<td class='left' onclick='A.changeName(this,$id,entityFlt,\"changeFltName\")'>$name</td>\n";
  foreach ($langs as $lang => $title)
  {
   $value = htmlspecialchars($itemFlt['title_' . $lang]);
   echo "<td class='left' onclick='A.changeTitle(this,$id,\"$lang\",entityFlt,\"changeFltTitle\")'>$value</td>\n";
  }
  if (WDomain::ok())
  {
   $checked = $local ? " class='checked'" : '';
   echo "<td$checked onclick='A.changeFlag(this,$id,entityFlt,\"local\",\"changeFltFlagLocal\")'></td>\n";
  }
  foreach ($itemsGrp as $itemGrp)
  {
   $grp = $itemGrp['id'];
   $fld = $itemGrp['name'];
   $include = $itemFlt['include_' . $grp];
   echo "<td class='center' onclick='changeFltInclude(this,$id,\"$name\",$grp,\"$fld\")'>$include</td>\n";
  }
  $value = $hidden ? 'Show' : 'Hide';
  $arg = $hidden ? 'false' : 'true';
  echo "<th><input type='button' value='$value' onclick='A.hideEntity(this,$id,$arg,entityFlt,\"hideFltEntity\")'/></th>\n";
  echo "<th><input type='button' value='Delete' onclick='A.deleteEntity($id,entityFlt,\"deleteFltEntity\")'/></th>\n";
  echo "</tr>\n";
 }
 echo '</table>' . "\n";
?>
</body>
</html>
<?php
  return true;
 }
}

?>