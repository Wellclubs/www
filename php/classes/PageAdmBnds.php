<?php

/**
 * Description of PageAdmBnds
 */
class PageAdmBnds
{
 private static function processAct($mbr_id, $act)
 {
  $table = WBrand::TABLE_BRAND;
  $entity = 'brand';
  switch ($act)
  {
  case 'createEntity' :
   PageAdm::createEntity($table, $entity, null, array('member_id' => $mbr_id), array(), array('noserial' => true));
   break;

  case 'deleteEntity' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField('com_centre', 'count(*)', "brand_id=$id")) > 0)
    echo ucfirst($entity) . " $id has some centres linked";
   else if (intval(PageAdm::db()->queryField('com_level', 'count(*)', "brand_id=$id")) > 0)
    echo ucfirst($entity) . " $id has some levels linked";
   else
    PageAdm::deleteEntity($table, $entity, $id);
   break;

  case 'changeName' :
   PageAdm::changeName($table, $entity);
   break;

  case 'changeEmail' :
   PageAdm::changeEmail($table, $entity);
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'brand_id', $entity);
   break;

  case 'changeURI' :
   $id = intval(HTTP::param('id'));
   $uri = HTTP::param('uri');
   if ($uri && PageAdm::db()->queryField($table, 'id', 'id<>' . $id . ' and uri=' . DB::str($uri)))
    echo 'This URI is already used for another ' . $entity;
   else if (!PageAdm::db()->modifyField($table, 'uri', 's', $uri, 'id=' . $id))
    echo "Error changing $entity $id URI to '$uri'";
   else
    echo 'OK';
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  $mbr_id = HTTP::get('mbr');
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($mbr_id, $_REQUEST['act']))
    exit;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
</style>
<script>
<?php if ($mbr_id != '') { ?>
var entity='brand';
function createEntity()
{
 A.createEntity(entity);
}
function deleteEntity(id)
{
 A.deleteEntity(id,entity,'','row-'+id);
}
function changeName(node,id)
{
 A.changeName(node,id,entity);
}
function changeTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entity);
}
function changeURI(id)
{
 var anchor=el('uri-'+id);
 var oldValue=decodeHTML(anchor.innerHTML);
 var newValue=prompt('Input a new URI for brand '+id+':',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'&act=changeURI&id='+id+'&uri='+newValue,false);
 req.send(null);
 var error='Error changing the brand URI on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 anchor.innerHTML=newValue;
 if(newValue.indexOf('http://')&&newValue.indexOf('https://'))
  newValue='http://'+newValue;
 anchor.href=newValue;
}
function changeEmail(node,id)
{
 A.changeEmail(node,id,entity)
}
<?php } ?>
function selectMember(id)
{
 document.location='<?php echo HTTP::addParam(HTTP::uriWithoutParam('mbr'), 'mbr', ''); ?>'+id;
}
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();

 $all_brands = !!HTTP::get('all_brands');
 $all_members = !!HTTP::get('all_members');
 $all_domains = !!HTTP::get('all_domains');

 echo '<span>Select a <a href="mbrs/">member</a>:</span>' . "\n";
 echo '<select onchange="selectMember(value)">' . "\n";
 echo '<option></option>' . "\n";
 $dw = (WDomain::ok() && !$all_domains) ? (' and domain_id=' . WDomain::id()) : '';
 $fields = 'client_id,(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id)';
 $where = '';
 if (!$all_members)
 {
  $filter = $all_brands ? '' :
    (' and exists (select null from ' . WCentre::TABLE_CENTRE . ' where member_id=a.client_id' . $dw . ')');
  $where .= 'exists (select null from ' . WBrand::TABLE_BRAND . ' where member_id=a.client_id' . $filter . ')';
 }
 $mbrs = PageAdm::db()->queryRecords(WMember::TABLE_MEMBER . ' a', $fields, $where, 'client_id');
 //echo "<!-- " . DB::lastQuery() . " -->\n";
 foreach ($mbrs as $mbr)
 {
  $id = $mbr[0];
  $selected = ($id == $mbr_id) ? ' selected' : '';
  $name = $mbr[1];
  echo "<option value='$id'$selected>$name</option>\n";
 }
 echo '</select>' . "\n";

 $uri = HTTP::uriWithoutParam('all_members');
 if ($all_members)
  echo "<a href='$uri'>Filter members</a>\n";
 else
 {
  $uri = HTTP::addParam($uri, 'all_members', '1');
  echo "<a href='$uri'>Show all members</a>" . "\n";
 }

 $uri = HTTP::uriWithoutParam('all_brands');
 if ($all_brands)
  echo "<a href='$uri'>Filter brands</a>\n";
 else
 {
  $uri = HTTP::addParam($uri, 'all_brands', '1');
  echo "<a href='$uri'>Show all brands</a>" . "\n";
 }

 $uri = HTTP::uriWithoutParam('all_domains');
 if ($all_domains)
  echo "<a href='$uri'>Current domain</a>\n";
 else
 {
  $uri = HTTP::addParam($uri, 'all_domains', '1');
  echo "<a href='$uri'>From all domains</a>" . "\n";
 }

 echo '<h1>' . htmlspecialchars(PageAdm::title()) . '</h1>' . "\n";

 $langs = Lang::map();
 $table = WBrand::TABLE_BRAND . ' a';
 $fields = 'id,name,uri,email' .
   ',(select count(*) from com_centre where brand_id=a.id and member_id=a.member_id' . $dw . ' and hidden is null)' .
   ',(select count(*) from com_centre where brand_id=a.id and member_id=a.member_id' . $dw . ')';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select title from com_brand_abc where brand_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 if ($where != null)
  $where = 'member_id=' . ($mbr_id ? $mbr_id : 'member_id');
 if (!$all_brands)
 {
  if ($where)
   $where .= ' and ';
  $where .= 'exists (select null from ' . WCentre::TABLE_CENTRE . ' where brand_id=a.id' . $dw . ')';
 }
 $limit = HTTP::paramInt('limit', 50);
 $offset = PageAdm::echoPageNav($table, $where, $limit);
 $records = PageAdm::db()->queryRecords($table, $fields, $where, 'id', $limit, $offset);
 //echo Base::htmlComment(DB::lastQuery()) . "\n";
