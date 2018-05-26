<?php

/**
 * Description of PageAdmBnd
 */
class PageAdmBnd
{
 private static function processAct($brandId, $act)
 {
  $table = WBrand::TABLE_BRAND;
  $entity = 'brand';
  switch ($act)
  {
  case 'changeMember' :
   $memberId = DB::str(HTTP::get('member'));
   $client = PageAdm::db()->queryField(WMember::TABLE_MEMBER, 'client_id', 'client_id=' . $memberId);
   if (!$client)
    echo 'Invalid member id: ' . $memberId;
   else if (!PageAdm::db()->modifyField($table, 'member_id', 'i', $client, "id=$brandId"))
    echo 'Error changing brand owner member: ' . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeName' :
   $_REQUEST['id'] = $brandId;
   PageAdm::changeName($table, $entity);
   break;

  case 'changeEmail' :
   $_REQUEST['id'] = $brandId;
   PageAdm::changeEmail($table, $entity);
   break;

  case 'changeURI' :
   $uri = HTTP::param('uri');
   if (PageAdm::db()->queryField($table, 'id', "id<>$brandId and uri=" . DB::str($uri)))
    echo 'This URI is already used for another brand';
   else if (!PageAdm::db()->modifyField($table, 'uri', 's', $uri, "id=$brandId"))
    echo "Error changing brand $brandId URI to '$uri': " . DB::lastQuery();
   else
    echo 'OK';
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'brand_id', $entity, $brandId);
   break;

  case 'createLevel' :
   PageAdm::createEntity('com_level', 'level', null, array('brand_id' => $brandId));
   break;

  case 'deleteLevel' :
   $id = intval(HTTP::param('id'));
   if (intval(PageAdm::db()->queryField('com_master', 'count(*)', "level_id=$id")) > 0)
    echo "Level $id has some masters linked";
   else if (intval(PageAdm::db()->queryField(WService::TABLE_TIP, 'count(*)', "level_id=$id")) > 0)
    echo "Level $id has some services linked";
   else
    PageAdm::deleteEntity('com_level', 'level', $id);
   break;

  case 'changeLevelSerial' :
   PageAdm::changeSerial('com_level', 'level');
   break;

  case 'changeLevelName' :
   PageAdm::changeName('com_level', 'level', "brand_id=$brandId");
   break;

  case 'changeLevelTitle' :
   PageAdm::changeTitle('com_level', 'level_id', 'level');
   break;

  case 'uploadLogo' :
   if (!WBrand::uploadLogo($brandId))
    return Base::addError('Error uploading a logo image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'loadLogoFromDB' :
   $id = HTTP::param('id');
   if (WBrand::uploadLogoFromDB($brandId, $id))
     echo 'OK';
    else
     echo "Error loading a logo image for centre $brandId to the database: " . DB::lastQuery();
   break;

  case 'loadLogoFromURI' :
   $uri = HTTP::param('uri');
   if (WBrand::uploadLogoFromURI($brandId, $uri))
     echo 'OK';
    else
     echo "Error loading a logo image for brand $brandId to the database: " . DB::lastQuery();
   break;

  case 'clearLogo' :
   if (!WBrand::clearLogo($brandId))
    echo 'Error clearing a logo image';
   else
    echo 'OK';// . DB::lastQuery();
   break;

  case 'uploadImage' :
   $serial = HTTP::param('nr');
   if (!WBrand::createGallery()->uploadImage($serial))
    return Base::addError('Error uploading an image: ' . DB::lastQuery());
   header('Location: ' . Base::loc());
   exit;

  case 'changeImageTitle' :
   $serial = HTTP::param('id');
   $lang = HTTP::param('lang');
   $title = HTTP::param('title');
   if (Lang::setDBValue($title, $table . '_img_abc', null, array('brand_id' => $brandId, 'serial' => $serial), $lang))
    echo 'OK';
   else
    echo "Error changing $entity $id language '$lang' title to '$title': " . DB::lastQuery();
   break;

  case 'deleteImage' :
   $serial = HTTP::get('nr');
   if (!WBrand::createGallery()->deleteImage($serial))
    echo 'Error deleting an image';
   else
    echo 'OK';
   break;

  case 'changeDescr' :
   $lang = HTTP::param('lang');
   $table = WBrand::TABLE_BRAND . '_abc';
   $where = array('brand_id' => $brandId, 'abc_id' => DB::str($lang));
   PageAdm::changeText($table, $where, 'brand description', 'descr');
   break;

  case 'changeFile' :
   PageAdm::changeText($table, 'id=' . $brandId, 'brand file', 'file');
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  if (!WBrand::initCurrent(Base::index()))
   return false;
  $brandId = WBrand::id();
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($brandId, $_REQUEST['act']))
    return true;
  }
  $langs = Lang::map();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
.block { display:block;float:left;margin:0 10px 10px 0; }
.block th { padding:1px 4px; }
.block td { padding:1px 4px; }
</style>
<script>
var entity='brand';
function changeMember()
{
 var newValue=prompt('Input a new owner member id:');
 if(newValue==null)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeMember&member='+newValue,false);
 req.send(null);
 var error='Error changing the '+entity+' owner member on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeName(node)
{
 A.changeName(node,null,entity);
}
function changeEmail(node)
{
 A.changeEmail(node,null,entity);
}
function changeURI()
{
 var anchor=el('uri');
 var oldValue=decodeHTML(anchor.innerHTML);
 var newValue=prompt('Input a new brand URI:',oldValue);
 if((newValue==null)||(newValue==oldValue))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeURI&uri='+newValue,false);
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
function changeTitle(node,lang)
{
 A.changeTitle(node,null,lang,entity);
}
function createLevel()
{
 A.createEntity('level','createLevel')
}
function deleteLevel(id)
{
 A.deleteEntity(id,'level','deleteLevel');
}
function changeLevelSerial(node,id)
{
 A.changeSerial(node,id,'level','changeLevelSerial');
}
function changeLevelName(node,id)
{
 A.changeName(node,id,'level','changeLevelName');
}
function changeLevelTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,'level','changeLevelTitle');
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
function changeImageTitle(node,nr,lang)
{
 A.changeTitle(node,nr,lang,'image','changeImageTitle');
}
function deleteImage(nr)
{
 if(!confirm('Delete image '+nr+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=deleteImage&nr='+nr,false);
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
<table style="display:block;float:left;"><tr><td>
<table class="main block">
<caption>General</caption>
<tr><th class="right">Owner:</th><th><?php
$memberId = WBrand::memberId();
$name = WClient::getClientName($memberId);
echo '<a href="mbr-' . $memberId . '/"><i>' . $memberId . ', ' . $name . '</i></a>';
?></th><th><input type="button" value="Change" onclick="changeMember()"/></th></tr>
<tr><th class="right">Name:</th>
<td class="left" colspan="2" onclick="changeName(this)"><?php echo htmlspecialchars(WBrand::name());?></td></tr>
<tr><th class="right">URI:</th>
<th><a id="uri" target='_blank' href="<?php echo WBrand::uri();?>"><?php echo WBrand::uri();?></a></th>
<th><input type="button" value="Change" onclick="changeURI()"/></th></tr>
<tr><th class="right">E-mail:</th>
<td class="left" colspan="2" onclick="changeEmail(this)"><?php echo htmlspecialchars(WBrand::email());?></td></tr>
<tr><th class="right">Centres:</th>
<th colspan="2"><?php
echo "<a href=\"ctrs/?bnd=$brandId\">" . WBrand::count() . ' / ' . WBrand::total() . '</a>';
?></th></tr>
</table>
</td></tr><tr><td>
<table class="main block">
<caption>Titles</caption>
<tr><th>Language</th><th>Title</th></tr>
<?php
$langs = Lang::map();
foreach ($langs as $lang => $Lang)
{
 $field = PageAdm::db()->queryField('com_brand_abc', 'title', 'brand_id=' . $brandId . ' and abc_id=' . DB::str($lang));
 echo "<tr>\n";
 echo "<th>$lang</th>\n";
 echo "<td onclick='changeTitle(this,\"$lang\")'>" . htmlspecialchars($field) . "</td>\n";
 echo "</tr>\n";
}
?></table>
</td></tr></table>
<table class="main block">
<caption>Levels</caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='200'>Name</th>
<?php
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
<th><input type="button" value="Create" onclick="createLevel()"/></th>
</tr>
<?php
 $fields = 'id,serial,name';
 foreach ($langs as $lang => $title)
  $fields .= ',(select title from com_level_abc where level_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
 $levels = PageAdm::db()->queryRecords('com_level a', $fields, 'brand_id=' . $brandId, 'serial,id');
 if ($levels)
  foreach ($levels as $level)
  {
   $id = $level[0];
   $name = htmlspecialchars($level[2]);
   echo "<tr>\n";
   echo "<th class='right'>$id</th>\n";
   echo "<td onclick='changeLevelSerial(this,$id)'>$level[1]</td>\n";
   echo "<td onclick='changeLevelName(this,$id)' class='lang'>$name</td>\n";
   $i = 3;
   foreach ($langs as $lang => $title)
   {
    $value = htmlspecialchars($level[$i++]);
    echo "<td onclick='changeLevelTitle(this,$id,\"$lang\")'>$value</td>\n";
   }
   echo "<th><input type='button' value='Delete' onclick='deleteLevel($id)'/></th>\n";
   echo "</tr>\n";
  }
?></table>
<table class="main block">
<caption>Logo</caption>
<?php
$logo = PageAdm::db()->queryFields(WBrand::TABLE_BRAND,
  'logo_filename,logo_width,logo_height,logo_size',
  'id=' . $brandId);
$attrs = $logo[0] ? (' src="' . WBrand::logoURI($brandId) . '"') : ' height="200"';
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
<table class="main" width="100%">
<caption>Description</caption>
<colgroup><col width="100"><col></colgroup>
<tr><th>Language</th><th>Text</th></tr>
<?php
$langs = Lang::map();
foreach ($langs as $lang => $Lang)
{
 echo '<tr><th>' . $Lang->title() . '</th>';
 $descr = PageAdm::db()->queryField(WBrand::TABLE_BRAND . '_abc', 'descr', 'brand_id=' . $brandId . ' and abc_id=' . DB::str($lang));
 echo "<td>\n";
 PageAdm::echoTextArea("descr-$lang", $descr, "centre description ($lang)", 'changeDescr', 'descr', "lang=$lang");
 echo "</td></tr>\n";
}
?></table>
<hr/>
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
 $fields .= ',(select title from com_brand_img_abc where brand_id=a.brand_id and serial=a.serial and abc_id=\'' . $lang . '\')title_' . $lang;
for ($i = 1; $i <= 5; $i++)
{
 $where = "brand_id=$brandId and serial=$i";
 $record = PageAdm::db()->queryFields(WBrand::TABLE_BRAND_IMG . ' a', $fields, $where, 'serial');
 $filename = $record ? $record[1] : 'No data';
 $img = ($record && $record[0]) ? "<img width='300' src='img/bnd-$brandId/$i/$filename'/>" : '';
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
<?php PageAdm::echoTextArea("file", WBrand::file(), "brand file", 'changeFile', 'file');?>
</td></tr>
</body>
</html>
<?php
  return true;
 }
}

?>
