<?php

/**
 * Description of XSiteMap
 * http://blog.aweb.ua/sitemap-xml/
 * http://site-on.net/optimization/6-sitemap
 * Maximum 50000 URLs in one file
 */
class XSiteMap
{
 const MAX_URLS = 10000;

 private static $kinds;

 public static function processURL($url)
 {
  if (strlen($url) < 10)
   return false;
  if ($url == '/robots.txt')
   return self::fileRobots();
  self::initKinds();
  if ($url == '/sitemap.xml')
   return self::fileSitemap();
  if (fnmatch('/sitemap-*-*.xml', $url))
   return self::fileSitemapKind(substr($url, 1));
  return false;
 }

 private static function initKinds()
 {
  $domain_id = WDomain::id();
  $where = 'hidden is null' . (is_null($domain_id) ? '' : (' and domain_id=' . $domain_id));
  self::$kinds = array
  (
   'main' => array('urls' => array('about', 'biz', 'faq', 'policy')),
   'ctrs' => array('url' => 'ctr', 'table' => WCentre::TABLE_CENTRE, 'where' => $where),
   'srvs' => array('url' => 'srv', 'table' => WService::TABLE_SRV, 'where' => "centre_id in (select id from com_centre where $where)"),
   'bnds' => array('url' => 'bnd', 'table' => WBrand::TABLE_BRAND, 'where' => "exists (select null from com_centre where brand_id=com_brand.id and $where)"),
   'clts' => array('url' => 'clt', 'table' => WClient::TABLE_CLIENT)
  );
 }

 private static function fileRobots()
 {
  $host = Base::pro() . Base::host();
  echo "User-agent: *\n";
  echo "Sitemap: {$host}/sitemap.xml \n";
  return true;
 }

 private static function fileSitemap()
 {
  Lang::initialize();
  $langs = Lang::map();
  $prefix = '<sitemap><loc>' . Base::pro() . Base::host() . '/sitemap-';
  $suffix = '.xml</loc></sitemap>' . "\n";
  // Make a file content
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
  foreach ($langs as $lang => $Lang)
  {
   foreach (self::$kinds as $kind => $Kind)
   {
    $count = 0;
    if (array_key_exists('table', $Kind))
     $count = DB::getDB()->queryField($Kind['table'], 'count(*)', Util::item($Kind, 'where'));
    if ($count > self::MAX_URLS)
    {
     $index = 0;
     while ($index * self::MAX_URLS < $count)
      echo $prefix . $kind . '-' . $lang . '-' . (++$index) . $suffix;
    }
    else
     echo $prefix . $kind . '-' . $lang . $suffix;
   }
  }
  echo "</sitemapindex>\n";
  return true;
 }

 private static function fileSitemapKind($filename)
 {
  $kind = substr($filename, 8, strpos($filename, '-', 8) - 8);
  if (!array_key_exists($kind, self::$kinds))
   return false;
  Lang::initialize();
  $langs = Lang::map();
  $lang = substr($filename, strlen($kind) + 9, 2);
  if (!array_key_exists($lang, $langs))
   return false;
  $index = 0;
  if ($filename != "/sitemap-$kind-$lang.xml")
  {
   $offset = strlen($kind) + 12;
   $index = intval(substr($filename, $offset, strlen($filename) - $offset - 4));
  }
  // Make a file content
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
  $langFolder = ($lang == Lang::DEF()) ? '' : ($lang . '/');
  $Kind = self::$kinds[$kind];
  $urls = array_key_exists('urls', $Kind) ? $Kind['urls'] : self::getURLs($Kind, $index);
  $prefix = '<url><loc>' . Base::bas() . $langFolder;
  $suffix = '/</loc></url>' . "\n";
  foreach ($urls as $url)
   echo $prefix . $url . $suffix;
  //echo "<!-- " . DB::lastQuery() . " -->\n";
  echo "</urlset>\n";
  return true;
 }

 private static function getURLs($Kind, $index)
 {
  $result = array();
  $url = $Kind['url'];
  $table = $Kind['table'];
  $where = Util::item($Kind, 'where');
  $skip = ($index > 0) ? (($index - 1) * self::MAX_URLS) : null;
  $rows = DB::getDB()->queryRecords($table, 'id', $where, '1', self::MAX_URLS, $skip);
  if ($rows != null)
   foreach ($rows as $row)
    $result[] = $url . '-' . $row[0];
  return $result;
 }
}

?>
