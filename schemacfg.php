<?php

include("header.php");

printHeader();

include("setup.php");

// -------------------------
// check login

if (!haveLogin())
{
   echo "<br/><div class=\"infoError\"><b><center>Login erforderlich!</center></b></div><br/>\n";
   die("<br/>");
}

$jpegTop = 260;
$jpegLeft = 20;

$selectAllSchemaConf = "select * from schemaconf c, valuefacts f where f.address = c.address and f.type = c.type and f.state = 'A'";

$action = "";
$cfg = "";
$started = 0;

if (isset($_POST['mouse_x']))
   $action = "click";

if (isset($_POST["cfg"]))
   $cfg = htmlspecialchars($_POST["cfg"]);

if (isset($_SESSION["started"]))
   $started = $_SESSION["started"];

if (isset($_POST["action"]))
   $action = htmlspecialchars($_POST["action"]);

// -------------------------
// establish db connection

mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
mysql_select_db($mysqldb);
mysql_query("set names 'utf8'");
mysql_query("SET lc_time_names = 'de_DE'");

// ------------------
// Schema  Settings

if ($action == "store")
{

if (isset($_POST["schema"]))
  $_SESSION['schema'] = htmlspecialchars($_POST["schema"]);

if (isset($_POST["schemaRange"]))
  $_SESSION['schemaRange'] = htmlspecialchars($_POST["schemaRange"]);

$_SESSION['schemaBez'] = ($_POST["schemaBez"] != $_SESSION['schemaBez']) ? $_POST["schemaBez"] : $_SESSION['schemaBez'];

$_SESSION['valuesBG'] = ($_POST["valuesBG"] != $_SESSION['valuesBG']) ? $_POST["valuesBG"] : $_SESSION['valuesBG'];

if (isset($_POST["pumpON"]))
  $_SESSION['pumpON'] = htmlspecialchars($_POST["pumpON"]);

if (isset($_POST["pumpOFF"]))
  $_SESSION['pumpOFF'] = htmlspecialchars($_POST["pumpOFF"]);

if (isset($_POST["ventON"]))
  $_SESSION['ventON'] = htmlspecialchars($_POST["ventON"]);

if (isset($_POST["ventOFF"]))
  $_SESSION['ventOFF'] = htmlspecialchars($_POST["ventOFF"]);

if (isset($_POST["pumpsVA"]))
  $_SESSION['pumpsVA'] = htmlspecialchars($_POST["pumpsVA"]);

if (isset($_POST["pumpsDO"]))
	$_SESSION['pumpsDO'] = htmlspecialchars($_POST["pumpsDO"]);

if (isset($_POST["pumpsAO"]))
  $_SESSION['pumpsAO'] = htmlspecialchars($_POST["pumpsAO"]);

// ------------------
// store settings

   writeConfigItem("schema",    $_SESSION['schema']);
   writeConfigItem("schemaBez", $_SESSION['schemaBez']);
   writeConfigItem("schemaRange", $_SESSION['schemaRange']);
   writeConfigItem("valuesBG",  $_SESSION['valuesBG']);
   writeConfigItem("pumpON",    $_SESSION['pumpON']);
   writeConfigItem("pumpOFF",   $_SESSION['pumpOFF']);
   writeConfigItem("ventON",    $_SESSION['ventON']);
   writeConfigItem("ventOFF",   $_SESSION['ventOFF']);
   writeConfigItem("pumpsVA",   $_SESSION['pumpsVA']);
   writeConfigItem("pumpsDO",   $_SESSION['pumpsDO']);
   writeConfigItem("pumpsAO",   $_SESSION['pumpsAO']);
}

// -------------------------
// start / stop

