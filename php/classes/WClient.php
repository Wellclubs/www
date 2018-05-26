<?php

class WClient
{
 const COOKIE_KEY = 'wcu';
 const COOKIE_LONG = 'long';
 const TIMEOUT = 31536000; // 60 * 60 * 24 * 365;
 const TIMEOUT_SIGNUP = 86400000; // 1000 * 60 * 60 * 24;
 const TIMEOUT_RESTORE = 3600000; // 1000 * 60 * 60;


 const TABLE_CLIENT = 'biz_client';

 const PASS_CASE_SIGNIN = 0;
 const PASS_CASE_RESTORE = 1;
 const PASS_CASE_MASTER = 2;

 const IMAGE_SIZE = 48;
 const PHOTO_SIZE = 128;

 const MSG_CLIENT_REGISTERED = 'Client registered';
 const MSG_USE_HASH_LINK = 'Click on the activation link sent to your email address and login to the website';
 const MSG_CHECK_EMAIL = 'Check the E-mail';
 //const MSG_PASSWORD_SENT = 'Use the password from the message sent to your address';

 const ERROR_NO_LOGIN = 'Client is not logged in';
 const ERROR_DUP_EMAIL = 'Client with this E-mail address is already registered';
 const ERROR_PASSWORD = 'Invalid combination of E-mail and password';
 const ERROR_LOCKED = 'Account is locked, contact the administration';
 const ERROR_UNKNOWN = 'Unknown server error';

 private $id = null;
 private $title = null;
 private $lastname = null;
 private $firstname = null;
 private $pass = null;
 private $email = null;
 private $phone = null;
 private $address = null;
 private $city = null;
 private $region = null;
 private $countryId = null;
 private $postCode = null;
 private $created = null;
 private $gender = null;
 private $byear = null;
 private $bmon = null;
 private $bday = null;
 private $note = null;
 private $file = null;
 private $visited = null;

 private $isLocked = null;
 private $isWorker = null;

 private $isHoster = null;
 private $isMaster = null;
 private $isMember = null;
 private $member = null;

 public function getId() { return $this->id; }
 public function getTitle() { return $this->title; }
 public function getLastName() { return $this->lastname; }
 public function getFirstName() { return $this->firstname; }
 public function getPass() { return $this->pass; }
 public function getEmail() { return $this->email; }
 public function getPhone() { return $this->phone; }
 public function getAddress() { return $this->address; }
 public function getCity() { return $this->city; }
 public function getRegion() { return $this->region; }
 public function getCountryId() { return $this->countryId; }
 public function getCountryName() { return WCountry::getTitle($this->countryId); }
 public function getPostCode() { return $this->postCode; }
 public function getCreated() { return $this->created; }
 public function getGender() { return $this->gender; }
 public function getBYear() { return $this->byear; }
 public function getBMon() { return $this->bmon; }
 public function getBDay() { return $this->bday; }
 public function getNote() { return $this->note; }
 public function getFile() { return $this->file; }
 public function getVisited() { return $this->visited; }

 public function isLocked() { return $this->isLocked; }
 public function isWorker() { return $this->isWorker; }

 public static function makeName($title, $firstname, $lastname)
 {
  $result = $lastname;
  if (strlen($firstname))
   $result = trim($firstname . ' ' . $result);
  if (strlen($title))
   $result = trim($title . ' ' . $result);
  return $result;
 }

 public function getName()
 {
  return self::makeName($this->title, $this->firstname, $this->lastname);
 }

 public function getMember() { return $this->member; }

 public function __construct($id)
 {
  if ($id > 0)
  {
   $fields = 'title,lastname,firstname,pass,email,phone,address,city,region,country_id,post_code' .
     ',created,gender,byear,bmon,bday,note,file,visited,is_locked,is_worker';
   $values = DB::getDB()->queryPairs(self::TABLE_CLIENT, $fields, 'id=' . $id);
   if (!$values)
    return;
   $this->id = intval($id);
   $this->title = Util::nvl($values['title'], '');
   $this->lastname = $values['lastname'];
   $this->firstname = $values['firstname'];
   $this->pass = $values['pass'];
   $this->email = $values['email'];
   $this->phone = $values['phone'];
   $this->address = $values['address'];
   $this->city = $values['city'];
   $this->region = $values['region'];
   $this->countryId = $values['country_id'];
   $this->postCode = $values['post_code'];
   $this->created = $values['created'];
   $this->gender = $values['gender'];
   $this->byear = $values['byear'];
   $this->bmon = $values['bmon'];
   $this->bday = $values['bday'];
   $this->note = $values['note'];
   $this->file = $values['file'];
   $this->visited = $values['visited'];
   $this->isLocked = $values['is_locked'];
   $this->isWorker = $values['is_worker'];
   if (array_search($this->gender, array('F', 'M')) === false)
    $this->gender = 'N';
   if ($this->isMember())
    $this->member = new WMember($this);
  }
  else
  {
   $this->id = 0;
   $this->isHoster = true;
   $this->lastname = 'Administrator';
  }
 }

 public function isEditable()
 {
  return ($this->id && ($this->id == self::id()) && (HTTP::get('view') != 'public'));
 }

 public function getBirthday()
 {
  $dd = $this->bday ? substr(100 + $this->bday, 1) : null;
  $mm = $this->bmon ? substr(100 + $this->bmon, 1) : null;
  $yyyy = $this->byear ? substr($this->byear, 0) : null;
  if ($dd && $mm && $yyyy)
   return $dd . '/' . $mm . '/' . $yyyy;
  if ($dd && $mm)
   return $dd . '/' . $mm;
  if ($mm && $yyyy)
   return $mm . '/' . $yyyy;
  if ($yyyy)
   return $yyyy;
  return '';
 }

 public static function getGenderText($gender)
 {
  if (!strlen($gender))
   return null;
  return Lang::getPageWord('gender', $gender === 'F' ? 'Female' : ($gender === 'M' ? 'Male' : 'Not specified'));
 }

 public function isMember()
 {
  if ($this->isMember === null)
   $this->isMember = (DB::getDB()->queryField('biz_member', 'client_id', 'client_id=' . $this->id) !== null);
  return $this->isMember;
 }

 public function isHoster()
 {
  return ($this->isHoster === true);
 }

 public function isMaster()
 {
  if ($this->isMaster === null)
   $this->isMaster = (DB::getDB()->queryField('com_master', 'client_id', 'client_id=' . $this->id) !== null);
  return $this->isMaster;
 }

 private static $me = null;
 private static $view = null;
 private static $chgpwd = null;

 public static function me() { return self::$me; }
 public static function view() { return self::$view; }
 public static function chgpwd() { return self::$chgpwd; }

 public static function id() { return self::$me ? self::$me->id : null; }
 public static function name() { return self::$me ? self::$me->getName() : null; }
 public static function email() { return self::$me ? self::$me->email : null; }
 public static function phone() { return self::$me ? self::$me->phone : null; }
 public static function gender() { return self::$me ? self::$me->gender : null; }
 public static function firstname() { return self::$me ? self::$me->firstname : null; }
 public static function lastname() { return self::$me ? self::$me->lastname : null; }

 public static function logged()
 {
  return HTTP::hasCookie(self::COOKIE_KEY);
 }

 public static function initCurrent()
 {
  self::$me = null;
  if (!HTTP::hasCookie(self::COOKIE_KEY))
   return array(/*'result' => 'Fail', */'error' => 'No cookie');
  $cookie = HTTP::cookie(self::COOKIE_KEY);
  if (!strlen($cookie))
   return array(/*'result' => 'Fail', */'failure' => 'Empty cookie');
  $wcu = self::decrypt($cookie);
  if (!isset($wcu) || !strlen($wcu) || !strpos($wcu, '/'))
   return array(/*'result' => 'Fail', */'failure' => 'Invalid cookie');
  $values = explode('/', $wcu);
  if (!is_array($values) || (count($values) != 2))
   return array(/*'result' => 'Fail', */'failure' => 'Incorrect cookie');
  return self::login($values[0], $values[1]);
 }

 public static function initAdmin()
 {
  self::$me = new WClient(0);
 }

 public static function crypt($wcu)
 {
  return $wcu;
 }

 public static function encrypt($wcu)
 {
  return base64_encode(self::crypt($wcu));
 }

 public static function decrypt($wcu)
 {
  return self::crypt(base64_decode($wcu));
 }

 public static function initView($id)
 {
  self::$view = new WClient($id);
  if (self::$view->id)
   return true;
  self::$view = null;
  return false;
 }

 public static function getClientEmail($id = null)
 {
  if ($id == null)
   return trim(self::email());
  return DB::getDB()->queryField(self::TABLE_CLIENT, 'email', 'id=' . $id);
 }

