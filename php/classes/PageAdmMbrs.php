<?php

/**
 * Description of PageAdmMbrs
 */
class PageAdmMbrs
{
 private static function processAct($act)
 {
  $table = WMember::TABLE_MEMBER;
  $entity = 'member';
  switch ($act)
  {
   case 'excludeMember' :
    $id = intval(HTTP::param('id'));
    if ($id <= 0)
     echo "Member id is invalid: $id";
    else if (intval(PageAdm::db()->queryField('com_brand', 'count(*)', 'member_id=' . $id)) > 0)
     echo "Member $id has some brands linked";
    else
    {
     PageAdm::db()->deleteRecords($table, 'client_id=' . $id);
     $count = PageAdm::db()->queryField($table, 'count(*)', 'client_id=' . $id);
     if ($count == 0)
      echo 'OK';
     else
      echo "Error deleting $entity $id record from the database";
    }
    return true;

   //case 'changeField' :
   // return PageAdm::changeField($table, $entity);
  }
  return false;
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
<style>
</style>
<script>
var entity='member';
function excludeMember(id)
{
 if(!confirm('Exclude the '+entity+' '+id+' ("'+el('name-'+id).innerHTML+'") from the club?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=excludeMember&id='+id),false);
 req.send(null);
 if (req.status!=200)
  return alert('Error excluding the '+entity+' '+id+' from the club');
 if(req.responseText!='OK')
  return alert(req.responseText);
 var row=el('row-'+id);
 row.parentNode.removeChild(row);
}
<?php /*
function changeField(node,id,field,value)
{
 if(typeof value=='undefined')
 {
  var prev=decodeHTML(node.innerHTML);
  value=prompt('Enter new field "'+field+'" value:',prev);
  if((value==null)||(value==prev))
   return;
 }
 else if(!confirm('Change '+entity+' "'+id+'" ("'+el('name-'+id).innerHTML+'") field "'+field+'" to "'+value+'"?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeField&id='+id+'&field='+field+'&value='+value),false);
 req.send(null);
 if (req.status!=200)
  return alert('Error changing field '+field+' value on the server');
 if(req.responseText!='OK')
  return alert(req.responseText);
 node.innerHTML=value;
}
*/ ?>
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<table class="main" cellspacing='0'>
<colgroup><col width="50"><col width="200"><col width="50"><col width="100"><col width="200"><col width="50"></colgroup>
<tr><th>Client</th><th>Name</th><th>Brands</th><th>Centres</th><th>Entered</th><th>Actions</th></tr>
<?php
  $fields = 'client_id' .
    ',(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id)' .
    ',(select count(*) from com_brand where member_id=a.client_id)' .
    ',(select count(*) from com_centre where member_id=a.client_id)' .
    ',(select count(*) from com_centre where member_id=a.client_id and hidden is null)' .
    ',entered';
  $records = PageAdm::db()->queryRecords('biz_member a', $fields, null, 'client_id');
  if ($records)
   foreach ($records as $record)
   {
    $id = $record[0];
    $name = htmlspecialchars($record[1]);
    $brands = $record[2];
    $centres = $record[3];
    $active = $record[4];
    $entered = $record[5];
    echo "<tr id='row-$id'>\n";
    echo "<th><a href='clt-$id/'>$id</a></th>\n";
    echo "<th><a href='mbr-$id/' id='name-$id'>$name</a></th>\n";
    echo "<th><a href='bnds/?mbr=$id'>$brands</a></th>\n";
    echo "<th><a href='ctrs/?mbr=$id'>$active / $centres</a></th>\n";
    echo "<th>$entered</th>\n";
    echo "<th><input type='button' value='Exclude' onclick='excludeMember(\"$id\")' /></th>\n";
    echo "</tr>\n";
   }
  else
   echo "<tr><th colspan='6'>No data</th></tr>\n";
?></table>
</body>
</html>
<?php
  return true;
 }
}

?>
