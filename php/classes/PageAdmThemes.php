<?php

class PageAdmThemes
{
 private static function processAct($act)
 {
  $table = WTheme::TABLE_THEME;
  switch ($act)
  {
  case 'uploadEntity' :
   if (!WThemeLoader::execute('theme', HTTP::param('name', '')))
    return false;
   header('Location: ' . Base::loc());
   exit;

  case 'renameEntity' :
   $name = HTTP::param('name');
   $newname = HTTP::param('to');
   if (is_dir(WTheme::path() . $newname))
    echo "The theme \"$newname\" already exists";
   else
   {
    PageAdm::db()->deleteRecords($table, "name='$newname'");
    PageAdm::db()->modifyFields($table, array('name' => DB::str($newname)), "name='$name'");
    if (rename(WTheme::path() . $name, WTheme::path() . $newname))
     echo 'OK';
    else
     echo "Error renaming the theme \"$name\" to \"$newname\"";
   }
   return true;

  case 'bookEntity' :
  case 'comEntity' :
   $name = HTTP::param('name');
   $field = 'active_' . substr($act, 0, strlen($act) - 6);
   PageAdm::db()->modifyFields($table, array($field => 'null'));
   $where = array('name' => DB::str($name));
   PageAdm::db()->mergeFields($table, array($field => DB::str('1')), $where);
   if (PageAdm::db()->queryField($table, $field, $where) == '1')
    echo 'OK';
   else
    echo "Error activating the theme \"$name\"";
   //echo print_r(DB::queries(), true);
   return true;

  case 'hideEntity' :
  case 'unhideEntity' :
   $name = HTTP::param('name');
   $hidden = ($act == 'hideEntity') ? DB::str('1') : 'null';
   $where = "name='$name'";
   PageAdm::db()->modifyFields($table, array('hidden' => $hidden), $where);
   if (PageAdm::db()->affected_rows == 0)
    PageAdm::db()->insertValues($table, array('name' => DB::str($name), 'hidden' => $hidden));
   $hidden = ($act == 'hideEntity') ? '1' : '';
   if (PageAdm::db()->queryField($table, 'hidden', $where) == $hidden)
    echo 'OK';
   else
    echo "Error " . substr($act, 0, strlen($act) - 7) . "ing the theme \"$name\"";
   return true;

  case 'deleteEntity' :
   $name = HTTP::param('name');
   PageAdm::db()->deleteRecords($table, "name='$name'");
   if (Util::deleteFile(WTheme::path() . $name))
    echo 'OK';
   else
    echo "Error deleting the theme \"$name\"";
   return true;

  default :
   echo "Unsupported action: '$act'";
   return true;
  }
 }

