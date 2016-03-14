<?php

  // -------------------------
  // get last time stamp

$result = mysql_query("select max(time), DATE_FORMAT(max(time),'%d. %b. %Y - %H:%i') as maxPretty from samples;")
   or die("Error" . mysql_error());

$row = mysql_fetch_assoc($result);
$max = $row['max(time)'];
$maxPretty = $row['maxPretty'];
//$schemaRange = $_SESSION['schemaRange'] or $schemaRange = 60;    // Bereich und Anfang
//$from = time() - ($schemaRange * 60 *60);                        // der Charts beim Klick auf Werte im Schema


// -------------------------
// show Date

echo "      <div class=\"schema\" style=\"width:".($_SESSION['viewport']+0)."px;\">\n";
echo "        <div style=\"position:absolute; font-size:22px; right:" . ((!isset($imgSize)) ? "5" : ($body_width-$_SESSION['viewport']+5)-$jpegLeft) . "px; top:" .($jpegTop + 5) . "px; z-index:5;\"><a class=\"nolink\" href=\"schema.php\">$maxPretty</a></div>\n";


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

$resultConf = mysql_query("select address, type, kind, color, showunit, showtext, bg, fontsize, aleft, xpos, ypos, link from smartconfig where state = 'A'")
   or die("Error" . mysql_error());

while ($rowConf = mysql_fetch_assoc($resultConf))
{
   $bg    = ($rowConf['bg'] == 1) ? "" : "background:none; border-radius:none; ";
   $addr  = $rowConf['address'];
   $type  = $rowConf['type'];
   $top   = $rowConf['ypos'];
   $left  = ($rowConf['aleft'] == 1) ? ($rowConf['xpos']+$jpegLeft) : ((!isset($imgSize)) ? ($_SESSION['viewport'] - $rowConf['xpos']) : (($body_width - $imgSize[0]) + ($imgSize[0] - $rowConf['xpos'])-$jpegLeft));
   $link  = $rowConf['link'];
   $size  = $rowConf['fontsize'];
   $color = $rowConf['color'];
   $align = ($rowConf['aleft'] == 1) ? "left" : "right";
   $showUnit = $rowConf['showunit'];
   $showText = $rowConf['showtext'];
   $strQuery = sprintf("select s.value as s_value, s.text as s_text, f.title as f_title, f.usrtitle as f_usrtitle, f.unit as f_unit from samples s, valuefacts f where f.address = s.address and f.type = s.type and s.time = '%s' and f.address = %s and f.type = '%s';", $max, $addr, $type);
   $result = mysql_query($strQuery)
      or die("Error" . mysql_error());

   if ($row = mysql_fetch_assoc($result))
   {
      $value = $row['s_value'];
      $text  = $row['s_text'];
      $unit  = $row['f_unit']; 
      $title = (preg_replace("/($pumpDir)/i","",$row['f_usrtitle']) != "") ? preg_replace("/($pumpDir)/i","",$row['f_usrtitle']) : $row['f_title'];      // prüfen ob eigene Sensor-Bezeichung
      $bez = ($_SESSION["schemaBez"] == true) ? $title . ": " : ""; 
      preg_match("/($pumpDir)/i",$row['f_usrtitle'],$pmpDummy);     // Pumpen-Symbol-Richtung bei Stillstand
      $pmpDir = (isset($pmpDummy[0])) ? $pmpDummy[0] : "";          // aus eigener Sensor-Bezeichnung holen
      setTxt();

	   if (!isset($imgSize) && ($link))                                             // Chart nur bei SCHEMA, nicht bei CONFIG anzeigen
	   { 
	     $url = "        <a class=\"a\" href=\"$link\">";
	     $url2 ="</a>";
	   }
	   else
	     $url = $url2 = "        ";  
           
    if (!preg_match("/img/", $value)) 
      echo $url . "<div class=\"values\" title=\"" . $title . "\" style=\"$bg font-size:".$size."px; position:absolute; top:" . ($top + $jpegTop) . "px; $align:" . ($left) . "px" . "; color:" . $color . "; z-index:11" . "\">";
    else                               // Bilder ohne Stylesheet-Klasse anzeigen 
    {
      echo $url . "<div title=\"" . $title . ": " . $row['s_value'] . $unit . "\" style=\" position:absolute; top:" . ($top + $jpegTop) . "px; $align:" . ($left) . "px" . "; z-index:11" . "\">";
      $bez = "";                       // keine Bezeichnung bei Bildern anzeigen
    }
    
    $value = (preg_match("/[a-zA-Z ]/", $value)) ? $value : number_format(round($value, 1),1);   // Nachkommastelle immer anzeigen
    if ($showText)
       echo $text;
    else if ($showUnit && !preg_match("/[a-zA-Z]/", $value)) // Unit nur anzeigen, wenn Wert eine Zahl ist
       echo $bez . $value . ($unit == "°" ? "°C" : $unit);
    else
       echo $bez . $value;
    
    echo "</div>" . $url2. "\n";
   }
}


function setTxt ()
{  
	global $addr,$type,$unit,$value,$bez,$title,$description,$pumpON,$pumpOFF,$ventON,$ventOFF,$pumpsVA,$pumpsDO,$pumpsAO,$pmpDir;
   
  if ($type == "VA")
  {
    ($addr == "") ? $bez = "Holzmenge: " : $addr;
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
