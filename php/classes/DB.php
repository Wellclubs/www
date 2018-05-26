<?php

class DB extends mysqli ///< http://php.net/manual/en/class.mysqli.php
{
 const ERROR_SELECT = 'Error reading data from the database';
 const ERROR_INSERT = 'Error inserting data to the database';
 const ERROR_UPDATE = 'Error updating data in the database';
 const ERROR_DELETE = 'Error deleting data from the database';

 private static $dbr = null; // Read-only granted anonymous database connection
 private static $dbw = array(); // Map of password-protected database connections
 private static $queries = array();

 public static function queries() { return self::$queries; }
 private static function addQuery($query) { self::$queries[] = $query; }
 public static function lastQuery() { return self::$queries[count(self::$queries)-1]; }

 public static function getDB($wca = null)
 {
  $result = null;
  if ($wca == null)
  {
   if (!isset(self::$dbr))
   {
    $db = new DB(GUEST_USERNAME, GUEST_PASSWORD);
    if ($db->connect_errno == 0)
     self::$dbr = $db;
   }
   $result = self::$dbr;
  }
  else
  {
   if (array_key_exists($wca, self::$dbw))
    $result = self::$dbw[$wca];
   else
   {
    $values = explode('/', $wca);
    if (is_array($values) && (count($values) == 2))
    {
     //$login = WClient::login($values[0], $values[1], true);
     //if ($login['result'] == 'OK')
     // return self::getAdminDB();
     $saved_display_errors = ini_get('display_errors');
     ini_set('display_errors', '0');
     $db = new DB($values[0], $values[1]);
     ini_set('display_errors', $saved_display_errors);
     if ($db->connect_errno == 0)
     {
      self::$dbw[$wca] = $db;
      $result = $db;
     }
    }
   }
  }
  return $result;
 }

 public static function getAdminDB()
 {
  return self::getDB(OWNER_USERNAME . '/' . OWNER_PASSWORD);
 }

 public function __construct($username, $password)
 {
  parent::__construct(null, $username, $password, DATABASE_NAME);
  if (!mysqli_connect_error()) // http://php.net/manual/ru/mysqli.construct.php
   $this->set_charset('utf8');
 }

 public function __destruct()
 {
   if (mysqli_connect_error())
    return;
   $saved_display_errors = ini_get('display_errors');
   ini_set('display_errors', '0');
   $this->close();
   ini_set('display_errors', $saved_display_errors);
 }

 /**
  * Add slashes and quotations to the text
  * @param string $value Text to process
  * @return string Quoted and slashed text
  */
 public static function str($value)
 {
  return '\'' . addslashes($value) . '\'';
 }

 public static function strn($value)
 {
  return strlen($value) ? ('\'' . addslashes($value) . '\'') : 'null';
 }

 public static function int($value)
 {
  return intval($value);
 }

 public static function intn($value)
 {
  if (is_int($value))
   return $value;
  return (($value != null) && strlen($value)) ? intval($value) : 'null';
 }

 public static function ints($value)
 {
  if (is_int($value))
   return $value;
  return (($value != null) && strlen($value)) ? intval($value) : '';
 }

 public static function money($value)
 {
  return number_format($value, 2, '.', '');
 }

 public static function moneyn($value)
 {
  return is_numeric($value) ? number_format($value, 2) : 'null';
 }

 public static function daten($value)
 {
  if (is_string($value))
   $value = Util::str2date($value);
  return $value ? self::str(self::date2str($value)) : 'null';
 }

 public static function date2str($value, $def = '')
 {
  return ($value instanceof DateTime) ? $value->format('Y-m-d') : $def;
 }

 public static function str2date($value, $def = null)
 {
  //return self::nvl(DateTime::createFromFormat('Y-m-d', $value), $def);
  if (fnmatch('????-??-??', $value))
  {
   $date = new DateTime();
   $date->setDate(intval(substr($value, 0, 4)), intval(substr($value, 5, 2)), intval(substr($value, 8, 2)));
   return $date;
  }
  return $def;
 }