?>
<table class="main" cellspacing="0" cellpadding="0">
<colgroup>
<col width="50"><col width="100"><col width="200"><?php
 foreach ($langs as $lang => $Lang)
  echo '<col width="200">';
?><col width="200"><col width="200"><col width="50">
</colgroup>
<tr>
<th>Id</th>
<th>Centres</th>
<th>Name</th>
<?php
 foreach ($langs as $lang => $Lang)
  echo '<th class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
?><th>URI</th>
<th>E-mail</th>
<th><input type='button' value='Create new' onclick='createEntity()'/></th>
</tr>
<?php
 if ($records)
  foreach ($records as $record)
  {
   $id = $record[0];
   $name = htmlspecialchars($record[1]);
   $uri = htmlspecialchars(Util::href($record[2]));
   $email = htmlspecialchars($record[3]);
   $active = htmlspecialchars($record[4]);
   $centres = htmlspecialchars($record[5]);

   echo "<tr id='row-$id'>\n";
   echo "<th class='right'><a class='b' href='bnd-$id/'>$id</a></th>\n";
   echo "<th><a class='b' href='ctrs/?mbr=$mbr_id&bnd=$id'>$active / $centres</a></th>\n";
   echo "<td class='left' onclick='changeName(this,$id)'>$name</td>\n";
   $i = 6;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($record[$i++]);
    echo "<td class='left' onclick='changeTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   echo "<td class='left' onclick='changeURI($id)'><a href='$uri' target='_blank' id='uri-$id' onclick='event.stopPropagation()'>$uri</a></td>\n";
   echo "<td class='left' onclick='changeEmail(this,$id)'>$email</td>\n";
   echo "<th><input type='button' value='Delete' onclick='deleteEntity($id)'/></th>\n";
   echo "</tr>\n";
  }
 else
  echo "<tr><th colspan='" . (6 + count($langs)) . "'>No data</th></tr>\n";
?></table>
</body>
</html>
<?php
  return true;
 }
}

?>
