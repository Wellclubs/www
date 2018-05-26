<?php

/**
 * Description of PageAdmCtrs
 */
class PageAdmCtrs
{
 private static $bnd_id = null;
 private static $mbr_id = null;

 private static function processAct($act)
 {
  $table = WCentre::TABLE_CENTRE;
  $entity = 'centre';
  switch ($act)
  {
  case 'createEntity' :
   if (!self::$mbr_id)
    echo 'No member selected';
   else
   {
    $type = PageAdm::db()->queryField('art_centre_type', 'min(id)');
    $values = array
    (
     'member_id' => self::$mbr_id,
     'type_id' => $type,
     'domain_id' => DB::str(WDomain::id())
    );
    if (is_numeric(self::$bnd_id))
     $values['brand_id'] = self::$bnd_id;
    PageAdm::createEntity($table, $entity, null, $values);
   }
   break;

  case 'deleteEntity' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField('com_menu_grp', 'count(*)', "centre_id=$id")) > 0)
    echo ucfirst($entity) . " $id has some services linked";
   else if (intval(PageAdm::db()->queryField('com_master', 'count(*)', "centre_id=$id")) > 0)
    echo ucfirst($entity) . " $id has some masters linked";
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
   PageAdm::changeTitle($table, 'centre_id', $entity);
   break;

  case 'changeType' :
   $id = intval(HTTP::param('id'));
   $type = intval(HTTP::param('value'));
   if (!PageAdm::db()->modifyField($table, 'type_id', 'i', $type, 'id=' . $id))
    echo "Error changing $entity type: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeMember' :
   $id = intval(HTTP::param('id'));
   $memberId = DB::str(HTTP::get('member'));
   $client = PageAdm::db()->queryField(WMember::TABLE_MEMBER, 'client_id', 'client_id=' . $memberId);
   if (!$client)
    echo 'Invalid member id: ' . $memberId;
   else if (!PageAdm::db()->modifyField($table, 'member_id', 'i', $client, "id=$id"))
    echo "Error changing $entity owner member: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeAddr' :
   $id = intval(HTTP::param('id'));
   $addr = HTTP::param('addr');
   if (WCentre::changeAddress(PageAdm::db(), $id, $addr))
    echo 'OK';
   else
    echo "Error changing $entity $id address to '$addr'";
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
    echo "Error changing $entity $id serial number to $serial";
   break;

  case 'changeBookType' :
   $id = intval(HTTP::param('id'));
   $type = HTTP::param('value');
   if (strlen($type))
   {
    $type = DB::str($type);
    if (!PageAdm::db()->queryField('art_book_type', 'id', 'id=' . $type))
    {
     echo "Invalid book_type: $type";
     break;
    }
   }
   else
    $type = 'null';
   if (!PageAdm::db()->modifyFields($table, array('book_type_id' => $type), 'id=' . $id))
    echo "Error changing $entity book_type: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeField' :
   PageAdm::changeField($table, $entity);
   break;