 public static function str2datetime($value, $def = null)
 {
  //return self::nvl(DateTime::createFromFormat('Y-m-d', $value), $def);
  if (fnmatch('????-??-?? ?:??:??', $value))
  {
   $date = new DateTime();
   $date->setDate(intval(substr($value, 0, 4)), intval(substr($value, 5, 2)), intval(substr($value, 8, 2)));
   $date->setTime(intval(substr($value, 11, 1)), intval(substr($value, 13, 2)), intval(substr($value, 16, 2)));
   return $date;
  }
  if (fnmatch('????-??-?? ??:??:??', $value))
  {
   $date = new DateTime();
   $date->setDate(intval(substr($value, 0, 4)), intval(substr($value, 5, 2)), intval(substr($value, 8, 2)));
   $date->setTime(intval(substr($value, 11, 2)), intval(substr($value, 14, 2)), intval(substr($value, 17, 2)));
   return $date;
  }
  return $def;
 }

 public static function mapWhere($key, $value)
 {
  return $key . '=' . $value;
 }

 /**
  * Make an SQL WHERE clause text
  * @param type $where
  * @return string String SQL WHERE clause or assoc_array
  */
 public static function makeWhere($where)
 {
  if (!is_array($where))
   return '' . $where;
  if (!count($where))
   return '';
  return implode(' and ', array_map(array(__CLASS__, 'mapWhere'), array_keys($where), $where));
 }

 /**
  * Add an SQL WHERE clause text to the SQL text
  * @param out ref string $sql SQL text to modify
  * @param string $where String SQL WHERE clause or assoc_array
  */
 private static function addWhere(&$sql, $where)
 {
  $where = self::makeWhere($where);
  if ($where)
   $sql .= ' where ' . $where;
 }

 /**
  * Make an SQL query text
  * @param string $table Database table name
  * @param string $fields An asterisk sign or comma-separated list of the field names to retrieve
  * @param mixed $where String SQL WHERE clause or assoc_array
  * @param string $order String SQL ORDER BY clause
  * @return string SQL query text
  */
 public function makeQuerySelect($table, $fields = null, $where = null, $order = null)
 {
  $sql = 'select ' . ($fields ? $fields : '*');
  if (strlen(trim($table)))
  {
   $sql .= ' from ' . $table;
   self::addWhere($sql, $where);
   if (strlen(trim($order)))
    $sql .= ' order by ' . $order;
  }
  self::addQuery($sql);
  return $sql;
 }

 /**
  * Retrieve data records from the database in assoc array(key => value)
  * @param string $table Database table names comma-separated list
  * @param string $key Database table key field name
  * @param string $field Database table value field name
  * @param string $where SQL WHERE clause
  * @param string $order SQL ORDER BY clause
  * @param int $limit Maximum number of records to retrieve
  * @param int $skip Number of records to skip at the beginning
  * @return array Non-empty records array or null
  */
 public function queryArray($table, $key, $field, $where = null, $order = null, $limit = null, $skip = null)
 {
  //echo "queryArray('$table','$key','$field','$where','$order','$limit','$skip')<br>\n";
  $result = null;
  $sql = $this->makeQuerySelect($table, $key . ',' . $field, $where, $order);
  if ($this->real_query($sql) && ($records = $this->use_result()))
  {
   while ($record = $records->fetch_row())
   {
    if (is_int($skip) && ($skip-- > 0))
     continue;
    if (is_int($limit) && (--$limit < 0))
     break;
    if ($result == null)
     $result = array();
    $result[$record[0]] = $record[1];
   }
   $records->close();
  }
  return $result;
 }

 /**
  * Retrieve data records from the database in 2D assoc_array(key => array(field => value)) or array(value)
  * @param string $table Database table names comma-separated list
  * @param string $fields Database field names comma-separated list (the first is the key)
  * @param string $where SQL WHERE clause
  * @param string $order SQL ORDER BY clause
  * @param int $limit Maximum number of records to retrieve
  * @param int $skip Number of records to skip at the beginning
  * @return array Non-empty records array or null
  */
 public function queryMatrix($table, $fields, $where = null, $order = null, $limit = null, $skip = null)
 {
  //echo "queryMatrix('$table','$fields','$where','$order','$limit','$skip')<br>\n";
  $result = null;
  $names = explode(',', $fields);
  $sql = $this->makeQuerySelect($table, $fields, $where, $order);
  if ($this->real_query($sql) && ($records = $this->use_result()))
  {
   while ($record = $records->fetch_row())
   {
    if (is_int($skip) && ($skip-- > 0))
     continue;
    if (is_int($limit) && (--$limit < 0))
     break;
    $row = array();
    if (count($names) == 2)
     $row = $record[1];
    else
     for ($i = 1; $i < count($names); $i++)
      $row[$names[$i]] = $record[$i];
    if ($result == null)
     $result = array();
    $result[$record[0]] = $row;
   }
   $records->close();
  }
  return $result;
 }

