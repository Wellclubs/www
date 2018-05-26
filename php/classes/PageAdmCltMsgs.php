<?php

/**
 * Description of PageAdmCltMsgs
 */
class PageAdmCltMsgs
{
 private static function processAct($act)
 {
  switch ($act)
  {
  case 'createMessage' :
   PageAdm::processResult(WMessage::actionSendToClient());
   break;

  case 'deleteMessage' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity(WMessage::TABLE_MAIL, 'message', $id);
   break;

  case 'hideMessage' :
   PageAdm::hideEntity(WMessage::TABLE_MAIL, 'message');
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
    return true;
  }
?><!doctype html>
<html>
<head>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/base64.js"></script>
<?php PageAdm::instance()->showTitle(); ?>
<style>
pre {margin:10px;}
td.line { padding:0 5px; }
#newmsg input[type="text"] {width:100%;border:0;}
#newmsg textarea {width:100%;margin:0;border:0;padding:0;}
#newmsg div {text-align:right;}
:focus { outline:none; }
</style>
<script>
var entity='message';
var src=0;
var client=0;
function openMessage(id)
{
 src=id;
 var subj=el("newsubj");
 var text=el("newtext");
 var row=el('row-'+id);
 client=row.children[3].children[0].innerHTML;
 var s=row.children[5].innerHTML;
 subj.value=(s.substr(0,4)=='Re: ')?s:('Re: '+s);
 text.value=row.children[4].innerHTML+', ';
 el("newmsg").style.display="";
 text.focus();
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
 params+='&src='+src;
 params+='&client='+client;
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
function hideMessage(id)
{
 A.hideEntity(null,id,true,'message','hideMessage');
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<table class="main" width="100%">
<colgroup><col width="50"><col width="50"><col width="200"><col width="50"><col width="200"><col>
<col width="10"><col width="50"><col width="50"><col width="50"></colgroup>
<tr><th>Id</th><th>Src</th><th>Sent</th><th colspan='2'>Client</th><th>Subject</th><th colspan='3'></th></tr>
<tr id="newmsg" style="display:none"><td colspan="9">
<div>
<input type='text' id="newsubj" placeholder="Subject..."/>
</div>
<textarea rows="10" id="newtext"></textarea>
<div>
<input type='button' value='Send' onclick='sendMessage()'/>
<input type='button' value='Cancel' onclick='el("newmsg").style.display="none"'/>
</div>
</td></tr>
<?php
 $fields = 'id,src_id,client_id,sent,subject,message';
 $fields .= ',(select trim(concat(firstname,\' \',lastname)) from ' . WClient::TABLE_CLIENT . ' where id=a.client_id)';
 $where = 'dir=\'o\' and hidden is null and not exists(select null from ' . WMessage::TABLE_MAIL . ' where src_id=a.id)';
 $msgs = DB::getDB()->queryRecords(WMessage::TABLE_MAIL . ' a', $fields, $where, 'id desc');
 if ($msgs)
  foreach ($msgs as $row)
  {
   $id = $row[0];
   $src = $row[1];
   $clientId = $row[2];
   $sent = $row[3];
   $subject = htmlspecialchars($row[4]);
   $message = htmlspecialchars($row[5]);
   $clientName = htmlspecialchars($row[6]);

   echo "<tr id='row-$id'>\n";
   echo "<th><a href=\"msg-$id/\">" . $id . "</a></th>\n";
   if($src)
    echo "<th><a href=\"msg-$src/\">" . $src . "</a></th>\n";
   else
    echo "<th></th>\n";
   echo "<th>" . $sent . "</th>\n";
   echo "<th><a href=\"clt-$clientId/\">" . $clientId . "</a></th>\n";
   echo "<td class='left'>" . $clientName . "</td>\n";
   echo "<td class='left'>" . $subject . "</td>\n";
   echo "<th><input type='button' value='Answer' onclick='openMessage($id)'/></th>\n";
   echo "<th><input type='button' value='Hide' onclick='hideMessage($id)'/></th>\n";
   echo "<th><input type='button' value='Delete' onclick='deleteMessage($id)'/></th>\n";
   echo "</tr>";

   echo '<tr><td colspan="9"><pre>' . $message . "</pre></td></tr>\n";
  }
 else
  echo '<tr><th colspan="9">No data</th></tr>';
?>
</table>
</body>
</html>
<?php
  return true;
 }
}
?>
