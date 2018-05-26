<?php

/*
class XNet
{
 private $href;
}

class XApp
{
 private $id;
 private $key;
 public function id() { return $this->id; }
 public function key() { return $this->key; }
 public function __construct($id, $key)
 {
  $this->$id = $id;
  $this->$key = $key;
 }
}

class XSite
{
 private $apps = array();
 public function __construct($apps)
 {
  $this->apps = $apps;
 }
 public function href($net)
 {
  if (array_key_exists($net, $this->apps))
   return $this->apps[$new]
 }
}
*/

class XTmp
{
 public $srv, $app, $params, $token, $user;
}

/**
 * Description of XAuth
 */
class XAuth
{
 /*private static $sites = array();
 public static function initSites()
 {
  self::$sites['localhost'] = new XSite(array
  (
   'gp' => new XApp('710609543227-819d25elc4lpofoqos81dros92qdlrdl.apps.googleusercontent.com', 'Jom06rfGG6f1z-3uvrY308m6')
  ,'fb' => new XApp('1462827984004540', '24ca324e2978da77a117bf35b603dae5')
  ));
 }*/

 private static $data = array
 (
  'localhost' => array
  (
   'gp' => array // http://ruseller.com/lessons.php?rub=37&id=1668
   ( // https://code.google.com/apis/console/
     'id' => '710609543227-819d25elc4lpofoqos81dros92qdlrdl.apps.googleusercontent.com',
     'key' => 'Jom06rfGG6f1z-3uvrY308m6'
   ),
   'fb' => array // http://ruseller.com/lessons.php?rub=37&id=1670
   ( // https://developers.facebook.com/apps
     'id' => '1462827984004540',
     'key' => '66f35c7bdc809fb305fef23ec2945c55'
   )
  ),
 'ag.dyndns.dk' => array
  (
   'gp' => array
    (
     'id' => '582643543132-dq9n4385t8vh2gk7l5q1umghvdjjj379.apps.googleusercontent.com',
     'key' => 'YdWpLNf6YtQ3Syrja16boUuI'
    ),
   'fb' => array
   (
     'id' => '1531798150366554',
     'key' => '24ca324e2978da77a117bf35b603dae5'
   )
  ),
 'wellclubs.ru' => array
  (
   'gp' => array
    (
     'id' => '458322040228-9eb1htsg059urvhh4pj350rgo7cgb3dr.apps.googleusercontent.com',
     'key' => 'JbwWL_AcU-6D3tMBfwgTZxxn'
    ),
   'fb' => array
   (
     'id' => '770691822972484',
     'key' => '074c7aa2cc706868352e703bd4b75705'
   )
  ),
 'wellclubs.com' => array
  (
   'gp' => array
    (
     'id' => '819031925987-hmmj30cb76rbjuu4c5ekq7b9upj03576.apps.googleusercontent.com',
     'key' => '7fVBTL0XcKTDw0x1w806gThu'
    ),
   'fb' => array
   (
     'id' => '264175330373361',
     'key' => 'dcb8b5453cb7c073ecc2d05dbf13e3c4'
   )
  ),
 'wellclubs.co.uk' => array
  (
   'gp' => array
    (
     'id' => '671048755741-4cgqr7iookih0q66f3fninc7l6kubqd4.apps.googleusercontent.com',
     'key' => 'K_8htZEBxSOK6T7OcsHOMUfc'
    ),
   'fb' => array
   (
     'id' => '367497893419922',
     'key' => '5b7467fb872f480e08de9bf6cf8295db'
   )
  )
 );

 private static function host()
 {
  return str_replace('www.', '', Base::host());
 }


 private static function app($net)
 {
  $host = self::host();
  if (!array_key_exists($host, self::$data))
   return null;
  $site = self::$data[$host];
  return array_key_exists($net, $site) ? $site[$net] : null;
 }

 const GP_SCOPE =
   'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';

 /// name - Title for screen button
 /// post - use curl for url1
 /// grant - use grant_type in params
 /// scope - scope for href
 /// url0 - URL for href
 /// url1 - URL for token
 /// url2 - URL for user
 private static $srv = array
 (
  'gp' => array
  (
   'name' => 'Google+',
   'post' => true,
   'grant' => true,
   'parse' => false,
   'scope' => self::GP_SCOPE,
   'url0' => 'https://accounts.google.com/o/oauth2/auth',
   'url1' => 'https://accounts.google.com/o/oauth2/token',
   'url2' => 'https://www.googleapis.com/oauth2/v1/userinfo'
  ),
  'fb' => array
  (
   'name' => 'Facebook',
   'post' => false,
   'grant' => false,
   'parse' => true,
   'scope' => 'email,user_birthday',
   'url0' => 'https://www.facebook.com/dialog/oauth',
   'url1' => 'https://graph.facebook.com/oauth/access_token',
   'url2' => 'https://graph.facebook.com/me'
  ),
  'vk' => array
  (
   'name' => 'VKontakte',
   'post' => false,
   'grant' => false,
   'parse' => false,
   'url0' => 'https://oauth.vk.com/authorize',
   'url1' => 'https://oauth.vk.com/access_token',
   'url2' => 'https://api.vk.com/method/users.get'
  ),
  'od' => array
  (
   'name' => 'Odnoklassniki',
   'post' => true,
   'grant' => true,
   'parse' => false,
   'url0' => 'http://www.odnoklassniki.ru/oauth/authorize',
   'url1' => 'http://api.odnoklassniki.ru/oauth/token.do',
   'url2' => 'http://api.odnoklassniki.ru/fb.do'
  )
 );

 private static function srv($net)
 {
  return Util::item(self::$srv, $net);
 }

