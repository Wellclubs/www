<?php

class XPaySrvcPaytabs extends XPaySrvc
{
 private static $merchant_id = 'natalie.koroleva@gmail.com';
 private static $merchant_password = 'wellclubspaytabs';

 //private $api_key, $p_id, $payment_url;

 public function initialize()
 {
  $response = $this->callAuth();
  if (!$response)
   return $this->error('No response');
  if (Util::item($response, 'access') != 'granted')
  {
   $error = Util::item($response, 'error_code');
   if ($error == '0001')
    return $this->error('Invalid username or password');
   return $this->error('Access denied. Code ' . $error);
  }
  $this->api_key = Util::item($response, 'api_key');
  if (is_null($this->api_key))
   return $this->error('No API key');
  return true;
 }

 public function createPage($values)
 {
  $response = $this->callPage($values);
  if (!$response)
   return $this->error('No response');

  if (Util::item($response, 'response') != '10')
  {
   $error = Util::item($response, 'error_code');
   if ($error == '0002')
    return $this->error('API Key not valid');
   if ($error == '0404')
    return $this->error('You don\'t have permissions to create an Invoice');
   return $this->error('Page creation failed. Code ' . $error);
  }

  $this->p_id = Util::item($response, 'p_id');
  if (is_null($this->p_id))
   return $this->error('No payment ID');

  $this->payment_url = Util::item($response, 'payment_url');
  if (is_null($this->payment_url))
   return $this->error('No payment URL');

  $this->result = Util::item($response, 'result');
  $api_key = Util::item($response, 'api_key');
  if (!is_null($api_key))
   $this->api_key = $api_key;

  return true;
 }

 protected function callAuth()
 {
  $params = array('merchant_id' => self::$merchant_id, 'merchant_password' => self::$merchant_password);
  $response = HTTP::ssl('https://www.paytabs.com/api/authentication', $params, true);
  //echo $response;
  return json_decode($response);
 }

