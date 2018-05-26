<?php

/**
 * Description of PageBookClt
 *
 * @author Интернет
 */
class PageBookClt
{
 public static function getPageData()
 {
  $clt = array();
  $view = WClient::view();
  if (!$view)
   return null;
  $clt['id'] = intval($view->getId());
  //$clt['name'] = $view->getName();
  $clt['title'] = $view->getTitle();
  $clt['firstname'] = $view->getFirstName();
  $clt['lastname'] = $view->getLastName();
  if ($view->isEditable())
  {
   $clt['edit'] = true;
   $clt['email'] = $view->getEmail();
   self::fillResultValue($clt, 'phone', $view->getPhone());
   self::fillResultValue($clt, 'address', $view->getAddress());
   self::fillResultValue($clt, 'city', $view->getCity());
   self::fillResultValue($clt, 'region', $view->getRegion());
   self::fillResultValue($clt, 'country', $view->getCountryId());
   self::fillResultValue($clt, 'countryT', $view->getCountryName());
   self::fillResultValue($clt, 'post_code', $view->getPostCode());
  }
  $clt['img'] = WClient::imageBigURI($view->getId());
  self::fillResultValue($clt, 'gender', $view->getGender());
  self::fillResultValue($clt, 'genderT', WClient::getGenderText($view->getGender()));
  $birthday = array();
  self::fillResultValue($birthday, 'day', $view->getBDay());
  self::fillResultValue($birthday, 'mon', $view->getBMon());
  self::fillResultValue($birthday, 'year', $view->getBYear());
  if (count($birthday))
   $clt['birthday'] = $birthday;
  $clt['visited'] = $view->getVisited();
  $clt['created'] = $view->getCreated();
  self::fillResultValue($clt, 'note', $view->getNote());
  return $clt;
 }

 private static function fillResultValue(&$result, $name, $value)
 {
  if ($value)
   $result[$name] = $value;
 }

 public static function putRecordsBegin($caption, $class = null)
 {
  echo "<table class='" . Util::str($class, null, ' ') . "records'>\n";
  echo '<caption class="topic-title big-title">' . Lang::getWord('title', $caption, 'clt') . "</caption>\n";
  echo "<colgroup><col width='200'/><col/></colgroup>\n";
 }

 public static function putRecordsEnd()
 {
  echo "</table>\n";
 }

 public static function putRecordText($field, $prompt)
 {
  echo "<tr class='record' field='" . $field . "' type='text'>\n";
  echo "<td class='prompt'>" . Lang::getWord('prompt', $prompt, 'clt') . "</td>\n";
  echo
    "<td>" .
    "<div class='value'></div>" .
    "<div class='input'><input/></div>" .
    "</td>\n";
  echo "</tr>\n";
 }

 public static function putRecordList($field, $prompt)
 {
  echo "<tr class='record' field='" . $field . "' type='list'>\n";
  echo "<td class='prompt'>" . Lang::getWord('prompt', $prompt, 'clt') . "</td>\n";
  echo
    "<td>" .
    "<div class='value'></div>" .
    "<div class='input'><select></select></div>" .
    "</td>\n";
  echo "</tr>\n";
 }

 public static function putRecordDate($field, $prompt)
 {
  echo "<tr class='record' field='" . $field . "' type='date'>\n";
  echo "<td class='prompt'>" . Lang::getWord('prompt', $prompt, 'clt') . "</td>\n";
  echo
    "<td>" .
    "<div class='value'>" .
    "<span part='day'></span>" .
    "<span>/</span>" .
    "<span part='mon'></span>" .
    "<span>/</span>" .
    "<span part='year'></span>" .
    "</div>\n";
  echo
    "<div class='input'>" .
    "<input part='day' maxlength='2'/>" .
    "<span>/</span>" .
    "<input part='mon' maxlength='2'/>" .
    "<span>/</span>" .
    "<input part='year' maxlength='4'/>" .
    "</div>" .
    "</td>\n";
  echo "</tr>\n";
 }

 public static function putRecordView($field, $prompt)
 {
  echo "<tr class='record' field='" . $field . "'>\n";
  echo "<td class='prompt'>" . Lang::getWord('prompt', $prompt, 'clt') . "</td>\n";
  echo "<td><div class='value'></div></td>\n";
  echo "</tr>\n";
 }

 public static function getList($field)
 {
  $response = array('result' => 'OK');
  if ($field == 'gender')
  {
   $response['list'] = array('M' => WClient::getGenderText('M'), 'F' => WClient::getGenderText('F'));
   echo json_encode($response);
  }
  else if ($field == 'country')
  {
   $response['list'] = WCountry::getList();
   echo json_encode($response);
  }
  else
  {
   echo json_encode(array('error' => 'Invalid field: ' + $field));
  }
 }

