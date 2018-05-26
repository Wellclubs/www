<?php

class PageAdmMenuTersMetro
{
 private static function processAct($ter_id, $act)
 {
  $table = 'biz_menu_ter_metro';
  switch ($act)
  {
   case 'addEntity' :
    $id = PageAdm::db()->queryField($table, 'ifnull(max(id),0)+1');
    PageAdm::db()->insertValues($table, array('id' => $id, 'ter_id' => $ter_id));
    if (PageAdm::db()->affected_rows == 1)
     echo 'OK';
    else
     echo "Error adding the metro station $id record to the database";
    return true;

   case 'deleteEntity' :
    $id = intval(HTTP::param('id'));

    $where = "id=$id";
    PageAdm::db()->deleteRecords($table, $where);
    $count = PageAdm::db()->queryField($table, 'count(*)', $where);
    if ($count == 0)
     echo 'OK';
    else
     echo "Error deleting the metro station $id record from the database";
    return true;

   case 'changeTitle' :
    $id = intval(HTTP::param('id'));
    $lang = HTTP::param('lang');
    $title = HTTP::param('title');
    $table .= '_abc';
    $where = "metro_id=$id and abc_id='$lang'";
    if ($title != '')
    {
     PageAdm::db()->modifyFields($table, array('title' => DB::str($title)), $where);
     if (PageAdm::db()->affected_rows == 0)
      PageAdm::db()->insertValues($table, array('metro_id' => $id, 'abc_id' => DB::str($lang), 'title' => DB::str($title)));
    }
    else
     PageAdm::db()->deleteRecords($table, $where);
    if (PageAdm::db()->queryField($table, 'title', $where) == $title)
     echo 'OK';
    else
     echo "Error changing metro station $id language '$lang' title to '$title'";
    return true;

   case 'hideEntity' :
    $id = intval(HTTP::param('id'));
    $hide = HTTP::param('hide');
    $where = "id=$id";
    PageAdm::db()->modifyFields($table, array('hidden' => (($hide == '1') ? "'1'" : 'null')), $where);
    if (PageAdm::db()->queryField($table, 'hidden', $where) == $hide)
     echo 'OK';
    else
     echo "Error " . ($hide ? '' : 'un') . "hiding the metro station $id";
    return true;
    
   default :
    echo "Unsupported action: '$act'";
    return true;
  }
 }

 public static function showPage()
 {
  $ter_id = HTTP::get('ter');
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($ter_id, $_REQUEST['act']))
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
<?php if ($ter_id != '') { ?>
function addEntity()
{
 if(!confirm('Create a new metro station?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'&act=addEntity',false);
 req.send(null);
 var error='Error adding a metro station on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteEntity(id)
{
 if(!confirm('Delete metro station '+id+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'&act=deleteEntity&id='+id,false);
 req.send(null);
 var error='Error deleting a metro station on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 var row=el('row-'+id);
 row.parentNode.removeChild(row);
}
function changeTitle(td,id,lang)
{
 var oldValue=decodeHTML(td.innerHTML);
 var newValue=prompt('Input a new title for row '+id+', column "'+lang+'":',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'&act=changeTitle&id='+id+'&lang='+lang+'&title='+newValue,false);
 req.send(null);
 var error='Error storing new title on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 td.innerHTML=newValue;
}
function hideEntity(button,id,hide)
{
 if(!confirm((hide?'Hide':'Show')+' row '+id+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'&act=hideEntity&id='+id+'&hide='+(hide?'1':''),false);
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
<?php } ?>
function selectTerritory(id)
{
 var uri=''+document.location;
 var pos=uri.indexOf('?');
 if(pos>=0)
  uri=uri.substr(0,pos);
 document.location=uri+'?ter='+id;
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<span>Select a <a href="mters/">territory</a>:</span><select onchange="selectTerritory(value)">
<option></option>
<?php
 $ters = PageAdm::db()->queryRecords('biz_menu_ter', 'id,name', '', 'serial,id');
 foreach ($ters as $ter)
 {
  $id = $ter[0];
  $selected = ($id == $ter_id) ? ' selected' : '';
  $title = Lang::getDBValueDef('biz_menu_ter_abc', 'title', "ter_id=$id", $ter[1]);
  echo "<option value='$id'$selected>$title</option>\n";
 }
?></select><br/>
<?php if ($ter_id != '') { ?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<tr>
<th width='50'>Id</th>
<?php
 $langs = Lang::map();
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="title">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?><th width='1' colspan='2'><input type='button' value='Create new' onclick='addEntity()'/></th>
</tr>
<?php
 $fields = 'id,hidden';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select title from biz_menu_ter_metro_abc where metro_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $records = PageAdm::db()->queryRecords('biz_menu_ter_metro a', $fields, "ter_id=$ter_id", 'id');
 if ($records)
  foreach ($records as $record)
  {
   $id = $record[0];
   $name = $record[1];
   $serial = htmlspecialchars($record[2]);
   $hidden = $record[3] != '';
   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th><b>$id</b></th>\n";
   $i = 2;
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
<?php } ?>
</body>
</html>
<?php
  return true;
 }

}

?>