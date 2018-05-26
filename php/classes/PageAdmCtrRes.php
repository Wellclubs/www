<?php

/**
 * Description of PageAdmCtrRes
 */
class PageAdmCtrRes
{
 const TABLE_CAT = 'com_matcat';
 const TABLE_RES = 'com_matres';

 private static function processAct($act)
 {
  $centreId = WCentre::id();
  $tableCat = self::TABLE_CAT;
  $entityCat = 'category';
  $tableRes = self::TABLE_RES;
  $entityRes = 'resource';

  switch ($act)
  {
  case 'createMatcat' :
   PageAdm::createEntity($tableCat, $entityCat, null, array('centre_id' => $centreId));
   break;

  case 'deleteMatcat' :
   $id = intval(HTTP::param('id'));

   if (intval(PageAdm::db()->queryField($tableRes, 'count(*)', "matcat_id=$id")))
    echo ucfirst($entityCat) . " $id has some {$entityRes}s linked";
   else
    PageAdm::deleteEntity($tableCat, $entityCat, $id);
   break;

  case 'changeMatcatSerial' :
   PageAdm::changeSerial($tableCat, $entityCat);
   break;

  case 'changeMatcatName' :
   PageAdm::changeName($tableCat, $entityCat);
   break;

  case 'createMatres' :
   $catId = intval(HTTP::param('cat_id'));
   PageAdm::createEntity($tableRes, $entityRes, null, array('matcat_id' => $catId));
   break;

  case 'deleteMatres' :
   $id = intval(HTTP::param('id'));

   //if (intval(PageAdm::db()->queryField($tableRes, 'count(*)', "matcat_id=$id")))
   // echo ucfirst($entityRes) . " $id has some bookings linked";
   //else
    PageAdm::deleteEntity($tableRes, $entityRes, $id);
   break;

  case 'changeMatresSerial' :
   PageAdm::changeSerial($tableRes, $entityRes);
   break;

  case 'changeMatresName' :
   PageAdm::changeName($tableRes, $entityRes);
   break;

  case 'changeMatresCapacity' :
   PageAdm::changeField($tableRes, $entityRes, 'capacity');
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
var entityCat='category';
var entityRes='resource';
function createMatcat()
{
 A.createEntity(entityCat,'createMatcat');
}
function deleteMatcat(id)
{
 var rowid2='catrow2-'+id;
 if(A.deleteEntity(id,entityCat,'deleteMatcat','catrow-'+id))
  el(rowid2).parentNode.removeChild(el(rowid2));
}
function changeMatcatSerial(node,id)
{
 A.changeSerial(node,id,entityCat,'changeMatcatSerial')
}
function changeMatcatName(node,id)
{
 A.changeName(node,id,entityCat,'changeMatcatName');
}
function createMatres(cat)
{
 A.createEntity(entityRes,'createMatres','cat_id='+cat);
}
function deleteMatres(id)
{
 A.deleteEntity(id,entityRes,'deleteMatres','resrow-'+id);
}
function changeMatresSerial(node,id)
{
 A.changeSerial(node,id,entityRes,'changeMatresSerial')
}
function changeMatresName(node,id)
{
 A.changeName(node,id,entityRes,'changeMatresName');
}
function changeMatresCapacity(node,id)
{
 A.changeField(node,id,entityRes,'capacity','changeMatresCapacity');
}
</script>
</head>
<?php PageAdm::instance()->showBodyTop(); ?>
<table class="main block">
<caption><?php echo "<a href='ctr-$centreId/'>" . PageAdm::title() . '</a>';?></caption>
<tr>
<th width='50'>Id</th>
<th width='50'>Nr</th>
<th width='500'>Name</th>
<th width='1'><input type="button" value="Create category" onclick="createMatcat()"/></th>
</tr>
<?php
 $fields = 'id,serial,name';
 $categories = PageAdm::db()->queryMatrix(self::TABLE_CAT, $fields, 'centre_id=' . $centreId, 'serial,id');
 if ($categories)
 {
  foreach ($categories as $catId => $category)
  {
   $catSerial = $category['serial'];
   $catName = htmlspecialchars($category['name']);
   echo "<tr id='catrow-$catId' style='background:#ddf;font-weight:bold;'>\n";
   echo "<th class='right' rowspan='2'>$catId</th>\n";
   echo "<td class='right' onclick='changeMatcatSerial(this,$catId)'>$catSerial</td>\n";
   echo "<td class='left' onclick='changeMatcatName(this,$catId)'>$catName</td>\n";
   echo "<th><input type='button' value='Delete category' onclick='deleteMatcat($catId)'/></th>\n";
   echo "</tr>\n";

   echo "<tr id='catrow2-$catId'><td class='table' colspan='3'>\n";
   $catTitle = PageAdm::makeEntityText($catId, $catName);

   echo "<table class='main' width='100%'>\n";
   echo "<caption>Resources of the category \"$catTitle\"</caption>\n";
   echo "<tr>\n";
   echo "<th width='50'>Id</th>\n";
   echo "<th width='50'>Nr</th>\n";
   echo "<th width='300'>Name</th>\n";
   echo "<th width='50'>Capacity</th>\n";
   echo "<th width='1%'><input type='button' value='Create resource' onclick='createMatres(\"$catId\")'/></th>\n";
   echo "</tr>\n";
   $fields = 'id,serial,name,capacity';
   $where = 'matcat_id=' . $catId;
   $resources = PageAdm::db()->queryMatrix(self::TABLE_RES, $fields, $where, 'serial,id');
   if ($resources)
   {
    foreach ($resources as $resId => $resource)
    {
     $resSerial = $resource['serial'];
     $resName = htmlspecialchars($resource['name']);
     $capacity = $resource['capacity'];
     echo "<tr id='resrow-$resId' style='font-weight:bold;'>\n";
     echo "<th class='right'>$resId</th>\n";
     echo "<td class='right' onclick='changeMatresSerial(this,$resId)'>$resSerial</td>\n";
     echo "<td class='left' onclick='changeMatresName(this,$resId)'>$resName</td>\n";
     echo "<td class='right' onclick='changeMatresCapacity(this,$resId)'>$capacity</td>\n";
     echo "<th><input type='button' value='Delete resource' onclick='deleteMatres($resId)'/></th>\n";
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
