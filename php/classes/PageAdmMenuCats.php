<?php

class PageAdmMenuCats
{
 private static function processAct($act)
 {
  $table = 'biz_menu_cat';
  $entity = 'category';
  switch ($act)
  {
  case 'createEntity' :
   PageAdm::createEntity($table, $entity);
   break;

  case 'deleteEntity' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField('biz_menu_prc', 'count(*)', "cat_id=$id")) > 0)
    echo "Category $id has some procedures linked";
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
   PageAdm::changeTitle($table, 'cat_id', $entity);
   break;

  case 'uploadImage' :
   $id = intval(HTTP::param('id'));
   PageAdm::db()->uploadFile('image', $table, array('contents' => 'image'), "id=$id");
   return false;

  case 'clearImage' :
   $id = intval(HTTP::param('id'));
   $where = "id=$id";
   PageAdm::db()->modifyFields($table, array('image' => 'null'), $where);
   if (PageAdm::db()->queryField($table, 'image', $where) == '')
    echo 'OK';
   else
    echo "Error clearing category $id image";
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
   if (self::processAct($act))
    return true;
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
var entity='category';
<?php if ($isHoster) { ?>
function clearImage(id)
{
 if(!confirm('Clear category '+id+' image?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=clearImage&id='+id),false);
 req.send(null);
 var error='Error clearing a category image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
<?php } ?>
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();

 echo "<table class='main' cellspacing='0' cellpadding='0'>\n";
 echo "<caption>" . PageAdm::title() . "</caption>\n";

 echo "<tr>\n";
 echo "<th width='50'>Id</th>\n";
 echo "<th width='50'>Nr</th>\n";
 echo "<th width='100'>Name</th>\n";
 echo "<th" . ($isHoster ? " width='300' colspan='2'" : " width='50'") . ">Image</th>\n";
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

 $fields = 'id,name,image,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select title from biz_menu_cat_abc where cat_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $records = PageAdm::db()->queryArrays('biz_menu_cat a', $fields, null, 'serial,id');

 if ($records)
  foreach ($records as $record)
  {
   $id = $record['id'];
   $name = $record['name'];
   $image = $record['image'];
   if ($image)
    $image = HTTP::embedImage($image, 'image/png');
   $serial = htmlspecialchars($record['serial']);
   $hidden = $record['hidden'] != '';
   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th class='right'><a href='mprcs/?cat=$id'>$id</a></th>\n";
   echo "<td class='right'" . ($isHoster ? " onclick='A.changeSerial(this,$id,entity)'" : '') . ">$serial</td>\n";
   echo "<td" . ($isHoster ? " onclick='A.changeName(this,$id,entity)'" : '') . ">$name</td>\n";
   echo "<td" . ($isHoster ? " onclick='clearImage($id)'" : '') . ">$image</td>\n";
   if ($isHoster)
    echo
      "<td><form method='post' enctype='multipart/form-data'>" .
      "<input type='hidden' name='id' value='$id' />" .
      "<input type='hidden' name='act' value='uploadImage' />" .
      "<input type='file' name='image' size='1' onchange='submit()' />" .
      "</form></td>\n";
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
?>
</body>
</html>
<?php
  return true;
 }

}

?>