<?php
 
include("pChart/class/pData.class.php");
include("pChart/class/pDraw.class.php");
include("pChart/class/pImage.class.php");
include("pChart/class/pCache.class.php");

include("config.php");
include("functions.php");

$fontText = $chart_fontpath . "/Forgotte.ttf";
$fontScale = $chart_fontpath . "/Forgotte.ttf";
// date_default_timezone_set('Europe/Berlin');

// -----------------------------
// db connection

mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
mysql_select_db($mysqldb);
mysql_query("set names 'utf8'");
mysql_query("SET lc_time_names = 'de_DE'");

// parameters
// echo $Div.":".$_GET['chartDiv']." - ".$XLines.":".$_GET['chartXLines'];

if(isset($_GET['chartDiv']) && is_numeric($_GET['chartDiv']))
   $Div = htmlspecialchars($_GET['chartDiv']);
else
   $Div = 25;
   
if (isset($_GET['chartXLines']) && $_GET['chartXLines'] == true) 
   $XLines = true;
else
   $XLines = false;

if (isset($_GET['address']) && is_numeric($_GET['address']))
   $sensorCond = " address = " .  mysql_real_escape_string(htmlspecialchars($_GET['address'])) . " ";
elseif (isset($_GET['condition']))
   $sensorCond = " " . mysql_real_escape_string(htmlspecialchars($_GET['condition'])) . " ";
else
   $sensorCond = " address in (0,1,21,25,4) ";

if (isset($_GET['type']))
   $sensorCond .= "and type = '" .  mysql_real_escape_string(htmlspecialchars($_GET['type'])) . "' ";
else
   $sensorCond .= "and type = 'VA' ";

if (isset($_GET['width']) && is_numeric($_GET['width']))
   $width = htmlspecialchars($_GET['width']);
else
   $width = 1200;

if (isset($_GET['height']) && is_numeric($_GET['height']))
   $height = htmlspecialchars($_GET['height']);
else
   $height = 600;
if (isset($_GET['from']) && is_numeric($_GET['from']))
   $from = mysql_real_escape_string(htmlspecialchars($_GET['from']));
else
   $from  = time() - (36 * 60 * 60);

if (isset($_GET['range']) && is_numeric($_GET['range']))
   $range = mysql_real_escape_string(htmlspecialchars($_GET['range']));
else
   $range = 1;

$to = $from + ($range * (24*60*60));

if ($to > time())
   $to = time();

$range = ($to - $from) / (24*60*60);

syslog(LOG_DEBUG, "p4: ---------");

// ------------------------------
// get data from db

$factsQuery = "select address, type, name, usrtitle, title, unit from valuefacts where" . $sensorCond;
syslog(LOG_DEBUG, "p4: range $range; from '" . strftime("%d. %b %Y  %H:%M", $from) 
       . "' to '" . strftime("%d. %b %Y %H:%M", $to) . " [$factsQuery]");

$factResult = mysql_query($factsQuery)
   or die("Error" . mysql_error() . "query [" . $factsQuery . "]");


$skipTicks = 0;
$count = 0;
$first = true;
$start = time();

if ($range < 3)
   $groupMinutes = 5;
elseif ($range < 8)
   $groupMinutes = 10;
else
   $groupMinutes = 15;

// loop over sensors ..

while ($fact = mysql_fetch_assoc($factResult))
{
   $address = $fact['address'];
   $type = $fact['type'];
   $title = (preg_replace("/($pumpDir)/i","",$fact['usrtitle']) != "") ? preg_replace("/($pumpDir)/i","",$fact['usrtitle']) : $fact['title'];
   $unit = $fact['unit'];
   $name = $fact['name'];

   $query = "select unix_timestamp(time) as time, avg(value) as value"
      . " from samples where address = " . $address
      . " and type = '" . $type . "'"
      . " and time > from_unixtime(" . $from . ") and time < from_unixtime(" . $to . ")"  
      . " group by date(time), ((60/" . $groupMinutes . ") * hour(time) + floor(minute(time)/" . $groupMinutes . "))"
      . " order by time";

   syslog(LOG_DEBUG, "p4: $query");

   $result = mysql_query($query)
      or die("Error: " . mysql_error() . ", query: [" . $query . "]");

   syslog(LOG_DEBUG, "p4: " . mysql_num_rows($result) . " for $title ($address)");

   $lastLabel = "";

   while ($row = mysql_fetch_assoc($result))
   {
      $time = $row['time'];
      $value = $row['value'];

      // store the sensor value
 
      $series[$title][] = $value;
      
      // need only one time scale -> create only for the first sensor

      if ($first)
      {
         // the timestamps are triky, hold the same label name to avoid pChart drawing one tick label per sample :o !

         // we get at most one value all 5, 10 or 15 minutes due to the sql group by clause above (depending on the range), 
         // therefore we tolerade a module difference of half of this

         $utc = $time + date('Z');

         if ($range < 3 && ($utc % (60*60)) < 2.5*60)         // max diff 2,5 minutes
            $times[] = strftime("%H:%M", $time);
         elseif ($range < 8 && ($utc % (12*60*60)) < 5*60)    // max diff 5 minutes
            $times[] = strftime("%d. %b %H:%M", $time);
         elseif ($range >= 8 && ($utc % (60*60*24)) < 7*60)   // max diff 7 minutes
            $times[] = strftime("%d. %b", $time);
         else
            $times[] = $lastLabel;

         $lastLabel = end($times);
 
         $count++;
      }
   }

   $first = false;
}

