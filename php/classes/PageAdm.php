<?php

class PageAdm extends Page
{
 const COOKIE_KEY = 'wca';
 const TIMEOUT = 3600;

 private static $instance;
 /**
  * Get PageAdm singleton instance
  * @return PageAdm singleton instance
  */
 public static function instance() { return self::$instance; }

 private static $title;
 public static function title() { return self::$title; }

 const DEF_MODE = 'start';

 public function getModes() { return $this->modes; }
 public function getDefaultMode() { return self::DEF_MODE; }

 public function __construct()
 {
  self::$instance = $this;

  $this->modes = array
  (
   self::DEF_MODE => 'Start',
   'clts' => 'Clients',
   'mbrs' => 'Members',
   'msgs' => 'Messages',
   'bnds' => 'Brands',
   'ctrs' => 'Centres',
   'bngs' => 'Bookings',
   'pmts' => 'Payments',
   'cmps' => 'Campaigns',
   'ofrs' => 'Offers',
   'tops' => 'Top 10',
   'mbgs' => 'Backgrounds',
   'hmenu' => 'Menu',
   'sgrps' => 'Social groups',
   'mcats' => 'Categories',
   'mprcs' => 'Procedures',
   'mters' => 'Territories',
   'mterms' => 'Metro',
   'roles' => 'Roles',
   'titles' => 'Titles',
   'texts' => 'Texts',
   'faqs' => 'FAQ',
   'langs' => 'Languages',
   'dmns' => 'Domains',
   'themes' => 'Themes',
   'loc' => 'TestGAPI'
  );

  if (defined('ADM_SHOW_TEST'))
   $this->modes['test'] = 'Test';

  $this->indexes = array
  (
   'clt' => 'Client',
   'msg' => 'Message',
   'mbr' => 'Member',
   'bnd' => 'Brand',
   'ctr' => 'Centre',
   'ctrd' => 'Schemes of the Centre',
   'ctrf' => 'Staff of the Centre',
   'ctrr' => 'Resources of the Centre',
   'ctrm' => 'Menu of the Centre',
   'ctrv' => 'Reviews on the Centre',
   'srv' => 'Service',
   'text' => 'Text',
   'faq' => 'FAQ'
  );
 }

 public function validateMode($mode)
 {
  return array_key_exists($mode, $this->modes);
 }

 public function validateModeWithIndex($mode)
 {
  return array_key_exists($mode, $this->indexes);
 }

 private static function testAuthCookie()
 {
  if (!array_key_exists(self::COOKIE_KEY, $_POST))
   return;
  $wca = $_POST[self::COOKIE_KEY];
  if (($wca == '/') && WDomain::local())
   $wca = ADMIN_USERNAME . '/' . ADMIN_PASSWORD;
  $values = explode('/', $wca);
  if (is_array($values) && (count($values) == 2))
  {
   $saved_display_errors = ini_get('display_errors');
   ini_set('display_errors', '0');
   $db = new DB($values[0], $values[1]);
   //$db = new mysqli(null, $values[0], $values[1], 'wellclub_db');
   ini_set('display_errors', $saved_display_errors);
   if (!$db->connect_errno)
    setcookie(self::COOKIE_KEY, base64_encode($wca), time() + self::TIMEOUT, Base::home() . 'adm/');
  }
  //header('Location: ' . Base::home() . Base::path());
  header('Location: ' . $_SERVER['REQUEST_URI']);
  exit;
 }

 private static function getAuthDigit($x, $y)
 {
  return intval($x / 200) + intval($y / 200) * 3 + 1;
 }

 private static function showAuthForm()
 {
?>
<div style="margin:0 auto;padding-top:120px;width:210px;">
<form method="post">
<input type="hidden" name="wca" value="aaa/bbb" />
<input id="f1"
 onkeydown="if((event.which==13)&&this.value)with(document.all.f2){select();focus();}"
 style="display:block;margin:0 auto;width:200px;" />
<input id="f2" type="password"
 style="display:block;margin:0 auto;width:200px;" />
<input type="submit"
 onclick="var f1=document.all.f1.value;var f2=document.all.f2.value;if(!f1||!f2)return false;wca.value=f1+'/'+f2;"
 style="display:block;margin:0 auto;width:100px;" />
</form>
</div>
<?php
 }

