<?php

  // -------------------------
  // get last time stamp

$result = mysql_query("select max(time), DATE_FORMAT(max(time),'%d. %M %Y   %H:%i') as maxPretty from samples;")
   or die("Error" . mysql_error());

$row = mysql_fetch_assoc($result);
$max = $row['max(time)'];
$maxPretty = $row['maxPretty'];
$schemaRange = $_SESSION['schemaRange'] or $schemaRange = 60;    // Bereich und Anfang
$from = time() - ($schemaRange * 60 *60);                        // der Charts beim Klick auf Werte im Schema

// -------------------------
// check for valuesBG

   if (isset($_SESSION["valuesBG"]))        // Hintergrund-Box bei Schema-Werten
      $valuesBG = " background-color:#ccc; border-radius:2px; box-shadow:2px,2px,2px,#666;";

// -------------------------
// show Date

echo "      <div class=\"schema\">\n";
echo "        <div style=\"position:absolute; font-size:22px; left:" . ($jpegLeft +8) . "px; top:" .($jpegTop) . "px; z-index:2;\"><a class=\"nolink\" href=\"smart.php\">$maxPretty</a></div>\n";
echo "        <div style=\"position:absolute; font-size:14px; left:" . ($jpegLeft +8) . "px; top:" .($jpegTop + 20) . "px; z-index:2;\">Chart-Zeitraum: $schemaRange Stunden</div>\n";


// -------------------------
// check for images

$pumpON  = (!preg_match("/img/", $_SESSION['pumpON']))  ? ($_SESSION['pumpON']  != "") ? $_SESSION['pumpON']  : "on"  : "<img src=\"" . $_SESSION['pumpON'] . "\">";
$pumpOFF = (!preg_match("/img/", $_SESSION['pumpOFF'])) ? ($_SESSION['pumpOFF'] != "") ? $_SESSION['pumpOFF'] : "off" : "<img src=\"" . $_SESSION['pumpOFF'] . "\">";
$ventON  = (!preg_match("/img/", $_SESSION['ventON']))  ? ($_SESSION['ventON']  != "") ? $_SESSION['ventON']  : "on"  : "<img src=\"" . $_SESSION['ventON'] . "\">";
$ventOFF = (!preg_match("/img/", $_SESSION['ventOFF'])) ? ($_SESSION['ventOFF'] != "") ? $_SESSION['ventOFF'] : "off" : "<img src=\"" . $_SESSION['ventOFF'] . "\">";
$pumpsVA = "|," . $_SESSION['pumpsVA'] . ",";   // Workaround damit die IDs erkannt werden...
$pumpsDO = "|," . $_SESSION['pumpsDO'] . ",";
$pumpsAO = "|," . $_SESSION['pumpsAO'] . ",";     

// -------------------------
// show values

$resultConf = mysql_query("select address, type, kind, color, showunit, showtext, xpos, ypos from schemaconf where state = 'A'")
   or die("Error" . mysql_error());

while ($rowConf = mysql_fetch_assoc($resultConf))
{
   $addr = $rowConf['address'];
   $type = $rowConf['type'];
   $left = $rowConf['xpos'];
   $top  = $rowConf['ypos'];
   $color = $rowConf['color'];
   $showUnit = $rowConf['showunit'];
   $showText = $rowConf['showtext'];
   
   $strQuery = sprintf("select s.value as s_value, s.text as s_text, f.title as f_title, f.usrtitle as f_usrtitle, f.unit as f_unit from samples s, valuefacts f where f.address = s.address and f.type = s.type and s.time = '%s' and f.address = %s and f.type = '%s';", $max, $addr, $type);
   $result = mysql_query($strQuery)
      or die("Error" . mysql_error());

   if ($row = mysql_fetch_assoc($result))
   {
      $value = $row['s_value'];
      $text = $row['s_text'];
      $unit = $row['f_unit']; 
      $title = (preg_replace("/($pumpDir)/i","",$row['f_usrtitle']) != "") ? preg_replace("/($pumpDir)/i","",$row['f_usrtitle']) : $row['f_title'];      // prüfen ob eigene Sensor-Bezeichung
      $bez = ($_SESSION["schemaBez"] == true) ? $title . ": " : ""; 
      preg_match("/($pumpDir)/i",$row['f_usrtitle'],$pmpDummy);     // Pumpen-Symbol-Richtung bei Stillstand
      $pmpDir = (isset($pmpDummy[0])) ? $pmpDummy[0] : "";          // aus eigener Sensor-Bezeichnung holen
      setTxt();

   if (!isset($imgSize))                                                 // Chart nur bei SCHEMA, nicht bei CONFIG anzeigen
   { 
     $url = "<a class=\"a\" href=\"#\" onclick=\"window.open('detail.php?width=1200&height=600&address=$addr&type=$type&from=" . $from . "&range=" . ($schemaRange / 24) . "&chartXLines=" . $_SESSION['chartXLines'] . "&chartDiv=" . $_SESSION['chartDiv'] . " ','_blank'," 
        . "'scrollbars=yes,width=1200,height=600,resizable=yes,left=120,top=120')\">";
     $url2 ="</a>";
   }
   else
     $url = $url2 = "";  
           
      if (!preg_match("/img/", $value)) 
        echo "        <div class=\"values\" title=\"" . $title . "\" style=\"$valuesBG position:absolute; top:" . ($top + $jpegTop) . "px; $aligned:" . ($left + $jpegLeft) . "px" . "; color:" . $color . "; z-index:11" . "\">";
      else                               // Bilder ohne Stylesheet-Klasse anzeigen 
      {
        echo "        <div title=\"" . $title . ": " . $row['s_value'] . $unit . "\" style=\" position:absolute; top:" . ($top + $jpegTop) . "px; $aligned:" . ($left + $jpegLeft) . "px" . "; z-index:11" . "\">";
        $bez = "";                       // keine Bezeichnung bei Bildern anzeigen
      }
      
      $value = (preg_match("/[a-zA-Z ]/", $value)) ? $value : number_format(round($value, 1),1);   // Nachkommastelle immer anzeigen
      if ($showText)
         echo $url . $text . $url2;
      else if ($showUnit && !preg_match("/[a-zA-Z]/", $value)) // Unit nur anzeigen, wenn Wert eine Zahl ist
         echo $url . $bez . $value . ($unit == "°" ? "°C" : $unit) . $url2;
      else
         echo $url . $bez . $value . $url2;
      
      echo "</div>\n";
   }
}
echo "      </div>\n";

