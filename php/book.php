<!doctype html>
<html lang="<?php echo Lang::current(); ?>">
<head>
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
<meta name="HandheldFriendly" content="true"/>
<meta name="MobileOptimized" content="width"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="format-detection" content="telephone=no"/>
<meta name="format-detection" content="address=no"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate"/>
<meta charset="utf-8" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php if (Base::useRobots()) { ?>
<meta name="robots" content="noindex,follow" />
<?php } ?>
<meta name="description" content="<?php echo htmlspecialchars(PageBook::getDescr()); ?>" />
<meta name="keywords" content="<?php echo htmlspecialchars(PageBook::getKeyWords()); ?>" />
<meta name='yandex-verification' content='72020f15efcdf04c' />
<title><?php echo htmlspecialchars(Base::fullTitle()); ?></title>
<base href="<?php echo Base::bas() . Base::langPath(); ?>" />
<meta name="google-site-verification" content="uObo1mZZrxjiMHFUsYerAMl1A7LlDPEzwvLw-eK4iZQ" />
<link href="<?php echo Base::home(); ?>favicon.ico" rel="icon" type="image/vnd.microsoft.icon" />
<link href="<?php echo Base::home(); ?>favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
<!--[if lt IE 9]>
<script src="<?php echo Base::pro(); ?>html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/jquery.ui.min.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/base64.js"></script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/app.js"></script>
<?php
 $fnjs = Base::home() . 'jss/jquery.ui.datepicker-' . Lang::current() . '.js';
 if (Base::justFileExists(Base::root() . $fnjs))
  echo '<script type="text/javascript" src="' . $fnjs . '"></script>' . "\n";
 echo '<script type="text/javascript" src="' . Base::pro() . 'maps.google.com/maps/api/js?sensor=false"></script>' . "\n";
 foreach (Base::errors() as $error)
  echo Base::htmlComment($error, true);
?>
<script type="text/javascript">
if(location.href.indexOf('#')>0)
{
 if(location.href.substr(location.href.length-1,1)=='#')
 {
  location.href=location.href.substr(0,location.href.length-1);
  history.pushState('',document.title,location.pathname);
 }
 else if(location.hash=='#_=_')
 {
  location.hash='';
  history.pushState('',document.title,location.pathname);
 }
}
app.noresize=false;
app.seance={};
<?php
if (WClient::id())
{
 echo "app.seance.online=true\n";
 echo "app.seance.userid=\"" . WClient::id() . "\"\n";
 echo "app.seance.username=\"" . Util::strJS(WClient::name()) . "\"\n";
}
else
 echo "app.seance.online=false\n";
?>
app.topw={href:null};
app.title='<?php echo Util::strJS(Base::title());?>';
app.base='<?php echo Util::strJS(Base::bas() . Base::langPath());?>';
app.msie=<?php echo Base::msie() ? 'true' : 'false';?>;
app.page='<?php echo Util::strJS(Base::page());?>';
app.mode='<?php echo Util::strJS(Base::mode());?>';
app.list={index:0,timeoutSearch:null};
app.bgs={timeout:null};
app.top={img:{WIDTH1:<?php echo WTop::WIDTH1; ?>,HEIGHT1:<?php echo WTop::HEIGHT1; ?>,WIDTH2:<?php echo WTop::WIDTH2; ?>,HEIGHT2:<?php echo WTop::HEIGHT2; ?>}};
<?php
echo "app.home='" . Util::strJS(Base::home()) . "';\n";
echo "app.list.filter=" . JSON::encode(PageBookList::filter(), null) . ";\n";
echo "app.list.result=" . JSON::encode(PageBookList::result(), 0) . ";\n";
if ((Base::mode() == 'srv') && array_key_exists('paystatus', $_GET))
{
 $status = $_GET['paystatus'];
 if ($status == 'a')
  echo "app.topw.msg='" . Util::strJS(Lang::getWord('message', 'Payment operation is complete', 'srv')) . "';\n";
 elseif ($status == 'd')
  echo "app.topw.msg='" . Util::strJS(Lang::getWord('message', 'Payment operation is declined by the service', 'srv')) . "';\n";
 elseif ($status == 'c')
  echo "app.topw.msg='" . Util::strJS(Lang::getWord('message', 'Payment operation is cancelled by the user', 'srv')) . "';\n";
 $uri = Util::removeUrlParam(Util::parseUrl(Base::path() . Base::params()), 'paystatus');
 echo "app.topw.msguri='" . Util::strJS(Util::buildUrl($uri)) . "';\n";
}
elseif (Base::topwMsg())
{
 echo "app.topw.msg=\"" . Util::strHTMLJS(Base::topwMsg()) . "\";\n";
 if (Base::topwUri())
  echo "app.topw.msguri='" . Util::strJS(Base::topwUri()) . "';\n";
}
if (WClient::chgpwd())
 echo "app.chgpwd=true;\n";
?>
app.ctr={id:null,groups:[]};
<?php
echo 'app.numCharMil="' . Lang::current()->charMil() . "\";\n";
echo 'app.curr=' . JSON::encode(WCurrency::current()->objs(), null) . ";\n";
$loc = null;
$addr = null;
$logoInfo = WCentre::logoInfo();
$images = PageBookCtr::images();
$groups = null;
$ratings = null;
$reviews = null;
$metros = null;
$phones = null;
$sched = null;
$descr = '';
if (WCentre::id())
{
 echo 'app.ctr.id=' . WCentre::id() . ";\n";
 echo 'app.ctr.title="' . Util::strJS(WCentre::title()) . "\";\n";
 echo 'app.ctr.type=' . WCentre::typeId() . ";\n";
 echo 'app.ctr.typeT="' . Util::strJS(WCentre::typeTitle()) . "\";\n";
 if (strlen(WCentre::currencyId()))
  echo 'app.ctr.curr=' . JSON::encode(WCurrency::makeObjs(WCentre::currencyId()), null) . ";\n";
}
if (Base::mode() == 'ctr')
{
 $loc = WCentre::loc();
 $addr = WCentre::address();
 $groups = PageBookCtr::groups();
 $ratings = PageBookCtr::ratings();
 $reviews = PageBookCtr::reviews();
 $metros = WCentre::metros();
 $phones = WCentre::phones();
 $sched = WCentre::sched();
 //$descr = WCentre::descr();
 //if (!$descr)
 // $descr = WBrand::descr();
 echo 'app.ctr.bnd="' . WBrand::id() . "\";\n";
 echo 'app.ctr.bndT="' . Util::strJS(WBrand::title()) . "\";\n";
 echo 'app.ctr.addr="' . Util::strJS($addr) . "\";\n";
 //echo 'app.ctr.descr="' . Util::strHTMLJS($descr) . "\";\n";
 echo 'app.ctr.loc=' . JSON::encode($loc, null) . ";\n";
 echo 'app.ctr.logo="' . $logoInfo['src'] . "\";\n";
 echo 'app.ctr.images=' . JSON::encode($images, null) . ";\n";
 echo 'app.ctr.groups=' . JSON::encode($groups, null) . ";\n";
 echo 'app.ctr.ratings=' . JSON::encode($ratings, null) . ";\n";
 echo 'app.ctr.reviews=' . JSON::encode($reviews, null) . ";\n";
 echo 'app.ctr.metros=' . JSON::encode($metros, null) . ";\n";
 echo 'app.ctr.phones=' . JSON::encode($phones, null) . ";\n";
 echo 'app.ctr.sched=' . JSON::encode($sched, null) . ";\n";
}
?>

app.bnd={id:null,groups:[]};
<?php
if (Base::mode() == 'bnd')
{
 echo 'app.bnd.id="' . WBrand::id() . "\";\n";
 echo 'app.bnd.title="' . Util::strJS(WBrand::title()) . "\";\n";
 echo 'app.bnd.logo="' . WBrand::logoURI() . "\";\n";
 echo 'app.ctr.images=' . JSON::encode($images, null) . ";\n";
 //echo 'app.ctr.descr="' . Util::strHTMLJS(WBrand::descr()) . "\";\n";
}
?>

app.srv={id:null};
<?php
if (Base::mode() == 'srv')
{
 $ratings = PageBookSrv::ratings();
 $reviews = PageBookCtr::reviews();
 echo 'app.srv.id="' . WService::id() . "\";\n";
 echo 'app.srv.title="' . Util::strJS(WService::title()) . "\";\n";
 echo 'app.srv.date="' . Util::date2str(PageBookSrv::date()) . "\";\n";
 echo 'app.srv.tip=' . PageBookSrv::tip() . ";\n";
 echo 'app.srv.tips=' . JSON::encode(PageBookSrv::tips(), null) . ";\n";
 if (!WCentre::id())
 {
  echo 'app.ctr.id=' . WService::centreId() . ";\n";
  echo 'app.ctr.title="' . Util::strJS(WCentre::getTitle(WService::centreId())) . "\";\n";
 }
 //echo 'app.ctr.logo="' . $logoInfo['src'] . "\";\n";
 //echo 'app.ctr.images=' . JSON::encode($images, null) . ";\n";
 echo 'app.ctr.ratings=' . JSON::encode(PageBookSrv::ratings(), null) . ";\n";
 echo 'app.ctr.reviews=' . JSON::encode(PageBookSrv::reviews(), null) . ";\n";
 //echo 'app.srv.descr="' . Util::strHTMLJS(WService::descr()) . "\";\n";
 //echo 'app.srv.restr="' . Util::strHTMLJS(WService::restr()) . "\";\n";
 //echo 'app.srv.notes="' . Util::strHTMLJS(WService::notes()) . "\";\n";
}

?>

app.pay={};
<?php
$cltStr = '{}';
if (Base::mode() == 'clt')
{
 $clt = PageBookClt::getPageData();
 if ($clt)
  $cltStr = json_encode($clt);
}
echo "app.clt=" . $cltStr . ";\n";
?>

app.g={map:null,geo:null};
</script>
<script type="text/javascript" src="<?php echo Base::home(); ?>jss/page.book.js"></script>
<?php
 $theme = WTheme::active();
 $files = array( 'reset.min.css', 'fonts.css', "ui/$theme/jquery-ui.css" );
 $home = Base::home() . 'css/';
 $path = Base::root() . $home;
 foreach ($files as $file)
  if (Base::justFileExists($path . $file))
   echo "<link rel='stylesheet' type='text/css' href='$home$file'>\n";