mysql_close();

// check

if (!$count)
{
   syslog(LOG_DEBUG, "p4: No data in range " . strftime("%d. %b %Y  %H:%M", $from) 
          . "  -  " . strftime("%d. %b %Y %H:%M", $to) . " aborting");
   return;
}

// -------------------------
// charting

if (!chkDir($cache_dir))
   syslog(LOG_DEBUG, "Can't create directory " . $cache_dir);

$data = new pData(); 
$cache = new pCache(array("CacheFolder"=>$cache_dir));

// fill data struct

$data->addPoints($times, "Timestamps");

foreach ($series as $sensor => $serie) 
   $data->addPoints($serie, $sensor);

$chartHash = $cache->getHash($data);

if ($cache->isInCache($chartHash))
{
   syslog(LOG_DEBUG, "p4: got from cache");
   $cache->strokeFromCache($chartHash);
}
else
{
   $points = $data->getSerieCount("Timestamps");

//    if ($skipTicks == -1)
//       $skipTicks = $points / ($width / 75);
   
   // $data->setAxisName(0, "Temperature");
   $data->setAxisUnit(0, $unit);
   $data->setAxisDisplay(0, AXIS_FORMAT_METRIC);

   // $data->setSerieDescription("Timestamp","Sampled Dates");
   $data->setAbscissa("Timestamps");
   // $data->setXAxisDisplay(AXIS_FORMAT_TIME, $xScaleFormat);
   $data->setXAxisName("Zeit");
   
   // Create the pChart object
   
   $picture = new pImage($width, $height, $data);
   // $picture->Antialias = false;
   
   // Draw the background
   
   $Settings = array("R"=>00, "G"=>250, "B"=>250, "Dash"=>1, "DashR"=>0, "DashG"=>0, "DashB"=>0);
   $picture->drawFilledRectangle(0,0,$width,$height,$Settings);
   
   // Overlay with a gradient 
   
   $Settings = array("StartR"=>0, "StartG"=>0, "StartB"=>0, "EndR"=>0, "EndG"=>0, "EndB"=>0, "Alpha"=>100);
   $picture->drawGradientArea(0,0,$width,$height,DIRECTION_VERTICAL,$Settings);
   
   // Add a border to the picture
   
   $picture->drawRectangle(0,0,$width-1,$height-1,array("R"=>0,"G"=>0,"B"=>0));
   
   //  title 

   $picture->setFontProperties(array("FontName" => $fontText, "FontSize"=>14, "R"=>255, "G"=>255, "B"=>255));
   // $picture->drawText(60,25,$title, array("FontSize"=>20));
   $picture->drawText(70,25,strftime("%d. %b %Y", $from) . "  ->  " . strftime(" %d. %b %Y %H:%M", $to) , array("FontSize"=>18));
   
   // scale 
   
   $picture->setGraphArea(70,30,$width,$height-100);
   $picture->drawFilledRectangle(70,30,$width,$height-100,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>5));
   
   $picture->drawScale(array("LabelingMethod"=>LABELING_DIFFERENT,"DrawSubTicks"=>false,"MinDivHeight"=>$Div,"LabelSkip"=>$skipTicks,"DrawXLines"=>$XLines,"LabelRotation"=>45,"RemoveSkippedAxis"=>TRUE));
   
   // $picture->setShadow(true, array("X"=>1, "Y"=>1, "R"=>0, "G"=>0, "B"=>0, "Alpha"=>10));
   $picture->setFontProperties(array("FontName" => $fontScale, "FontSize"=>6, "R"=>0, "G"=>0, "B"=>0));

   $picture->drawSplineChart(array("DisplayValues"=>false,"DisplayColor"=>DISPLAY_AUTO));
   // $picture->drawLineChart(array("DisplayValues"=>false,"DisplayColor"=>DISPLAY_AUTO));
   
   $picture->setShadow(false);
   $picture->drawLegend(75, $height-19, array("Style"=>LEGEND_ROUND, "Family"=>LEGEND_FAMILY_CIRCLE, "Mode"=>LEGEND_HORIZONTAL, "FontSize"=>14));
 
   $cache->writeToCache($chartHash, $picture);
   $picture->Stroke();
}

$dd = time() - $start;
syslog(LOG_DEBUG, "p4: --------- done in $dd seconds");

?>
