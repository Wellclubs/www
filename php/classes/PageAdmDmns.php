<?php

class PageAdmDmns
{
 const TABLE = 'biz_domain';

 private static function processAct($act)
 {
  $table = self::TABLE;
  $entity = 'domain';
  switch ($act)
  {
  case 'createEntity' :
   PageAdm::createEntity($table, $entity, null, null, null, array('noserial' => true));
   break;

  case 'deleteEntity' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField('com_centre', 'count(*)', "domain_id=$id")) > 0)
   {
    echo ucfirst($entity) . " $id has some centres linked";
    return false;
   }
   PageAdm::deleteEntity($table, $entity, $id);
   break;

  case 'changeName' :
   PageAdm::changeName($table, $entity);
   break;

  case 'changeFlag' :
   PageAdm::changeFlag($table, $entity);
   break;

  case 'changeField' :
   $id = intval(HTTP::param('id'));
   $field = HTTP::param('field');
   $value = HTTP::param('value');
   if ($field == 'id')
   {
    $value = intval($value);
    if (intval(PageAdm::db()->queryField('com_centre', 'count(*)', "domain_id=$id")) > 0)
    {
     echo ucfirst($entity) . " $id has some centres linked";
     return false;
    }
    if (PageAdm::db()->queryField($table, 'count(*)', 'id=' . $value))
    {
     echo "Error changing $entity $id $field to '$value': " .
       "there is an another $entity exist with the same $field, " . DB::lastQuery();
     return false;
    }
    PageAdm::db()->modifyFields($table, array($field => DB::str($value)), array('id' => $id));
    echo 'OK';
   }
   else
   {
    if ($field == 'abc_id')
     $_REQUEST['value'] = strtolower($value);
    if ($field == 'currency_id')
     $_REQUEST['value'] = strtoupper($value);
    PageAdm::changeField($table, $entity);
   }
   break;

  case 'include' :
   $id = intval(HTTP::param('id'));
   $lang = HTTP::param('lang');
   PageAdm::db()->mergeFields($table . '_abc', array('used' => '1'), array('domain_id' => $id, 'abc_id' => DB::str($lang)));
   echo 'OK';
   break;

  case 'exclude' :
   $id = intval(HTTP::param('id'));
   $lang = HTTP::param('lang');
   PageAdm::db()->deleteRecords($table . '_abc', array('domain_id' => $id, 'abc_id' => DB::str($lang)));
   echo 'OK';
   break;

  default :
   echo "Unsupported action: '$act'";
  }
  return true;
 }

 public static function showPage()
 {
  $isHoster = WClient::me()->isHoster();
  if (array_key_exists('act', $_REQUEST))
  {
   if (!$isHoster || self::processAct($_REQUEST['act']))
    exit;
  }
  Lang::initialize(true);
  $langs = Lang::map();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<?php if ($isHoster) { ?>
<script>
var entity='domain';
function changeUsed(node,id,lang,title,domain)
{
 var oldValue=(node.className=='checked');
 var L='the language '+lang+' ('+title+')';
 var D='the '+entity+' '+id+' ('+domain+')';
 var text=oldValue?('Exclude '+L+' from '+D):('Include '+L+' to '+D);
 if(!confirm(text+'?'))
  return;
 var act=oldValue?'exclude':'include';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+'&id='+id+'&lang='+lang),false);
 req.send(null);
 var error='Error changing '+L+' for '+D+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.className=oldValue?'':'checked';
}
</script>
<?php } ?>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main" cellspacing='0'>
<caption><?php echo PageAdm::title();?></caption>
<colgroup><col width="50"><col width="20"><col width="50"><col width="30"><col width="*"><col width="50"><col width="50"></colgroup>
<tr>
<th width='50'>Id</th>
<th width='200'>Name</th>
<th width='50'>SSL</th>
<th width='100'>Google&nbsp;Analytics&nbsp;ID</th>
<th width='50'>Tawk</th>
<?php
 foreach ($langs as $lang => $Lang)
 {
  $title = $Lang->title();
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) $title</th>\n";
 }
?>
<th width='100'>Language</th>
<th width='100'>Currency</th>
<th width='50'>1st Day</th>
<?php if ($isHoster) { ?>
<th><input type="button" value="Create" onclick="A.createEntity(entity)" /></th>
<?php } ?>
</tr>
<?php
 $fields = 'id,name,use_ssl,ga_id,use_tawk,abc_id,currency_id,first_day';
 foreach ($langs as $lang => $Lang)
  $fields .= ',(select 1 from ' . self::TABLE . '_abc where domain_id=a.id and abc_id=\'' . $lang . '\')used_' . $lang;
 $dmns = PageAdm::db()->queryArrays(self::TABLE . ' a', $fields, null, 'id');
 if ($dmns)
  foreach ($dmns as $dmn)
  {
   $id = $dmn['id'];
   $name = htmlspecialchars($dmn['name']);
   $ssl = $dmn['use_ssl'] == '1';
   $gaId = htmlspecialchars($dmn['ga_id']);
   $tawk = $dmn['use_tawk'] == '1';
   $abcId = $dmn['abc_id'];
   //$abcTitle = htmlspecialchars($langs[$abcId]->title());
   $currencyId = $dmn['currency_id'];
   $firstDay = $dmn['first_day'];
   echo "<tr>\n";
   echo "<td class='right'" . ($isHoster ? " onclick='A.changeField(this,$id,entity,\"id\")'" : '') . ">$id</td>\n";
   echo "<td class='lang'" . ($isHoster ? " onclick='A.changeName(this,$id,entity)'" : '') . ">$name</td>\n";
   $checked = $ssl ? " class='checked'" : null;
   echo "<td$checked" . ($isHoster ? " onclick='A.changeFlag(this,$id,entity,\"use_ssl\")'" : '') . ">&nbsp;</td>\n";
   echo "<td class='left'" . ($isHoster ? " onclick='A.changeField(this,$id,entity,\"ga_id\")'" : '') . ">$gaId</td>\n";
   $checked = $tawk ? " class='checked'" : null;
   echo "<td$checked" . ($isHoster ? " onclick='A.changeFlag(this,$id,entity,\"use_tawk\")'" : '') . ">&nbsp;</td>\n";
   foreach ($langs as $lang => $Lang)
   {
    $title = $Lang->title();
    $sys = ($lang == Lang::SYS);
    $used = $dmn['used_' . $lang];
    if ($sys || ($lang == $abcId))
     echo "<th>&nbsp;</th>\n";
    else
    {
     $checked = ($sys || $used) ? " class='checked'" : null;
     $onclick = ($sys || !$isHoster) ? null : " onclick='changeUsed(this,$id,\"$lang\",\"$title\",\"$name\")'";
     echo "<td$checked$onclick>&nbsp;</td>\n";
    }
   }
   echo "<td class='center'" . ($isHoster ? " onclick='if(A.changeField(this,$id,entity,\"abc_id\"))document.location.reload(true);'" : '') . ">$abcId</td>\n";
   echo "<td class='center'" . ($isHoster ? " onclick='A.changeField(this,$id,entity,\"currency_id\")'" : '') . ">$currencyId</td>\n";
   echo "<td class='center'" . ($isHoster ? " onclick='A.changeField(this,$id,entity,\"first_day\")'" : '') . ">$firstDay</td>\n";
   if ($isHoster)
    echo "<th><input type='button' value='Delete' onclick='A.deleteEntity($id,entity)' /></th>\n";
   echo "</tr>\n";
  }
?></table>
</body>
</html>
<?php
  return true;
 }
}

?>
