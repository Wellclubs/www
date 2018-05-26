<?php
class PageAdmOfrs
{
 const TABLE = 'biz_offer';

 private static function processAct($act)
 {
  $table = self::TABLE;
  $entity = 'offer';
  switch ($act)
  {
  case 'createEntity' :
   PageAdm::createEntity($table, $entity, null, array('domain_id' => WDomain::id(), 'currency_id' => DB::str(WDomain::currencyId())));
   break;

  case 'deleteEntity' :
   PageAdm::deleteEntity($table, $entity);
   break;

  case 'changeSerial' :
   PageAdm::changeSerial($table, $entity);
   break;

  case 'changeName' :
   PageAdm::changeName($table, $entity);
   break;

  case 'changeFlag' :
   $flag = HTTP::param('flag');
   $flags = array('C' => 'ask_centre', 'B' => 'ask_brand', 'D' => 'ask_start_date');
   $field = Util::item($flags, $flag);
   PageAdm::changeFlag($table, $entity, $field);
   break;

  case 'changeField' :
   PageAdm::changeField($table, $entity);
   break;

  case 'changeTitle' :
   PageAdm::changeTitle($table, 'offer_id', $entity);
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
  if (is_null(WDomain::id()))
  {
   echo "Unknown domain: " . WDomain::name();
   exit;
  }
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    exit;
  }
?>
<!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
table.main th.title { text-align:left;padding:0 5px; }
table.main th a { display:block; }
</style>
<script>
var entity='offer';
function createEntity()
{
 A.createEntity(entity);
}
function deleteEntity(id)
{
 A.deleteEntity(id,entity,'','row-'+id);
}
function changeSerial(node,id)
{
 A.changeSerial(node,id,entity);
 document.location.reload(true);
}
function changeFlag(node,id,flag)
{
 var name={'C':'Ask Centre','B':'Ask Brand','D':'Ask Start Date'}[flag];
 var oldValue=(node.className=='checked');
 var flagTitle='the flag "'+name+'" for '+entity+' '+id;
 var text=(oldValue?'Reset ':'Set ')+flagTitle;
 if(!confirm(text+'?'))
  return;
 var value=oldValue?'':'1';
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=changeFlag&id='+id+'&flag='+flag+'&value='+value,false);
 req.send(null);
 var error='Error changing '+flagTitle+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.className=oldValue?'':'checked';
}
function changeName(node,id)
{
 A.changeName(node,id,entity);
}
function changeField(node,id,field)
{
 A.changeField(node,id,entity,field);
}
function changeTitle(node,id,lang)
{
 A.changeTitle(node,id,lang,entity);
}
function hideEntity(button,id,hide)
{
 A.hideEntity(button,id,hide,entity);
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='200'>Name</th>
<th width='50'>Centre</th>
<th width='50'>Brand</th>
<th width='50'>Start</th>
<th width='50'>Period</th>
<th width='50'>Price</th>
<th width='50'>Cur.</th>
<?php
 $langs = Lang::map();
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo "<th width=\"150\" class=\"title\">" . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
<th width='1' colspan='2'><input type='button' value='Create new' onclick='createEntity()'/></th>
</tr>
<?php
$rowID = 1;
$table = self::TABLE;
$fields = 'id,serial,name,ask_centre,ask_brand,ask_start_date,period_days,price,currency_id,hidden';
foreach ($langs as $lang => $Lang)
{
 $fields .= ",(select title from {$table}_abc where offer_id=a.id and abc_id='$lang')title_$lang";
}
$where = 'domain_id=' . WDomain::id();

$records = PageAdm::db()->queryArrays("$table a", $fields, $where, 'serial,id');
if ($records)
{
 foreach ($records as $record)
 {
  $id = $record['id'];
  $serial = $record['serial'];
  $name = $record['name'];
  $askCentre = $record['ask_centre'];
  $askBrand = $record['ask_brand'];
  $askStartDate = $record['ask_start_date'];
  $periodDays = $record['period_days'];
  $price = $record['price'];
  $currencyId = $record['currency_id'];
  $hidden = $record['hidden'] != '';
  $class = $hidden ? (" class='hidden'") : '';
  echo "<tr id='row-$id'$class>\n";
  echo "<th class='right'>$id</th>\n";
  echo "<td class='right' onclick='changeSerial(this,$id)'>$serial</td>\n";
  echo "<td class='left' onclick='changeName(this,$id)'>$name</td>\n";
  echo "<td" . ($askCentre ? " class='checked'" : null) . " onclick='changeFlag(this,$id,\"C\")'></td>\n";
  echo "<td" . ($askBrand ? " class='checked'" : null) . " onclick='changeFlag(this,$id,\"B\")'></td>\n";
  echo "<td" . ($askStartDate ? " class='checked'" : null) . " onclick='changeFlag(this,$id,\"D\")'></td>\n";
  echo "<td class='right' onclick='changeField(this,$id,\"period_days\")'>$periodDays</td>\n";
  echo "<td class='right' onclick='changeField(this,$id,\"price\")'>$price</td>\n";
  echo "<th>$currencyId</th>\n";
  foreach ($langs as $lang => $title)
  {
   $value = htmlspecialchars($record["title_$lang"]);
   echo "<td class='left' onclick='changeTitle(this,$id,\"$lang\")'>$value</td>\n";
  }
  $value = $hidden ? 'Show' : 'Hide';
  $arg = $hidden ? 'false' : 'true';
  echo "<td><input type='button' value='$value' onclick='hideEntity(this,$id,$arg)'/></td>\n";
  echo "<td><input type='button' value='Delete' onclick='deleteEntity($id)'/></td>\n";
  echo "</tr>\n";
 }
}
?></table>
</body>
</html>
<?php
return true;
}

}

?>