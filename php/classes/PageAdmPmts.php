<?php
class PageAdmPmts
{
 const TABLE = 'com_payment';

 private static $mbr_id = null;

 private static function processAct($act)
 {
  $table = self::TABLE;
  $entity = 'payment';
  switch ($act)
  {
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
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    exit;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
table.main th.title { text-align:left;padding:0 5px; }
table.main th a { display:block; }
</style>
<script>
var entity='payment';
function hideEntity(button,id,hide)
{
 A.hideEntity(button,id,hide,entity);
}
function selectMember(id)
{
 var uri=''+document.location;
 var pos=uri.indexOf('?');
 if(pos>=0)
  uri=uri.substr(0,pos);
 document.location=uri+'?mbr='+id;
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop();
echo "<span>Select a <a href='mbrs/'>member</a>:</span><select onchange='selectMember(value)'>\n";
echo "<option></option>\n";
$mbrs = PageAdm::db()->queryRecords('biz_member a', 'client_id,(select trim(concat(firstname,\' \',lastname)) from biz_client where id=a.client_id)', '', 'client_id');
foreach ($mbrs as $mbr)
{
 $id = $mbr[0];
 $name = $mbr[1];
 $selected = ($id == self::$mbr_id) ? ' selected' : '';
 echo "<option value='$id'$selected>$name</option>\n";
}
echo "</select>\n";

?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<colgroup>
<col width="50">
<col width="50">
<?php if (!self::$mbr_id) { ?>
<col width="100">
<col width="100">
<?php } ?>
<col width="100">
<col width="200">
<col width="100">
<col width="100">
<col width="100">
<col width="50">
<col width="100">
<col width="40">
</colgroup>
<tr>
<th>Nr</th>
<th>Id</th>
<?php if (!self::$mbr_id) { ?>
<th>Member</th>
<th>E-mail</th>
<?php } ?>
<th>Pay Date</th>
<th>Offer Title</th>
<th>Centre</th>
<th>Brand</th>
<th>Start Date</th>
<th>Days</th>
<th>Price</th>
<th></th>
</tr>
<?php
 $nr = 0;
 $fields = 'id,member_id,offer_id,client_name,client_email,pay_date,offer_title,' .
   'centre_id, centre_name, brand_id, brand_name,' .
   'start_date,offer_days,offer_price,offer_currency_id,hidden';
 $where = self::$mbr_id ? ('member_id=' . self::$mbr_id) : null;
 $records = PageAdm::db()->queryRecords(self::TABLE, $fields, $where, 'id desc');
 if ($records)
 {
  foreach ($records as $record)
  {
   $id = $record[0];
   $memberId = $record[1];
   $offerId = $record[2];
   $clientName = htmlspecialchars($record[3]);
   $clientEmail = htmlspecialchars($record[4]);
   $payDate = $record[5];
   $offerTitle = htmlspecialchars($record[6]);
   $centreId = $record[7];
   $centreName = htmlspecialchars($record[8]);
   $brandId = $record[9];
   $brandName = htmlspecialchars($record[10]);
   $startDate = $record[11];
   $offerDays = $record[12];
   $offerPrice = $record[13];
   $offerCurrencyId = htmlspecialchars($record[14]);
   $hidden = $record[15] != '';
   $class = $hidden ? (" class='hidden'") : '';
   echo "<tr id='row-$id'$class>\n";
   echo "<th>" . ++$nr . "</th>\n";
   echo "<th>$id</th>\n";
   if (!self::$mbr_id)
   {
    echo "<td>$memberId, $clientName</td>\n";
    echo "<td>$clientEmail</td>\n";
   }
   echo "<th>$payDate</th>\n";
   echo "<th>$offerId, $offerTitle</th>\n";
   echo $centreId ? "<th><a href=ctr-$centreId/>$centreId, $centreName</a></th>\n" : "<th>&nbsp;</th>\n";
   echo $brandId ? "<th><a href=ctr-$brandId/>$brandId, $brandName</a></th>\n" : "<th>&nbsp;</th>\n";
   echo "<td>$startDate</td>\n";
   echo "<td>$offerDays</td>\n";
   echo "<td>$offerPrice $offerCurrencyId</td>\n";
   $value = $hidden ? 'Show' : 'Hide';
   $arg = $hidden ? 'false' : 'true';
   echo "<td><input type='button' value='$value' onclick='hideEntity(this,$id,$arg)'/></td>\n";
   echo "</tr>\n";
  }
 }
 else
  echo "<tr><th colspan='" . (10 + (!self::$mbr_id ? 2 : 0)) . "'>No data</th></tr>\n";
?></table>
</body>
</html>
<?php
  return true;
 }

}

?>