 public static function name($net)
 {
  return array_key_exists($net, self::$srv) ? self::$srv[$net]['name'] : 'NONAME';
 }

 public static function uri($net)
 {
  return WDomain::pro() . self::host() . Base::home() . 'auth?net=' . $net;
 }

 public static function href($net)
 {
  $srv = self::srv($net);
  $app = self::app($net);
  if (!$srv || !$app)
   return null;
  $params = array
  (
   'client_id'     => $app['id'],
   'redirect_uri'  => self::uri($net),
   'response_type' => 'code'
  );
  if (array_key_exists('scope', $srv))
   $params['scope'] = $srv['scope'];
  return $srv['url0'] . '?' . urldecode(http_build_query($params));
 }

 private static $tmp;

 public static function info($net)
 {
  self::$tmp = new XTmp();
  self::$tmp->net = $net;
  self::$tmp->srv = self::srv($net);
  if (!self::$tmp->srv)
   exit('Unsupported social network');
  self::$tmp->app = self::app($net);
  if (!self::$tmp->app)
   exit('Application is not specified');
  self::params();
  //echo JSON::encode(self::$tmp);
  self::token();
  self::user();
  //exit("/*\n" . print_r(self::$tmp->user, true) .  "*/\n");
  $result = array();
  switch (self::$tmp->net)
  {
  case 'gp' :
   $result['email'] = Util::item(self::$tmp->user, 'email');
   //$name = Util::item(self::$tmp->user, 'name');
   $result['firstname'] = Util::item(self::$tmp->user, 'given_name');
   $result['lastname'] = Util::item(self::$tmp->user, 'family_name');
   $result['picture'] = Util::item(self::$tmp->user, 'picture');
   break;
  case 'fb' :
   $result['email'] = Util::item(self::$tmp->user, 'email');
   $result['firstname'] = Util::item(self::$tmp->user, 'first_name');
   $result['lastname'] = Util::item(self::$tmp->user, 'last_name');
   $result['picture'] = 'http://graph.facebook.com/' . Util::item(self::$tmp->user, 'id') . '/picture';
   break;
  case 'vk' :
   break;
  case 'od' :
   break;
  }
  return $result;
 }

 private static function params()
 {
  self::$tmp->params = array(
    'client_id'     => self::$tmp->app['id'],
    'client_secret' => self::$tmp->app['key'],
    'redirect_uri'  => self::uri(self::$tmp->net),
    'code'          => HTTP::param('code')
  );
  if (self::$tmp->srv['grant'])
   self::$tmp->params['grant_type'] = 'authorization_code';
 }

 private static function token()
 {
  $file = HTTP::file(self::$tmp->srv['url1'], self::$tmp->params, self::$tmp->srv['post']);
  if (self::$tmp->srv['parse'])
   parse_str($file, self::$tmp->token);
  else
   self::$tmp->token = json_decode($file, true);
  if (!isset(self::$tmp->token['access_token']))
   exit('Error retrieving an access token: ' . print_r(self::$tmp->token, true));
 }

 private static function user()
 {
  switch (self::$tmp->net)
  {
  case 'gp' :
   self::$tmp->params['access_token'] = self::$tmp->token['access_token'];
   break;
  case 'fb' :
   self::$tmp->params = array
   (
     'access_token' => self::$tmp->token['access_token']
   );
   break;
  case 'vk' :
   self::$tmp->params = array
   (
     'uids'         => self::$tmp->token['user_id'],
     'access_token' => self::$tmp->token['access_token'],
     'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big'
   );
   break;
  case 'od' :
   $sign =
     md5("application_key=" . self::$tmp->srv['pub'] . "format=jsonmethod=users.getCurrentUser" .
     md5(self::$tmp->token['access_token'] . self::$tmp->srv['key']));
   self::$tmp->params = array
   (
     'method'          => 'users.getCurrentUser',
     'access_token'    => self::$tmp->token['access_token'],
     'application_key' => self::$tmp->srv['pub'],
     'format'          => 'json',
     'sig'             => $sign
   );
   break;
  }
  self::$tmp->user = json_decode(HTTP::file(self::$tmp->srv['url2'], self::$tmp->params), true);
 }
}

/*
Google:
(
    [id] =&gt; 116878953193535469302
    [email] =&gt; gavriliuk@gmail.com
    [verified_email] =&gt; 1
    [name] =&gt; Alexander Gavriliuk
    [given_name] =&gt; Alexander
    [family_name] =&gt; Gavriliuk
    [link] =&gt; https://plus.google.com/116878953193535469302
    [picture] =&gt; https://lh3.googleusercontent.com/-dvkFr6NYK60/AAAAAAAAAAI/AAAAAAAAAAA/rYNpJ9Qgix8/photo.jpg
    [locale] =&gt; ru
)

http://lh3.googleusercontent.com/-dvkFr6NYK60/AAAAAAAAAAI/AAAAAAAAAAA/rYNpJ9Qgix8/photo.jpg

*/

/*
Facebook:
(
    [id] =&gt; 839465639398537
    [birthday] =&gt; 04/24/1968
    [email] =&gt; gavriliuk@gmail.com
    [first_name] =&gt; Alexander
    [gender] =&gt; male
    [last_name] =&gt; Gavriliuk
    [link] =&gt; https://www.facebook.com/app_scoped_user_id/839465639398537/
    [locale] =&gt; ru_RU
    [name] =&gt; Alexander Gavriliuk
    [timezone] =&gt; 3
    [updated_time] =&gt; 2014-08-22T18:15:31+0000
    [verified] =&gt; 1
)

The picture is under http://graph.facebook.com/<user_id>/picture
http://graph.facebook.com/839465639398537/picture

*/

?>