 /**
  * Retrieve data records from the database
  * @param string $table Database table names comma-separated list
  * @param string $fields Database field names comma-separated list
  * @param string $where SQL WHERE clause
  * @param string $order SQL ORDER BY clause
  * @param int $limit Maximum number of records to retrieve
  * @param int $skip Number of records to skip at the beginning
  * @return array Non-empty records array or null
  */
 public function queryRecords($table, $fields = null, $where = null, $order = null, $limit = null, $skip = null)
 {
  //echo "queryRecords('$table','$fields','$where','$order','$limit','$skip')<br>\n";
  $result = null;
  $sql = $this->makeQuerySelect($table, $fields, $where, $order);
  if ($this->real_query($sql) && ($records = $this->use_result()))
  {
   while ($record = $records->fetch_row())
   {
    if (is_int($skip) && ($skip-- > 0))
     continue;
    if (is_int($limit) && (--$limit < 0))
     break;
    if ($result == null)
     $result = array();
    $result[] = $record;
   }
   $records->close();
  }
  return $result;
 }

 /**
  * Retrieve data records from the database in 2D array(array(field => value)) or array(value)
  * @param string $table Database table names comma-separated list
  * @param string $fields Database field names comma-separated list
  * @param string $where SQL WHERE clause
  * @param string $order SQL ORDER BY clause
  * @param int $limit Maximum number of records to retrieve
  * @param int $skip Number of records to skip at the beginning
  * @return array Non-empty records array or null
  */
 public function queryArrays($table, $fields = null, $where = null, $order = null, $limit = null, $skip = null)
 {
  //echo "queryRecords('$table','$fields','$where','$order','$limit','$skip')<br>\n";
  $result = null;
  $sql = $this->makeQuerySelect($table, $fields, $where, $order);
  if ($this->real_query($sql) && ($records = $this->use_result()))
  {
   while ($record = $records->fetch_array(MYSQLI_ASSOC))
   {
    if (is_int($skip) && ($skip-- > 0))
     continue;
    if (is_int($limit) && (--$limit < 0))
     break;
    if ($result == null)
     $result = array();
    $result[] = (count($record) == 1) ? $record[$fields] : $record;
   }
   $records->close();
  }
  return $result;
 }

 /**
  * Get field values from the single record
  * @param string $table Database table name
  * @param string $fields Comma-separated field list
  * @param string $where SQL filter expression
  * @param string $order SQL sort order expression
  * @return array Specified field values
  */
 public function queryFields($table, $fields = null, $where = null, $order = null)
 {
  $result = null;
  $sql = $this->makeQuerySelect($table, $fields, $where, $order);
  if ($this->real_query($sql) && ($records = $this->store_result()))
  {
   $result = $records->fetch_row();
   $records->free();
  }
  return $result;
 }

 /**
  * Get field values from the database table
  * @param string $table Database table name
  * @param string $fields Comma-separated field list
  * @param string $where SQL filter expression
  * @param string $order SQL sort order expression
  * @return array Specified field values
  */
 public function queryPairs($table, $fields = null, $where = null, $order = null)
 {
  $result = null;
  $sql = $this->makeQuerySelect($table, $fields, $where, $order);
  if ($this->real_query($sql) && ($records = $this->store_result()))
  {
   $result = $records->fetch_array(MYSQLI_ASSOC);
   $records->free();
  }
  return $result;
 }

 /**
  * Get a field value
  * @param string $table Database table name
  * @param string $field Database table field name
  * @param string $where Database table filter value
  * @param string $order Records sort order
  * @return mixed Field value or null
  */
 public function queryField($table, $field, $where = null, $order = null)
 {
  $result = null;
  $sql = $this->makeQuerySelect($table, $field, $where, $order);
  if ($this->real_query($sql) && ($cursor = $this->use_result()) && ($record = $cursor->fetch_row()))
   $result = $record[0];
  return $result;
 }

 public function embedFieldData($table, $field, $where, $mimetype = null)
 {
  $data = $this->queryField($table, $field, $where);
  return HTTP::embedData($data, $mimetype);
 }