 public static function getClientName($id = null)
 {
  if ($id == null)
   return trim(self::name());
  return DB::getDB()->queryField(self::TABLE_CLIENT,
   "trim(concat(ifnull(title,' '),' ',ifnull(firstname,' '),' ',lastname))", 'id=' . $id);
 }

 public static function getClientNameAndEmail($id = null)
 {
  if ($id == null)
   return trim(self::name() . ' <' . self::email() . '>');
  return DB::getDB()->queryField(self::TABLE_CLIENT,
   'trim(concat(title,\' \',firstname,\' \',lastname,\' <\',email,\'>\'))', 'id=' . $id);
 }

 public static function imageURI($id = null)
 {
  if (!$id)
   $id = self::id();
  if ($id && DB::getDB()->queryField(self::TABLE_CLIENT, '1', 'id=' . $id . ' and pic is not null'))
   return 'img/clt-' . $id . '.png';
  return 'pic/no-client-48.png';
 }

 public static function imageBigURI($id = null)
 {
  if (!$id)
   $id = self::id();
  if ($id && DB::getDB()->queryField(self::TABLE_CLIENT, '1', 'id=' . $id . ' and photo is not null'))
   return 'img/clt-' . $id . '.jpg';
  return 'pic/no-client-128.png';
 }

 public static function downloadImage($id = null, $big = false)
 {
  if (!$id)
  {
   self::initCurrent();
   $id = self::id();
  }
  if (!Base::isIndexNatural($id))
   return false;
  if ($big)
   return DB::getDB()->downloadFile(self::TABLE_CLIENT, 'photo', 'id=' . $id, 'photo_filename', 'photo_mimetype') ||
     Base::downloadFile(file_get_contents(Base::root() . Base::home() . 'pic/no-client-128.png'), 'no-client-128.png', 'image/png');
  return DB::getDB()->downloadFile(self::TABLE_CLIENT, 'pic', 'id=' . $id, 'pic_filename', 'pic_mimetype') ||
     Base::downloadFile(file_get_contents(Base::root() . Base::home() . 'pic/no-client-48.png'), 'no-client-48.png', 'image/png');
 }

 private static function resizeImage($file_in, $file_out, $size)
 {
  $image = new XImage();
  if (!$image->loadFromFile($file_in))
   return null;
  if (($image->height() != $size) && ($image->width() != $size))
   if ($image->height() > $image->width())
   {
    if (!$image->height($size))
     return null;
   }
   else
   {
    if (!$image->width($size))
     return null;
   }
  if (!$image->writeToFile($file_out))
   return null;
  return $image;
 }

 public static function uploadImage($id)
 {
  $table = self::TABLE_CLIENT;
  $where = array('id' => $id);
  $tmp_name1 = $_FILES['image']['tmp_name'];
  $tmp_name2 = $tmp_name1 . 'x';
  $result = true;
  // Resize to 128x128
  if (self::resizeImage($tmp_name1, $tmp_name2, self::PHOTO_SIZE))
  {
   $_FILES['image']['tmp_name'] = $tmp_name2;
   $_FILES['image']['size'] = filesize($tmp_name2);
   $fields = DB::uploadFields('photo', 'photo_filename', 'photo_mimetype', 'photo_width', 'photo_height', 'photo_size');
   $result &= DB::getAdminDB()->uploadFile('image', $table, $fields, $where);
   unlink($tmp_name2);
  }
  // Resize to 48x48
  if (self::resizeImage($tmp_name1, $tmp_name2, self::IMAGE_SIZE))
  {
   $_FILES['image']['tmp_name'] = $tmp_name2;
   $_FILES['image']['size'] = filesize($tmp_name2);
   $fields = DB::uploadFields('pic', 'pic_filename', 'pic_mimetype', 'pic_width', 'pic_height', 'pic_size');
   $result &= DB::getAdminDB()->uploadFile('image', $table, $fields, $where);
   unlink($tmp_name2);
  }
  return $result;
 }

 public static function uploadImageFromURI($id, $uri)
 {
  $filename = $uri;
  $pos = strpos($filename, '?');
  if ($pos !== false)
   $filename = substr ($filename, 0, $pos);
  $parts = explode('/', $filename);
  if (count($parts) > 1)
   $filename = $parts[count($parts) - 1];
  $tmp_name = tempnam(null, "img");
  $table = self::TABLE_CLIENT;
  $where = array('id' => $id);
  $result = true;
  // Resize to 128x128
  $image1 = self::resizeImage($uri, $tmp_name, self::PHOTO_SIZE);
  if ($image1)
  {
   $size = filesize($tmp_name);
   unlink($tmp_name);
   $data = $image1->getData();
   $mimetype = $image1->mimetype();
   $values = array('photo' => DB::str($data), 'photo_filename' => DB::str($filename), 'photo_mimetype' => DB::str($mimetype),
     'photo_width' => $image1->width(), 'photo_height' => $image1->height(), 'photo_size' => $size);
   $result &= DB::getAdminDB()->mergeFields($table, $values, $where);
  }
  // Resize to 48x48
  $image2 = self::resizeImage($uri, $tmp_name, self::IMAGE_SIZE);
  if ($image2)
  {
   $size = filesize($tmp_name);
   unlink($tmp_name);
   $data = $image2->getData();
   $mimetype = $image2->mimetype();
   $values = array('pic' => DB::str($data), 'pic_filename' => DB::str($filename), 'pic_mimetype' => DB::str($mimetype),
     'pic_width' => $image2->width(), 'pic_height' => $image2->height(), 'pic_size' => $size);
   $result &= DB::getAdminDB()->mergeFields($table, $values, $where);
  }
  return $result;
 }

 public static function clearImage($id)
 {
  return DB::getAdminDB()->modifyFields(self::TABLE_CLIENT,
    array('pic' => 'null', 'pic_filename' => 'null', 'pic_mimetype' => 'null',
        'pic_size' => 'null', 'pic_width' => 'null', 'pic_height' => 'null',
        'photo' => 'null', 'photo_filename' => 'null', 'photo_mimetype' => 'null',
        'photo_size' => 'null', 'photo_width' => 'null', 'photo_height' => 'null'),
    array('id' => $id));
 }

 public static function note()
 {
  return self::$me ? DB::getDB()->queryField(self::TABLE_CLIENT, 'note', 'id=' . self::$me->id) : null;
 }

 public static function setNote($note)
 {
  if (!self::$me)
   return Lang::getPageWord('error', self::ERROR_NO_LOGIN);
  if (!DB::getDB()->modifyField(self::TABLE_CLIENT, 'note', 's', $note, 'id=' . self::$me->id))
   return Lang::getPageWord('error', DB::ERROR_UPDATE) . "\n" . DB::lastQuery();
  return null;
 }

 public static function setEmail($email, $pass)
 {
  $values = array('email' => DB::str($email));
  if (!self::$me)
   return Lang::getPageWord('error', self::ERROR_NO_LOGIN);
  if (DB::getDB()->queryField(self::TABLE_CLIENT, 'id', 'id!=' . self::$me->id . ' and email=' . $values['email']))
   return Lang::getPageWord('error', self::ERROR_DUP_EMAIL);
  if (DB::getDB()->queryField(self::TABLE_CLIENT, 'pass', 'id=' . self::$me->id) != $pass)
   return Lang::getPageWord('error', self::ERROR_PASSWORD);
  if (!DB::getDB()->modifyFields(self::TABLE_CLIENT, $values, 'id=' . self::$me->id))
   return Lang::getPageWord('error', DB::ERROR_UPDATE) . "\n" . DB::lastQuery();
  setCookie($email, $pass);
  return null;
 }

 public static function wcu($email, $pass)
 {
  return self::encrypt($email . '/' . $pass);
 }

 private static function setCookie($email, $pass, $long = false)
 {
  if (!$long)
   $long = HTTP::hasCookie(self::COOKIE_LONG);
  $expire = $long ? time() + self::TIMEOUT : 0;
  HTTP::setCookie(self::COOKIE_KEY, self::wcu($email, $pass), $expire);
  if ($long)
   HTTP::setCookie(self::COOKIE_LONG, '', $expire);
 }

 private static function errorArray($key, $db = false)
 {
  $text = $db ? (': ' . DB::lastQuery()) : '';
  return array('error' => Lang::getPageWord('error', $key) . $text);
 }

