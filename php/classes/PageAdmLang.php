<?php

class PageAdmLang
{
 const TABLE = 'art_abc';

 private static function processAct($act, $lang)
 {
  $table = self::TABLE;
  switch ($act)
  {
   case 'changeTitle' :
    $title = HTTP::param('title');
    $where = 'id=' . DB::str($lang);
    PageAdm::db()->modifyFields($table, array('title' => DB::str($title)), $where);
    if (PageAdm::db()->queryField($table, 'title', $where) == $title)
     echo 'OK';
    else
     echo "Error changing language '$lang' title to '$title'";
    return true;

   case 'changeCharDec' :
    $value = trim(HTTP::param('value'));
    $where = 'id=' . DB::str($lang);
    PageAdm::db()->modifyFields($table, array('char_dec' => DB::strn($value)), $where);
    if (PageAdm::db()->queryField($table, 'char_dec', $where) == $value)
     echo 'OK';
    else
     echo "Error changing language '$lang' char_dec to '$value'";
    return true;

   case 'changeCharMil' :
    $value = trim(HTTP::param('value'));
    $where = 'id=' . DB::str($lang);
    PageAdm::db()->modifyFields($table, array('char_mil' => DB::strn($value)), $where);
    if (PageAdm::db()->queryField($table, 'char_mil', $where) == $value)
     echo 'OK';
    else
     echo "Error changing language '$lang' char_mil to '$value'";
    return true;

   case 'uploadImage' :
    $where = 'id=' . DB::str($lang);
    if (!PageAdm::db()->uploadFile('image', $table, array('contents' => 'image'), $where))
     Base::addError ("Error uploading the image for a language '$lang'");
    header('Location: ./?');
    //header('Location: ' . Base::url());
    return false;

   case 'clearImage' :
    if (PageAdm::db()->modifyFields($table, array('image' => 'null'), "id='$lang'"))
     echo 'OK';
    else
     echo "Error clearing the image for a language '$lang'";
    return true;

   case 'addLang' :
    $where = 'id=' . DB::str($lang);
    if (PageAdm::db()->queryField($table, 'count(*)', $where) > 0)
    {
     echo "Adding language '$lang' - Duplicated language id" . print_r(DB::queries(), true);
    }
    else
    {
     $title = HTTP::param('title');
     PageAdm::db()->insertValues($table, array('id' => DB::str($lang), 'title' => DB::str($title)));
     if (PageAdm::db()->affected_rows == 1)
      echo 'OK';
     else
      echo "Error adding the language '$lang' record to the database" . print_r(DB::queries(), true);
    }
    return true;

   case 'hideLang' :
    $hide = HTTP::param('hide');
    $where = 'id=' . DB::str($lang);
    PageAdm::db()->modifyFields($table, array('hidden' => (($hide == '1') ? "'1'" : 'null')), $where);
    if (PageAdm::db()->queryField($table, 'hidden', $where) == $hide)
     echo 'OK';
    else
     echo "Error " . ($hide ? '' : 'un') . "hiding the territory $id";
    return true;

   case 'deleteLang' :
    $where = 'id=' . DB::str($lang);
    if (PageAdm::db()->deleteRecords($table, $where) &&
      (PageAdm::db()->affected_rows == 1) &&
      (PageAdm::db()->queryField($table, 'count(*)', $where) == 0))
     echo 'OK';
    else
     echo "Error deleting the language '$lang' record from the database" . print_r(DB::queries(), true);
    return true;

   default :
    echo "Unsupported action: '$act'";
    return true;
  }
  return false;
 }