 public static function showPage()
 {
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    exit;
  }
  if (array_key_exists('demo', $_REQUEST))
  {
   self::showDemo($_REQUEST['demo']);
   exit;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style type="text/css">
table.main th.title { text-align:left;padding:0 5px; }
iframe { height:150px;width:100%;border:0; }
</style>
<script>
function renameEntity(name)
{
 var newname=prompt('Input a new theme name for theme '+name+':',name);
 if((newname==null)||(newname==name))
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=renameEntity&name='+name+'&to='+newname,false);
 req.send(null);
 var error='Error renaming the theme "'+name+'" to "'+newname+'" on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
function manageEntity(button,name)
{
 if(!confirm(button.value+' the theme '+name+'?'))
  return;
 action=button.value.toLowerCase();
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act='+action+'Entity&name='+name,false);
 req.send(null);
 var error='Error '+action.substr(0,action.length-1)+'ing the theme "'+name+'" on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main" cellspacing="0" cellpadding="0">
<caption><?php echo PageAdm::title();?></caption>
<tr><th width='200'>Name</th><th width='200'>ThemeRoller</th><th width='1' colspan='4'>Actions</th></tr>
<?php
 $path = WTheme::path();
 $folders = scandir($path);
 foreach ($folders as $name)
 {
  if ((substr($name,0,1) == '.') || !is_dir($path . $name))
   continue;
  $base = ($name == WTheme::DEFAULT_THEME);
  $fields = PageAdm::db()->queryFields(WTheme::TABLE_THEME, 'active_book,active_com,hidden', "name='$name'");
  $activeBook = isset($fields) && ($fields[0] != '');
  $activeCom = isset($fields) && ($fields[1] != '');
  $hidden = isset($fields) && ($fields[2] != '');
  $class = $hidden ? (" class='hidden'") : (($activeBook || $activeCom) ? (" class='active'") : '');

  $style = '';
  if (!$hidden)
  {
   if ($activeBook)
    $style = ' style="background:#eef;font-weight:bold"';
   if ($activeCom)
    $style = ' style="background:#eef;font-weight:bold"';
  }
  echo "<tr id='row-$name'$class$style>\n";

  if ($base)
   echo '<th><i>base</i></th>';
  else
   echo "<td onclick='renameEntity(\"$name\")'>$name</td>\n";

  $uri = WTheme::extractURI($name);
  if ($uri)
   $uri = "<a target='_blank' href='$uri'>Go to theme builder</a>";
  echo "<th>$uri</th>\n";

  $activateBook = '';
  if (!$hidden && !$activeBook)
   $activateBook = "<input type='button' value='Book' onclick='manageEntity(this,\"$name\")'/>";
  echo "<th>$activateBook</th>\n";

  $activateCom = '';
  if (!$hidden && !$activeCom)
   $activateCom= "<input type='button' value='Com' onclick='manageEntity(this,\"$name\")'/>";
  echo "<th>$activateCom</th>\n";

  $show = '';
  if (!$activeBook && !$activeCom)
   $show = $hidden ?
     "<input type='button' value='Unhide' onclick='manageEntity(this,\"$name\")'/>" :
     "<input type='button' value='Hide' onclick='manageEntity(this,\"$name\")'/>";
  echo "<th>$show</th>\n";

  $delete = '';
  if (!$base && !$activeBook && !$activeCom)
   $delete = "<input type='button' value='Delete' onclick='manageEntity(this,\"$name\")'/>";
  echo "<th>$delete</th>\n";

  echo "</tr>\n";

  echo "<tr><td colspan='6'><iframe src='themes/?demo=$name'></iframe></td></tr>\n";
 }
?><tr>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name='act' value="uploadEntity" />
<td colspan="2"><input type="file" name="theme" style="width:100%" onchange='el("upload").style.visibility="visible"' /></td>
<td colspan="3"><input name="name" /></td>
<th><input type='button' id="upload" style="visibility:hidden" value='Upload' onclick='submit()'/></th>
</form>
</tr>
</table>
<h4><a target="_blank" href="http://jqueryui.com/download/#!version=1.9.2"
>Download predefined or custom themes from here</a></h4>
</body>
</html>
<?php
  return true;
 }

 private static function showDemo($name)
 {
?><!doctype html>
<html>
<head>
<title>WC Admin Panel : Theme <?php echo htmlspecialchars($name); ?> Demo</title>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.ui.min.js"></script>
<link rel='stylesheet' type='text/css' href='<?php echo Base::home(); ?>css/reset.min.css'>
<link rel='stylesheet' type='text/css' href='<?php echo Base::home(); ?>css/ui/<?php echo htmlspecialchars($name); ?>/jquery-ui.css'>
<style type="text/css">
.demo {margin:2px;padding:2px 5px;}
.demo .ui-icon {float:left;margin:2px 2px 0 0;}
#dialog {display:none;}
.form {position:relative;float:left;width:auto;margin:5px;}
.form .header {margin-bottom:10px;}
.form .header .ui-icon {}
.form p .ui-icon {float:left;margin:2px 2px 0 0;}
#radio {margin-top:10px;}
#button {float:right;margin:5px;}
</style>
<script type="text/javascript">
$(function()
{
 $("#button").button().click(function(){
  $("#dialog").dialog(
  {
   modal:true
  ,title:"Modal dialog"
  ,height:100
  ,position:{my:"left bottom",at:"right bottom"}
  });
 })
 $("#radio").buttonset();
});
</script>
</head>
<body>
<table width="100%"><tr><td width="100" valign="top">

<div class="demo ui-state-default"><span class="ui-icon ui-icon-home"></span> Default</div>
<div class="demo ui-state-hover"  ><span class="ui-icon ui-icon-gear"></span> Hover</div>
<div class="demo ui-state-active" ><span class="ui-icon ui-icon-star"></span> Active</div>
<div class="demo ui-state-highlight"><span class="ui-icon ui-icon-info" ></span> Highlight</div>
<div class="demo ui-state-error"    ><span class="ui-icon ui-icon-alert"></span> Error</div>

</td><td valign="top">

<div class="form ui-dialog ui-widget ui-widget-content ui-corner-all">
<div class="header ui-dialog-titlebar ui-widget-header ui-corner-all">
<div class="ui-dialog-titlebar-close ui-corner-all">
<span class="ui-icon ui-icon-closethick"></span>
</div>
Widget header
</div>
<p><span class="ui-icon ui-icon-info"></span>Widget content</p>
<div id="radio">
<input type="radio" id="radio1" name="radio"><label for="radio1">First</label>
<input type="radio" id="radio2" name="radio" checked="checked"><label for="radio2">Second</label>
<input type="radio" id="radio3" name="radio"><label for="radio3">Third</label>
</div>
</div>

<div id="button">Button</div>

<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

</td></tr></table>

<div id="dialog">Click the cross button to close</div>

</body>
</html>
<?php
 }

 private static function showDemo2($name)
 {
?><!doctype html>
<html>
<head>
<title>WC Admin Panel : Theme <?php echo htmlspecialchars($name); ?> Demo</title>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.ui.min.js"></script>
<link rel='stylesheet' type='text/css' href='<?php echo Base::home(); ?>css/reset.min.css'>
<link rel='stylesheet' type='text/css' href='<?php echo Base::home(); ?>css/ui/<?php echo htmlspecialchars($name); ?>/jquery-ui.css'>
<style type="text/css">
header {overflow:hidden;}
header .demo div {float:left;margin:5px 0 0 5px;padding:5px 10px;vertical-align:baseline;}
header .demo div .ui-icon {float:left;margin:2px 2px 0 0;}
nav {display:none;}
section {overflow:hidden;}
section .form {position:relative;float:left;width:auto;margin:5px;}
section .form .header {margin-bottom:10px;}
section .form .header .ui-icon {}
section .form p .ui-icon {float:left;margin:2px 2px 0 0;}
#radio {margin-top:10px;}
#button {float:right;margin:5px;}
</style>
<script type="text/javascript">
$(function()
{
 $("#button").button().click(function(){
  $("#dialog").dialog(
  {
   modal:true
  ,title:"Dialog"
  ,height:100
  //,buttons:[{text:"OK",click:function(){$("#dialog").dialog("close");}}]
  ,position:{my:"left bottom",at:"right bottom"}
  });
 })
 $("#radio").buttonset();
});
</script>
</head>
<body>
<header>
<div class="demo ui-widget">
<div class="ui-state-default"  ><span class="ui-icon ui-icon-check"></span> Default</div>
<div class="ui-state-hover"    ><span class="ui-icon ui-icon-gear" ></span> Hover</div>
<div class="ui-state-active"   ><span class="ui-icon ui-icon-star" ></span> Active</div>
<div class="ui-state-highlight"><span class="ui-icon ui-icon-info" ></span> Highlight</div>
<div class="ui-state-error"    ><span class="ui-icon ui-icon-alert"></span> Error</div>
</div>
</header>

<nav>
<div id="dialog">Lorem ipsum dolor sit amet.</div>
</nav>

<section>

<div class="form ui-dialog ui-widget ui-widget-content ui-corner-all">
<div class="header ui-dialog-titlebar ui-widget-header ui-corner-all">
<div class="ui-dialog-titlebar-close ui-corner-all">
<span class="ui-icon ui-icon-closethick"></span>
</div>
Buttonset
</div>
<p><span class="ui-icon ui-icon-info"></span>In suscipit faucibus urna.</p>
<div id="radio">
<input type="radio" id="radio1" name="radio"><label for="radio1">First</label>
<input type="radio" id="radio2" name="radio" checked="checked"><label for="radio2">Second</label>
<input type="radio" id="radio3" name="radio"><label for="radio3">Third</label>
</div>
</div>

<div id="button">Dialog</div>

<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

</section>

</body>
</html>
<?php
 }
}

?>