 public static function createMember($clientId, $centre, $addr, $phone)
 {
  $db = DB::getAdminDB();

  $email = $db->queryField(WClient::TABLE_CLIENT, 'email', array('id' => $clientId));

  $memberId = $db->queryField(WMember::TABLE_MEMBER, 'client_id', array('client_id' => $clientId));
  if (!$memberId)
  {
   if (!$db->insertValues(WMember::TABLE_MEMBER, array('client_id' => $clientId)))
    return false;//self::error(DB::ERROR_INSERT, true);
   $memberId = $db->queryField(WMember::TABLE_MEMBER, 'client_id', array('client_id' => $clientId));
   if (!$memberId)
    return false;//self::error(DB::ERROR_INSERT, true);
  }

  $brandId = $db->queryField(WBrand::TABLE_BRAND, 'id', 'name=' . DB::str($centre));
  if (!$brandId)
  {
   if (!$db->insertValues(WBrand::TABLE_BRAND, array('member_id' => $memberId, 'name' => DB::str($centre), 'email' => DB::str($email))))
    return false;//self::error(DB::ERROR_INSERT, true);
   $brandId = $db->insert_id;
  }

  $values = array
  (
   'member_id' => $memberId
  ,'brand_id' => $brandId
  ,'type_id' => WCentre::CENTRE_TYPE_SALON
  ,'name' => DB::str($centre)
  ,'email' => DB::str($email)
  ,'serial' => '1'
  ,'hidden' => '1'
  ,'address' => DB::str($addr)
  );
  if (!$db->insertValues(WCentre::TABLE_CENTRE, $values))
   return false;//self::error(DB::ERROR_INSERT, true);
  $centreId = $db->insert_id;

  $values = array
  (
   'centre_id' => $centreId
  ,'serial' => '1'
  ,'phone' => DB::str($phone)
  ,'number' => DB::str(Util::pureNumber($phone))
  );
  if (!$db->insertValues(WCentre::TABLE_CENTRE_PHONE, $values))
   return false;//self::error(DB::ERROR_INSERT, true);

  return true;
 }

 const PASS_MIN_LENGTH = 1; // 8
 const PASS_MIN_DIGITS = 0; // 2
 const PASS_MIN_LETTERS = 0; // 2
 const PASS_MIN_UPPERS = 0;
 const PASS_MIN_LOWERS = 0;
 const PASS_MIN_PUNCTS = 0;

 private static function validatePassword($pass)
 {
  $len = strlen($pass);
  if ($len < self::PASS_MIN_LENGTH)
   return str_replace('#', self::PASS_MIN_LENGTH, Lang::getPageWord('error', 'Your password is too short. You need #+ characters'));
  $digit = 0;
  $upper = 0;
  $lower = 0;
  $punct = 0;
  $other = 0;
  for ($i = 0; $i < $len; ++$i)
  {
   $a = ord(substr($pass, $i, 1));
   if (($a < 32) || ($a > 127))
    ++$other;
   else if (($a >= 48) && ($a <= 57))
    ++$digit;
   else if (($a >= 65) && ($a <= 90))
    ++$upper;
   else if (($a >= 97) && ($a <= 122))
    ++$lower;
   else
    ++$punct;
  }
  if ($digit < self::PASS_MIN_DIGITS)
   return str_replace('#', self::PASS_MIN_DIGITS, Lang::getPageWord('error', 'Your password is weak. You need #+ digits'));
  if (($upper + $lower) < self::PASS_MIN_LETTERS)
   return str_replace('#', self::PASS_MIN_LETTERS, Lang::getPageWord('error', 'Your password is weak. You need #+ letters'));
  if ($upper < self::PASS_MIN_UPPERS)
   return Lang::getPageWord('error', 'The password entered contains too few uppercase characters. The minimum count of uppercase characters is ') . self::PASS_MIN_UPPERS;
  if ($lower < self::PASS_MIN_LOWERS)
   return Lang::getPageWord('error', 'The password entered contains too few lowercase characters. The minimum count of lowercase characters is ') . self::PASS_MIN_LOWERS;
  if ($punct < self::PASS_MIN_PUNCTS)
   return Lang::getPageWord('error', 'The password entered contains too few punctuations. The minimum count of punctuations is ') . self::PASS_MIN_PUNCTS;
  return null;
 }

 /**
  * Create a new client account with data for centre creation in a file field
  * @param string $centre Centre name
  * @param string $addr Centre address
  * @param string $phone Centre phone number
  * @return assoc_array Set of the result values
  */
 public static function listed($centre, $addr, $phone)
 {
  if (!WClient::id())
   return array('result' => 'OK', 'failure' => 'User is not logged in');
  if (!WClient::createMember(WClient::id(), $centre, $addr, $phone))
   return array('result' => 'OK', 'error' => 'Error registering new member');
  if (!self::sendListedNotice(WClient::id(), WClient::name(), WClient::email(), $centre, $addr, $phone))
   return array('result' => 'OK', 'failure' => 'Error sending an email message to a new member');
  return array('result' => 'OK', 'uri' => 'com/');
 }

 /**
  * Finalize the registration of member's business
  * @param type $id
  * @param type $key
  */
 public static function fixup($id, $key)
 {
  $db = DB::getAdminDB();
  $data = $db->queryPairs(self::TABLE_CLIENT, 'file,email,pass', 'id=' . $id . ' and visited is null');
  if (!$data)
   return;
  $file = json_decode($data['file']);
  if (!$file ||
    !array_key_exists('action', $file) || ($file->action != 'signup') ||
    !array_key_exists('key', $file) || ($file->key != $key))
   return;
  if (!array_key_exists('brand', $file) || !array_key_exists('centre', $file) ||
    !array_key_exists('addr', $file) || !array_key_exists('phone', $file))
   return;
  if (!self::createMember($id, $file->brand, $file->centre, $file->addr, $file->phone))
   return;
  self::setCookie($data['email'], $data['pass']);
  $db->modifyFields(self::TABLE_CLIENT, array('file' => 'null'), array('id' => $id));
  header('Location: ' . Base::bas() . 'com/');
  exit;
 }

 /**
  * Create a new client account
  * @param string $firstname Client firstname
  * @param string $lastname Client lastname
  * @param string $email Client E-mail address
  * @param string $pass Client password
  * @param string $href Original url to return after the E-mail confirmation
  * @return assoc_array Set of the result values
  */
 public static function signup($firstname, $lastname, $email, $pass, $href)
 {
  $result = array('result' => 'Fail');
  DB::getAdminDB()->deleteRecords(self::TABLE_CLIENT, 'email=' . DB::str($email) .
   ' and visited is null and (current_timestamp-created)>' . self::TIMEOUT_SIGNUP);
  if (DB::getDB()->queryFields(self::TABLE_CLIENT, 'id', 'email=' . DB::str($email)))
   $result['error'] = Lang::getPageWord('error', self::ERROR_DUP_EMAIL);
  else
  {
   $error = self::validatePassword($pass);
   if ($error)
   {
    $result['error'] = $error;
   }
   else
   {
    $hash = sha1('((' . date('U') .  $email . '))');
    $values = array
    (
     'firstname' => DB::str($firstname),
     'lastname' => DB::str($lastname),
     'email' => DB::str($email),
     'pass' => DB::str($pass),
     'hash' => DB::str($hash)
    );
    if (!DB::getAdminDB()->insertValues(self::TABLE_CLIENT, $values))
     $result['error'] = Lang::getPageWord('error', DB::ERROR_INSERT) . "\n" . DB::lastQuery();
    elseif (!self::sendSignupNotice(DB::getAdminDB()->insert_id, $firstname, $lastname, $email, $href, $hash, null))
     $result['error'] = Lang::getPageWord('error', SMTP::ERROR_SEND);
    else
    {
     $result['message'] = Lang::getPageWord('message', self::MSG_USE_HASH_LINK);
       //'. <a href="http://' . substr($email, strpos('@', $email) + 1) . '" target="_blank">' .
       //Lang::getPageWord('message', self::MSG_CHECK_EMAIL) . '</a>';
     $result['result'] = 'OK';
    }
   }
  }
  return $result;
 }

 /**
  * Restore the client password (send a new one to the specified E-mail address)
  * @param type $email Client E-mail address
  * @param string $href Original url to return after the password change
  * @return assoc_array Set of the result values
  */
 public static function restore($email, $href)
 {
  $result = array('result' => 'Fail');
  $fields = DB::getDB()->queryPairs(self::TABLE_CLIENT, 'id,firstname,lastname,is_locked', "email='$email'");
  if (!isset($fields))
   $result['error'] = Lang::getPageWord('error', 'Client with this E-mail address is not registered');
  elseif ($fields['is_locked'])
   $result['error'] = Lang::getPageWord('error', 'Account is locked, contact the administration');
  else
  {
   $id = $fields['id'];
   $hash = sha1('((' . date('U') .  $email . '))');
   $values = array
   (
    'hash' => DB::str($hash),
    'restored' => 'current_timestamp'
   );
   if (!DB::getAdminDB()->modifyFields(self::TABLE_CLIENT, $values, array('id' => $id)))
    $result['error'] = Lang::getPageWord('error', DB::ERROR_UPDATE);
   //$pass = self::createPassword();
   //if (!DB::getAdminDB()->modifyField(self::TABLE_CLIENT, 'pass', 's', $pass, array('id' => $fields['id'])))
   // $result['error'] = Lang::getPageWord('error', DB::ERROR_UPDATE);
   //elseif (!self::sendPassword($fields['id'], $fields['firstname'], $fields['lastname'], $email, $pass, self::PASS_CASE_RESTORE))
   elseif (!self::sendRestoreNotice($id, $fields['firstname'], $fields['lastname'], $email, $href, $hash))
    $result['error'] = Lang::getPageWord('error', SMTP::ERROR_SEND);
   else
   {
    $result['message'] = Lang::getPageWord('message', self::MSG_USE_HASH_LINK);
      //'. <a href="http://' . substr($email, strpos('@', $email) + 1) . '" target="_blank">' .
      //Lang::getPageWord('message', self::MSG_CHECK_EMAIL) . '</a>';
    $result['result'] = 'OK';
   }
  }
  return $result;
 }