  case 'changeFlag' :
   PageAdm::changeFlag($table, $entity);
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
  self::$mbr_id = HTTP::get('mbr');
  self::$bnd_id = HTTP::get('bnd');
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
var entity='centre';
<?php
$types = PageAdm::db()->queryRecords('art_centre_type', 'id,name', '', 'id');
$typesObj = $types ? implode(',', array_map(array('Util', 'mapJsonObject'), $types)) : null;
$typesStr = $types ? implode(', ', array_map(array('Util', 'mapJsonString'), $types)) : null;
?>
var typesObj={<?php echo $typesObj;?>};
var typesStr="<?php echo $typesStr;?>";
function changeType(node,id)
{
 A.changeItem(node,id,entity,'type','changeType',typesStr,typesObj);
}
function changeMember(node,id)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new owner member id:');
 if((newValue==null)||(newValue=='')||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeMember&id='+id+'&member='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' owner member on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function changeAddr(node,id)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new address for centre '+id+':',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeAddr&id='+id+'&addr='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' address on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
<?php
$bookTypes = PageAdm::db()->queryRecords('art_book_type', 'id,name', '', 'id');
$bookTypesObj = $bookTypes ? implode(',', array_map(array('Util', 'mapJsonObject'), $bookTypes)) : null;
$bookTypesStr = $bookTypes ? implode(', ', array_map(array('Util', 'mapJsonString'), $bookTypes)) : null;
?>
var bookTypesObj={<?php echo $bookTypesObj;?>};
var bookTypesStr="<?php echo $bookTypesStr;?>";
function changeBookType(node,id)
{
 A.changeItem(node,id,entity,'book_type','changeBookType',bookTypesStr,bookTypesObj);
}
function selectMember(id)
{
 document.location='<?php echo HTTP::addParam(HTTP::uriWithoutParam('mbr'), 'mbr', ''); ?>'+id;
}
function selectBrand(id)
{
 document.location='<?php echo HTTP::addParam(HTTP::uriWithoutParam('bnd'), 'bnd', ''); ?>'+id;
}
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();

 $all_brands = !!HTTP::get('all_brands');
 $all_members = !!HTTP::get('all_members');
 $all_domains = !!HTTP::get('all_domains');

 echo "<span>Select a <a href='mbrs/'>member</a>:</span><select onchange='selectMember(value)'>\n";
 echo "<option></option>\n";
 $dw = (WDomain::ok() && !$all_domains) ? (' and domain_id=' . WDomain::id()) : '';
 $fields = 'client_id,(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id)';
 $where = '';
 if (!$all_members)
 {
  if ($where)
   $where .= ' and ';
  $where .= 'exists (select null from ' . WCentre::TABLE_CENTRE . ' where member_id=a.client_id' . $dw . ')';
 }
 $mbrs = PageAdm::db()->queryRecords(WMember::TABLE_MEMBER . ' a', $fields, $where, 'client_id', 100);
 if ($mbrs)
 {
  foreach ($mbrs as $mbr)
  {
   $id = $mbr[0];
   $name = $mbr[1];
   $selected = ($id == self::$mbr_id) ? ' selected' : '';
   echo "<option value='$id'$selected>$name</option>\n";
  }
 }
 echo "</select>\n";

 $uri = HTTP::uriWithoutParam('all_members');
 if ($all_members)
  echo "<a href='$uri'>Filter members</a>\n";
 else
 {
  $uri = HTTP::addParam($uri, 'all_members', '1');
  echo "<a href='$uri'>Show all members</a>" . "\n";
 }

 $uri = 'bnds/';
 if (self::$mbr_id)
  $uri .= '?mbr=' . self::$mbr_id;
 echo "<span>Select a <a href='$uri'>brand</a>:</span><select onchange='selectBrand(value)'>\n";
 echo "<option></option>\n";
 echo "<option value='N'" . ((self::$bnd_id == 'N') ? ' selected' : '') . ">---- NO BRAND ----</option>\n";
 echo "<option value='A'" . ((self::$bnd_id == 'A') ? ' selected' : '') . ">--- ALL BRANDS ---</option>\n";
 if ($where != null)
  $where = self::$mbr_id ? ('member_id=' . self::$mbr_id) : '';
 if (!$all_brands)
 {
  if ($where)
   $where .= ' and ';
  $where .= 'exists (select null from ' . WCentre::TABLE_CENTRE . ' where brand_id=a.id' . $dw . ')';
 }
 $bnds = PageAdm::db()->queryRecords(WBrand::TABLE_BRAND . ' a', 'id,name', $where, 'id');
 if ($bnds)
 {
  foreach ($bnds as $bnd)
  {
   $id = $bnd[0];
   $name = $bnd[1];
   $selected = ($id == self::$bnd_id) ? ' selected' : '';
   echo "<option value='$id'$selected>$name</option>\n";
  }
 }
 echo "</select>\n";

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
 $table = WCentre::TABLE_CENTRE . ' a';
 $fields = 'id,brand_id,member_id,serial,hidden,name,email,address,capacity,is_hotel,book_type_id,tax_prc,com_prc' .
   ',(select name from art_centre_type where id=a.type_id)type_name' .
   ',(select name from art_book_type where id=a.book_type_id)book_type_name';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select title from com_centre_abc where centre_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $where = 'member_id=' . (self::$mbr_id ? self::$mbr_id : 'member_id');
 if (self::$bnd_id != 'A')
  $where .= ' and brand_id' . ((self::$bnd_id == 'N') ? ' is null' : ('=' . self::$bnd_id));
 $where .= $dw;
 $limit = HTTP::paramInt('limit', 50);
 $offset = PageAdm::echoPageNav($table, $where, $limit);
 $records = PageAdm::db()->queryArrays($table, $fields, $where, 'brand_id,serial,id', $limit, $offset);
 //echo Base::htmlComment(DB::lastQuery()) . "\n";
 echo "<table class='main' cellspacing='0' cellpadding='0'>\n";
 echo "<colgroup>";
 echo "<col width='50'>"; // Id
 echo "<col width='50'>"; // Nr
 echo "<col width='100'>"; // Member
 echo "<col width='200'>"; // Type
 echo "<col width='200'>"; // Name
 foreach ($langs as $lang => $Lang)
  echo '<col width="200">';
 echo "<col width='200'>"; // Address
 echo "<col width='200'>"; // E-mail
 echo "<col width='20'>"; // Cap.
 echo "<col width='20'>"; // Is Hotel
 echo "<col width='100'>"; // Book Type
 echo "<col width='20'>"; // Tax Prc
 echo "<col width='20'>"; // Com. Prc
 echo "<col width='20'>"; // Button 'Hide'
 echo "<col width='20'>"; // Button 'Delete'
 echo "</colgroup>";
?>
<tr>
<th>Id</th>
<th>Nr</th>
<?php if (!self::$mbr_id) { ?>
<th>Member</th>
<?php } ?>
<th>Type</th>
<th>Name</th>
<?php
 foreach ($langs as $lang => $Lang)
  echo '<th class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
?><th>Address</th>
<th>E-mail</th>
<th>Cap.</th>
<th>Hotel</th>
<th>Book Type</th>
<th>Tax %</th>
<th>Com. %</th>
<th colspan='2'>
<?php if (self::$mbr_id) { ?>
<input type='button' value='Create new' onclick='A.createEntity(entity)'/>
<?php } ?>
</th>
</tr>
<?php
 if ($records)
 {
  $lastBrandId = null;
  foreach ($records as $record)
  {
   $id = $record['id'];
   $brandId = $record['brand_id'];
   $memberId = $record['member_id'];
   $type = $record['type_name'];
   $serial = htmlspecialchars($record['serial']);
   $hidden = $record['hidden'] != '';
   $name = htmlspecialchars($record['name']);
   $email = htmlspecialchars($record['email']);
   $address = htmlspecialchars($record['address']);
   $capacity = $record['capacity'];
   $isHotel = $record['is_hotel'];
   $bookType = $record['book_type_name'];
   $taxPrc = $record['tax_prc'];
   $comPrc = $record['com_prc'];

   if ($brandId != $lastBrandId)
   {
    $values = PageAdm::db()->queryFields(WBrand::TABLE_BRAND, 'member_id,name', 'id=' . $brandId);
    $brandOwnerId = $values[0];
    $brandName = $values[1];
    $brandOwnerName = null;
    if ($brandOwnerId != self::$mbr_id)
     $brandOwnerName = WClient::getClientName($brandOwnerId);
    $brand = htmlspecialchars(PageAdm::makeEntityText($brandId, $brandName, $brandOwnerName));
    echo "<tr><th colspan='" . (14 + count($langs)) . "'>$brand</th></tr>\n";
    $lastBrandId = $brandId;
   }

   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th class='right'><a class='b' href='ctr-$id/'>$id</a></th>\n";
   echo "<td class='right' onclick='A.changeSerial(this,$id,entity)'>$serial</td>\n";
   if (!self::$mbr_id)
    echo "<td class='right' onclick='changeMember(this,$id)'>$memberId</td>\n";
   echo "<td class='left' onclick='changeType(this,$id)'>$type</td>\n";
   echo "<td class='left' onclick='A.changeName(this,$id,entity)'>$name</td>\n";

   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($record['title_' . $lang]);
    echo "<td class='left' onclick='A.changeTitle(this,$id,\"$lang\",entity)'>$value</td>\n";
   }

   echo "<td class='left' onclick='changeAddr(this,$id)'>$address</td>\n";
   echo "<td class='left small' onclick='A.changeEmail(this,$id,entity)'>$email</td>\n";
   echo "<td class='right' onclick='A.changeField(this,$id,entity,\"capacity\")'>$capacity</td>\n";
   $checked = $isHotel ? " class='checked'" : '';
   echo "<td$checked onclick='A.changeFlag(this,$id,entity,\"is_hotel\")'></td>\n";
   echo "<td class='center small' onclick='changeBookType(this,$id)'>$bookType</td>\n";
   echo "<td class='left right' onclick='A.changeField(this,$id,entity,\"tax_prc\")'>$taxPrc</td>\n";
   echo "<td class='left right' onclick='A.changeField(this,$id,entity,\"com_prc\")'>$comPrc</td>\n";
   $value = $hidden ? 'Show' : 'Hide';
   $arg = $hidden ? 'false' : 'true';
   echo "<th><input type='button' value='$value' onclick='A.hideEntity(this,$id,$arg,entity)'/></th>\n";
   echo "<th><input type='button' value='Delete' onclick='A.deleteEntity($id,entity,\"\",\"row-\"+id)'/></th>\n";
   echo "</tr>\n";
  }
 }
 else
  echo "<tr><th colspan='" . (14 + (!self::$mbr_id ? 1 : 0) + count($langs)) . "'>No data</th></tr>\n";
?></table>
</body>
</html>
<?php
  return true;
 }
}

?>
