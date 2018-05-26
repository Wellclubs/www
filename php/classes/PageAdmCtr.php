<?php

/**
 * Description of PageAdmCtr
 */
class PageAdmCtr
{
 private static function processAct($centreId, $act)
 {
  $table = WCentre::TABLE_CENTRE;
  $entity = 'centre';
  switch ($act)
  {
  case 'numbers' :
   $phones = PageAdm::db()->queryRecords('com_centre_phone', 'centre_id,serial,phone');
   foreach ($phones as $record)
   {
    $centre = $record[0];
    $serial = $record[1];
    $phone = $record[2];
    $number = Util::pureNumber($phone);
    if (!PageAdm::db()->modifyField('com_centre_phone', 'number', 'i', $number, "centre_id=$centre and serial=$serial"))
     Base::addError("Error purifying phone '$phone' to number '$number' ($centre:$serial): " . DB::lastQuery());
   }
   return false;

  case 'changeMember' :
   $memberId = DB::str(HTTP::get('member'));
   $client = PageAdm::db()->queryField(WMember::TABLE_MEMBER, 'client_id', 'client_id=' . $memberId);
   if (!$client)
    echo 'Invalid member id: ' . $memberId;
   else if (!PageAdm::db()->modifyField($table, 'member_id', 'i', $client, "id=$centreId"))
    echo "Error changing $entity owner member: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeBrand' :
   $brand = HTTP::get('brand');
   if (strlen($brand) && !PageAdm::db()->queryField(WBrand::TABLE_BRAND, 'count(*)', 'id=' . $brand))
    echo "Brand '$brand' does not exist";
   else if (!PageAdm::db()->modifyFields('com_master', array('level_id' => 'null'), "centre_id=$centreId"))
    echo "Error reseting $entity master levels: " . DB::lastQuery();
   else if (!PageAdm::db()->modifyFields(WService::TABLE_TIP, array('level_id' => 'null'), "centre_id=$centreId"))
    echo "Error reseting $entity pricing option levels: " . DB::lastQuery();
   else if (!PageAdm::db()->modifyFields($table, array('brand_id' => (strlen($brand) ? DB::str($brand) : 'null')), "id=$centreId"))
    echo "Error changing $entity brand: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeDomain' :
   $value = HTTP::get('value');
   if (strlen($value) && !PageAdm::db()->queryField(WDomain::TABLE_DOMAIN, 'count(*)', 'id=' . $value))
    echo "Domain '$value' does not exist";
   else if (!PageAdm::db()->modifyFields($table, array('domain_id' => (strlen($value) ? DB::str($value) : 'null')), "id=$centreId"))
    echo "Error changing $entity domain: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeName' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeName($table, $entity);
   break;

  case 'changeType' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeField($table, $entity, 'type_id', 'value');
   break;

  case 'changeIsHotel' :
   $field = 'is_hotel';
   $value = (HTTP::get('value') == 'Y') ? '1' : 'null';
   $where = 'id=' . $centreId;
   PageAdm::db()->modifyFields($table, array($field => $value), $where);
   echo 'OK';
   break;

  case 'changeTaxPrc' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeField($table, $entity, 'tax_prc');
   break;

  case 'changeComPrc' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeField($table, $entity, 'com_prc');
   break;

  case 'changeBookType' :
   $field = 'book_type_id';
   $value = HTTP::get('value');
   if (array_search($value, array('B', 'P')) === false)
    $value = 'null';
   else
    $value = DB::str($value);
   $where = 'id=' . $centreId;
   PageAdm::db()->modifyFields($table, array($field => $value), $where);
   echo 'OK';
   break;

  case 'changeAddr' :
   $addr = HTTP::param('addr');
   if (WCentre::changeAddress(PageAdm::db(), $centreId, $addr))
    echo 'OK';
   else
    echo "Error changing $entity $centreId address to '$addr'";
   break;

  case 'changeLocation' :
   $lat = HTTP::param('lat');
   $lng = HTTP::param('lng');
   if (WCentre::changeLocation(PageAdm::db(), $centreId, $lat, $lng))
    echo 'OK';
   else
    echo "Error changing $entity $centreId location to '$lat/$lng'";
   break;

  case 'queryLocation' :
   if (WCentre::queryLocation($centreId))
    echo 'OK';
   else
    echo "Error querying $entity $centreId location";
   break;

  case 'changeEmail' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeEmail($table, $entity);
   break;

  case 'changeKeywords' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeField($table, $entity, 'keywords');
   break;

  case 'changeCurrency' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeField($table, $entity, 'currency_id', 'currency');
   break;

  case 'changeCapacity' :
   $_REQUEST['id'] = $centreId;
   PageAdm::changeField($table, $entity, 'capacity');
   break;

  case 'changeSgrp' :
   $field = HTTP::param('field');
   $param = HTTP::param('value', '');
   $value = ($param == '1') ? '1' : '0';
   $where = array('centre_id' => $centreId, 'sgrp_id' => $field);
   $def = PageAdm::db()->queryField(WSGrp::TABLE_GROUP, 'popular', array('id' => $field));
   if ($def == $param)
   {
    PageAdm::db()->deleteRecords('com_centre_sgrp', $where);
    $ok = (PageAdm::db()->queryField('com_centre_sgrp', 'count(*)', $where) == '0');
   }
   else
   {
    PageAdm::db()->mergeField('com_centre_sgrp', 'active', $value, $where);
    $ok = (PageAdm::db()->queryField('com_centre_sgrp', 'active', $where) == $value);
   }
   if ($ok)
    echo 'OK';
   else
    echo "Error changing the $entity $centreId record in the database";
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'centre_id', $entity, $centreId);
   break;

  case 'changeSubtitle' :
  $lang = HTTP::param('lang');
  $value = HTTP::param('value');
  $table .= '_abc';
  $field = 'subtitle';
  $where = array('centre_id' => $centreId, 'abc_id' => DB::str($lang));
  PageAdm::db()->mergeField($table, $field, $value ? DB::str($value) : 'null', $where);
  if (PageAdm::db()->queryField($table, $field, $where) == $value)
   echo 'OK';
  else
   echo "Error changing $field to '$value': " . DB::lastQuery();
   break;

  case 'appendMetro' :
   $table = WCentre::TABLE_CENTRE_METRO;
   $metro_id = HTTP::get('id');
   if (PageAdm::db()->queryField($table, '1', 'centre_id=' . $centreId . ' and metro_id=' . $metro_id))
    echo 'Appending metro station is already appended';
   else if (!PageAdm::db()->insertValues($table, array('centre_id' => $centreId, 'metro_id' => $metro_id)))
    echo 'Error appending a metro station: ' . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'deleteMetro' :
   $table = WCentre::TABLE_CENTRE_METRO;
   $metro_id = HTTP::get('id');
   $where = 'centre_id=' . $centreId . ' and metro_id=' . $metro_id;
   PageAdm::db()->deleteRecords($table, $where);
   if (PageAdm::db()->queryField($table, '1', 'centre_id=' . $centreId . ' and metro_id=' . $metro_id))
    echo 'Error removing a metro station: ' . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'appendPhone' :
   $table = WCentre::TABLE_CENTRE_PHONE;
   $phone = HTTP::get('phone');
   $number = Util::pureNumber($phone);
   if (!strlen($number))
    echo "Phone number '$phone' does not contain any digit";
   else
   {
    $fields = "(select count(*) from $table where centre_id=$centreId and number='$number')," .
      "(select ifnull(max(serial),0)+1 from $table where centre_id=$centreId)";
    $values = PageAdm::db()->queryFields(null, $fields);
    if ($values[0])
     echo 'Appending phone number is already appended';
    else if (!PageAdm::db()->insertValues('com_centre_phone', array('centre_id' => $centreId, 'serial' => $values[1], 'phone' => DB::str($phone), 'number' => DB::str($number))))
     echo 'Error appending a phone number: ' . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  case 'deletePhone' :
   $table = WCentre::TABLE_CENTRE_PHONE;
   $serial = HTTP::get('nr');
   $where = 'centre_id=' . $centreId . ' and serial=' . $serial;
   PageAdm::db()->deleteRecords($table, $where);
   if (PageAdm::db()->queryField($table, '1', 'centre_id=' . $centreId . ' and serial=' . $serial))
    echo 'Error removing a phone number: ' . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changePhoneSerial' :
   $table = WCentre::TABLE_CENTRE_PHONE;
   $old = HTTP::get('old');
   $new = HTTP::get('new');
   $fields = "(select count(*) from $table where centre_id=$centreId and serial=$old)," .
     "(select count(*) from $table where centre_id=$centreId and serial=$new)";
   $values = PageAdm::db()->queryFields(null, $fields);
   if ($new == $old)
    echo 'Changing and new serial numbers are the same';
   else if (!$values[0])
    echo 'Changing phone number is not found';
   else if ($values[1])
    echo 'New serial number is already used';
   else if (!PageAdm::db()->modifyField($table, 'serial', 'i', $new, "centre_id=$centreId and serial=$old"))
    echo 'Error changing a serial number';// . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changePhoneNumber':
   $table = WCentre::TABLE_CENTRE_PHONE;
   $serial = HTTP::get('nr');
   $phone = HTTP::get('phone');
   $number = Util::pureNumber($phone);
   if (!strlen($number))
    echo "Phone number '$phone' does not contain any digit";
   else
   {
    $fields = "(select count(*) from $table where centre_id=$centreId and serial=$serial)," .
      "(select count(*) from $table where centre_id=$centreId and number='$number' and serial<>$serial)";
    $values = PageAdm::db()->queryFields(null, $fields);
    if (!$values[0])
     echo 'Changing phone number is not found';// . DB::lastQuery();
    else if ($values[1])
     echo 'New phone number is already entered';
    else if (!PageAdm::db()->modifyFields($table, array('phone' => DB::str($phone), 'number' => DB::str($number)), "centre_id=$centreId and serial=$serial"))
     echo 'Error changing a phone number';// . DB::lastQuery();
    else
     echo 'OK';
   }
   break;

  case 'changeTime' :
   $table = WCentre::TABLE_CENTRE_SCHED;
   $day = HTTP::get('day');
   $kind = HTTP::get('kind');
   $time = HTTP::get('time');
   $field = ($kind == '0') ? 'open_min' : (($kind == '1') ? 'close_min' : '');
   $min = Util::str2min($time);
   $where = array('centre_id' => $centreId, 'week_day' => $day);
   if (!is_numeric($day) || (intval($day) < 1) || (intval($day) > 7))
    echo 'Invalid "day" parameter value: "' . $day . '"';
   else if ($field == '')
    echo 'Invalid "kind" parameter value: "' . $kind . '"';
   else if ($min < 0)
    echo 'Invalid time string: "' . $time . '" (HH:MM or HH expected)';
   else if (!PageAdm::db()->mergeField($table, $field, isset($min) ? $min : 'null', $where))
    echo 'Error changing a time value: ' . DB::lastQuery();
   else
    echo Util::min2str($min);
   break;

  case 'copyTimes' :
   $table = WCentre::TABLE_CENTRE_SCHED;
   $day = HTTP::get('day');
   $values = PageAdm::db()->queryPairs($table, 'open_min,close_min', array('centre_id' => $centreId, 'week_day' => ($day - 1)));
   if (!$values)
    $values = array('open_min' => '', 'close_min' => '');
   foreach ($values as $key => $value)
    $values[$key] = DB::str($value);
   if (!PageAdm::db()->mergeFields($table, $values, array('centre_id' => $centreId, 'week_day' => $day)))
    echo 'Error copying opening hours';// . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'uploadLogo' :
   if (!WCentre::uploadLogo($centreId))
    return Base::addError('Error uploading a logo image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'loadLogoFromDB' :
   $id = HTTP::param('id');
   if (WCentre::uploadLogoFromDB($centreId, $id))
     echo 'OK';
    else
     echo "Error loading a logo image for centre $centreId to the database: " . DB::lastQuery();
   break;

  case 'loadLogoFromURI' :
   $uri = HTTP::param('uri');
   if (WCentre::uploadLogoFromURI($centreId, $uri))
     echo 'OK';
    else
     echo "Error loading a logo image for centre $centreId to the database: " . DB::lastQuery();
   break;

  case 'clearLogo' :
   if (!WCentre::clearLogo($centreId))
    echo 'Error clearing a logo image';
   else
    echo 'OK';// . DB::lastQuery();
   break;

  case 'clearPlaces' :
   if (!PageAdm::db()->deleteRecords(WCentre::TABLE_CENTRE_PLACE, "centre_id=$centreId"))
    echo 'Error deleting all centre places: ' . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'deletePlace' :
   $serial = HTTP::get('nr');
   if (!PageAdm::db()->deleteRecords(WCentre::TABLE_CENTRE_PLACE, "centre_id=$centreId and serial=$serial"))
    echo "Error deleting centre place $serial: " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeDescr' :
   $lang = HTTP::param('lang');
   $table = WCentre::TABLE_CENTRE . '_abc';
   $where = array('centre_id' => $centreId, 'abc_id' => DB::str($lang));
   PageAdm::changeText($table, $where, 'centre description', 'descr');
   break;

  case 'changeFile' :
   PageAdm::changeText($table, 'id=' . $centreId, 'centre file', 'file');
   break;

  case 'uploadImage' :
   $serial = HTTP::param('nr');
   if (!WCentre::createGallery()->uploadImage($serial))
    return Base::addError('Error uploading an image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'changeImageTitle' :
   $serial = HTTP::param('id');
   $lang = HTTP::param('lang');
   $title = HTTP::param('title');
   if (Lang::setDBValue($title, $table . '_img_abc', null, array('centre_id' => $centreId, 'serial' => $serial), $lang))
    echo 'OK';
   else
    echo "Error changing $entity $id language '$lang' title to '$title': " . DB::lastQuery();
   break;

  case 'deleteImage' :
   $serial = HTTP::get('nr');
   if (!WCentre::createGallery()->deleteImage($serial))
    echo 'Error deleting an image';
   else
    echo 'OK';// . DB::lastQuery();
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
  $centreId = WCentre::id();
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($centreId, $_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
table.main th.sgrp { background:#ccc; }
.block { display:block;float:left;margin:0 10px 10px 0; }
.block th { padding:1px 4px; }
.block td { padding:1px 4px; }
tr.holiday td { background-color:#ffc;border-color:#000;color:#00f; }
</style>
<script>
var entity='centre';
function changeMember()
{
 var newValue=prompt('Input a new owner member id:');
 if((newValue==null)||(newValue==''))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeMember&member='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' owner member on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeBrand()
{
 var newValue=prompt('Input a new brand id:');
 if(newValue==null)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeBrand&brand='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' brand on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeDomain(id)
{
 var newValue=prompt('Input a new domain id (0,1,2):',id);
 if((newValue==null)||(newValue==id))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeDomain&value='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' domain on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeName(node)
{
 if(A.changeName(node,null,entity,null,true))
  document.location.reload(true);
}
<?php
$types = PageAdm::db()->queryRecords('art_centre_type', 'id,name', '', 'id');
$typesObj = $types ? implode(',', array_map(array('Util', 'mapJsonObject'), $types)) : null;
$typesStr = $types ? implode(', ', array_map(array('Util', 'mapJsonString'), $types)) : null;
?>
var typesObj={<?php echo $typesObj;?>};
var typesStr="<?php echo $typesStr;?>";
function changeType(node)
{
 A.changeItem(node,id,entity,'type','changeType',typesStr,typesObj);
}
function changeIsHotel(node)
{
 var newValue=prompt('Is the centre an hotel (Y or N)?:');
 if(newValue==null)
  return;
 newValue=newValue.toUpperCase();
 if((newValue!='Y')&&(newValue!='N'))
  return alert('Invalid value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeIsHotel&value='+newValue),false);
 req.send(null);
 var error='Error changing the centre is_hotel flag on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function changeTaxPrc(node)
{
 A.changeField(node,null,entity,'tax_prc','changeTaxPrc');
}
function changeComPrc(node)
{
 A.changeField(node,null,entity,'com_prc','changeComPrc');
}
function changeBookType(node)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input allowed booking type (B, P or empty for all)?:',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 newValue=newValue.toUpperCase();
 if((newValue!='')&&(newValue!='B')&&(newValue!='P'))
  return alert('Invalid value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeBookType&value='+newValue),false);
 req.send(null);
 var error='Error changing the centre book_type_id field on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function changeAddr(node)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new address for the '+entity+':',oldValue);
 if(newValue===null)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeAddr&addr='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' address on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeLocation(node)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new location for the '+entity+':',oldValue);
 if((newValue===null)||(newValue==oldValue))
  return;
 var values=newValue.split('/');
 if(values.length!=2)
  return alert('Invalid location format');
 var lat=values[0];
 var lng=values[1];
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeLocation&lat='+lat+'&lng='+lng),false);
 req.send(null);
 var error='Error changing the '+entity+' location on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function queryLocation(node)
{
 if(!confirm('Query location?'))
  return changeLocation(node);
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=queryLocation'),false);
 req.send(null);
 var error='Error querying a location on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeEmail(node)
{
 A.changeEmail(node,null,entity);
}
function changeKeywords(node)
{
 A.changeField(node,null,entity,'keywords','changeKeywords');
}
function changeCurrency(node)
{
 var newValue=prompt('Input a new currency code (eg., RUB):').toUpperCase();
 if(newValue==null)
  return;
 if(newValue.length&&(newValue.length!=3))
  return alert('Invalid currency code');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeCurrency&currency='+newValue),false);
 req.send(null);
 var error='Error changing the centre currency code on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function changeCapacity(node)
{
 A.changeField(node,null,entity,'capacity','changeCapacity');
}
function changeSgrp(node,id,title)
{
 A.changeFlag(node,'',entity,id,'changeSgrp',title);
}
function changeTitle(node,lang)
{
 A.changeTitle(node,null,lang,entity);
}
function changeSubtitle(node,lang)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new subtitle for the language "'+lang+'":',oldValue);
 if((newValue===null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeSubtitle&lang='+lang+'&value='+newValue),false);
 req.send(null);
 var error='Error storing the new subtitle on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
function appendMetro()
{
 var id=el('metro-new').value;
 if(!id)
  {alert('Select a station from the list');return el('metro-new').focus();};
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=appendMetro&id='+id),false);
 req.send(null);
 var error='Error appending a station on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deleteMetro(id,title)
{
 if(!confirm('Remove metro station "'+title+'"?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deleteMetro&id='+id),false);
 req.send(null);
 var error='Error removing a metro station "'+title+'" on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function appendPhone()
{
 var phone=el('phone-new').value;
 if(!phone)
  {alert('Input a phone number into the field');return el('phone-new').focus();};
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=appendPhone&phone='+phone),false);
 req.send(null);
 var error='Error appending a phone number on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deletePhone(nr,phone)
{
 if(!confirm('Remove phone number '+nr+' "'+phone+'"?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deletePhone&nr='+nr),false);
 req.send(null);
 var error='Error removing a phone number '+nr+' "'+phone+'" on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changePhoneSerial(nr,phone)
{
 var newNr=prompt('Input a new serial number for the phone '+nr+' "'+phone+'":',nr);
 if((newNr==null)||(newNr==nr))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changePhoneSerial&old='+nr+'&new='+newNr),false);
 req.send(null);
 var error='Error changing the serial number on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changePhoneNumber(nr)
{
 var td=el('phone-'+nr);
 var oldValue=decodeHTML(td.innerHTML);
 var newValue=prompt('Input a new phone number '+nr+':',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changePhoneNumber&nr='+nr+'&phone='+newValue),false);
 req.send(null);
 var error='Error changing the phone number on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 td.innerHTML=newValue;
}
function changeTime(day,kind)
{
 var td=el('time-'+day+kind);
 var oldValue=decodeHTML(td.innerHTML);
 var newValue=prompt('Input a new time:',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeTime&day='+day+'&kind='+kind+'&time='+newValue),false);
 req.send(null);
 var error='Error changing the time value on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText.length>5)
  return alert(error+': '+req.responseText);
 td.innerHTML=req.responseText;
}
function copyTimes(day)
{
 if(!confirm('Copy opening hours from the day '+(day-1)+' to the day '+day+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=copyTimes&day='+day),false);
 req.send(null);
 var error='Error copying the time values on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 el('time-'+day+0).innerHTML=el('time-'+(day-1)+0).innerHTML;
 el('time-'+day+1).innerHTML=el('time-'+(day-1)+1).innerHTML;
}
function loadLogoFromDB()
{
 var id=parseInt(prompt('Input the ID of a logo picture'));
 if(id<=0)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=loadLogoFromDB&id='+id,false);
 req.send(null);
 var error='Error loading a logo image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function loadLogoFromURI()
{
 var uri=prompt('Input the URI of a logo picture');
 if(!uri)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=loadLogoFromURI&uri='+encodeURIComponent(uri),false);
 req.send(null);
 var error='Error loading a logo image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function clearLogo()
{
 if(!confirm('Clear logo image?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=clearLogo',false);
 req.send(null);
 var error='Error clearing an image on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function clearPlaces()
{
 if(!confirm('Delete all places?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=clearPlaces'),false);
 req.send(null);
 var error='Error deleting all places on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function deletePlace(nr)
{
 if(!confirm('Delete place '+nr+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deletePlace&nr='+nr),false);
 req.send(null);
 var error='Error deleting place '+nr+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeImageTitle(node,nr,lang)
{
 A.changeTitle(node,nr,lang,'image','changeImageTitle');
}
function deleteImage(nr)
{
 if(!confirm('Delete image '+nr+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deleteImage&nr='+nr),false);
 req.send(null);
 var error='Error deleting an image '+nr+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<table class="main block">
<caption>General</caption>
<tr><th class="right">Owner:</th><th class="left"><?php
$memberId = WCentre::memberId();
$memberName = WClient::getClientName($memberId);
echo '<a href="mbr-' . $memberId . '/">' . htmlspecialchars(PageAdm::makeEntityText($memberId, $memberName)) . '</a>';
?></th><th><input type="button" value="Change" onclick="changeMember()"/></th></tr>
<tr><th class="right">Brand:</th><th id="brand" class="left"><?php
$brandId = WCentre::brandId();
$brandTitle = null;
if ($brandId)
{
 $brandName = PageAdm::db()->queryField('com_brand', 'name', 'id=' . $brandId);
 $brandOwner = null;
 if (WBrand::memberId() != WCentre::memberId())
  $brandOwner = PageAdm::makeEntityText(WBrand::memberId(), WClient::getClientName(WBrand::memberId()));
 $brandTitle = PageAdm::makeEntityText($brandId, $brandName, $brandOwner);
}
echo '<a href="bnd-' . $brandId . '/">' . htmlspecialchars($brandTitle) . '</a>';
?></th><th><input type="button" value="Change" onclick="changeBrand()"/></th></tr>
<tr><th class="right">Domain:</th><th id="domain" class="left"><?php echo htmlspecialchars(WCentre::domainName());
?></th><th><input type="button" value="Change" onclick="changeDomain('<?php echo WCentre::domainId(); ?>')"/></th></tr>
<tr><th class="right">Name:</th>
<td id='name' align="left" colspan="2" onclick="changeName(this)"><?php echo htmlspecialchars(WCentre::name());?></td></tr>
<tr><th class="right">Type:</th>
<td id='type' align="left" colspan="2" onclick="changeType(this)"><?php echo htmlspecialchars(WCentre::typeTitle());?></td></tr>
<tr><th class="right">Hotel:</th>
<td id='hotel' align="center" colspan="2" onclick="changeIsHotel(this)"><?php echo WCentre::isHotel() ? 'Y' : 'N';?></td></tr>
<tr><th class="right">Tax %:</th>
<td id='tax_prc' align="right" colspan="2" onclick="changeTaxPrc(this)"><?php echo WCentre::taxPrc();?></td></tr>
<tr><th class="right">Com. %:</th>
<td id='com_prc' align="right" colspan="2" onclick="changeComPrc(this)"><?php echo WCentre::comPrc();?></td></tr>
<tr><th class="right">Booking:</th>
<td id='booking' align="center" colspan="2" onclick="changeBookType(this)"><?php echo WCentre::bookType();?></td></tr>
<tr><th class="right">Address:</th>
<td align="left" colspan="2" onclick="changeAddr(this)"><?php echo htmlspecialchars(WCentre::address());?></td></tr>
<tr><th class="right">Location:</th>
<th onclick="queryLocation(this)" colspan="2"><?php echo WCentre::lat() . ' / ' . WCentre::lng();?></th></tr>
<tr><th class="right">E-mail:</th>
<td class="left" colspan="2" onclick="changeEmail(this)"><?php echo htmlspecialchars(WCentre::email());?></td></tr>
<tr><th class="right">Keywords:</th>
<td id='keywords' colspan="2" onclick="changeKeywords(this)"><?php echo htmlspecialchars(WCentre::keywords());?></td></tr>
<tr><th class="right">Currency:</th>
<td id='currency' align="center" colspan="2" onclick="changeCurrency(this)"><?php echo htmlspecialchars(WCentre::currencyId());?></td></tr>
<tr><th class="right">Capacity:</th>
<td id='capacity' align="right" colspan="2" onclick="changeCapacity(this)"><?php echo htmlspecialchars(WCentre::capacity());?></td></tr>
<?php
 $sgrps = WSGrp::groups(true, false, $centreId);
 if ($sgrps)
 {
  foreach ($sgrps as $sgrp)
  {
   $id = $sgrp['id'];
   $name = $sgrp['name'];
   $class = ($sgrp['active'] == '1') ? ' class="checked"' : '';
   echo "<tr><th class='left sgrp'>$name:</th>\n";
   echo "<td$class colspan='2' onclick='changeSgrp(this,$id,\"$name\")'></td></tr>";
  }
 }
 else
 {
  echo "<tr><td colspan='3'>" . DB::lastQuery() . "</td></tr>\n";
 }
?>
<tr><th class="right">Staff:</th><th colspan="2"><?php
echo '<a href="ctrf-' . $centreId . '/">' . WCentre::masterCount() . ' employees</a>';
?></th></tr>
<tr><th class="right">Resources:</th><th colspan="2"><?php
echo '<a href="ctrr-' . $centreId . '/">' . WCentre::matresCount() . '/' . WCentre::matcatCount() . ' resources</a>';
?></th></tr>
<tr><th class="right">Services:</th><th colspan="2"><?php
echo '<a href="ctrm-' . $centreId . '/">' . WCentre::serviceCount() . ' services</a>';
?></th></tr>
<tr><th class="right">Reviews:</th><th colspan="2"><?php
echo '<a href="ctrv-' . $centreId . '/">' . WCentre::reviewCount() . ' reviews</a>';
?></th></tr>
<tr><th class="right">Rate:</th><th colspan="2"><?php
if (WCentre::rateCount())
 echo number_format(WCentre::rateTotal(), 2) . ' ( <span class="small">' . WCentre::rateCount() . '</span> )';
?></th></tr>
</table>
<table style="display:block;float:left;"><tr><td>
<table class="main block">
<caption>Titles</caption>
<tr><th>Language</th><th>Title</th><th>Subtitle</th></tr>
<?php
$langs = Lang::map();
foreach ($langs as $lang => $Lang)
{
 $fields = PageAdm::db()->queryFields('com_centre_abc', 'title,subtitle', 'centre_id=' . $centreId . ' and abc_id=' . DB::str($lang));
 echo "<tr>\n";
 echo "<th>$lang</th>\n";
 echo "<td onclick='changeTitle(this,\"$lang\")'>" . htmlspecialchars($fields[0]) . "</td>\n";
 echo "<td onclick='changeSubtitle(this,\"$lang\")'>" . htmlspecialchars($fields[1]) . "</td>\n";
 echo "</tr>\n";
}
?></table>
</td></tr><tr><td>
<table class="main block">
<caption>Phone numbers</caption>
<tr><th>Nr</th><th>Phone number</th><th>Action</th></tr>
<?php
$records = PageAdm::db()->queryRecords(WCentre::TABLE_CENTRE_PHONE, 'serial,phone', 'centre_id=' . $centreId, 'serial');
if ($records)
{
 foreach ($records as $record)
 {
  $serial = $record[0];
  $phone = $record[1];
  $button = "<input type='button' value='Delete' onclick='deletePhone($serial,\"$phone\")'/>";
  $td1 = "<td onclick='changePhoneSerial($serial,\"$phone\")' align='center'>$serial</td>";
  $td2 = "<td id='phone-$serial' onclick='changePhoneNumber($serial)'>$phone</td>";
  echo "<tr>$td1$td2<th>$button</th></tr>\n";
 }
}
$edit = '<input id="phone-new" style="100%">';
$button = '<input type="button" value="Append" onclick="appendPhone()"/>';
echo "<tr><th colspan='2'>$edit</th><th>$button</th></tr>\n";
?></table>
</td></tr><tr><td>
<table class="main block">
<caption>Metro stations</caption>
<tr><th>Nr</th><th>Station name</th><th>Action</th></tr>
<?php
$records = PageAdm::db()->queryRecords(WCentre::TABLE_CENTRE_METRO, 'metro_id', 'centre_id=' . $centreId, 'metro_id');
if ($records)
{
 $nr = 0;
 foreach ($records as $record)
 {
  $nr++;
  $metro_id = $record[0];
  $title = PageAdm::db()->queryField('biz_menu_ter_metro_abc', 'title',
    'metro_id=' . $metro_id . ' and length(title)>0', 'case abc_id when \'en\' then 0 else 1 end');
  $button = "<input type='button' value='Delete' onclick='deleteMetro($metro_id,\"$title\")'/>";
  echo "<tr><th>$nr</th><th align='left'>$title</th><th>$button</th></tr>\n";
 }
}
$select = '<select id="metro-new" style="width:100%"><option></option>';
$records = PageAdm::db()->queryRecords('biz_menu_ter_metro a', 'id,(select title from biz_menu_ter_metro_abc where metro_id=a.id limit 1)', 'ter_id=' . WCentre::typeId() . ' and hidden is null', '2');
if ($records)
{
 foreach ($records as $record)
 {
  $metro_id = $record[0];
  $title = htmlspecialchars($record[1]);
  $select .= "<option value='$metro_id'>$title</option>\n";
 }
}
$select .= '</select>';
$button = '<input type="button" value="Append" onclick="appendMetro()"/>';
echo "<tr><th colspan='2'>$select</th><th>$button</th></tr>\n";
?></table>
</td></tr></table>
<table class="main block">
<caption>Opening hours</caption>
<tr><th>Day</th><th>Open</th><th>Close</th><th>Action</th></tr>
<?php
$days = array( 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su' );
for ($i = 1; $i <= 7; $i++)
{
 //$time = PageAdm::db()->queryFields(WCentre::TABLE_CENTRE_SCHED, 'open_min,close_min', "centre_id=$id and week_day=$i");
 $time = PageAdm::db()->queryFields(WCentre::TABLE_CENTRE_SCHED, 'open_min,close_min', array('centre_id' => $centreId, 'week_day' => $i));
 $class = ($i > 5) ? ' class="holiday"' : '';
 $day = $days[$i - 1];
 $open = $time ? Util::min2str($time[0]) : '';
 $close = $time ? Util::min2str($time[1]) : '';
 $td1 = "<td id='time-{$i}0' onclick='changeTime($i,0)'>$open</td>";
 $td2 = "<td id='time-{$i}1' onclick='changeTime($i,1)'>$close</td>";
 $button = ($i > 1) ? "<input type='button' value='Copy' onclick='copyTimes($i)'/>" : '';
 echo "<tr$class><th>$day</th>$td1$td2<th>$button</th><tr>\n";
}
?><tr><th colspan="4"><a href="ctrd-<?php echo $centreId; ?>/">Time schemes</a></th></tr></table>
<table class="main block">
<caption>Logo</caption>
<?php
$logo = PageAdm::db()->queryFields(WCentre::TABLE_CENTRE,
  'logo_filename,logo_width,logo_height,logo_size',
  'id=' . $centreId);
$attrs = $logo[0] ? (' src="' . WCentre::logoURI($centreId) . '"') : ' height="200"';
echo '<tr><th colspan="2"><img width="300"' . $attrs . '/></th></tr>';
echo '<tr><th class="right">Filename</th><td>' . ($logo[0] ? $logo[0] : '&nbsp;') . '</td></tr>';
echo '<tr><th class="right">Size</th><td>' . ($logo[0] ? ($logo[1] . 'x' . $logo[2] . '; ' . number_format($logo[3])) : '&nbsp;') . '</td></tr>';
?>
<tr>
<th><input type='button' value='Load from DB' onclick='loadLogoFromDB()'/>
<input type='button' value='Load from URI' onclick='loadLogoFromURI()'/></th>
<th><input type='button' value='Clear' onclick='clearLogo()'/></th>
</tr>
<tr><td colspan="2">
<form method='post' enctype='multipart/form-data'>
<input type='hidden' name='act' value='uploadLogo'/>
<input type='file' name='image' size='1' onchange='submit()'/>
</form>
</td></tr>
</table>
<hr/>
<table class="main memo" width="100%">
<caption>Nested locations</caption>
<colgroup><col width="50"><col width="100"><col width="300"><col><col width="50"></colgroup>
<tr><th>Nr</th><th>Type</th><th>Name</th><th>Address</th><th><input type='button' value='Clear' onclick='clearPlaces()'/></th></tr>
<?php
$records = PageAdm::db()->queryRecords(WCentre::TABLE_CENTRE_PLACE, 'serial,type,name,address', 'centre_id=' . $centreId, 'serial');
if ($records)
{
 foreach ($records as $record)
 {
  $serial = $record[0];
  $th1 = "<th class='right'>$serial</th>";
  $td2 = "<td class='left'>$record[1]</td>";
  $td3 = "<td class='left'>$record[2]</td>";
  $td4 = "<td class='left'>$record[3]</td>";
  $th5 = "<th><input type='button' value='Delete' onclick='deletePlace($serial)'/></th>";
  echo "<tr>$th1$td2$td3$td4$th5</tr>\n";
 }
}
?></table>
<hr/>
<table class="main" width="100%">
<caption>Description</caption>
<colgroup><col width="100"><col></colgroup>
<tr><th>Language</th><th>Text</th></tr>
<?php
$langs = Lang::map();
foreach ($langs as $lang => $Lang)
{
 echo '<tr><th>' . $Lang->title() . '</th>';
 $descr = PageAdm::db()->queryField(WCentre::TABLE_CENTRE . '_abc', 'descr', 'centre_id=' . $centreId . ' and abc_id=' . DB::str($lang));
 echo "<td>\n";
 PageAdm::echoTextArea("descr-$lang", $descr, "centre description ($lang)", 'changeDescr', 'descr', "lang=$lang");
 echo "</td></tr>\n";
}
?></table>
<hr/>
<!--?php echo '<pre>' . print_r(DB::queries(), true) . '</pre>';?-->
<table class="main" width="100%">
<caption>Images</caption>
<tr>
<th width="50">Nr</th>
<th width="300">Picture</th>
<th width="200">Filename</th>
<th width="100">Size</th>
<?php
 foreach ($langs as $lang => $Lang)
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) " . $Lang->title() . "</th>\n";
?>
</tr>
<?php
$fields = 'image,filename,size,width,height';
foreach ($langs as $lang => $Lang)
 $fields .= ',(select title from com_centre_img_abc where centre_id=a.centre_id and serial=a.serial and abc_id=\'' . $lang . '\')title_' . $lang;
for ($i = 1; $i <= 5; $i++)
{
 $where = "centre_id=$centreId and serial=$i";
 $record = PageAdm::db()->queryFields(WCentre::TABLE_CENTRE_IMG . ' a', $fields, $where, 'serial');
 $filename = $record ? $record[1] : 'No data';
 $img = ($record && $record[0]) ? "<img width='300' src='img/ctr-$centreId/$i/$filename'/>" : '';
 $size = $record ? ($record[3] . 'x' . $record[4] . '<br>' . number_format($record[2])) : '';
 $form =
   "<form method='post' enctype='multipart/form-data'>" .
   "<input type='hidden' name='act' value='uploadImage' />" .
   "<input type='hidden' name='nr' value='$i' />" .
   "<input type='file' name='image' size='1' onchange='submit()' />" .
   "</form>";
 $delete = $record ? "<input type='button' value='Delete' onclick='deleteImage($i)'/>" : '';
 echo "<tr><th rowspan='2'>$i</th><th rowspan='2'>$img</th><th>$filename</th><th>$size</th>\n";
 $fieldIndex = 4;
 foreach ($langs as $lang => $Lang)
  if ($record)
   echo "<td rowspan='2' onclick='changeImageTitle(this,$i,\"$lang\")'>" . htmlspecialchars($record[++$fieldIndex]) . "</td>\n";
  else
   echo "<th rowspan='2'></th>\n";
 echo "</tr>\n";
 echo "<tr><th>$form</th><th>$delete</th></tr>\n";
}
?></table>
<hr/>
<table class="main" width="100%">
<caption>File</caption>
<tr><td>
<?php PageAdm::echoTextArea("file", WCentre::file(), "centre file", 'changeFile', 'file');?>
</td></tr>
</table>
</body>
</html>
<?php
  return true;
 }
}

?>