 public static function passwd($old, $pass)
 {
  $result = array('result' => 'Fail');
  if (!self::id())
   $result['error'] = Lang::getPageWord('error', 'Client is not logged in');
  elseif (self::me()->isLocked())
   $result['error'] = Lang::getPageWord('error', 'Account is locked, contact the administration');
  elseif (self::me()->getPass() != $old)
   $result['error'] = Lang::getPageWord('error', 'Invalid current password entered');
  else if (!DB::getAdminDB()->modifyField(self::TABLE_CLIENT, 'pass', 's', $pass, 'id=' . self::id()))
   $result['error'] = Lang::getPageWord('error', DB::ERROR_UPDATE);
  else
  {
   self::setCookie(self::me()->getEmail(), $pass);
   $result['result'] = 'OK';
   $result['message'] = Lang::getPageWord('message', 'Your password has been successfully changed');
  }
  return $result;
 }

 public static function passwdWithHash($pass, $urlhash)
 {
  $result = array('result' => 'Fail');
  DB::getAdminDB()->modifyFields(self::TABLE_CLIENT, array('hash' => 'null', 'restored' => 'null'),
    'hash is not null and restored is not null and (current_timestamp-restored)>' . self::TIMEOUT_RESTORE);
  $fields = DB::getDB()->queryPairs(self::TABLE_CLIENT, 'hash', 'id=' . self::id());
  if (!isset($fields))
   $result['error'] = Lang::getPageWord('error', 'Client is not logged in');
  elseif (self::me()->isLocked())
   $result['error'] = Lang::getPageWord('error', 'Account is locked, contact the administration');
  else
  {
   $hash = $fields['hash'];
   if ($hash != $urlhash)
   {
    $result['result'] = 'OK';
    $result['message'] = Lang::getPageWord('error', 'Operation expired');
   }
   else
   {
    $msg = self::validatePassword($pass);
    if ($msg)
     $result['error'] = $msg;
    else
    {
     $values = array(
      'pass' => DB::str($pass),
      'hash' => 'null',
      'restored' => 'null',
      'visited' => 'current_timestamp'
     );
     if (!DB::getAdminDB()->modifyFields(self::TABLE_CLIENT, $values, 'id=' . self::id()))
      $result['error'] = Lang::getPageWord('error', DB::ERROR_UPDATE);
     else
     {
      self::setCookie(self::me()->getEmail(), $pass, true);
      $result['result'] = 'OK';
      $result['message'] = Lang::getPageWord('message', 'Your password has been successfully changed');
     }
    }
   }
  }
  return $result;
 }

 /**
  * Log a client in
  * @param string $email Client E-mail address
  * @param string $pass Client password
  * @return assoc_array Set of the result values
  */
 public static function login($email, $pass)
 {
  $result = array('result' => 'Fail');
  $where = "email='$email' and pass='$pass'";
  if (Base::page() == 'adm')
   $where .= " and is_worker='1'";
  $values = DB::getDB()->queryPairs(self::TABLE_CLIENT, 'id,is_locked', $where);
  if (!isset($values))
   $result['error'] = Lang::getPageWord('error', self::ERROR_PASSWORD);
  elseif ($values['is_locked'])
   $result['error'] = Lang::getPageWord('error', self::ERROR_LOCKED);
  else
  {
   $newValues = array('hash' => 'null', 'visited' => 'current_timestamp');
   DB::getAdminDB()->modifyFields(self::TABLE_CLIENT, $newValues, array('id' => $values['id']));
   self::setCookie($email, $pass);
   $me = new WClient($values['id']);
   if (!$me->id)
    $result['error'] = self::ERROR_UNKNOWN;
   else
   {
    $result['name'] = $me->getName();
    $result['result'] = 'OK';
    self::$me = $me;
   }
  }
  return $result;
 }

 public static function processHash()
 {
   if (!array_key_exists('hash', $_GET) ||
     !array_key_exists('wcid', $_GET) ||
     !array_key_exists('action', $_GET))
    return false;
   $hash = $_GET['hash'];
   $id = intval($_GET['wcid']);
   if ($id < 1)
    return false;
   $url = Base::loc();
   self::$me = new WClient($id);
   if (!self::$me->id)
   {
    $url = Util::buildUrl(Util::removeUrlParams($url, array('wcid', 'hash', 'action')));
    header('Location: ' . $url);
    return true;
   }
   $action = $_GET['action'];
   if ($action == 'signup')
   {
    $url = Util::buildUrl(Util::removeUrlParams($url, array('wcid', 'hash', 'action')));
    if (!self::signupWithHash($hash))
     return false;
    header('Location: ' . $url);
    return true;
   }
   if ($action == 'restore')
   {
    if (array_key_exists('pass', $_POST))
    {
     $pass = $_POST['pass'];
     $result = self::passwdWithHash($pass, $hash);
     if ($result['result'] == 'OK')
     {
      $url = Util::buildUrl(Util::removeUrlParams($url, array('wcid', 'hash', 'action', 'pass')));
      $result['uri'] = $url;
     }
     exit(json_encode($result));
    }
    $realhash = DB::getDB()->queryField(WClient::TABLE_CLIENT, 'hash', 'id=' . self::me()->id());
    if (!$realhash == $hash)
    {
     $url = Util::buildUrl(Util::removeUrlParams($url, array('wcid', 'hash', 'action')));
     header('Location: ' . $url);
     return true;
    }
    self::$chgpwd = true;
   }
   return false;
 }

 private static function signupWithHash($urlhash)
 {
  DB::getAdminDB()->deleteRecords(self::TABLE_CLIENT, 'visited is null' .
    ' and (current_timestamp-created)>' . self::TIMEOUT_SIGNUP);
  if (!$urlhash)
   return false;
  $fields = 'email,pass,hash,visited,is_locked';
  $values = DB::getAdminDB()->queryPairs(self::TABLE_CLIENT, $fields, 'id=' . self::id());
  if (!isset($values) || $values['is_locked'])
   return false;
  $email = $values['email'];
  $pass = $values['pass'];
  $hash = $values['hash'];
  if (!strlen($pass) || !strlen($hash) || ($hash != $urlhash))
   return false;
  if ($values['visited'])
   return false;
  $newValues = array('visited' => 'current_timestamp', 'hash' => 'null');
  DB::getAdminDB()->modifyFields(self::TABLE_CLIENT, $newValues, 'id=' . self::id());
  self::setCookie($email, $pass);
  return true;
 }

 public static function processCmp()
 {
  $cmp = HTTP::get('cmp');
  // Test $cmp
  if (WDsc::testCmp($cmp))
  {
   $urlWC = Util::buildUrl(Util::removeUrlParam(Base::loc(), 'cmp'));
   HTTP::setCookie('auth_uri', $urlWC);
   HTTP::setCookie('auth_cmp', $cmp);
   $urlFB = XAuth::href('fb');
   //$urlFB = XAuth::href('fb', array('cmp' => $cmp));
   //echo Base::htmlComment($urlFB, true);return true;
   header('Location: ' . $urlFB);
   return true;
  }
  $key = 'auth_cmp_id';
  if (HTTP::hasCookie($key))
  {
   $msg = WDsc::getCmpDesc(intval(HTTP::getCookie($key)));
   if ($msg)
    Base::setTopwMsg($msg);
   HTTP::clearCookie($key);
  }
  return false;
 }

