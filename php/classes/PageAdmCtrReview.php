<?php

/**
 * Description of PageAdmCtrReview
 */
class PageAdmCtrReview
{
 const TABLE_REVIEW = 'com_review';
 const TABLE_PRC = 'com_review_prc';
 const TABLE_REV_CAVIL = 'com_review_cavil';
 const TABLE_COMMENT = 'com_review_comment';
 const TABLE_CMT_CAVIL = 'com_review_comment_cavil';

 private static function processAct($act)
 {
  $db = PageAdm::db();
  $centreId = WCentre::id();
  $tableRev = self::TABLE_REVIEW;
  $entityRev = 'review';
  $tablePrc = self::TABLE_PRC;
  $entityPrc = 'proc. rate';
  $tableRevCvl = self::TABLE_REV_CAVIL;
  $entityRevCvl = 'review cavil';
  $tableCmt = self::TABLE_COMMENT;
  $entityCmt = 'comment';
  $tableCmtCvl = self::TABLE_CMT_CAVIL;
  $entityCmtCvl = 'comment cavil';
  switch ($act)
  {
  case 'createRevEntity' :
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $id = $db->queryField($tableRev, 'ifnull(max(id),0)+1');
   $values = array('id' => $id, 'centre_id' => $centreId, 'client_id' => $clientId, 'client_name' => DB::str($clientName));
   if (!strlen($clientName))
    echo "Client $clientId does not exist";
   else if (!$db->insertValues($tableRev, $values))
    echo "Error adding the new $entityRev record to the database: " . DB::lastQuery();
   else
   {
    WCentre::calcRate($db, $centreId);
    echo 'OK';
   }
   break;

  case 'deleteRevEntity' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity($tableRev, $entityRev, $id);
   WCentre::calcRate($db, $centreId);
   break;

  case 'changeRevClient' :
   $id = intval(HTTP::param('id'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $values = array('client_id' => $clientId, 'client_name' => DB::str($clientName));
   if ($clientId && !strlen($clientName))
    echo "Client $clientId does not exist";
   else if ($clientId && !$db->modifyFields($tableRev, $values, 'id=' . $id))
    echo "Error changing the $entityRev $id client id in the database: " . DB::lastQuery();
   else if (!$clientId && !$db->modifyFields($tableRev, array('client_id' => 'null'), 'id=' . $id))
    echo "Error clearing the $entityRev $id client id in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeRevRate' :
   if (array_key_exists('total', $_REQUEST))
   {
    PageAdm::changeField(self::TABLE_REVIEW, $entityRev, 'rate_total', 'total');
    WCentre::calcRate($db, $centreId);
   }
   else if (array_key_exists('ambie', $_REQUEST))
    PageAdm::changeField(self::TABLE_REVIEW, $entityRev, 'rate_ambie', 'ambie');
   else if (array_key_exists('clean', $_REQUEST))
    PageAdm::changeField(self::TABLE_REVIEW, $entityRev, 'rate_clean', 'clean');
   else if (array_key_exists('staff', $_REQUEST))
    PageAdm::changeField(self::TABLE_REVIEW, $entityRev, 'rate_staff', 'staff');
   else if (array_key_exists('value', $_REQUEST))
    PageAdm::changeField(self::TABLE_REVIEW, $entityRev, 'rate_value', 'value');
   else
    echo 'Undefined rate field';
   break;

  case 'changeRevFlag' :
   PageAdm::changeFlag($tableRev, $entityRev);
   if (HTTP::param('field') == 'signaled')
    WCentre::calcRate(PageAdm::db(), $centreId);
   break;

  case 'changeRevText' :
   $id = HTTP::param('id');
   PageAdm::changeText($tableRev, array('id' => $id), $entityRev);
   break;

  case 'createPrcEntity' :
   $revId = intval(HTTP::param('rev'));
   $prcId = intval(HTTP::param('prc'));
   $rate = intval(HTTP::param('rate'));
   $values = array('review_id' => $revId, 'prc_id' => $prcId, 'rate' => $rate);
   if (($rate < 1) || ($rate > 5))
    echo "Rate $rate is out of range 1..5";
   else if (!$db->queryField('biz_menu_prc', 'id', 'id=' . $prcId))
    echo "Procedure $prcId does not exist";
   else if (!$db->queryField(WService::TABLE_SRV_PRC, '1', 'prc_id=' . $prcId . ' and srv_id in (select id from ' . WService::TABLE_SRV . ' where centre_id=' . $centreId . ')'))
    echo "Procedure $prcId does not depend to the current centre";
   else if ($db->queryField($tablePrc, '1', 'review_id=' . $revId . ' and prc_id=' . $prcId))
    echo "Procedure $prcId is already rated in the review $revId";
   else if (!$db->insertValues($tablePrc, $values))
    echo "Error adding the new $entityPrc record to the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'deletePrcEntity' :
   $rev = intval(HTTP::param('rev'));
   $prc = intval(HTTP::param('prc'));
   $where = "review_id=$rev and prc_id=$prc";
   PageAdm::db()->deleteRecords($tablePrc, $where);
   if (PageAdm::db()->queryField($tablePrc, 'count(*)', $where))
    echo "Error deleting the $entity $id record from the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changePrcRate' :
   $rev = intval(HTTP::param('rev'));
   $prc = intval(HTTP::param('prc'));
   $rate = intval(HTTP::param('rate'));
   $where = "review_id=$rev and prc_id=$prc";
   $db->modifyFields($tablePrc, array('rate' => $rate), $where);
   if ($db->queryField($tablePrc, 'rate', $where) == $rate)
    echo 'OK';
   else
    echo "Error changing $entityPrc $prc rate to '$rate': " . DB::lastQuery();
   break;

  case 'createRevCvlEntity' :
   $revId = intval(HTTP::param('rev'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $id = $db->queryField($tableRevCvl, 'ifnull(max(id),0)+1');
   $values = array('id' => $id, 'review_id' => $revId, 'client_id' => $clientId, 'client_name' => DB::str($clientName));
   if (!strlen($clientName))
    echo "Client $clientId does not exist";
   else if (!$db->insertValues($tableRevCvl, $values))
    echo "Error adding the new $entityRevCvl record to the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'deleteRevCvlEntity' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity($tableRevCvl, $entityRevCvl, $id);
   break;

  case 'changeRevCvlClient' :
   $id = intval(HTTP::param('id'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $values = array('client_id' => $clientId, 'client_name' => DB::str($clientName));
   if ($clientId && !strlen($clientName))
    echo "Client $clientId does not exist";
   else if ($clientId && !$db->modifyFields($tableRevCvl, $values, 'id=' . $id))
    echo "Error changing the $entityRevCvl $id client id in the database: " . DB::lastQuery();
   else if (!$clientId && !$db->modifyFields($tableRevCvl, array('client_id' => 'null'), 'id=' . $id))
    echo "Error clearing the $entityRevCvl $id client id in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeRevCvlFlag' :
   PageAdm::changeFlag($tableRevCvl, $entityRevCvl);
   break;

  case 'changeRevCvlText' :
   $id = HTTP::param('id');
   PageAdm::changeText($tableRevCvl, array('id' => $id), $entityRevCvl);
   break;

  case 'createCmtEntity' :
   $revId = intval(HTTP::param('rev'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $id = $db->queryField($tableCmt, 'ifnull(max(id),0)+1');
   $values = array('id' => $id, 'review_id' => $revId, 'client_id' => $clientId, 'client_name' => DB::str($clientName));
   if (!strlen($clientName))
    echo "Client $clientId does not exist";
   else if (!$db->insertValues($tableCmt, $values))
    echo "Error adding the new $entityCmt record to the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'deleteCmtEntity' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity($tableCmt, $entityCmt, $id);
   break;

  case 'changeCmtClient' :
   $id = intval(HTTP::param('id'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $values = array('client_id' => $clientId, 'client_name' => DB::str($clientName));
   if ($clientId && !strlen($clientName))
    echo "Client $clientId does not exist";
   else if ($clientId && !$db->modifyFields($tableCmt, $values, 'id=' . $id))
    echo "Error changing the $entityCmt $id client id in the database: " . DB::lastQuery();
   else if (!$clientId && !$db->modifyFields($tableCmt, array('client_id' => 'null'), 'id=' . $id))
    echo "Error clearing the $entityCmt $id client id in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeCmtFlag' :
   PageAdm::changeFlag($tableCmt, $entityCmt);
   break;

  case 'changeCmtText' :
   $id = HTTP::param('id');
   PageAdm::changeText($tableCmt, array('id' => $id), $entityCmt);
   break;

  case 'createCmtCvlEntity' :
   $cmtId = intval(HTTP::param('cmt'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $id = $db->queryField($tableCmtCvl, 'ifnull(max(id),0)+1');
   $values = array('id' => $id, 'comment_id' => $cmtId, 'client_id' => $clientId, 'client_name' => DB::str($clientName));
   if (!strlen($clientName))
    echo "Client $clientId does not exist";
   else if (!$db->insertValues($tableCmtCvl, $values))
    echo "Error adding the new $entityCmtCvl record to the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'deleteCmtCvlEntity' :
   $id = intval(HTTP::param('id'));
   PageAdm::deleteEntity($tableCmtCvl, $entityCmtCvl, $id);
   break;

  case 'changeCmtCvlClient' :
   $id = intval(HTTP::param('id'));
   $clientId = intval(HTTP::param('client'));
   $clientName = WClient::getClientName($clientId);
   $values = array('client_id' => $clientId, 'client_name' => DB::str($clientName));
   if ($clientId && !strlen($clientName))
    echo "Client $clientId does not exist";
   else if ($clientId && !$db->modifyFields($tableCmtCvl, $values, 'id=' . $id))
    echo "Error changing the $entityCmtCvl $id client id in the database: " . DB::lastQuery();
   else if (!$clientId && !$db->modifyFields($tableCmtCvl, array('client_id' => 'null'), 'id=' . $id))
    echo "Error clearing the $entityCmtCvl $id client id in the database: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeCmtCvlFlag' :
   PageAdm::changeFlag($tableCmtCvl, $entityCmtCvl);
   break;

  case 'changeCmtCvlText' :
   $id = HTTP::param('id');
   PageAdm::changeText($tableCmtCvl, array('id' => $id), $entityCmtCvl);
   break;

  default :
   echo "Unsupported action: '$act'";
  }

  return true;
 }

 public static function showPage()
 {
  if (!WCentre::initCurrent(Base::index(), true))
   return false;
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
  $centreId = WCentre::id();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
</style>
<script>
var entityRev='review';
var entityPrc='proc. rate';
var entityRevCvl='review cavil';
var entityCmt='comment';
var entityCmtCvl='comment cavil';
function createRevEntity()
{
 var client=prompt('Input an author id');
 if(client==null)
  return;
 client=parseInt(client);
 if(client<1)
  return alert('Client id must be a positive integer value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createRevEntity&client='+client),false);
 req.send(null);
 var error='Error adding the '+entityRev+' of client '+client+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteRevEntity(id)
{
 A.deleteEntity(id,entityRev,'deleteRevEntity');
}
function changeRevClient(node,id)
{
 if(A.changeField(node,id,entityRev,'client','changeRevClient',true))
  document.location.reload(true);
}
function changeRevRate(node,id,index)
{
 var field=['total','ambie','clean','staff','value'][index];
 A.changeField(node,id,entityRev,field,'changeRevRate');
}
function changeRevFlag(node,id,index)
{
 var field=['notifier','verified','signaled'][index];
 A.changeFlag(node,id,entityRev,field,'changeRevFlag')
}
function createPrcEntity(id)
{
 var prc=prompt('Input a procedure id');
 if(prc==null)
  return;
 prc=parseInt(prc);
 if(prc<1)
  return alert('Procedure id must be a positive integer value');
 var rate=prompt('Input a rate for the '+entityPrc+' '+prc+' (1..5)');
 if(rate==null)
  return;
 rate=parseInt(rate);
 if((rate<1)||(rate>5))
  return alert('Rate value must be between 1 and 5');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createPrcEntity&rev='+id+'&prc='+prc+'&rate='+rate),false);
 req.send(null);
 var error='Error adding the '+entityPrc+' '+prc+' to the review '+id+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deletePrcEntity(rev,prc)
{
 if(!confirm('Delete the '+entityPrc+' '+prc+' from the '+entityRev+' '+rev+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deletePrcEntity&rev='+rev+'&prc='+prc),false);
 req.send(null);
 var error='Error deleting the '+entityPrc+' '+prc+' from the '+entityRev+' '+rev+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changePrcRate(node,rev,prc)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new rate for the '+entityPrc+' '+prc+' in the '+entityRev+' '+rev+':',oldValue);
 if((newValue===null)||(newValue==='')||(newValue==oldValue))
  return;
 var params='act=changePrcRate&rev='+rev+'&prc='+prc+'&rate='+newValue;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI(params),false);
 req.send(null);
 var error='Error changing the '+entityPrc+' '+prc+' in the '+entityRev+' '+rev+' rate on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function createRevCvlEntity(rev)
{
 var client=prompt('Input an author id');
 if(client==null)
  return;
 client=parseInt(client);
 if(client<1)
  return alert('Client id must be a positive integer value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createRevCvlEntity&rev='+rev+'&client='+client),false);
 req.send(null);
 var error='Error adding the '+entityRevCvl+' of client '+client+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteRevCvlEntity(id)
{
 A.deleteEntity(id,entityRevCvl,'deleteRevCvlEntity');
}
function changeRevCvlClient(node,id)
{
 if(A.changeField(node,id,entityRevCvl,'client','changeRevCvlClient',true))
  document.location.reload(true);
}
function changeRevCvlFlag(node,id,index)
{
 var field=['notifier','violation','falsehood'][index];
 A.changeFlag(node,id,entityRevCvl,field,'changeRevCvlFlag')
}
function createCmtEntity(rev)
{
 var client=prompt('Input an author id');
 if(client==null)
  return;
 client=parseInt(client);
 if(client<1)
  return alert('Client id must be a positive integer value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createCmtEntity&rev='+rev+'&client='+client),false);
 req.send(null);
 var error='Error adding the '+entityCmt+' of client '+client+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteCmtEntity(id)
{
 A.deleteEntity(id,entityCmt,'deleteCmtEntity');
}
function changeCmtClient(node,id)
{
 if(A.changeField(node,id,entityCmt,'client','changeCmtClient',true))
  document.location.reload(true);
}
function changeCmtFlag(node,id,index)
{
 var field=['notifier','verified','signaled'][index];
 A.changeFlag(node,id,entityCmt,field,'changeCmtFlag')
}
function createCmtCvlEntity(cmt)
{
 var client=prompt('Input an author id');
 if(client==null)
  return;
 client=parseInt(client);
 if(client<1)
  return alert('Client id must be a positive integer value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=createCmtCvlEntity&cmt='+cmt+'&client='+client),false);
 req.send(null);
 var error='Error adding the '+entityCmtCvl+' of client '+client+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteCmtCvlEntity(id)
{
 A.deleteEntity(id,entityCmtCvl,'deleteCmtCvlEntity');
}
function changeCmtCvlClient(node,id)
{
 if(A.changeField(node,id,entityCmtCvl,'client','changeCmtCvlClient',true))
  document.location.reload(true);
}
function changeCmtCvlFlag(node,id,index)
{
 var field=['notifier','violation','falsehood'][index];
 A.changeFlag(node,id,entityCmtCvl,field,'changeCmtCvlFlag')
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo "<a href='ctr-$centreId'>" . htmlspecialchars(PageAdm::title()) . '</a>';?></h1>
<table class="main block" xwidth="100%">
<tr>
<th width='50'>Id</th>
<th width='200'>Written</th>
<th width='200'>Author</th>
<th width='50'>Total</th>
<th width='50'>Ambie.</th>
<th width='50'>Clean.</th>
<th width='50'>Staff</th>
<th width='50'>Value</th>
<th width='50'>Notifier</th>
<th width='50'>Verified</th>
<th width='50'>Signaled</th>
<th width='1'><input type="button" value="Create new review" onclick="createRevEntity()"/></th>
</tr>
<?php
 $fields = 'id,written,client_id,client_name,' .
   'rate_total,rate_ambie,rate_clean,rate_staff,rate_value,' .
   'text,notifier,verified,signaled';
 $reviews = PageAdm::db()->queryMatrix(self::TABLE_REVIEW, $fields, 'centre_id=' . $centreId, 'id');
 if ($reviews)
 {
  foreach ($reviews as $revId => $review)
  {
   $revText = $review['text'];
   $clientId = $review['client_id'];
   $clientName = $review['client_name'];
   $client = htmlspecialchars($clientId ? PageAdm::makeEntityText($clientId, $clientName) : $clientName);

   echo "<tr>\n";
   echo "<th rowspan='5'>$revId</th>\n";
   echo '<th>' . $review['written'] . "</th>\n";
   echo "<td onclick='changeRevClient(this,$revId)'>$client</td>\n";
   echo "<td onclick='changeRevRate(this,$revId,0)' class='center'>" . $review['rate_total'] . "</td>\n";
   echo "<td onclick='changeRevRate(this,$revId,1)' class='center'>" . $review['rate_ambie'] . "</td>\n";
   echo "<td onclick='changeRevRate(this,$revId,2)' class='center'>" . $review['rate_clean'] . "</td>\n";
   echo "<td onclick='changeRevRate(this,$revId,3)' class='center'>" . $review['rate_staff'] . "</td>\n";
   echo "<td onclick='changeRevRate(this,$revId,4)' class='center'>" . $review['rate_value'] . "</td>\n";
   echo "<td" . (!!$review['notifier'] ? " class='checked'" : null) . " onclick='changeRevFlag(this,$revId,0)'></td>\n";
   echo "<td" . (!!$review['verified'] ? " class='checked'" : null) . " onclick='changeRevFlag(this,$revId,1)'></td>\n";
   echo "<td" . (!!$review['signaled'] ? " class='checked'" : null) . " onclick='changeRevFlag(this,$revId,2)'></td>\n";
   echo "<th><input type='button' value='Delete review $revId' onclick='deleteRevEntity($revId)'/></th>\n";
   echo "</tr>\n";

   echo "<tr><td colspan='11'>\n";
   self::showPrcRates($revId);
   echo "</td></tr>\n";

   echo "<tr>\n";
   echo "<td colspan='11'>\n";
   PageAdm::echoTextArea("rev-$revId", $revText, "review text", 'changeRevText', null, "id=$revId", 3);
   echo "</td>\n";
   echo "</tr>\n";

   echo "<tr><td colspan='11'>\n";
   self::showRevCavils($revId);
   echo "</td></tr>\n";

   echo "<tr><td colspan='11'>\n";
   self::showRevComments($revId);
   echo "</td></tr>\n";
  }
 }
 else
  echo '<tr><th colspan="12"><h3>No reviews written yet</h3></th></tr>' . "\n";
?></table>
</body>
</html>
<?php
  return true;
 }

 private static function showPrcRates($revId)
 {
  echo "<table style='margin:5px'>\n";
  echo "<caption>Procedure rate values</caption>\n";
  echo "<tr>\n";
  echo "<th width='50'>Id</th><th width='400'>Name</th><th width='50'>Rate</th>";
  echo "<th><input type='button' value='Add proc.' onclick='createPrcEntity($revId)'/></th>\n";
  echo "</tr>\n";
  $query = '(select prc_id' .
    ',(select serial from biz_menu_prc where id=a.prc_id)prc_nr' .
    ',(select name from biz_menu_prc where id=a.prc_id)prc_name' .
    ',(select cat_id from biz_menu_prc where id=a.prc_id)cat_id' .
    ',(select c.serial from biz_menu_prc b,biz_menu_cat c where b.id=a.prc_id and c.id=b.cat_id)cat_nr' .
    ',(select c.name from biz_menu_prc b,biz_menu_cat c where b.id=a.prc_id and c.id=b.cat_id)cat_name' .
    ',rate' .
    ' from ' . self::TABLE_PRC . ' a' .
    ' where review_id=' . $revId .
    ')a';
  $prcs = PageAdm::db()->queryMatrix($query, 'prc_id,prc_name,cat_id,cat_name,rate', null, 'cat_nr,cat_id,prc_nr,prc_id');
  if ($prcs)
  {
   $lastCatId = null;
   foreach ($prcs as $id => $prc)
   {
    $catId = $prc['cat_id'];
    if ($catId != $lastCatId)
    {
     echo "<tr><th></th><th colspan='3'>" . htmlspecialchars(PageAdm::makeEntityText($catId, $prc['cat_name'])) . "</th></tr>\n";
     $lastCatId = $catId;
    }
    echo "<tr>\n";
    echo "<th>$id</th>\n";
    echo "<th class='left'>" . htmlspecialchars($prc['prc_name']) . "</th>\n";
    echo "<td class='center' onclick='changePrcRate(this,$revId,$id)'>" . $prc['rate'] . "</td>\n";
    echo "<th><input type='button' value='Delete proc. $id' onclick='deletePrcEntity($revId,$id)'/></th>\n";
    echo "</tr>\n";
   }
  }
  else
   echo '<tr><th colspan="4"><h3>No procedures rated in this review</h3></th></tr>' . "\n";
  echo "</table>\n";
 }

 private static function showRevCavils($revId)
 {
  echo "<table style='float:right;margin:5px;'>\n";
  echo "<caption>Cavils to review $revId</caption>\n";
  echo "<tr>\n";
  echo "<th width='50'>Id</th>\n";
  echo "<th width='200'>Written</th>\n";
  echo "<th width='200'>Author</th>\n";
  echo "<th width='50'>Notifier</th>";
  echo "<th width='50'>Violation</th>";
  echo "<th width='50'>Falsehood</th>";
  echo "<th><input type='button' value='Add cavil' onclick='createRevCvlEntity($revId)'/></th>\n";
  echo "</tr>\n";
  $fields = 'id,written,client_id,client_name,text,notifier,violation,falsehood';
  $cvls = PageAdm::db()->queryMatrix(self::TABLE_REV_CAVIL, $fields, 'review_id=' . $revId, 'id');
  if ($cvls)
  {
   foreach ($cvls as $cvlId => $cavil)
   {
    $cvlText = $cavil['text'];
    $clientId = $cavil['client_id'];
    $clientName = $cavil['client_name'];
    $client = htmlspecialchars($clientId ? PageAdm::makeEntityText($clientId, $clientName) : $clientName);

    echo "<tr>\n";
    echo "<th rowspan='2'>$cvlId</th>\n";
    echo '<th>' . $cavil['written'] . "</th>\n";
    echo "<td onclick='changeRevCvlClient(this,$cvlId)'>$client</td>\n";
    echo "<td" . (!!$cavil['notifier'] ? " class='checked'" : null) . " onclick='changeRevCvlFlag(this,$cvlId,0)'></td>\n";
    echo "<td" . (!!$cavil['violation'] ? " class='checked'" : null) . " onclick='changeRevCvlFlag(this,$cvlId,1)'></td>\n";
    echo "<td" . (!!$cavil['falsehood'] ? " class='checked'" : null) . " onclick='changeRevCvlFlag(this,$cvlId,2)'></td>\n";
    echo "<th><input type='button' value='Delete cavil $cvlId' onclick='deleteRevCvlEntity($cvlId)'/></th>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "<td colspan='6'>\n";
    PageAdm::echoTextArea("rcvl-$cvlId", $cvlText, "cavil text", 'changeRevCvlText', null, "id=$cvlId", 3);
    echo "</td>\n";
    echo "</tr>\n";
   }
  }
  else
   echo '<tr><th colspan="7"><h3>No cavils added to this review</h3></th></tr>' . "\n";
  echo "</table>\n";
 }

 private static function showRevComments($revId)
 {
  echo "<table style='width:99%;margin:5px;'>\n";
  echo "<caption>Comments to review $revId</caption>\n";
  echo "<tr>\n";
  echo "<th width='50'>Id</th>\n";
  echo "<th width='200'>Written</th>\n";
  echo "<th width='200'>Author</th>\n";
  echo "<th width='50'>Notifier</th>";
  echo "<th width='50'>Verified</th>";
  echo "<th width='50'>Signaled</th>";
  echo "<th><input type='button' value='Add comment' onclick='createCmtEntity($revId)'/></th>\n";
  echo "</tr>\n";
  $fields = 'id,written,client_id,client_name,text,notifier,verified,signaled';
  $cmts = PageAdm::db()->queryMatrix(self::TABLE_COMMENT, $fields, 'review_id=' . $revId, 'id');
  if ($cmts)
  {
   foreach ($cmts as $cmtId => $comment)
   {
    $cmtText = $comment['text'];
    $clientId = $comment['client_id'];
    $clientName = $comment['client_name'];
    $client = htmlspecialchars($clientId ? PageAdm::makeEntityText($clientId, $clientName) : $clientName);

    echo "<tr>\n";
    echo "<th rowspan='3'>$cmtId</th>\n";
    echo '<th>' . $comment['written'] . "</th>\n";
    echo "<td onclick='changeCmtClient(this,$cmtId)'>$client</td>\n";
    echo "<td" . (!!$comment['notifier'] ? " class='checked'" : null) . " onclick='changeCmtFlag(this,$cmtId,0)'></td>\n";
    echo "<td" . (!!$comment['verified'] ? " class='checked'" : null) . " onclick='changeCmtFlag(this,$cmtId,1)'></td>\n";
    echo "<td" . (!!$comment['signaled'] ? " class='checked'" : null) . " onclick='changeCmtFlag(this,$cmtId,2)'></td>\n";
    echo "<th><input type='button' value='Delete comment $cmtId' onclick='deleteCmtEntity($cmtId)'/></th>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "<td colspan='6'>\n";
    PageAdm::echoTextArea("cmt-$cmtId", $cmtText, "comment text", 'changeCmtText', null, "id=$cmtId", 3);
    echo "</td>\n";
    echo "</tr>\n";

    echo "<tr><td colspan='6'>\n";
    self::showCmtCavils($cmtId);
    echo "</td></tr>\n";
   }
  }
  else
   echo '<tr><th colspan="7"><h3>No comments added to this review</h3></th></tr>' . "\n";
  echo "</table>\n";
 }

 private static function showCmtCavils($cmtId)
 {
  echo "<table style='float:right;margin:5px;'>\n";
  echo "<caption>Cavils to comment $cmtId</caption>\n";
  echo "<tr>\n";
  echo "<th width='50'>Id</th>\n";
  echo "<th width='200'>Written</th>\n";
  echo "<th width='200'>Author</th>\n";
  echo "<th width='50'>Notifier</th>";
  echo "<th width='50'>Violation</th>";
  echo "<th width='50'>Falsehood</th>";
  echo "<th><input type='button' value='Add cavil' onclick='createCmtCvlEntity($cmtId)'/></th>\n";
  echo "</tr>\n";
  $fields = 'id,written,client_id,client_name,text,notifier,violation,falsehood';
  $cvls = PageAdm::db()->queryMatrix(self::TABLE_CMT_CAVIL, $fields, 'comment_id=' . $cmtId, 'id');
  if ($cvls)
  {
   foreach ($cvls as $cvlId => $cavil)
   {
    $cvlText = $cavil['text'];
    $clientId = $cavil['client_id'];
    $clientName = $cavil['client_name'];
    $client = htmlspecialchars($clientId ? PageAdm::makeEntityText($clientId, $clientName) : $clientName);

    echo "<tr>\n";
    echo "<th rowspan='2'>$cvlId</th>\n";
    echo '<th>' . $cavil['written'] . "</th>\n";
    echo "<td onclick='changeCmtCvlClient(this,$cvlId)'>$client</td>\n";
    echo "<td" . (!!$cavil['notifier'] ? " class='checked'" : null) . " onclick='changeCmtCvlFlag(this,$cvlId,0)'></td>\n";
    echo "<td" . (!!$cavil['violation'] ? " class='checked'" : null) . " onclick='changeCmtCvlFlag(this,$cvlId,1)'></td>\n";
    echo "<td" . (!!$cavil['falsehood'] ? " class='checked'" : null) . " onclick='changeCmtCvlFlag(this,$cvlId,2)'></td>\n";
    echo "<th><input type='button' value='Delete cavil $cvlId' onclick='deleteCmtCvlEntity($cvlId)'/></th>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "<td colspan='6'>\n";
    PageAdm::echoTextArea("ccvl-$cvlId", $cvlText, "cavil text", 'changeCmtCvlText', null, "id=$cvlId", 3);
    echo "</td>\n";
    echo "</tr>\n";
   }
  }
  else
   echo '<tr><th colspan="7"><h3>No cavils added to this comment</h3></th></tr>' . "\n";
  echo "</table>\n";
 }
}
?>
