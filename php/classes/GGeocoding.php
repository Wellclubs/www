<?php

/**
 * Description of GGeocoding
 */
class GGeocoding
{
 // https://developers.google.com/maps/documentation/geocoding/intro

 const API_KEY = 'AIzaSyC-MD2B56rz7QuYunjBGERkLK_iieL5uWg';//WC
 const URI1 = 'https://maps.googleapis.com/maps/api/geocode/json?key=';
 const URI2 = '&address=';

 private static function makeURI($address)
 {
  $lang = GAPI::paramLang();
  $address = str_replace(' ', '+', $address);
  return self::URI1 . self::API_KEY . $lang . self::URI2 . $address;
 }

 public static function query($address)
 {
  return HTTP::ssl(self::makeURI($address));
 }
}

?>