 public static function auth($net)
 {
  $info = XAuth::info($net); /// In case of any error function uses exit("...")
  $email = $info['email'];
  $firstname = $info['firstname'];
  $lastname = $info['lastname'];
  $uri = Util::item($info, 'picture');
  $values = DB::getDB()->queryPairs(self::TABLE_CLIENT, 'id,is_locked,pass,pic_size', "email='$email'");
  if (!isset($values))
  {
   $dba = DB::getAdminDB();
   $pass = self::createPassword();
   $values = array('firstname' => DB::str($firstname), 'lastname' => DB::str($lastname), 'email' => DB::str($email), 'pass' => DB::str($pass), 'visited' => 'current_timestamp');
   if (!$dba->insertValues(self::TABLE_CLIENT, $values))
    exit(Lang::getPageWord('error', DB::ERROR_INSERT) . "\n" . DB::lastQuery());
   $id = $dba->insert_id;
   // Upload avatar picture
   if (!is_null($uri))
    self::uploadImageFromURI($id, $uri);
   // Test auth campaign
   $cmpId = null;
   $cmp = HTTP::getCookie('auth_cmp');
   if ($cmp)
   {
    HTTP::clearCookie('auth_cmp');
    $cmpId = WDsc::testCmp($cmp);
    if ($cmpId)
     HTTP::setCookie('auth_cmp_id', $cmpId);
   }
   if (!self::sendSignupNotice($id, $firstname, $lastname, $email, null, null, $cmpId))
    exit(Lang::getPageWord('error', SMTP::ERROR_SEND));
   //$topwMsg .= WDsc::getCmpDesc($cmpId);
   //echo Base::htmlComment($topwMsg, true);
  }
  elseif ($values['is_locked'])
   exit(Lang::getPageWord('error', self::ERROR_LOCKED));
  else
  {
   $id = $values['id'];
   $pass = $values['pass'];
   if (!$values['pic_size'] && !is_null($uri))
    self::uploadImageFromURI($id, $uri);
  }
  self::setCookie($email, $pass);
  $auth_uri = HTTP::getCookie('auth_uri');
  if ($auth_uri)
   HTTP::clearCookie('auth_uri');
  else
   $auth_uri = Base::bas();
  //exit ($auth_uri . ' ' . $_SERVER['REQUEST_URI'] . print_r($_SERVER, true));
  //if (strpos($auth_uri, '#'))
  // $auth_uri = substr($auth_uri, 0, strpos($auth_uri, '#'));
  $auth_params = HTTP::getCookie('auth_params');
  if ($auth_params)
  {
   HTTP::clearCookie('auth_params');
   $params = json_decode($auth_params, true);
   if ($params && is_array($params) &&
     array_key_exists('centre', $params) &&
     array_key_exists('brand', $params) &&
     array_key_exists('addr', $params) &&
     array_key_exists('phone', $params))
   {
    $centre = Util::item($params, 'centre');
    $brand = Util::item($params, 'brand');
    $addr = Util::item($params, 'addr');
    $phone = Util::item($params, 'phone');
    if (!self::createMember($id, $brand, $centre, $addr, $phone))
     exit('Error ' . DB::lastQuery());
    $auth_uri = WDomain::pro() . Base::host() . Base::home() . 'com/';
   }
  }
  if ($auth_uri)
  {
   if (WDomain::ssl() && substr($auth_uri, 0, 7) == 'http://')
    $auth_uri = 'https://' . substr($auth_uri, 7);
   header('Location: ' . $auth_uri);
   return true;
  }
  return false;
 }

 /**
  * Log the current client out
  * @return assoc_array Set of the result values
  */
 public static function logout()
 {
  HTTP::clearCookie(self::COOKIE_KEY);
  return array('result' => 'OK');
 }

 public static function createClientForMaster($firstname, $lastname, $email)
 {
  $db = DB::getAdminDB();
  $pass = WClient::createPassword();
  $values = array
  (
   'firstname' => DB::str($firstname)
  ,'lastname' => DB::str($lastname)
  ,'email' => DB::str($email)
  ,'pass' => DB::str($pass)
  );
  if (!$db->insertValues(WClient::TABLE_CLIENT, $values))
   return null;
  $id = $db->insert_id;
  self::sendPassword($id, $firstname, $lastname, $email, $pass, self::PASS_CASE_MASTER);
  return $id;
 }

 /**
  * Create a new client password
  * @return string The new password text
  */
 public static function createPassword()
 {
  return Util::randomString(8);
 }

 /**
  * Send a message to client with the notice text
  * @param int $id Client id
  * @param string $firstname Client firstname
  * @param string $lastname Client lastname
  * @param string $email E-mail address
  * @param string $href Original url to return after the E-mail confirmation
  * @param string $hash Hash value
  * @param string $cmpId Auth campaign id
  * @return bool success of the operation
  */
 private static function sendSignupNotice($id, $firstname, $lastname, $email, $href, $hash, $cmpId)
 {
  // Substitution templates:
  $NAME = '#NAME#';
  $HREF = '#HREF#';
  $TITLE = '#TITLE#';
  $LOGO = '#LOGO#';
  $DISC = '#DISC#';
  $MINVLE = '#MINVLE#';
  $RDDRTN = '#RDDRTN#';
  $OTHRDSC = '#OTHRDSC#';
  $head =
  "<html>\n" .
  "<head>\n" .
  "<meta charset=\"utf-8\" />\n" .
  "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
  "</head>\n" .
  "<body>\n";
  $foot =
  "</body>\n" .
  "</html>\n";
  $bodyTmpl = Lang::getText('email', 'signin');
  if (!$bodyTmpl)
  {
   $bodyTmpl =
   "<div style='max-width:600px;'>\n" .
   "<h1 style='font-size:18px;margin:0 0 12px;' align='center'>$LOGO<br/>Congratulations on joining the Wellclubs online community!</h1>\n" .
   "<p style='margin:12px 6px;'>Dear $NAME,</p>\n" .
   "<if-hash>\n" .
   "<p style='margin:12px 6px;'>\n" .
   "To complete the registration of your account please <a target='_blank' href=\"$HREF\">click here</a>. You will be transferred back to the Wellclubs website.<br/>\n" .
   "</p>\n" .
   "</if-hash>\n" .
   "<if-no-hash>\n" .
   " <p style='margin:12px 6px;'>Thank you for registering. You can now <if-href><a target='_blank' href=\"$HREF\">log-in</a></if-href><if-no-href>log-in</if-no-href> to your account any time with the social network you used to register.<br/>\n\n" .
   " </p>\n" .
   "</if-no-hash>\n" .
   "<if-signin-dsc>\n\n" .
   " <p style='margin:12px 6px;'>You have $DISC to use against your first booking\n\n" .
   " <if-min-vle> of $MINVLE or more</if-min-vle>\n\n" .
   " <if-rd-drnt> within the next $RDDRTN days</if-rd-drnt>.\n\n" .
   " <if-no-othr-dsc> This offer cannot be used in conjunction with any other promotion.</if-no-othr-dsc>\n\n" .
   " <if-othr-dsc> This offer can be used in conjunction with other promotion discounts of $OTHRDSC or less.</if-othr-dsc>\n\n" .
   " </p><p style='margin:12px 6px;'>Just log in and book!<br/>\n\n" .
   " </p>\n" .
   "</if-signin-dsc>\n\n" .
   "<p style='margin:20px 6px 6px;'>With warm regards,</p>\n".
   "<p style='margin:12px 6px;'>Wellclubs Customer Care Team</p>\n"  .
   "</div>\n";
   Lang::setText($bodyTmpl, 'email', 'signin');
  }

  $disc = null;
  $minVle = '';
  $rdDrtn = '';
  $othrDsc = '';

  //echo Base::htmlComment("cmpId='$cmpId'", true);
  $signInDscData = WDsc::signinCltCmp($id, $cmpId);
  if ($signInDscData)
  {
   $curr = WCurrency::makeObjs(WCentre::currencyIdSafe());
   $disc = WCurrency::addObjs($signInDscData['evnt_dsc_amnt'], $curr);
   $minVle = ($signInDscData['min_vle'] > 0) ? WCurrency::addObjs($signInDscData['min_vle'], $curr) : '';
   $othrDsc = ($signInDscData['max_othr_dsc_vle'] > 0) ? WCurrency::addObjs($signInDscData['max_othr_dsc_vle'], $curr) : '';
   $rdDrtn = $signInDscData['rdmptn_durtn'];
  }

  $tags = array
  (
    'if-signin-dsc' => !!$signInDscData,
    'if-min-vle' => !!$minVle,
    'if-rd-drnt' => !!$rdDrtn,
    'if-no-othr-dsc' => !$othrDsc,
    'if-othr-dsc' => !!$othrDsc,
    'if-hash' => !!$hash,
    'if-no-hash' => !$hash,
    'if-href' => !!$href,
    'if-no-href' => !$href
  );
  $bodySrc = Util::cutTags($bodyTmpl, $tags);

  $bas = Base::bas();
  $subject = 'Welcome to Wellclubs';
  $name = htmlspecialchars($firstname . ' ' . $lastname);
  $href = Util::buildUrl(Util::addUrlParams($href, array('wcid' => $id, 'hash' => $hash, 'action' => 'signup')));
  $title = htmlspecialchars(Base::fullTitle());
  $logo = "<a target='_blank' href='" . $bas . "'><img src='" . $bas . "pic/wc-logo-32.png' width='91px' height='32px'/></a>\n";
  $body = str_replace(
    array($NAME, $HREF, $TITLE, $LOGO, $DISC, $MINVLE, $RDDRTN, $OTHRDSC),
    array($name, $href, $title, $logo, $disc, $minVle, $rdDrtn, $othrDsc),
    $bodySrc);

  $message = $head . $body . $foot;
  return self::sendMail($id, $name, $email, $subject, $message);
 }