?>
<style type="text/css">
article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section { display:block; }
body,a { font:normal 14px helvetica;color:#444; }
th,td { vertical-align:top; }
span.meta { display:none; }
input,textarea { outline:none; }
input:active,textarea:active { outline:none; }
:focus { outline:none; }

ol[type="1"]>li { list-style-type:decimal; }
ol[type="0"]>li { list-style-type:decimal-leading-zero; }
ol[type="a"]>li { list-style-type:lower-alpha; }
ol[type="aa"]>li { list-style-type:upper-alpha; }
ol[type="i"]>li { list-style-type:lower-roman; }
ol[type="ii"]>li { list-style-type:upper-roman; }
ol[type="g"]>li { list-style-type:lower-greek; }
ol[type="ar"]>li { list-style-type:armenian; }
ol[type="gr"]>li { list-style-type:georgian; }

input[type="checkbox"] { display:none; }
input[type="checkbox"] + label { cursor:pointer; }
input[type="checkbox"] + label::before { display:inline-block;height:16px;width:16px;margin:2px 4px -2px;content:"";background:url(pic/checkbox.png) no-repeat; }
input[type="checkbox"]:checked + label::before { background-position:0 -16px; }

a.button { display:block;padding:10px;border-radius:3px;
 font-family:inherit;font-size:inherit;color:#fff !important;
 text-align:center;text-decoration:none; }
a.button:hover { opacity:0.8; }

table.layout { table-layout:fixed;width:100%; }
table.layout td { overflow:hidden; }
footer table.layout { table-layout:auto;width:100%; }


.noselect { -webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none; }

.big-title,.big-title * { color:#333; }

.search-frame { position:relative;display:inline-block;border:1px solid #ccc;overflow:hidden; }
.search-edit { height:28px;padding:4px 25px;border:0;color:#717171; }
.search-icon-left,.search-icon-right { position:absolute;top:10px;cursor:pointer; }
.search-icon-left { width:16px;height:16px;left:4px;background-repeat:no-repeat; }
.search-icon-left.search { background-image:url("pic/icon-search16.png"); }
.search-icon-left.geoloc { background-image:url("pic/icon-geoloc16.png"); }
.search-icon-left.calend { background-image:url("pic/icon-calend16.png"); }
.search-icon-right { right:4px;visibility:hidden; }
.search-frame.has-text .search-icon-right { visibility:visible; }

.ui-menu { z-index:100 !important; }
ul.ui-menu.ui-autocomplete { overflow:hidden; }
* html .ui-autocomplete { height:expression('300px'); }
.ui-autocomplete { max-height:300px;border:none;padding:0;overflow-y:auto;overflow-x:hidden; }
.ui-autocomplete.ui-menu .cat span.title { font-size:16px;font-weight:bold; }
.cat.ui-menu-item a i { font-size:0.9em;opacity:0.5; }
.ui-menu-item a b { font:inherit;color:#ff3333; }

header { background:#fff;overflow:hidden;white-space:nowrap; }
.ui-datepicker { z-index:999 !important; }
section { /*margin-top:60px;*/background:#f5f5f5; }

header col.header-col-left { width:40%; }
header col.header-col-logo { width:20%; }
header col.header-col-right { width:40%; }

header .hmenu-button {display:none;position:relative;z-index:92;float:left;margin:3px 25px 0 8px;}
header .logo { margin-top:3px;display:block; }

header .search-frame .search-edit { padding-top:0;padding-bottom:0; }
header .search-frame.brand { display:block;float:left;margin:15px 0 0 30px; }
header .search-frame.brand .search-edit { width:200px; }
header .search-icon-left, header .search-icon-right { top:6px;}

header .button { display:block;float:left;margin-top:14px;font-size:16px;visibility:hidden;padding:4px 10px; }
header .clickable { display:block;float:left;border:0;margin:20px 10px 0;padding:0 5px;background:none; }
header .clickable,header .clickable a { font-weight:bold;font-size:16px;text-decoration:none; }
header .lang { margin-left:0;margin-right:0;padding:0; }
header .lang.active { background:#ddd; }
header .icon-lang { margin:5px; }

header .header-right { padding-right:20px; }
header .header-right>div { float:right; }

header .hmenu-background {position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(255,255,255,0.4);z-index:90;display:none;}
header .hmenu {display:none;align-content:stretch;min-width:1000px;}
header .hmenu-on {display:flex;}
header .hmenu .hitem {display:block;text-align:center;cursor:pointer;}
header .hmenu .hitem-divider {display:block;border-left:1px solid lightgrey;border-top:1px solid whitesmoke;height:auto;margin:5px 1px;}
header .hmenu .hitem a.top .ui-menu-icon {margin-top:2px;}
header .hmenu .hitem .hsubmenu {display:none;position:absolute;z-index:10;}

nav { position:relative;overflow:hidden; }
nav>* { display:none; }
nav .topw { position:relative;margin:10px auto 20px; }
nav .topw.restore { margin:auto; }
nav .topw .ui-icon { position:absolute;top:1px;right:5px;cursor:pointer; }
nav .topw .title {margin:10px auto;width:90%;text-align: center;}
nav .topw .title,nav .topw .title * { font-size:24px; }
nav .topw .note { margin:5px 0 0;font-size:12px; }
nav .topw table { display:inline-table; }
nav .topw td { padding:0;text-align:center; }
nav .topw td .button { height:32px;border:none;padding:0 10px;line-height:30px; }
nav .topw td .input { height:30px;border:1px solid #aaa;overflow:hidden; }
nav .topw td input {display:block;height:30px;border:none;padding:0 1% 0 1%;width:98%;}
nav .topw .sn-split { margin:10px;line-height:32px; }
nav .topw .sn-hr { margin-top:8px; }
nav .topw a.link:focus { font-weight:bold; }
nav .topw .button { height:14px;white-space:nowrap; }
nav .topw .button.auth { padding-left:32px;background:2px 4px no-repeat;color:#fff;font-weight:bold; }
nav .topw .button.auth[net="gp"] { background-image:url(pic/social-gp.png);background-color:#D54035; }
nav .topw .button.auth[net="fb"] { background-image:url(pic/social-fb.png);background-color:#3B5998; }
nav .topw.login { max-width:1000px; margin:auto;text-align:center; }
nav .topw.login .form { margin:auto;text-align:center; }
nav .topw.login .pass input { width:97%;padding:0 1.5% 0 1.5%;margin:0; }
nav .topw .email input { width:97%;padding:0 1.5% 0 1.5%;margin:0; }

nav .topw.signup table { display:table; }
nav .topw.signup {max-width:650px; margin:auto;text-align:center;}
nav .topw.signup .firstname input {width:97%;padding:0 1.5% 0 1.5%;margin:0;}
nav .topw.signup .lastname input {width:97%;padding:0 1.5% 0 1.5%;margin:0;}
nav .topw.signup .pass input {width:97%;padding:0 1.5% 0 1.5%;margin:0;}
nav .topw.signup .button.main {width:122px;}

nav .topw.passwd { margin:auto; }

nav .nav-pop {position:fixed;right:5%;bottom:0px;z-index:99;background-color:#3c948b;color:white;width:250px;padding:0 20px 5px;border-radius:20px 20px 0 0;text-align:justify;}
nav .nav-pop a {color:white}
nav .nav-pop .pop-subject{font-size:18px;padding:15px 0 5px;width:100%;text-align:center;}
nav .nav-pop .pop-message{padding-bottom:10px;}

#client-menu {display:none;position:fixed;right:0;top:40px;z-index:100;<?php if (Base::msie()) echo 'width:100px;';?>}

#button-client { height:23px;margin-top:14px;padding:5px 5px 0 30px;background:url("<?php echo WClient::imageURI(); ?>") no-repeat 0 / 24px; }

.icon-lang { display:block;width:16px;height:11px;background-repeat:no-repeat; }
<?php
 foreach (Lang::map() as $lang => $Lang) // IE does not support embedded images :-(
  echo '.icon-lang-' . $lang . ' { background-image:url(img/lang-' . $lang . '.png)!important; }' . "\n";
?>

div.dialog { display:none; }
div.dialog * { font-size:12px; }
div.dialog.form label,div.dialog.form a { display:block;margin-top:10px; }
div.dialog input.text {width:97%;padding:0 1.5% 0 1.5%;margin:0;}
div.dialog .button.auth { height:20px;margin-top:10px;padding:4px 10px 0 32px;
background:2px 0 no-repeat;color:#eee;font-family:arial;font-size:14px;font-weight:bold;
cursor:pointer;text-decoration:none;text-align:center; }
div.dialog .button.auth:hover { color:#fff; }
div.dialog .button.auth[net="gp"] { background-image:url(pic/social-gp.png);background-color:#D54035; }
div.dialog .button.auth[net="fb"] { background-image:url(pic/social-fb.png);background-color:#3B5998; }
div.dialog .social-hr { text-align:center; }
div.dialog .social-hr span { display:inline-block;margin:0;padding:8px;font-size:10px;line-height:16px;background-color:#fff;text-transform:uppercase; }
div.dialog .social-hr hr { margin:-18px auto 10px auto;width:90%; }

#dlg-review tr { border:0; }
#dlg-review .divider { width:10px; }
#dlg-review h4 { margin-bottom:5px;padding:1px 2px; }
#dlg-review .stars { margin:2px; }
#dlg-review .stars * { width:0; }
#dlg-review .ctr-rates label { display:block;width:120px; }
#dlg-review .prc-rates { width:300px;height:100px;overflow-y:auto; }
#dlg-review .prc-rates label { display:block;width:200px; }
#dlg-review textarea { width:100%;margin:5px 0; }
#dlg-review input { margin-right:5px; }

#dlg-review-comment tr { border:0; }
#dlg-review-comment textarea { width:100%;margin:5px 0; }
#dlg-review-comment input { margin-right:5px; }

#dlg-review-cavil tr { border:0; }
#dlg-review-cavil textarea { width:100%;margin:5px 0; }
#dlg-review-cavil input { margin-right:5px; }

#dlg-clt-firstname input.name {width:97%;padding:0 1.5% 0 1.5%;margin:0;}
#dlg-clt-lastname input.name {width:97%;padding:0 1.5% 0 1.5%;margin:0;}
#dlg-clt-birthday .birthday input { width:1.33em; }
#dlg-clt-birthday .birthday input.byear { width:2.33em; }
#dlg-clt-note .value { min-width:200px;min-height:150px;width:96%;height:96%;resize:none; }

article { position:relative;display:none;width:100%;overflow:hidden; }
article.active { display:block; }

article .obj-header * { text-decoration:none; }
article .obj-header .title { padding:10px;border:none;background-image:none; }
article .obj-header .title,article .obj-header .title * { font-size:20px;font-weight:bold; }
article .obj-header .subtitle { padding:5px 10px;border:none;background-image:none; }
article .obj-header .subtitle,article .obj-header .subtitle * { font-size:16px; }
article .topic { margin-bottom:10px; }
article .detail .topic { background:#fff; }
article .detail .topic .text { padding:0 10px;line-height:20px;text-align:justify; }

article h1 { text-align:center;font-size:2em;line-height:1.5em; }
article>.detail { overflow:hidden; }

footer { padding:15px 0 20px;background-color:#e5e5e5; }
footer .col-accept { width:200px; }
footer .col-follow { width:200px; }
footer td { padding:0 10px; }
footer .flink,footer .ftext { font-size:14px;text-decoration:none; }
footer .ftext { display:block;padding-bottom:20px;padding-top:5px; }
footer .copy { margin:10px 0 0 0;font-size:12px; }

div.const { width:282px;height:350px;margin:10px auto;background:url(pic/adm/const.jpg) no-repeat center; }

/*#art-home { height:80%; }*/

#home-main { -visibility:hidden; }

#home-slideshow { position:absolute;left:0;top:0;width:100%;height:100%;background-repeat:no-repeat;background-size:100%; }
#home-slideshow .slide { position:absolute;display:none;overflow:hidden;z-index:-1; }
#home-slideshow .image { position:relative;background-repeat:no-repeat;background-size:cover;background-position:50%; }
/*#home-slideshow img { position:absolute;display:none; }*/
#home-slideshow .prev { display:block;z-index:0; }
#home-slideshow .next { display:block;z-index:1; }
#home-slideshow .active { display:block;z-index:2; }

#home-more { position:fixed;z-index:3;bottom:60px;left:50%;margin-left:-30px;cursor:pointer; }

#home-adv .adv-row { overflow:hidden; }
#home-adv .adv-title { padding: 30px 16px 0;font-size:22px;text-transform:uppercase; }
#home-adv table { border-collapse:separate;border-spacing:10px; }
#home-adv a { display:block;text-decoration:none; }
#home-adv img { display:block; }
/* big */
#home-adv .adv-row.big { }
#home-adv .big td {position:relative;padding-bottom:10px;}
#home-adv .big .img-title { position:relative;margin:auto;margin-top:-40%;width:90%;text-align:center;font-size:22px;color:#fff;text-shadow:0px 2px 2px rgba(102, 102, 153, 0.5);}
#home-adv .big .book-button {text-transform:uppercase;text-align:center;position:relative;width:30%;min-width:120px;margin:auto;margin-top:10px;height:auto;}
#home-adv .big #button-service {color:#444!important;background-color:#cfcdca;padding:6px;}
/* med */
#home-adv .adv-row.med { background:#e5e5e5; }
#home-adv .med td { padding:5px;background-color:#fff; }
#home-adv .med img { width:<?php echo WTop::WIDTH2; ?>px;height:<?php echo WTop::HEIGHT2; ?>px; }
#home-adv .med .img-title1 { margin:5px 0;font-size:18px; }
#home-adv .med .img-title2 { margin:5px 0;font-size:16px; }
#home-adv .book-link {/*text-decoration:underline;*/margin-bottom:5px;}
#home-adv .book-link .max-discount {color:red;}

#art-list { overflow:hidden; }
#art-list .handle { position:relative;display:inline-table;margin:5px 5px 0;float:left;border-radius:5px;height:30px;width:30px;border:none;padding:0;z-index:2;background:#e5e5e5;cursor:pointer; }
/*#art-list .handle { height:expression(''+$('#art-list').height()+'px'); }*/
#art-list .topbtn { display:block;margin-top:4px;padding:2px;border:inherit;background:none; }
/*#art-list .topbtn.ui-state-hover { padding:1px; }*/
#list-filter .topbtn { float:right;cursor:pointer; }

#list-filter { position:relative;float:left;width:94%;max-width:300px;padding:10px;background:#fff; }
#list-filter .prompt { padding:10px 0 0 15px;font-size:16px;font-weight:bold;text-transform:uppercase; }
#list-filter .search-frame { display:block;margin:10px; }
#list-filter .brand .search-frame { margin-bottom:0; }
#list-filter .search-edit { width:227px; }
#list-filter .options { padding-bottom:4px; }
#list-filter .options * { font-size:12px; }
#list-filter .options .buttons { clear:both;text-align:right; }
#list-filter .options .buttons .show { display:inline-block; }
#list-filter .options .buttons .hide { display:none; }
#list-filter .options .buttons div { position:relative;padding-left:20px;font-size:0.8em;text-decoration:underline;cursor:pointer; }
#list-filter .options .buttons .ui-icon { position:absolute;left:0;top:-2px; }
#list-filter .options .more { clear:both;display:none; }
#list-filter .option { display:block;overflow:hidden;border-bottom:1px dotted #ccc; }
#list-filter .option label { display:block; }
#list-filter .option .value { float:right;clear:both;width:4em;margin-top:4px;vertical-align:bottom;text-align:right;font-size:0.8em; }
#list-filter .block.date { margin-top:10px;padding-bottom:10px; }
#list-filter .block.date .options { padding:0 10px; }
#list-filter .block.date .options .button { height:10px;margin:5px 10px;padding-top:5px; }
#list-filter .block.date .option .value { clear:both;width:10em; }
#list-filter .block.soc { margin-top:10px;padding-bottom:10px; }
#list-filter .block.soc .options { padding:0 10px; }
#list-filter .block.proc { margin-top:10px; }
#list-filter .block.proc .prompt { padding-bottom:6px; }
#list-filter .block.proc .item { position:relative;height:34px;padding:0 15px 0 10px;cursor:pointer; }
#list-filter .block.proc .item .title { border-top:1px solid #eee;padding:10px 0 0;font-weight:bold; }
#list-filter .block.proc .item img { position:absolute;left:5px;top:5px; }
#list-filter .block.proc .options { padding:0 10px 2px; }
#list-filter .ui-datepicker-div { width:80%;max-width:270px; }

/*#list-result { margin-left:320px; }*/
#list-result { border: 10px solid white; }
#art-list .header { padding:12px 5px;display:inline-block; }
#list-result .data { overflow:hidden;padding: 10px; }
#list-result table.cols { table-layout:fixed;float:left;width:100%; }
#list-result table.cols .col { min-width:270px;overflow:hidden;width:270px; }
#list-result .data-col { width:270px; }
#list-result .data-col:first-of-type { padding-left:10px; }
#list-result .last-data-col { width:auto; }
#list-result .spacer { min-width:20px;width:20px; }
#list-result .last-spacer { display:none; }
#list-result table.cols .col a.centre { padding:5px;margin: 0 0 15px; }
#list-result .centre { display:block;margin: 0 0 20px;border:0;background-image:none;text-decoration:none; }
#list-result .image { position:relative;overflow:hidden;background-size:cover;background-repeat:no-repeat;background-position:inherit; }
#list-result .brand-container {padding-bottom:12px;border-bottom:1px solid #EEE;margin:3px;}
#list-result .brand {font-weight:bold;font-size:12px;margin-top:5px;}
#list-result .shadow {left:3px;top:12px;z-index:1;color:#fff;}
#list-result .addr {font-size:10px;color:grey;margin-top:3px;}
#list-result .srvs {overflow:hidden;margin-bottom:8px;}
#list-result .srvs * {font-size:12px;}
#list-result .srvs a {text-decoration:none;}
#list-result .srvs .more {color:grey;font-size:10px;}
#list-result a.srv {display: block;}
#list-result .srv { position:relative;height:30px;margin:10px 0;overflow:hidden;background-image:none;vertical-align:top; }
#list-result .srv.first { border:none; }
#list-result .srv .title { display:block;margin-right:8em;text-decoration:none; }
#list-result .srv .title:hover { text-decoration:underline; }
#list-result .srv .tip { position:absolute;top:0;right:0;height:100%; }
#list-result .srv .price { text-align:right; }
#list-result .srv .price:hover { text-decoration:underline; }
#list-result .srv .dura { overflow:hidden;white-space:nowrap; }
#list-result .srv .dura .ui-icon { float:right;overflow:hidden; }
#list-result .srv .dura .text { float:right;padding-top:2px;font-size:10px; }
#list-result .srv .dura .text:hover { text-decoration:underline; }
#list-result .footer { margin-top:5px;text-align:center; }
#list-result .footer .loading { display:none; }

#art-ctr .header .title {  }
#art-ctr .header .title .type { padding-right:5px; }
#art-ctr .header .title .centre {  }
#art-ctr .header .title .brand { padding-left:5px;display:none; }
#art-ctr .header .title .brand a { text-decoration:none; }

#art-ctr .header .subtitle .loc a { text-decoration:none; }

.stars,.stars * { overflow:hidden;background:url('pic/stars.png') repeat-x; }
.stars.big,.stars.big * { height:30px; }
.stars.big { width:150px; }
.stars.big .light { background-position:0 -30px; }
.stars.big .dark { background-position:0 -60px; }

.stars.small,.stars.small * { height:15px;background-size:15px,44px; }
.stars.small { width:75px; }
.stars.small .light { background-position:0 -15.5px; }
.stars.small .dark { background-position:0 -30.5px; }

article .obj-header .rating .total .value,
article .detail .reviews .stat .common .total .value { font-size:30px; }
article .total .value sub { font-size:0.7em; }

<?php /*article .brief .logo { margin-top:10px;display:none }*/ ?>

article .brief .loc { margin-bottom:10px;float:right; }

#art-ctr .brief .card { margin-bottom:10px;padding:5px; }
#art-ctr .brief .card .title { font-weight:bold; }
#art-ctr .brief .card .block { margin-top:15px; }
#art-ctr .brief .card .caption { margin-bottom:5px;font-weight:bold;text-transform:uppercase; }

#art-ctr .brief .card .sched td { padding:0 5px; }
#art-ctr .brief .card .sched th { text-align:center; }

#art-ctr .services a { display:block;text-decoration:none; }
#art-ctr .services .groups { margin:0 10px; }
#art-ctr .services .groups>table {width:100%;table-layout:fixed;}
#art-ctr .services col.name {width:80%;}
#art-ctr .services col.dura {width:20%;text-align:right;}
#art-ctr .services col.price {width:120px;}

#art-ctr .services .group { font-size:16px;font-weight:bold;cursor:pointer; }
#art-ctr .services .group * { padding:5px 10px; }
#art-ctr .services .group .title .size { margin-left:20px; }
#art-ctr .services .group .prices { text-align:right; }
#art-ctr .services .name a[href] { padding:3px 0 3px 10px; }
#art-ctr .services .tip,#art-ctr .services .tips {border-bottom:1px solid #cfcdca;border-top:1px solid #cfcdca;}
#art-ctr .services .tip a[href],#art-ctr .services .tips a[href] { margin:5px; }
#art-ctr .services .tip:hover { background:#eee; }
#art-ctr .services .tip:not(.srv) .name { padding-left:20px; }
#art-ctr .services .tip .dura a { padding:3px 0;text-align:right; }
#art-ctr .services .tip .dura a .ui-icon { float:right; }
#art-ctr .services .tip .price a { padding:3px 10px;text-align:right;background:#cfcdca;border:#cfcdca;color:#444!important; }
#art-ctr .services .tip .price s { color:#ff3333; }
#art-ctr .services .grp-splitter * { height:10px;line-height:8px; }

article .reviews {  }
article .reviews .stat { background-image:none;padding:10px;overflow:hidden; }
article .reviews .stat .stat-left { padding-right:10px; }
article .reviews .stat .stat-right { padding-left:10px;border-left:1px solid; }
article .reviews .stat .common { float:left;overflow:hidden; }
article .reviews .stat .common .total { margin-bottom:5px; }
article .reviews .stat .common .total td { padding:0 5px;text-align:center;vertical-align:bottom; }
article .reviews .stat .common .total .value { float:left; }
article .reviews .stat .common .total .title { white-space:nowrap; }
article .reviews .stat .common .total .stars { float:left; }
article .reviews .stat .common .total .count { float:left;margin-left:10px;font-size:0.8em;white-space:nowrap; }
article .reviews .stat .stat-left .distr { margin:5px 10px 0 0; }
article .reviews .stat .stat-left .distr td { padding-right:5px; }
article .reviews .stat .stat-left .distr th { text-align:right; }
article .reviews .stat .stat-left .distr .stars { margin-top:2px; }
article .reviews .stat .stat-left .distr .progress { width:100px;height:10px;margin:3px;border:1px solid;overflow:hidden; }
article .reviews .stat .stat-left .distr .progress .bar { float:left;height:100%;border:none; }
article .reviews .stat .stat-left .facil { margin:5px 0 0 10px; }
article .reviews .stat .stat-left .facil .stars { margin-top:2px;margin-left:5px; }
article .reviews .stat .stat-left .facil td { padding:0 0 6px; }
article .reviews .stat .prcs { float:left;width:100%;height:160px;overflow-y:auto; }
article .reviews .stat .prcs th { font-weight:bold; }
article .reviews .stat .prcs th,article .reviews .stat .prcs td { padding-bottom:6px; }
article .reviews .stat i { font-style:normal;color:#aaa; }

article .reviews .add { padding:10px;overflow:hidden; }
article .reviews .add .prompt { margin:0.4em 1em; }
article .reviews .add * { float:left; }
/*article .reviews .add { display:none; }*/

article .reviews .body { background-image:none; }
article .reviews .body .review { margin-bottom:5px; }
article .reviews .body .review .inner { margin:20px 10px 20px 70px; }
article .reviews .body .review .avatar { float:left;margin-left:-60px;width:50px;height:50px;background-color:#fff; }
article .reviews .body .review .avatar img { margin:1px; }
article .reviews .body .review .title { padding: 5px 10px;overflow:hidden; }
article .reviews .body .review .rates { padding:5px;cursor:pointer; }
article .reviews .body .review .rates .rate { overflow:hidden; }
article .reviews .body .review .rates .rate.first { margin-top:5px; }
article .reviews .body .review .rates .label { float:left;font-size:0.8em; }
article .reviews .body .review .name { float:left;font-weight:bold; }
article .reviews .body .review .subtitle { overflow:hidden; }
article .reviews .body .review .stars { float:left;margin-right:1em; }
article .reviews .body .review .written { float:left;margin:2px 0 0 50px;font-size:0.8em; }
article .reviews .body .review .text { padding:5px;background-color:#fff; }
article .reviews .body .review .ctrl { overflow:hidden;background-color:#fff; }
article .reviews .body .review .actions { float:right; }
article .reviews .body .review .action { display:inline-block;padding:5px 10px;font-size:0.8em;text-decoration:none; }
article .reviews .body .review .comments { margin-top:20px; }
article .reviews .body .review .comments .comments-title { padding: 5px 10px;font-size:16px; }
article .reviews .body .review .comment { margin:20px 0 20px 60px; }
article .reviews .body .review .comments .subtitle { padding: 5px 10px;font-size:16px; }

article .detail .topic.compact {overflow:hidden;}
article .detail .topic.compact.small .text {max-height:80px;}
article .detail .topic.compact .text {cursor:pointer;position:relative;}

.more-form {position:relative;bottom:0;left:0;width:100%;cursor:pointer;}
.more-form .grad {height:20px;width:80%;position:absolute;bottom:0;left:-10px;
background:rgba(255,255,255,0.6);
background:-webkit-linear-gradient(left,rgba(255,255,255,0),rgba(255,255,255,1)); /* For Safari 5.1 to 6.0 */
background:-o-linear-gradient(left,rgba(255,255,255,0),rgba(255,255,255,1)); /* For Opera 11.1 to 12.0 */
background:-moz-linear-gradient(left,rgba(255,255,255,0),rgba(255,255,255,1)); /* For Firefox 3.6 to 15 */
background:linear-gradient(left,rgba(255,255,255,0),rgba(255,255,255,1)); /* Standard syntax (must be last) */}
.more-form .more {height:20px;width:20%;position:absolute;right:0;bottom:0;padding-right:10px;color:#aaa;background-color:white;text-align:right;}
article .detail .topic.compact.large .more-form {display:none;}


article .topics { position:relative; }

article .topics .menu { position:fixed;float:left;width:250px;padding:40px 10px; }
article .topics .menu li { margin:10px 0; }
article .topics .menu a { font-size:16px;text-decoration:none; }

article .topics .content { margin-left:260px;padding:20px; }
article .topics .content .caption { font-size:24px; }
article .topics .content .topic .title { margin:30px 0 10px;font-size:24px; }
article .topics .content .topic a[name] { display:block; }
article .topics .content .topic .text { font-size:15px;line-height:20px; }
article .topics .content .topic .text ol { margin-left:20px;padding-left:10px; }
article .topics .content .topic .text ol:not([type]) li { list-style-type:decimal; }
article .topics .content .topic .text ul { margin-left:20px;padding-left:10px; }
article .topics .content .topic .text ul:not([type]) li { list-style-type: disc; }

article .topics .content .stat .frame { height:52px;margin:30px 20px 0 0;border:1px solid #bbb;padding:10px; }
article .topics .content .stat .icon { float:left;width:50px;height:50px;margin-right:10px; }
article .topics .content .stat .title { margin-bottom:22px;font-size:12px;font-weight:bold;text-transform:uppercase;white-space:nowrap;overflow:hidden; }
article .topics .content .stat .value { font-size:16px;font-weight:bold; }

#art-bnd .brief { float:right;width:300px;margin:10px; }
#art-bnd .detail { margin:10px;margin-right:310px; }

#art-srv .brief .main-caption {padding:10px;background-color:#fff;cursor:pointer;}
#art-srv .brief .main-caption .text { border:0;padding:10px 10px 5px;font-size:1.2em;text-transform:uppercase;text-align:center; }
#art-srv .brief .caption { padding:5px 5px 0;font-size:16px;text-transform:uppercase; }
#book-form .book-hint {display:none;text-transform:none !important;}

#srv-calendar .ui-datepicker { width:100%;margin-bottom:10px;border:0;padding:0; }
#srv-calendar .ui-datepicker .ui-datepicker-calendar td * { font-size:14px; }
#srv-calendar .ui-datepicker .ui-datepicker-calendar td:not(.ui-state-disabled) .ui-state-default:not(.ui-state-active)
{ color:#444;border-color:#fff;background:#fff; }
#srv-calendar .ui-datepicker .ui-datepicker-calendar td .ui-state-highlight { font-weight:bold; }

#srv-tips { margin-bottom:10px; }
#srv-tips ul { border:0; }
#srv-tips li { padding:5px;overflow:hidden; }
#srv-tips li:not(.selected) { cursor:pointer; }
/*#srv-tips li:not(.selected):hover { background-color:#eee; }*/
#srv-tips li .radio { float:left;width:16px;height:16px;background:url(pic/radiobox.png) no-repeat; }
#srv-tips li.selected .radio { background-position-y:-16px; }
#srv-tips li .title { margin-left:20px; }
#srv-tips li .dura { float:left;width:80px;margin-left:16px;text-align:right; }
#srv-tips li .dura .ui-icon { display:block;float:left;margin:0 2px 0 0; }
#srv-tips li .price { float:right;text-align:right; }

#srv-slots li { border:0;padding:5px;overflow:hidden; }
#srv-slots ul li.busy { background:#ccc !important; }
#srv-slots ul:not(.invalid) li:not(.busy):not(.disabled) { cursor:pointer; }
#srv-slots li.disabled { color:#ccc; }
#srv-slots li .icon { position:relative;float:left;width:20px;height:20px;margin-left:-5px;visibility:hidden; }
#srv-slots li.booked .icon { visibility:visible; }
#srv-slots li .icon .ui-icon { position: absolute;left:50%;top:50%;margin:-8px 0 0 -8px; }
#srv-slots li .time { float:left; }
#srv-slots li .oldprice { float:right;padding:0 5px;text-align:right;text-decoration:line-through; }
#srv-slots li .price { float:right;text-align:right; }

#art-pay center { margin:25px 0 50px; }
#art-pay center>* { max-width:600px;overflow:hidden; }
#art-pay .message { border:0;font-size:20px;text-align:center;color:#f00;margin:10px;display:none; }
#art-pay .type-thanks { color:inherit; }
#art-pay .caption { border:0;padding:5px 0 5px;font-size:20px;text-transform:uppercase;text-align:center;display:none; }
#art-pay .ref-data { margin-top:10px; }
#art-pay .ref-data .book-id { float:left; }
#art-pay .ref-data .user-id { float:right;width:200px; }
#art-pay .data { background:#fff;padding:10px;margin:10px;text-align:left; }
#art-pay .data .pay-top { display:inline-block;position:relative;width:100%;background:#f4f4f4; }
#art-pay .data .ctr { height:100%;padding:0 5px 10px;font-size:16px;font-weight:bold;line-height:30px; }
#art-pay .data .pay-top-left { float:left;clear:left;width:100px;margin:10px;border:2px solid #000;padding:10px;
 font-size:18px;text-align:center;line-height:26px;display:table; }
#art-pay .data .pay-top-right { position:absolute;right:0;bottom:0;width:100px;padding:5px 10px;text-align:right;font-size:18px; }
#art-pay .data .pay-top-main { height:100%;font-size:16px;line-height:30px;padding:5px; }
#art-pay .data .pay-top-main .dura { float:left; }
#art-pay .data .pay-top-main .ui-icon-clock { float:left;margin-top:8px; }
#art-pay .data .pay-bottom { font-size:18px; }
#art-pay .data .pay-bottom .disc {padding-top:7px;overflow:hidden;clear:both;}
#art-pay .data .pay-bottom .disc .disc-left { float:left;padding-left:150px;text-transform:uppercase; }
#art-pay .data .pay-bottom .disc .disc-right { float:right;padding:0 10px;color:#f33; }

#art-pay .data .pay-bottom .pro-code .prompt {float:left;margin-right:10px;}
#art-pay .data .pay-bottom .pro-code .input-block {float:left;}
#art-pay .data .pay-bottom .pro-code input {width:100px;font-size:17px;text-transform:uppercase;}
#art-pay .data .pay-bottom .pro-code-show {display:none;}
#art-pay.has-pro-code .data .pay-bottom .pro-code-show {display:block;}
#art-pay .data .pay-bottom .pro-code .pro-code-vld-show {display:none;}
#art-pay.has-pro-code-vld .data .pay-bottom .pro-code .pro-code-vld-show {display:block;}
#art-pay .data .pay-bottom .pro-code .error {display:none;text-transform:none;font-size:small;}
#art-pay .data .pay-bottom .pro-code.error .error {display:block;}

#art-pay .data .pay-bottom .total { margin-bottom:20px;overflow:hidden;float:right;clear:right; }
#art-pay .data .pay-bottom .total>* { float:right;padding:0 10px; }
#art-pay .data .pay-bottom .total .prompt { float:left; }
#art-pay .data .pay-bottom .opts { overflow:hidden;clear:right;margin-bottom: 15px; }
#art-pay .data .pay-bottom .opts>* { margin-bottom:15px; }
#art-pay .data .pay-bottom .opts .prompt { float:left; }
#art-pay .data .pay-bottom .opts .checkbox { float:right;width:200px; }
#art-pay .data .pay-bottom .opts .checkbox input { width:20px;height:20px; }
#art-pay .data .pay-bottom .opts.online .checkbox.pay-now label::before { background-position: 0 -16px; }
#art-pay .data .pay-bottom .opts.offline .checkbox.pay-later label::before { background-position: 0 -16px; }

#art-pay .data .pay-bottom .opts .phone {clear:both;}
#art-pay .data .pay-bottom .opts .phone>* {margin:0 25px 10px 0;}
#art-pay .data .pay-bottom .opts.online .phone {display:none;}
#art-pay .data .pay-bottom .opts.offline .phone {display:block;}
#art-pay .data .pay-bottom .input-row {margin-top:5px!important;}
#art-pay .data .pay-bottom .input-block .input {display:block;border:1px solid #aaa;float:left;}
#art-pay.has-ref .data .pay-bottom .input-block .input {border:none;}
#art-pay .data .pay-bottom .opts .phone input {width:165px;}
#art-pay .data .pay-bottom .input-block input {margin-left:5px;display:block;height:30px;border:none;}

#art-pay .data .pay-bottom .opts .phone .error {display:none;}
#art-pay .data .pay-bottom .opts .phone.error .error {display:table-cell;}

#art-pay .data .pay-bottom .btns { margin-top:20px;overflow:hidden;clear:right; }
#art-pay .data .pay-bottom .btns a.change { display:block;float:left;margin-top:20px;font-size:16px; }
#art-pay .data .pay-bottom .btns a.button { margin:auto;max-width:250px;border:0;text-transform:uppercase; }
#art-pay .data .pay-bottom .btns a.button.ok { display:none; }
#art-pay .data .pay-bottom .opts.online+.btns a.button.book { display:block; }
#art-pay .data .pay-bottom .opts.offline+.btns a.button.order { display:block; }
#art-pay .data .pay-bottom .sent { clear:both;padding-top:20px;font-size:12px; }
#art-pay .data .pay-bottom .policy { margin-top:20px;font-size:12px; }
#art-pay .data .pay-bottom .policy h6 { font-size:14px; }
#art-pay .data .pay-bottom .policy ul { margin-top:10px;list-style:disc outside; }
#art-pay .data .pay-bottom .policy ul li { display:list-item;margin:10px;list-style: '- ' outside; }
#art-pay .data .pay-bottom .policy .ctr { font-size:inherit;line-height:inherit;padding:inherit;font-weight: inherit; }
#art-pay .data .srv { font-weight:bold; }
#art-pay .data hr {width:100px;float:right;clear:both;}
#art-pay .ref-hide { display:block; }
#art-pay.has-ref .ref-hide { display:none; }
#art-pay .ref-show { display:none; }
#art-pay.has-ref .ref-show { display:block; }
#art-pay.has-ref.is-type-pay .type-pay { display:block; }
#art-pay.has-ref.is-type-book .type-book { display:block; }

#art-pay .disc-hide { display:block; }
#art-pay.has-disc .disc-hide { display:none; }
#art-pay .disc-show { display:none; }
#art-pay.has-disc .disc-show { display:block; }

#art-pay .signin-disc-hide {display:block;}
#art-pay.signin-disc .signin-disc-hide {display:none;}
#art-pay .signin-disc-show {display:none;}
#art-pay.signin-disc .signin-disc-show {display:block;float:left;width:100%;}

#art-pay .pro-code-vld-hide {display:block;}
#art-pay.has-pro-code-vld .pro-code-vld-hide {display:none;}

#art-pay .inactive {color:#ddd;}

#art-clt { max-width:600px;margin:0 auto; }
#art-clt .obj-header { height:128px;overflow:hidden; }

#art-clt .obj-header [field="img"] { position:relative; }
#art-clt .obj-header [field="img"] img { display:block; }
#art-clt .obj-header [field="img"] .change { visibility:hidden;float:right;padding:10px;
 position:absolute;bottom:0;right:0;text-decoration:underline;background-color:#fff;opacity:0.5; }
#art-clt.editable .obj-header [field="img"]:hover .change { visibility:visible;cursor:pointer; }

#art-clt .obj-header .public-view { display:none;margin:40px;text-align:right;font-weight:bold; }
#art-clt.editable .obj-header .public-view { display:block; }

#art-clt table.records { width:100%;border-top:1px #eee solid; }
#art-clt table.private.records { display:none; }
#art-clt.editable table.private.records { display:table; }

#art-clt tr.record { display:none;border-bottom:1px #eee solid; }
#art-clt tr.caption { text-align:center;font-size:24px; }
#art-clt tr.record>td { padding:5px 10px; }
#art-clt tr.record .prompt { font-weight:bold; }

#art-clt tr.record .input { display:none; }
#art-clt tr.record .input input { display:block;width:100%;min-width:150px;border:0; }
#art-clt.editable tr.record.editing .value { display:none; }
#art-clt.editable tr.record.editing .input { display:block; }

#art-clt.editable tr.record[type=text] .value { cursor:text; }
#art-clt.editable tr.record[type=list] .value { cursor:text; }
#art-clt.editable tr.record[type=date] .value { cursor:text; }
#art-clt.editable tr.record[type=date] span { display:block;float:left;height:1em; }
#art-clt.editable tr.record[type=date] [part] { display:block;float:left;width:25px;text-align:center; }
#art-clt.editable tr.record[type=date] [part="year"] { width:40px; }

#art-clt div.change { float:right; }
#art-clt [field="note"] .change { visibility:hidden;float:left;margin-top:18px;padding:10px;font-size:0.8em;font-weight:bold;text-decoration:underline; }
#art-clt.editable [field="note"]:hover .change { visibility:visible;cursor:pointer; }
#art-clt [field="note"] .text { clear:both; }

#art-bookings .caption { text-align:center;font-size:20px; }
#art-bookings .data th { text-align:center;white-space:nowrap;font-weight:bold; }
#art-bookings .data td { padding:2px 5px;white-space:nowrap; }
#art-bookings .data td.right { text-align:right; }
#art-bookings .data td.center { text-align:center; }

#art-biz * { color:#fff; }
#art-biz .shadow { text-shadow:0 1px 1px rgba(113,113,113,0.7); }

#art-biz .slide { position:relative;width:100%;overflow:hidden; }
#art-biz .slide * { }
#art-biz .slide>img { width:100%; }
#art-biz .slide .content { position:absolute;top:0;width:100%;z-index:2; }

#art-biz h1,#art-biz h2,#art-biz h3,#art-biz p { text-align:center; }
#art-biz h1 { padding:10% 0 2%;font-size:40px;text-transform:uppercase; }
#art-biz h2 { padding:10% 0 3%;font-size:30px;text-transform:uppercase; }
#art-biz h3 { font-size:22px;line-height:40px; }
#art-biz p { padding-top:2%;font-size:18px; }

#art-biz .signup { padding-top:100px; }
#art-biz .signup .button { position:relative;display:block;height:40px;width:250px;margin:0 auto;padding:0 20px;
font-size:20px;line-height:40px;text-transform:none !important;border-radius:3px; }

#art-biz .content table.main { padding-bottom:50px;text-align:center;border-collapse:separate;border-spacing:40px 0; }
#art-biz .content table.main td { background-color:rgba(0,0,0,0.2);padding:10px;font-size:15px; }
#art-biz .content table.main a { text-decoration:none;font-size:12px; }

#home-main-pop {display:none;position:relative;z-index:10;margin:4% auto 0;background-color:#00AE9C;width:80%;max-width:300px;padding:10px;text-align:ceter;color:white;}
#home-main-pop a {color:white}
#home-main-pop .pop-subject{font-size:18px;width:96%;text-align:center;}
#home-main-pop .pop-message{display:block;text-align:center;font:normal 14px helvetica;margin:10px;}

#nav-pop-mod {display:block;position:fixed;left:0;top:0;width:100%;height:100%;z-index:98;padding-top:150px;background:rgba(255,255,255,0.6);}
<?php
// $dlyPop=7000;
 $dlyPop=0;
 if($dlyPop>0)
  echo '#nav-pop-mod {visibility:hidden;}\n';
?>
#nav-pop-mod .nav-pop-mod-bkg {background:white;position:absolute;left:0;top:0;height:100%;width:100%;}
/*#nav-pop-mod-wnd {background-size:cover;background-image:url("pic/pop-bg-27-s.jpg")!important;max-width:650px;padding:30px 10px;background-color:#00AE9C;visibility:hidden;display:block;border:solid 2px gainsboro;position:relative;z-index:99;margin:auto;width:80%;text-align:ceter;color:white;}*/
#nav-pop-mod-wnd {background-size:cover;min-width:200px;text-shadow: 0 2px 2px rgba(102,102,153,0.5);max-width:530px;padding:30px 10px;background-color:#00AE9C;visibility:hidden;display:block;position:relative;z-index:99;margin:auto;width:80%;text-align:ceter;color:white;}
#nav-pop-mod-wnd a {color:white;}
#nav-pop-mod-wnd .ui-icon-circle-close-bck {margin:-38px -18px;float:right;background-color:gainsboro;border-radius:10px;}
#nav-pop-mod-wnd .ui-icon-circle-close {cursor:pointer;}
#nav-pop-mod-wnd .pop-subject {font-size:20px;text-align:center;margin:0 20px;text-transform:uppercase;}
/*#nav-pop-mod-wnd .pop-message {display:block;text-align:center;margin:25px 25px 20px;text-transform:uppercase;font-size:24px;}*/
#nav-pop-mod-wnd .pop-message {display:block;text-align:center;margin:30px 25px;font-size:30px;line-height:45px;}
#nav-pop-mod-wnd .topw {margin-bottom:15px!important;}
#nav-pop-mod-wnd .email-signup {background-color:#466;cursor:pointer;padding:0;}
#nav-pop-mod-wnd .continue {display:block;text-transform:uppercase;width:100%;text-align:right;}
#nav-pop-mod-wnd .continue a {margin:10px;text-decoration:underline;cursor:pointer;}
#nav-pop-mod-wnd .gp {display:none;}

<?php
//Responsive design rules:
//Styles are called rd-...
//Single-colunm tables are used for block elemants htat need to have fixed rows
//?Tables do not carry any formatting
//Div-s are block elements
//?Span-s are inline elements
//Maximum fixed width of any element cannot be more than 300px
?>

body{width: 100%}

#home-filter { position:relative;z-index:10;overflow:hidden; }

#home-filter .form { margin:0 auto;background:none;border:none;padding:5px; }
#home-filter .form td { padding:5px 5px 5px 0;background-color:rgba(255,255,255,0.7); }
#home-filter .form td:first-child { padding:5px !important; }

#home-filter .proc .search-edit { width:348px; }
#home-filter .terr .search-edit { width:348px; }
#home-filter .date .search-edit { width:150px; }

#home-filter .search-frame { display:block;float:left; }
#home-filter .search-edit {  }
/*#home-filter .button { height:19px; }*/


#home-filter h1 {margin:0 5px;color:#fff;text-transform:uppercase;text-shadow:0 2px 2px rgba(102,102,153,0.5);}
#home-filter h2 {text-shadow:0 2px 2px rgba(102,102,153,0.5);text-align:center;color:#fff;}

article .obj-header .rating {  }
article .obj-header .rating { height:80px;padding:0 10px;border:none;overflow:hidden;white-space:nowrap; }
article .obj-header .rating .total { text-align:center; }
article .obj-header .rating .total .title { font-size:1.2em;font-weight:bold; }
article .obj-header .rating .total .stars { margin:0 auto; }
article .obj-header .rating .total .count { font-size:0.8em;margin-top:-5px; }
article .obj-header .rating .facil { width:80px; }
article .obj-header .rating .facil .title { font-size:0.8em; }
article .obj-header .rating .facil .stars { margin-bottom:0px; }

.rd-padding{padding:5px 5px 5px 5px;}
.rd-fxd-blck{text-align:center;padding:5px 5px 5px 5px;display:inline-block;float:left;}
.rd-fxd-wdth-200px{width:200px}
.rd-center-at-500px{float:left;display:inline-block;}
.rd-center-at-670px{float:left;display:inline-block;}
.rd-center-at-999px{float:left;display:inline-block;}

.rd-width-400-250px-at-670px{width:400px;text-align:center;}
.rd-width-460-210px-at-999px{width:460px;text-align:center;}
.rd-max-width-1000-500-200px{max-width:1000px;text-align:center;}
.rd-width-1000-200px-at-999px{width:1000px;text-align:center;}

.rd-adv-tbl{display:none;}
.rd-footer-block1{float:left;margin-right:10%;}

 #art-ctr .brief { float:right;width:300px;margin:10px; }
 #art-ctr .detail { margin:10px;margin-right:320px; }
 #art-srv .brief { float:right;width:300px;margin:10px;max-width:400px; }
 #art-srv .detail { margin:10px;margin-right:310px; }

@media screen and (min-width: 1000px) {
 header .hmenu {display:none;min-width:1000px;margin:8px 0;}
 header .hmenu-mobile {display:none;}
 header .hmenu-on {display:flex;}
 header .hmenu .hitem-divider {margin:5px 1px;}

 article .detail .gallery { width:100%;height:400px;overflow:hidden; }
 article .detail .gallery .images { height:400px;overflow:hidden;white-space:nowrap; }
 article .detail .gallery .images .image { display:none;float:left;height:400px;cursor:move; }

 #home-filter h1 {font-size:40px;}
 #home-filter h2 {margin:39px 0 43px;font-size:22px; }

 div .proc.search-frame {width:44%;margin-right:.7%;float:left;}
 div .terr.search-frame {width:44%;margin-right:0;float:left;}
 div .search-button {width:10.1%;margin-right:0;float:right;min-width:75px;}

 article .obj-header { height:80px;margin:10px 10px 0;background:#fff; }
 article .brief img.map { width:300px;height:300px; }
 article .topic-title { border:solid #fff 10px;padding:10px 10px 5px;font-size:1.2em;font-weight:bold;text-transform:uppercase; }

 header .logo { height:53px; }

 .rd-hide-on-full-screen {display: none;}
}

@media screen and (max-width: 999px) {
 .ui-menu .ui-menu-item a { line-height:2;font-size:1em; }
 header {margin:5px 0;}
 header .hmenu {display:none;min-width:0px;position:absolute;width:250px;border:1px solid lihjtgrey;z-index:95!important;}
 header .hmenu .hitem {background-color:lightgrey;text-align:left;}
 header .hmenu-button {display:block;}
 header .hmenu .hitem-divider {margin:0 1px;border-top:none;}
 header .hmenu-mobile {display:block;}

 #list-filter { max-width:270px; }
 #list-filter .option { padding:5px 0; }
 #list-filter .options .buttons { padding:10px 0; }

 #list-result table.cols { width:100%; }
 #list-result table.cols .col { min-width:200px;width:100%; }
 #list-result .data-col { width:auto; }
 #list-result .data-col:first-of-type { padding-left:0; }

 #list-result .spacer { min-width:10px;width:10px; }
 #list-result .last-spacer { display:none; }

 #list-result table.cols .col a.centre { border: 1px solid #eeeeee;border-radius: 7px;margin: 0 0 10px; }
 #list-result .image { margin: 4px; }
 #list-result .srvs { margin: 0 4px 8px; }

 #button-your-business { display:none; }

 #home-filter h1 {font-size:2.5em;}
 #home-main-pop {margin:5% auto 0;}
 #home-filter h2 {margin:0 0 2%;font-size:1.5em;}

 #home-filter .form { margin:0 auto;background:none;border:none;padding:5px;max-width:500px; }
 #home-filter .form td { background-color:rgba(255,255,255,0.4); }
 #home-filter .form td:first-child { padding:2% !important; }
 #home-filter .proc .search-edit { width:80%; }
 #home-filter .terr .search-edit { width:80%; }
 #home-filter .date .search-edit { width:50%; }

 #home-main-pop {border-radius:5px;}
 /*
 div .proc.search-frame {width:49%;margin-right:1%;float:left;}
 div .terr.search-frame {width:49%;margin-right:0;float:right;}
 div .search-button {width:10%;margin-right:0;margin-left:45%;margin-top:1%;float:left;min-width:75px;}
 */

 div .proc.search-frame {width:99.5%;margin:0 0 1%;padding:0;float:left;}
 div .terr.search-frame {width:99.5%;margin:1% 0;padding:0;float:left;}
 div .search-button {width:30%;margin:0;padding:1% 0 0 35%;float:left;min-width:75px;}
 a.button {border-radius:5px;}

 article .obj-header { height:80px;margin: 10px 1% 0;background:#fff; }
 article .brief img.map {float:right;width:75px;margin:5px;}

 article .obj-header { height:auto;background:#fff; }
 article .topic-title { border:solid #fff 2px;padding:10px 10px 5px;font-size:1.2em;font-weight:bold;text-transform:uppercase; }

 #art-ctr .brief {float:none;width:98%;margin:1%;margin-right:1%;}
 #art-ctr .detail { width:98%;margin:1%;margin-right:1%; }

 header .logo {margin-left:10px;width:64px;}
 header .header-right { padding-right:5px; }
 header .header-right .clickable { margin:5px 2px 0;padding:0 10px; }
 #button-client { margin-top:0;padding:5px 2px 0 25px;background:url("<?php echo WClient::imageURI(); ?>") no-repeat 0 / 18px; }

 img {max-width: 100%;height: auto;}

 article .obj-header .rating { height:auto;padding:0 0;float:right; }
 article .obj-header .rating .total .count { margin-top:-2px; }

 #art-pay .caption { padding: 10px 0px; }

 #art-clt [field="note"] .change {visibility:visible;font-size:14px;}
 #art-clt tr.record > td {line-height: 25px;}
 #art-clt.editable tr.record[type="text"].value{line-height:25px;}

 .rd-obj-header-left {width:70%;}
 .rd-hide-on-full-screen {display:block;}
 .rd-hide-at-999px {display:none;}
 .rd-max-width-1000-500-200px{max-width:500px;}
 .rd-center-at-999px{float:none;margin:auto;width:auto;}

 .rd-width-100prc-at-999px{width:100%;padding-left:0;padding-right:0;}
 .rd-width-460-210px-at-999px{width:210px;text-align:center;}
 .rd-width-1000-200px-at-999px{width:200px;}
}

@media screen and (max-width: 750px)
{
 .rd-hide-at-750px {display:none;}
}

@media screen and (max-width:670px)
{
 header-col-logo {width:40%;}
 header col.header-col-right {width:30%;}

 #home-filter h1 { font-size:2em; }

 #list-result table.cols .col { min-width:180px;width:100%; }

 #art-ctr .services .group .title { display:inline-block;width:95%; }

 #art-pay center { margin: 0; }
 #art-pay .data { margin: 0 0 10px; }
 #art-pay .data .pay-bottom .opts .phone .input-block {float:right;}

 .rd-hide-at-670px{display:none;}
 .rd-center-at-670px{float:none;margin:auto;}
 .rd-width-400-250px-at-670px{width:250px;text-align:center;}
 .rd-padding-0-at-670px{padding:0;}
}

@media screen and (max-width: 550px)
{
 #art-srv .brief {float:none;width:95%;margin:15px auto;}
 #art-srv .detail {float:right;width:98%;margin:1%;margin-right:1%;}
 .rd-unhide-at-550px {display:block;}
 #home-adv .adv-title {padding:25px 14px 10px;font-size:18px;}
 #home-adv .big td {padding-bottom:0px}

 #nav-pop-mod-wnd .pop-message {font-size:26px;}

 #list-result {display:inline-block;border:none;}
 #list-filter {max-width:none;width:auto;}
 div .proc.search-frame {width:98%;margin:1% .5% 2%;padding:0;float:left;}
 div .terr.search-frame {width:98%;margin:1% .5% 2%;padding:0;float:left;margin-top:1%;}
 div .search-button {width:30%;margin:1% 0 2%;padding:1% 0 0 35%;float:left;min-width:75px;}
 a.button {border-radius:5px;}

 #art-pay .data .pay-top-main {padding-bottom:20px;}
 #art-pay .data .pay-bottom .btns a.button {float:none;margin:auto;max-width:250px;}
 #art-pay .data .pay-bottom .btns a.button.ok {float:none;margin:auto;max-width:250px;}
 #art-pay .data .pay-bottom .disc .disc-left {padding-left:0;}

 .rd-hide-at-500px{display:none;}
 .rd-center-at-500px{float:none;margin:auto;}
 .rd-max-width-1000-500-200px{max-width:200px;margin:auto;text-align:center;display:block;}
}

@media screen and (max-width: 450px)
{
 article .obj-header .title { padding:2px 5px; }
 #home-filter .form { margin:8% auto;background:none;border:none;padding:5px;max-width:400px; }
 #home-main-pop {margin:0 auto;}

 #art-ctr .services .group .service td:nth-child(2) {display:none;}
 nav .nav-pop{width:65%;right:12%;}

 .rd-footer-block1{float:left;margin-right:0;}
 .rd-hide-at-450px{display:none;}
}

@media screen and (max-width: 300px)
{
 #art-list .header { padding:5px 5px; }
 #art-ctr .services .group .title { display:inline-block;width:90%; }

 #art-pay .data .pay-top { display:table; }
 #art-pay .data .pay-top-right { float:left }

 .rd-hide-at-300px{display:none;}
}

@media screen and (max-height: 570px)
{
 #nav-pop-mod-wnd {padding:20px 10px;}
 #nav-pop-mod-wnd .ui-icon-circle-close-bck {margin:-28px -18px;}
 /*#nav-pop-mod-wnd .pop-message {margin:20px;text-transform:uppercase;font-size:35px;line-height:55px;}*/
 #nav-pop-mod-wnd .pop-message {margin:20px;}
}

@media screen and (max-height: 500px)
{
 #home-filter h1 {display:none;}
 #home-main.has-pop h2 {display:none;}
 #home-filter .form {margin:2% auto;}
 #home-main-pop {margin:0 auto;}
}

#art-home .banner-link {text-decoration:none;}
#art-home .banner {width:100%;background-color:#787878;border-top:solid #E5E5E5 60px;position:relative;overflow:hidden;background-size:cover;background-position:50%;background-repeat:no-repeat;-webkit-transform-style:preserve-3d;-moz-transform-style:preserve-3d;transform-style:preserve-3d;}
@media screen and (max-width:700px){#art-home .banner {background-image:url('pic/bnr600.jpg');height:100px;}}
@media screen and (min-width:700px){#art-home .banner {background-image:url('pic/bnr1000.jpg');height:180px;}}
@media screen and (min-width:1200px){#art-home .banner {background-image:url('pic/bnr1500.jpg');height:250px;}}
#art-home .banner .banner-text {width:70%;margin-left:6%;font-size:36px;line-height:55px;/*text-transform:uppercase;*/color:white;text-shadow:0 1px 1px #787878;top:50%;-webkit-transform:translateY(-50%);-ms-transform:translateY(-50%);transform:translateY(-50%);position:relative;}
@media screen and (max-width:900px){#art-home .banner .banner-text {margin-left:5%;font-size:32px;line-height:44px;}}
@media screen and (max-width:700px){#art-home .banner .banner-text {margin-left:4%;font-size:24px;line-height:30px;}}
@media screen and (max-width:550px){
 #art-home .banner .banner-text {margin-left:3%;font-size:16px;line-height:24px;}
 #art-home .banner {width:100%;background-color:#787878;border-top:solid #E5E5E5 30px;}}
#art-home .banner .list-email:hover {text-decoration:underline;}

.rd-small-total-rating { float:right;padding:1px 2px;margin-right:2%;width:75px;}
.rd-center {float:none;margin:auto;display:block;}
.rd-float-none {float:none;}
.rd-hide {display:none;}
//:AT

</style>
<!--[if lt IE 8]><style type="text/css">
#search .form { position:absolute;left:50%;margin-top:50px;width:1000px;margin-left:-500px;padding:5px;white-space:nowrap;background:#e6e4e3; }
</style><![endif]-->

</head>

<body>

<header class="noselect">

<table class="layout">
<colgroup><col class="header-col-left rd-hide-at-999px"/><col class="header-col-logo"/><col class="header-col-right"/></colgroup>
<tr>

<td class="rd-hide-at-999px header-left">
<div class="search-frame ui-state-highlight brand"<?php if (Base::mode() == 'list') echo ' style="display:none;"';?>>
<span class="search-icon-left search"></span>
<input id="header-input-brand" class="search-edit" placeholder="<?php echo addslashes(Lang::getPageWord('hint', 'Search by venue')); ?>" />
<span class="search-icon-right ui-icon ui-icon-close"></span>
</div>
</td>

<td class="header-center" align="center">
<a class="ax hmenu-button js" href="#"><img src="pic/menu-icon-24.png" alt="Menu"/></a>
<a class="ax logo" href="<?php echo Base::home() . Base::langPath(); ?>"><img src="pic/wc-logo-53.png" alt="Wellclubs" /></a>
</td>

<td class="header-right">
<div><table><tr>
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
  echo '<td><a class="clickable lang' . $class . '" href="' . $uri . '" path="' . $path . '" title="' . $title . '">' .
    '<span class="icon-lang icon-lang-' . $id . '"></span></a></td>';
 }
}
if (!WClient::id())
 echo '<td><a id="button-login" class="js clickable text ui-widget-content" href="#">' . Lang::getPageWord('button', 'Log in') . '</a></td>';
//if (!WClient::id() || !WClient::me()->isMember())
// echo "<td class='rd-hide rd-hide-at-999px'><a id='button-join-business' class='ax button text' href='biz/'>" . htmlspecialchars(Lang::getPageWord('button', 'Join as a business')) . "</a></td>";
//else if (!WClient::me()->isMember() && !WClient::me()->isMaster())
// echo "<td><a id='button-list-business' class='ax button text' href='biz/'>" . htmlspecialchars(Lang::getPageWord('button', 'List your business')) . "</a></td>";
else
 echo "<td class='rd-hide'><a id='button-your-business' class='button text' href='com/'>" . htmlspecialchars(Lang::getPageWord('button', 'Your business')) . "</a></td>";
if (WClient::id())
 echo '<td><a id="button-client" class="js clickable text ui-widget-content big-title" href="#">' . htmlspecialchars(WClient::name()) . '</a></td>';
?>
</tr></table></div>
</td>

</tr>
</table>

<?php
$dwhere = (WDomain::ok() ? (' and (domain_id is null or domain_id=' . WDomain::id() . ')') : ' and domain_id is null') . ' and hidden is null';
$topItems = DB::getDB()->queryRecords('biz_hmenu', 'id,name,addr', 'parent_id is null' . $dwhere, 'serial,id');
if ($topItems)
{
 echo "<div class='hmenu-background'></div>\n";
 echo "<ul class='hmenu'>\n";
 foreach ($topItems as $topItem)
 {
  $id = $topItem[0];
  $title = htmlspecialchars(Lang::getDBTitle('biz_hmenu_abc', 'hmenu_id', $id, $topItem[1]));
  $addr = $topItem[2];
  if ($topItem[0]!==$topItems[0][0])
   echo "<li class='hitem-divider'></li>\n";
  echo "<li class='hitem'>\n";
  if ($addr)
  {
   echo "<a class='ax top text' href='$addr'>$title</a>\n";
  }
  else
  {
   echo "<a class='top text'>$title</a>\n";
   $items = DB::getDB()->queryRecords('biz_hmenu', 'id,name,addr', 'parent_id=' . $id . $dwhere, 'serial,id');
   if ($items)
   {
    echo "<ul class='hsubmenu'>\n";
    foreach ($items as $item)
    {
     $id = $item[0];
     $title = htmlspecialchars(Lang::getDBTitle('biz_hmenu_abc', 'hmenu_id', $id, $item[1], 'serial,id'));
     $addr = $item[2];
     echo "<li class='hsubitem'>";
     if ($addr)
      echo "<a class='ax sub text' href='$addr'>$title</a>";
     else
      echo "<a class='sub text'>$title</a>";
     echo "</li>\n";
    }
    echo "</ul>\n";
   }
  }
  echo "</li>\n";
 }
 echo "</ul>\n";
}
?>

</header>

<nav>

<?php
if (WClient::id())
{
 echo '<ul id="client-menu">' . "\n";
 echo '<li><a class="ax" href="clt-' . WClient::id() . '/">' . htmlspecialchars(Lang::getPageWord('menu', 'Edit profile')) . '</a></li>' . "\n";
 echo '<li><a class="ax" href="bookings/">' . htmlspecialchars(Lang::getPageWord('menu', 'Booking history')) . '</a></li>' . "\n";
 echo '<li><a id="client-menu-passwd" class="js" href="#">' . htmlspecialchars(Lang::getPageWord('menu', 'Change password')) . '</a></li>' . "\n";
 echo '<li><a id="client-menu-logout" class="js" href="#">' . htmlspecialchars(Lang::getPageWord('menu', 'Logout')) . '</a></li>' . "\n";
 echo '</ul>' . "\n";
}

// Message
echo "<center class='topw message'>\n";
echo "<span class='ui-icon ui-icon-circle-close ui-state-highlight'></span>\n";
echo "<center class='title'></center>\n";
echo "<table><tr>\n";
echo "<td><a class='js button main ui-state-error' href='#'>" . Lang::getPageWord('button', 'OK') . "</a></td>\n";
echo "</tr></table>\n";
echo "</center>\n";

if (!WClient::id())
{
 // Log in
 echo "<div class='topw login'>";
  echo "<span class='ui-icon ui-icon-circle-close ui-state-highlight'></span>";
  echo "<div class='title'>" . Lang::getWord('title', 'Log in to Wellclubs', 'login') . "</div>\n";
  echo "<center class='error ui-state-highlight'></center>\n";

  echo "<div class='form rd-max-width-1000-500-200px'>";
   echo "<table><tr><td>";
    echo "<div class='rd-center-at-999px'>";
    $sn = false;
    foreach (array('gp', 'fb') as $net){
     $href = XAuth::href($net);
     if ($href)
      echo "<div style='width:190px;' class='rd-fxd-blck'><a class='button auth' net='$net' href='$href'>" .
        Lang::getPageWord('text', 'Log in with') . ' ' . XAuth::name($net) . "</a></div>\n";
     $sn = true;
    }
    if ($sn){
     echo "<div style='width:30px;' class='rd-fxd-blck rd-center-at-500px'><span class='sn-split ui-widget-content'>" . Lang::getPageWord('text', 'or') . "</div></div>\n";
    }
    echo "<div style='float:left;'>";
     echo "<div style='width:190px;' class='email rd-fxd-blck'>";
      echo "<div class='input'><input class='text' name='email' placeholder='" . Lang::getPageWord('text', 'Email address') . "' /></div>\n";
     echo "</div>";
     echo "<div style='width:190px;' class='pass rd-fxd-blck'>\n";
      echo "<div class='input'><input type='password' class='text' name='password' placeholder='" . Lang::getPageWord('text', 'Password') . "' /></div>\n";
     echo "</div>\n";
     echo "<div style='width:90px;' class='rd-fxd-blck rd-center-at-500px'><a class='js button main ui-state-error' href='#'>" . Lang::getPageWord('button', 'Log in now') . "</a></div>";
    echo "</div>\n";
   echo "</td></tr></table>";
  echo "</div>\n";

  echo "<div style='max-width:80%;float:none;margin:auto;' class='rd-fxd-blck rd-center'>";
   echo "<div class='rd-fxd-blck rd-float-none'>";
    echo Lang::getWord('hint', 'Forgot password', 'login') . "? <a class='js link-forgot-password' href='#' title='" . Lang::getWord('hint', 'Forgot password', 'login') . "?'>Reset</a>\n";
   echo "</div>";
   echo "<div class='rd-fxd-blck rd-float-none'>" . Lang::getWord('link', 'Don\'t have an account?', 'login') . " <a class='js link link-signup' href='#'>" . Lang::getWord('link', 'Sign up!', 'login') . "</a></div>\n";
  echo "</div>";
 echo "</div>";


 // Sign up
 echo "<div class='topw signup'>\n";
  echo "<span class='ui-icon ui-icon-circle-close ui-state-highlight'></span>\n";
  echo "<div class='title'>\n" . Lang::getWord('title', 'Join Wellclubs - a world of wellness and beauty!', 'signup') . "</div>\n";
  echo "<div style='margin:auto;text-align:center;' class='rd-width-1000-200px-at-999px'>";
   echo "<table><tr><td>";
    echo "<div class='rd-center rd-width-460-210px-at-999px'>";
    $sn = false;
    foreach (array('gp', 'fb') as $net)
    {
     $href = XAuth::href($net);
     if ($href)
      echo "<div class='rd-fxd-blck rd-fxd-wdth-200px'><a class='button auth' net='$net' href='$href'>" .
        Lang::getPageWord('text', 'Sign up with') . ' ' . XAuth::name($net) . "</a></div>\n";
     $sn = true;
    }
    if ($sn){
     echo "<div style='width:30px;' class='rd-fxd-blck rd-center-at-999px'><span class='sn-split ui-widget-content'>" . Lang::getPageWord('text', 'or') . "</div>\n";
     echo "</div>";
    }
   echo "</td></tr><tr><td>";
   echo "<center class='error ui-state-highlight'></center>\n";

   echo "</td></tr><tr><td>";
    echo "<div class='firstname rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
     echo "<div class='input'><input class='text' name='firstname' placeholder='" . Lang::getPageWord('text', 'First name') . "' /></div>\n";
    echo "</div>\n";

    echo "<div class='lastname rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
     echo "<div class='input'><input class='text' name='lastname' placeholder='" . Lang::getPageWord('text', 'Last name') . "' /></div>\n";
    echo "</div>\n";

    echo "<div class='email rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px rd-center-at-999px'>\n";
     echo "<div class='input'><input class='text' name='email' placeholder='" . Lang::getPageWord('text', 'Email address') . "' /></div>\n";
    echo "</div>\n";

   echo "</td></tr><tr><td>";
    echo "<div style='max-width:572px;' class='rd-center'>";
     echo "<div class='pass rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
     echo "<div class='input'><input type='password' class='text' name='password' placeholder='" . Lang::getPageWord('text', 'Password') .
      ' (' . WClient::PASS_MIN_LENGTH . '+ ' . Lang::getPageWord('text', 'characters') . ")' /></div>\n";
     echo "</div>\n";

     echo "<div class='pass rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
      echo "<div class='input'><input type='password' class='text' placeholder='" . Lang::getPageWord('text', 'Password') .
       ' (' . Lang::getPageWord('text', 'repeat') . ")' /></div>\n";
     echo "</div>\n";

     echo "<div class='rd-fxd-blck rd-center-at-999px'><a class='js button main ui-state-error' href='#'>" . Lang::getWord('button', 'Join now', 'signup') . "</a></div>\n";
    echo "</div>\n";
   echo "</td></tr></table>\n";
  echo "</div>";
  echo "<center class='note'>\n" . Lang::getWord('note', 'Joining Wellclubs means you agree with Wellclubs\'s Terms and Conditions and Privacy Policy', 'signup') . "</center>\n";
  //echo "<center class='note'>\n" . Lang::getWord('note', 'Creating an account means you agree with Wellclubs\'s Terms and Conditions and Privacy Policy', 'signup') . "</center>\n";
 echo "</div>\n";

 // Restore
 echo "<div class='topw restore rd-width-400-250px-at-670px'>\n";
  echo "<span class='ui-icon ui-icon-circle-close ui-state-highlight'></span>\n";
  echo "<center class='title'>\n" . Lang::getWord('title', 'Reset password', 'restore') . "</center>\n";
  echo "<center class='error ui-state-highlight'></center>\n";

  echo "<table><tr><td>\n";
   echo "<div class='email rd-fxd-blck rd-center-at-670px'>";
    echo "<div class='input'><input class='text' name='email' placeholder='" . Lang::getPageWord('text', 'Email address') . "' /></div>\n";
   echo "</div>";
   echo "<div class='rd-fxd-blck rd-center-at-670px'>";
    echo "<a class='js button main ui-state-error' href='#'>" . Lang::getWord('button', 'Send me instructions', 'restore') . "</a></td>\n";
   echo "</div>";
  echo "</td></tr></table>\n";
 echo "</div>\n";
}

// Passwd
echo "<div class='topw passwd rd-width-1000-200px-at-999px'>\n";
echo "<span class='ui-icon ui-icon-circle-close ui-state-highlight'></span>\n";
echo "<center class='title'>\n" . Lang::getWord('title', 'Change password', 'passwd') . "</center>\n";
echo "<center class='error ui-state-highlight'></center>\n";
echo "<table><tr><td>";
echo "<div class='oldpass rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
 echo "<div class='input'><input type='password' class='text' name='oldpass' placeholder='" . Lang::getPageWord('text', 'Current password') . "' /></div>\n";
echo "</div>\n";
echo "<div class='pass rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
 echo "<div class='input'><input type='password' class='text' name='password' placeholder='" . Lang::getPageWord('text', 'Password') .
  ' (' . WClient::PASS_MIN_LENGTH . '+ ' . Lang::getPageWord('text', 'characters') . ")' /></div>\n";
echo "</div>\n";
echo "<div class='pass rd-fxd-blck rd-fxd-wdth-200px rd-width-100prc-at-999px'>\n";
 echo "<div class='input'><input type='password' class='text' name='password' placeholder='" . Lang::getPageWord('text', 'Password') .
  ' (' . Lang::getPageWord('text', 'repeat') . ")' /></div>\n";
echo "</div>\n";
echo "<div  class='rd-fxd-blck rd-center-at-999px'><a class='js button main ui-state-error' href='#'>" . Lang::getWord('button', 'Change now', 'passwd') . "</a></div>\n";
echo "</td></tr></table>\n";
echo "</div>\n";

if (WClient::id() && !WClient::me()->isMember())
{
 // Get listed
 echo "<center class='topw listed'>\n";
 echo "<span class='ui-icon ui-icon-circle-close ui-state-highlight'></span>\n";
 echo "<center class='title'>\n" . Lang::getWord('title', 'Get listed free', 'listed') . "</center>\n";
 echo "<center class='error ui-state-highlight'></center>\n";
 echo "<div class='centre'><input class='text required' placeholder='" . Lang::getPageWord('text', 'Business name') . "' /></div>\n";
 echo "<div class='addr'><input class='text required' placeholder='" . Lang::getPageWord('text', 'Business address') . "' /></div>\n";
 echo "<div class='phone'><input class='text required' placeholder='" . Lang::getPageWord('text', 'Business phone number') . "' /></div>\n";
 echo "<a class='js button main ui-state-error' href='#'>" . Lang::getPageWord('button', 'Get listed now') . "</a>\n";
 echo "</center>\n";
}

?>

<div class="dialog" id="dlg-msgbox"></div>

<div class="dialog" id="dlg-review" title="<?php echo Lang::getWord('button', 'Write a review', 'ctr');?>">
<table>
<tr><td>
<h4 class="ui-widget-header"><?php echo Lang::getWord('rate', 'Rate the venue overall', 'ctr');?>:</h4>
<div class="ctr-rates">
<table><tbody>
<?php
$rateTotalTitle = Lang::getWord('rate','Total rating', 'ctr');
$rateAmbieTitle = Lang::getWord('rate','Ambience', 'ctr');
$rateCleanTitle = Lang::getWord('rate','Cleanliness', 'ctr');
$rateStaffTitle = Lang::getWord('rate','Staff', 'ctr');
$rateValueTitle = Lang::getWord('rate','Value', 'ctr');
?><tr><th><label for="rate-total"><?php echo Lang::getWord('rate', 'Total rating', 'ctr');?></label></th>
<td><div id="rate-total" class="edit stars small"><div class="dark"></div></div></td></tr>
<tr><th><label for="rate-ambie"><?php echo Lang::getWord('rate', 'Ambience', 'ctr');?></label></th>
<td><div id="rate-ambie" class="edit stars small"><div class="dark"></div></div></td></tr>
<tr><th><label for="rate-staff"><?php echo Lang::getWord('rate', 'Staff', 'ctr');?></label></th>
<td><div id="rate-staff" class="edit stars small"><div class="dark"></div></div></td></tr>
<tr><th><label for="rate-clean"><?php echo Lang::getWord('rate', 'Cleanliness', 'ctr');?></label></th>
<td><div id="rate-clean" class="edit stars small"><div class="dark"></div></div></td></tr>
<tr><th><label for="rate-value"><?php echo Lang::getWord('rate', 'Value', 'ctr');?></label></th>
<td><div id="rate-value" class="edit stars small"><div class="dark"></div></div></td></tr>
</tbody></table>
</div>
</td><td>
<div class="divider"></div>
</td><td>
<h4 class="ui-widget-header"><?php echo Lang::getWord('rate', 'Rate the single procedures', 'ctr');?>:</h4>
<div class="prc-rates">
<table><tbody>
</tbody></table>
</div>
</td></tr>
<tr><td colspan="3"><textarea rows="10"></textarea></td></tr>
<tr><td colspan="3"><input id="review-notifier" type="checkbox"/><label for="review-notifier"><?php
echo Lang::getPageWord('review', 'Notify me whenever someone comments on my review');?></label></td></tr>
</table>
</div>

<?php
if (WClient::id())
{
?>

<div class="dialog" id="dlg-review-comment" title="<?php echo Lang::getWord('button', 'Add a comment', 'ctr');?>">
<table><tbody>
<tr><td><textarea rows="10"></textarea></td></tr>
<tr><td><input id="review-comment-notifier" type="checkbox"/><label for="review-comment-notifier"><?php
echo Lang::getWord('review', 'Notify me whenever someone comments on this review', 'ctr');?></label></td></tr>
</tbody></table>
</div>

<div class="dialog" id="dlg-review-cavil" title="<?php echo Lang::getWord('button', 'Report as inappropriate', 'ctr');?>">
<table><tbody>
<tr><td width="50%"><input id="review-cavil-violation" type="checkbox"/><label for="review-cavil-violation"><?php
echo Lang::getWord('cavil', 'It violates Wellclubs guidelines', 'ctr');?></label>
</td><td width="50%">
<input id="review-cavil-illegal" type="checkbox"/><label for="review-cavil-illegal"><?php
echo Lang::getWord('cavil', 'It\'s illegal', 'ctr');?></label>
</td></tr>
<tr><td colspan="2"><textarea rows="10"></textarea></td></tr>
<tr><td colspan="2">
<input id="review-cavil-notifier" type="checkbox"/><label for="review-cavil-notifier"><?php
echo htmlspecialchars(Lang::getWord('review', 'Notify me whenever someone comments on my review', 'ctr'));?></label>
</td></tr>
</tbody></table>
</div>

<div class="dialog form" id="dlg-clt-firstname" title="<?php echo Lang::getWord('title', 'First name', 'clt');?>">
<label><?php echo Lang::getWord('prompt', 'Your first name', 'clt');?>:</label>
<input class="firstname"/>
</div>

<div class="dialog form" id="dlg-clt-lastname" title="<?php echo Lang::getWord('title', 'Last name', 'clt');?>">
<label><?php echo Lang::getWord('prompt', 'Your last name', 'clt');?>:</label>
<input class="lastname"/>
</div>

<div class="dialog form" id="dlg-clt-img" title="<?php echo Lang::getWord('title', 'Avatar', 'clt');?>">
<label><?php echo Lang::getWord('prompt', 'Your avatar', 'clt');?>:</label>
<input type="file" name="image" />
</div>

<div class="dialog form" id="dlg-clt-gender" title="<?php echo Lang::getWord('title', 'Gender', 'clt');?>">
<label><?php echo Lang::getWord('prompt', 'Your gender', 'clt');?>:</label>
<div class="gender">
<label><input name="gender" type="radio" value="F"/><?php echo WClient::getGenderText('F');?></label>
<label><input name="gender" type="radio" value="M"/><?php echo WClient::getGenderText('M');?></label>
<label><input name="gender" type="radio" value="N"/><?php echo WClient::getGenderText('N');?></label>
</div>
</div>

<div class="dialog form" id="dlg-clt-birthday" title="<?php echo Lang::getWord('title', 'Date of birth', 'clt');?>">
<label><?php echo Lang::getWord('prompt', 'Your date of birth', 'clt');?>:</label>
<div class="birthday">
<input class="bday" maxlength="2" />
<input class="bmon" maxlength="2" />
<input class="byear" maxlength="4" />
</div>
</div>

<div class="dialog form" id="dlg-clt-note" title="<?php echo Lang::getWord('title', 'Note', 'clt');?>">
<textarea class="value"></textarea>
</div>

<?php
} // if (WClient::id())
?>

<?php $popMess = WDsc::lastCmpMsg('nav-pop', 'subject,message');?>
<div class="nav-pop noselect">
 <div class='pop-subject'><?php if ($popMess) echo $popMess['subject']; ?></div>
 <div class='pop-message'><?php if ($popMess) echo $popMess['message']; ?></div>
</div>

<?php
$popMess = WDsc::lastCmpMsg('nav-pop-mod', 'subject,message');
if ($popMess)
{
 echo '<script type="text/javascript">app.dlyPop=' . JSON::encode($dlyPop, null) . ";</script>\n";
?>
 <div id="nav-pop-mod" class="noselect">
  <div class="nav-pop-mod-bkg">
  </div>
  <div id="nav-pop-mod-wnd" class="noselect">
   <span class='ui-icon-circle-close-bck'><span class='ui-icon ui-icon-circle-close'></span></span>
   <div class='pop-subject'><?php echo $popMess['subject']; ?></div>
   <div class='pop-message'><?php echo $popMess['message']; ?></div>
   <?php
    echo "<div style='margin:auto;text-align:center;' class='topw rd-fxd-wdth-200px'>";
     echo "<table><tr><td>";
      echo "<div class='rd-center rd-fxd-wdth-200px'>";
      $sn = false;
      foreach (array('gp', 'fb') as $net)
      {
       $href = XAuth::href($net);
       if ($href)
        echo "<div class='rd-fxd-blck rd-fxd-wdth-200px " . $net . "'><a class='button auth' net='$net' href='$href'>" .
          Lang::getPageWord('text', 'Sign up with') . ' ' . XAuth::name($net) . "</a></div>\n";
       $sn = true;
      }
      if ($sn){
       echo "<div style='width:30px;float:none;' class='rd-fxd-blck rd-center'>" . Lang::getPageWord('text', 'or') . "</div>\n";
      }
      echo "<div class='rd-fxd-blck rd-fxd-wdth-200px'><a class='button auth email-signup link-signup'>" .
          Lang::getPageWord('text', 'Sign up with email') . "</a></div>\n";
      echo "</div>";
     echo "</td></tr></table>";
    echo "</div>";
    echo "<div class='continue'><a>" .
     Lang::getPageWord('text', 'Continue to site') . "</a></div>\n";
    echo "</div>";
   ?>
  </div>
 </div>
<?php
}
?>

</nav>

<section>

<article id="art-home" class="noselect<?php if (Base::mode() == 'home') echo ' active';?>">
<?php $popMess = WDsc::lastCmpMsg('home-main-pop', 'subject,message');?>
<div id="home-main">
<div id="home-slideshow">
<?php
 $bgs = DB::getDB()->queryRecords('biz_menu_bg', 'id', 'image is not null and hidden is null', 'serial,id');
 for($i = 0; $i < count($bgs); ++$i)
 {
  $id = $bgs[$i][0];
  if ($i)
   echo "<div class='slide'><div class='image' src='img/menu-bg-$id.jpg'></div></div>\n";
  else
   echo "<div class='slide'><div class='image' style='background-image:url(img/menu-bg-$id.jpg)'></div></div>\n";
 }
?></div>
<div id="home-filter">
<h1><?php echo Lang::getPageWord('title', 'Find spa and salon appointments');?></h1>
<?php
 $whereC = 'hidden is null';
 if (WDomain::id() != null)
  $whereC .= ' and domain_id=' . WDomain::id();
 $numVenues = DB::getDB()->queryField('com_centre', 'count(*)', $whereC);
 $whereS = "exists (select null from com_centre b where id=a.centre_id and $whereC)";
 $numOffers = DB::getDB()->queryField(WService::TABLE_TIP . ' a', 'count(*)', $whereS);
 //echo "<h1>" . DB::lastQuery() . "</h1>\n";
 $offers = /*'<strong>' .*/ Lang::strInt($numOffers) /*. '</strong>'*/;
 $venues = /*'<strong>' .*/ Lang::strInt($numVenues) /*. '</strong>'*/;
 $subtext = Lang::getPageWord('title', 'Choose from $OFFERS$ offers and $VENUES$ venues');
 $subtitle = str_replace(array('$OFFERS$', '$VENUES$'), array($offers, $venues), $subtext);
?>
<h2><?php echo $subtitle;?></h2>
<table class="form ui-widget-content"><tr>

<td><div class="proc search-frame ui-state-highlight">
<span class="search-icon-left search"></span>
<input class="search-edit" placeholder="<?php echo addslashes(Lang::getPageWord('hint', 'What service are you looking for')); ?>?" placeholder-focus="<?php echo addslashes(Lang::getPageWord('hint', 'Treatment name')); ?>" />
<span class="search-icon-right ui-icon ui-icon-close"></span>
</div>

<div class="terr search-frame ui-state-highlight">
<span class="search-icon-left geoloc"></span>
<input class="search-edit" placeholder="<?php echo addslashes(Lang::getWord('hint', 'Where', 'home')); ?>?" placeholder-focus="<?php echo addslashes(Lang::getWord('hint', 'Location', 'home')); ?>" />
<span class="search-icon-right ui-icon ui-icon-close"></span>
</div>

<?php
//<td class="date"><div class="search-frame ui-state-highlight">
//<span class="search-icon-left ui-icon ui-icon-calendar"></span>
//<input class="search-edit date" />
//<span class="search-icon-right ui-icon ui-icon-close"></span>
//</div></td>
?>

<div class="search-button"><a id="button-search-start" href="list/" class="ax button main ui-state-error"><?php echo Lang::getWord('button', 'Search', 'home');?></a></div>
</td>
</tr></table>
</div>
<?php if ($popMess) { ?>
 <div id="home-main-pop" class="noselect">
  <div class='pop-subject'><?php echo $popMess['subject']; ?></div>
  <div class='pop-message'><?php echo $popMess['message']; ?></div>
 </div>
<?php } ?>
</div>

<div id="home-more" class="rd-hide-at-999px"><img src="pic/home-more.png"></div>

<div id="home-adv">

<?php
 $table = WTop::TABLE_CENTRE . ' a';
 $fields = 'id,centre_id' .
  ',coalesce' .
  '((select title from com_centre_abc where centre_id=a.centre_id and abc_id=\'' . Lang::current() . '\')' .
  ',(select title from com_centre_abc where centre_id=a.centre_id limit 1)' .
  ',(select name from com_centre where id=a.centre_id)' .
  ')centre_title' .
  ',coalesce' .
  '((select subtitle from com_centre_abc where centre_id=a.centre_id and abc_id=\'' . Lang::current() . '\')' .
  ',(select subtitle from com_centre_abc where centre_id=a.centre_id limit 1)' .
  ')centre_subtitle' .
  ',filename' .
  ",(select coalesce(max(discount),0) from com_centre_schema_interval
    where (day1=1 or day2=1 or day3=1 or day4=1 or day5=1 or day6=1 or day7=1)
    and discount>0 and discount<100
    and id in(
      select schema_id from com_centre_schema where centre_id=a.centre_id
      and (final_date is null or final_date>=current_timestamp)
      and (global='1'
          or schema_id in(
          select schema_id from com_menu_grp where centre_id=a.centre_id and schema_id is not null
          union
          select schema_id from com_menu_srv where centre_id=a.centre_id and schema_id is not null
          )))
    )max_discount";
 $order = 'serial,id';
 $domainId = WDomain::id();
 //$titles = array('TOP SPAS IN UAE', 'BEAUTY INDUSTRY NEWS', 'SUMMER BEAUTY TRENDS 2015');
 $titles = array('Features first row', 'Features second row', 'Features third row');
 for ($row = 1; $row <= 3; ++$row)
 {
  $where = "domain_id=$domainId and centre_id is not null and image is not null and hidden is null and row=$row";
  $where .= " and exists (select null from com_centre where id=a.centre_id and hidden is null)";
  $records = DB::getDB()->queryArrays($table, $fields, $where, $order);
  if ($records)
  {
   //echo "<!-- " . DB::lastQuery() . "-->\n";
   $rowClass = ($row == 1) ? 'big' : 'med';
   $imgCntnrPadding = ($row == 1) ? '.5%' : '1%';
   $imgWidth = ($row == 1) ? WTop::WIDTH1 : WTop::WIDTH2;
   $imgHeight = ($row == 1) ? WTop::HEIGHT1 : WTop::HEIGHT2;
   $title = Lang::getWord('title', $titles[$row - 1], 'home');
   echo "<div class='adv-row $rowClass'>\n";
   echo "<div class='adv-title big-title'>$title</div>\n";
   echo "<table class='layout rd-adv-tbl'><tr><td>\n";
   $count = count($records);
   foreach ($records as $record)
   {
    $id = $record['id'];
    $centreId = $record['centre_id'];
    $centreTitle = htmlspecialchars($record['centre_title']);
    $centreSubtitle = htmlspecialchars(str_replace('<br>', '. ', $record['centre_subtitle']));
    $filename = htmlspecialchars($record['filename']);
    $maxDsc = $record['max_discount'];
    echo '<div>';
    echo "<a style='float:left;padding:$imgCntnrPadding;' class='rd-img-cntnr ax' href='ctr-$centreId/'>\n";
    echo "<img width='$imgWidth' height='$imgHeight' src='img/top-$row/$id/$filename' style='width:100%;'>\n";
    if ($row == 1)
    {
     echo '<div class="book-button">' . Lang::getWord("button", "Book", "home") . '</div>';
     echo "<div class='img-title'>$centreTitle</div>\n";
    }
    else
    {
     echo "<div class='img-title1'>$centreTitle</div>\n";
     echo "<div class='img-title2'>$centreSubtitle</div>\n";
     echo "<div class='book-link'>" . Lang::getWord("button", "Book now", "home");
     if ($maxDsc > 0)
      echo str_replace('#DISCOUNT#%', '<span class="max-discount">' . $maxDsc . '%</span>', Lang::getWord("button", " and save up to #DISCOUNT#%", "home"));
     echo "</div>\n";
    }
    echo "</a>\n";
    echo '</div>';
   }
   echo "</td></tr></table>\n";
   echo "</div>\n";
  }
 }
?>
</div>

<a class="banner-link" href="mailto:<?php echo Lang::getPageWord('list-email', 'support@wellclubs.com')?>">
<div class="banner">
<div class="banner-text">
<div class="banner-top"><?php echo Lang::getPageWord('text', 'Would like to list your business?')?></div>
<div class="banner-bottom"><?php echo Lang::getPageWord('text', 'Drop us a line: ')?>
<span class="list-email"><?php echo Lang::getPageWord('list-email', 'support@wellclubs.com')?></span>
</div>
</div>
</div>
</a>

</article>

<article id="art-list" class="noselect<?php if (Base::mode() == 'list') echo ' active';?>">
<div style="display:flex;min-height:40px">
<div class="handle ui-widget-header">
<a class="topbtn ui-corner-all ui-state-highlight"><span class="search-icon-left search" style="left: 6px;top: 7px;"></span></a>
</div><div class="header"></div>
</div>

<div id="list-filter">

<div class="block brand ui-widget-header">
<div class="search-frame ui-state-highlight">
<span class="search-icon-left search"></span>
<input class="search-edit" placeholder="Search by venue" placeholder-focus="Venue name" value="<?php echo addslashes(PageBookList::bnd()); ?>"/>
<span class="search-icon-right ui-icon ui-icon-close"></span>
</div>
</div>

<div class="block terr ui-widget-header">
<div class="search-frame ui-state-highlight">
<span class="search-icon-left geoloc"></span>
<input class="search-edit" placeholder="<?php echo addslashes(Lang::getPageWord('prompt', 'Where?', 'list')); ?>" placeholder-focus="Location" value="<?php echo addslashes(PageBookList::locT());?>"/>
<span class="search-icon-right ui-icon ui-icon-close"></span>
</div>
</div>

<div class="block date ui-widget-header">
<div class="prompt ui-widget big-title"><?php echo addslashes(Lang::getPageWord('prompt', 'Availability', 'list')); ?></div>
<div class="search-frame ui-state-highlight">
<span class="search-icon-left calend"></span>
<input class="search-edit date" readonly placeholder="<?php echo addslashes(Lang::getPageWord('prompt', 'Date of visit', 'list')); ?>"/>
<span class="search-icon-right ui-icon ui-icon-close"></span>
</div>
<div class="options">
<table class="layout" width="100%"><colgroup><col width="50%"><col width="50%"></colgroup><tr>
<td><a href="#" offset="0" class="js button today"><?php echo addslashes(Lang::getPageWord('title', 'Today', 'list')); ?></a></td>
<td><a href="#" offset="1" class="js button tomorrow"><?php echo addslashes(Lang::getPageWord('title', 'Tomorrow', 'list')); ?></a></td>
</tr></table>
<?php
$morning = htmlspecialchars(Lang::getPageWord('title', 'Morning', 'list'));
$afternoon = htmlspecialchars(Lang::getPageWord('title', 'Afternoon', 'list'));
$evening = htmlspecialchars(Lang::getPageWord('title', 'Evening', 'list'));
$timeOfDay = array('', $morning, $afternoon, $evening);
$timeValue = array('', '(. . . . . - 12:00)', '(12:00 - 17:00)', '(17:00 - . . . . .)');
for ($i = 1; $i <= 3; ++$i)
{
 $id = 'list-time-' . $i;
 $checked = (strpos(PageBookList::time(), $i) === false) ? '' : 'checked ';
 echo
   "<div class='ui-widget-content'>" .
   "<input type='checkbox' id='$id' value='$i' $checked/>" .
   "<label class='option' for='$id'>" . $timeOfDay[$i] .
   "<div class='value'>" . $timeValue[$i] .
   "</div></label></div>\n";
}
?>
</div>
</div>

<?php
$socs = WSGrp::filters();
if (count($socs))
{
?>
<div class="block soc ui-widget-header">
<div class="prompt ui-widget ui-widget-header big-title"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Customers')); ?></div>
<div class="options">
<?php
foreach ($socs as $soc)
{
 $id = $soc['id'];
 $name = htmlspecialchars($soc['name']);
 $attrChk = PageBookList::testSoc($id) ? 'checked ' : '';
 echo
   "<div class='ui-widget-content'>" .
   "<input type='checkbox' id='list-soc-$id' value='$id' $attrChk/>" .
   "<label class='option' for='list-soc-$id'>$name</label>" .
   "</div>\n";
} // End of foreach ($socs as $soc)
?>
</div>
</div>
<?php
} // End of if (count($socs))
?>

<div class="block proc ui-widget-header">
<div class="prompt ui-widget ui-widget-header big-title"><?php echo htmlspecialchars(Lang::getPageWord('prompt', 'Treatments')); ?></div>
<?php
$cats = DB::getDB()->queryRecords('biz_menu_cat', 'id,name', 'hidden is null', 'serial,id');
if ($cats)
{
 foreach ($cats as $cat)
 {
  $title = htmlspecialchars(Lang::getDBValueDef('biz_menu_cat_abc', 'title', 'cat_id=' . ($id = $cat[0]), $cat[1]));
  echo "<div id='list-cat-item-$id' class='item ui-widget-header ui-corner-all'/><div class='title'>" . $title . "</div></div>\n";
  $prcs = DB::getDB()->queryRecords('biz_menu_prc', 'id,name', "cat_id=$id and hidden is null", 'serial,id');
  if ($prcs)
  {
   echo "<div id='list-cat-opts-$id' class='options'" . (PageBookList::testCat($id) ? " style='display:block;'" : "") . ">\n";
   foreach ($prcs as $i => $prc)
   {
    if ($i == '5')
     echo "<div class='more'>\n";
    $id = $prc[0];
    $title = htmlspecialchars(Lang::getDBValueDef('biz_menu_prc_abc', 'title', 'prc_id=' . $prc[0], $prc[1]));
    echo "<table class='option ui-widget-content' width='100%'><tr><td width='90%'><input type='checkbox' id='list-prc-$id'" . (PageBookList::testPrc($id) ? " checked" : "") . "/><label for='list-prc-$id'>$title</label></td><td class='value ui-priority-secondary' width='10%'></td></tr></table>\n";
   }
   if (count($prcs) > 5)
   {
    echo "</div>\n";
    echo "<div class='buttons'>\n";
    echo "<div class='show'/>" . Lang::getPageWord('button', 'Show more') . "</div>\n";
    echo "<div class='hide'/>" . Lang::getPageWord('button', 'Show less') . "</div>\n";
    echo "</div>\n";
   }
   echo "</div>\n";
  }
 }
}
?>
</div>

</div>
<div id="list-result">
<div class="data"><table class="cols"><tr>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
<td class="data-col"><div class="col"></div></td><td class='spacer'></td>
</tr></table></div>
<div class="footer"><span class="loading"><?php echo Lang::getPageWord('title', 'Loading');?>...</span>&nbsp;</div>
</div>

</article>

<?php ///Page Single Centre ?>
<article id="art-ctr" itemscope itemtype="http://schema.org/LocalBusiness" class="noselect<?php if (Base::mode() == 'ctr') echo ' active';?>">

<?php /// Header ?>
<div class="header obj-header">

<table class="layout"><tr><td class="rd-obj-header-left">

<div class="title big-title">
<table><tr>
<td itemprop="name" class="centre"><?php echo htmlspecialchars(WCentre::title());?></td>
<td class="brand"><a class="ax" <?php if (WBrand::title()) echo 'itemprop="brand"'; ?> href="<?php echo WBrand::title() ? ('bnd-' . WBrand::id() . '/') : '#';?>"><?php echo htmlspecialchars(WBrand::title());?></a></td>
</tr></table>
</div>

<div class="rd-hide-at-999px subtitle">
<table><tr>
<td class="loc"><a itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" class="ax" href="<?php echo htmlspecialchars(WCentre::locURI());?>"><span itemprop="streetAddress"><?php echo htmlspecialchars(WCentre::address());?></span></a></td>
</tr></table>
</div>

</td><td class="rd-hide-at-999px" width="300">

<div class="rating ui-widget-content"><div class="total">
<?php
if (Base::mode() == 'ctr')
{
 $facil = $ratings['facil'];
 $rateCount = $facil['count'];
 $rateTotal = $facil['total'];
 $rateCountText = PageBook::reviewCountText($rateCount);
 $rateTotalText = number_format($rateTotal, 1);
 $rateTotalStars = PageBook::stars($rateTotal, true);
 $rateTotalStarsSmall = PageBook::stars($rateTotal);
 $rateAmbieStars = PageBook::stars($facil['ambie']);
 $rateCleanStars = PageBook::stars($facil['clean']);
 $rateStaffStars = PageBook::stars($facil['staff']);
 $rateValueStars = PageBook::stars($facil['value']);
}
else
{
 $rateCount = 0;
 $rateTotal = 0;
 $rateCountText = PageBook::reviewCountText(0);
 $rateTotalText = '0.0';
 $stars = PageBook::stars(0);
 $rateTotalStars = PageBook::stars(0,true);
 $rateAmbieStars = $stars;
 $rateCleanStars = $stars;
 $rateStaffStars = $stars;
 $rateValueStars = $stars;
}
//echo '<div class="title">' . $rateTotalTitle . '</div>' . "\n";
echo '<div class="value">' . $rateTotalText . '<sub>/5</sub></div>' . "\n";
echo $rateTotalStars . "\n";
echo '<div class="count">' . $rateCountText . '</div>' . "\n";
if ($rateCount)
{
 echo '<span class="meta" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">' . "\n";
 echo '<meta itemprop="ratingValue" content="' . $rateTotal . '"/>' . "\n";
 echo '<meta itemprop="ratingCount" content="' . $rateCount . '"/>' . "\n";
 // If count is specified in review aggregate, page should contain reviews
 //echo '<meta itemprop="reviewCount" content="' . $rateCount . '"/>' . "\n";
 echo '</span>' . "\n";
}
?>
</div></div>

</td><td class="rd-hide-on-full-screen rd-small-total-rating">

<div class="rd-hide-on-full-screen rating ui-widget-content"><div class="total">
<?php
if (Base::mode() == 'ctr')
{
 $facil = $ratings['facil'];
 $rateCount = $facil['count'];
 $rateTotal = $facil['total'];
 $rateCountText = PageBook::reviewCountText($rateCount);
 $rateTotalText = number_format($rateTotal, 1);
 $rateTotalStars = PageBook::stars($rateTotal, true);
 $rateTotalStarsSmall = PageBook::stars($rateTotal);
 $rateAmbieStars = PageBook::stars($facil['ambie']);
 $rateCleanStars = PageBook::stars($facil['clean']);
 $rateStaffStars = PageBook::stars($facil['staff']);
 $rateValueStars = PageBook::stars($facil['value']);
}
else
{
 $rateCount = 0;
 $rateTotal = 0;
 $rateCountText = PageBook::reviewCountText(0);
 $rateTotalText = '0.0';
 $stars = PageBook::stars(0);
 $rateTotalStars = PageBook::stars(0,true);
 $rateTotalStarsSmall = PageBook::stars($rateTotal);
 $rateAmbieStars = $stars;
 $rateCleanStars = $stars;
 $rateStaffStars = $stars;
 $rateValueStars = $stars;
}
//echo '<div class="title">' . $rateTotalTitle . '</div>' . "\n";
echo '<div class="value" style="font-size:inherit;">' . $rateTotalText . '<sub>/5</sub></div>' . "\n";
echo $rateTotalStarsSmall . "\n";
echo '<div class="count">' . $rateCountText . '</div>' . "\n";
if ($rateCount)
{
 echo '<span class="meta" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">' . "\n";
 echo '<meta itemprop="ratingValue" content="' . $rateTotal . '"/>' . "\n";
 echo '<meta itemprop="ratingCount" content="' . $rateCount . '"/>' . "\n";
 // If count is specified in review aggregate, page should contain reviews
 //echo '<meta itemprop="reviewCount" content="' . $rateCount . '"/>' . "\n";
 echo '</span>' . "\n";
}
?>
</div></div>

</td>
</tr></table>

</div>
<?php /// End of the Header ?>

<?php /// Sidebar ?>
<div class="brief">

<?php
echo '<div class="loc"' . (isset($loc) ? ' itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates"' : ' style="display:none;"') . '>' . "\n";
echo '<meta itemprop="latitude" content="' . (isset($loc) ? $loc['lat'] : '') . '"/>' . "\n";
echo '<meta itemprop="longitude" content="' . (isset($loc) ? $loc['lng'] : '') . '"/>' . "\n";
echo '<a class="map" href="' . (isset($loc) ? $loc['dynamicURI'] : '') . '" target="_blank">' . "\n";
echo '<img class="map" src="' . (isset($loc) ? $loc['staticURI'] : '') . '"/>' . "\n";
echo '</a>' . "\n";
echo '</div>' . "\n";

$cardVisible = isset($addr) && strlen($addr) || (isset($metros) && count($metros)) ||
  (isset($phones) && count($phones)) || (isset($sched) && count($sched));
echo '<div class="card ui-widget-content"' . ($cardVisible ? '' : ' style="display:none;"') . '>' . "\n";

//echo '<div class="title">' . htmlspecialchars(WCentre::title() ? WCentre::title() : WBrand::title()) . '</div>' . "\n";
echo '<div>' . "\n";
echo '<div class="addr">' . htmlspecialchars($addr) . '</div>' . "\n";
echo '</div>' . "\n";

echo '<div class="rd-hide-at-670px">';
// Metro stations
echo '<div class="block metros"' . ((isset($metros) && count($metros)) ? '' : ' style="display:none;"') . '>' . "\n";
echo '<div class="caption">' . htmlspecialchars(Lang::getPageWord('title','Metro stations')) . '</div>' . "\n";
echo '<div class="list">' . "\n";
if (isset($metros))
 foreach ($metros as $metro)
  echo '<div class="metro">' . htmlspecialchars($metro) . '</div>' . "\n";
echo '</div>' . "\n";
echo '</div>' . "\n";
// Phone numbers
echo '<div class="block phones"' . ((isset($phones) && count($phones)) ? '' : ' style="display:none;"') . '>' . "\n";
echo '<div class="caption">' . htmlspecialchars(Lang::getPageWord('title','Phone numbers')) . '</div>' . "\n";
echo '<div class="list">' . "\n";
if (isset($phones))
 foreach ($phones as $phone)
  echo '<div itemprop="telephone" class="phone">' . htmlspecialchars($phone) . '</div>' . "\n";
echo '</div>' . "\n";
echo '</div>' . "\n";
echo '</div>' . "\n"; // class="rd-hide-at-670px"
// Schedule
echo '<div class="block sched"' . ((isset($sched) && count($sched)) ? '' : ' style="display:none;"') . '>' . "\n";
echo '<div class="caption">' . htmlspecialchars(Lang::getPageWord('title','Opening hours')) . '</div>' . "\n";
echo '<table><tbody>' . "\n";
if (isset($sched))
 foreach ($sched as $day)
 {
  if ($day[1])
   echo '<meta itemprop="openingHours" content="' . $day[2] . ' ' . $day[1][0] . '-' . $day[1][1] . '" />';
  echo '<tr>';
  echo '<td>' . htmlspecialchars($day[0]) . '</td>';
  if ($day[1])
   echo '<td>' . $day[1][0] . '</td><th>-</th><td>' . $day[1][1] . '</td>';
  else
   echo '<th colspan="3">' . htmlspecialchars(Lang::getPageWord('sched', 'Closed')) . '</th>';
  echo '</tr>' . "\n";
 }
echo '</tbody></table>' . "\n";
echo '</div>' . "\n";

echo '</div>' . "\n";
?>

</div>
<?php /// End of the sidebar ?>

<div class="detail">

<?php
/// Image gallery
echo "<div class='gallery rd-hide-at-999px'" . (count($images) ? '' : ' style="display:none;"') . ">\n";
echo "<div class='images'>\n";
for ($i = 0; $i < 10; $i++)
 echo "<div class='image'></div>\n";
echo "</div>\n"; //  class='images'
echo "</div>\n"; //  class='gallery'
/// End of the Image gallery
?>

<?php
/// Description
if (Base::mode() == 'ctr')
{
 $descr = WCentre::descr();
 if (!$descr)
  $descr = WBrand::descr();
}
echo "<div class='topic descr compact small'" . (strlen($descr) ? '' : " style='display:none;'") . ">\n";
echo "<div class='topic-title big-title ui-widget-header'>" . htmlspecialchars(Lang::getWord('title', 'Overview', 'ctr')) . "</div>\n";
echo "<div class='rd-hide-at-999px text'>" . Util::strHTML($descr) . "</div>\n";
//Template for more-form, it must be presents here once, gets cloned to all elements of this class in page.book.js:
echo "<div class='more-form'><div class='grad'></div><div class='more'>... " . Lang::getPageWord('text','show&nbspmore') . " ></div></div>\n";
echo "</div>\n";
/// End of the Description
?>

<?php /// Services ?>
<div id="srvs" class="topic services" style="display:none;">
<div class="topic-title big-title ui-widget-header"><?php echo htmlspecialchars(Lang::getWord('title', 'Services', 'ctr'));?></div>
<div class="groups">
<table>
<colgroup><col class="name" /><col class="dura" /><col class="price" /></colgroup>
<tbody class="body"></tbody>
</table>
</div>
</div>
<?php /// End of the Services ?>

<?php /// Reviews ?>
<div class="topic reviews">
<div class="topic-title big-title ui-widget-header"><?php echo htmlspecialchars(Lang::getWord('title', 'Reviews', 'ctr'));?></div>

<div class="stat ui-widget-content">
<table width="100%"><tr><td class="stat-left" width="1">
<div class="common"><table class="total"><tr><td>
<div class="value"><?php echo $rateTotalText; ?></div>
</td><td>
<!--div class="title"><?php //echo $rateTotalTitle; ?></div-->
<?php echo $rateTotalStars; ?>
</td><td>
<div class="count"><?php echo $rateCountText; ?></div>
</td></tr></table></div>
<table><tr><td>
<table class="distr">
<?php
$distr = (is_array($ratings) && array_key_exists('distr', $ratings)) ? $ratings['distr'] : null;
for ($i = 5; $i > 0; $i--)
{
 $stars = PageBook::stars($i);
 $value = $distr ? intval($distr[$i]) : 0;
 $width = ($value && $rateCount) ? intval($value * 100 / $rateCount) : 0;
 echo "<tr class='rate$i'><td>$stars</td><td><div class='progress'><div class='bar ui-widget-header' style='width:{$width}%'></div></div></td><th><i>(<span class='value'>$value</span>)</i></th></tr>\n";
}
?>
</table>
</td><td class="rd-hide-at-450px" style="border-left:1px solid">
<table class="facil">
<?php
echo '<tr class="ambie"><td class="title">' . $rateAmbieTitle . '</td><td>' . $rateAmbieStars . '</td></tr>' . "\n";
echo '<tr class="clean"><td class="title">' . $rateCleanTitle . '</td><td>' . $rateCleanStars . '</td></tr>' . "\n";
echo '<tr class="staff"><td class="title">' . $rateStaffTitle . '</td><td>' . $rateStaffStars . '</td></tr>' . "\n";
echo '<tr class="value"><td class="title">' . $rateValueTitle . '</td><td>' . $rateValueStars . '</td></tr>' . "\n";
?>
</table>
</td></tr></table>
</td><td class="rd-hide-at-670px stat-right">
<div class="prcs">
<table><tbody>
<?php
if (is_array($ratings) && array_key_exists('cats', $ratings))
{
 foreach ($ratings['cats'] as $cat)
 {
  if (!intval($cat['rated']))
   continue;
  $title = htmlspecialchars($cat['title']);
  $list = $cat['list'];
  echo "<tr><th colspan='2'>$title</th></tr>\n";
  foreach ($list as $prc)
  {
   $count = intval($prc['rcnt']);
   if (!$count)
    continue;
   $title = htmlspecialchars($prc['title']);
   $rate = round(intval($prc['rsum']) / $count, 2);
   echo "<tr title='$title ($count)'><td><label>$title</label> <i>($count)</i></td><td>" . PageBook::stars($rate) . "</td></tr>\n";
  }
 }
}
?>
</tbody></table>
</div>
</td></tr></table>
</div>

<div> <!--to protect toggle-->
<div class="add rd-hide-at-670px">
<div class="prompt"><?php echo Lang::getPageWord('text', 'Have you been there');?>?</div>
<a id="button-ctr-write-review" class="js button" href="#"><?php echo htmlspecialchars(Lang::getPageWord('title', 'Write a review'));?></a>
</div>
</div>

<div class="body"></div>
</div>
<?php /// End of the Reviews ?>

</div>

</article>

<?php ///Page Brand ?>
<article id="art-bnd" class="noselect<?php if (Base::mode() == 'bnd') echo ' active';?>">

<?php /// Header ?>
<div class="header obj-header">

<div class="title big-title">
<table><tr>
<td><a class="ax" href="<?php echo WBrand::title() ? ('list/bnd-' . urlencode(WBrand::title()) . '/') : '#';?>"><?php echo htmlspecialchars(WBrand::title());?></a></td>
</tr></table>
</div>

</div>
<?php /// End of the Header ?>

<?php /// Sidebar ?>
<div class="brief">

<?php /*<div class="logo">
<img src="" width="300">
</div>*/ ?>

</div>
<?php /// End of the sidebar ?>

<div class="detail">

<?php
/// Image gallery
echo "<div class='gallery'" . (count($images) ? '' : ' style="display:none;"') . ">\n";
echo "<div class='images'>\n";
for ($i = 0; $i < 5; $i++)
 echo "<div class='image'></div>\n";
echo "</div>\n"; //  class='images'
echo "</div>\n"; //  class='gallery'
/// End of the Image gallery

/// Description
if (Base::mode() == 'bnd')
 $descr = WBrand::descr();
echo "<div class='topic descr compact small'" . (strlen($descr) ? '' : " style='display:none;") . "'>\n";
echo "<div class='topic-title big-title ui-widget-header'>" . htmlspecialchars(Lang::getWord('title', 'Overview', 'bnd')) . "</div>\n";
echo "<div class='text'>" . Util::strHTML($descr) . "</div>\n";
echo "</div>\n";
/// End of the Description
?>

</div>

</article>

<?php ///Page Service ?>
<article id="art-srv" class="noselect<?php if (Base::mode() == 'srv') echo ' active';?>">

<?php
/// Header
echo "<div class='header obj-header'>\n";

echo "<table class='layout'><tr><td>\n";

echo "<div class='title big-title'>\n";
echo "<table><tr>\n";
echo "<td><a class='ax srv' href='ctr-" . WService::centreId() . "/#srv-" . WService::id() . "'>" . htmlspecialchars(WService::title()) . "</a></td>\n";
echo "</tr></table>\n";
echo "</div>\n"; // class='title big-title'

echo "<div class='subtitle'>\n";
echo "<table><tr>\n";
echo "<td><a class='ax ctr' href='ctr-" . WService::centreId() . "/'>" . htmlspecialchars(WCentre::getTitle(WService::centreId())) . "</a></td>\n";
echo "</tr></table>\n";
echo "</div>\n"; // class='subtitle'

echo "</td><td class='rd-hide-at-999px' width='300'>\n";

echo "<div class='rating ui-widget-content'>\n";
echo "<div class='total'>\n";
if (Base::mode() == 'srv')
{
 $facil = $ratings['facil'];
 $rateCount = $facil['count'];
 $rateCountText = PageBook::reviewCountText($rateCount);
 $rateTotal = $facil['total'];
 $rateTotalText = number_format($rateTotal, 1);
 $rateTotalStars = PageBook::stars($rateTotal, true);
 $rateAmbieStars = PageBook::stars($facil['ambie']);
 $rateCleanStars = PageBook::stars($facil['clean']);
 $rateStaffStars = PageBook::stars($facil['staff']);
 $rateValueStars = PageBook::stars($facil['value']);
}
//echo '<div class="title">' . $rateTotalTitle . '</div>' . "\n";
echo '<div class="value">' . $rateTotalText . '</div>' . "\n";
echo $rateTotalStars . "\n";
echo '<div class="count">' . $rateCountText . '</div>' . "\n";
if ($rateCount)
{
 echo '<span class="meta" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">' . "\n";
 echo '<meta itemprop="ratingValue" content="' . $rateTotal . '"/>' . "\n";
 echo '<meta itemprop="ratingCount" content="' . $rateCount . '"/>' . "\n";
 // If count is specified in review aggregate, page should contain reviews
 //echo '<meta itemprop="reviewCount" content="' . $rateCount . '"/>' . "\n";
 echo '</span>' . "\n";
}
echo "</div>\n"; // class='total'
echo "</div>\n"; // class='rating ui-widget-content'
echo '</td><td class="rd-hide-on-full-screen rd-small-total-rating">';
echo '<div class="rd-hide-on-full-screen rating ui-widget-content"><div class="total">';
if (Base::mode() == 'srv')
{
 $facil = $ratings['facil'];
 $rateCount = $facil['count'];
 $rateTotal = $facil['total'];
 $rateCountText = PageBook::reviewCountText($rateCount);
 $rateTotalText = number_format($rateTotal, 1);
 $rateTotalStars = PageBook::stars($rateTotal, true);
 $rateTotalStarsSmall = PageBook::stars($rateTotal);
 $rateAmbieStars = PageBook::stars($facil['ambie']);
 $rateCleanStars = PageBook::stars($facil['clean']);
 $rateStaffStars = PageBook::stars($facil['staff']);
 $rateValueStars = PageBook::stars($facil['value']);
}
else
{
 $rateCount = 0;
 $rateTotal = 0;
 $rateCountText = PageBook::reviewCountText(0);
 $rateTotalText = '0.0';
 $stars = PageBook::stars(0);
 $rateTotalStars = PageBook::stars(0,true);
 $rateTotalStarsSmall = PageBook::stars($rateTotal);
 $rateAmbieStars = $stars;
 $rateCleanStars = $stars;
 $rateStaffStars = $stars;
 $rateValueStars = $stars;
}
echo '<div>' . $rateTotalText . '<sub>/5</sub></div>' . "\n";
echo $rateTotalStarsSmall . "\n";
echo '<div class="count">' . $rateCountText . '</div>' . "\n";
if ($rateCount)
{
 echo '<span class="meta" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">' . "\n";
 echo '<meta itemprop="ratingValue" content="' . $rateTotal . '"/>' . "\n";
 echo '<meta itemprop="ratingCount" content="' . $rateCount . '"/>' . "\n";
 // If count is specified in review aggregate, page should contain reviews
 //echo '<meta itemprop="reviewCount" content="' . $rateCount . '"/>' . "\n";
 echo '</span>' . "\n";
}
echo '</div></div>';

echo "</td></tr></table>\n";

echo "</div>\n"; // class='header obj-header'
/// End of the Header

/// Sidebar
echo "<div class='brief'>\n";
echo "<div class='book-form'>\n";

echo "<div class='main-caption'>\n";
echo '<div class="text ui-state-error">' . Lang::getWord('title', 'Book appointment', 'srv') . '</div>' . "\n";
echo '<div class="text book-hint">' . Lang::getWord('hint', 'To book an appointment please select a date and time below') . '</div>' . "\n";
echo "</div>\n"; // class='main-caption'
echo "<div id='srv-calendar'></div>\n";

$tips = (Base::mode() == 'srv') ? PageBookSrv::tips() : array(); // For quick test
echo "<div id='srv-tips'" . ((count($tips) > 1) ? "" : " style='display:none'") . ">\n";
echo '<div class="caption ui-widget-header">' . Lang::getWord('title', 'Select option', 'srv') . '</div>' . "\n";
echo "<ul class='ui-widget-content'></ul>\n";
echo "</div>\n"; // id='srv-tips'

echo "<div id='srv-slots'" . ((count($tips) && count($tips[0]['slots'])) ? "" : " style='display:none'") . ">\n";
echo '<div class="caption ui-widget-header">' . Lang::getWord('title', 'Choose time slot', 'srv') . '</div>' . "\n";
echo "<ul class='ui-widget-content'></ul>\n";
echo "</div>\n"; // id='srv-slots'

echo "</div>\n"; // class='book-form'
echo "</div>\n"; // class='brief'
/// End of the sidebar

echo "<div class='detail'>\n";

/// Description
if (Base::mode() == 'srv')
 $descr = WService::descr();
echo "<div class='topic descr compact small'" . (strlen($descr) ? '' : " style='display:none;") . "'>\n";
echo "<div class='topic-title big-title ui-widget-header'>" . htmlspecialchars(Lang::getWord('title', 'Description', 'srv')) . "</div>\n";
echo "<div class='text'>" . Util::strHTML($descr) . "</div>\n";
echo "</div>\n";
/// End of the Description

/// Restrictions
$restr = (Base::mode() == 'srv') ? WService::restr() : '';
echo "<div class='topic restr compact small'" . (strlen($restr) ? '' : " style='display:none;") . "'>\n";
echo "<div class='topic-title big-title ui-widget-header'>" . htmlspecialchars(Lang::getWord('title', 'Restrictions', 'srv')) . "</div>\n";
echo "<div class='text'>" . Util::strHTML($restr) . "</div>\n";
echo "</div>\n";
/// End of the Restrictions

/// Notes
$notes = (Base::mode() == 'srv') ? WService::notes() : '';
echo "<div class='topic notes compact small'" . (strlen($notes) ? '' : " style='display:none;") . "'>\n";
echo "<div class='topic-title big-title ui-widget-header'>" . htmlspecialchars(Lang::getWord('title', 'Notes', 'srv')) . "</div>\n";
echo "<div class='text'>" . Util::strHTML($notes) . "</div>\n";
echo "</div>\n";
/// End of the Notes

/// Reviews
echo "<div class='topic reviews'>\n";

echo "<div class='topic-title big-title ui-widget-header'>" . htmlspecialchars(Lang::getWord('title', 'Reviews', 'ctr')) . "</div>\n";

echo "<div class='stat ui-widget-content'>\n";

// Table level 1
echo "<table width='100%'><tr><td class='stat-left' width='1'>\n";
echo "<div class='common'><table class='total'><tr><td>\n";
echo "<div class='value'>" . $rateTotalText . "</div>\n";
echo "</td><td>\n";
echo $rateTotalStars;
echo "</td><td>\n";
echo "<div class='count'>" . $rateCountText . "</div>\n";
echo "</td></tr></table></div>\n";

echo "<table><tr><td>\n"; // Table level 2

echo "<table class='distr'>\n"; // Table level 3
if (Base::mode() == 'srv')
 $distr = (is_array($ratings) && array_key_exists('distr', $ratings)) ? $ratings['distr'] : null;
for ($i = 5; $i > 0; --$i)
{
 $stars = PageBook::stars($i);
 $value = $distr ? intval($distr[$i]) : 0;
 $width = ($value && $rateCount) ? intval($value * 100 / $rateCount) : 0;
 echo "<tr class='rate$i'><td>$stars</td><td><div class='progress'><div class='bar ui-widget-header' style='width:{$width}%'></div></div></td><th><i>(<span class='value'>$value</span>)</i></th></tr>\n";
}
echo "</table>\n"; // Table level 3

echo "</td><td style='border-left:1px solid'>\n"; // Table level 2

echo "<table class='facil rd-hide-at-750px rd-unhide-at-550px rd-hide-at-450px'>\n"; // Table level 3
echo '<tr class="ambie"><td class="title">' . $rateAmbieTitle . '</td><td>' . $rateAmbieStars . '</td></tr>' . "\n";
echo '<tr class="clean"><td class="title">' . $rateCleanTitle . '</td><td>' . $rateCleanStars . '</td></tr>' . "\n";
echo '<tr class="staff"><td class="title">' . $rateStaffTitle . '</td><td>' . $rateStaffStars . '</td></tr>' . "\n";
echo '<tr class="value"><td class="title">' . $rateValueTitle . '</td><td>' . $rateValueStars . '</td></tr>' . "\n";
echo "</table>\n"; // Table level 3

echo "</td></tr></table>\n"; // Table level 2

echo "</td><td class='stat-right rd-hide-at-999px'>\n"; // Table level 1

echo "<div class='prcs'>\n";
echo "<table><tbody>\n"; // Table level 2

if (is_array($ratings) && array_key_exists('cats', $ratings))
{
 foreach ($ratings['cats'] as $cat)
 {
  if (!intval($cat['rated']))
   continue;
  $title = htmlspecialchars($cat['title']);
  $list = $cat['list'];
  echo "<tr><th colspan='2'>$title</th></tr>\n";
  foreach ($list as $prc)
  {
   $count = intval($prc['rcnt']);
   if (!$count)
    continue;
   $title = htmlspecialchars($prc['title']);
   $rate = round(intval($prc['rsum']) / $count, 2);
   echo "<tr title='$title ($count)'><td><label>$title</label> <i>($count)</i></td><td>" . PageBook::stars($rate) . "</td></tr>\n";
  }
 }
}

echo "</tbody></table>\n"; // Table level 2
echo "</div>\n";

echo "</td></tr></table>\n"; // Table level 1

echo "</div>\n"; // class='stat ui-widget-content'

echo "<div><!--to protect toggle--><div class='add rd-hide-at-670px'>\n";
echo "<div class='prompt'>" . htmlspecialchars(Lang::getPageWord('text', 'Have you been there')) . "</div>\n";
echo "<a id='button-srv-write-review' class='js button' href='#'>" . htmlspecialchars(Lang::getPageWord('title', 'Write a review')) . "</a>\n";
echo "</div></div>\n"; // class='add'

echo "<div class='body'></div>\n";

echo "</div>\n"; // class='topic reviews'
/// End of the Reviews

echo "</div>\n"; // class='detail'
?>

</article>

<?php ///Page Payment

$purchase = null;
$price = 0;
$total = 0;
$curr = '';
$disc = '';
$qty = 0;
$signInDscAmnt = 0;
if (Base::mode() == 'pay')
{
 $purchase = WPurchase::getDataFromHttpParams(true);
 if ($purchase)
 {
  $price = Util::item($purchase, 'price');
  $total = Util::item($purchase, 'total');
  $curr = Util::item($purchase, 'curr');
  $disc = Util::item($purchase, 'disc');
  $qty = Util::item($purchase, 'qty');
  $signInDscAmnt = Util::item($purchase, 'signInDscAmnt');
  echo '<script type="text/javascript">' . "\n";
  echo 'app.pay=' . JSON::encode($purchase, null) . ';' . "\n";
  echo '</script>' . "\n";
 }
}

$class = 'noselect';
if (Base::mode() == 'pay')
{
 $class .= ' active';
 if (Util::item($purchase, 'ref'))
  $class .= ' has-ref';
 if (Util::item($purchase, 'disc') > 0)
  $class .= ' has-disc';

 if ($signInDscAmnt)
  $class .= ' signin-disc';
}

echo '<article id="art-pay" class="' . $class . '">' . "\n";

echo '<center>' . "\n";
echo '<div class="message type-thanks ref-show">' . Lang::getWord('title', 'Thank you for using Wellclubs!', 'pay') . '</div>' . "\n";
echo '<div class="message type-pay">' . Lang::getWord('title', 'Your payment has been successful', 'pay') . '</div>' . "\n";
echo '<div class="message type-book">' . Lang::getWord('title', 'Your booking has been successful', 'pay') . '</div>' . "\n";
echo '<div class="caption ref-hide">' . Lang::getWord('title', 'Review your booking details', 'pay') . '</div>' . "\n";
echo '<div class="caption ref-show">' . Lang::getWord('title', 'Your booking details', 'pay') . '</div>' . "\n";

if (WClient::id())
{
 echo '<div class="ref-data ref-show">' . "\n";
 echo '<div class="book-id">' .
   '<span class="prompt">' . Lang::getWord('prompt', 'Booking Reference Number', 'pay') . ':</span>&nbsp;' .
   '<span class="value">' . Util::item($purchase, 'ref') . '</span>' .
   '</div>' . "\n"; // class="book-id"
 echo '<div class="user-id">' .
   '<span class="prompt">' . Lang::getWord('prompt', 'Customer ID', 'pay') . ':</span>&nbsp;' .
   '<span class="value">' . WClient::id() . '</span>' .
   '</div>' . "\n"; // class="user-id"
 echo '</div>' . "\n"; // class="ref-data"
}

echo '<div class="data">' . "\n";
// Begin of the block

echo '<div class="ctr">' . Util::item($purchase, 'ctrT') . '</div>' . "\n";
echo '<div class="pay-top">' . "\n";
echo '<div class="pay-top-left">' . "\n";
echo '<div class="date">' . Util::item($purchase, 'dateT') . '</div>' . "\n";
echo '<div class="time">' . Util::item($purchase, 'timeT') . '</div>' . "\n";
echo '</div>' . "\n"; // class="pay-top-left"
echo '<div class="pay-top-right">' . "\n";
echo '<div class="price">' .
  '<span class="value">' . WCurrency::addObjs(Lang::strInt($price), $curr, false, true) . '</span>' .
  '</div>' . "\n"; // class="price"
echo '</div>' . "\n"; // class="pay-top-right"
echo '<div class="pay-top-main">' . "\n";
echo '<div class="srv">' . Util::item($purchase, 'srvT') . '</div>' . "\n";
echo '<div class="dura">' . Util::item($purchase, 'dura') . '&nbsp;' . Lang::getPageWord('time', 'min') . '&nbsp;</div>' . "\n";
echo '<span class="ui-icon ui-icon-clock"></span>';
echo '</div>' . "\n"; // class="pay-top-main"
echo '</div>' . "\n"; // class="pay-top"

echo '<div class="pay-bottom">' . "\n";
echo '<div class="disc disc-show">' .
  '<div class="disc-left">' .
  '<span class="promo">' . Lang::getPageWord('disc', 'Promotion') . ' ' . '</span>' .
  '<span class="prompt">' . Lang::getPageWord('disc', 'Discount') . ' ' . '</span>' .
  '<span class="prc">' . $disc . '</span>%' .
  //'<span class="off">' . '&nbsp;' . Lang::getPageWord('disc', 'off') . '</span>' .
  '</div>' . // class="disc-left"
  '<div class="disc-right">' .
  '-<span class="value">' . WCurrency::addObjs(Lang::strInt(Util::item($purchase, 'totalDiscount')), $curr, false, true) . '</span>' .
  '</div>' . // class="disc-right"
  '</div>' . "\n";

// signin discount
echo
  '<div class="disc signin-disc-show">' .
  '<div class="disc-left">' .
  '<span class="promo signin-disc-description">' . Util::item($purchase, 'signInDscDscr') . ' </span>' .
  '</div>' . // class="disc-left"
  '<div class="disc-right">' .
  '-<span class="signin-disc-total">' . WCurrency::addObjs(Lang::strInt($signInDscAmnt), $curr, false, true) . '</span>' .
  '</div>' . // class="disc-right"
  '</div>' . "\n";

// promo code
 echo "<div class='disc pro-code pro-code-show'>\n";
  echo "<div class='disc-left'>\n";
   echo '<div class="prompt input-row">' . Lang::getPageWord('prompt', 'Promo code:', 'clt') . '</div>' . "\n";
   echo "<div class='input-block'>\n";
    echo "<div class='input'><input value='' type='promo' class='text' name='pro-code' /></div>\n";
   echo "</div>\n"; // class="input-block"
  echo "</div>\n"; // class="disc-left"
  echo
   '<div class="disc-right input-row pro-code-vld-show">' .
   '-<span class="disc-total">' . WCurrency::addObjs(Lang::strInt($signInDscAmnt), $curr, false, true) . '</span>' .
   '</div>' . // class="disc-right"
   "\n";
   echo '<div class="disc-left error">' . Lang::getPageWord('text', "this promo code is not valid, please check terms and conditions in your discount vaucher", 'clt') . '</div>' . "\n";
 echo "</div>\n"; // class='pro-code'

// Total amount
echo '<hr>' . "\n";
echo '<div class="total">' . "\n";
echo '<div class="prompt">' . Lang::getPageWord('prompt', 'Total') . '&nbsp;</div>' . "\n";
echo '<div class="amount">' .
  '<span class="value">' . WCurrency::addObjs(Lang::strInt($total), $curr, false, true) . '</span>' .
  '</div>' . "\n"; // class="value"
echo '</div>' . "\n"; // class="total"
if (WClient::id())
{
 echo '<div class="opts ref-hide disc-hide signin-disc-hide pro-code-vld-hide online">' . "\n";
 echo '<div class="prompt">' . Lang::getPageWord('prompt', 'Payment options') . '</div>' . "\n";
 echo '<div class="checkbox pay-later"><input type="checkbox"/><label>' . Lang::getPageWord('checkbox', 'Pay at venue') . '</label></div>' . "\n";
 echo '<div class="checkbox pay-now"><input type="checkbox"/><label>' . Lang::getPageWord('checkbox', 'Pay now') . '</label></div>' . "\n";
 /// Telephone number
 $phoneFormat = Lang::getPageWord('text', '+971 5x xxx xxxx');
 echo "<div class='phone'>\n";
  echo '<div class="prompt input-row">' . Lang::getPageWord('prompt', 'Please provide your mobile phone number:', 'clt') . '</div>' . "\n";
  echo "<div class='input-block'>\n";
   echo "<div class='input'><input value='" . WClient::phone() . "' type='phone' class='text' name='phone' placeholder='" . $phoneFormat . "' /></div>\n";
   echo '<div class="error ui-state-highlight">' . Lang::getPageWord('text', "please enter a valid UAE mobile number or international mobile number starting with '+' and country code", 'clt') . '</div>' . "\n";
  echo "</div>\n"; // class='input-block'
 echo "</div>\n"; // class='phone'
 echo '</div>' . "\n"; // class="opts"
}
echo '<div class="btns ref-hide">' . "\n";
if (WClient::id())
{
 echo '<a class="js button main ok book" href="#">' . Lang::getWord('button', 'Proceed to payment', 'pay') . '</a>' . "\n";
 echo '<a class="js button main ok order" href="#">' . Lang::getWord('button', 'Confirm booking', 'pay') . '</a>' . "\n";
}
else
 echo '<a class="js button login" href="#">' . Lang::getWord('button', 'Log in to book', 'pay') . '</a>' . "\n";
$hrefChg = Util::item($purchase, 'hrefChg');
if (!strlen($hrefChg))
 $hrefChg = Base::home();
echo '<a class="ax change" href="' . $hrefChg . '">' . Lang::getWord('button', 'Back to selection', 'pay') . '</a>' . "\n";
echo '</div>' . "\n"; // class="btns"
if (WClient::id())
{
 echo '<div class="sent ref-show ui-state-highlight">' . "\n";
 echo Lang::getWord('text', 'Booking confirmation has been sent to your registered email address', 'pay') . ' &lt;' . WClient::email() . '&gt;';
 echo '</div>' . "\n"; // class="sent"
 echo '<div class="btns ref-show">' . "\n";
 echo '<a class="ax button home" href=".">' . Lang::getWord('button', 'Make another booking', 'pay') . '</a>' . "\n";
 echo '</div>' . "\n"; // class="btns"
 echo '<div class="policy">' . "\n";
 $pay_policy_def = '<h6>Cancellation policy:</h6>' .
   '<ul>' . "\n" .
   '<li>Full refund if cancelled within 24 hours of placing the booking. The refund will only apply to bookings paid through online payment system and will be made through the original mode of payment only.</li>' . "\n" .
   '<li>If cancelled in less than 24 hours before the time of appointment or in case of no-show, the payment is non-refundable.</li>' . "\n" .
   '<li>In all other cases, you can reschedule your booking by directly contacting <span class="ctr"></ctr>.</li>' . "\n" .
   '</ul>' . "\n";
 $pay_policy = Lang::getText('text', 'pay-policy', 'pay');
 if (!strlen($pay_policy))
  $pay_policy = $pay_policy_def;
 echo $pay_policy;
 echo '</div>' . "\n"; // class="policy"
}
echo '</div>' . "\n"; // class="pay-bottom"

// End of the block
echo '</div>' . "\n"; // class="data"
echo '</article>' . "\n"; // id="art-pay"
?>

<?php ///Page Client Info ?>
<article id="art-clt" class="noselect<?php if (Base::mode() == 'clt') echo ' active';?>">

<?php /// Header ?>
<div class="obj-header">
<table class="layout"><tr>
<td field="img" width="128">
<img id="clt-img" src="" width="128" height="128">
<div class="change"><?php echo Lang::getWord('action', 'Change', 'clt');?></div>
</td>
<td field="name">
<div id="clt-name" class="title big-title"></div>
<a class="ax public-view" href="#"><?php echo Lang::getWord('link', 'Public view', 'clt');?></a>
</td>
</tr></table>
</div>
<?php /// End of the Header ?>

<?php

/// Public records
PageBookClt::putRecordsBegin('Public information');
PageBookClt::putRecordText('title', 'Title');
PageBookClt::putRecordText('firstname', 'First name');
PageBookClt::putRecordText('lastname', 'Last name');
PageBookClt::putRecordList('gender', 'Gender');
PageBookClt::putRecordDate('birthday', 'Date of birth');
PageBookClt::putRecordView('visited', 'Last visited');
PageBookClt::putRecordView('created', 'Registered');
PageBookClt::putRecordsEnd();

/// Note
?>
<div class="topic descr" field="note">
<div class="topic-title big-title"><?php echo Lang::getWord('title', 'Overview', 'clt');?></div>
<div class="change"><?php echo Lang::getWord('action', 'Change', 'clt');?></div>
<div id="clt-note" class="text"></div>
</div>
<?php
/// End of the Note

/// Private records
PageBookClt::putRecordsBegin('Private information', 'private');
PageBookClt::putRecordText('phone', 'Phone number');
PageBookClt::putRecordText('address', 'Address');
PageBookClt::putRecordText('city', 'City');
PageBookClt::putRecordText('region', 'Region');
PageBookClt::putRecordList('country', 'Country');
PageBookClt::putRecordText('post_code', 'Post code');
PageBookClt::putRecordsEnd();

?>
<?php /// End of the Records ?>

</article>

<article id="art-bookings" class="noselect<?php if (Base::mode() == 'bookings') echo ' active';?>">

<?php
echo "<div class='caption'>" . htmlspecialchars(Lang::getWord('caption', 'Booking history', 'bookings')) . "</div>\n";

echo "<table class='data layout'>\n";
echo "<colgroup><col width='5%'/><col width='5%'/><col width='5%'/><col width='30%'/><col width='40%'/><col width='10%'/><col width='5%'/></colgroup>\n";
echo "<thead><tr>\n";
echo "<th>" . '#' . "</th>\n";
echo "<th>" . Lang::getPageWord('title', 'Date') . "</th>\n";
echo "<th>" . Lang::getPageWord('title', 'Time') . "</th>\n";
echo "<th>" . Lang::getPageWord('title', 'Centre') . "</th>\n";
echo "<th>" . Lang::getPageWord('title', 'Service') . "</th>\n";
echo "<th>" . Lang::getPageWord('title', 'Duration') . "</th>\n";
echo "<th>" . Lang::getPageWord('title', 'Amount') . "</th>\n";
echo "</tr></thead>\n";
echo "<tbody>\n";
if (Base::mode() == 'bookings')
{
 $min = ' ' . Lang::getWord('time', 'min');
 $bookings = PageBookClt::getActiveBookings();
 foreach ($bookings as $b)
 {
  echo "<tr>\n";
  echo "<td class='right'><a class=\"ax\" href=\"pay/?ref=" . $b['id'] . "\">" . $b['id'] . "</a></td>\n";
  echo "<td class='center'>" . $b['date'] . "</td>\n";
  echo "<td class='center'>" . $b['time'] . "</td>\n";
  echo "<td><a class=\"ax\" href=\"ctr-" . $b['ctr'] . "/\">" . htmlspecialchars($b['ctrT']) . "</a></td>\n";
  echo "<td><a class=\"ax\" href=\"srv-" . $b['srv'] . "/\">" . htmlspecialchars($b['srvT']) . "</a></td>\n";
  echo "<td class='right'>" . $b['dura'] . $min . "</td>\n";
  echo "<td class='right'>" . htmlspecialchars($b['total']) . "</td>\n";
  echo "</tr>\n";
 }
}
echo "</tbody></table>"
?>

</article>

<article id="art-biz" class="noselect<?php if (Base::mode() == 'biz') echo ' active';?>">

<div class="slide"><img src="pic/biz/slide1.png"/>
<div class="content">
<h1 class="shadow"><?php
echo htmlspecialchars(Lang::getWord('slide1a', 'Grow your business', 'biz'));
echo "<br/>\n";
echo htmlspecialchars(Lang::getWord('slide1b', 'with Wellclubs', 'biz'));
?></h1>
<h3 class="shadow"><?php
echo Lang::getText('slide1', 'Join the marketplace', 'biz');
/*echo htmlspecialchars(Lang::getWord('slide1c', 'Join the fastest growing online marketplace', 'biz'));
echo "<br/>\n";
echo htmlspecialchars(Lang::getWord('slide1d', 'for wellness and beauty and start transforming the way you do business!', 'biz'));*/
?></h3>
<?php
if (!WClient::id())
{
 echo '<div class="signup"><a id="button-listed" href="#" class="js button main ui-state-error">';
 echo htmlspecialchars(Lang::getWord('button', 'Signup and get listed free', 'biz'));
 echo '</a></div>';
}
else if (!WClient::me()->isMember())
{
 echo '<div class="signup"><a id="button-listed" href="#" class="js button main ui-state-error">';
 echo htmlspecialchars(Lang::getWord('button', 'Get listed free', 'biz'));
 echo '</a></div>';
}
?>
</div>
</div>

<div class="slide"><img src="pic/biz/slide2.png"/>
<div class="content">
<h2><?php
echo htmlspecialchars(Lang::getWord('caption', 'Why Wellclubs?', 'biz'));
?></h2>
<?php
echo Lang::getText('slide2', 'Wellclubs is a tool', 'biz');
?>

</div>
</div>

<div class="slide"><img src="pic/biz/slide3.png"/>
<div class="content">
<h2><?php
echo htmlspecialchars(Lang::getWord('title', 'HOW IT WORKS', 'biz'));
?></h2>

<table class="layout main">

<tr>
<td>
<img src="pic/biz/icon-notepad.png">
</td>
<td>
<img src="pic/biz/icon-calendar.png">
</td>
<td>
<img src="pic/biz/icon-money.png">
</td>
</tr>

<tr>
<td><br><?php echo Lang::getText('slide3', 'Col. 1: Begin', 'biz'); ?></td>
<td><br><?php echo Lang::getText('slide3', 'Col. 2: Begin', 'biz'); ?></td>
<td><br><?php echo Lang::getText('slide3', 'Col. 3: Begin', 'biz'); ?></td>
</tr>

<tr>
<td>
<br>
<?php
echo Lang::getText('slide3', 'Col. 1: Center', 'biz');
?>
</td>
<td>
<br>
<?php
echo Lang::getText('slide3', 'Col. 2: Center', 'biz');
?>
</td>
<td>
<br>
<?php
echo Lang::getText('slide3', 'Col. 3: Center', 'biz');
?>
</td>
</tr>

<tr>
<td><?php echo Lang::getText('slide3', 'Col. 1: End', 'biz'); ?></td>
<td><?php echo Lang::getText('slide3', 'Col. 2: End', 'biz'); ?></td>
<td><?php echo Lang::getText('slide3', 'Col. 3: End', 'biz'); ?></td>
</tr>

<tr>
<td><br><a class="js" href="#"><?php echo Lang::getWord('link', 'Request more info', 'biz'); ?></a></td>
<td><br><a class="js" href="#"><?php echo Lang::getWord('link', 'Request more info', 'biz'); ?></a></td>
<td><br><a class="js" href="#"><?php echo Lang::getWord('link', 'Request more info', 'biz'); ?></a></td>
</tr>

</table>

</div>
</div>

</article>

<article id="art-about" class="noselect<?php if (Base::mode() == 'about') echo ' active';?>">

<?php
echo "<div class='topics'>\n";

/// Sidebar
echo "<div class='menu'>\n";
echo "&nbsp;\n";
echo "</div>\n";
/// End of the sidebar

echo "<div class='content'>\n";
echo "<div class='caption'>" . htmlspecialchars(Lang::getWord('caption', 'Thank you for using Wellclubs!', 'about')) . "</div>\n";

echo "<div class='topic about'>\n";
echo "<div class='title'>" . htmlspecialchars(Lang::getWord('title', 'About us', 'about')) . "</div>\n";
echo "<div class='text'>\n";
$textAbout = Lang::getText('topic', '1. About us', 'about');
if (!$textAbout)
{
 $textAbout =
   "<b>Wellclubs</b> is a new and fast growing company in the on-line world of health, beauty and wellness.\n" .
   "It is your local guide to answer all “where” and “what” questions about health and beauty.\n" .
   "<br><br>\n" .
   "Discover nearest health spas, beauty salons and beauty clinics, latest news on beauty treatments,\n" .
   "wellbeing tips and more! All in one place, so you can book your favorite treatment online,\n" .
   "from a mobile phone or tablet. What can be easier?\n" .
   "<br><br>\n" .
   "We are a small team in a large world of beauty, always looking for talented professionals.\n" .
   "If you are one of those, email us your CV on jobs@wellclubs.com.\n";
 Lang::setText($textAbout, 'topic', '1. About us', 'about');
}
echo $textAbout;
echo "</div>\n"; //  class='text'
echo "</div>\n"; //  class='topic about'

echo "<div class='topic contact'>\n";
echo "<div class='title'>" . htmlspecialchars(Lang::getWord('title', 'Company information', 'about')) . "</div>\n";
echo "<div class='text'>\n";
//echo Lang::getText('topic', '2. Company information', 'about');
$textComp = Lang::getText('topic', '2. Company information', 'about');
if (!$textComp)
{
 $textComp =
   "Our office is registered as Wellclubs FZ-LLC, License Number 92917, Dubai Internet City, Dubai, United Arab Emirates.\n";
 Lang::setText($textComp, 'topic', '2. Company information', 'about');
}
echo $textComp;
echo "</div>\n"; //  class='text'
echo "</div>\n"; //  class='topic contact'

echo "<div class='stat'>\n";
echo "<table class='layout'><tr>\n";

echo "<td width='33%'><div class='frame'>\n";
echo "<div class='icon ui-state-error'><img src='pic/about/icon-ss.png'></div>\n";
echo "<div class='title'>" . htmlspecialchars(Lang::getWord('icon1text', 'Salons & Spas', 'about')) . "</div>\n";
echo "<div class='value'>" . number_format(round(DB::getDB()->queryField('com_centre', 'count(*)', 'hidden is null') - 50, -2), 0, '.', ',') . "+</div>\n";
echo "</div></td>\n";

echo "<td width='33%'><div class='frame'>\n";
echo "<div class='icon ui-state-error'><img src='pic/about/icon-tr.png'></div>\n";
echo "<div class='title'>" . htmlspecialchars(Lang::getWord('icon2text', 'Treatments', 'about')) . "</div>\n";
echo "<div class='value'>" . number_format(round(DB::getDB()->queryField(WService::TABLE_SRV, 'count(*)', 'centre_id in (select id from com_centre where hidden is null)')-50,-2), 0, '.', ',') . "+</div>\n";
echo "</div></td>\n";

echo "<td width='33%'><div class='frame'>\n";
echo "<div class='icon ui-state-error'><img src='pic/about/icon-ru.png'></div>\n";
echo "<div class='title'>" . htmlspecialchars(Lang::getWord('icon3text', 'Registered users', 'about')) . "</div>\n";
echo "<div class='value'>" . number_format(round(DB::getDB()->queryField('biz_client', 'count(*)', 'visited is not null') - 5, -1), 0, '.', ',') . "+</div>\n";
echo "</div></td>\n";

echo "</tr></table>\n";
echo "</div>\n"; // class='stat'

echo "</div>\n"; // class='content'
echo "</div>\n"; // class='topics'

//echo "\n";
//echo "<div class=''>" . '' . "</div>\n";
?>

</article>

<article id="art-faq" class="noselect<?php if (Base::mode() == 'faq') echo ' active';?>">

<?php
echo "<div class='topics'>\n";

/// Sidebar
echo "<div class='menu'><ul>\n";
if (Base::mode() == 'faq')
{
 $faqs = PageBook::getFAQs();
 foreach ($faqs as $faq)
 {
  $id = $faq['id'];
  $title = htmlspecialchars($faq['title']);
  $faq['title'] = $title;
  echo "<li><a class='ax' href='fag/#faq-$id'>$title</a></li>\n";
 }
}
echo "</ul></div>\n";
/// End of the sidebar

echo "<div class='content'>\n";
echo "<div class='caption'>" . Lang::getWord('caption', 'Frequently asked questions', 'faq') . "</div>\n";

echo "<div class='data'>\n";
if (Base::mode() == 'faq')
{
 foreach ($faqs as $faq)
 {
  $id = $faq['id'];
  $title = $faq['title'];
  echo "<div class='topic'>\n";
  echo "<div class='title'><a name='faq-$id'></a>$title</div>\n";
  echo "<div class='text'>\n";
  echo Util::strHTML($faq['reply']);
  echo "</div>\n"; //  class='text'
  echo "</div>\n"; //  class='topic'
 }
}
echo "</div>\n"; // class='data'

echo "</div>\n"; // class='content'
echo "</div>\n"; // class='topics'
?>

</article>

<article id="art-policy" class="noselect<?php if (Base::mode() == 'policy') echo ' active';?>">

<div class="topics">

<?php

$topics = array
(
 'terms' => 'Terms and Conditions',
 'booking' => 'Booking Terms and Conditions',
 'privacy' => 'Privacy and Cookie Policy'
);
foreach ($topics as $topic => $title)
 $topics[$topic] = htmlspecialchars(Lang::getWord('title', $title, 'policy'));

/// Sidebar
echo "<div class='menu'><ul>\n";
foreach ($topics as $topic => $title)
 echo "<li><a class='ax' href='policy/#policy-$topic'>$title</a></li>\n";
echo "</ul></div>\n";
/// End of the sidebar

//echo "\n";
//echo "<div class=''>" . '' . "</div>\n";
//echo "<div class=''>\n";
//echo "</div>\n"; // class=''
echo "<div class='content'>\n";

foreach ($topics as $topic => $title)
{
 echo "<div class='topic $topic'>\n";
 echo "<div class='title'><a name='policy-$topic'></a>$title</div>\n";
 echo "<div class='text'>\n";
 if (Base::mode() == 'policy')
  echo PageBook::getPolicyTopicText($topic);
 echo "</div>\n"; // class='text'
 echo "</div>\n"; // class='topic'
}

echo "</div>\n"; // class='content'
echo "</div>\n"; // class='topics'
?>
</article>

</section>

<footer>

<table class="layout"><tr><td style="width:35%">

<div class='rd-footer-block1'>
<div class='rd-padding'><a class="ax flink" href="about/"><?php
echo htmlspecialchars(Lang::getPageWord('flink', 'The company'));
?></a></div>
<div class='rd-padding'><a class="ax flink" href="policy/#policy-terms"><?php
echo htmlspecialchars(Lang::getPageWord('flink', 'Terms and conditions'));
?></a></div>
<div class='rd-padding'><a class="ax flink" href="policy/#policy-privacy"><?php
echo htmlspecialchars(Lang::getPageWord('flink', 'Privacy and Cookie Policy'));
?></a></div>
</div>

<div style='min-width:25px;width:10%;float:left;' class='rd-hide-at-999px'>&nbsp</div>

<div style='float:left;'>
<div class='rd-padding'><a class="ax flink" href="faq/"><?php
echo htmlspecialchars(Lang::getPageWord('flink', 'FAQ'));
?></a></div>
<div class='rd-padding'><a class="ax flink" href="about/#about-contact"><?php
echo htmlspecialchars(Lang::getPageWord('flink', 'Contact us'));
?></a></div>
</div>

</td><td>

<table style="margin:auto">
<tr><td><span class="ftext"><?php
echo htmlspecialchars(Lang::getPageWord('ftext', 'Powered by'));
?>:</td></tr>
<tr><td>
<a href="http://www.visa.com" title="VISA" target="_blank"><img width="62" height="38" src="pic/pay-visa.png" alt="VISA" /></a>
<a href="https://www.mastercard.com" title ="MasterCard" target="_blank"><img width="61" height="38" src="pic/pay-mcard.png" alt="MasterCard" /></a>
</td></tr>
</table>
</td><td class='rd-padding-0-at-670px' style='float:right;'>

<div style='float:right;'>

<table style='float:right;'>
<tr><td><span class="ftext"><?php
echo htmlspecialchars(Lang::getPageWord('ftext', 'Follow us'));
?>:</td></tr>
<tr><td>

<div style='float:left;padding-left:4px;'>
<a href="https://www.facebook.com/wellclubs/" title="Facebook" target="_blank"><img width="35" height="35" src="pic/sn/fb.png" alt="Facebook" /></a>
<a href="" title="Google+" target="_blank"><img width="35" height="35" src="pic/sn/gp.png" alt="Google+" /></a>
</div>

<div style='float:left;padding-left:4px;'>
<a href="https://instagram.com/well_clubs/" title="Instagram" target="_blank"><img width="35" height="35" src="pic/sn/ig.png" alt="Instagram" /></a>
<a href="https://www.pinterest.com/wellclubs/" title="Pinterest" target="_blank"><img width="35" height="35" src="pic/sn/pi.png" alt="Pinterest" /></a>
</div>

</td></tr>
</table>

</div>
</td></tr></table>


<table class='layout'>
<tr><td style='text-align: center'>
 <div class="copy"><?php
echo 'Copyright &copy; 2015 Wellclubs. ';
echo htmlspecialchars(Lang::getPageWord('ftext', 'All rights reserved'));
?>.</div>
</td></tr>
</table>


<table class="layout"><tr><td><?php /*
<!--a href="//www.pinterest.com/pin/create/button/?url=http%3A%2F%2Fwww.flickr.com%2Fphotos%2Fkentbrew%2F6851755809%2F&media=http%3A%2F%2Ffarm8.staticflickr.com%2F7027%2F6851755809_df5b2051c9_z.jpg&description=Next%20stop%3A%20Pinterest"
   data-pin-do="buttonPin" data-pin-config="above" data-pin-color="red" data-pin-height="28">
 <img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_28.png" /></a-->
<!--a href="//www.pinterest.com/pin/create/button/"
   data-pin-do="buttonBookmark" data-pin-shape="round" data-pin-height="32">
 <img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_round_red_32.png" /></a-->
<!--script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script-->
<!--a href="https://plus.google.com/116878953193535469302" rel="publisher">Мы в Google+</a-->
*/ ?></td><td width="100">
<?php
if (strlen(WDomain::gaId())) //if (fnmatch('*ag.dyndns.dk*', $_SERVER['HTTP_HOST']))
{
// Google analytics begin
?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
<?php
echo "ga('create','" . WDomain::gaId() . "','auto');\n";
echo "ga('send','pageview');\n";
//echo "console.log('GA pageview sent: '+location.href);";
if (WClient::id())
 echo "ga('set','&uid'," . WClient::id() . ");\n";
?>
</script>
<?php
// Google analytics end
}

if (WDomain::name() == 'ag.dyndns.dk')
{
// Yandex.Metrika informer
?>

<a href="https://metrika.yandex.ru/stat/?id=27693450&amp;from=informer"
target="_blank" rel="nofollow"><img src="//bs.yandex.ru/informer/27693450/3_1_FFFFFFFF_EFEFEFFF_0_pageviews"
style="width:88px; height:31px; border:0;" alt="Yandex.Metrika"
onclick="try{Ya.Metrika.informer({i:this,id:27693450,lang:'<?php echo Lang::current(); ?>'});return false}catch(e){}"/></a>

<?php // Yandex.Metrika counter ?>
<script type="text/javascript">
(function(d,w,c,k)
{
 (w[c]=w[c]||[]).push(function()
 {
  try
  {
   w['yaCounter'+k]=new Ya.Metrika({id:k,clickmap:true,trackLinks:true,accurateTrackBounce:true});
  } catch(e) { }
 });

 var n=d.getElementsByTagName("script")[0],s=d.createElement("script"),f=function(){n.parentNode.insertBefore(s,n);};
 s.type="text/javascript";
 s.async=true;
 s.src=d.location.protocol+"//mc.yandex.ru/metrika/watch.js";

 if (w.opera=="[object Opera]")
  d.addEventListener("DOMContentLoaded",f,false);
 else
  f();
})(document,window,"yandex_metrika_callbacks",27693450);
</script>
<!-- /Yandex.Metrika counter -->
<?php
}
?>

</td></tr>
</table>

</footer>

<?php
if (WDomain::tawk())
{
// Google analytics begin
?>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/569f89e787faab542687398d/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
<?php
}
?>

<script type="text/javascript">
app.txt=
{
 button_signup:'<?php echo Lang::getPageWord('button','Signup');?>'
,button_upload:'<?php echo Lang::getPageWord('button','Upload');?>'
,button_delete:'<?php echo Lang::getPageWord('button','Delete');?>'
,topw_title_topw_login:'<?php echo Lang::getPageWord('title','Log in to Wellclubs');?>'
,topw_title_topw_login_listed:'<?php echo Lang::getPageWord('title','Please login in or sign up before adding your business');?>'
,all_offers:'<?php echo Lang::getSiteWord('js','all offers');?>'
,list_more:'<?php echo Lang::getPageWord('text','show more >');?>'
,server_error:'<?php echo Lang::getSiteWord('js','Server error');?>'
,protocol_error:'<?php echo Lang::getSiteWord('js','Protocol error');?>'
,no_title_returned:'<?php echo Lang::getPageWord('js','No title returned from the server');?>'
,no_message_returned:'<?php echo Lang::getPageWord('js','No message returned from the server');?>'
,error_no_fname:'<?php echo Lang::getSiteWord('error','No first name entered');?>'
,error_no_lname:'<?php echo Lang::getSiteWord('error','No last name entered');?>'
,error_no_email:'<?php echo Lang::getSiteWord('error','No email address entered');?>'
,error_no_centre:'<?php echo Lang::getSiteWord('error','No business name entered');?>'
,error_no_addr:'<?php echo Lang::getSiteWord('error','No business address entered');?>'
,error_no_phone:'<?php echo Lang::getSiteWord('error','No business phone number entered');?>'
,error_invalid_email:'<?php echo Lang::getSiteWord('error','Invalid email address');?>'
,error_no_password:'<?php echo Lang::getSiteWord('error','No password entered');?>'
,error_password_differ:'<?php echo Lang::getSiteWord('error','Passwords don\\\'t match');?>'
,error_file_too_large:'<?php echo Lang::getSiteWord('error','File size is too large');?>'
,write_review:'<?php echo Lang::getPageWord('button','Write a review');?>'
,rate_total:'<?php echo $rateTotalTitle;?>'
,rate_ambie:'<?php echo $rateAmbieTitle;?>'
,rate_staff:'<?php echo $rateStaffTitle;?>'
,rate_clean:'<?php echo $rateCleanTitle;?>'
,rate_value:'<?php echo $rateValueTitle;?>'
,review_short:'<?php echo Lang::getPageWord('js','Review text is too short');?>'
,add_comment:'<?php echo Lang::getPageWord('button','Add a comment');?>'
,comment_empty:'<?php echo Lang::getPageWord('js','Comment text is empty');?>'
,comment_short:'<?php echo Lang::getPageWord('js','Comment text is too short');?>'
,add_cavil:'<?php echo Lang::getPageWord('button','Report as inappropriate');?>'
,reason_empty:'<?php echo Lang::getPageWord('js','Reason is not specified');?>'
,cavil_empty:'<?php echo Lang::getPageWord('js','Report text is empty');?>'
,cavil_short:'<?php echo Lang::getPageWord('js','Report text is too short');?>'
,comments:'<?php echo Lang::getPageWord('title','Comments');?>'
,written:'<?php echo Lang::getPageWord('prompt','Written');?>'
,closed:'<?php echo Lang::getPageWord('sched', 'Closed');?>'
,minutes:'<?php echo Lang::getPageWord('time', 'min');?>'
,clear_avatar:'<?php echo Lang::getPageWord('message', 'Delete personal avatar image');?>'
};
<?php
 $bgSize = PageBook::getBgSize();
 echo "app.bg={width:$bgSize[0],height:$bgSize[1]};\n";
 echo "app.acMenuProc=" . PageBook::autocompleteMenuProc('') . ";\n";
 echo "app.acMenuTerr=" . PageBook::autocompleteMenuTerr() . ";\n";
 //echo "/*\n" . print_r($_SERVER, true) . "*/\n";
?>
</script>

</body>
</html>
