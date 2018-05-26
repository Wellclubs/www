<!doctype html>
<html lang="<?php echo Lang::current(); ?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<title><?php echo htmlspecialchars(Base::fullTitle()); ?></title>
<base href="<?php echo Base::bas() . Base::langPath() . Base::page() . '/'; ?>" />
<meta name="google-site-verification" content="uObo1mZZrxjiMHFUsYerAMl1A7LlDPEzwvLw-eK4iZQ" />
<link href="<?php echo Base::home(); ?>favicon.ico" rel="icon" type="image/vnd.microsoft.icon" />
<link href="<?php echo Base::home(); ?>favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.ui.min.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/base64.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/app.js"></script>
<?php
 $fnjs = Base::home() . 'jss/jquery.ui.datepicker-' . Lang::current() . '.js';
 if (Base::justFileExists(Base::root() . $fnjs))
  echo '<script type="text/javascript" src="' . $fnjs . '"></script>' . "\n";
?>
<script type="text/javascript" src="<?php echo Base::pro(); ?>maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/page.com.js"></script>
<script type="text/javascript">
<?php
 echo 'app.page="' . Base::page() . '";' . "\n";
 echo 'app.home="' . Base::home() . '";' . "\n";
 /*$app = &PageCom::app();
 foreach (PageCom::app() as $key => $value)
  echo "app.$key=" . JSON::encode($value) . ";\n";*/
 echo 'app.langs=' . JSON::encode(Lang::titles(), null) . ';' . "\n";
 echo 'app.lang="' . Lang::current() . '";' . "\n";
 //echo 'app.cdr={times:{},filter:{tmin:"",tmax:"",grp:"",srv:"",fname:"",sname:"",email:""}};' . "\n";
 echo 'app.cdr={times:{},filter:{},dnew:{params:{}}};' . "\n";
 echo 'app.cdr.fday=' . WDomain::firstDay() . ';' . "\n";
 echo 'app.cdr.grps=' . JSON::encode(PageComArtCdr::grps(), null) . ';' . "\n";
 echo 'app.cdr.srvs=' . JSON::encode(PageComArtCdr::srvs(), null) . ';' . "\n";
?>
</script>
<?php
 $theme = WTheme::active();
 $files = array( 'reset.min.css', "ui/$theme/jquery-ui.css" );
 $home = Base::home() . 'css/';
 $path = Base::root() . $home;
 foreach ($files as $file)
  if (Base::justFileExists($path . $file))
   echo "<link rel='stylesheet' type='text/css' href='$home$file'>\n";
