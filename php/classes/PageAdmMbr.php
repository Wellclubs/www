<?php

/**
 * Description of PageAdmMbr
 */
class PageAdmMbr
{
 private static $me = null;
 
 private static function processAct($clientId, $act)
 {
  $table = WMember::TABLE_MEMBER;
  $entity = 'member';
  switch ($act)
  {
  case 'changeBrandLimit' :
   $_REQUEST['id'] = $clientId;
   PageAdm::changeField($table, $entity, 'brand_limit', 'value');
   break;

  case 'changeFile' :
   PageAdm::changeText($table, 'client_id=' . $clientId, 'member file', 'file');
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  self::$me = new WMember(Base::index());
  if (!self::$me || !self::$me->getId())
   return false;
  $clientId = self::$me->getId();
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($clientId, $_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
td.text { padding:5px; }
</style>
<script>
function changeBrandLimit(node)
{
 var newValue=prompt('Input a new brand limit value:');
 if(newValue==null)
  return;
 if(newValue.length)
 {
  newValue=parseInt(newValue);
  if(isNaN(newValue))
   return alert('Invalid number');
 }
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeBrandLimit&value='+newValue),false);
 req.send(null);
 var error='Error changing the member brand limit on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="act" />
<table class="main" width="100%">
<colgroup><col width="40%"><col width="60%"></colgroup>
<tr><th>General:</th><th>File:</th></tr>
<tr><td>
<table class="main" width="100%">
<tr><th><b>Client:</b></th><th><?php echo "<a href='clt-$clientId/'>" . PageAdm::makeEntityText($clientId, self::$me->getClient()->getName()) . '</a>';?></th></tr>
<tr><th><b>Entered:</b></th><th><?php echo self::$me->getEntered();?></th></tr>
<tr><th><b>Brand limit:</b></th><td id='type' align="center" colspan="2" onclick="changeBrandLimit(this)"><?php echo self::$me->getBrandLimit();?></td></tr>
<tr><th><b>Brand count:</b></th><th><?php echo "<a href='bnds/?mbr=$clientId'>" . self::$me->getBrandCount() . '</a>';?></th></tr>
<tr><th><b>Centre count:</b></th><th><?php echo "<a href='ctrs/?mbr=$clientId'>" . self::$me->getActiveCentreCount() . ' / ' . self::$me->getCentreCount() . '</a>';?></th></tr>
</table>
</td>
<td class="text"><?php PageAdm::echoTextArea('file', self::$me->getFile(), "member file", 'changeFile', 'file', null, 5);?></td>
</tr></table>
</form>
</body>
</html>
<?php
  return true;
 }
}

?>
