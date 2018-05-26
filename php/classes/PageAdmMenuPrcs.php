<?php

class PageAdmMenuPrcs
{
 private static function processAct($cat_id, $act)
 {
  $table = 'biz_menu_prc';
  $entity = 'procedure';
  switch ($act)
  {
  case 'createEntity' :
   PageAdm::createEntity($table, $entity, null, array('cat_id' => $cat_id), array());
   break;

  case 'deleteEntity' :
   $id = intval(HTTP::param('id'));

   //if (intval(PageAdm::db()->queryField(WService::TABLE_SRV_PRC, 'count(*)', "prc_id=$id")) > 0)
   // echo ucfirst ($entity) . " $id has some services linked";
   //else
     PageAdm::deleteEntity($table, $entity);
   break;

  case 'changeSerial' :
   PageAdm::changeSerial($table, $entity);
   break;

  case 'changeName' :
   PageAdm::changeName($table, $entity);
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'prc_id', $entity);
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
  $cat_id = HTTP::get('cat');
  $isHoster = WClient::me()->isHoster();
  if (array_key_exists('act', $_REQUEST))
  {
   $act = $_REQUEST['act'];
   if (!$isHoster)
   {
    if ($act != 'changeTitle')
     return true;
    $lang = HTTP::param('lang');
    if (!PageAdm::checkWorkerLang($lang))
     return true;
   }
   if (self::processAct($cat_id, $act))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<script>
var entity='procedure';
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();

 $links = PageAdm::db()->queryRecords('biz_menu_cat', "id,name", null, 'serial,id');
 if ($links)
 {
  for ($i = 0; $i < count($links); ++$i)
  {
   $id = $links[$i][0];
   $links[$i][0] = 'mprcs/?cat=' . $id;
   $links[$i][1] = Lang::getDBValueDef('biz_menu_cat_abc', 'title', "cat_id=$id", $links[$i][1]);
   $links[$i][2] = ($id == $cat_id);
  }
  PageAdm::echoMenuTableOfLinks('Category', $links);
 }

 if ($cat_id)
 {
  echo "<table class='main' cellspacing='0' cellpadding='0'>\n";
  echo "<caption>" . PageAdm::title() . "</caption>\n";

  echo "<tr>\n";
  echo "<th width='50'>Id</th>\n";
  echo "<th width='50'>Nr</th>\n";
  echo "<th width='200'>Name</th>\n";
  $langs = Lang::map();
  $checks = array();
  foreach ($langs as $lang => $Lang)
  {
   $title = htmlspecialchars($Lang->title());
   echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
   $checks[$lang] = PageAdm::checkWorkerLang($lang);
  }
  if ($isHoster)
   echo "<th width='1' colspan='2'><input type='button' value='Create new' onclick='A.createEntity(entity)'/></th>\n";
  echo "</tr>\n";

  $fields = 'id,name,serial,hidden';
  foreach ($langs as $lang => $Lang)
   $fields .= ',(select title from biz_menu_prc_abc where prc_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
  $records = PageAdm::db()->queryArrays('biz_menu_prc a', $fields, "cat_id=$cat_id", 'serial,id');
  if ($records)
   foreach ($records as $record)
   {
    $id = $record['id'];
    $name = $record['name'];
    $serial = htmlspecialchars($record['serial']);
    $hidden = $record['hidden'] != '';
    $class = $hidden ? (" class='hidden'") : '';
    echo "<tr id='row-$id'$class>\n";
    echo "<th class='right'>$id</th>\n";
    echo "<td class='right'" . ($isHoster ? " onclick='A.changeSerial(this,$id,entity)'" : '') . ">$serial</td>\n";
    echo "<td" . ($isHoster ? " onclick='A.changeName(this,$id,entity)'" : '') . ">$name</td>\n";
    $i = 4;
    foreach ($langs as $lang => $title)
    {
     $value = htmlspecialchars($record['title_' . $lang]);
     echo "<td" . (($isHoster || $checks[$lang]) ? " onclick='A.changeTitle(this,$id,\"$lang\",entity)'" : '') . ">$value</td>\n";
    }
    if ($isHoster)
    {
     $value = $hidden ? 'Show' : 'Hide';
     $arg = $hidden ? 'false' : 'true';
     echo "<th><input type='button' value='$value' onclick='A.hideEntity(this,$id,$arg,entity)'/></th>\n";
     echo "<th><input type='button' value='Delete' onclick='A.deleteEntity($id,entity,\"\",\"row-\"+$id)'/></th>\n";
    }
    echo "</tr>\n";
   }
  echo "</table>\n";
 }
?>
</body>
</html>
<?php
  return true;
 }

}

?>