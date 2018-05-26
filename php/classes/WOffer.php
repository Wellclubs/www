<?php

/**
 * Description of WOffer
 */
class WOffer
{
 const TABLE_OFFER = 'biz_offer';

 public static function getOffers($ask_centre, $ask_brand)
 {
  $ofrs = array();
  if (WDomain::ok())
  {
   $where = 'domain_id=' . WDomain::id();
   $where .= ' and ask_centre is ' . ($ask_centre ? 'not null' : 'null');
   $where .= ' and ask_brand is ' . ($ask_brand ? 'not null' : 'null');
   $where .= ' and hidden is null';
   $fields = 'id,ask_start_date,price,currency_id';
   $rows = DB::getDB()->queryRecords(self::TABLE_OFFER, $fields, $where, 'serial,id');
   if ($rows)
    foreach ($rows as $row)
     $ofrs[] = array
     (
      'id' => $row[0],
      'name' => Lang::getDBTitle(self::TABLE_OFFER, 'offer', $row[0]),
      'ask_date' => ($row[1] != null),
      'price' => $row[2],
      'curr' => Util::nvl($row[3], WDomain::currencyId())
     );
  }
  return $ofrs;
 }

 public static function actionOfferByClient($ofrid)
 {
  return array(true);
 }
}

?>