 public static function showPage()
 {
  $isHoster = WClient::me()->isHoster();
  if (array_key_exists('act', $_REQUEST))
  {
   if (!array_key_exists('lang', $_REQUEST))
    exit('Parameter "lang" is not set');
   if (!$isHoster || self::processAct($_REQUEST['act'], $_REQUEST['lang']))
    exit;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<?php if ($isHoster) { ?>
<script>
function changeTitle(td,lang)
{
 var oldTitle=decodeHTML(td.innerHTML);
 var newTitle=prompt('Input a new title for language "'+lang+'":',oldTitle);
 if((newTitle==null)||(newTitle==oldTitle))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeTitle&lang='+lang+'&title='+newTitle),false);
 req.send(null);
 var error='Error storing new title on server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 td.innerHTML=newTitle;
}
function uploadImage(lang)
{
 var upload=el('file-'+lang);
 upload.name='image';
 upload.form.act.value='uploadImage';
 upload.form.lang.value=lang;
 upload.form.submit();
}
function clearImage(lang)
{ // http://learn.javascript.ru/uibasic
 if(!confirm('Clear image for language "'+lang+'"?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=clearImage&lang='+lang),false);
 req.send(null);
 var error='Error clearing an image on server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function addLang()
{
 var langEdit=el('lang-new');
 var lang=langEdit.value;
 if(lang=='')
  return langEdit.select()||alert('Code not entered')
 if(lang.length!=2)
  return langEdit.select()||alert('Code length must be 2 letters')
 var titleEdit=el('title-new');
 var title=titleEdit.value;
 if(title=='')
  return titleEdit.select()||alert('Title not entered')
 if(!confirm('Add new language "'+lang+'" ("'+title+'")?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=addLang&lang='+lang+'&title='+title),false);
 req.send(null);
 var error='Error adding a new language on server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function changeCharDec(td,lang)
{
 var oldChar=decodeHTML(td.innerHTML);
 var newChar=prompt('Input a new decimal separator for language "'+lang+'":',oldChar);
 if((newChar==null)||(newChar==oldChar))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeCharDec&lang='+lang+'&value='+newChar),false);
 req.send(null);
 var error='Error storing new decimal separator on server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 td.innerHTML=newChar;
}
function changeCharMil(td,lang)
{
 var oldChar=decodeHTML(td.innerHTML);
 var newChar=prompt('Input a new thousand separator for language "'+lang+'":',oldChar);
 if((newChar==null)||(newChar==oldChar))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=changeCharMil&lang='+lang+'&value='+newChar),false);
 req.send(null);
 var error='Error storing new thousand separator on server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 td.innerHTML=newChar;
}
function hideLang(button,lang,hide)
{
 var span=el('title-'+lang);
 var title=span.innerHTML;
 if(!confirm((hide?'Hide':'Unhide')+' the language "'+lang+'" ("'+title+'")?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=hideLang&lang='+lang+'&hide='+(hide?'1':'')),false);
 req.send(null);
 var error='Error '+(hide?'hid':'show')+'ing the language '+lang+' ("'+title+'") on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 button.parentNode.parentNode.className=hide?'hidden':'';
 button.value=hide?'Show':'Hide';
 button.onclick=eval('(function onclick(){hideLang(this,"'+lang+'",'+(!hide)+')})');
}
function deleteLang(lang)
{
 var span=el('title-'+lang);
 var title=span.innerHTML;
 if(!confirm('Delete language "'+lang+'" ("'+title+'")?'))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act=deleteLang&lang='+lang),false);
 req.send(null);
 var error='Error deleting a language on server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
</script>
<?php } ?>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<?php if ($isHoster) { ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="act" />
<input type="hidden" name="lang" />
<input type="hidden" name="MAX_FILE_SIZE" value="65535" />
<?php } ?>
<table class="main" cellspacing='0'>
<caption><?php echo PageAdm::title();?></caption>

<colgroup><col width="50"><col width="20"><col width="50"><col width="50"><col width="*"><col width="50"><col width="50">
<?php if ($isHoster) { ?>
<col width="50"><col width="50">
<?php } ?>
</colgroup>

<tr><th>Code</th><th>Title</th><th<?php if ($isHoster) { ?> colspan="3"<?php } ?>>Image</th><th>Dec.</th><th>Mil.</th>
<?php if ($isHoster) { ?>
<th>Hide</th><th>Delete</th>
<?php } ?>
</tr>

<?php
  $fields = 'id,title,image,char_dec,char_mil,hidden';

  self::showLangSys($fields, Lang::SYS, Lang::SYS_TITLE, Lang::SYS_CHAR_DEC, Lang::SYS_CHAR_MIL);
  if (Lang::DEF() != Lang::SYS)
   self::showLangSys($fields, Lang::DEF(), Lang::DEF_TITLE(), Lang::DEF_CHAR_DEC(), Lang::DEF_CHAR_MIL());

  $langs = PageAdm::db()->queryRecords(self::TABLE, $fields, 'id<>\'' . Lang::SYS . '\' and id<>\'' . Lang::DEF() . '\'', 'id');
  if ($langs)
   foreach ($langs as $record)
    self::showLang($record[0], $record[1], $record[2] != null, $record[3], $record[4], $record[5] != null);
?>
<?php if ($isHoster) { ?>
<tr>
<td><input id='lang-new' maxlength='2' style='width:30px;border:0;text-align:center;' /></td>
<td><input id='title-new' maxlength='30' style='width:200px;border:0;' /></td>
<th><input type='button' value='Add' style='float:right' onclick='addLang()' /></th>
<th colspan="6"></th>
</tr>
<?php } ?>
</table>
<?php if ($isHoster) { ?>
</form>
<?php } ?>
</body>
</html>
<?php
  return true;
 }

 private static function showLangSys($fields, $id, $title, $charDec, $charMil)
 {
  $record = PageAdm::db()->queryFields(self::TABLE, $fields, "id='$id'");
  if (isset($record))
  {
   if (isset($record[1]))
    $title = $record[1];
   self::showLang($id, $title, $record[2] != null, $record[3], $record[4], $record[5] != null);
  }
  else
   self::showLang($id, $title, false, $charDec, $charMil, false);
 }

 private static function showLang($id, $title, $imaged, $charDec, $charMil, $hidden)
 {
  $rowStyle = ($id == Lang::DEF()) ? ' style="background:#eef"' : ($hidden ? ' class="hidden"' : '');
  $img = ($imaged ? "<img src='img/lang-$id.png'>" : '');
  if (WClient::me()->isHoster())
  {
   $clear = $imaged ? "<input type='button' value='Clear' onclick='clearImage(\"$id\")' /'>" : '';
   $value = $hidden ? 'Show' : 'Hide';
   $arg = $hidden ? 'false' : 'true';
   $hide = "<input type='button' value='$value' onclick='hideLang(this,\"$id\",$arg)' />";
   $delete = (($id == Lang::SYS) || ($id == Lang::DEF())) ? '' :
    "<input type='button' value='Delete' onclick='deleteLang(\"$id\")' />";
   echo "<tr id='row-$id'$rowStyle>" .
     "<th>$id</th>" .
     "<td id='title-$id' onclick='changeTitle(this,\"$id\")'>" . htmlspecialchars($title) . "</td>" .
     "<th>$img</th>" .
     "<td><input id='file-$id' type='file' size='1' onchange='uploadImage(\"$id\")' /'></td>" .
     "<th>$clear</th>" .
     "<td class='center' onclick='changeCharDec(this,\"$id\")'>$charDec</td>" .
     "<td class='center' onclick='changeCharMil(this,\"$id\")'>$charMil</td>" .
     "<th>$hide</th>" .
     "<th>$delete</th>" .
     "</tr>\n";
  }
  else
  {
   echo "<tr id='row-$id'$rowStyle>" .
     "<th>$id</th>" .
     "<td id='title-$id'>" . htmlspecialchars($title) . "</td>" .
     "<th>$img</th>" .
     "<td class='center'>$charDec</td>" .
     "<td class='center'>$charMil</td>" .
     "</tr>\n";
  }
 }

}

?>