 private static function testAuthParams()
 {
  if (!array_key_exists('data', $_GET))
   return;
  $data = explode(',', $_GET['data']);
  if (!is_array($data) || (count($data) != 8))
   return;
  $result = '';
  for ($i = 0; $i < 8; $i += 2)
   $result .= self::getAuthDigit($data[$i], $data[$i + 1]);
  if ($result == ADM_PIN_CODE)
   self::showAuthForm();
  exit;
 }

 private function showPageAuth($message = null)
 {
  self::testAuthCookie();
  self::testAuthParams();
?><!doctype html>
<html><head>
<title>Think twice<?php if ($message) echo ': ' . $message; ?></title>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow">
<base href="<?php echo Base::bas(); ?>" />
<script>
var data=[];
a=function(e)
{
 if(!e||e.button)
  return;
 data.push(e.offsetX);
 data.push(e.offsetY);
 if(data.length!=8)
  return;
 a=null;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+((''+document.location).indexOf('?')>0?'&':'?')+'data='+data.join(','),false);
 req.send(null);
 if (req.status!=200)
  return;
 document.getElementById('stop').innerHTML=req.responseText;
 document.all.f1.focus();
}
</script>
</head><body style="text-align:center">
<div id="stop" style="width:600px;height:600px;margin:0 auto;background:url(pic/adm/stop.png);"
onclick="a(event)"></div>
</body></html>
<?php
  exit;
 }

 public function showPage()
 {
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Expires: " . date("r"));
  if (!WDomain::local())
  {
   WClient::initCurrent();
   if (WClient::id())
   {
    $mode = Base::mode();
    if (($mode != $this->getDefaultMode()) && !self::checkWorkerMode($mode))
     return self::showPageAuth();
   }
   else
   {
    if (!HTTP::hasCookie(self::COOKIE_KEY))
     return self::showPageAuth();
    $cookie = HTTP::getCookie(self::COOKIE_KEY);
    if (!strlen($cookie))
     return self::showPageAuth('Cookie is empty');
    $wca = base64_decode($cookie);
    if (($wca === false) || !strpos($wca, '/'))
    {
     setcookie(self::COOKIE_KEY, '', 0, Base::home() . 'adm/');
     return self::showPageAuth("Invalid cookie: '$wca'");
    }
    self::setDB(DB::getDB($wca));
    if (!self::db())
     return self::showPageAuth('Error connecting to DB');
    setcookie(self::COOKIE_KEY, $cookie, time() + self::TIMEOUT, Base::home() . 'adm/');
   }
  }
  if (!self::db())
   self::setDB(DB::getAdminDB());
  if (!WClient::id())
   WClient::initAdmin();
  //
  switch (Base::mode())
  {
   case 'clts' :
    return PageAdmClts::showPage();
   case 'mbrs' :
    return PageAdmMbrs::showPage();
   case 'msgs' :
    return PageAdmCltMsgs::showPage();
   case 'bnds' :
    return PageAdmBnds::showPage();
   case 'ctrs' :
    return PageAdmCtrs::showPage();
   case 'bngs' :
    return PageAdmBngs::showPage();
   case 'pmts' :
    return PageAdmPmts::showPage();
   case 'cmps' :
    return PageAdmCmps::showPage();
   case 'ofrs' :
    return PageAdmOfrs::showPage();
   case 'tops' :
    return PageAdmTops::showPage();
   case 'mbgs' :
    return PageAdmMenuBgs::showPage();
   case 'hmenu' :
    return PageAdmHmenu::showPage();
   case 'sgrps' :
    return PageAdmSgrps::showPage();
   case 'mcats' :
    return PageAdmMenuCats::showPage();
   case 'mprcs' :
    return PageAdmMenuPrcs::showPage();
   case 'mters' :
    return PageAdmMenuTers::showPage();
   case 'mterms' :
    return PageAdmMenuTersMetro::showPage();
   case 'roles' :
    return PageAdmRoles::showPage();
   case 'titles' :
    return PageAdmTitles::showPage();
   case 'texts' :
    return PageAdmTexts::showPage();
   case 'text' :
    return PageAdmText::showPage();
   case 'faqs' :
    return PageAdmFAQs::showPage();
   case 'faq' :
    return PageAdmFAQ::showPage();
   case 'langs' :
    return PageAdmLang::showPage();
   case 'dmns' :
    return PageAdmDmns::showPage();
   case 'themes' :
    return PageAdmThemes::showPage();
   case 'clt' :
    return PageAdmClt::showPage();
   case 'msg' :
    return PageAdmCltMsg::showPage();
   case 'mbr' :
    return PageAdmMbr::showPage();
   case 'bnd' :
    return PageAdmBnd::showPage();
   case 'ctr' :
    return PageAdmCtr::showPage();
   case 'ctrd' :
    return PageAdmCtrDisc::showPage();
   case 'ctrf' :
    return PageAdmCtrEmp::showPage();
   case 'ctrr' :
    return PageAdmCtrRes::showPage();
   case 'ctrm' :
    return PageAdmCtrMenu::showPage();
   case 'ctrv' :
    return PageAdmCtrReview::showPage();
   case 'srv' :
    return PageAdmSrv::showPage();
   case 'loc' :
    return PageAdmLoc::showPage();
   case 'test' :
    return PageAdmZero::showPage();
  }
  return self::showPageStart();
 }

 public static function checkWorkerMode($mode, $clientId = null)
 {
  if (!$clientId)
   $clientId = WClient::id();
  if (!fnmatch('*s', $mode) && (array_search($mode, array('hmenu', 'loc', 'test')) === false))
  {
   if (fnmatch('ctr*', $mode))
    $mode = 'ctrs';
   else
    $mode .= 's';
  }
  $where = "client_id=$clientId and mode='$mode'";
  if (DB::getAdminDB()->queryField('biz_client_work', 'count(*)', $where) == '1')
   return true;
  return false;
 }

 public static function checkWorkerLang($lang, $clientId = null)
 {
  if (!$clientId)
   $clientId = WClient::id();
  $where = "client_id=$clientId and abc_id='$lang'";
  if (DB::getAdminDB()->queryField('biz_client_work_lang', 'count(*)', $where) == '1')
   return true;
  return false;
 }

 private static function makeIndexedTitle($title)
 {
  $db = array
  (
   'clt' => 'biz_client',
   'mbr' => 'biz_client',
   'bnd' => 'com_brand',
   'ctr' => 'com_centre',
   'ctrf' => 'com_centre',
   'ctrr' => 'com_centre',
   'ctrm' => 'com_centre',
   'srv' => WService::TABLE_SRV,
   'faq' => 'art_faq'
  );
  if (array_key_exists(Base::mode(), $db))
  {
   $table = $db[Base::mode()];
   $field = ($table == 'biz_client') ? 'trim(concat(firstname,\' \',lastname))' : 'name';
   $name = DB::getDB()->queryField($table, $field, 'id=' . Base::index());
   if (strlen($name))
    $title .= ", \"$name\"";
  }
  else if (Base::mode() == 'msg')
  {
   $row = DB::getDB()->queryPairs('biz_mail', 'client_id,dir,subject', 'id=' . Base::index());
   if ($row)
   {
    $subject = $row['subject'];
    $title .= ", \"$subject\"";
   }
  }
  else if (Base::mode() == 'text')
  {
   $row = DB::getDB()->queryPairs('biz_text', 'page,mode,kind,name', 'id=' . Base::index());
   if ($row)
   {
    $title .= ", ";
    $page = $row['page'];
    if (strlen($page))
     $title .= "\"$page\":";
    $mode = $row['mode'];
    if (strlen($mode))
     $title .= "\"$mode\":";
    $kind = $row['kind'];
    if (strlen($kind))
     $title .= "\"$kind\":";
    $name = $row['name'];
    $title .= "\"$name\"";
    if (Lang::used())
    {
     $lang = HTTP::get('lang');
     if ($lang)
     {
      $langs = Lang::map();
      $title .= ' - "' . $langs[$lang]->title() . '"';
     }
    }
   }
  }
  return $title;
 }

 public function showTitle()
 {
  $mode = Base::mode();
  if (array_key_exists($mode, $this->modes))
   self::$title = $this->modes[$mode];
  else if (array_key_exists($mode, $this->indexes))
   self::$title = self::makeIndexedTitle($this->indexes[$mode] . ' ' . Base::index());
  else
   self::$title = 'Undefined mode "' . $mode . '"';
  self::$title = htmlspecialchars(self::$title);
?><title>WC Admin Panel <?php echo ': ' . self::$title; ?></title>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow">
<base href="<?php echo Base::bas() /*. Base::langPath()*/; ?>adm/" />
<style>
ul.menu { list-style:none; }
ul.menu li { float:left;padding:0 5px }
hr { clear:both; }
.menu-links { margin-bottom:5px;border-bottom:1px solid #ddd;overflow:hidden; }
.menu-links th { padding:5px 0; }
.menu-links td { padding:5px 10px; }
.menu-links td.selected { background:#ddd; }
h1 { text-align:center;font-size:1.4em;font-weight:bold;color:darkcyan; }
h1.error { color:red; }
table { border:0;padding:0;border-collapse:collapse; }
td { padding:0; }
table.main caption { font-size:1.2em;font-weight:bold;color:green; }
table.main tr.hidden { background:#fee; }
table.main th, table.main td { border:1px solid;vertical-align:top; }
table.main th { background:#eee }
table.main th.lang { text-align:left;padding:0 5px;white-space:nowrap; }
table.main .left { padding-left:5px;text-align:left; }
table.main .right { padding-right:5px;text-align:right; }
table.main th a { display:block; }
table.main a.b { display:block; }
table.main:not(.text) b { display:block;text-align:right; }
table.main:not(.text) i { display:block;text-align:left; }
table.main:not(.text) u { display:block;text-align:center;text-decoration:none; }
table.main.text ol[type="1"]>li { list-style-type:decimal; }
table.main.text ol[type="0"]>li { list-style-type:decimal-leading-zero; }
table.main.text ol[type="a"]>li { list-style-type:lower-alpha; }
table.main.text ol[type="aa"]>li { list-style-type:upper-alpha; }
table.main.text ol[type="i"]>li { list-style-type:lower-roman; }
table.main.text ol[type="ii"]>li { list-style-type:upper-roman; }
table.main.text ol[type="g"]>li { list-style-type:lower-greek; }
table.main.text ol[type="ar"]>li { list-style-type:armenian; }
table.main.text ol[type="gr"]>li { list-style-type:georgian; }
th.left { text-align:left; }
.center { text-align:center; }
th[onclick],td[onclick] { cursor:pointer; }
td.checked { background:#888; }
span.size { display:block;white-space:nowrap;font-size:0.6em; }
input[type=button] { margin:0;padding:0 5px; }
input[type=file] { margin:0; }
.small { font-size:0.8em; }
.pagenav { margin:5px;padding:5px;background:#ddd; }
.pagenav .item { margin:5px;padding:2px;background:#fff; }
.pagenav span.item { font-weight:bold; }
.memo { font-size:0.8em; }
.memo .view { padding:2px;overflow:scroll; }
.memo .edit { display:none; }
.memo textarea { display:block;width:99.5%;margin:0;border:0;padding:2px;font-family:inherit; }
.memo b,.memo i,.memo u { display:inline; }
.memo .buttons { float:right; }
</style>
<script>
function decodeHTML(text)
{
 text=text.replace(new RegExp("&apos;","gi"),"'");
 text=text.replace(new RegExp("&quot;","gi"),'"');
 text=text.replace(new RegExp("&gt;","gi"),">");
 text=text.replace(new RegExp("&lt;","gi"),"<");
 text=text.replace(new RegExp("&amp;","gi"),"&");
 return text;
}
function el(id)
{
 return document.getElementById(id);
}
A={};
A.makeURI=function(params)
{
 var uri=''+document.location;
 return uri+(uri.indexOf('?')>0?'&':'?')+params;
}
A.inputEntityId=function(entity,parent,min)
{
 if(typeof(min)=='undefined')
  min=1;
 var id=prompt('Input '+entity+' id'+(parent?(' for '+parent):''));
 if((id===null)||(id===''))
  return id;
 if((id==(1*id))&&(id>=min))
  return id;
 alert('Invalid '+entity+' id value: "'+id+'" (id must be a number)');
 return false;
}
A.createEntity=function(entity,act,params,noname)
{
 if(noname&&!confirm('Create a new '+entity+'?'))
  return;
 var name=noname?'':prompt('Input a new '+entity+' name');
 if(!noname&&(name==null))
  return;
 if((noname===false)&&!name.trim().length)
  return;
 if(!act)
  act='createEntity';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+(noname?'':'&name='+name)+(params?('&'+params):'')),false);
 req.send(null);
 var error='Error adding the '+entity+(noname?'':' "'+name+'"')+' on the server'
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
A.deleteEntity=function(id,entity,act,rowid)
{
 if(!confirm('Delete the '+entity+' '+id+'?'))
  return false;
 if(!act)
  act='deleteEntity';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+'&id='+id),false);
 req.send(null);
 var error='Error deleting the '+entity+' '+id+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 if(rowid)
  return el(rowid).parentNode.removeChild(el(rowid))||true;
 document.location.reload(true);
 return false;
}
A.hideEntity=function(button,id,hide,entity,act)
{
 if(!confirm((hide?'Hide':'Show')+' the '+entity+' '+id+'?'))
  return;
 if(!act)
  act='hideEntity';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+'&id='+id+'&hide='+(hide?'1':'')),false);
 req.send(null);
 var error='Error '+(hide?'':'un')+'hiding the '+entity+' '+id+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 if(!button)
  return document.location.reload(true);
 button.parentNode.parentNode.className=hide?'hidden':'';
 button.value=hide?'Show':'Hide';
 var fn=window[act]?act:'A.hideEntity';
 button.onclick=eval('(function onclick(){'+fn+'(this,'+id+','+(!hide)+',"'+entity+'","'+act+'")})');
}
A.changeField=function(node,id,entity,field,act,required,reload)
{
 if(id)
  entity+=' '+id;
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new '+field+' for the '+entity+':',oldValue);
 if(field=='abc_id')
  newValue=newValue.toLowerCase();
 else if(field=='currency_id')
  newValue=newValue.toUpperCase();
 if((newValue===null)||(required&&(newValue===''))||(newValue==oldValue))
  return;
 var params='act='+(act?act:'changeField')+((id!=null)?'&id='+id:'')+(act?'&'+field+'=':'&field='+field+'&value=')+newValue;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI(params),false);
 req.send(null);
 var error='Error changing the '+entity+' '+field+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 if(reload)
  document.location.reload(true);
 else
  node.innerHTML=newValue;
 return true;
}
A.changeSerial=function(node,id,entity,act)
{
 if(!act)
  act='changeSerial';
 return A.changeField(node,id,entity,'serial',act,false,true);
}
A.changeName=function(node,id,entity,act,optional)
{
 if(!act)
  act='changeName';
 return A.changeField(node,id,entity,'name',act,!optional);
}
A.changeEmail=function(node,id,entity,act)
{
 if(!act)
  act='changeEmail';
 return A.changeField(node,id,entity,'email',act);
}
A.changeTitle=function(node,id,lang,entity,act)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new title for the '+entity+' '+id+', language "'+lang+'":',oldValue);
 if((newValue===null)||(newValue==oldValue))
  return;
 if(!act)
  act='changeTitle';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+(id?('&id='+id):'')+'&lang='+lang+'&title='+encodeURIComponent(newValue)),false);
 req.send(null);
 var error='Error storing the '+entity+' '+id+' new title on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.innerHTML=newValue;
}
A.changeFlag=function(node,id,entity,field,act,title)
{
 id=(id===null?"":""+id);
 var oldValue=(node.className.split(' ').indexOf('checked')>=0);
 var F='the '+(title||field)+' flag';
 var S=entity+(id.length?(' '+id):'');
 var text=oldValue?('Reset '+F+' for '+S):('Set '+F+' for '+S);
 if(!confirm(text+'?'))
  return;
 if(!act)
  act='changeFlag';
 var value=oldValue?'':'1';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+(id.length?('&id='+id):'')+'&field='+field+'&value='+value),false);
 req.send(null);
 var error='Error changing '+F+' for '+S+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 node.className=value?'checked':'';
 return true;
}
A.changeItem=function(node,id,entity,prop,act,itemsStr,itemsObj,options)
{
 var text=node?node.innerHTML:'';
 var oldValue='';
 if(text.length)
  for(var key in itemsObj)
   if(text==itemsObj[key])
   {
    oldValue=key;
    break;
   }
 var newValue=prompt('Input a new '+prop+' for '+entity+' '+id+' ('+itemsStr+'):',oldValue);
 if((newValue===null)||(newValue==oldValue))
  return;
 if(!newValue.length&&('def' in options))
  newValue=options.def;
 if(newValue.length&&!(newValue in itemsObj))
  return alert('Invalid value');
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+'&id='+id+'&value='+newValue),false);
 req.send(null);
 var error='Error changing the '+entity+' '+prop+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 if(!node||options.reload)
  document.location.reload(true);
 else
  node.innerHTML=(newValue in itemsObj)?itemsObj[newValue]:'';
}
A.editText=function(id)
{
 el(id+'-view').style.display='none';
 el(id+'-edit').style.display='block';
 el(id+'-text').focus();
}
A.viewText=function(id)
{
 el(id+'-edit').style.display='none';
 el(id+'-view').style.display='block';
}
A.changeText=function(id,entity,act,field,params)
{
 var text=encodeURIComponent(el(id+'-text').value);
 field=field?field:'text';
 act=act?act:'changeText';
 params=params?('&'+params):'';
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI('act='+act+params+'&'+field+'='+text),false);
 req.send(null);
 var error='Error saving '+entity+' on the server';
 if (req.status!=200)
  return alert(error);
 if (req.responseText!='OK')
  return alert(error+': '+req.responseText);
 document.location.reload(true);
}
</script>
<?php
 }

 public function showBodyTop()
 {
  echo "<body style='font-family:arial;font-size:16px;'>\n";
  echo '<h3><ul class="menu">';
  foreach ($this->modes as $mode => $title) // Base::home()
  {
   if (($mode == self::DEF_MODE) || WClient::me()->isHoster() || self::checkWorkerMode($mode))
   {
    echo '<li>' .
      (($mode == Base::mode()) ? "<b>$title</b>" : "<a href='$mode/'>$title</a>") .
      "</li>\n";
   }
  }
  echo "</ul><br/><hr/></h3>\n";
  $errors = Base::errors();
  if (count($errors))
  {
   echo '<div style="color:red">';
   foreach ($errors as $text)
    echo "<h4>$text</h4>";
   echo '</div>';
  }
 }

 private function showPageStart()
 {
  if (array_key_exists('act', $_POST) && ($_POST['act'] == 'logout'))
  {
   setcookie(self::COOKIE_KEY, null, 0, Base::home() . 'adm/');
   header('Location: .');
   exit;
  }
?><!doctype html>
<html>
<head>
<?php $this->showTitle(); ?>
</head>
<?php $this->showBodyTop(); ?>
<form method="post"><input type="hidden" name="act" value="logout"/></form>
<div style="text-align:center"><img width="836" height="435" src="<?php echo Base::home(); ?>pic/adm/admin.jpg"
 onclick="document.forms[0].submit()"/></div>
</body>
</html>
<?php
  return true;
 }

 public static function makeEntityText($id, $name, $ownerName = null)
 {
  $result = $id;
  if ($name)
  {
   $result .= ', ' . $name;
   if ($ownerName)
    $result .= ' [' . $ownerName . ']';
  }
  return $result;
 }

 public static function echoMenuTableOfLinks($prompt, $links)
 {
  echo "<div class='menu-links'><table><tr>\n";
  echo "<th>$prompt:</th>\n";
  foreach ($links as $link)
  {
   $href = $link[0];
   $title = htmlspecialchars($link[1]);
   $class = $link[2] ? ' class="selected"' : '';
   echo "<td$class><a href='$href'>$title</a></td>\n";
  }
  echo "</tr></table></div>\n";
 }

 public static function echoPageNav($table, $where, $limit)
 {
  $offset = Util::intval(HTTP::get('offset'));
  $count = DB::getDB()->queryField($table, 'count(*)', $where);
  //echo Base::htmlComment(DB::lastQuery()) . "\n";
  if ($count > $limit)
  {
   $uri = HTTP::uriWithoutParam('offset');
   echo "<div class='pagenav'>\n";
   $done = 0;
   $page = 1;
   while ($done < $count)
   {
    $text = ($done + 1) . '-' . min(array($done + $limit, $count));
    if ($done == $offset)
     echo "<span class='item'>$text</span>";
    else if (!$done)
     echo "<a class='item' href='$uri'>$text</a>";
    else
     echo "<a class='item' href='" . HTTP::addParam($uri, 'offset', $done) . "'>$text</a>";
    $done += $limit;
    $page++;
   }
   echo "\n</div>\n"; //class='pagenav'
  }
  return $offset;
 }

 /**
  * Echo an editable memo text block
  * @param string $id Prefix for 'edit', 'view' and 'text' ids
  * @param string $text Editable text value
  * @param string $entity Entity name
  * @param string $act HTTP parameter 'act' value (default: 'changeText')
  * @param string $field HTTP parameter name (default: 'text')
  * @param string $params HTTP parameters
  * @param int $rows Number of rows (default: 10)
  */
 public static function echoTextArea($id, $text, $entity, $act = null, $field = null, $params = null, $rows = null)
 {
  $text = htmlspecialchars($text);
  $rows = Util::dbl2str(((is_int($rows) && ($rows > 0)) ? $rows : 10) * 1.25);
  echo "<div class='memo' onclick='A.editText(\"$id\")'>\n";
  echo "<div id='$id-view' class='view' style='height:{$rows}em;'>\n" . str_replace("\n", "<br/>\n", $text) . "\n</div>\n";
  echo "<div id='$id-edit' class='edit'><textarea id='$id-text' style='height:{$rows}em;' onkeydown='if(event.keyCode==27)A.viewText(\"$id\")'\n";
  echo ">$text</textarea><div class='buttons'>\n";
  echo "<input type='button' value='Save' onclick='event.stopPropagation();A.changeText(\"$id\",\"$entity\",\"$act\",\"$field\",\"$params\")'/>\n";
  echo "<input type='button' value='Cancel' onclick='event.stopPropagation();A.viewText(\"$id\")'/>\n";
  echo "</div></div></div>\n";
 }

 /**
  * Create the entity record in the database table
  * @param string $table The database table name
  * @param string $entity The entity type name
  * @param string $name The new entity name
  * @param array $values Predefined field values
  * @param array $filter The entity name definition area
  * @param array $extra Array of extra (additional) parameters ('noname')
  */
 public static function createEntity($table, $entity, $name = null, $values = null, $filter = null, $extra = null)
 {
  $result = 0;
  $noname = $extra ? !!Util::item($extra, 'noname') : false;
  $noserial = $extra ? !!Util::item($extra, 'noserial') : false;
  if (!$noname && !$name)
   $name = HTTP::param('name');
  $where = $noname ? '' : "name=" . DB::str($name);
  if (!$values)
   $values = array();
  if ($filter == null)
   $filter = $values;
  $record = null;
  if (!$noname && array_search($table, array('com_centre')) === false) // Skip name uniqueness
  {
   $where = $filter;
   $where['name'] = DB::str($name);
   $record = self::db()->queryFields($table, 'id,name', $where);
  }
  if ($record)
   echo "Error creating $entity '$name': this name is already used for another $entity";
  else
  {
   $id = self::db()->queryField($table, 'ifnull(max(id),0)+1');
   $values['id'] = $id;
   //if (array_search($table, array('biz_domain', 'com_brand', 'com_centre_schema_interval')) === false) // Skip serial inserting
   if (!$noserial) // Skip serial inserting
    $values['serial'] = $id;
   if (!$noname)
    $values['name'] = DB::str($name);
   self::db()->insertValues($table, $values);
   if (self::db()->affected_rows != 1)
    echo "Error adding the $entity $id record to the database: " . DB::lastQuery();
   else
   {
    echo 'OK';
    $result = $id;
   }
  }
  return $result;
 }

 public static function deleteEntity($table, $entity, $id = null)
 {
  if (!$id)
   $id = intval(HTTP::param('id'));
  $where = "id=$id";
  PageAdm::db()->deleteRecords($table, $where);
  $query = DB::lastQuery();
  if (PageAdm::db()->queryField($table, 'count(*)', $where))
   echo "Error deleting the $entity $id record from the database: " . $query;
  else
   echo 'OK';
 }

 public static function hideEntity($table, $entity, $id = null)
 {
  if (!$id)
   $id = intval(HTTP::param('id'));
  $hide = HTTP::param('hide');
  $field = 'hidden';
  $where = "id=$id";
  PageAdm::db()->modifyFields($table, array($field => (($hide == '1') ? "'1'" : 'null')), $where);
  if (PageAdm::db()->queryField($table, $field, $where) == $hide)
   echo 'OK';
  else
   echo "Error " . ($hide ? '' : 'un') . "hiding the $entity $id: " . DB::lastQuery();
 }

 /**
  * Common change table field from the HTTP parameter
  * @param string $table Database table name
  * @param string $entity User-friendly changing object name
  * @param string $field Database table field name
  * @param string $param HTTP parameter name
  * @param boolean $silent Do not send 'OK' string in case of success
  */
 public static function changeField($table, $entity, $field = null, $param = null, $silent = null)
 {
  if (!$field)
  {
   $field = HTTP::param('field');
   $param = 'value';
  }
  else if (!$param)
   $param = $field;
  $id = intval(HTTP::param('id'));
  $value = HTTP::param($param);
  $key = ($table == WMember::TABLE_MEMBER) ? 'client_id' : 'id';
  $where = $key . '=' . $id;
  self::db()->modifyFields($table, array($field => (strlen($value) ? DB::str($value) : 'null')), $where);
  $sql = DB::lastQuery();
  $realValue = self::db()->queryField($table, $field, $where);
  if ($realValue != $value)
  {
   echo "Error changing $entity $id $field to '$value': result is $realValue.<br>\n" . $sql;
   return false;
  }
  if (!$silent)
   echo 'OK';
  return true;
 }

 public static function changeSerial($table, $entity)
 {
  return self::changeField($table, $entity, 'serial');
 }

 public static function changeName($table, $entity, $filter = null)
 {
  $id = intval(HTTP::param('id'));
  $name = HTTP::param('name');
  if (array_search($table, array(WCentre::TABLE_CENTRE)) === false) // Skip name uniqueness
  {
   $where = "id<>$id and name=" . DB::str($name);
   if ($filter)
    $where = '(' . $filter . ') and ' . $where;
   if (intval(self::db()->queryField($table, 'count(*)', $where)))
   {
    echo "Error changing $entity $id name to '$name': this name is already used for another $entity";
    return false;
   }
  }
  $where = "id=$id";
  self::db()->modifyField($table, 'name', 's', $name, $where);
  if (self::db()->queryField($table, 'name', $where) != $name)
  {
   echo "Error changing $entity $id name to '$name': " . DB::lastQuery();
   return false;
  }
  echo 'OK';
  return true;
 }

 public static function changeEmail($table, $entity)
 {
  self::changeField($table, $entity, 'email');
 }

 public static function changeTitle($table, $field, $entity, $id = null)
 {
  if (!$id)
   $id = intval(HTTP::param('id'));
  $lang = HTTP::param('lang');
  $title = HTTP::param('title');
  if (Lang::setDBValue($title, $table . '_abc', null, array($field => $id), $lang))
   echo 'OK';
  else
   echo "Error changing $entity $id language '$lang' title to '$title': " . DB::lastQuery();
 }

 public static function changeFlag($table, $entity, $field = null, $value = null)
 {
  if ($field === null)
   $field = HTTP::param('field');
  if ($value === null)
   $value = HTTP::param('value', '');
  $id = intval(HTTP::param('id'));
  $where = "id=$id";
  self::db()->modifyFields($table, array($field => (($value == '1') ? "'1'" : 'null')), $where);
  if (self::db()->queryField($table, $field, $where) == $value)
   echo 'OK';
  else
   echo "Error changing the $entity $id record in the database";
 }

 /**
  * Store multi-line text from HTTP parameter to a database field
  * @param string $table Database table name
  * @param assoc_array $where Database predicate pairs
  * @param string $entity Entity name
  * @param string $field Database table field name (default: 'text')
  * @param string $param HTTP parameter name (default: same as $field)
  */
 public static function changeText($table, $where, $entity, $field = null, $param = null)
 {
  if (!$field)
   $field = 'text';
  if (!$param)
   $param = $field;
  $text = HTTP::param($param);
  if (!self::db()->mergeFields($table, array($field => DB::str($text)), $where))
   echo "Error saving $entity to the database: " . DB::lastQuery();
  else
   echo 'OK';
 }

 /**
  * Process the result of an action executing function
  * If success then count($result) == 1 and $result[0] is the result
  * Else count($result) == 2 and $result[0] is the error type, $result[1] is the message
  * @param array $result array of 1 or 2 values
  * @return mixed $result[0] if success, else false
  */
 public static function processResult($result)
 {
  if (is_array($result))
  {
   if (count($result) == 1)
    echo ($result[0] === true) ? 'OK' : $result[0];
   else if (count($result) == 2)
   {
    echo $result[1];
    if ($result[0] == 'faildb')
     echo "\n" . DB::lastQuery();
   }
   else
    echo 'Invalid count($result): ' . count($result);
  }
  else
   echo 'Invalid type of $result (not an array):' . "\n" . print_r($result, true);
 }

}

?>