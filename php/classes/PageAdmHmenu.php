<?php

class PageAdmHmenu
{
 private static function processAct($act)
 {
  $table = 'biz_hmenu';
  $entity = 'menu item';
  switch ($act)
  {
  case 'createEntity' :
   $parentId = HTTP::get('parent_id');
   $values = $parentId ? array('parent_id' => $parentId) : null;
   PageAdm::createEntity($table, $entity, null, $values);
   break;

  case 'deleteEntity' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField($table, 'count(*)', "parent_id=$id")) > 0)
    echo "Item $id has some subitems";
   else
    PageAdm::deleteEntity($table, $entity, $id);
   break;

  case 'changeSerial' :
   PageAdm::changeSerial($table, $entity);
   break;

  case 'changeName' :
   PageAdm::changeName($table, $entity);
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'hmenu_id', $entity);
   break;

  case 'changeAddr' :
   PageAdm::changeField($table, $entity, 'addr');
   break;

  case 'changeFlagLocal' :
   $_REQUEST['field'] = 'domain_id';
   $_REQUEST['value'] = $_REQUEST['value'] ? WDomain::id() : null;
   PageAdm::changeField($table, $entity);
   break;

  case 'hideEntity' :
   PageAdm::hideEntity($table, $entity);
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
var entity='menu item';
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();
 $langs = Lang::map();

 echo '<table class="main" cellspacing="0" cellpadding="0">' . "\n";
 echo '<caption>' . PageAdm::title() . '</caption>' . "\n";

 echo '<tr>' . "\n";
 echo '<th width="50">Id</th>' . "\n";
 echo '<th width="50">Nr</th>' . "\n";
 echo '<th width="100: colspan="2">Submenu</th>' . "\n";
 echo '<th width="200">Name</th>' . "\n";
 echo '<th width="200">Address</th>' . "\n";
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
 if (WDomain::ok())
  echo "<th width='50'>Local</th>\n";
 echo "<th width='1' colspan='2'><input type='button' value='Create item' onclick='A.createEntity(entity)'/></th>\n";
 echo "</tr>\n";

 $table = 'biz_hmenu';
 $fields = 'id,name,addr,domain_id,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select title from biz_hmenu_abc where hmenu_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $dwhere = ' and ' . WSGrp::where(true);
 $topItems = PageAdm::db()->queryArrays($table . ' a', $fields, 'parent_id is null' . $dwhere, 'serial,id');
 if ($topItems)
 {
  foreach ($topItems as $topItem)
  {
   $id = $topItem['id'];
   $items = DB::getDB()->queryArrays($table . ' a', $fields, 'parent_id=' . $id . $dwhere, 'serial,id');
   $itemCount = $items ? count($items) : 0;
   self::showItem($topItem, $langs, $itemCount, true);
   if ($items)
   {
    foreach ($items as $item)
     self::showItem($item, $langs, 0, false);
   }
  }
 }
 echo '</table>' . "\n";
?>
</body>
</html>
<?php
  return true;
 }

 private static function showItem($item, $langs, $itemCount, $top)
 {
  $id = $item['id'];
  $name = htmlspecialchars($item['name']);
  $addr = htmlspecialchars($item['addr']);
  $local = $item['domain_id'] != '';
  $serial = htmlspecialchars($item['serial']);
  $hidden = $item['hidden'] != '';
  $class = $hidden ? (" class='hidden'") : '';
  $rowspan = $itemCount ? (' rowspan="' . ($itemCount + 1) . '"') : '';
  echo "<tr id='row-$id'$class>\n";
  echo "<th$rowspan class='right'>$id</th>\n";
  echo "<td$rowspan class='right' onclick='A.changeSerial(this,$id,entity)'>$serial</td>\n";
  if ($top)
   echo "<td colspan=2><input type='button' value='Create subitem' onclick='A.createEntity(entity,\"\",\"parent_id=$id\")' /></td>\n";
  echo "<td class='left' onclick='A.changeName(this,$id,entity)'>$name</td>\n";
  echo "<td class='left' onclick='A.changeField(this,$id,entity,\"addr\",\"changeAddr\")'>$addr</td>\n";
  if (Lang::used())
  {
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($item['title_' . $lang]);
    echo "<td class='left' onclick='A.changeTitle(this,$id,\"$lang\",entity)'>$value</td>\n";
   }
  }
  if (WDomain::ok())
  {
   $checked = $local ? " class='checked'" : '';
   echo "<td$checked onclick='A.changeFlag(this,$id,entity,\"local\",\"changeFlagLocal\")'></td>\n";
  }
  $value = $hidden ? 'Show' : 'Hide';
  $arg = $hidden ? 'false' : 'true';
  echo "<th><input type='button' value='$value' onclick='A.hideEntity(this,$id,$arg,entity)'/></th>\n";
  echo "<th><input type='button' value='Delete' onclick='A.deleteEntity($id,entity)'/></th>\n";
  echo "</tr>\n";
 }
}

?>