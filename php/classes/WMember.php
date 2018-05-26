<?php

/**
 * Description of WMember
 */
class WMember
{
 const TABLE_MEMBER = 'biz_member';

 const DEFAULT_BRAND_LIMIT = 5;

 private $client = null;
 private $entered = null;
 private $file = null;
 
 private $brandLimit = null;
 private $brandCount = null;
 private $centreCount = null;
 private $activeCount = null;
 
 public function getClient() { return $this->client; }
 public function getEntered() { return $this->entered; }
 public function getBrandLimit() { return Util::nvl($this->brandLimit, self::DEFAULT_BRAND_LIMIT); }
 public function getFile() { return $this->file; }

 public function getId() { return $this->client ? $this->client->getId() : null; }

 public function __construct($client)
 {
  $id = 0;
  if (is_int($client) && ($client > 0))
  {
   $this->client = new WClient($client);
   $id = $client;
  }
  else if (is_object($client) && ($client instanceof WClient))
  {
   $this->client = $client;
   $id = $client->getId();
  }
  else
   return;

  $fields = 'entered,brand_limit,file';
  $values = DB::getDB()->queryPairs(self::TABLE_MEMBER, $fields, 'client_id=' . $id);
  if (!$values)
  {
   $this->client = null;
   return;
  }
  $this->entered = $values['entered'];
  $this->brandLimit = $values['brand_limit'];
  $this->file = $values['file'];
 }

 public function getBrandCount()
 {
  if ($this->brandCount === null)
   $this->brandCount = DB::getDB()->queryField('com_brand', 'count(*)', 'member_id=' . $this->getId());
  return $this->brandCount;
 }
 
 public function getCentreCount()
 {
  if ($this->centreCount === null)
   $this->centreCount = DB::getDB()->queryField('com_centre', 'count(*)', 'member_id=' . $this->getId());
  return $this->centreCount;
 }

 public function getActiveCentreCount()
 {
  if ($this->activeCount === null)
   $this->activeCount = DB::getDB()->queryField('com_centre', 'count(*)', 'member_id=' . $this->getId() . ' and hidden is null');
  return $this->activeCount;
 }
 
}

?>
