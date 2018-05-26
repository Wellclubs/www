<?php

/**
 * Description of WMaster
 */
class WMaster
{
 const TABLE_MASTER = 'com_master';

 private $id = null;
 private $centreId = null;
 private $clientId = null;
 private $levelId = null;
 private $roleId = null;
 private $jobTitle = null;
 private $serial = null;
 private $canConnect = null;
 private $forService = null;
 private $allServices = null;
 private $selByName = null;
 
 private $client;

 public function getId() { return $this->id; }
 public function getCentreId() { return $this->centreId; }
 public function getClientId() { return $this->clientId; }
 public function getLevelId() { return $this->levelId; }
 public function getRoleId() { return $this->roleId; }
 public function getJobTitle() { return $this->jobTitle; }
 public function getSerial() { return $this->serial; }
 public function getCanConnect() { return $this->canConnect; }
 public function getForService() { return $this->forService; }
 public function getAllServices() { return $this->allServices; }
 public function getSelByName() { return $this->selByName; }

 public function __construct($id)
 {
  $fields = 'centre_id,client_id,level_id,role_id,job_title,serial,can_connect,for_service,all_services,sel_by_name';
  $values = DB::getDB()->queryPairs(self::TABLE_MASTER, $fields, 'id=' . $id);
  if (!$values)
   return;
  $this->id = $id;
  $this->centreId = $values['centre_id'];
  $this->clientId = $values['client_id'];
  $this->levelId = $values['level_id'];
  $this->roleId = $values['role_id'];
  $this->jobTitle = $values['job_title'];
  $this->serial = $values['serial'];
  $this->canConnect = $values['can_connect'];
  $this->forService = $values['for_service'];
  $this->allServices = $values['all_services'];
  $this->selByName = $values['sel_by_name'];
 }

 public function getClient()
 {
  if (!$this->client)
   $this->client = new WClient($this->clientId);
  return $this->client;
 }

 public function getLevelName()
 {
  return WBrand::getLevelTitle($this->levelId);
 }

 public function getRoleName()
 {
  return WPriv::getRoleTitle($this->roleId);
 }
}

?>