 public static function changeField($field, $value)
 {
  $response = array();
  if (!WClient::id())
   $response['error'] = Lang::getPageWord('error', WClient::ERROR_NO_LOGIN);
  else if (!WClient::view()->isEditable())
   $response['error'] = 'Access denied';
  else
  {
   $db = DB::getAdminDB();
   $values = array($field => DB::strn($value));
   switch ($field)
   {
   case 'email' :
   case 'title' :
   case 'firstname' :
   case 'lastname' :
   case 'phone' :
   case 'address' :
   case 'city' :
   case 'region' :
   case 'country' :
   case 'post_code' :
   case 'note' :
    if ($field == 'email')
    {
     if ($value == WClient::view()->email())
      break;
     $id = $db->queryField(WClient::TABLE_CLIENT, 'id', $values);
     if ($id)
     {
      $response['error'] = "Duplicated E-mail: '$value'";
      break;
     }
    }
    elseif ($field == 'phone')
    {
     $values['number'] = Util::pureNumber($value);
    }
    elseif ($field == 'country')
    {
     //$value = strtoupper($value);
     if (strlen($value) && !WCountry::exists($value))
     {
      $response['error'] = "Invalid country code: '$value'";
      break;
     }
     $values = array('country_id' => DB::strn($value));
    }
    if (!DB::getAdminDB()->modifyFields(WClient::TABLE_CLIENT, $values, 'id=' . WClient::view()->id()))
     $response['failure'] = DB::ERROR_UPDATE . "\n" . DB::lastQuery();
    break;
   case 'gender' :
    if (array_search($value, array('M', 'F', '')) === false)
     $response['error'] = "Invalid gender value: '$value'";
    else if (!DB::getAdminDB()->modifyField(WClient::TABLE_CLIENT, $field, 's', $value, 'id=' . WClient::view()->id()))
     $response['failure'] = DB::ERROR_UPDATE . "\n" . DB::lastQuery();
    break;
   case 'birthday' :
    $values = array
    (
     'byear' => DB::str(HTTP::get('y')),
     'bmon' => DB::str(HTTP::get('m')),
     'bday' => DB::str(HTTP::get('d'))
    );
    if (!DB::getAdminDB()->modifyFields(WClient::TABLE_CLIENT, $values, 'id=' . WClient::view()->id()))
     $response['failure'] = DB::ERROR_UPDATE . "\n" . DB::lastQuery();
    /*else
    {
     $response['bday'] = HTTP::get('bday');
     $response['bmon'] = HTTP::get('bmon');
     $response['byear'] = HTTP::get('byear');
    }*/
    break;
   default :
    $response['error'] = 'Unknown field: "' . $field . '"';
   }
  }
  $response['result'] =
    array_key_exists('failure', $response) ? 'failure' :
    array_key_exists('error', $response) ? 'error' :
   'OK';
  echo json_encode($response);
 }

 public static function uploadField($field)
 {
  $response = array();
  if (!WClient::id())
   $response['error'] = Lang::getPageWord('error', WClient::ERROR_NO_LOGIN);
  else if (!WClient::view()->isEditable())
   $response['error'] = 'Access denied';
  else
  {
   switch ($field)
   {
   case 'avatar' :
    if (!WClient::uploadImage(WClient::view()->id()))
     $response['failure'] = DB::ERROR_UPDATE . "\n" . DB::lastQuery();
    else
    {
     $suffix = '?v=' . date(DateTime::W3C);
     $response['data'] = array
     (
       'pic' => WClient::imageURI(WClient::view()->id()) . $suffix,
       'photo' => WClient::imageBigURI(WClient::view()->id()) . $suffix
     );
    }
    break;
   default :
    $response['error'] = 'Unknown field: "' . $field . '"';
   }
  }
  $response['result'] =
    array_key_exists('failure', $response) ? 'failure' :
    array_key_exists('error', $response) ? 'error' :
   'OK';
  echo json_encode($response);
 }

 public static function clearField($field)
 {
  $response = array();
  if (!WClient::id())
   $response['error'] = Lang::getPageWord('error', WClient::ERROR_NO_LOGIN);
  else if (!WClient::view()->isEditable())
   $response['error'] = 'Access denied';
  else
  {
   switch ($field)
   {
   case 'avatar' :
    if (!WClient::clearImage(WClient::view()->id()))
     $response['failure'] = DB::ERROR_UPDATE . "\n" . DB::lastQuery();
    else
    {
     $suffix = '?v=' . date(DateTime::W3C);
     $response['data'] = array
     (
       'pic' => WClient::imageURI(WClient::view()->id()) . $suffix,
       'photo' => WClient::imageBigURI(WClient::view()->id()) . $suffix
     );
    }
    break;
   default :
    $response['error'] = 'Unknown field: "' . $field . '"';
   }
  }
  $response['result'] =
    array_key_exists('failure', $response) ? 'failure' :
    array_key_exists('error', $response) ? 'error' :
   'OK';
  echo json_encode($response);
 }

 public static function getActiveBookings()
 {
  if (!WClient::id())
   return array();
  $fields = 'id,centre_id,srv_id,book_date,book_time,book_dura,total,curr';
  $where = 'client_id=' . WClient::id() . " and status='a'";
  $order = 'book_date desc,book_time desc,created desc';
  $limit = 50;
  $bookings = DB::getDB()->queryArrays(WPurchase::TABLE_BOOK, $fields, $where, $order, $limit);
  if (!$bookings)
   return array();
  $result = array();
  foreach ($bookings as $b)
  {
   $id = intval($b['id']);
   //$refId = 2011 * $b['id'] + (($id % 37) * ($id % 53));
   $refId = WPurchase::encodeRefId($id);
   $result[] = array
   (
    // http://unconnected.info/sn.aspx
    // http://denisx.ru/tech/prime-number/prime-numbers-list/
    // http://www.dpva.info/Guide/GuideMathematics/GuideMathematicsFiguresTables/SimpleFigures/
    'id' => $refId,
    'ctr' => $b['centre_id'],
    'ctrT' => WCentre::getTitle($b['centre_id']),
    'srv' => $b['srv_id'],
    'srvT' => WService::getTitle($b['srv_id']),
    'date' => Util::date2str(DB::str2date($b['book_date'])),
    'time' => Util::min2str($b['book_time']),
    'dura' => $b['book_dura'],
    'total' => $b['total'] . ' ' . $b['curr']
   );
  }
  return $result;
 }
}

?>
