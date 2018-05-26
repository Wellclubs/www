<?php

/**
 * Description of PageAdmCtrDisc
 */
class PageAdmCtrDisc
{
 const TABLE = 'com_centre_schema';
 const TABLE_INT = 'com_centre_schema_interval';

 private static function time($time)
 {
   $result = Util::str2min($time);
   if ($result >= 0)
    return $result;
   echo 'Invalid time: ' . $time;
   return false;
 }

 private static function processAct($act)
 {
  $centreId = WCentre::id();
  $table = self::TABLE;
  $entity = 'schema';
  $tableInt = self::TABLE_INT;
  $entityInt = 'interval';

  switch ($act)
  {
  case 'createSchema' :
   PageAdm::createEntity($table, $entity, null, array('centre_id' => $centreId, 'start_date' => DB::str(DB::date2str(new DateTime()))));
   break;

  case 'deleteSchema' :
   $id = intval(HTTP::param('id'));

   PageAdm::deleteEntity($table, $entity, $id);
   break;

  case 'changeSchemaSerial' :
   PageAdm::changeSerial($table, $entity);
   break;

  case 'changeSchemaName' :
   PageAdm::changeName($table, $entity, array('centre_id' => $centreId));
   break;

  case 'changeStartDate' :
   PageAdm::changeField($table, $entity, 'start_date');
   break;

  case 'changeFinalDate' :
   PageAdm::changeField($table, $entity, 'final_date');
   break;

  case 'changeSchemaGlobal' :
   PageAdm::changeFlag($table, $entity);
   break;

  case 'createInterval' :
   $schemaId = intval(HTTP::param('schema'));
   $extra = array('noname' => true, 'noserial' => true);
   PageAdm::createEntity($tableInt, $entity, null, array('schema_id' => $schemaId), null, $extra);
   break;

  case 'deleteInterval' :
   PageAdm::deleteEntity($tableInt, $entityInt);
   break;

  case 'changeStartTime' :
   $id = intval(HTTP::param('id'));
   $value = HTTP::param('value');
   $startTime = self::time($value);
   if ($startTime !== false)
   {
    PageAdm::db()->modifyField($tableInt, 'start_time', 'i', $startTime, 'id=' . $id);
    echo 'OK';
   }
   break;

  case 'changeFinalTime' :
   $id = intval(HTTP::param('id'));
   $value = HTTP::param('value');
   $finalTime = self::time($value);
   if ($finalTime !== false)
   {
    PageAdm::db()->modifyField($tableInt, 'final_time', 'i', $finalTime, 'id=' . $id);
    echo Util::min2str($finalTime);
   }
   break;

  case 'changeDiscount' :
   $id = intval(HTTP::param('id'));
   $discount = intval(HTTP::param('discount'));
   if (($discount < 0) || ($discount >= 100))
    echo 'Invalid discount: ' . $discount;
   else
   {
    PageAdm::db()->modifyField($tableInt, 'discount', 'i', $discount, 'id=' . $id);
    echo 'OK';
   }
   break;

  case 'changeCapacity' :
   $id = intval(HTTP::param('id'));
   $capacity = Util::intval(HTTP::param('capacity'));
   if ($capacity === null)
    PageAdm::db()->modifyFields($tableInt, array('capacity' => 'null'), 'id=' . $id);
   else if ($capacity >= 0)
    PageAdm::db()->modifyField($tableInt, 'capacity', 'i', $capacity, 'id=' . $id);
   else
   {
    echo 'Invalid capacity: ' . $capacity;
    break;
   }
   echo 'OK';
   break;

  case 'changeFlag' :
   PageAdm::changeFlag($tableInt, $entityInt);
   break;

  default :
   echo "Unsupported action: '$act'";
  }

  return true;
 }

