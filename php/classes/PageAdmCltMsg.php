<?php

/**
 * Description of PageAdmCltMsg
 */
class PageAdmCltMsg
{
 private static function processAct($act)
 {
  $table = 'biz_mail';
  $messageId = Base::index();
  switch ($act)
  {
  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  if (!Base::index())
   return false;
  $row = PageAdm::db()->queryPairs('biz_mail', 'client_id,sent,dir,subject,message,hidden', 'id=' . Base::index());
  if (!$row)
   return false;
  $client = new WClient($row['client_id']);
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
pre {margin:10px;}
td.text { padding:5px; }
</style>
<script>
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<h1><?php echo PageAdm::title();?></h1>
<table class="main" width="100%">
<?php
echo "<tr>\n";
echo "<td colspan=\"4\" class=\"left\"><a href=\"clt-" . $client->getId() . "/\">Client " . $client->getId() . ", " . $client->getName() . "</a></td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<th width=\"200\">" . $row['sent'] . "</th>\n";
echo "<th width=\"100\">" . (($row['dir'] == 'i') ? 'Incoming' : 'Outgoing') . "</th>\n";
echo "<td class=\"left\">" . htmlspecialchars($row['subject']) . "</td>\n";
echo "<th width=\"50\">" . (($row['hidden'] != '') ? 'Hidden' : '') . "</th>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td colspan=\"4\"><pre>" . $row['message'] . "</pre></td>\n";
echo "</tr>\n";
?>
</table>
</body>
</html>
<?php
  return true;
 }
}

?>
