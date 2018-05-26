<?php

class PageAdmMenuBgs
{
 private static function processAct($act)
 {
  $table = 'biz_menu_bg';
  $entity = 'background';
  switch ($act)
  {
   case 'createEntity' :
    $id = PageAdm::db()->queryField($table, 'ifnull(max(id),0)+1');
    PageAdm::db()->insertValues($table, array('id' => $id, 'serial' => $id));
    if (PageAdm::db()->affected_rows == 1)
     echo 'OK';
    else
     echo "Error adding the background $id record to the database";
    break;

   case 'deleteEntity' :
    $id = intval(HTTP::param('id'));
    $where = "id=$id";
    PageAdm::db()->deleteRecords($table, $where);
    $count = PageAdm::db()->queryField($table, 'count(*)', $where);
    if ($count == 0)
     echo 'OK';
    else
     echo "Error deleting the background $id record from the database";
    break;

   case 'uploadImage' :
    $id = intval(HTTP::param('id'));
    PageAdm::db()->uploadFile('image', $table, array('contents' => 'image', 'size' => '', 'width' => '', 'height' => ''), "id=$id");
    return false;

   case 'clearImage' :
    $id = intval(HTTP::param('id'));
    $where = "id=$id";
    PageAdm::db()->modifyFields($table, array('image' => 'null'), $where);
    if (PageAdm::db()->queryField($table, 'image', $where) == '')
     echo 'OK';
    else
     echo "Error clearing background $id image";
    break;

   case 'changeURI' :
    $id = intval(HTTP::param('id'));
    $uri = HTTP::param('uri');
    $where = "id=$id";
    PageAdm::db()->modifyField($table, 'uri', 's', $uri, $where);
    if (PageAdm::db()->queryField($table, 'uri', $where) == $uri)
     echo 'OK';
    else
     echo "Error changing background $id URI to $uri";
    break;

   case 'changeTitle' :
    $id = intval(HTTP::param('id'));
    $lang = HTTP::param('lang');
    $title = HTTP::param('title');
    $table .= '_abc';
    $where = "bg_id=$id and abc_id='$lang'";
    if ($title != '')
    {
     //PageAdm::db()->modifyField($table, 'title', 's', $title, $where);
     PageAdm::db()->modifyFields($table, array('title' => DB::str($title)), $where);
     if (PageAdm::db()->affected_rows == 0)
      PageAdm::db()->insertValues($table, array('bg_id' => $id, 'abc_id' => DB::str($lang), 'title' => DB::str($title)));
    }
    else
     PageAdm::db()->deleteRecords($table, $where);
    if (PageAdm::db()->queryField($table, 'title', $where) == $title)
     echo 'OK';
    else
     echo "Error changing background $id language '$lang' title to '$title'";
    break;

   case 'changeSerial' :
    $id = intval(HTTP::param('id'));
    $serial = intval(HTTP::param('serial'));
    $field = 'serial';
    $where = "id=$id";
    PageAdm::db()->modifyField($table, $field, 'i', $serial, $where);
    if (PageAdm::db()->queryField($table, $field, $where) == $serial)
     echo 'OK';
    else
     echo "Error changing background $id serial number to $serial";
    break;

   case 'hideEntity' :
    $id = intval(HTTP::param('id'));
    $hide = HTTP::param('hide');
    $field = 'hidden';
    $where = "id=$id";
    PageAdm::db()->modifyFields($table, array('hidden' => (($hide == '1') ? "'1'" : 'null')), $where);
    if (PageAdm::db()->queryField($table, 'hidden', $where) == $hide)
     echo 'OK';
    else
     echo "Error " . ($hide ? '' : 'un') . "hiding the background $id";
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
</style>
<script>
var entity='background';
function createEntity()
{
 if(!confirm('Create new '+entity+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createEntity'),false);
 req.send(null);
 var error='Error adding a '+entity+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteEntity(id)
{
 A.deleteEntity(id,entity,'','row-'+id);
}
function clearImage(id)
{
 if(!confirm('Clear background '+id+' image?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=clearImage&id='+id),false);
 req.send(null);
 var error='Error clearing a background image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeURI(id)
{
 var td=el('uri-'+id);
 var oldValue=decodeHTML(td.innerHTML);
 var newValue=prompt('Input a new URI for row '+id+':',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeURI&id='+id+'&uri='+newValue),false);
 req.send(null);
 var error='Error changing the background URI on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 td.innerHTML=newValue;
}
function changeSerial(node,id)
{
 A.changeSerial(node,id,entity)
}
function changeTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entity);
}
function hideEntity(button,id,hide)
{
 if(!confirm((hide?'Hide':'Show')+' row '+id+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=hideEntity&id='+id+'&hide='+(hide?'1':'')),false);
 req.send(null);
 var error='Error '+(hide?'hid':'show')+'ing the row '+id+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 button.parentNode.parentNode.className=hide?'hidden':'';
 button.value=hide?'Show':'Hide';
 button.onclick=eval('(function onclick(){hideEntity(this,'+id+','+(!hide)+')})');
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='300' colspan="2">Image</th>
<th width='200'>URI</th>
<th width='100'>Size</th>
<?php
 $langs = Lang::map();
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?><th width='1' colspan='2'><input type='button' value='Create new' onclick='createEntity()'/></th>
</tr>
<?php
 $fields = 'id,image,uri,size,width,height,serial,hidden';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select title from biz_menu_bg_abc where bg_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $records = PageAdm::db()->queryRecords('biz_menu_bg a', $fields, null, 'serial,id');
 //foreach (DB::queries() as $query)
 // echo "<!-- $query -->\n";
 if ($records)
  foreach ($records as $record)
  {
   $id = $record[0];
   $image = $record[1];
   if ($image)
    $image = "<img height='100' src='img/menu-bg-$id.jpg'/>";
   $form =
     "<form method='post' enctype='multipart/form-data'>" .
     "<input type='hidden' name='id' value='$id' />" .
     "<input type='hidden' name='act' value='uploadImage' />" .
     "<input type='file' name='image' size='1' onchange='submit()' />" .
     "</form>";
   $uri = $record[2];
   $size = number_format($record[3]);
   $width = $record[4];
   $height = $record[5];
   $serial = htmlspecialchars($record[6]);
   $hidden = $record[7] != '';
   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<td class='right' onclick='changeSerial(this,$id)'>$serial</u></td>\n";
   echo "<td onclick='clearImage($id)'>$image</td>\n";
   echo "<td>$form</td>\n";
   echo "<td id='uri-$id' onclick='changeURI($id)'>$uri</b></td>\n";
   echo "<th>{$width}x{$height}<br>$size</th>\n";
   $i = 8;
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
  }
?></table>
</body>
</html>
<?php
  return true;
 }

}

?>