 private static function sendRestoreNotice($id, $firstname, $lastname, $email, $href, $hash)
 {
  $subject = 'Wellclubs password reset';
  $name = htmlspecialchars($firstname . ' ' . $lastname);
  $URI = Util::parseUrl($href);
  $URI['params']['wcid'] = $id;
  $URI['params']['hash'] = $hash;
  $URI['params']['action'] = 'restore';
  $URI['scheme'] = WDomain::ssl() ? 'https' : 'http';
  $href = Util::buildUrl($URI);
  // Substitution templates:
  $NAME = '#NAME#';
  $HREF = '#HREF#';
  $head =
  "<html>\n" .
  "<head>\n" .
  "<meta charset=\"utf-8\" />\n" .
  "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
  "</head>\n" .
  "<body>\n";
  $foot =
  "</body>\n" .
  "</html>\n";
  $body = Lang::getText('email', 'pwd-reset');
  if (!$body)
  {
   $body =
   "<div style='max-width:600px;'>\n" .
   "<p style='margin:6px 0;'>Dear $NAME,</p>\n" .
   "<p style='margin:6px 0;'>We have received a request to reset the password on your account. If you haven't requested it please disregard this email.</p>\n" .
   "<p style='margin:6px 0;'>To set a new password <a target=\"_blank\" href=\"$HREF\">click here</a>.</p>\n" .
   "<p style='margin:10px 0 6px;'>With warm regards,</p>\n" .
   "<p style='margin:6px 0;'>Wellclubs Customer Care</p>\n" .
   "</div>\n";
   Lang::setText($body, 'email', 'pwd-reset');
  }
  $body = str_replace(array($NAME, $HREF), array($name, $href), $body);
  $message = $head . $body . $foot;
  return self::sendMail($id, $name, $email, $subject, $message);
 }

 private static function sendListedNotice($id, $name, $email, $ctr, $addr, $phone)
 {
  $subject = 'Welcome to Wellclubs';
  $href = Base::bas() . 'com/';
  // Substitution templates:
  $NAME = '#NAME#';
  $HREF = '#HREF#';
  $CTR = '#CTR#';
  $ADDR = '#ADDR#';
  $PHONE = '#PHONE#';
  $head =
  "<html>\n" .
  "<head>\n" .
  "<meta charset=\"utf-8\" />\n" .
  "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
  "</head>\n" .
  "<body>\n" .
  "<div style='max-width:600px;'>\n";
  $foot =
  "</div>\n" .
  "</body>\n" .
  "</html>\n";
  $body = Lang::getText('email', 'listed');
  if (!$body)
  {
   $body =
   "<h1>Wellclubs business listing confirmation</h1>\n" .
   "<p>Dear $NAME,</p>\n" .
   "<p>Somebody (maybe just you) has listed the business \"$CTR\" in \"$ADDR\", phone $PHONE.</p>\n" .
   "<p>To setup this business you should visit <a href=\"$HREF\">Wellclubs business page</a> and answer the questions.</p>\n" .
   "<p>If you do not know what happens just delete this message.</p>\n";
   Lang::setText($body, 'email', 'listed');
  }
  $body = str_replace
  (
    array($NAME, $HREF, $CTR, $ADDR, $PHONE),
    array($name, $href, $ctr, $addr, $phone),
    $body
  );
  $message = $head . $body . $foot;
  return self::sendMail($id, $name, $email, $subject, $message);
 }

 /*private static function sendInvite($id, $name, $email, $pass, $key)
 {
  $subject = 'Welcome to Wellclubs!';
  $home = Base::bas();
  $com = $home . 'com/';
  $message =
  "<html><head>\n" .
  "<meta charset=\"utf-8\" />\n" .
  "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
  "</head><body>\n" .
  "<h2>Congratulations!</h2>\n" .
  "<p>Dear $name,</p>\n" .
  "<p>Registration of your business is almost complete.</p>\n" .
  "<p>To finalize it you can visit <a href=\"$home\">Wellclubs</a> once" .
  " using following credentials for login" .
  "<table><tr><td>E-mail address:</td><td>$email</td></tr>\n" .
  "<tr><td>Password:</td><td>$pass</td></tr></table>\n" .
  " or just click this link: <a href=\"$com?fixup=$id&key=$key\">Finalize registration</a>.</p>\n" .
  "<p>Please keep your password safe from unauthorized access.</p>\n" .
  "<p>Use your personal <a href=\"$com\">Control page</a> for describing your business in details.</p>\n" .
  "<p>Sincerely yours,</p>\n" .
  "<p>Wellclubs site team</p>\n" .
  "</body></html>\n";
  return self::sendMail($id, $name, $email, $subject, $message);
 }*/

 private static function errorFalse($text)
 {
  echo $text;
  return false;
 }

