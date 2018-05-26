<?php

/**
 * Description of PageBookBnd
 */
class PageBookBnd
{
 public static function fillResult(&$result)
 {
  $result['bnd'] = WBrand::id();
  $result['bndT'] = WBrand::title();
  //$result['logo'] = WBrand::logoURI();
  $result['images'] = self::images();
  $result['descr'] = WBrand::descr();
 }
}
?>
