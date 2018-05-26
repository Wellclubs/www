<?php
// https://learn.javascript.ru/xhr-forms

class PageAdmText
{
 const TABLE_ABC = 'biz_text_abc';

 public static function showPage()
 {
  $domain = WDomain::id();
  if (is_null($domain))
   return false;
  $id = Base::index();
  if (!$id)
   return false;
  $lang = WDomain::abcId();
  if (Lang::used())
  {
   $lang = HTTP::get('lang');
   if (is_null($lang))
    $lang = Lang::DEF();
  }
  $editable = WClient::me()->isHoster() || PageAdm::checkWorkerLang($lang);
  $field = 'value';
  $where = array('text_id' => $id, 'domain_id' => $domain, 'abc_id' => DB::str($lang));
  if (array_key_exists('value', $_POST))
  {
   if ($editable)
   {
    $value = $_POST['value'];
    if (!PageAdm::db()->mergeFields(self::TABLE_ABC, array($field => DB::str($value)), $where))
     return false;
    echo PageAdm::db()->queryField(self::TABLE_ABC, $field, $where);
   }
   return true;
  }
  $value = PageAdm::db()->queryField(self::TABLE_ABC, $field, $where);
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
pre { min-height:200px;margin:0;border:0;padding:0;font-size:inherit; }
textarea { width:100%;margin:0;border:0;padding:0;font-size:inherit; }
#btnSave { display:none; }
#btnCancel { display:none; }
#rowEdit { display:none; }
</style>
<?php if ($editable) { ?>
<script>
function edit()
{
 el('editor').value=el('viewer').innerHTML;
 el('btnEdit').style.display='none';
 el('btnSave').style.display='inline-block';
 el('btnCancel').style.display='inline-block';
 el('rowView').style.display='none';
 el('rowEdit').style.display='table-row';
 el('editor').focus();
}
function save()
{
 var formData=new FormData(document.forms[0]);
 var req=new XMLHttpRequest();
 req.open("POST",document.location,false);
 req.send(formData);
 if (req.status!=200)
  return alert('Error savint text value on the server');
 el('viewer').innerHTML=req.responseText;
 el('btnEdit').style.display='inline-block';
 el('btnSave').style.display='none';
 el('btnCancel').style.display='none';
 el('rowView').style.display='table-row';
 el('rowEdit').style.display='none';
}
function cancel()
{
 el('btnEdit').style.display='inline-block';
 el('btnSave').style.display='none';
 el('btnCancel').style.display='none';
 el('rowView').style.display='table-row';
 el('rowEdit').style.display='none';
}
</script>
<?php } ?>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main text" cellspacing="0" cellpadding="0" width="100%">
<caption><?php echo PageAdm::title();?></caption>
<?php if ($editable) { ?>
<tr><th class="left">
<input id="btnEdit" type="button" value="Edit" onclick="edit()" />
<input id="btnSave" type="button" value="Save" onclick="save()" />
<input id="btnCancel" type="button" value="Cancel" onclick="cancel()" />
<a href="texts/" style="display:inline-block;font-size:12px;">Back to texts</a>
</th></tr>
<?php } ?>
<tr id="rowView"><td><pre id="viewer">
<?php
echo $value;// ? $value : '&nbsp;';
?>
</pre></td></tr>
<?php if ($editable) { ?>
<tr id="rowEdit"><td><form><textarea id="editor" name="value" rows="20"></textarea></form></td></tr>
<?php } ?>
</table>
</body>
</html>
<?php
  return true;
 }
}

?>