 public static function sendBookNotice($bookId)
 {
  $bas = Base::bas();
  $db = DB::getDB();
  // Booking record
  $book = $db->queryPairs(WPurchase::TABLE_BOOK, null, 'id=' . $bookId);
  $bookRef = WPurchase::encodeRefId($bookId);
  if (!$book)
   return self::errorFalse("No book $bookId record");
  $pay = $book['book_type_id'] != 'B';
  $cltId = $book['client_id'];
  $clt = $db->queryPairs(self::TABLE_CLIENT, null, 'id=' . $cltId);
  $cltName = htmlspecialchars(self::makeName($clt['title'], $clt['firstname'], $clt['lastname']));
  $cltEmail = $clt['email'];
  $cltPhone = htmlspecialchars($clt['phone']);
  $ctrId = $book['centre_id'];
  $srvId = $book['srv_id'];
  $bookDate = Util::date2strDDMMMYYYY(DB::str2date($book['book_date']));
  $bookTime = Util::min2str($book['book_time']);
  $bookDura = $book['book_dura'];
  $bookType = htmlspecialchars(Lang::getWord('paytype', $pay ? 'ONLINE' : 'AT VENUE', 'email'));
  $bookQty = intval($book['qty']);
  $discPrc = intval($book['disc']);
  $price = intval($book['price']);
  $fact = intval($book['fact']);
  $total = intval($book['total']);
  $discCtr = $bookQty * $price - $fact;
  $discWC = $fact - $total;
  $discAmnt = $discCtr + $discWC;
  // Centre information
  if (!WCentre::id())
   WCentre::initCurrent($ctrId);
  if (WCentre::id() != $ctrId)
   return self::errorFalse("Error loading centre $ctrId info");
  $ctrTitle = htmlspecialchars(WCentre::title());
  $ctrAddr = htmlspecialchars(WCentre::address());
  $ctrPhones = htmlspecialchars(implode(', ', WCentre::phones()));
  $ctrEmail = WCentre::email();
  $curr = WCurrency::makeObjs(WCentre::currencyIdSafe());
  // Service information
  $srvTitle = htmlspecialchars(WService::getTitle($srvId));
  $logo = "<a target='_blank' href='" . $bas . "'><img src='" . $bas . "pic/wc-logo-32.png' width='91px' height='32px'/></a>\n";
  if (WCentre::lat() || WCentre::lng())
  {
   $mapW = 405;
   $mapH = 250;
   $mapURI = WCentre::getStaticMapURI($mapW, $mapH);
   $mapHref = WCentre::getDynamicMapURI();
   $ctrMap = "<a href='$mapHref' target='_blank'><img src='$mapURI' width='$mapW' height='$mapH'/></a><br/><br/>\n";
  }
  else
   $ctrMap = '';
  $ctrLink = "<a target='_blank' href='{$bas}ctr-{$ctrId}/'>$ctrTitle</a>";
  $srvLink = "<a target='_blank' href='{$bas}srv-{$srvId}/'>$srvTitle</a>";
  $priceBase = WCurrency::addObjs($price, $curr);
  $priceFact = WCurrency::addObjs($fact, $curr);
  $discCtrCurr = WCurrency::addObjs($discCtr, $curr);
  $discCurr = WCurrency::addObjs($discAmnt, $curr);
  $zeroMoney = WCurrency::addObjs(intval(0), $curr);
  $totalAmnt = $priceFact;
  $totalPaid = $pay ? $priceFact : $zeroMoney;
  $totalDue = $pay ? $zeroMoney : $priceFact;
  $sumTotal = WCurrency::addObjs($total, $curr);
  $uriPolicy = "{$bas}policy/#policy-booking";
  $subject =
   htmlspecialchars(Lang::getWord('subj-pay', 'Booking confirmation', 'email') . ' ' .
   Util::date2strWEEKDDMMM(DB::str2date($book['book_date'])) . ', ' . $bookTime . ', ' .
   Lang::getWord('prompt', 'ref', 'email') . ': ' . $bookRef);
  // Substitution templates:
  $LOGO = '#LOGO#';
  $CLTID = '#CLT-ID#';
  $CLTNAME = '#CLT-NAME#';
  $CLTPHONE = '#CLT-PHONE#';
  $CLTEMAIL = '#CLT-EMAIL#';
  $CTRLINK = '#CTR-LINK#';
  $CTRTITLE = '#CTR-TITLE#';
  $CTRMAP = '#CTR-MAP#';
  $CTRADDR = '#CTR-ADDR#';
  $CTRPHONES = '#CTR-PHONES#';
  $SRVLINK = '#SRV-LINK#';
  $BOOKREF = '#BOOK-REF#';
  $BOOKDATE = '#BOOK-DATE#';
  $BOOKTIME = '#BOOK-TIME#';
  $BOOKDURA = '#BOOK-DURA#';
  $BOOKTYPE = '#BOOK-TYPE#';
  $TOTALAMNT = '#TOTAL-AMNT#';
  $TOTALPAID = '#TOTAL-PAID#';
  $TOTALDUE = '#TOTAL-DUE#';
  $BOOKQTY = '#BOOK-QTY#';
  $DISCPRC = '#DISC-PRC#';
  $DISCCTRCURR = '#DISC-CTR-CURR#';
  $DISCCURR = '#DISC-CURR#';
  $PRICEBASE = '#PRICE-BASE#';
  $SUMTOTAL = '#SUM-TOTAL#';
  $URIPOLICY = '#URI-POLICY#';
  $head =
  "<html>\n" .
  "<head>\n" .
  "<meta charset=\"utf-8\" />\n" .
  "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
  "</head>\n" .
  "<body>\n";
  $foot =
  "</body>\n" .
  "</html>\n";
  $body = Lang::getText('email', 'book', 'pay');
  if (!$body)
  {
   $body =
   "<div style='max-width:600px;'>\n\n" .

   "<if-clt>\n" .
   " <h1 style='font-size:18px;margin:0 0 12px;' align='center'>$LOGO<br/>Dear $CLTNAME,<br/>Thank you for using Wellclubs!</h1>\n" .
   " <p style='font-weight:bold;margin-bottom:10px;'>Your booking details:</p>\n" .
   "</if-clt>\n\n" .
   "<if-ctr>\n" .
   " <h1 style='font-size:18px;margin:0 0 12px;' align='center'>$LOGO<br/>Online booking confirmation</h1>\n" .
   " <p style='font-weight:bold;margin-bottom:10px;'>Booking details:</p>\n" .
   " <p style='font-weight:bold;'>Customer: $CLTNAME</p>\n\n" .
   " <if-clt-phone><p style='font-weight:bold;'>Phone: $CLTPHONE</p></if-clt-phone>\n\n" .
   " <p style='font-weight:bold;margin-bottom:10px;'>email: $CLTEMAIL</p>\n" .
   "</if-ctr>\n\n" .
   "<p style='background:#ddd'>Booking Reference Number: $BOOKREF<br/>Customer ID: $CLTID</p>\n" .
   "<p style='margin-bottom:10px;'>Payment: $BOOKTYPE</p>\n" .
   "<p style='font-weight:bold'>Venue: $CTRLINK</p>\n" .
   "Address: $CTRADDR<br/>\n" .
   "Phones: $CTRPHONES\n" .

   "<div style='background:#eee;margin:0;'>\n" .
   "Service: $SRVLINK<br/>\n" .
   "<table style='width:250px;'>\n" .
   " <tr><td>Date:</td><td style='text-align:right;'>$BOOKDATE</td></tr>\n" .
   " <tr><td>Time:</td><td style='text-align:right;'>$BOOKTIME</td></tr>\n" .
   " <tr><td>Duration:</td><td style='text-align:right;'>$BOOKDURA&nbsp;min" .
   "  <span style='background-position:-80px -112px;width:16px;height:16px;display:block;float:right;" .
   "  background-image:url(\"" . $bas . "css/ui/book/images/ui-icons_444_256x240.png\");'></span></td></tr>\n" .
   " <tr><td>Price:</td><td style='text-align:right;'>$PRICEBASE</td></tr>\n" .
   " <tr><td><if-qty>Quantity:</if-qty></td><td style='text-align:right;'><if-qty>$BOOKQTY</if-qty></td></tr>\n" .
   "</table>\n" .
   "</div>\n" . // style='background:#eee;margin:0;'

   "<div style='margin:0;'>\n\n" .

   "<if-clt>\n\n" .
   " <table style='width:250px;'><tbody>\n" .
   "  <tr><td><if-dsc-amnt>Discount:</if-dsc-amnt></td><td style='text-align:right;color:red;'><if-dsc-amnt>-&nbsp;$DISCCURR</if-dsc-amnt></td></tr>\n" .
   "  <tr style='font-size:0px;'><td>&nbsp;</td><td style='text-align:right;'><hr></td></tr>\n" .
   "  <tr style='font-weight:bold;'><td>\n" .
   "    <if-pay-online>Total paid:</if-pay-online>\n" .
   "    <if-pay-at-venue>Total due at venue:</if-pay-at-venue>\n" .
   "   </td><td style='text-align:right;'>$SUMTOTAL</td>\n" .
   "  </tr>\n" .
   " </tbody></table>\n" .
   " <p>\n" .
   " </p>\n" .
   " <p><br/>\n" .
   "Need to change your booking?\n" .
   "Refer to our <a target='_blank' href='$URIPOLICY'>cancellation policy</a>\n" .
   "to ensure you are eligible to change or cancel your booking.\n" .
   " </p>\n" .
   " <p>\n" .
   "For cancellations within 24 hours of booking please contact us on booking@wellclubs.com<br/>\n" .
   "For re-scheduling the appointment please contact $CTRTITLE directly.\n" .
   " </p><br/><br/>\n\n" .
   " <if-map><p>Location map:</p><p align='center'>$CTRMAP</p></if-map>\n\n" .
   " <p>\n" .
   "We hope you will enjoy your treatment!\n" .
   " <br/><br/>\n" .
   "Kind regards,\n" .
   " <br/><br/>\n" .
   "Wellclubs Customer Care\n" .
   " </p>\n" .
   " <div style='background:#eee;width:100%;display:block;margin:10px 0;text-align:center;'>\n" .
   "Need help? Email us on <a href='mailto:support@wellclubs.com'>support@wellclubs.com</a>\n" .
   " </div>\n" .
   " <p><br/>&copy; 2015 Wellclubs.com</p>\n\n" .
   "</if-clt>\n\n" .

   "<if-ctr>\n" .
   " <table style='width:250px;'>\n" .
   "  <tr style='color:red;'>\n" .
   "  <td>\n" .
   "   <if-dsc-ctr>Discount: $DISCPRC%</if-dsc-ctr>\n" .
   "  </td>\n" .
   "  <td style='text-align:right;'>\n".
   "   <if-dsc-ctr>-&nbsp;$DISCCTRCURR</if-dsc-ctr>\n" .
   "  </td>\n" .
   "  </tr>\n" .
   "  <tr style='font-size:0px;'><td>&nbsp;</td><td style='text-align:right;'><hr></td></tr>\n" .
   "  <tr><td>Total:</td><td style='text-align:right;'>$TOTALAMNT</td></tr>\n" .
   "  <tr><td>Paid:</td><td style='text-align:right;'>$TOTALPAID</td></tr>\n" .
   "  <tr style='font-size:0px;'><td>&nbsp;</td><td style='text-align:right;'><hr></td></tr>\n" .
   "  <tr style='font-weight:bold;'><td>Total due at venue:</td><td style='text-align:right;'>$TOTALDUE</td></tr>\n" .
   " </table>\n" .
   " <p>\n" .
   " </p>\n" .
   " <p style='color:red;'><br/>\n" .
   "Time slot not available? Please contact the customer directly for re-booking." .
   " </p>\n" .
   " <p>Need Help? Refer to our <a target='_blank' href='$URIPOLICY'>cancellation policy</a> or email our customer care at <a href='mailto:booking@wellclubs.com'>booking@wellclubs.com</a>\n" .
   " <br/><br/>\n\n" .
   " </p>\n" .
   " <p>\n" .
   "Kind regards,<br/>Wellclubs Customer Care\n" .
   " </p>\n" .
   " <p><br/>&copy; 2015 Wellclubs.com</p>\n\n" .
   "</if-ctr>\n\n" .

   "</div>\n" . // style='margin:0;'

   "</div>\n"; // style='max-width:600px;'

   Lang::setText($body, 'email', 'book', 'pay');
  }

  $tags = array
  (
    'if-dsc-amnt' => $discAmnt > 0,
    'if-dsc-ctr' => $discCtr > 0,
    'if-map' => !!$ctrMap,
    'if-qty' => $bookQty > 1,
    'if-clt-phone' => !!$cltPhone,
    'if-pay-online' => $pay,
    'if-pay-at-venue' => !$pay
  );
  $body = Util::cutTags($body, $tags);

  $source = array($LOGO, $CLTID, $CLTNAME, $CLTPHONE, $CLTEMAIL, $CTRLINK, $CTRTITLE, $CTRMAP, $CTRADDR, $CTRPHONES, $SRVLINK);
  $target = array($logo, $cltId, $cltName, $cltPhone, $cltEmail, $ctrLink, $ctrTitle, $ctrMap, $ctrAddr, $ctrPhones, $srvLink);
  array_push($source, $BOOKREF, $BOOKDATE, $BOOKTIME, $BOOKDURA, $BOOKTYPE, $TOTALAMNT, $TOTALPAID, $TOTALDUE, $BOOKQTY);
  array_push($target, $bookRef, $bookDate, $bookTime, $bookDura, $bookType, $totalAmnt, $totalPaid, $totalDue, $bookQty);
  array_push($source, $DISCPRC, $DISCCTRCURR, $DISCCURR, $PRICEBASE, $SUMTOTAL, $URIPOLICY);
  array_push($target, $discPrc, $discCtrCurr, $discCurr, $priceBase, $sumTotal, $uriPolicy);
  $body = str_replace($source, $target, $body);

  $bCtr = Util::cutTags($body, array('if-ctr' => true, 'if-clt' => false));
  $body = Util::cutTags($body, array('if-ctr' => false, 'if-clt' => true));

  $result = array();
  $error = '';

  $message = $head . $bCtr . $foot;
  $mbrId = WCentre::memberId();
  $mbrName = self::getClientName($mbrId);
  if (!strlen($ctrEmail))
   $ctrEmail = self::getClientEmail($mbrId);
  if (!self::sendMail($mbrId, $mbrName, $ctrEmail, $subject, $message))
   $error .= self::errorFalse("Error sending a booking notice to centre $ctrId via SMTP: " . SMTP::error() . "\n");

  if (!SMTP::send('Wellclubs booking control', 'booking@wellclubs.com', $subject, $message))
   $error .= self::errorFalse("Error sending a booking notice to wellclubs via SMTP: " . SMTP::error() . "\n");

  $message = $head . $body . $foot;
  if (!self::sendMail($cltId, $cltName, $cltEmail, $subject, $message))
   $error .= self::errorFalse("Error sending a booking notice to client $cltId via SMTP: " . SMTP::error() . "\n");

  if ($error)
   $result['warning'] = $error;
  return $result;
 }