 public function embedFieldImage($table, $field, $where, $mimetype = null, $class = null, $style = null, $attrs = null)
 {
  $data = $this->queryField($table, $field, $where);
  return HTTP::embedImage($data, $mimetype, $class, $style, $attrs);
 }

 /*public function embedFile($table, $field, $where, $mimetype)
 {
  $data = $this->queryField($table, $field, $where);
  return HTTP::embedData($data, $mimetype);
 }*/

 /**
  * Download file
  * @param string $table Database table name
  * @param string $field Database table field name
  * @param string $where Database table filter (SQL WHERE clause)
  * @param string $filename File name
  * @param string $mimetype File data MIME type
  * @return bool Result of the operation
  */
 public function downloadFile($table, $field, $where, $filename, $mimetype)
 {
  $record = $this->queryPairs($table, null, $where);
  if (is_null($record) || !array_key_exists($field, $record))
   return false;
  $content = $record[$field];
  if (is_null($content))
   return false;
  // Is $filename a field name?
  if ((strpos($filename, '.') === false) && array_key_exists($filename, $record))
  {
   $value = $record[$filename];
   if (strpos($value, '.') !== false)
    $filename = $value;
  }
  // Is $mimetype a field name?
  if ((strpos($mimetype, '/') === false) && array_key_exists($mimetype, $record))
  {
   $value = $record[$mimetype];
   if (strpos($value, '/') !== false)
    $mimetype = $value;
  }
  Base::downloadFile($content, $filename, $mimetype);
  return true;
 }

 /**
  * Change the values of the database table fields
  * @param string $table Database table name
  * @param assoc_array $values Field names and their values as named pairs
  * @param string $where Database predicate (where clause)
  * @return bool Result of the operation
  */
 public function modifyFields($table, array $values, $where = null)
 {
  $sql = 'update ' . $table . ' set ';
  $comma = '';
  foreach ($values as $field => $value)
  {
   $sql .= $comma . $field . '=' . $value;
   $comma = ',';
  }
  self::addWhere($sql, $where);
  self::addQuery($sql);
  return $this->query($sql);
 }

 /**
  * Change the value of the database table field
  * @param string $table Database table name
  * @param string $field Database table field name
  * @param string $type Database table field type name
  * @param string $value Database table field value
  * @param string $where Database predicate (where clause)
  * @return bool Result of the operation
  */
 public function modifyField($table, $field, $type, $value, $where = null)
 { // $type: 'i' - integer, 'd' - double, 's' - string, 'b' - blob
  $result = false;
  $sql = 'update ' . $table . ' set ' . $field . '=?';
  self::addWhere($sql, $where);
  //echo "\n/*{$sql}*/<br>\n[$type]($value)\n";
  if (($stmt = $this->prepare($sql)))
  {
   $stmt->bind_param($type, $value); ///< http://www.php.net/manual/en/mysqli-stmt.bind-param.php
   $result = $stmt->execute();
   $stmt->close();
  }
  //echo $sql;
  self::addQuery($sql);
  return $result;
 }

 /**
  * Append a new record to database table
  * @param string $table Database table name
  * @param assoc_array $values Field names and their values as named pairs
  * @param serial boolean Modify serial after insert
  * @return bool Result of the operation
  */
 public function insertValues($table, array $values, $serial = false)
 {
  $sqlFields = '';
  $sqlValues = '';
  $comma = '';
  foreach ($values as $field => $value)
  {
   $sqlFields .= $comma . $field;
   $sqlValues .= $comma . $value;
   $comma = ',';
  }
  $sql = 'insert into ' . $table . ' (' . $sqlFields . ') values (' . $sqlValues . ')';
  self::addQuery($sql);
  $result = $this->query($sql);
  //echo Base::htmlComment(self::lastQuery(), true);
  if ($result && $serial)
   $this->modifySerialAfterInsert($table);
  return $result;
 }

 /**
  * Update the 'serial' field of the last inserted record by the 'id' value
  * @param string $table Database table name
  * @param int $id Record key value
  */
 public function modifySerialAfterInsert($table, $id = null)
 {
  if (!$id)
   $id = $this->insert_id;
  $this->modifyField($table, 'serial', 'i', $id, 'id=' . $this->insert_id);
 }

