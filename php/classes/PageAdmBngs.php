<?php

/**
 * Description of PageAdmBngs
 */
class PageAdmBngs
{
 private static function processAct($act)
 {
  $table = WPurchase::TABLE_BOOK;
  $entity = 'booking';
  switch ($act)
  {
  case 'changeField' :
   PageAdm::changeField($table, $entity);
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return false;
 }

 public static function showPage()
 {
  $ref = Util::intval(HTTP::get('ref'));
  $days = max(array(HTTP::paramInt('days', 0), 0));
  $ctr_id = Util::intval(HTTP::get('ctr'));
  $clt_id = Util::intval(HTTP::get('clt'));
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
table.filter th,table.filter td { padding:2px; }
table.filter input.text { border:0;padding:0; }
</style>
<script>
var entity='booking';
function filterEdit(cell,text,uri0,uri1)
{
 if(cell.edit)
  return;
 var saved=cell.innerHTML;
 var edit=document.createElement('input');
 edit.className='text '+cell.className;
 edit.style.height=''+(cell.clientHeight-4)+'px';
 edit.style.width=''+(cell.clientWidth-4)+'px';
 edit.onblur=function()
 {
  cell.edit=null;
  cell.innerHTML=saved;
 }
 edit.onkeydown=function(event)
 {
  if(event.keyCode==27)
   edit.onblur();
  else if(event.keyCode==13)
   document.location=edit.value?uri1+edit.value:uri0;
 }
 edit.value=text;
 cell.innerHTML='';
 cell.appendChild(edit);
 cell.edit=edit;
 edit.select();
 edit.focus();
}
</script>
</head>
<?php
 PageAdm::instance()->showBodyTop();
 $cells1 = array();
 $cells2 = array();
 // Days
 $cells1[] = '<th>Days</th>';
 $uri0 = HTTP::uriWithoutParam('days');
 $uri1 = HTTP::addParam($uri0, 'days', '');
 $cells2[] = "<td class='center' onclick='filterEdit(this,\"$days\",\"$uri0\",\"$uri1\")'>$days</td>";
 // Centre
 $cells1[] = '<th>Venue</th>';
 $uri0 = HTTP::uriWithoutParam('ctr');
 $uri1 = HTTP::addParam($uri0, 'ctr', '');
 $title = $ctr_id ? ($ctr_id . ' ' . htmlspecialchars(WCentre::getTitle($ctr_id))) : '';
 $cells2[] = "<td onclick='filterEdit(this,\"$ctr_id\",\"$uri0\",\"$uri1\")'>$title</td>";
 // Client
 $cells1[] = '<th>Customer</th>';
 $uri0 = HTTP::uriWithoutParam('clt');
 $uri1 = HTTP::addParam($uri0, 'clt', '');
 $title = $clt_id ? ($clt_id . ' ' . htmlspecialchars(WClient::getClientName($clt_id))) : '';
 $cells2[] = "<td onclick='filterEdit(this,\"$clt_id\",\"$uri0\",\"$uri1\")'>$title</td>";
 // Domain
 $all_domains = !!HTTP::get('all_domains');
 $uri = HTTP::uriWithoutParam('all_domains');
 if ($all_domains)
 {
  $cells1[] = "<td class='center'><a href='$uri'>Current domain</a></td>";
  $cells2[] = '<th>All domains</th>';
 }
 else
 {
  $uri = HTTP::addParam($uri, 'all_domains', '1');
  $cells1[] = '<th>Current domain</th>';
  $cells2[] = "<td class='center'><a href='$uri'>All domains</a></td>";
 }
 // Mode
 $cells1[] = '<th>Mode</th>';
 $book_type = strtoupper(HTTP::get('type'));
 $uri = HTTP::uriWithoutParam('type');
 $mode0 = 'Any';
 $mode1 = '[ P ]';
 $mode2 = '[ B ]';
 if ($book_type == 'P')
 {
  $uri2 = HTTP::addParam($uri, 'type', 'B');
  $cells2[] = "<td class='center'><a href='$uri'>$mode0</a>&nbsp;$mode1&nbsp;<a href='$uri2'>$mode2</a></td>";
 }
 else if ($book_type == 'B')
 {
  $uri1 = HTTP::addParam($uri, 'type', 'P');
  $cells2[] = "<td class='center'><a href='$uri'>$mode0</a>&nbsp;<a href='$uri1'>$mode1</a>&nbsp;$mode2</td>";
 }
 else
 {
  $book_type = null;
  $uri1 = HTTP::addParam($uri, 'type', 'P');
  $uri2 = HTTP::addParam($uri, 'type', 'B');
  $cells2[] = "<td class='center'>$mode0&nbsp;<a href='$uri1'>$mode1</a>&nbsp;<a href='$uri2'>$mode2</a></td>";
 }
 // Mode
 $cells1[] = '<th>Status</th>';
 $status = strtolower(HTTP::get('status'));
 $uri = HTTP::uriWithoutParam('status');
 $status0 = 'Any';
 $status1 = '[a]';
 $status2 = '[c]';
 $status3 = '[d]';
 $status4 = '[&nbsp;]';
 if ($status == 'a')
 {
  $uri2 = HTTP::addParam($uri, 'status', 'c');
  $uri3 = HTTP::addParam($uri, 'status', 'd');
  $uri4 = HTTP::addParam($uri, 'status', 'n');
  $cells2[] = "<td class='center'><a href='$uri'>$status0</a>&nbsp;$status1&nbsp;<a href='$uri2'>$status2</a>" .
    "&nbsp;<a href='$uri3'>$status3</a>&nbsp;<a href='$uri4'>$status4</a></td>";
 }
 else if ($status == 'c')
 {
  $uri1 = HTTP::addParam($uri, 'status', 'a');
  $uri3 = HTTP::addParam($uri, 'status', 'd');
  $uri4 = HTTP::addParam($uri, 'status', 'n');
  $cells2[] = "<td class='center'><a href='$uri'>$status0</a>&nbsp;<a href='$uri1'>$status1</a>" .
    "&nbsp;$status2&nbsp;<a href='$uri3'>$status3</a>&nbsp;<a href='$uri4'>$status4</a></td>";
 }
 else if ($status == 'd')
 {
  $uri1 = HTTP::addParam($uri, 'status', 'a');
  $uri2 = HTTP::addParam($uri, 'status', 'c');
  $uri4 = HTTP::addParam($uri, 'status', 'n');
  $cells2[] = "<td class='center'><a href='$uri'>$status0</a>&nbsp;<a href='$uri1'>$status1</a>" .
    "&nbsp;<a href='$uri2'>$status2</a>&nbsp;$status3&nbsp;<a href='$uri4'>$status4</a></td>";
 }
 else if ($status == 'n')
 {
  $uri1 = HTTP::addParam($uri, 'status', 'a');
  $uri2 = HTTP::addParam($uri, 'status', 'c');
  $uri3 = HTTP::addParam($uri, 'status', 'd');
  $cells2[] = "<td class='center'><a href='$uri'>$status0</a>&nbsp;<a href='$uri1'>$status1</a>" .
    "&nbsp;<a href='$uri2'>$status2</a>&nbsp;<a href='$uri3'>$status3</a>&nbsp;$status4</td>";
 }
 else
 {
  $status = null;
  $uri1 = HTTP::addParam($uri, 'status', 'a');
  $uri2 = HTTP::addParam($uri, 'status', 'c');
  $uri3 = HTTP::addParam($uri, 'status', 'd');
  $uri4 = HTTP::addParam($uri, 'status', 'n');
  $cells2[] = "<td class='center'>$status0&nbsp;<a href='$uri1'>$status1</a>&nbsp;<a href='$uri2'>$status2</a>" .
    "&nbsp;<a href='$uri3'>$status3</a>&nbsp;<a href='$uri4'>$status4</a></td>";
 }
 // Ref
 $cells1[] = '<th>Book Ref ID</th>';
 $uri0 = HTTP::uriWithoutParam('ref');
 $uri1 = HTTP::addParam($uri0, 'ref', '');
 $cells2[] = "<td class='center' onclick='filterEdit(this,\"$ref\",\"$uri0\",\"$uri1\")'>$ref</td>";
 // Filter
 echo "<table class='main filter'><tr>\n";
 foreach ($cells1 as $cell)
  echo "$cell\n";
 echo "</tr><tr>\n";
 foreach ($cells2 as $cell)
  echo "$cell\n";
 echo "</tr></table>\n";
 // Records
 $table = WPurchase::TABLE_BOOK . ' a';
 $fields = 'a.*';
 if ($ref || $all_domains)
  $fields .= ',(select name from biz_domain where id=a.domain_id)domain_name';
 if ($ref || !$ctr_id)
  $fields .= ',(select name from com_centre where id=a.centre_id)centre_name';
 $fields .= ',(select promo_code from dsc_cmp_clnt where id=a.cmp_clnt_id)promo_code';
 if ($ref)
 {
  $id = WPurchase::decodeRefId($ref);
  $where = $id ? "id=$id" : 'id is null';
  if (!$id)
   $ref = null;
  $records = PageAdm::db()->queryArrays($table, $fields, $where, null, 1);
 }
 else
 {
  $where = "created>=date_sub(current_date,interval $days day)";
  if ($ctr_id)
   $where .= " and centre_id=$ctr_id";
  if ($clt_id)
   $where .= " and client_id=$clt_id";
  if (WDomain::ok() && !$all_domains)
   $where .= ' and domain_id=' . WDomain::id();
  if ($book_type)
   $where .= " and book_type_id='$book_type'";
  if ($status)
   $where .= ($status == 'n') ? " and status is null" : " and status='$status'";
  $limit = HTTP::paramInt('limit', 50);
  $offset = PageAdm::echoPageNav($table, $where, $limit);
  $records = PageAdm::db()->queryArrays($table, $fields, $where, 'id desc', $limit, $offset);
 }
 //echo Base::htmlComment(DB::lastQuery()) . "\n";

 echo "<h1>" . PageAdm::title() . "</h1>\n";
 echo "<table class='main' cellspacing='0'>\n";
 $colcount = 3;
 $cols = "<col width='50'><col width='100'><col width='100'>";
 $hdrs = "<th>Id</th><th>Booked</th><th>Ref ID</th>";
 if ($ref || $all_domains)
 {
  $colcount++;
  $cols .= "<col width='100'>";
  $hdrs .= "<th>Domain</th>";
 }
 if ($ref || !$clt_id)
 {
  $colcount += 3;
  $cols .= "<col width='50'><col width='200'><col width='150'>";
  $hdrs .= "<th colspan='3'>Customer</th>";
 }
 $colcount += 6;
 $cols .= "<col width='100'><col width='50'><col width='50'><col width='50'><col width='50'><col width='50'>";
 $hdrs .= "<th>Promo code</th><th>Value listed</th><th>Value paid</th><th>Disc.</th><th>Tax</th><th>Com.</th>";
 if ($ref || !$book_type)
 {
  $colcount++;
  $cols .= "<col width='50'>";
  $hdrs .= "<th>Mode</th>";
 }
 if ($ref || !$status)
 {
  $colcount++;
  $cols .= "<col width='50'>";
  $hdrs .= "<th>Status</th>";
 }
 if ($ref || !$ctr_id)
 {
  $colcount += 2;
  $cols .= "<col width='50'><col width='200'>";
  $hdrs .= "<th colspan='2'>Venue</th>";
 }
 $colcount += 3;
 $cols .= "<col width='100'><col width='300'><col width='100'>";
 $hdrs .= "<th>Booking date/time</th><th>Description</th><th>Answer date/time</th>";
 echo "<colgroup>$cols</colgroup>\n";
 echo "<tr>$hdrs</tr>\n";
 if ($records)
  foreach ($records as $record)
  {
   $id = $record['id'];
   $refId = WPurchase::encodeRefId($id);
   $created = Util::datetime2str(DB::str2datetime($record['created']));
   echo "<tr id='row-$id'>\n";
   echo "<th>$id</th>\n";
   echo "<th>$created</th>\n";
   echo "<th>$refId</th>\n";
   if ($ref || $all_domains)
   {
    $domain = htmlspecialchars($record['domain_name']);
    echo "<td class='left'>$domain</td>\n";
   }
   if ($ref || !$clt_id)
   {
    $clt = $record['client_id'];
    $cltName = htmlspecialchars($record['client_name']);
    $phone = $record['phone'];
    echo "<th><a href='clt-$clt/'>$clt</a></th>\n";
    echo "<td class='left'>$cltName</td>\n";
    echo "<td class='right'>$phone</td>\n";
   }
   $cmp_clnt_id = $record['cmp_clnt_id'];
   $promo_code = $record['promo_code'];
   echo "<td class='center'>$cmp_clnt_id<br>$promo_code</td>\n";
   $price = $record['price'];
   $fact = $record['fact'];
   $qty = $record['qty'];
   $total = $record['total'];
   $disc = '';
   if ($price && ($fact != $price))
   {
    $discVal = $price - $fact;
    $discPrc = round($discVal * 100 / $price);
    $disc = "<small>$discPrc&nbsp;%</small><br>$discVal";
   }
   $taxPrc = Util::intval($record['ctr_tax_prc']);
   $taxVal = $taxPrc ? DB::money(ceil($total * $taxPrc) / 100) : '';
   $comPrc = Util::intval($record['ctr_com_prc']);
   $comVal = $comPrc ? DB::money(ceil(($total - $taxVal) * $comPrc) / 100) : '';
   if ($qty > 1)
   {
    $price = "$price<br><small>x&nbsp;$qty&nbsp;=</small>";
    $fact = "$fact<br><small>" . DB::money($fact * $qty) . "</small>";
   }
   echo "<td class='right'>$price</td>\n";
   echo "<td class='right'>$fact</td>\n";
   echo "<td class='right'>$disc</td>\n";
   echo "<td class='right'>$taxPrc&nbsp;%<br><small>$taxVal</small></td>\n";
   echo "<td class='right'>$comPrc&nbsp;%<br><small>$comVal</small></td>\n";
   if ($ref || !$book_type)
   {
    $type = $record['book_type_id'];
    $mode = ($type == 'P') ? 'Payment' : (($type == 'B') ? 'Booking' : $type);
    echo "<th class='center'>$mode</th>\n";
   }
   if ($ref || !$status)
   {
    $s = $record['status'];
    $st = ($s == 'a') ? 'Authorized' : (($s == 'c') ? 'Cancelled' : (($s == 'd') ? 'Declined' : $s));
    echo "<th class='center'><small>$st</small></th>\n";
   }
   if ($ref || !$ctr_id)
   {
    $ctr = $record['centre_id'];
    $ctrName = htmlspecialchars($record['centre_name']);
    echo "<th><a href='ctr-$ctr/'>$ctr</a></th>\n";
    echo "<td class='left'>$ctrName</td>\n";
   }
   $datetime = Util::date2str(DB::str2date($record['book_date'])) . ' ' . Util::min2str($record['book_time']);
   $descr = htmlspecialchars($record['descr']);
   echo "<td class='center'>$datetime</td>\n";
   echo "<td>$descr</td>\n";
   $answered = $record['answered'];
   if ($answered)
    $answered = Util::datetime2str(DB::str2datetime($answered));
   echo "<td class='center'>$answered</td>\n";
   echo "</tr>\n";
  }
 else
  echo "<tr><th colspan='$colcount'>No data</th></tr>\n";
 echo "</table>\n";
?>
</body>
</html>
<?php
  return true;
 }
}

?>