function setTxt ()
{  
	global $addr,$type,$unit,$value,$bez,$title,$pumpON,$pumpOFF,$ventON,$ventOFF,$description,$rightbound,$pumpsVA,$pumpsDO,$pumpsAO,$pmpDir;
   
  if ($type == "VA")
  {
    ($addr == "266") ? $bez = "Holzmenge: " : $addr;
    ($addr == "xxx") ? $bez = "eigeneBez: " : $bez;
    (strpos($pumpsVA, ",$addr,") != false) ? ($value == 0) ? $value = preg_replace("/\./",$pmpDir.".",$pumpOFF) : $value = (!preg_match("/img/", $pumpON)) ? $value : $pumpON : $addr;     //Pumpen
    (strpos($pumpsVA, ",($addr),") != false) ? ($value == 0) ? $value = $ventOFF : $value = (!preg_match("/img/", $ventON)) ? $value : $ventON : $addr;                                    //Lüfter
    // wenn Sensor-Adresse in der Pumpenliste enthalten ist, dann (bei Value=0 --> $pumpOFF), wenn kein Bild --> Wert anzeigen, sonst Bild
    $all = "|,23,27,31,35,39,43,47,51,55,59,63,67,71,75,79,83,87,91,258,260,";     // IDs für Party-Schalter
    if (strpos($all, (",".$addr.",")) != false) 
    {
     	switch ($value) 
     	{
     		case 2:	$value = "Nacht"; break;
     		case 1:	$value = "Party"; break;
     		case 0: $value = "Auto";  break;
     	}
    }
  }
  
  if ($type == "DI")
  {
      ($addr == "0") ? ($value == "1") ? $value = "Tür auf" : $value = "Tür zu" : $addr;
      ($addr == "xxx") ? $bez = "eigeneBez: " : $bez;
  }
  
  if ($type == "DO")
  {
    (strpos($pumpsDO, ",$addr,") != false) ? ($value == 0) ? $value = preg_replace("/\./",$pmpDir.".",$pumpOFF) : $value = (!preg_match("/img/", $pumpON)) ? $value : $pumpON : $addr;     //Pumpen
    (strpos($pumpsDO, ",($addr),") != false) ? ($value == 0) ? $value = $ventOFF : $value = (!preg_match("/img/", $ventON)) ? $value : $ventON : $addr;                                    //Lüfter
    ($addr == "8") ? ($value == "1") ? $value = "Holzbetrieb" : $value = "Gasbetrieb" : $value;
    ($addr == "xxx") ? $bez = "eigeneBez: " : $bez;
  }
  
  if ($type == "AO")
  {
    (strpos($pumpsAO, ",$addr,") != false) ? ($value == 0) ? $value = preg_replace("/\./",$pmpDir.".",$pumpOFF) : $value = (!preg_match("/img/", $pumpON)) ? $value : $pumpON : $addr;     //Pumpen
    (strpos($pumpsAO, ",($addr),") != false) ? ($value == 0) ? $value = $ventOFF : $value = (!preg_match("/img/", $ventON)) ? $value : $ventON : $addr;                                    //Lüfter
    // wenn Sensor-Adresse in der Pumpenliste enthalten ist, dann (bei Value=0 --> $pumpOFF), wenn kein Bild --> Wert anzeigen, sonst Bild
    ($addr == "xxx") ? $bez = "eigeneBez: " : $bez;
  }
  
  if ($type == "W1")
  {
      ($addr == "xxx") ? $bez = "eigeneBez: " : $bez;
  }
      
  ($unit == "U") ? $unit = "U/min"	: $unit;
  ($unit == "k") ? $unit = "kg"	: $unit;

}
?>
