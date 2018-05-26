<?php

class PageAdmTitles
{
 const TABLE = 'art_word';
 const TABLE_ABC = 'art_word_abc';

 const PAGE = 'page';
 const MODE = 'mode';
 const WORD = 'word';
 const LANG = 'lang';
 const TITLE = 'title';

 public static function showPage()
 {
  $isHoster = WClient::me()->isHoster();
  if (array_key_exists(self::WORD, $_REQUEST) && array_key_exists(self::LANG, $_REQUEST) && array_key_exists(self::TITLE, $_REQUEST))
  {
   $lang = $_REQUEST[self::LANG];
   if (PageAdm::checkWorkerLang($lang))
   {
    $word = $_REQUEST[self::WORD];
    $title = $_REQUEST[self::TITLE];
    $where = array('word_id' => $word, 'abc_id' => DB::str($lang));
    if (strlen($title))
     PageAdm::db()->mergeField(self::TABLE_ABC, self::TITLE, DB::str($title), $where);
    else
     PageAdm::db()->deleteRecords(self::TABLE_ABC, $where);
    echo PageAdm::db()->queryField(self::TABLE_ABC, self::TITLE, $where);
   }
   return true;
  }
  if (array_key_exists('act', $_REQUEST))
  {
   if (!$isHoster)
    return true;
   $act = $_REQUEST['act'];
   if ($act == 'deleteWord')
   {
    if (array_key_exists(self::WORD, $_REQUEST))
    {
     $word = $_REQUEST[self::WORD];
     if (is_numeric($word))
     {
      PageAdm::db()->deleteRecords(self::TABLE, "id='$word'");
      echo 'OK';
      return true;
     }
    }
   }
  }
  $show = true;
  $where = null;
  $pages = null;
  $modes = null;
  $page = null;
  $mode = null;
  // $pages
  $rows = PageAdm::db()->queryRecords(self::TABLE, 'distinct page', 'length(page)>0');
  if ($rows)
  {
   $pages = array('' => 'Common');
   foreach ($rows as $key => $row)
    $pages[$row[0]] = ucfirst($row[0]);
   $pages['*'] = 'Total';
   $show = false;
  }
  if (array_key_exists(self::PAGE, $_REQUEST))
  {
   $show = true;
   $page = $_REQUEST[self::PAGE];
   $where = ($page != '*') ? (self::PAGE . '=' . DB::str($page)) : null;
   $rows = PageAdm::db()->queryRecords(self::TABLE, 'distinct mode', $where . ' and length(mode)>0');
   if ($rows && ($page != '*'))
   {
    $modes = array('' => 'Common');
    foreach ($rows as $key => $row)
     $modes[$row[0]] = ucfirst($row[0]);
    $modes['*'] = 'Total';
   }
   $mode = '*';
   if (array_key_exists(self::MODE, $_REQUEST))
   {
    $mode = $_REQUEST[self::MODE];
    if ($mode != '*')
     $where .= ($where ? ' and ' : '') . self::MODE . '=' . DB::str($mode);
   }
  }
  //print_r($pages);
  //exit($page);
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
form { clear:both; }
table.main td div { white-space:nowrap }
table.main td input { margin:0;border:0;padding:0;font-size:16px;width:100%; }
</style>
<script>
var editing={word:0,lang:''};
<?php if ($isHoster) { ?>
function deleteWord(word)
{
 if(!confirm('Delete row '+word+'?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deleteWord&word='+word),false);
 req.send(null);
 if ((req.status!=200) || (req.responseText!='OK'))
  return alert('Error deleting a word on server');
 var row=document.getElementById('row-'+word);
 row.parentNode.removeChild(row);
}
<?php } ?>
function inputKeyDown(e)
{
 if(e.keyCode==27)
  hideEditor();
 else if(e.keyCode==13)
 {
  var id='-'+editing.word+'-'+editing.lang;
  var text=document.getElementById('text'+id);
  var input=document.getElementById('input'+id);
  var req=new XMLHttpRequest();
  req.open("GET",A.makeURI('word='+editing.word+'&lang='+editing.lang+'&title='+input.value),false);
  req.send(null);
  if (req.status==200)
   text.innerHTML=req.responseText||'&nbsp;';
  else
   alert('Error storing new title on server');
  hideEditor();
 }
}
function showEditor(word,lang)
{
 hideEditor();
 var id='-'+word+'-'+lang;
 var text=document.getElementById('text'+id);
 var edit=document.getElementById('edit'+id);
 var input=document.getElementById('input'+id);
 input.value=(text.innerHTML=='&nbsp;')?'':text.innerHTML;
 text.style.display='none';
 edit.style.display='';
 input.onkeydown=inputKeyDown;
 input.onblur=hideEditor;
 input.select();
 input.focus();
 editing.word=word;
 editing.lang=lang;
}
function hideEditor()
{
 if(editing.word)
 {
  var id='-'+editing.word+'-'+editing.lang;
  var text=document.getElementById('text'+id);
  var edit=document.getElementById('edit'+id);
  var input=document.getElementById('input'+id);
  edit.style.display='none';
  text.style.display='';
  input.onkeydown=null;
  input.onblur=null;
  input.value='';
  editing.word=0;
  editing.lang='';
 }
}
</script>
</head>
<?php
PageAdm::instance()->showBodyTop();
if ($pages || $modes)
{
 if ($pages)
 {
  $links = array();
  foreach ($pages as $p => $t)
   $links[] = array("titles/?page=$p", $t, $p == $page);
  PageAdm::echoMenuTableOfLinks('Page', $links);
 }
 if ($modes)
 {
  $links = array();
  foreach ($modes as $m => $t)
   $links[] = array("titles/?page=$page" . (($m == '*') ? '' : "&mode=$m"), $t, $m == $mode);
  PageAdm::echoMenuTableOfLinks('Mode', $links);
 }
}
?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="act" />
<input type="hidden" name="lang" />
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Page</th>
<th width='50'>Mode</th>
<th width='50'>Kind</th>
<th width='200'>Name</th>
<?php
 $langs = Lang::map();
 $checks = array();
 foreach ($langs as $lang => $Lang)
 {
  echo '<th width="150" class="lang">' . $Lang->htmlImage() . " ($lang) {$Lang->title()}</th>\n";
  $checks[$lang] = PageAdm::checkWorkerLang($lang);
 }
 if ($isHoster)
  echo "<th width='1'></th>\n";
?>
</tr>
<?php
 if (!$show)
  $records = null;
 else
 {
  $fields = 'id,page,mode,kind,name';
  foreach ($langs as $lang => $title)
   $fields .= ',(select title from art_word_abc where word_id=a.id and abc_id=\'' . $lang . '\')title_' . $lang;
  $records = PageAdm::db()->queryRecords('art_word a', $fields, $where, '2,3,4,5');
 }
 if ($records)
  foreach ($records as $record)
  {
   $id = $record[0];
   echo "<tr id='row-$id'>\n";
   echo "<th><b>$id</b></th>\n";
   for ($i = 1; $i < 4; $i++)
    echo "<th>{$record[$i]}</th>\n";
   echo "<th><i>{$record[$i++]}</i></th>\n";
   foreach ($langs as $lang => $title)
   {
    $value = $record[$i++];
    if ($value == '')
     $value = '&nbsp;';
    if ($checks[$lang])
     echo "<td>" .
       "<div id='text-$id-$lang' onclick='showEditor($id,\"$lang\")'>" . $value . "</div>" .
       "<div id='edit-$id-$lang' style='display:none'><input id='input-$id-$lang' value='$value' /></div>" .
       "</td>\n";
    else
     echo "<td><div id='text-$id-$lang'>" . $value . "</div></td>\n";
   }
  if ($isHoster)
   echo "<th><input type='button' value='Delete' onclick='deleteWord($id)'/></th>\n";
  echo "</tr>\n";
 }
?></table>
</form>
</body>
</html>
<?php
  return true;
 }

}

?>