 /**
  * Delete records from the database table
  * @param string $table Database table name
  * @param string $where Database predicate (where clause)
  * @return bool Result of the operation
  */
 public function deleteRecords($table, $where = null)
 {
  $sql = 'delete from ' . $table;
  self::addWhere($sql, $where);
  //echo $sql;
  self::addQuery($sql);
  return $this->query($sql);
 }

 public static function uploadFields($contents, $filename = null, $mimetype = null, $width = null, $height = null, $size = null)
 {
  $result = array();
  if(isset($contents))
   $result['contents'] = $contents;
  if(isset($filename))
   $result['filename'] = $filename;
  if(isset($mimetype))
   $result['mimetype'] = $mimetype;
  if(isset($width))
   $result['width'] = $width;
  if(isset($height))
   $result['height'] = $height;
  if(isset($size))
   $result['size'] = $size;
  return $result;
 }

 private static function extractFieldName($field, array &$fields)
 {
  if (!array_key_exists($field, $fields))
   return null;
  $result = $fields[$field];
  if ($result === '')
   $result = $field;
  return $result;
 }

  /**
  * Get the uploaded file contents from the HTTP server
  * @param string $name HTTP parameter name
  * @param assoc_array $fields Values 'filename', 'mimetype', 'width', 'height' and 'contents' content correspondent field names
  * @return assoc_array Uploaded file data in key-value pairs
  */
 public function uploadFileData($name, array $fields)
 {
  // http://www.php.net/manual/ru/features.file-upload.post-method.php
  // http://www.php.su/articles/?cat=protocols&page=006
  // http://php.spb.ru/php/image.html
  if (!strlen($name) || ($_FILES[$name]['size'] == 0))
   return null;
  $tmp_name = $_FILES[$name]['tmp_name'];
  if (!strlen($tmp_name))
   return null;
  $fieldContents = self::extractFieldName('contents', $fields);
  if (!strlen($fieldContents))
   return null;
  $file = fopen($tmp_name, 'rb');
  if ($file == null)
   return null;
  $contents = fread($file, filesize($tmp_name));
  fclose($file);
  $values = array($fieldContents => self::str($contents));
  // File name
  $fieldFilename = self::extractFieldName('filename', $fields);
  $filename = $_FILES[$name]['name'];
  if (strlen($fieldFilename) && strlen($filename))
   $values[$fieldFilename] = self::str($filename);
  // File size
  $fieldSize = self::extractFieldName('size', $fields);
  $size = $_FILES[$name]['size'];
  if (strlen($fieldSize) && strlen($size))
   $values[$fieldSize] = $size;
  // Mime type
  $fieldMimetype = self::extractFieldName('mimetype', $fields);
  $mimetype = $_FILES[$name]['type'];
  if (strlen($fieldMimetype) && strlen($mimetype))
   $values[$fieldMimetype] = self::str($mimetype);
  // Image width and height
  $fieldWidth = self::extractFieldName('width', $fields);
  $fieldHeight = self::extractFieldName('height', $fields);
  if(strlen($fieldWidth) || strlen($fieldHeight))
  { // http://www.php.su/getimagesize
   $size = getimagesize($tmp_name);
   if(strlen($fieldWidth))
    $values[$fieldWidth] = $size ? $size[0] : 0;
   if(strlen($fieldHeight))
    $values[$fieldHeight] = $size ? $size[1] : 0;
  }

  return $values;
 }

 public function mergeField($table, $field, $value, $where)
 {
  $values = array($field => $value);
  return $this->mergeFields($table, $values, $where);
 }

 public function mergeFields($table, $values, $where)
 {
  if ($this->queryField($table, '1', $where) == null)
  {
   if (!is_array($where) || !$this->insertValues($table, $where))
    return false;
  }
  return $this->modifyFields($table, $values, $where);
 }

 /**
  * Upload the file to the database
  * @param string $name HTTP parameter name
  * @param string $table Database table name
  * @param array $fields Values 'filename', 'mimetype', 'width', 'height' and 'contents' content correspondent field names
  * @param string $where Database predicate (where clause)
  * @return bool Result of the operation
  */
 public function uploadFile($name, $table, array $fields, $where)
 {
  $values = $this->uploadFileData($name, $fields);
  if ($values == null)
   return false;
  return $this->mergeFields($table, $values, $where);
 }
}

?>
