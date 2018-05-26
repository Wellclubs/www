<?php
class PageAdmTops {
 const TABLE = 'com_top_centre';

 private static function processAct($act)
 {
  $table = self::TABLE;
  $entity = 'promotion';
  switch ($act)
  {
  case 'createEntity' :
   $row = HTTP::param('row');
   PageAdm::createEntity($table, "row $row $entity", null,
     array('domain_id' => WDomain::id(), 'row' => $row), null,
     array('noname' => true));
   break;

  case 'deleteEntity' :
   PageAdm::deleteEntity($table, $entity);
   break;

  case 'changeSerial' :
   PageAdm::changeSerial($table, $entity);
   break;

  case 'changeField' :
   PageAdm::changeField($table, $entity);
   break;

  case 'uploadImage' :
   $id = HTTP::param('id');
   $row = HTTP::param('row');
   if (!WTop::uploadImage($row, $id))
    return Base::addError('Error uploading an image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'clearImage' :
   $id = HTTP::param('id');
   if (!WTop::clearImage($id))
    echo 'Error clearing an image';
   else
    echo 'OK';// . DB::lastQuery();
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
var entity='promotion';
function createEntity(row)
{
 A.createEntity('row '+row+' '+entity,null,'row='+row,true);
}
function deleteEntity(row,id)
{
 A.deleteEntity(id,'row '+row+' '+entity);
}
function changeSerial(node,row,id)
{
 A.changeSerial(node,id,'row '+row+' '+entity);
 document.location.reload(true);
}
function changeCentre(node,row,id)
{
 A.changeField(node,id,'row '+row+' '+entity,'centre_id');
 document.location.reload(true);
}
function clearImage(row,id)
{
 if(!confirm('Clear row '+row+' '+entity+' '+id+' image?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=clearImage&id='+id),false);
 req.send(null);
 var error='Error clearing a row '+row+' '+entity+' '+id+' image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function hideEntity(button,row,id,hide)
{
 A.hideEntity(button,id,hide,'row '+row+' '+entity);
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop();
$domainId = WDomain::id();
for ($row = 1; $row <= 3; $row++)
{
?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title() . " row $row";?></caption>
<tr>
<th width="50">Nr</th>
<th width="50">Id</th>
<th width="100">Centre</th>
<th width="500">Picture</th>
<th width="150">Filename</th>
<th width="100">Size</th>
<?php
// foreach ($langs as $lang => $Lang)
//  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
echo "<th width='1' colspan='2'><input type='button' value='Create new' onclick='createEntity($row)'/></th>\n";
?>
</tr>
<?php
$fields = 'id,serial,centre_id,coalesce' .
  '((select name from com_centre where id=a.centre_id)' .
  ',(select title from com_centre_abc where centre_id=a.centre_id and abc_id=\'' . Lang::current() . '\')' .
  ',(select title from com_centre_abc where centre_id=a.centre_id limit 1)' .
  ')centre_name,image,filename,size,width,height,hidden';
//foreach ($langs as $lang => $Lang)
// $fields .= ',(select title from com_centre_img_abc where centre_id=a.centre_id and serial=a.serial and abc_id=\'' . $lang . '\')title_' . $lang;
$where = "domain_id=$domainId and row=$row";
$records = PageAdm::db()->queryArrays(self::TABLE . ' a', $fields, $where, 'serial,id');
if ($records)
{
 foreach ($records as $record)
 {
  $id = $record['id'];
  $serial = $record['serial'];
  $centreId = $record['centre_id'];
  $centreName = $record['centre_name'];
  $filename = $record['filename'];
  $width = $record['width'];
  $height = $record['height'];
  $img = ($record['image']) ? "<img width='$width' height='$height' src='img/top-$row/$id/$filename'/>" : '';
  $size = ($record['width'] . 'x' . $record['height'] . '<br>' . number_format($record['size']));
  $hidden = $record['hidden'] != '';
  $class = $hidden ? (" class='hidden'") : '';
  echo "<tr id='row-$row-$id'$class>\n";
  echo "<th class='right'>$id</th>\n";
  echo "<td class='right' onclick='changeSerial(this,$row,$id)'>$serial</td>\n";
  echo "<td class='right' onclick='changeCentre(this,$row,$id)'>$centreId</td>\n";
  echo "<th rowspan='2'>$img</th>\n";
  echo "<th>$filename</th>\n";
  echo "<th>$size</th>\n";
  //foreach ($langs as $lang => $Lang)
  // echo "<td rowspan='2' onclick='changeImageTitle(this,$i,\"$lang\")'>" . htmlspecialchars($record['title_' . $lang]) . "</td>\n";
  $value = $hidden ? 'Show' : 'Hide';
  $arg = $hidden ? 'false' : 'true';
  echo "<th>" .
    "<input type='button' value='$value' onclick='hideEntity(this,$row,$id,$arg)'/>" .
    "<br/>" .
    "<input type='button' value='Clear' onclick='clearImage($row,$id)'/>" .
    "</th>\n";
  echo "</tr>\n";
  echo "<tr id='row-$row-2'$class>\n";
  echo "<th colspan='3' class='left'>$centreName</th>\n";
  echo
    "<th colspan='2'>" .
    "<form method='post' enctype='multipart/form-data'>" .
    "<input type='hidden' name='act' value='uploadImage' />" .
    "<input type='hidden' name='row' value='$row' />" .
    "<input type='hidden' name='id' value='$id' />" .
    "<input type='file' name='image' size='1' onchange='submit()' />" .
    "</form>" .
    "</th>";
  echo "<th><input type='button' value='Delete' onclick='deleteEntity($row,$id)'/></th>\n";
  echo "</tr>\n";
 }
}
//else
// echo DB::lastQuery();
?></table>
<?php
}
?>
</body>
</html>
<?php
  return true;
 }

}

?>
