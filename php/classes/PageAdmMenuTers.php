<?php

class PageAdmMenuTers
{
 const TABLE = 'biz_menu_ter';

 private static function processAct($act)
 {
  $table = self::TABLE;
  $entity = 'territory';
  switch ($act)
  {
   case 'createEntity' :
    PageAdm::createEntity($table, $entity, null, array('domain_id' => WDomain::id()));
    break;

   case 'deleteEntity' :
    $id = intval(HTTP::paramInt('id'));
    $where = array('id' => $id, 'domain_id' => WDomain::id());
    if (!intval(PageAdm::db()->queryField($table, 'count(*)', $where)))
    {
     echo ucfirst($entity) . " $id not found";
    }
    else if (intval(PageAdm::db()->queryField('biz_menu_ter_metro', 'count(*)', "ter_id=$id")) > 0)
    {
     echo ucfirst($entity) . " $id has some metro stations linked";
    }
    else if (intval(PageAdm::db()->queryField('com_centre', 'count(*)', "ter_id=$id")) > 0)
    {
     echo ucfirst($entity) . " $id has some business centres linked";
    }
    else
    {
     PageAdm::deleteEntity($table, $entity, $id);
    }
    break;

   case 'changeName' :
    PageAdm::changeName($table, $entity, 'domain_id=' . WDomain::id());
    break;

   case 'changeTitle' :
    PageAdm::changeTitle($table, 'ter_id', $entity);
    break;

   case 'changeSerial' :
    PageAdm::changeSerial($table, $entity);
    break;

   case 'changeField' :
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
var entity='territory';
function addEntity()
{
 A.createEntity(entity,null,null,false);
}
function deleteEntity(id)
{
 A.deleteEntity(id,entity,null,'row-'+id);
}
function changeName(node,id)
{
 A.changeName(node,id,entity);
}
function changeSerial(node,id)
{
 A.changeSerial(node,id,entity);
}
function changeTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entity);
}
function changeWid(node,id)
{
 A.changeField(node,id,entity,'wid');
}
function changeRef(node,id)
{
 A.changeField(node,id,entity,'ref');
}
function hideEntity(button,id,hide)
{
 A.hideEntity(button,id,hide,entity);
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<tr>
<th width='50'>Nr</th>
<th width='50'>Id</th>
<th width='100'>Name</th>
<?php
 $langs = Lang::map();
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
<th width='100'>Wid</th>
<th width='100'>Ref</th>
<th width='1' colspan='2'><input type='button' value='Create new' onclick='addEntity()'/></th>
</tr>
<?php
 $table = self::TABLE;
 $fields = 'id,name,wid,ref,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fields .= ",(select title from {$table}_abc where ter_id=a.id and abc_id='$lang')title_$lang";
 $records = PageAdm::db()->queryRecords("$table a", $fields, 'domain_id=' . WDomain::id(), 'serial,id');
 if ($records)
  foreach ($records as $record)
  {
   $id = $record[0];
   $name = $record[1];
   $wid = $record[2];
   $ref = $record[3];
   $serial = htmlspecialchars($record[4]);
   $hidden = $record[5] != '';
   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th><a href='mterms/?ter=$id'>$id</a></th>\n";
   echo "<td class='right' onclick='changeSerial(this,$id)'>$serial</td>\n";
   echo "<td onclick='changeName(this,$id)'>$name</td>\n";
   $i = 6;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($record[$i++]);
    echo "<td onclick='changeTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   echo "<td onclick='changeWid(this,$id)'>$wid</td>\n";
   echo "<td onclick='changeRef(this,$id)'>$ref</td>\n";
   $value = $hidden ? 'Show' : 'Hide';
   $arg = $hidden ? 'false' : 'true';
   echo "<th><input type='button' value='$value' onclick='hideEntity(this,$id,$arg)'/></th>\n";
   echo "<th><input type='button' value='Delete' onclick='deleteEntity($id)'/></th>\n";
   echo "</tr>\n";
  }
?></table>
</body>
</html>
<?php
  return true;
 }

}

?>