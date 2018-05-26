<?php

/**
 * Description of PageAdmClt
 */
class PageAdmClt
{
 private static $me = null;

 private static function processAct($act)
 {
  $table = WClient::TABLE_CLIENT;
  $clientId = self::$me->getId();
  switch ($act)
  {
  case 'becomeMember' :
   $table = WMember::TABLE_MEMBER;
   if (intval(PageAdm::db()->queryField($table, 'count(*)', 'client_id=' . $clientId)) > 0)
    echo "Client $clientId is already a club member";
   else
   {
    PageAdm::db()->insertValues($table, array('client_id' => $clientId));
    $count = PageAdm::db()->queryField($table, 'count(*)', 'client_id=' . $clientId);
    if ($count == 1)
     echo 'OK';
    else
     echo "Error inserting member $clientId record to the database: " . DB::lastQuery();
   }
   break;

  case 'uploadImage' :
   if (!WClient::uploadImage($clientId))
    return Base::addError('Error uploading an image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'loadImageFromURI' :
   $uri = HTTP::param('uri');
   if (WClient::uploadImageFromURI($clientId, $uri))
     echo 'OK';
    else
     echo "Error loading an image for client $clientId to the database: " . DB::lastQuery();
   break;

  case 'clearImage' :
   if (!WClient::clearImage($clientId))
    echo 'Error clearing an image';
   else
    echo 'OK';// . DB::lastQuery();
   break;

  case 'changeNote' :
   PageAdm::changeText($table, 'id=' . $clientId, 'client note', 'note');
   break;

  case 'changeFile' :
   PageAdm::changeText($table, 'id=' . $clientId, 'client file', 'file');
   break;

  case 'createMessage' :
   $_REQUEST['client'] = Base::index();
   PageAdm::processResult(WMessage::actionSendToClient());
   break;

  case 'deleteMessage' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity(WMessage::TABLE_MAIL, 'message', $id);
   break;

  case 'hideMessage' :
   PageAdm::hideEntity(WMessage::TABLE_MAIL, 'message');
   break;

  case 'changeWork' :
   $field = HTTP::param('field');
   $value = HTTP::param('value', '');
   $tableWork = 'biz_client_work';
   $where = array('client_id' => $clientId, 'mode' => DB::str($field));
   if ($value == '1')
    PageAdm::db()->mergeFields($tableWork, array('mode' => 'mode'), $where);
   else
    PageAdm::db()->deleteRecords($tableWork, $where);
   echo 'OK';
   break;

  case 'changeLang' :
   $field = HTTP::param('field');
   $value = HTTP::param('value', '');
   $tableLang = 'biz_client_work_lang';
   $where = array('client_id' => $clientId, 'abc_id' => DB::str($field));
   if ($value == '1')
    PageAdm::db()->mergeFields($tableLang, array('abc_id' => 'abc_id'), $where);
   else
    PageAdm::db()->deleteRecords($tableLang, $where);
   echo 'OK';
   //echo DB::lastQuery();
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  self::$me = new WClient(Base::index());
  if (!self::$me || !self::$me->getId())
   return false;
  $isHoster = WClient::me()->isHoster();
  if (array_key_exists('act', $_REQUEST))
  {
   if (!$isHoster || self::processAct($_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/base64.js"></script>
<?php PageAdm::instance()->showTitle(); ?>
<style>
td.text { padding:5px; }
td.highlight {font-weight:bold;}
#newmsg input[type="text"] {width:100%;border:0;}
#newmsg textarea {width:100%;margin:0;border:0;padding:0;}
#newmsg div {text-align:right;}
:focus { outline:none; }
</style>
<?php if ($isHoster) { ?>
<script>
var entity='the current client';
function becomeMember()
{
 if(!confirm('Make the client a club member?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=becomeMember',false);
 req.send(null);
 if (req.status!=200)
  return alert('Error making the client a club member');
 if(req.responseText!='OK')
  return alert(req.responseText);
 document.location.reload(true);
}
function loadImageFromURI()
{
 var uri=prompt('Input the URI of a picture');
 if(!uri)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=loadImageFromURI&uri='+encodeURIComponent(uri),false);
 req.send(null);
 var error='Error loading an image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function clearImage()
{
 if(!confirm('Clear image?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=clearImage',false);
 req.send(null);
 var error='Error clearing an image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
var src=0;
function openMessage(id)
{
 src=id;
 var subj=el("newsubj");
 var text=el("newtext");
 if(id)
 {
  var s=el('row-'+id).children[4].innerHTML;
  subj.value=(s.substr(0,4)=='Re: ')?s:('Re: '+s);
 }
 else
  subj.value="Attention!";
 text.value='<?php echo addslashes(self::$me->getName()); ?>, ';
 el("newmsg").style.display="";
 (id?text:subj).focus();
}
function sendMessage()
{
 var subj=el('newsubj').value.trim();
 if(!subj.length)
  return alert('Input a subject')||el('newsubj').focus();
 var text=el('newtext').value.trim();
 if(!text.length)
  return alert('Input a message')||el('newtext').focus();
 var req=new XMLHttpRequest();
 var params='act=createMessage';
 if(src)
  params+='&src='+src;
 params+='&subject='+encodeURIComponent(Base64.encode(subj));
 params+='&message='+encodeURIComponent(Base64.encode(text));
 req.open("GET",A.makeURI(params),false);
 req.send(null);
 var error='Error sending a message to a client'
 if ((req.status!=200)||!parseInt(req.responseText))
  alert(error+' '+req.responseText);
 else
  document.location.reload(true);//document.location='msg-'+req.responseText+'/';
}
function deleteMessage(id)
{
 A.deleteEntity(id,'message','deleteMessage','row-'+id);
}
function hideMessage(button,id,hide)
{
 A.hideEntity(button,id,hide,'message','hideMessage');
}
</script>
<?php } ?>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="act" />
<table class="main" width="100%">
<colgroup><col width="20%"><col width="40%"><col width="40%"></colgroup>
<tr><th>General:</th><th>Note:</th><?php if ($isHoster) { ?><th>File:</th><?php } ?></tr>
<tr><td>
<table class="main" width="100%">
<tr><th><b>Name:</b></th><td><?php echo self::$me->getName();?></td></tr>
<tr><th><b>E-mail:</b></th><td><?php echo self::$me->getEmail();?></td></tr>
<?php if ($isHoster) { ?>
<tr><th><b>Pass:</b></th><td><?php echo self::$me->getPass();?></td></tr>
<?php } ?>
<tr><th><b>Created:</b></th><td class="center"><?php echo self::$me->getCreated();?></td></tr>
<tr><th><b>Visited:</b></th><td class="center"><?php echo self::$me->getVisited();?></td></tr>
<tr><th><b>Locked:</b></th><td class="center"><?php echo self::$me->isLocked() ? 'Y' : '';?></td></tr>
<?php if ($isHoster) { ?>
<tr><th><b>Worker:</b></th><td class="center"><?php echo self::$me->isWorker() ? 'Y' : '';?></td></tr>
<?php } ?>
<tr><th><b>Gender:</b></th><td class="center"><?php echo self::$me->getGender();?></td></tr>
<tr><th><b>Birthday:</b></th><td class="center"><?php echo self::$me->getBirthday();?></td></tr>
<?php if ($isHoster) { ?>
<tr><th><b>Member:</b></th><th><?php
if (self::$me->isMember())
 echo '<a style="display:block;text-align:center;" href="mbr-' . self::$me->getId() . '/">&nbsp;member&nbsp;</a>';
else
 echo '<input type="button" value="Become" onclick="becomeMember()" />';
?></th></tr>
<?php } ?>
<tr><td colspan="2"><table class="main" width="100%">
<tr>
<th rowspan="4"><img src="img/clt-<?php echo self::$me->getId(); ?>.jpg" width="128" height="128"/></th>
<th><img src="img/clt-<?php echo self::$me->getId(); ?>.png" width="48" height="48"/></th>
</tr>
<?php
$pic = PageAdm::db()->queryFields(WClient::TABLE_CLIENT,
  'pic_filename,pic_width,pic_height,pic_size,photo_width,photo_height,photo_size',
  'id=' . self::$me->getId());
echo '<tr><td>' . Util::nvls(htmlspecialchars($pic[0]),'&nbsp;') . "</td></tr>\n";
echo '<tr><td>' . ($pic[3] ? ($pic[1] . 'x' . $pic[2] . '; ' . number_format($pic[3])) : '&nbsp;') . "</td></tr>\n";
echo '<tr><td>' . ($pic[6] ? ($pic[4] . 'x' . $pic[5] . '; ' . number_format($pic[6])) : '&nbsp;') . "</td></tr>\n";
?>
<?php if ($isHoster) { ?>
<tr>
<th><input type='button' value='Load from URI' onclick='loadImageFromURI()'/></th>
<th><input type='button' value='Clear' onclick='clearImage()'/></th>
</tr>
<tr><td colspan="2">
<form method='post' enctype='multipart/form-data'>
<input type='hidden' name='act' value='uploadImage'/>
<input type='file' name='image' size='1' onchange='submit()'/>
</form>
</td></tr>
<?php } ?>
</table></td></tr>
</table>
</td>
<td class="text">
<?php
if ($isHoster)
 PageAdm::echoTextArea('note', self::$me->getNote(), "member note", 'changeNote', 'note', null, 20);
else
 echo str_replace("\n", "<br/>\n", htmlspecialchars(self::$me->getNote()));
?>
</td>
<?php if ($isHoster) { ?>
<td class="text"><?php PageAdm::echoTextArea('file', self::$me->getFile(), "member file", 'changeFile', 'file', null, 20);?></td>
<?php } ?>
</tr></table>
<?php
if ($isHoster && self::$me->isWorker())
{
 $count = count(PageAdm::instance()->getModes());
 $modes = array_keys(PageAdm::instance()->getModes());
 $titles = array_values(PageAdm::instance()->getModes());
?>
<hr/>
<table class="main" width="100%">
<caption>Worker access</caption>
<colgroup><?php
for ($i = 1; $i < $count; $i++)
 echo '<col width="50">';
?></colgroup>
<tr><?php
for ($i = 1; $i < $count; $i++)
 echo "<th>{$titles[$i]}</th>";
?></tr>
<tr><?php
for ($i = 1; $i < $count; $i++)
{
 $class = PageAdm::checkWorkerMode($modes[$i], self::$me->getId()) ? ' class="checked"' : '';
 echo "<td$class onclick='A.changeFlag(this,0,entity,\"{$modes[$i]}\",\"changeWork\")'>&nbsp;</td>";
}
?></tr>
</table>
<hr/>
<table class="main" width="100%">
<caption>Worker languages</caption>
<colgroup><?php
$langs = Lang::map();
for ($i = 0; $i < count($langs); $i++)
 echo '<col width="100">';
?></colgroup>
<tr><?php
foreach ($langs as $lang)
 echo "<th>{$lang->title()}</th>";
?></tr>
<tr><?php
foreach ($langs as $key => $lang)
{
 $class = PageAdm::checkWorkerLang($key, self::$me->getId()) ? ' class="checked"' : '';
 echo "<td$class onclick='A.changeFlag(this,0,entity,\"$key\",\"changeLang\")'>&nbsp;</td>";
}
?></tr>
</table>
<?php } ?>
<?php if ($isHoster) { ?>
<hr/>
<table class="main" width="100%">
<caption>Messages</caption>
<colgroup><col width="50"><col width="50"><col width="200"><col width="100"><col>
<col width="10"><col width="50"><col width="50"><col width="50"></colgroup>
<tr><th>Id</th><th>Src</th><th>Sent</th><th>Direction</th><th colspan='3'>Subject</th>
<th colspan='2'><input type='button' value='Write to client' onclick='openMessage(0);'/></th>
</tr>
<tr id="newmsg" style="display:none"><td colspan="9">
<div>
<input type='text' id="newsubj" placeholder="Subject..."/>
</div>
<textarea rows="10" id="newtext"><?php echo self::$me->getName();?>, </textarea>
<div>
<input type='button' value='Send' onclick='sendMessage()'/>
<input type='button' value='Cancel' onclick='el("newmsg").style.display="none"'/>
</div>
</td></tr>
<?php
 $fields = 'id,src_id,sent,dir,subject,hidden';
 $fields .= ',(select count(*) from ' . WMessage::TABLE_MAIL . ' where src_id=a.id)';
 $msgs = DB::getDB()->queryRecords(WMessage::TABLE_MAIL . ' a', $fields, 'client_id=' . self::$me->getId(), 'id desc');
 if ($msgs)
  foreach ($msgs as $row)
  {
   $id = $row[0];
   $src = $row[1];
   $sent = $row[2];
   $dir = (($row[3] == 'i') ? 'Incoming' : 'Outgoing');
   $subject = htmlspecialchars($row[4]);
   $hidden = $row[5] != '';
   $ans = $row[6];

   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th><a href=\"msg-$id/\">" . $id . "</a></th>\n";
   if($src)
    echo "<th><a href=\"msg-$src/\">" . $src . "</a></th>\n";
   else
    echo "<th></th>\n";
   echo "<th>" . $sent . "</th>\n";
   echo "<th>" . $dir . "</th>\n";
   if ($row[3] == 'i')
    echo "<td colspan='3' class='left'>" . $subject . "</td>\n";
   else
   {
    if (!$ans)
     echo "<td colspan='2' class='highlight left'>" . $subject . "</td>\n";
    else
     echo "<td class='left'>" . $subject . "</td>\n<th>(" . $ans . ")</th>\n";
    echo "<th><input type='button' value='Answer' onclick='openMessage($id)'/></th>\n";
   }
   $value = $hidden ? 'Show' : 'Hide';
   $arg = $hidden ? 'false' : 'true';
   echo "<th><input type='button' value='$value' onclick='hideMessage(this,$id,$arg)'/></th>\n";
   echo "<th><input type='button' value='Delete' onclick='deleteMessage($id)'/></th>\n";
   echo "</tr>";
  }
 else
  echo '<tr><th colspan="9">No data</th></tr>';
?>
</table>
<?php } ?>
<hr/>
<table class="main">
<caption>History</caption>
<tr><th>Time</th><th>Centre</th></tr>
<?php
 $rows = DB::getDB()->queryRecords('com_history a', 'ts,centre_id', 'client_id=' . self::$me->getId(), 'ts desc');
 if ($rows)
  foreach ($rows as $row)
   echo '<tr><th>' . $row[0] . '</th><td>' . $row[1] . '</td></tr>';
 else
  echo '<tr><th colspan="2">No data</th></tr>';
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
