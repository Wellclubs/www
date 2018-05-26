<?php

/**
 * Description of GPlaces
 */
class GPlaces
{
 // https://developers.google.com/places/web-service/autocomplete
 // https://developers.google.com/places/web-service/intro
 // https://maps.googleapis.com/maps/api/place/autocomplete/json?key=AIzaSyDpBcf3YKdHAFMnuaWIlcgYt-sm80aoe98&input=Dubai,%20United%20Arab%20Emirates

 //const API_KEY = 'AIzaSyD4DTY-Ckhwl6RG2cHC4343aNG00tq1_pk';//AG
 const API_KEY = 'AIzaSyDpBcf3YKdHAFMnuaWIlcgYt-sm80aoe98';//WC
 const URI1 = 'https://maps.googleapis.com/maps/api/place/';
 const URI2 = '/json?sensor=false'; // &language=ru
 const URI3 = '&location=0,0&radius=20000000';

 private static function makeURI($service, $params)
 {
  $lang = GAPI::paramLang();
  return self::URI1 . $service . self::URI2 . $lang . self::URI3 . '&key=' . self::API_KEY . $params;
 }

 public static function queryAutocomplete($types, $term)
 { // &components=country:ru
  return HTTP::ssl(self::makeURI('autocomplete', '&types=' . $types . '&input=' . GAPI::term($term)));
 }

 public static function queryACGeocode($term)
 {
  return self::queryAutocomplete('geocode', $term);
 }

 public static function queryACRegions($term)
 {
  return self::queryAutocomplete('(regions)', $term);
 }

 public static function queryDetails($ref)
 {
  return HTTP::ssl(self::makeURI('details', '&reference=' . $ref));
 }

 public static function queryReferences($ref)
 {
  $details = json_decode(self::queryDetails($ref));
  $status = GAPI::getField($details, 'response', 'status');
  if ($status != 'OK')
   exit('Details status: ' . $status);
  $rresult = GAPI::getField($details, 'response', 'result');

  $url = GAPI::getField($rresult, 'result', 'url');
  $geometry = GAPI::getField($rresult, 'result', 'geometry');
  $location = GAPI::getField($geometry, 'geometry', 'location');
  $viewport = GAPI::getField($geometry, 'geometry', 'viewport', true);
  if ($viewport)
  {
   $northeast = GAPI::getField($viewport, 'viewport', 'northeast');
   $southwest = GAPI::getField($viewport, 'viewport', 'southwest');
  }

  $references = array();

  $component = array();
  $component['name'] = GAPI::getField($rresult, 'result', 'name');
  $types = GAPI::getField($rresult, 'result', 'types');
  $type = $types[0];
  $component['type'] = $type;
  $component['address'] = GAPI::getField($rresult, 'result', 'formatted_address');
  $component['reference'] = GAPI::getField($rresult, 'result', 'reference');
  $references[] = $component;

  $components = GAPI::getField($rresult, 'result', 'address_components');
  for ($i = 1; $i < count($components); $i++)
  {
   $item = $components[$i];
   $comp_name = "address_components[$i]";
   $types = GAPI::getField($item, $comp_name, 'types');
   if (!array_search('political', $types))
    continue;
   $component = array();
   $name = GAPI::getField($item, $comp_name, 'long_name');
   $component['name'] = $name;
   $type = $types[0];
   $component['type'] = $type;
   $places = json_decode(self::queryACRegions(str_replace(' ', '-', $name)));
   if (!$places || !array_key_exists('status', $places) || ($places->status != 'OK'))
    continue;
   $reference = null;
   $predictions = GAPI::getField($places, 'places', 'predictions');
   foreach ($predictions as $key => $place)
   {
    $place_name = $name . ".predictions[$key]";
    $types = GAPI::getField($place, $place_name, 'types');
    //if (!is_array($types) || !array_search($type, $types))
    // continue;
    $reference = GAPI::getField($place, $place_name, 'reference');
    $address = GAPI::getField($place, $place_name, 'description');
    break;
   }
   if ($reference == null)
    continue;
   $details = json_decode(self::queryDetails($reference));
   if ($details && ($details->status == 'OK'))
   {
    $rresult = GAPI::getField($details, $comp_name . '.details', 'result');
    $address = GAPI::getField($rresult, $comp_name . '.details.result', 'formatted_address');
   }
   $component['address'] = $address;
   $component['reference'] = $reference;
   $references[] = $component;
  }

  $result = array();
  $result['references'] = $references;
  $result['lat'] = GAPI::getField($location, 'location', 'lat');
  $result['lng'] = GAPI::getField($location, 'location', 'lng');
  if ($viewport)
  {
   $result['latN'] = GAPI::getField($northeast, 'northeast', 'lat');
   $result['latS'] = GAPI::getField($southwest, 'southwest', 'lat');
   $result['lngW'] = GAPI::getField($southwest, 'southwest', 'lng');
   $result['lngE'] = GAPI::getField($northeast, 'northeast', 'lng');
  }
  $result['url'] = $url;

  return JSON::encode($result);
 }

}

?>