?>
<style type="text/css">
body { padding:0px;margin:0px;box-sizing:border-box;background:#fff;font-family:verdana,sans-serif;font-size:16px;color:#888; }
article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section { display:block; }
body * { overflow:hidden; }
th,td { vertical-align:top; }
.center { text-align:center; }
input,textarea { outline:none; }
input:active,textarea:active { outline:none; }
:focus { outline:none; }
a { text-decoration: none; }
optgroup { background-color:#ddd;font-weight:bold; }

.ui-widget { font-size: 1.2em; }
.ui-widget-header,.ui-widget-content,.ui-state-default,.ui-state-hover,.ui-state-active { background-image:none !important; }

a.ax { -webkit-transition:all 0.3s ease-out;transition:all 0.3s ease-out; }
.block,.button,.btn,.databtn { -webkit-transition:background 0.3s ease-out;transition:background 0.3s ease-out; }

.icon-lang { width:16px;height:11px;background-repeat:no-repeat; }
<?php
 foreach (Lang::map() as $id => $lang) // IE does not support embedded images :-(
  echo '.icon-lang-' . $lang . ' { background-image:url(img/lang-' . $lang . '.png)!important; }' . "\n";
?>

header {  }
header a { color:inherit; }
header .frame .left { display:block;width:280px;margin:5px 5px 0 5px;padding:0 0 0 10px; }
header .frame .right { float:right;padding:9px;white-space:nowrap;font-size:1.1em;font-weight:bold; }
header .frame { width:100%; }
header .menu a { display:block;border:1px;line-height:37px;padding:0 20px 3px;white-space:nowrap;text-decoration:none;text-transform:uppercase;font-weight:bold; }
header .menu a .marker { height:3px;margin:0 -20px; }
header .menu a.active .marker { background-color:#000; }
header a.lang { display:block;margin:10px 0 0;border:1px solid #fff; }
header .icon-lang { margin:5px; }
header .right.client { padding-left:30px;background:url("<?php echo WClient::imageURI(); ?>") no-repeat 0 / 24px; }
header .ctrt { height:60px; }
header .ctrt .name { float:left;padding:10px 20px;font-size:32px;font-family:Helvetica; }
header .ctrt .select { float:left;width:80px;height:100%;background:url('pic/home-more.png') no-repeat 10px 16px;
 cursor:pointer;opacity:0.6;-webkit-transition:all 0.3s ease-out;transition:all 0.3s ease-out; }
header .ctrt .select:hover { opacity:1; }
header .ctrt .list { width:100%; }
header .ctrt ul { position:absolute;z-index:10;font-size:24px;display:none; }
header .ctrt ul a { display:block;padding:10px; }
header .title { font-size:1.6em;text-align:center; }

nav { display:none; }
.dialog { display:none; }
.dialog input[type="text"] { width:100%;border:0 !important;font-weight:normal; }
.dialog select { width:100%; }
.dialog textarea { width:100%;height:10em;border:0;overflow:auto; }
.dialog .prompt { margin-top:4px;padding-top:5px; }
.dialog table { width:100%; }
.dialog .btn { height:18px;width:18px;padding:0;font-size:0.6em;text-align:center;cursor:pointer; }

#dlg-input .bool { float:right; }
#dlg-input .titles input { margin:2px 0; }
#dlg-input .tip table input { text-align:right; }
#dlg-input .prcs { margin-top:5px; }
#dlg-input .prcs .item { margin:2px 0;padding:2px 25px 2px 3px; }
#dlg-input .prcs .item .btn { float:right;margin-right:-22px;border:1px;cursor:pointer; }
#dlg-input .prcs .item .btn span { margin:1px;padding:1px; }

#dlg-privs .priv { font-size:0.8em; }

#dlg-date {}

#dlg-booking-time select { border:0; }

#dlg-booking-srv select { border:0; }

#dlg-booking-clt input { display:block;width:100%;border:0; }
#dlg-booking-clt select { border:0;overflow-y:scroll; }

#dlg-booking-view td.center { text-align:center; }
#dlg-booking-view td.right { text-align:right; }

#dlg-booking-new input { display:block;width:100%;border:0; }
#dlg-booking-new select { display:block;width:100%;border:0; }
#dlg-booking-new .srv { clear:both; }
#dlg-booking-new .mtr { display:none; }

/*.ui-menu { z-index:100 !important; }*/
* html .ui-autocomplete { height:expression('300px'); }
.ui-autocomplete { max-height:300px;overflow-y:auto;overflow-x:hidden; }
.ui-autocomplete span.title { font-size:1.4em; }
.ui-autocomplete .cat { display:block;height:30px;padding:2px 5px 2px 50px;line-height:2em; }
.ui-autocomplete .cat img { position:absolute;left:5px; }

section { clear:both; }

article .main { margin:15px; }
article a { color:inherit; }
article { overflow:hidden }

article table.tabholder { width:100%; }

article table.tabholder .tags { width:300px; }
article table.tabholder .tags .tab { display:block;padding:20px 15px 20px;font-size:16px;text-decoration:none;text-transform:uppercase;letter-spacing:1px; }
article table.tabholder .tags .tab { border-top-width:0;border-left-width:0;border-right-width:0; }
article table.tabholder .tags .tab.active { cursor:default; }
article table.tabholder .tags .label { font-size:1.2em;font-weight:500; }

article table.tabholder .work .line {  }
article table.tabholder .work .line .filter { float:left;padding:10px 15px 0;  }
article table.tabholder .work .line .filter select {  }
article table.tabholder .work .line .taps { float:right; }
article table.tabholder .work .line .taps .tab { display:block;float:left;padding:10px 15px 10px;font-size:16px;text-decoration:none; }
article table.tabholder .work .line .taps .tab.active { cursor:default; }

article table.tabholder .tabs .tab { margin:15px; }
article table.tabholder .tabs .tab .const { background-color:#fff; }

article *>.button { overflow:hidden; }
article .button { padding:5px 10px;text-align:center;cursor:pointer; }
article a.button { display:block;text-decoration:none; }
article .button.left { float:left; }
article .button.right { float:right; }
article .menu .prompt { float:left;margin:5px; }
article .menu .item.button { margin:0 5px; }
article .action .button { margin:5px; }

article .side { float:left;width:300px;border:0;/*border-bottom:1px;/*padding:2px;*/ }
article .side .pane { overflow:hidden; }
article .side+div.main { margin-left:210px; }

article .side .ui-datepicker { width:100%;border:0;padding:0; }

article .no-data { padding:10px 0;font-size:1.2em;text-align:center; }

article .block { padding:10px; }
article .block:not(:last-child):not(.no-data) { margin-bottom:15px; }
article .block .title { border-bottom:1px #888 dotted;padding:5px 10px 0;font-size:1.2em;text-transform:uppercase; }
article .block .title a { display:block; }
article .block a.static { display:block;margin:5px;padding:5px;background-color:#fff; }
article .block a.simple { display:block;text-decoration:underline; }
/*article .main>:last-child,article .tabs .tab>:last-child,article .blocks>:last-child { margin-bottom:0; }*/

article .block .left { float:left;margin:10px; }
article .block .right { float:right;margin:10px; }
article .block table td.left,article .block table th.left,
article .block table td.right,article .block table th.right { float:none;margin:0; }
article .block .action .button.left,article .block .action .button.right { margin:5px; }
article .block .ui-widget-content { background-color:inherit;border:none; }
article .block.action .wrapper { margin:11px 5px 0 }

article .block.ctr.alien .title a.left { font-style:italic; }
article .block.ctr.alien .title a.right { text-transform:none; }
article .block.ctr .bnd { text-decoration:underline;font-weight:bold; }

article .no-data.small { float:left;padding:5px;font-size:0.8em; }

article table.list { width:100%; }
article table.list.small { width:auto; }
article table.list th { padding:5px;text-align:center;font-weight:bold; }
article table.list td { padding:1px; }
article table.list td.nw { white-space:nowrap; }

article table.form { width:100%; }
article table.form.small { width:auto; }
article table.form tr th { padding:2px 1px;font-weight:bold; }
article table.form tr td { padding:1px; }

article table.list.hdr-left th { text-align:left; }
article table.list.row-click tr[rowid] { cursor:pointer; }
article table.list.two-color tr[rowid]:nth-child(even) td { border-bottom:1px solid #fff;background:#fff; }

article .data a { display:block;padding:1px 2px;text-decoration:none; }

article .data.right a { text-align:right; }
article .data a[href]:hover { text-decoration:underline; }
article .data .btn { height:18px;text-align:center;cursor:pointer; }
article .data .btn[action] { padding:0 5px; }

article .data.edit { background:#fff !important;font-weight:normal !important; }
article .data.edit .btn { display:none;float:right;width:18px;font-size:0.6em; }
article .data.edit.able a { margin-right:22px; }
article .data.edit.able .btn { display:block; }

article .data .databtn { float:right;padding:1px 2px;cursor:pointer; }

#art-cdr table.book-sheet { border-style:hidden; }
#art-cdr table.book-sheet tr:nth-child(3) a { font-size:0.8em; }
#art-cdr table.book-sheet tr[rowid] td { border-bottom:1px dashed #ccc !important; }
#art-cdr table.book-sheet tr[rowid] td[field] { border-left:1px solid #ccc !important; }
/*#art-cdr table.book-sheet tr[rowid]:hover td  { border-bottom:1px dashed #f00 !important; }*/
#art-cdr table.book-sheet tr[rowid]:hover td * { color:#f00 !important; }
#art-cdr table.book-sheet tr[rowid] .data a[href] { background-color:#cdf; }
#art-cdr table.book-sheet .data .databtn { background-color:#cfd; }
#art-cdr table.book-sheet .data .databtn:empty { visibility:hidden; }
#art-cdr table.book-sheet .data .databtn:hover { background-color:#dfe; }

#art-cdr table.list tr[rowid]:hover td  { border-bottom:1px dashed #f00 !important; }

article .frame>* { margin:5px; }
article .viewer { min-height:1em;xmargin-bottom:15px; }
article .viewer .text.html a { text-decoration:underline; }
article .viewer .text,article .editor textarea { padding:10px;background-color:#fff;font-weight:normal; }
article .editor textarea { width:100%;height:20em;border:0;overflow:auto; }

article .tab .image>table { width:100%; }
article .tab .image .thumb { min-width:300px; }
article .tab .image img {  }
article .tab .image td table { width:500px;float:right; }
article .tab .image td table tbody th { min-width:100px; }
article .tab .image td table tbody td { min-width:100px; }

#art-home .tabs .msg.in { margin-right:100px; }
#art-home .tabs .msg.out { margin-left:100px; }
#art-home .tabs .msg .sent { float:left;margin:10px; }

#art-cdr .side .filter { margin:1px 0;border:0;white-space:nowrap;cursor:pointer; }
#art-cdr .side .filter .widget .text { padding:4px 10px;line-height:30px; }
#art-cdr .side .filter .widget .value { font-weight:normal; }
#art-cdr .side .filter .widget .using { display:none; }
#art-cdr .side .filter .widget .icon { display:block;margin:1px;padding:10px; }
#art-cdr .side .filter .widget .right { float:right; }
#art-cdr .side .filter .widget .left { float:left; }
#art-cdr .side .filter .widget .clear-select { visibility:hidden; }
#art-cdr .side .filter .widget[set='1'] .clear-select { visibility:visible; }
#art-cdr .side .filter .widget[set='1'] .empty { display:none; }
#art-cdr .side .filter .widget[set='1'] .using { display:block; }

#art-cdr .side .filter .widget .select { padding:8px 6px 0;line-height:30px;font-weight:bold; }
#art-cdr .side .filter .widget .select select { display:block;width:100%;border:0; }

#art-cdr .side .filter.date .widget[ani='1'] .using .ctrl { display:none; }
#art-cdr .side .filter.date .widget[ani='1'] .using .pin-on { display:none; }
#art-cdr .side .filter.date .widget[ani='0'] .using .pin-off { display:none; }
#art-cdr .side .filter.date .widget .nav .icon { padding:10px 11px; }
#art-cdr .side .filter.date .widget .nav .button { width:140px;margin:3px auto 0; }

#art-mtr .tabs .tab-privs .priv { margin-top:5px; }
#art-mtr .tabs .tab-privs .priv input { position:relative;top:1px; }

#art-srvs .grps a { text-decoration:none; }
#art-srvs .grps .grp table { width:100%; }
#art-srvs .grps .grp .head th.action { width:10px;white-space:nowrap; }
#art-srvs .grps .grp .head th.prompt .text { margin:10px;font-weight:bold; }
#art-srvs .grps .grp .head td .title { margin:10px; }
#art-srvs .grps .grp .body .srv .label { font-size:1.2em; }
#art-srvs .grps .grp .body .srv .tip { font-style:italic; }
#art-srvs .grps .grp .body .srv table { border-collapse:separate; }
#art-srvs .grps .grp .body .srv .line { border-bottom:1px #ccc dotted !important; }
#art-srvs .grps .grp .body .srv th { width:100px;text-align:right;vertical-align:bottom; }

</style>
</head>

<body>
<header>
<table class="frame layout"><tbody><tr>
<th width="300"><a href="<?php echo Base::home(); ?>" class="left"><img src="pic/wc-logo-32.png"></a></th>
<td><table class="menu"><tbody><tr>
<?php
$arts = &PageComArt::arts();
foreach ($arts as $id => $art)
 $art->putMenuItem();
?>
</tr></tbody></table></td>
<?php
if (Lang::used())
{
 foreach (Lang::map() as $id => $lang)
 {
  $class = ($id == Lang::current()) ? ' active ui-state-active' : '';
  $path = ($id != Lang::DEF()) ? ($id . '/') : '';
  //$uri = Base::makeChangeLangURI($lang);
  $uri = Base::home() . $path . Base::path() . Base::params();
  $title = $lang->title();
  echo '<th width="1"><a class="lang' . $class . '" href="' . $uri . '" path="' . $path . '" title="' . $title . '">' .
    '<span class="ui-icon icon-lang icon-lang-' . $id . '"></span></a></th>' . "\n";
 }
}
?>
<th width="1"><div class="right client"><?php echo WClient::name(); ?></div></th>
</tr></tbody></table>
<div class="mode ui-widget-header">
<?php
echo '<div class="ctrt">';
echo '<div class="name">' . htmlspecialchars(PageCom::pretitle()) . '</div>';
echo '<div class="select"></div>';
echo '<div class="list"><ul></ul></div>';
echo '</div>';
echo '<div class="title ui-widget-content">' . htmlspecialchars(PageCom::subtitle()) . '</div>';
?>
</div>
</header>

<nav>

<div class="style">
<div class="ui-widget-header"></div>
<div class="ui-widget-content"></div>
<div class="ui-state-default"></div>
<div class="ui-state-hover"></div>
<div class="ui-state-active"></div>
<div class="ui-state-highlight"></div>
<div class="ui-state-error"></div>
</div>

<div class="dialog" id="dlg-confirm">
<div class="prompt"></div>
</div>

<div class="dialog" id="dlg-input">
<div class="name">
<div class="prompt">
<div class="bool"><input type="checkbox" /></div>
<span class="text"></span>
</div>
<div class="edit"><input type="text" class="required" /></div>
</div>
<div class="uri">
<!--div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Web site')); ?></div-->
<div class="edit"><input type="text" class="uri" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Web site')); ?>" /></div>
</div>
<div class="firstname">
<!--div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Web site')); ?></div-->
<div class="edit"><input type="text" class="firstname" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'First name')); ?>" /></div>
</div>
<div class="lastname">
<!--div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Web site')); ?></div-->
<div class="edit"><input type="text" class="lastname" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Last name')); ?>" /></div>
</div>
<div class="email">
<!--div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'E-mail')); ?></div-->
<div class="edit"><input type="text" class="required email" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'E-mail')); ?>" /></div>
</div>
<div class="prc">
<!--div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Procedure')); ?></div-->
<div class="prcs"></div>
<div class="edit"><table><tr>
<td><input type="text" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Procedure')); ?>" /></td>
<td width="1"><div class="btn ui-state-default">...</div></td>
</tr></table></div>
</div>
<div class="curr">
<div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Currency')); ?></div>
<div class="edit"><table><tr>
<td><input type="text"
 placeholder="<?php echo htmlspecialchars(Lang::getPageWord('hint', 'prompt', 'Currency')); ?>"
 placeholder-focus="<?php echo htmlspecialchars(Lang::getPageWord('hint', 'Type a currency code')); ?>"
 /></td>
<td width="1"><div class="btn ui-state-default">...</div></td>
</tr></table></div>
</div>
<div class="bnd">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Select a brand')); ?></div>
<div class="edit"><select></select></div>
</div>
<div class="level">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Pricing level')); ?></div>
<div class="edit"><select></select></div>
</div>
<div class="role">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Select a role')); ?></div>
<div class="edit"><select></select></div>
</div>
<div class="mtr">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Select a master')); ?></div>
<div class="edit"><select></select></div>
</div>
<div class="title">
<div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Title')); ?></div>
<div class="edit"><input type="text" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Title')); ?>" /></div>
</div>
<?php
/*echo '<div class="titles">' . "\n";
echo '<div class="prompt">' . htmlspecialchars(Lang::getSiteWord('title', 'Translations')) . '</div>' . "\n";
foreach (Lang::map() as $id => $lang)
 echo '<div class="edit" lang="' . $id . '"><input type="text" placeholder="' . htmlspecialchars($lang->title()) . '" /></div>' . "\n";
echo '</div>' . "\n";*/
?>
<div class="tip">
<table><tr><th>
<div class="duration">
<div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Duration')); ?></div>
<div class="edit"><input type="text" field="duration" class="required"
 placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Duration')); ?>"
 placeholder-focus="<?php echo htmlspecialchars(Lang::getPageWord('hint', 'in minutes')); ?>"
 /></div>
</div>
</th><th width="4"></th><th>
<div class="price">
<div class="prompt"><?php //echo htmlspecialchars(Lang::getPageWord('prompt', 'Price')); ?></div>
<div class="edit"><input type="text" field="price" class="required"
 placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Price')); ?>"
 placeholder-focus="<?php echo htmlspecialchars(Lang::getPageWord('hint', 'in currency')); ?>"
 /></div>
</div>
</th></tr></table>
</div>
</div>

<div class="dialog" id="dlg-privs">
<?php
foreach (WPriv::getListPrGr() as $grId => $prgr)
{
 echo '<div>' . "\n";
 echo '<div class="title">';
 echo htmlspecialchars(Lang::getDBTitle(WPriv::TABLE_PRGR, 'prgr', $grId, $prgr['name']));
 echo '</div>' . "\n";
 foreach ($prgr['privs'] as $prId => $prName)
 {
  echo '<div class="priv">';
  echo '<input type="checkbox" priv="' . $prId . '">&nbsp;';
  echo htmlspecialchars(Lang::getDBTitle(WPriv::TABLE_PRIV, 'priv', $prId, $prName));
  echo '</div>' . "\n";
 }
 echo '</div>' . "\n";
}
?>
</div>

<div class="dialog" id="dlg-file">
<div class="prompt"></div>
<div class="edit"><input type="file" name="image" /></div>
</div>

<div class="dialog" id="dlg-date">
<div class="prompt"></div>
<div class="edit"><input type="text" name="date" readonly /></div>
</div>

<div class="dialog" id="dlg-booking-time">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Time')); ?></div>
<div class="value"><table><tr>
<td width="45%" class="tmin"><select></select></td>
<td>&nbsp;-&nbsp;</td>
<td width="45%" class="tmax"><select></select></td>
</tr></table></div>
</div>

<div class="dialog" id="dlg-booking-srv">
<div class="grp">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Group')); ?></div>
<div class="value"><select></select></div>
</div>
<div class="srv">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Service')); ?></div>
<div class="value"><select></select></div>
</div>
</div>

<div class="dialog" id="dlg-booking-clt">
<div class="fname"><input placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'First name')); ?>"/></div>
<div class="sname"><input placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Last name')); ?>"/></div>
<div class="phone"><input placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Phone number')); ?>"/></div>
<div class="email"><input placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Email')); ?>"/></div>
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Result')); ?></div>
<select class="result" size="5"></select>
</div>

<div class="dialog" id="dlg-booking-view">
<table>
<tr class="client"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Client')); ?></td><td class="value"></td></tr>
<tr class="service"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Service')); ?></td><td class="value"></td></tr>
<tr class="date"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Date')); ?></td><td class="value center"></td></tr>
<tr class="time"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Time')); ?></td><td class="value center"></td></tr>
<tr class="dura"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Duration')); ?></td><td class="value center"></td></tr>
<tr class="price"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Price')); ?></td><td class="right"><span class="value"></span> <span class="curr"></span></td></tr>
<tr class="disc"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Discount')); ?></td><td class="right"><span class="value"></span>%</td></tr>
<tr class="fact"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Final price')); ?></td><td class="right"><span class="value"></span> <span class="curr"></span></td></tr>
<tr class="qty"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Quantity')); ?></td><td class="value right"></td></tr>
<tr class="total"><td class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Total amount')); ?></td><td class="right"><span class="value"></span> <span class="curr"></span></td></tr>
</table>
</div>

<div class="dialog" id="dlg-booking-new">
<div class="name"><input placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Client name')); ?>"/></div>
<div class="phone"><input placeholder="<?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Phone number')); ?>"/></div>
<div class="srv"><select></select></div>
<div class="tip"><select></select></div>
<div class="mtr"><select></select></div>
</div>

<div class="dialog" id="dlg-message">
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Subject')); ?></div>
<div class="subject"><input type="text" placeholder="<?php echo htmlspecialchars(Lang::getPageWord('hint', 'Type the message subject here')); ?>"/></div>
<div class="prompt"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Message')); ?></div>
<div class="message"><textarea></textarea></div>
</div>

</nav>

<section>

<?php
foreach ($arts as $art)
 $art->putArticle();
?>
</section>

<footer class="ui-widget-header"></footer>

<script type="text/javascript">
<?php
 echo "app.acMenuProc=\n" . str_replace(',{', "\n,{", JSON::encode(WProc::acQueryAll(), null)) . ";\n";
 /*$prc = PageBook::autocompleteMenuProc('', true);
 foreach ($prc as $key => $value)
  $prc[$key] = array('value' => $value['id'], 'label' => $value['value']);
 echo "app.acMenuProc=" . $prc . ";\n";*/

 //echo "/*\n" . print_r($_SERVER, true) . "*/\n";
?>
app.prcs=[];
$(app.acMenuProc).each(function(i,item){if(item.id[0]=='p')app.prcs[parseInt(item.id.substr(1))]=item.value;});
app.txt=
{
 button_ok:'OK'
,button_cancel:'<?php echo Lang::getSiteWord('button','Cancel');?>'
,button_delete:'<?php echo Lang::getSiteWord('button','Delete');?>'
,button_deltel:'<?php echo Lang::getSiteWord('button','Delete');?>'
,button_close:'<?php echo Lang::getSiteWord('button','Close');?>'
,button_copy:'<?php echo Lang::getSiteWord('button','Copy');?>'
,button_order:'<?php echo Lang::getSiteWord('button','Order');?>'
,button_showup:'<?php echo Lang::getSiteWord('button','Show up');?>'
,button_noshow:'<?php echo Lang::getSiteWord('button','No show');?>'
,button_delmsg:'<?php echo Lang::getSiteWord('button','Delete message');?>'
,button_delgrp:'<?php echo Lang::getSiteWord('button','Delete group');?>'
,button_addsrv:'<?php echo Lang::getSiteWord('button','Add new service');?>'
,button_addpkg:'<?php echo Lang::getSiteWord('button','Add new package');?>'
,button_delprc:'<?php echo Lang::getSiteWord('button','Delete');?>'
,button_send:'<?php echo Lang::getSiteWord('button','Send');?>'
,button_search:'<?php echo Lang::getSiteWord('button','Search');?>'
,value_true:'<?php echo Lang::getSiteWord('bool','Yes');?>'
,value_false:'<?php echo Lang::getSiteWord('bool','No');?>'
,prompt_group_title:'<?php echo Lang::getSiteWord('prompt','Group title');?>'
,prompt_package_name:'<?php echo Lang::getSiteWord('prompt','Package name');?>'
,prompt_all_services:'<?php echo Lang::getSiteWord('prompt','All services');?>'
,prompt_start_date:'<?php echo Lang::getSiteWord('prompt','Start date');?>'
,obj_service_group:'<?php echo Lang::getSiteWord('obj','Service group');?>'
,msg_no_more_masters:'<?php echo Lang::getSiteWord('msg','No more masters available');?>'
,title_upload_image:'<?php echo Lang::getSiteWord('title','Upload image');?>'
,title_last_visited:'<?php echo Lang::getSiteWord('title','Last visited');?>'
,title_booking_details:'<?php echo Lang::getSiteWord('title','Booking details');?>'
,title_booking_add_new:'<?php echo Lang::getSiteWord('title','Add a new booking');?>'
,title_booking_any_time:'<?php echo PageComArtCdr::titleAnyTime();?>'
,title_booking_all_groups:'<?php echo PageComArtCdr::titleAllGroups();?>'
,title_booking_all_masters:'<?php echo PageComArtCdr::titleAllMasters();?>'
,title_booking_all_resources:'<?php echo PageComArtCdr::titleAllResources();?>'
,title_booking_all_services:'<?php echo PageComArtCdr::titleAllServices();?>'
,title_booking_all_clients:'<?php echo PageComArtCdr::titleAllClients();?>'
,title_booking_select_time:'<?php echo Lang::getSiteWord('title','Select a time');?>'
,title_booking_select_srv:'<?php echo Lang::getSiteWord('title','Select a service');?>'
,title_booking_select_clt:'<?php echo Lang::getSiteWord('title','Select a client');?>'
,prompt_select_image_file:'<?php echo Lang::getSiteWord('prompt','Select an image file');?>'
,error_file_too_large:'<?php echo Lang::getSiteWord('error','File size is too large');?>'
,protocol_error:'<?php echo Lang::getSiteWord('js','Protocol error');?>'
,server_error:'<?php echo Lang::getSiteWord('js','Server error');?>'
,user_error:'<?php echo Lang::getSiteWord('js','User error');?>'
};
</script>
</body>
</html>