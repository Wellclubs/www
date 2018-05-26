<?php

class PageAdmFAQs
{
 const TABLE = 'art_faq';
 const TABLE_ABC = 'art_faq_abc';

 const MAXLEN = 300;

 private static function processAct($act)
 {
  $table = self::TABLE;
  $entity = 'question';
  switch ($act)
  {
   case 'createEntity' :
    PageAdm::createEntity($table, $entity, null);
    break;

   case 'deleteEntity' :
    $id = intval(HTTP::paramInt('id'));
    $where = array('id' => $id);
    if (!intval(PageAdm::db()->queryField($table, 'count(*)', $where)))
     echo ucfirst($entity) . " $id not found";
    else
     PageAdm::deleteEntity($table, $entity, $id);
    break;

   case 'changeName' :
    PageAdm::changeName($table, $entity);
    break;

   case 'changeTitle' :
    PageAdm::changeTitle($table, 'faq_id', $entity);
    break;

   case 'changeSerial' :
    PageAdm::changeSerial($table, $entity);
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
var entity='question';
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
<th width='300'>Name</th>
<?php
 $langs = Lang::map();
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="300" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?><th width='1' colspan='2'><input type='button' value='Create new' onclick='addEntity()'/></th>
</tr>
<?php
 $table = self::TABLE;
 $fields = 'id,name,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fields .= ",(select title from {$table}_abc where faq_id=a.id and abc_id='$lang')title_$lang";
 $records = PageAdm::db()->queryRecords("$table a", $fields, null, 'serial,id');
 //exit(DB::lastQuery());
 if ($records)
 {
  $colspan = 4 + count($langs);
  foreach ($records as $record)
  {
   $id = $record[0];
   $name = $record[1];
   $serial = htmlspecialchars($record[2]);
   $hidden = $record[3] != '';
   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th>$id</th>\n";
   echo "<td class='right' onclick='changeSerial(this,$id)'>$serial</td>\n";
   echo "<td onclick='changeName(this,$id)'>$name</td>\n";
   $i = 4;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($record[$i++]);
    echo "<td onclick='changeTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   $value = $hidden ? 'Show' : 'Hide';
   $arg = $hidden ? 'false' : 'true';
   echo "<th><input type='button' value='$value' onclick='hideEntity(this,$id,$arg)'/></th>\n";
   echo "<th><input type='button' value='Delete' onclick='deleteEntity($id)'/></th>\n";
   echo "</tr>\n";
   foreach ($langs as $lang => $Lang)
   {
    $where = array('faq_id' => $id, 'abc_id' => DB::str($lang));
    $reply = PageAdm::db()->queryField(self::TABLE_ABC, 'reply', $where);
    if (!strlen($reply))
     $reply = '<p class="center">' . Lang::getPageWord('placeholder', 'Click here to edit') . '</p>';
    else
    {
     if (strlen($reply) > self::MAXLEN)
      $reply = substr($reply, 0, self::MAXLEN) . '...';
     $reply = htmlspecialchars($reply);
    }
    echo "<tr><th>" . $Lang->htmlImage() . '<br/>' . $Lang->title() .
      "</th><td colspan='$colspan'><a class='b' href='faq-$id?lang=$lang'><pre>\n";
    echo $reply;
    echo "</pre></a></td></tr>\n";
   }
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