 /*
  * Create Pay Page
  * This method uses the API Key obtained from the authentication API Call to validate the
  * request. This method will accept all the parameters required to create a PayPage and then
  * return the response as well as the link where the customer can enter the credit card
  * information and make the payment.
  * @param $values assoc_array Array of parameter values:
  * 'client_id', 'email', 'first_name', 'last_name',
  * 'phone', 'address', 'state', 'city', 'country', 'postal_code',
  * 'invoice', 'title', 'amount', 'currency', 'return_url'
  */
 protected function callPage($values)
 {
  if (is_null($this->api_key) && !$this->initialize())
   return false;
  // http://userpage.chemie.fu-berlin.de/diverse/doc/ISO_3166.html
  // http://www.xe.com/iso4217.php
  $params = array
  (
   'api_key' => $this->api_key ///< API Key received from authentication API call or a valid API key
  ,'cc_first_name' => Util::item($values, 'first_name') ///< First Name of the Customer
   ///< 32 characters. E.g.: John
  ,'cc_last_name' => Util::item($values, 'last_name') ///< Last Name of the Customer
   ///< 32 characters. E.g.: Smith
  ,'phone_number' => Util::item($values, 'phone') ///< Phone Number of the Customer
   ///< 32 characters. E.g.: 9733312345678
  ,'billing_address' => Util::item($values, 'address') ///< Complete Address of the customer.
   ///< Multiple address lines will be merged into one single line.
   ///< 64 characters. E.g.: Flat 11 Building 222 Block 333 Road 444 Manama Bahrain
  ,'state' => Util::item($values, 'state') ///< State (part of the address) entered by the customer
   ///< 32 characters. E.g.: Manama
  ,'city' => Util::item($values, 'city') ///< Name of the city selected by customer
   ///< 3-4 characters. E.g.: Manama
  ,'postal_code' => Util::item($values, 'postal_code') ///< Postal code provided by the customer
   ///< Up to 9 characters. E.g.: 12345
  ,'country' => Util::item($values, 'country') ///< Country of the customer
   ///< 3 character ISO country code. E.g.: BHR
  ,'email' => Util::item($values, 'email') ///< Email of the customer
   ///< 32 characters. E.g.: customer@domain.com
  ,'amount' => Util::item($values, 'amount') ///< Amount of the transaction which should be the total Invoice amount
   ///< Up to 3 Decimal places. E.g.: 123.399
  ,'discount' => Util::item($values, 'discount') ///< Discount of the transaction
   ///< Up to 3 Decimal places. E.g.: 123.399
  ,'reference_no' => Util::item($values, 'invoice') ///< Invoice reference number
   ///< 11 characters. E.g.: Abc-5566
  ,'currency' => Util::item($values, 'currency') ///< Currency of the amount stated
   ///< 3 character ISO currency code. E.g.: BHD
  ,'title' => Util::item($values, 'title') ///< Description or title of the transaction done by the customer
   ///< 32 characters. E.g.: Order # 3321
  ,'ip_customer' => self::getIPAddress(false) ///< The client IP with which the order is placed.
   ///< 16 characters. E.g.: 123.123.12.2
  ,'ip_merchant' => self::getIPAddress(true) ///< Server IP where the order is coming from
   ///< 16 characters. E.g.: 11.11.22.22
  ,'return_url' => Util::item($values, 'return') ///< The URL to which the customer will be returned to
   ///< E.g.: http://yourwebsite.com/payment_completed.php
  ,'address_shipping' => Util::item($values, 'address') ///< Shipping address of the customer
   ///< 64 characters. E.g.: Flat abc road 123
  ,'city_shipping' => Util::item($values, 'city') ///< Shipping City of the customer
   ///< 32 characters. E.g.: Manama
  ,'state_shipping' => Util::item($values, 'state') ///< Shipping State of the customer
   ///< 32 characters. E.g.: Manama
  ,'postal_code_shipping' => Util::item($values, 'postal_code') ///< Shipping postal code of the customer
   ///< Up to 9 characters. E.g.: 403129
  ,'country_shipping' => Util::item($values, 'country') ///< Shipping country of the customer
   ///< 3 character ISO country code. E.g.: BHR
  ,'quantity' => '1' ///< Quantity of a products
   ///< 256 characters. E.g.: 1 || 2 || 3
  ,'unit_price' => Util::item($values, 'amount') ///< Unit price of the product
   ///< 256 characters. E.g.: 21.09 || 22.12 || 12.01
  ,'products_per_title' => Util::item($values, 'title') ///< Product title of the product
   ///< 256 characters. E.g.: IPhone || Samsung S5 || Samsung S4
  ,'ChannelOfOperations' => 'Services' ///< Type of Products covered by the Merchant
   ///< 32 characters. E.g.: Software or NonPhysical, Physical Goods, Travel Related Services
  ,'ProductCategory' => 'Services' ///< Broad Spectrum category of the product
   ///< 32 characters. E.g.: Electronics
  ,'ProductName' => Util::item($values, 'title') ///< Product names with “ || ” separated
   ///< 256 characters. E.g.: IPhone || Samsung S5 || Samsung S4
  ,'ShippingMethod' => 'No shipping' ///< Shipping method
   ///< 16 characters. E.g.: Cash on Delivery
  ,'DeliveryType' => 'No delivery' ///< Delivery Type
   ///< 16 characters. E.g.: Fedex
  ,'CustomerId' => Util::item($values, 'client_id') ///< Any ID Number assigned to the customer by the merchant
   ///< 16 characters. E.g.: T12112312
  //,'msg_lang' => 'English' ///< Language of the PayPage to be created
  );
  $response = HTTP::ssl('https://www.paytabs.com/api/create_pay_page', $params, true);
  //echo $response;
  return json_decode($response);
 }
}

?>
