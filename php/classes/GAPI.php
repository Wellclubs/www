<?php

/**
 * Description of GAPI
 */
class GAPI
{
 /**
  * Replace spaces in input term by hyphens
  * @param string $term Input term
  * @return string Result value
  */
 public static function term($term)
 {
  return str_replace(' ', '-', $term);
 }

 public static function paramLang()
 {
  $lang = '';
  if (WDomain::ok())
   $lang = WDomain::abcId();
  if (!$lang && Lang::current())
   $lang = Lang::current()->id();
  if ($lang)
   $lang = '&language=' . $lang;
  return $lang;
 }

  /**
  * Get autocomplete list for input term
  * 1st case: select a region for service select (useref = false, types = '(regions)')
  * 2nd case: select a place for centre address (useref = true, types = 'geocode')
  * @param string $term Input term
  * @param bool $geocode Use 'geocode' types instead of '(regions)'
  * @param bool $useref Usi 'reference' field instead of 'id'
  * @return array Autocomplete list ('id', 'value' pairs)
  */
 public static function acPlacesUI($term, $geocode = null, $useref = false)
 {
  if (!is_bool($geocode)) // Two-sectional list requested
  {
   $geocode = false;
   $result = array();
   $result['geocode'] = self::acPlacesUI($term, true); // Local search
   $result['regions'] = self::acPlacesUI($term, false); // Global search
   return $result;
  }
  $method = $geocode ? 'queryACGeocode' : 'queryACRegions';
  $places = GPlaces::$method($term);
  $data = json_decode($places);

  $status = self::getField($data, 'response', 'status');
  if (($status != 'OK') && ($status != 'ZERO_RESULTS'))
   exit("GAPI::acPlacesUI($term, $geocode, $useref) Query failed: $status");

  $predictions = self::getField($data, 'response', 'predictions');

  $result = array();
  foreach ($predictions as $i => $item)
  {
   $caption = "predictions[$i]";
   if (!$geocode)
    $id = self::getField($item, $caption, 'id');
   else if ($useref)
    $id = self::getField($item, $caption, 'reference');
   else if (array_search('political', self::getField($item, $caption, 'types')) !== false)
    continue;
   else if (array_search('establishment', self::getField($item, $caption, 'types')) !== false)
    continue;
   else
   {
    $ref = self::getField($item, $caption, 'reference');
    $det = self::queryDetails($ref, true);
    if (!$det)
     continue;
    $comps = self::getField($det, 'details.result', 'address_components');
    $terms = array();
    foreach ($comps as $j => $comp)
    {
     $title = "address_components[$j]";
     if (array_search('political', self::getField($comp, $title, 'types')) !== false)
      $terms[] = self::getField($comp, $title, 'long_name');
    }
    $id = self::queryFirstId(implode(', ', $terms), false);
   }
   $terms = self::getField($item, $caption, 'terms');
   foreach ($terms as $key => $term)
    $terms[$key] = self::getField($term, "terms['$key']", 'value');
   $value = implode(', ', $terms);
   $result[] = array('id' => $id, 'value' => $value);
  }

  return $result;
 }

 /**
  * Query Google Geocoding API service
  * @param string $address Address
  * @param bool optional Result data is optional
  * @return mixed Field 'results' value
  */
 public static function queryGeocoding($address, $optional = false)
 {
   $geocoding = json_decode(GGeocoding::query($address));
   $status = self::getField($geocoding, 'geocoding', 'status');
   if ($status != 'OK')
   {
    if ($optional && ($status == 'ZERO_RESULTS'))
     return null;
    exit("GAPI::queryGeocoding('$address') Query failed: $status");
   }
   return self::getField($geocoding, 'geocoding', 'results');
 }

 /**
  * Query Google Places API Details service
  * @param string $ref Place reference
  * @param bool optional Result data is optional
  * @return mixed Field 'result' value
  */
 public static function queryDetails($ref, $optional = false)
 {
   $details = json_decode(GPlaces::queryDetails($ref));
   $status = self::getField($details, 'details', 'status');
   if ($status != 'OK')
   {
    if ($optional && ($status == 'NOT_FOUND'))
     return null;
    exit("GAPI::queryDetails('$ref') Query failed: $status");
   }
   return self::getField($details, 'details', 'result');
 }

 /**
  * Get 'id' for first item in autocomplete list
  * @param string $term Input term
  * @param bool $geocode Use 'geocode' types instead of '(regions)'
  * @return string or null;
  */
 public static function queryFirstId($term, $geocode)
 {
  $places = self::acPlacesUI($term, $geocode);
  return count($places) ? self::getField($places[0], 'places[0]', 'id') : null;
 }