if ($cfg == "Start") 
{
   $_SESSION["num"] = 1;
   $_SESSION["started"] = 1;
   $started = 1;
   syslog(LOG_DEBUG, "p4: starting schema-cfg");

   $result = mysql_query($selectAllSchemaConf);
   $_SESSION["cur"] = -1;
   $_SESSION["addr"] = -1;
}
elseif ($cfg == "Stop") 
{
   $_SESSION["started"] = 0;
   $started = 0;
   syslog(LOG_DEBUG, "p4: schema-cfg stop");
}
// -------------------------
// check for BACK

 if ($cfg == "Back")  
   $_SESSION["cur"] -= 1;

// -------------------------
// show image

$schemaImg = $schema_path . substr($schema_pattern, 0, -5) . $_SESSION["schema"] . ".png";
$imgSize = GetImageSize ($schemaImg);
echo "    <br/>\n";
echo "    <form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>\n"; 
echo "      <div class=\"schemaImage\" style=\"position:absolute; left:" . $jpegLeft . "px; top:" . $jpegTop . "px; z-index:2;\">\n";
echo "        <input type=\"image\" id=\"schemaJPG\" src=\"$schemaImg\" value=\"click\" name=\"mouse\" alt=\"Schema to configure\" style=\"cursor:crosshair;\"  onmousemove=\"displayCoords('coordx','coordy',event);\"></input>\n";
echo "        <span style=\"position:absolute; left:" . (0) . "px; top:" . (-$jpegtop-27) . "px;\">x:<input type=\"text\" name=\"posX\" id=\"coordx\" value=\"$xpos\" size=\"2\" readonly></span>\n";
echo "        <span style=\"position:absolute; left:" . (80) . "px; top:" . (-$jpegtop-27) . "px;\">y:<input type=\"text\" name=\"posY\" id=\"coordy\" value=\"$ypos\" size=\"2\" readonly></span>\n";
echo "      </div>\n";


// -------------------------
// show buttons
if ($started == 1 && $_SESSION["cur"] != $_SESSION["num"] - 1) // Fix für letzten Wert... 
{
   echo "      <button class=\"button3\" type=submit name=cfg value=Stop>Stop</button>\n";
   echo "      <button class=\"button3\" type=submit name=cfg value=Skip>Skip</button>\n";
   echo "      <button class=\"button3\" type=submit name=cfg value=Hide>Hide</button>\n";
   echo "      <button class=\"button3\" type=submit name=cfg value=Back>Back</button>\n";
   echo "      <span class=\"checkbox\">\n";
   echo "        <input type=checkbox name=unit value=unit checked />Einheit\n";
   echo "        <input type=radio name=showtext value=Value checked />Wert\n";
   echo "        <input type=radio name=showtext value=Text />Text\n";
   echo "      </span>\n";
   echo "      <br/><br/>\n";
   echo "      <div class=\"seperatorTitle1\">Einheit und Wert/Text wählen und mit der Maus auf dem Schema positionieren,<br /> mit 'Hide' verbergen oder mit 'Skip' unverändert beibehalten</div>\n";
   echo "      <br/>\n";
}
else
   echo "      <button class=\"button3\" type=submit name=cfg value=Start>Start der Werte-Positionierung</button>\n";


if ($started == 1)
{
   if ($_SESSION["cur"] == -1)
   {
      nextConf(1);
   }
   elseif ($cfg == "Skip")
   {
      nextConf(1);
   }
   elseif ($cfg == "Back")
   {
      syslog(LOG_DEBUG, "p4: schema-cfg back");
      nextConf(0);      
   }
   elseif ($cfg == "Hide")
   {
      syslog(LOG_DEBUG, "p4: schema-cfg hide");
      store("D", 0, 0, "black");
      nextConf(1);
   }
   
   if ($action == "click")
   {
      $mouseX = htmlspecialchars($_POST['mouse_x']);
      $mouseY = htmlspecialchars($_POST['mouse_y']);

      if ($_SESSION["cur"] >= 0)
      {
         // check numrows

         $result = mysql_query($selectAllSchemaConf);
         $_SESSION["num"] = mysql_numrows($result);  // update ... who knows :o
         store("A", $mouseX, $mouseY, "black");
			   nextConf(1);
			}
   }
}

