<?php

/**
 * Description of WDomain
 */
class WDomain {
 const TABLE_DOMAIN = 'biz_domain';

 //const DEF_ID = 1;
 //const DEF_NAME = 'wellclubs.ru';

 private $id = null;
 private $name = null;
 private $test = null;
 private $local = null;
 private $abcId = null;
 private $currencyId = null;
 private $currencyCode = null;
 private $firstDay = null;
 private $ssl = null;
 private $gaId = null;
 private $tawk = null;

 public function __toString() { return $this->name; }

 public function __construct($id = null, $name = '')
 {
  if ($id !== null)
  {
   $this->id = $id;
   $this->name = $name;
  }
  else
  {
   $host = Base::host();
   if (substr($host, 0, 4) == 'www.')
    $host = substr($host, 4);
   //exit($host . ' Wait a minute...');
   //
   $row = DB::getDB()->queryPairs(self::TABLE_DOMAIN, null, 'name=' . DB::str($host));
   //exit(DB::lastQuery() . '<br>Wait a minute...');
   if ($row)
   {
    //exit($host . ' - host is recognized<br>domain ID: ' . $row['id'] . '<br>Wait a minute...');
    $this->id = $row['id'];
    $this->name = $row['name'];
    $this->local = (array_search($this->name, array('localhost', '127.0.0.1')) !== false);
    $this->test = $this->local || (substr(Base::home(), 0, 6) == '/test/');
    $this->abcId = $row['abc_id'];
    $this->currencyId = $row['currency_id'];
    $this->firstDay = intval($row['first_day']);
    $this->ssl = ($row['use_ssl'] == '1');
    $this->gaId = $row['ga_id'];
    $this->tawk = ($row['use_tawk'] == '1');
   }
   else
   {
    //exit($host . ' - unknown host<br>' . DB::lastQuery() . '<br>Wait a minute...');
    $this->name = Base::host();
    $this->abcId = Lang::SYS;
    $this->currencyId = 'USD';
    $this->firstDay = 1;
   }
   $this->currencyCode = WCurrency::getCode($this->currencyId);
  }
 }

 private static $current = null;

 public static function current()
 {
  if (!self::$current)
  {
   self::$current = new WDomain();
  }
  return self::$current;
 }

 public static function id() { return self::current()->id; }
 public static function name() { return self::current()->name; }
 public static function test() { return self::current()->test; }
 public static function local() { return self::current()->local; }
 public static function abcId() { return self::current()->abcId; }
 public static function currencyId() { return self::current()->currencyId; }
 public static function currencyCode() { return self::current()->currencyCode; }
 public static function currencyCodeSafe() { return Util::nvl(self::current()->currencyCode, self::current()->currencyId); }
 public static function firstDay() { return self::current()->firstDay; }
 public static function ssl() { return self::current()->ssl; }
 public static function gaId() { return self::current()->gaId; }
 public static function tawk() { return self::current()->tawk; }

 public static function ok() { return self::current()->id !== null; }

 public static function pro() { return self::current()->ssl ? 'https://' : 'http://'; }

 public static function filter($alias = null)
 {
  $result = '';
  if (self::ok())
  {
   $result = ' and ';
   if ($alias != null)
    $result .= $alias . '.';
   $result .= 'domain_id=' . self::id();
  }
  return $result;
 }
}