 /**
  * Get 'reference' for first item in autocomplete list
  * @param string $term Input term
  * @param bool $geocode Use 'geocode' types instead of '(regions)'
  * @return string or null;
  */
 public static function queryFirstRef($term, $geocode)
 {
  $places = self::acPlacesUI($term, $geocode, true);
  //echo "queryFirstRef('$term', '$geocode')\n";
  //print_r($places);
  return count($places) ? self::getField($places[0], 'places[0]', 'id') : null;
 }

 /**
  * Get details for first item in autocomplete list
  * @param string $term Input term
  * @param bool $geocode Use 'geocode' types instead of '(regions)'
  * @return assoc_array Details service response
  */
 public static function queryFirstDetails($term, $geocode)
 {
  $ref = self::queryFirstRef($term, $geocode);
  return $ref ? json_decode(GPlaces::queryDetails($ref)) : null;
 }

 /**
  * Get details for first item in autocomplete list
  * @param string $term Input term
  * @param bool $geocode Use 'geocode' types instead of '(regions)'
  * @return array of assoc_arrays ('id', 'type', 'name', 'address')
  */
 public static function queryFirstDetailsAndRegions($term, $geocode)
 { // TODO: implement querySmartDetailsAndRegions which uses 'types' for making a smart choice
  $details = null;
  $results = self::queryGeocoding($term, true);
  if ($results)
  {
   $details = $results[0]; // TODO: Stupid choice must be replaced by a smart one
  }
  if (!$details)
  {
   $ref = self::queryFirstRef($term, $geocode); // TODO: Stupid choice must be replaced by a smart one
   if ($ref)
    $details = self::queryDetails($ref, true);
  }
  if (!$details)
   return null;
  $result = self::useDetailsAndQueryRegions($details);
  return $result;
 }

 /**
  * Get details for specified reference value and nested locations list
  * @param string $ref Input reference value
  * @return assoc_array ('lat', 'lng', 'place_id', 'regions')
  */
// public static function queryDetailsAndRegions($ref)
// {
//  $details = self::queryDetails($ref);
//  $result = self::useDetailsAndQueryRegions($details);
//  return $result;
// }

 /**
  * Use details and query regions for making the info array
  * @param assoc_array $details Result geodata ('geometry', 'place_id', ...)
  * @return assoc_array ('lat', 'lng', 'place_id', 'regions')
  */
 public static function useDetailsAndQueryRegions($details)
 {
  $result = array();

  $geometry = self::getField($details, 'details', 'geometry');
  $location = self::getField($geometry, 'geometry', 'location');
  $result['lat'] = round(self::getField($location, 'location', 'lat') * 1000000);
  $result['lng'] = round(self::getField($location, 'location', 'lng') * 1000000);
  $result['place_id'] = self::getField($details, 'result', 'place_id');
  $result['regions'] = self::queryRegions($details);

  return $result;
 }

 /**
  * Get details for specified reference value
  * @param assoc_array $result Field 'result' of the Details
  * @return array of assoc_arrays ('id', 'type', 'name', 'address')
  */
 public static function queryRegions($result)
 {
  $regions = array();
  $components = self::getField($result, 'result', 'address_components');
  $ids = array();
  foreach ($components as $i => $comp)
  {
   $comp_name = "address_components[$i]";
   $types = self::getField($comp, $comp_name, 'types');
   if (!array_search('political', $types))
    continue;
   $component = array();
   $name = self::getField($comp, $comp_name, 'long_name');
   $type = $types[0];
   $where = "type='$type' and name=" . DB::str($name);
   $fields = DB::getDB()->queryFields(WCentre::TABLE_CENTRE_PLACE, 'place_id,address', $where);
   if ($fields)
   {
    $id = $fields[0];
    $addr = $fields[1];
   }
   else
   {
    $places = self::acPlacesUI($name, false);
    if (!count($places))
     continue;
    $id = self::getField($places[0], 'places[0]', 'id');
    $addr = self::getField($places[0], 'places[0]', 'value');
   }
   if (array_search($id, $ids) !== false)
    continue;
   $ids[] = $id;
   $component['id'] = $id;
   $component['type'] = $type;
   $component['name'] = $name;
   $component['addr'] = $addr;
   $regions[] = $component;
  }
  return $regions;
 }

 /**
  * Extract field value from the object
  * @param object $object Source object reference
  * @param string $caption Source object caption
  * @param string $field Field name
  * @param bool $optional True if field is optional
  * @return mixed
  */
 public static function getField(&$object, $caption, $field, $optional = false)
 {
  if (!is_array($object) && !is_object($object))
   exit("GAPI.getField(object, '$caption', '$field') error: object is neither an array nor an object: " . print_r($object, true));
  if (array_key_exists($field, $object))
   return is_array($object) ? $object[$field] : $object->$field;
  if ($optional)
   return null;
  exit('Invalid ' . $caption . ' format: "' . $field . '" element not found');
 }
}

?>