// -------------------------
// show schema and values

include("schemacore.php");

echo "    </form>\n";

// ------------------
// config form

echo "      <form action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " method=post>\n"; 
echo "        <div  style=\"position:absolute; left:" . $jpegLeft . "px; top:" . (250 + $imgSize[1]) . "px; z-index:2;\">\n";
echo "        <br/></br>\n";

// ------------------------
// setup items ...

seperator("Grund-Einstellungen", 0, 1);
schemaItem(1, "Schema", $_SESSION['schema'], "schema");
configBoolItem(5, "Sensor-Bezeichnungen", "schemaBez", $_SESSION['schemaBez']);
configBoolItem(5, "Hintergrund?", "valuesBG", $_SESSION['valuesBG'], "anzeigen?");
configOptionItem(7, "Zeitraum für Chart-Anzeige", "schemaRange", $_SESSION['schemaRange'], "24,24 48,48 60,60 72,72", "Stunden");
echo "          <button class=\"button3\" style=\"position:absolute; right:20px;\" type=submit name=action value=store>Einstellungen Speichern</button>\n";
echo"        </div><br/>\n";
    
echo "        <div class=\"input\" id=\"hlp\" style=\"display:none;\" onClick=\"showContent('hlp')\">\n";
echo "          <span class=\"inputComment\">
                Oben links könnt ihr ein Schema aus der Liste auswählen, die Sensorbezeichnungen aus der 'Aufzeichnung'-Liste anzeigen und<br/>
                zur besseren Sichtbarkeit die Werte hinterlegen.<br/><br/>
                Die Anzeige der Pumpen/Lüfter könnt ihr entweder als Bild (Bilder müssen im img/... -Verzeichnis liegen, Pfad muss mit 'img/' beginnen), <br/>
                als Text (z.B. 'aus') definieren oder ganz leer lassen (dann wird 'on' bzw. 'off' angezeigt).<br/>
                <b>Wichtig:</b> damit die Pumpen im AUS-Zustand in die richtige Richtung zeigen, muss in der 'Aufzeichnung'-Liste<br/>
                im 'Bezeichnung'-Feld der jeweiligen ID die Richtung angehangen werden (Bsp: 'HKP-1-up', oder nur '-up', '-down', '-left', '-right')!<br/>
                Sonst werden die Pumpen im AUS-Zustand immer 'nach oben zeigend' verwendet!<br/><br/>
                Unten notiert ihr die IDs (kommagetrennt) der Pumpen, dabei gilt wieder: Sensor-ID und Typ aus der Tabelle <br />
                'Aufzeichnung' entnehmen und hier eintragen.<br /><br />
                <b>Achtung:</b> Um Lüfter und Pumpen mit separaten Grafiken zu bedienen und dennoch Übersichtlichkeit zu wahren,<br />
                gebt ihr bitte die Lüfter in Klammern an. <b>Beispiel (VA):</b> 140,141,(15),143,...<br />
                <b>das bedeutet:</b> 140,141,143 sind Pumpen - (15) ist der Saugzug-Ventilator <br />
          </span>\n";
echo "        </div><br/>\n";
seperator("Piktogramme&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"help\" onClick=\"showContent('hlp')\">(Hilfe)</span>", 0, 4);
configStrItem(1, "Pumpe an", "pumpON", $_SESSION['pumpON'], "", 217);
configStrItem(5, "Pumpe aus", "pumpOFF", $_SESSION['pumpOFF'], "jeweils Text oder Pfad/zum/Bild.gif", 217);
configStrItem(7, "Lüfter &nbsp;an", "ventON", $_SESSION['ventON'], "", 217);
configStrItem(5, "Lüfter &nbsp; aus", "ventOFF", $_SESSION['ventOFF'], "jeweils Text oder Pfad/zum/Bild.gif", 217);