 /**
  * Send a message to client with the password text
  * @param int $id Client id
  * @param string $firstname Client firstname
  * @param string $lastname Client lastname
  * @param string $email E-mail address
  * @param string $pass Password text
  * @param bool $case PASS_CASE_SIGNIN (0) or PASS_CASE_RESTORE (1)
  * @return bool success of the operation
  */
 private static function sendPassword($id, $firstname, $lastname, $email, $pass, $case)
 {
  switch ($case)
  {
  case self::PASS_CASE_RESTORE :
   $subject = 'Thank you for your interest';
   $message = 'Your new password is';
   break;
  default :
   $subject = 'Welcome to Wellclubs!';
   $message = 'Your password is';
  }
  $message .= ': ' . $pass;
  return self::sendMail($id, trim($firstname . ' ' . $lastname), $email, $subject, $message);
 }

 /**
  * Send an message via E-mail
  * @param int $id Client id of the recipient
  * @param string $name Name of the recipient
  * @param string $email E-mail address of the recipient
  * @param string $subject Subject of the message
  * @param string $message Text of the message
  * @param array $headers Additional headers as assoc_array
  * @return bool result of the operation
  */
 public static function sendMail($id, $name, $email, $subject, $message, $headers = null)
 {
  $values = array('client_id' => $id, 'subject' => DB::str($subject), 'message' => DB::str($message));
  DB::getAdminDB()->insertValues('biz_mail', $values);
  return SMTP::send($name, $email, $subject, $message, $headers);
 }

 /**
  * Function returns an array of assoc_arrays about the centres available for the client
  * @return array of assoc_array List of centres available for the client
  */
 public function centres($domainId = null)
 {
  if (!$this->id)
   return null;
  $result = array();
  $this->centresMember($result, $domainId);
  $this->centresMaster($result, $domainId);
  return $result;
 }

 /**
  * Function returns an array of assoc_arrays about the centres owned by the client as member
  * @param assoc_array reference $result Destination array reference
  * @return array of assoc_array List of centres owned by the client as member
  */
 private function centresMember(array &$result, $domainId = null)
 {
  if ($this->isMember())
  {
   $table = WCentre::TABLE_CENTRE;
   $fields = 'id,name,address,brand_id';
   $where = 'member_id=' . WClient::id();
   $where .= Util::str($domainId, ' and domain_id=');
   $order = 'serial,id';
   $centres = DB::getDB()->queryRecords($table, $fields, $where, $order);
   if ($centres)
    foreach ($centres as $centre)
     $this->addCentreToList($result, $centre[0], $centre[1], $centre[2], WClient::id(), $centre[3],
       true, null);
  }
  //echo "Here\n" . DB::lastQuery() . "\n";
 }

 /**
  * Function returns an array of assoc_arrays about the centres available for the client as master
  * @param assoc_array reference $result Destination array reference
  * @return array of assoc_array List of centres available for the client as master
  */
 private function centresMaster(array &$result, $domainId = null)
 {
  if (WClient::me()->isMaster())
  {
   $tables = WCentre::TABLE_CENTRE . ' a,' . WMaster::TABLE_MASTER . ' b';
   $fields = 'a.id,a.name,a.address,a.member_id,a.brand_id,b.id master_id,b.role_id';
   $where = 'b.centre_id=a.id and b.client_id=' . WClient::id() . ' and can_connect=\'1\'';
   $where .= Util::str($domainId, ' and a.domain_id=');
   $order = 'a.serial,a.id';
   $centres = DB::getDB()->queryRecords($tables, $fields, $where, $order);
   if ($centres)
    foreach ($centres as $centre)
     if (!array_key_exists($centre[0], $result))
      $this->addCentreToList($result, $centre[0], $centre[1], $centre[2], $centre[3], $centre[4],
        false, WPriv::getMasterListPrivByRole($centre[5], $centre[6]));
  }
 }

 /**
  * Function adds an assoc_array of centre to array result
  * @param array $result Destination array reference
  * @param int $id Centre ID
  * @param string $name Centre name
  * @param string $addr Centre address
  * @param int $member Centre owner ID
  * @param int $brand Centre brand ID
  * @param bool $owner Centre ownership flag
  * @param assoc_array $privs List of privileges ( id => name )
  */
 private function addCentreToList(array &$result, $id, $name, $addr, $member, $brand, $owner, $privs)
 {
   $id = intval($id);
   $name = WCentre::getTitleForData($id, $name, $brand);
   $centre = array(
       'id' => $id,
       'name' => $name,
       'addr' => $addr,
       'bnd' => intval($brand),
       'bndT' => '',
       'owner' => $owner,
       'privs' => $privs);
   $bnd = DB::getDB()->queryFields(WBrand::TABLE_BRAND, 'member_id,name', 'id=' . $brand);
   if ($bnd && ($bnd[0] != $member) && ($bnd[0] != WClient::id())) ///< If nor centre owner nor client are not the brand owner
   {
    $centre['bnd'] = null;
    $centre['bndT'] = WBrand::getTitleForData($brand, $bnd[1]);
   }
   if ($member != WClient::id())
    $centre['ownerId'] = $member;
   $result[$id] = $centre;
 }

 /**
  * Function returns an array of assoc_arrays about the brands owned by the client
  * @return array of assoc_array List of brands owned by the client
  */
 public function brands($extra = false)
 {
  if (!$this->id)
   return null;
  $result = array();
  if ($this->isMember())
  {
   $fields = $extra ? 'id,name,email,uri' : 'id,name';
   $bnds = DB::getDB()->queryRecords(WBrand::TABLE_BRAND, $fields, 'member_id=' . WClient::id(), 'id');
   if ($bnds)
    foreach ($bnds as $bnd)
    {
     $id = intval($bnd[0]);
     $name = WBrand::getTitleForData($id, $bnd[1]);
     if ($extra)
      $result[$id] = array('name' => $name, 'email' => $bnd[2], 'uri' => $bnd[3]);
     else
      $result[$id] = array('name' => $name);
    }
  }
  return $result;
 }
}

?>