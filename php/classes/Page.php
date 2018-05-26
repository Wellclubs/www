<?php

abstract class Page
{
 private static $db;

 /**
  * Get Database connection
  * @return DB Database connection
  */
 public static function db() { return self::$db; }

 /**
  * Set Database connection
  * @param DB $db Database connection
  */
 protected static function setDB($db)
 {
  self::$db = $db;
 }

 /**
  * Init Database connection
  * @param bool $admin Use write-allowed database connection
  */
 protected static function initDB($admin = false)
 {
  self::$db = $admin ? DB::getAdminDB() : DB::getDB();
 }

 protected static $ajax = array(); // Data being sent via ajax mode
 protected static $app = array(); // Object being sent in both modes

 public static function addToAjax($name, $value, $required = false)
 {
  if (!$required)
  {
   if (is_null($value))
    return;
   if (is_string($value) && !strlen($value))
    return;
  }
  self::$ajax[$name] = $value;
 }

 public static function addDataToAjax($value, $required = false)
 {
  self::addToAjax('data', $value, $required);
 }

 public static function addToAjaxData($name, $value, $required = false)
 {
  if (!$required)
  {
   if (is_null($value))
    return;
   if (is_string($value) && !strlen($value))
    return;
  }
  if (!array_key_exists('data', self::$ajax) || !is_array(self::$ajax['data']))
   self::$ajax['data'] = array();
  self::$ajax['data'][$name] = $value;
 }

 public static function addToApp($name, $value)
 {
  self::$app[$name] = $value;
 }

 protected $modes;
 protected $indexes;

 public abstract function showPage();

 public function getDefaultMode() { return 'home'; }
 public function getDefaultPar() { return ''; }
 public function getDefaultTab() { return ''; }
 public function validatePar($par) { return false; }
 public function validateParWithIndex($par) { return false; }
 public function validateTab($tab) { return false; }

 public function validateMode($mode)
 {
  return array_search($mode, $this->modes) !== false;
 }

 public function validateModeWithIndex($mode)
 {
  return array_search($mode, $this->indexes) !== false;
 }

 public static function jsonResult($result)
 {
  return is_string($result) ? array('result' => 'Fail', 'error' => $result) : array('result' => 'OK');
 }

}

?>