configStrItem(7, "Sensor-IDs (VA)", "pumpsVA", $_SESSION['pumpsVA'], "IDs f. Pumpe/Lüfter", 650);
configStrItem(7, "Sensor-IDs (DO)", "pumpsDO", $_SESSION['pumpsDO'], "IDs f. Pumpe/Lüfter", 650);
configStrItem(7, "Sensor-IDs (AO)", "pumpsAO", $_SESSION['pumpsAO'], "IDs f. Pumpe/Lüfter", 650);

echo "     </div>\n    </form>\n    <br/><br/>";

include("footer.php");

//***************************************************************************
// Next Conf
//***************************************************************************

function nextConf($dir)
{
   global $selectAllSchemaConf, $started;
   $_SESSION["cur"] += $dir;
   
   syslog(LOG_DEBUG, "p4: schema-cfg select " .  $_SESSION["cur"]);
   if ($_SESSION["cur"] >= $_SESSION["num"])
   {
      syslog(LOG_DEBUG, "p4: schema-cfg done");
      $_SESSION["started"] = 0; 
      $started = 0; 
      return;
   }
   
   // get last time stamp
   
   $result = mysql_query("select max(time), DATE_FORMAT(max(time),'%d. %M %Y   %H:%i') as maxPretty from samples;");
   $row = mysql_fetch_array($result, MYSQL_ASSOC);
   $max = $row['max(time)'];
   
   // select conf item

   $result = mysql_query($selectAllSchemaConf);
   $_SESSION["num"] = mysql_numrows($result);
   $_SESSION["addr"] = mysql_result($result, $_SESSION["cur"], "f.address");
   $_SESSION["type"] = mysql_result($result, $_SESSION["cur"], "f.type");

   $title = (mysql_result($result, $_SESSION["cur"], "f.usrtitle") != "") ? mysql_result($result, $_SESSION["cur"], "f.usrtitle") : mysql_result($result, $_SESSION["cur"], "f.title");

   // get coresponding value/text and unit

   $strQuery = sprintf("select s.value as s_value, s.text as s_text, f.unit as f_unit from samples s, valuefacts f where f.address = s.address and f.type = s.type and s.time = '%s' and f.address = %s and f.type = '%s';", 
                       $max, $_SESSION["addr"], $_SESSION["type"]);

   $result = mysql_query($strQuery)
      or die("Error" . mysql_error());
   
   if ($row = mysql_fetch_array($result, MYSQL_ASSOC))
   {
      $value = $row['s_value'];
      $unit = $row['f_unit'];
      $text = $row['s_text'];
   }
  
   // show

   echo "      <div class=\"seperatorTitle2\">";
   echo $title . " - ";
   echo "  Wert: " . $value . $unit;
   echo "  Text: " . $text;
   echo "</div>\n";

   
}

//***************************************************************************
// Store
//***************************************************************************

function store($state, $xpos, $ypos, $color)
{
   $showUnit = 0;
   $showText = 0;

   if (isset($_POST["unit"]))
      $showUnit = 1;
   
   if (htmlspecialchars($_POST["showtext"]) == "Text")
      $showText = 1;
      syslog(LOG_DEBUG, "p4: schema-cfg store position: " . $xpos . "/" . $ypos . " with unit: " . $showUnit);

   if ($_SESSION["cur"] < $_SESSION["num"] && $_SESSION["addr"] >= 0)
   {
      
      if ($state == "D")
         mysql_query("update schemaconf set state = '" . $state . "'" .
                     " where address = '" . $_SESSION["addr"] . "' and type = '" .
                     $_SESSION["type"] . "'");
      else
         mysql_query("update schemaconf set xpos = '" . $xpos . 
                     "', ypos = '" . $ypos . "', state = '" . $state . 
                     "', showunit = " . $showUnit . ", showtext = " . $showText . 
                     " where address = '" . $_SESSION["addr"] . "' and type = '" .
                     $_SESSION["type"] . "'");
   }
}
	

?>
