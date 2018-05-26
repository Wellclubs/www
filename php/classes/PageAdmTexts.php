<?php

class PageAdmTexts
{
 const TABLE = 'biz_text';
 const TABLE_ABC = 'biz_text_abc';

 const TEXT = 'text';
 const LANG = 'lang';
 const VALUE = 'value';

 const MAXLEN = 300;

 public static function showPage()
 {
  $domain = WDomain::id();
  $isHoster = WClient::me()->isHoster();
  if (array_key_exists('act', $_REQUEST))
  {
   if (!$isHoster)
    return true;
   $act = $_REQUEST['act'];
   if ($act == 'delete')
   {
    if (array_key_exists('id', $_REQUEST))
    {
     $id = intval($_REQUEST['id']);
     if ($id > 0)
     {
      PageAdm::db()->deleteRecords(self::TABLE_ABC, "text_id=$id and domain_id=$domain");
      echo 'OK';
      return true;
     }
    }
   }
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
a.b { color:#000;text-decoration:none; }
pre { margin:0; }
pre p.center { color:#00f; }
</style>
<?php if ($isHoster) { ?>
<script>
function deleteText(id)
{
 A.deleteEntity(id,'text','delete');
}
</script>
<?php } ?>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main text" cellspacing="0" cellpadding="0" width="100%">
<caption><?php echo PageAdm::title();?></caption>
<?php
if (!is_null(WDomain::id()))
{
?>
<tr>
<th width='50'>Id</th>
<th width='50'>Page</th>
<th width='50'>Mode</th>
<th width='50'>Kind</th>
<th width='200'>Name</th>
<?php if ($isHoster) { ?>
<th width='1'></th>
<?php } ?>
</tr>
<?php
 $order = 'page,mode,kind,name';
 $texts = PageAdm::db()->queryArrays(self::TABLE, 'id,' . $order, null, $order);
 if ($texts)
 {
  $langs = Lang::map();
  $lang = Lang::used() ? null : WDomain::abcId();
  $checks = array();
  foreach ($langs as $lang => $Lang)
   $checks[$lang] = PageAdm::checkWorkerLang($lang);
  $where = array('text_id' => null, 'domain_id' => WDomain::id(), 'abc_id' => DB::str($lang));
  foreach ($texts as $text)
  {
   $id = intval($text['id']);
   $page = $text['page'];
   $mode = $text['mode'];
   $kind = $text['kind'];
   $name = $text['name'];
   echo "<tr rowid='t$id'>";
   echo "<th class='right'>$id</th>";
   echo "<th>$page</th>";
   echo "<th>$mode</th>";
   echo "<th>$kind</th>";
   echo "<th>$name</th>";
   if ($isHoster)
    echo "<th><input type='button' value='Delete' onclick='deleteText($id)' /></th>";
   echo "</tr>\n";
   $where['text_id'] = $id;
   if (Lang::used())
   {
    $colspan = $isHoster ? 5 : 4;
    foreach ($langs as $lang => $Lang)
    {
     $where['abc_id'] = DB::str($lang);
     $value = PageAdm::db()->queryField(self::TABLE_ABC, 'value', $where);
     if (strlen($value) > self::MAXLEN)
      $value = substr($value, 0, self::MAXLEN) . '...';
     else if (!strlen($value))
     {
      $name = ($isHoster || $checks[$lang]) ? 'Click here to edit' : 'Click here to view';
      $value = '<p class="center">' . Lang::getPageWord('placeholder', $name) . '</p>';
     }
     echo "<tr><th>" . $Lang->htmlImage() . '<br/>' . $Lang->title() .
       "</th><td colspan='$colspan'><a class='b' href='text-$id?lang=$lang'><pre>\n";
     echo $value;
     echo "</pre></a></td></tr>\n";
    }
   }
   else
   {
    $value = PageAdm::db()->queryField(self::TABLE_ABC, 'value', $where);
    if (!strlen($value))
     $value = '<p class="center">' . Lang::getPageWord('placeholder', 'Click here to edit') . '</p>';
    echo "<tr><td colspan='6'><a class='b' href='text-$id'><pre>\n";
    if (strlen($value) > self::MAXLEN)
     $value = substr($value, 0, self::MAXLEN) . '...';
    echo $value;
    echo "</pre></a></td></tr>\n";
   }
  }
 }
}
?></table>
<?php
if (is_null(WDomain::id()))
 echo "<h1 class='error'>Unregistered domain</h1>\n";
?>
</body>
</html>
<?php
  return true;
 }
}

?>
