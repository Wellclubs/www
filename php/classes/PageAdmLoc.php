<?php

/**
 * Description of PageAdmLoc
 */
class PageAdmLoc
{
 private static function processAct($act)
 {
  switch ($act)
  {
  case 'queryACGeocode' :
   $term = HTTP::get('term');
   echo GPlaces::queryACGeocode($term);
   break;

  case 'queryACPlacesUI' :
   $term = HTTP::get('term');
   echo JSON::encode(GAPI::acPlacesUI($term, true, true));
   break;

  case 'queryACRegions' :
   $term = HTTP::get('term');
   echo GPlaces::queryACRegions($term);
   break;

  case 'queryACRegionsUI' :
   $term = HTTP::get('term');
   echo JSON::encode(GAPI::acPlacesUI($term));
   break;

  case 'queryFirstDetails' :
   $term = HTTP::get('term');
   echo JSON::encode(GAPI::queryFirstDetails($term, true));
   break;

  case 'queryFirstRegions' :
   $term = HTTP::get('term');
   echo JSON::encode(GAPI::queryFirstDetailsAndRegions($term, true));
   break;

  case 'queryDetails' :
   $term = HTTP::get('term');
   echo GPlaces::queryDetails($term);
   break;

  case 'queryRegions' :
   $term = HTTP::get('term');
   $details = GAPI::queryDetails($term);
   echo JSON::encode(GAPI::useDetailsAndQueryRegions($details));
   break;

  case 'queryReferences' :
   $term = HTTP::get('term');
   echo GPlaces::queryReferences($term);
   break;

  case 'requeryLocations' :
   PageAdm::db()->modifyFields(WCentre::TABLE_CENTRE, array('lat' => 'null', 'lng' => 'null'), 'lat=0 and lng=0');
   //To be continued at the next case ('queryLocations')

  case 'queryLocations' :
   echo '<!doctype html><html><head><meta charset="utf-8"/></head><body>';
   echo "Press &lt;Back&gt; button after the procedure finishes<br><br>\n";
   $records = PageAdm::db()->queryRecords(WCentre::TABLE_CENTRE, 'id,address', 'length(address)>0 and lat is null', 'id');
   if ($records)
   {
    $error = false;
    foreach ($records as $record)
    {
     $id = $record[0];
     $address = htmlspecialchars($record[1]);
     echo "<a href='../ctr-$id/' target='_blank'>ID $id</a>: ";
     if (WCentre::queryLocation($id))
     {
      $fields = PageAdm::db()->queryFields(WCentre::TABLE_CENTRE, 'lat,lng', 'id=' . $id);
      $lat = $fields[0];
      $lng = $fields[0];
      if ($lat || $lng)
       echo "OK ($lat / $lng]])<br>\n";
      else
       echo "Location not found, check the address: '$address'<br>\n";
      continue;
     }
     echo "FAIL<br>\n";
     $error = true;
     break;
    }
    echo '<br>Finish: ' . ($error ? 'stop on error' : 'successful completion');
   }
   else
    echo 'Finish: Nothing to do!';
   echo '</body></html>';
   break;

  default :
   echo "Unsupported action: '$act'";
  }

  return true;
 }

 public static function showPage()
 {
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
#edit-addr,#edit-ref {width:99.5%}
</style>
<script>
function query(src,mode)
{
 el('memo-addr').innerHTML='';
 el('memo-ref').innerHTML='';
 var term=el('edit-'+src).value;
 if(!term.length)
  return;
 var req=new XMLHttpRequest();
 req.open("GET",document.location+'?act=query'+mode+'&term='+term,false);
 req.send(null);
 if (req.status!=200)
  return alert('Error executing the request on the server: status '+req.status);
 if(!req.responseText.length)
  return alert('No response');
 if('{['.indexOf(req.responseText[0])<0)
  return alert('Invalid response:\n'+req.responseText);
 el('memo-'+src).innerHTML=req.responseText;
}
function queryAddr(btn)
{
 query('addr',btn.value);
}
function queryRef(btn)
{
 query('ref',btn.value);
}
function queryLocations(restart)
{
 if(confirm('This action may take a long time, are you sure to start?'))
  document.location=document.location+'?act='+(restart?'re':'')+'queryLocations';
}
window.onload=function()
{
 el('edit-addr').focus();
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>

Input address:<br/>
<input id="edit-addr"
value="москва красная площадь"
onkeydown="if(event.keyCode==13)query('addr','ACGeocode')"/>

<table class="main" width="100%" border="1"><tr><th>
Direct Google Places API queries</th><th>
Same queries preprocessed for using in UI</th></tr><tr><td>
<input type="button" value="ACGeocode" onclick="queryAddr(this)"/>
Query for specified address</td><td>
<input type="button" value="ACPlacesUI" onclick="queryAddr(this)"/>
Autocomplete for centre location search</td></tr><tr><td>
<input type="button" value="ACRegions" onclick="queryAddr(this)"/>
Query for specified REGION (not address!)</td><td>
<input type="button" value="ACRegionsUI" onclick="queryAddr(this)"/>
Autocomplete for centre filtering by location</td></tr><tr><td>
<input type="button" value="FirstDetails" onclick="queryAddr(this)"/>
Query for details about specified address (using 1st prediction)</td><td>
<input type="button" value="FirstRegions" onclick="queryAddr(this)"/>
Searching for coordinates and nested locations</td></tr>
</table>

<pre id="memo-addr"></pre>

Input reference:<br/>
<input id="edit-ref"
value="CoQBgAAAAKdfcha3mNel441C2DiVmMCuMBQcRVkw_5ps5P7R4LKcw1UNUmSEbI209ok-TAZq_1mMaT6SDPV6ym8tM7reIEZ_ZMlmqy83GPG7AATnXv3sVD5q-1CYzJCp6mS_39BHNkYvIhmnxi2IMpYEKpQ0Apu3cOwNawTmEPvLhY4wiT-OEhCYweaSe0P-aj0C7ox0yYrvGhRacNS0nD1QXZFVwwLqGgJBdZCw4g"
onkeydown="if(event.keyCode==13)query('ref','Details')"/>

<table class="main" width="100%" border="1"><tr><td>
<input type="button" value="Details" onclick="queryRef(this)"/>
Direct Google Places API query for Details using reference</td></tr><tr><td>
<input type="button" value="Regions" onclick="queryRef(this)"/>
Searching coordinates and nested locations using reference</td></tr><tr><td>
<input type="button" value="References" onclick="queryRef(this)"/>
Old method for searching coordinates and nested locations</td></tr>
</table>

<pre id="memo-ref"></pre>

<table class="main" width="100%" border="1"><tr><th>
These buttons start searching coordinates and nested locations for all centres having an address but not having a location</th></tr><tr><td>
<input type="button" value="Continue previous attempt" onclick="queryLocations(false)"/>
This button continues searching from the last stop point</td></tr><tr><td>
<input type="button" value="Start from the beginning" onclick="queryLocations(true)"/>
This button resets negative results and starts searching again</td></tr>
</table>

</body>
</html>
<?php
  return true;
 }
}
?>
