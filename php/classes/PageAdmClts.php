<?php

class PageAdmClts
{
 private static function processAct($act)
 {
  $table = WClient::TABLE_CLIENT;
  $entity = 'client';
  switch ($act)
  {
   case 'addClient' :
    $title = HTTP::param('title');
    $firstname = HTTP::param('firstname');
    $lastname = HTTP::param('lastname');
    $email = HTTP::param('email');
    $pass = HTTP::param('pass');
    if (!strlen($pass))
     $pass = WClient::createPassword();

    $client = PageAdm::db()->queryFields($table, 'id,firstname,lastname', "email='$email'");
    if (isset($client))
     echo "Adding $entity ['$name','$email'] - Duplicated E-mail '$email': $entity $client[0] ('$client[1] $client[2]')";
    else
    {
     $values = array
     (
      'title' => DB::str($title)
     ,'firstname' => DB::str($firstname)
     ,'lastname' => DB::str($lastname)
     ,'email' => DB::str($email)
     ,'pass' => DB::str($pass)
     );
     if (PageAdm::db()->insertValues($table, $values))
      echo 'OK';
     else
      echo "Error adding new $entity record to DB";
    }
    break;

   case 'deleteClient' :
    $id = intval(HTTP::param('id'));
    if ($id <= 0)
     echo "Client id is invalid: $id";
    else if (intval(PageAdm::db()->queryField('biz_member', 'count(*)', 'client_id=' . $id)) > 0)
     echo "Client $id is a club member";
    else if (intval(PageAdm::db()->queryField('com_master', 'count(*)', 'client_id=' . $id)) > 0)
     echo "Client $id is linked as a master in some business centres";
    else
    {
     PageAdm::db()->deleteRecords($table, "id='$id'");
     $count = PageAdm::db()->queryField($table, 'count(*)', 'id=' . $id);
     if ($count == 0)
      echo 'OK';
     else
      echo "Error deleting $entity $id record from the database";
    }
    break;

   case 'changeField' :
    $id = HTTP::param('id');
    $field = HTTP::param('field');
    $value = HTTP::param('value');

    if ($field == 'email')
    {
     if (!strlen($value))
     {
      echo 'E-mail address is empty';
      break;
     }

     $client = PageAdm::db()->queryFields($table, 'id,trim(concat(firstname,\' \',lastname))', "email='$value' and id<>'$id'");
     if (isset($client))
     {
      echo "Changing $entity $id E-mail to '$value' failed - Duplicated E-mail with $entity $client[0] ('$client[1]')";
      break;
     }
    }
    else if ($field == 'pass')
    {
     if (!strlen($value))
      $value = WClient::createPassword();
    }

    $where = 'id=\'' . $id . '\'';
    PageAdm::db()->modifyField($table, $field, 's', $value, $where);
    echo PageAdm::db()->queryField($table, $field, $where);

    break;

  case 'changeFlag' :
   PageAdm::changeFlag($table, $entity);
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
   if (!$isHoster || self::processAct($_REQUEST['act']))
    exit;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
.dt { font-size:8px;line-height:18px;white-space:nowrap; }
</style>
<?php if ($isHoster) { ?>
<script>
var entity='client';
function clientName(id)
{
 return el('title-'+id).innerHTML+' '+el('firstname-'+id).innerHTML+' '+el('lastname-'+id).innerHTML
}
function addClient()
{
 var title=el('title-new').value;
 var firstname=el('firstname-new').value;
 var lastname=el('lastname-new').value;
 var email=el('email-new').value;
 var pass=el('pass-new').value;
 if(lastname=='')
  return el('lastname-new').select()||alert('Last Name not entered')
 if(email=='')
  return el('email-new').select()||alert('E-mail not entered')
 if(!confirm('Add new '+entity+' "'+title+' "'+firstname+'" "'+lastname+'" ("'+email+'")?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=addClient'+
   '&title='+encodeURIComponent(title)+
   '&firstname='+encodeURIComponent(firstname)+
   '&lastname='+encodeURIComponent(lastname)+
   '&email='+encodeURIComponent(email)+
   '&pass='+encodeURIComponent(pass),false);
 req.send(null);
 if (req.status!=200)
  return alert('Error creating '+entity+' on the server');
 if(req.responseText!='OK')
  return alert(req.responseText);
 document.location.reload(true);
}
function deleteClient(id)
{
 if(!confirm('Delete '+entity+' '+id+' ("'+clientName(id)+'")?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=deleteClient&id='+id,false);
 req.send(null);
 if (req.status!=200)
  return alert('Error deleting '+entity+' '+id+' on the server');
 if(req.responseText!='OK')
  return alert(req.responseText);
 var row=el('row-'+id);
 row.parentNode.removeChild(row);
}
function changeField(node,id,field,value)
{
 if(typeof value=='undefined')
 {
  var prev=decodeHTML(node.innerHTML);
  value=prompt('Enter new field "'+field+'" value:',prev);
  if((value==null)||(value==prev)&&((field!='pass')||(prev='')))
   return;
 }
 else if(!confirm('Change '+entity+' "'+id+'" ("'+clientName(id)+'") field "'+field+'" to "'+value+'"?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeField&id='+id+'&field='+field+'&value='+encodeURIComponent(value),false);
 req.send(null);
 var err='Error changing field '+field+' value on the server';
 if(req.status!=200)
  return alert(err);
 if((req.responseText!=value)&&((field!='pass')||(value!='')))
  return alert(err+': '+req.responseText);
 node.innerHTML=req.responseText;
}
</script>
<?php } ?>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="act" />
<table class="main" cellspacing='0'>
<caption><?php echo PageAdm::title();?></caption>
<?php
  $showCtrs = $isHoster || PageAdm::checkWorkerMode('ctrs');
  $showBnds = $isHoster || PageAdm::checkWorkerMode('bnds');

  $header = array();

  $header[] = array(50, 'Client');
  if ($isHoster)
   $header[] = array(20, 'WC');
  if ($showCtrs)
   $header[] = array(20, 'C');
  if ($showBnds)
   $header[] = array(20, 'B');
  if ($isHoster)
   $header[] = array(20, 'M');

  $header[] = array(100, 'Title');
  $header[] = array(200, 'First Name');
  $header[] = array(200, 'Last Name');
  $header[] = array(200, 'E-mail');
  if ($isHoster)
   $header[] = array(200, 'Password');

  $header[] = array(50, 'Created');
  $header[] = array(50, 'Visited');
  $header[] = array(50, 'Locked');
  if ($isHoster)
  {
   $header[] = array(50, 'Worker');
   $header[] = array(50, '');
  }

  echo '<colgroup>';
  foreach ($header as $values)
   echo "<col width={$values[0]}>";
  echo '</colgroup>' . "\n";

  echo '<tr>';
  foreach ($header as $values)
   echo "<th>{$values[1]}</th>";
  echo '</tr>' . "\n";

  $fields = 'id,title,firstname,lastname,email,pass,created,visited,is_locked,is_worker' .
    ($isHoster ? ',(select 1 from biz_member where client_id=a.id)is_member' : '') .
    ($showCtrs ? ',(select count(*) from com_centre where member_id=a.id)centre_count' : '') .
    ($showBnds ? ',(select count(*) from com_brand where member_id=a.id)brand_count' : '') .
    ($isHoster ? ',(select count(*) from com_master where client_id=a.id)master_count' : '');
  $records = PageAdm::db()->queryArrays('biz_client a', $fields, null, 'id');
  if ($records)
  {
   foreach ($records as $record)
   {
    $id = $record['id'];
    $title = $record['title'];
    $firstname = $record['firstname'];
    $lastname = $record['lastname'];
    $email = $record['email'];
    $pass = $record['pass'];
    $created = $record['created'];
    $visited = $record['visited'];
    $is_locked = $record['is_locked'] != null;
    $is_worker = $record['is_worker'] != null;
    $is_member = $isHoster ? ($record['is_member'] != null) : false;
    $centre_count = $showCtrs ? $record['centre_count'] : 0;
    $brand_count = $showBnds ? $record['brand_count'] : 0;
    $master_count = $isHoster ? $record['master_count'] : 0;

    echo "<tr id='row-$id'>\n";
    echo "<th class='right'><a href='clt-$id/'>$id</a></th>\n";
    if ($isHoster)
     echo "<th>" . ($is_member ? "<a href='mbr-$id/'>M</a>" : "&nbsp;") . "</th>\n";
    if ($showCtrs)
     echo "<th>" . ($centre_count ? "<a href='ctrs/?mbr=$id&bnd=A/'>$centre_count</a>" : "&nbsp;") . "</th>\n";
    if ($showBnds)
     echo "<th>" . ($brand_count ? "<a href='bnds/?mbr=$id/'>$brand_count</a>" : "&nbsp;") . "</th>\n";
    if ($isHoster)
     echo "<th>" . ($master_count ? "$master_count" : "&nbsp;") . "</th>\n";

    echo "<td id='title-$id'" . ($isHoster ? " onclick='changeField(this,\"$id\",\"title\")'" : '') . " value='" . Util::strJS($title) . "'>" . htmlspecialchars($title) . "</td>\n";
    echo "<td id='firstname-$id'" . ($isHoster ? " onclick='changeField(this,\"$id\",\"firstname\")'" : '') . " value='" . Util::strJS($firstname) . "'>" . htmlspecialchars($firstname) . "</td>\n";
    echo "<td id='lastname-$id'" . ($isHoster ? " onclick='changeField(this,\"$id\",\"lastname\")'" : '') . " value='" . Util::strJS($lastname) . "'>" . htmlspecialchars($lastname) . "</td>\n";
    echo "<td id='email-$id'" . ($isHoster ? " onclick='changeField(this,\"$id\",\"email\")'" : '') . " value='" . Util::strJS($email) . "'>" . htmlspecialchars($email) . "</td>\n";
    if ($isHoster)
     echo "<td id='pass-$id' onclick='changeField(this,\"$id\",\"pass\")' value='" . Util::strJS($pass) . "'>" . htmlspecialchars($pass) . "</td>\n";
    echo "<th class='dt' title='$created'>$created</th>\n";
    echo "<th class='dt' title='$visited'>$visited</th>\n";
    echo "<td" . ($is_locked ? " class='checked'" : null) . " onclick='A.changeFlag(this,$id,entity,\"is_locked\")'></td>\n";
    if ($isHoster)
    {
     echo "<td" . ($is_worker ? " class='checked'" : null) . " onclick='A.changeFlag(this,$id,entity,\"is_worker\")'></td>\n";
     echo "<th><input type='button' value='Delete' onclick='deleteClient(\"$id\")' /></th>\n";
    }
    echo "</tr>\n";
   }
  }
  else
  {
   echo 'Invalid SQL: ' . DB::lastQuery();
  }

  if ($isHoster)
  {
?><tr>
<th colspan="5"></th>
<td><input id='title-new' maxlength='30' style='width:100px;border:0;' /></td>
<td><input id='firstname-new' maxlength='30' style='width:200px;border:0;' /></td>
<td><input id='lastname-new' maxlength='30' style='width:200px;border:0;' /></td>
<td><input id='email-new' maxlength='50' style='width:200px;border:0;' /></td>
<td><input id='pass-new' maxlength='30' style='width:200px;border:0;' /></td>
<th colspan="3"></th>
<th colspan="2"><input type='button' value='Create new client' onclick='addClient()' /></th>
</tr>
<?php
  }
?>
</table>
</form>
</body>
</html>
<?php
  return true;
 }
}

?>