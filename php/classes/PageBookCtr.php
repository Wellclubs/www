<?php

/**
 * Description of PageBookCtr
 */
class PageBookCtr
{
 public static function fillResult(&$result)
 {
  $result['ctr'] = WCentre::id();
  if (WCentre::title())
   $result['ctrT'] = WCentre::title();
  $result['type'] = WCentre::typeId();
  $result['typeT'] = WCentre::typeTitle();
  if (WBrand::id())
  {
   $result['bnd'] = WBrand::id();
   $result['bndT'] = WBrand::title();
  }
  $result['addr'] = WCentre::address();
  if (strlen(WCentre::currencyId()))
   $result['curr'] = WCurrency::makeObjs(WCentre::currencyId());
  $result['descr'] = WCentre::descr() ? WCentre::descr() : WBrand::descr();
  $result['loc'] = WCentre::loc();
  //$logoInfo = WCentre::logoInfo();
  //$result['logo'] = $logoInfo['src'];
  $result['images'] = self::images();
  $result['groups'] = self::groups();
  $result['ratings'] = self::ratings();
  $result['reviews'] = self::reviews();

  self::fillResultValue($result, 'metros', WCentre::metros());
  self::fillResultValue($result, 'phones', WCentre::phones());
  self::fillResultValue($result, 'sched', WCentre::sched());
 }

 private static function fillResultValue(&$result, $name, $value)
 {
  if ($value)
   $result[$name] = $value;
 }

 public static function locURI()
 {
  return WCentre::locId() ? ('list/loc-' . WCentre::locId() . '/') : '#';
 }

 /**
  * Get images information
  * @return array: [{href,width,height}]
  */
 public static function images()
 {
  $result = array();
  if ((Base::mode() == 'ctr') || (Base::mode() == 'bnd'))
  {
   //if (Base::mode() == 'srv')
   // $result = WService::createGallery()->getImagesBook();
   if (WCentre::id())
    $result = array_merge($result, WCentre::createGallery()->getImagesBook());
   if (WBrand::id())
    $result = array_merge($result, WBrand::createGallery()->getImagesBook());
  }
  return $result;
 }

 /**
  * Get services information grouped
  * @return array: [{title,price,list:[{id,title,price,dura}]}]
  */
 public static function groups()
 {
  $groups = WCentre::listGroups();
  if (!$groups)
   return null;
  $result = array();
  foreach ($groups as $group)
  {
   $grpId = $group['id'];
   $list = array();
   $totalPriceMin = 0;
   $totalPriceMax = 0;
   $services = $group['services'];
   foreach ($services as $service)
   {
    $tips = $service['tips'];
    if (!$tips || !count($tips))
     continue;
    $srvId = $service['id'];
    $disc = WService::getMaxDisc($srvId, $grpId, WCentre::id());
    $tiplist = array();
    $tipcount = count($tips);
    foreach ($tips as $tip)
    {
     if (!array_key_exists('price', $tip))
      continue;
     $price = $tip['price'];
     if ($price < 1)
      continue;
     $fact = $disc ? WService::getPriceWithDisc($price, $disc) : $price;
     if (!$totalPriceMin || ($fact < $totalPriceMin))
      $totalPriceMin = $fact;
     if ($price > $totalPriceMax)
      $totalPriceMax = $price;
     $tipitem = array('id' => $tip['id']);
     if ($tipcount > 1)
      $tipitem['title'] = $tip['title'];
     $tipitem['price'] = Lang::strInt($price);
     if ($fact < $price)
      $tipitem['fact'] = Lang::strInt($fact);
     if (array_key_exists('duration', $tip))
     {
      $dura = $tip['duration'];
      if ($dura > 0)
       $tipitem['dura'] = $dura;
     }
     $tiplist[] = $tipitem;
    }
    if (!count($tiplist))
     continue;
    $service = array('id' => $srvId, 'title' => $service['title'], 'tips' => $tiplist);
    $list[] = $service;
   }
   $group = array('id' => $grpId, 'title' => $group['title']);
   if ($totalPriceMin)
    $group['price'] = self::interval(Lang::strInt($totalPriceMin), Lang::strInt($totalPriceMax), '', '');
   $group['list'] = $list;
   $result[] = $group;
  }
  return $result;
 }

 /**
  * Get the centre rating information
  * @return array: {facil:{count,total,ambie,staff,clean,value},distr:[5],cats:[{title,list:[{id,title,rsum,rcnt}],rated}]}
  */
 public static function ratings()
 {
  return WCentre::listRatings();
 }

 public static function reviews()
 {
  return WCentre::listReviews();
 }

 private static function interval($min, $max, $prefix, $suffix)
 {
  return ($min == $max) ? ($prefix . $min . $suffix) : ($prefix . $min . ' - ' . $max . $suffix);
 }
}

?>