 public static function showPage()
 {
  if (!WCentre::initCurrent(Base::index(), true))
   return false;
  if (array_key_exists('act', $_REQUEST))
  {
   if (self::processAct($_REQUEST['act']))
    return true;
  }
  $centreId = WCentre::id();
?><!doctype html>
<html>
<head>
<?php PageAdm::instance()->showTitle(); ?>
<style>
</style>
<script>
var entity='schema';
var entityInt=entity+' day';
var entityInt=entityInt+' interval';
function createSchema()
{
 A.createEntity(entity,'createSchema');
}
function deleteSchema(id)
{
 var rowid2='catrow2-'+id;
 if(A.deleteEntity(id,entity,'deleteSchema','schema-'+id))
  el(rowid2).parentNode.removeChild(el(rowid2));
}
function changeSchemaSerial(node,id)
{
 A.changeSerial(node,id,entity,'changeSchemaSerial')
}
function changeSchemaName(node,id)
{
 A.changeName(node,id,entity,'changeSchemaName');
}
function changeStartDate(node,id)
{
 A.changeField(node,id,entity,'start_date','changeStartDate',true);
}
function changeFinalDate(node,id)
{
 A.changeField(node,id,entity,'final_date','changeFinalDate',true);
}
function changeSchemaGlobal(node,id)
{
 A.changeFlag(node,id,entity,'global','changeSchemaGlobal')
}
function createInterval(schema)
{
 A.createEntity(entityInt,'createInterval','schema='+schema,true);
}
function deleteInterval(id)
{
 A.deleteEntity(id,entityInt,'deleteInterval','row-'+id);
}
function changeTime(node,id,field,action,reload)
{
 var oldValue=decodeHTML(node.innerHTML);
 var newValue=prompt('Input a new '+field+' for the '+entityInt+':',oldValue);
 if((newValue===null)||(newValue==='')||(newValue==oldValue))
  return;
 var params='act='+action+'&id='+id+'&value='+newValue;
 var req=new XMLHttpRequest();
 req.open("GET",A.makeURI(params),false);
 req.send(null);
 var error='Error changing the '+entityInt+' '+field+' on the server';
 if (req.status!=200)
  return alert(error);
 var ok=reload?(req.responseText=='OK'):(req.responseText.length==5);
 if(!ok)
  return alert(error+': '+req.responseText);
 if(reload)
  document.location.reload(true);
 else
  node.innerHTML=req.responseText;
}
function changeStartTime(node,id)
{
 if(changeTime(node,id,'start time','changeStartTime',true))
  document.location.reload(true);
}
function changeFinalTime(node,id)
{
 changeTime(node,id,'final time','changeFinalTime');
}
function changeDiscount(node,id)
{
 A.changeField(node,id,entityInt,'discount','changeDiscount',true);
}
function changeCapacity(node,id)
{
 A.changeField(node,id,entityInt,'capacity','changeCapacity');
}
function changeFlag(node,id,index)
{
 var field='day'+index;
 A.changeFlag(node,id,entityInt,field)
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main block">
<caption><?php echo "<a href='ctr-$centreId/'>" . PageAdm::title() . '</a>';?></caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='500'>Schema Name</th>
<th width='100'>Start Date</th>
<th width='100'>Final Date</th>
<th width='50'>Global</th>
<th width='1'><input type="button" value="Create schema" onclick="createSchema()"/></th>
</tr>
<?php
 $fields = 'id,serial,name,start_date,final_date,global';
 $schemes = PageAdm::db()->queryMatrix(self::TABLE, $fields, 'centre_id=' . $centreId, 'serial,id');
 if ($schemes)
 {
  foreach ($schemes as $id => $schema)
  {
   $serial = $schema['serial'];
   $name = htmlspecialchars($schema['name']);
   $startDate = $schema['start_date'];
   $finalDate = $schema['final_date'];
   $global = !!$schema['global'];
   echo "<tr id='schema-$id' style='background:#ddf;font-weight:bold;'>\n";
   echo "<th class='right' rowspan='2'>$id</th>\n";
   echo "<td class='right' onclick='changeSchemaSerial(this,$id)'>$serial</td>\n";
   echo "<td class='left' onclick='changeSchemaName(this,$id)'>$name</td>\n";
   echo "<td class='center' onclick='changeStartDate(this,$id)'>$startDate</td>\n";
   echo "<td class='center' onclick='changeFinalDate(this,$id)'>$finalDate</td>\n";
   echo "<td" . ($global ? " class='checked'" : null) . " onclick='changeSchemaGlobal(this,$id)'>&nbsp;</td>\n";
   echo "<th><input type='button' value='Delete schema' onclick='deleteSchema($id)'/></th>\n";
   echo "</tr>\n";

   echo "<tr id='catrow2-$id'><td class='table' colspan='6'>\n";
   $title = PageAdm::makeEntityText($id, $name);

   echo "<table class='main' width='100%'>\n";
   //echo "<caption>Schedule of the schema \"$title\"</caption>\n";
   echo "<tr>\n";
   echo "<th width='50'>Id</th>\n";
   echo "<th width='100'>Start time</th>\n";
   echo "<th width='100'>Final time</th>\n";
   echo "<th width='75'>Discount</th>\n";
   echo "<th width='75'>Capacity</th>\n";
   for ($i = 1; $i <= 7; ++$i)
    echo "<th width='50'>Day $i</th>\n";
   echo "<th width='1%'><input type='button' value='Create interval' onclick='createInterval(\"$id\")'/></th>\n";
   echo "</tr>\n";
   $fields = 'id,start_time,final_time,discount,capacity,day1,day2,day3,day4,day5,day6,day7';
   $where = 'schema_id=' . $id;// . ' and day=0';
   $intervals = PageAdm::db()->queryArrays(self::TABLE_INT, $fields, $where, 'day1,day2,day3,day4,day5,day6,day7,start_time');
   if ($intervals)
   {
    foreach ($intervals as $interval)
    {
     $id = $interval['id'];
     $start = $interval['start_time'];
     $final = $interval['final_time'];
     $discount = $interval['discount'];
     $capacity = $interval['capacity'];
     $startTime = Util::min2str($start);
     $finalTime = Util::min2str($final);
     echo "<tr id='row-$id' style='font-weight:bold;'>\n";
     echo "<td class='right'>$id</td>\n";
     echo "<td class='center' onclick='changeStartTime(this,$id)'>$startTime</td>\n";
     echo "<td class='center' onclick='changeFinalTime(this,$id)'>$finalTime</td>\n";
     echo "<td class='right' onclick='changeDiscount(this,$id)'>$discount</td>\n";
     echo "<td class='right' onclick='changeCapacity(this,$id)'>$capacity</td>\n";
     for ($i = 1; $i <= 7; ++$i)
      echo "<td" . ($interval['day' . $i] ? " class='checked'" : null) . " onclick='changeFlag(this,$id,$i)'>&nbsp;</td>\n";
     echo "<th><input type='button' value='Delete interval' onclick='deleteInterval($id,$start)'/></th>\n";
     echo "</tr>\n";
    }
   }
   echo "</table>\n";

   echo "</td></tr>\n";
  }
 }
?></table>
</body>
</html>
<?php
  return true;
 }
}
?>
