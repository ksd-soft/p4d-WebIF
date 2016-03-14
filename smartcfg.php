<?php

include("header.php");

printHeader(0,2);

include("setup.php");

// -------------------------
// check login

if (!haveLogin())
{
   echo "<br/><div class=\"infoError\"><b><center>Login erforderlich!</center></b></div><br/>\n";
   die("<br/>");
}

$jpegTop = 250;
$jpegLeft = 20;

$selectAllSmartConf = "select * from smartconfig c, valuefacts f where f.address = c.address and f.type = c.type and f.state = 'A'";

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

if (isset($_POST["schemasmart"]))
  $_SESSION['schemasmart'] = htmlspecialchars($_POST["schemasmart"]);

// ------------------
// store settings

   writeConfigItem("schemasmart", $_SESSION['schemasmart']);
   $imgSize = GetImageSize($schema_path . substr($schema_pattern, 0, -5) . $_SESSION["schemasmart"] . ".png");
   $_SESSION['viewport'] = $imgSize[0];
   writeConfigItem("viewport",    $_SESSION['viewport']);
}

// -------------------------
// start / stop

$dummy = 1;
if ($cfg == "Start") 
{
   $_SESSION["num"] = 1;
   $_SESSION["started"] = 1;
   $started = 1;
   syslog(LOG_DEBUG, "p4: starting smart-cfg");

   $result = mysql_query($selectAllSmartConf);
   $_SESSION["cur"] = -1;
   $_SESSION["addr"] = -1;
}
elseif ($cfg == "Stop") 
{
   $_SESSION["started"] = 0;
   $started = 0;
   syslog(LOG_DEBUG, "p4: smart-cfg stop");
}
// -------------------------
// check for BACK

if ($cfg == "Back")  
{
	 $_SESSION["cur"] -= 1;
   $dummy = 0;
}

// -------------------------
// show image

$schemaImg = $schema_path . substr($schema_pattern, 0, -5) . $_SESSION["schemasmart"] . ".png";
$imgSize = GetImageSize ($schemaImg);
$_SESSION['viewport'] = $imgSize[0];

echo "      <br/>\n";
echo "      <form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>\n"; 


// -------------------------
// show buttons
if ($started == 1 && $_SESSION["cur"] != $_SESSION["num"] - 1) // Fix für letzten Wert... 
{
   $result = mysql_query($selectAllSmartConf);
   $hide = mysql_result($result, $_SESSION["cur"]+$dummy, "c.state");
   $xpos = mysql_result($result, $_SESSION["cur"]+$dummy, "c.xpos");
   $ypos = mysql_result($result, $_SESSION["cur"]+$dummy, "c.ypos");
   $_SESSION["bg"] = mysql_result($result, $_SESSION["cur"]+$dummy, "c.bg");
   $_SESSION["aleft"] = mysql_result($result, $_SESSION["cur"]+$dummy, "c.aleft");
   $_SESSION["color"] = mysql_result($result, $_SESSION["cur"]+$dummy, "c.color");
   $_SESSION["fontsize"] = mysql_result($result, $_SESSION["cur"]+$dummy, "c.fontsize");
   $_SESSION["showunit"] = mysql_result($result, $_SESSION["cur"]+$dummy, "c.showunit");
   $_SESSION["showtext"] = mysql_result($result, $_SESSION["cur"]+$dummy, "c.showtext");

   echo "      <button class=\"button3\" type=submit name=cfg value=Stop>Stop</button>\n";
   echo "      <button class=\"button3\" type=submit name=cfg value=Skip>Skip</button>\n";
   echo "      <button class=\"button3\" " . ($hide == "D" ? "style=background-color:orange; " : "") . "type=submit name=cfg value=Hide>Hide</button>\n";
   echo "      <button class=\"button3\" type=submit name=cfg value=Back>Back</button>\n";
   echo "      <div class=\"input\" style=\"font:normal 14px sans-serif; position:absolute; top:90px; right:22px; height:45px; width:570px;\">Optionen wählen und mit der Maus auf dem Schema positionieren, mit 'Hide' verbergen, mit 'Skip' unverändert beibehalten oder manuell mit '&#10004' speichern.</div>\n";
   echo "      <span style=\"font-size:165%;\"><br/> <br/></span>\n";
   echo "      <div class=\"input\">\n";
   echo "        <input type=checkbox name=unit value=unit " . ($_SESSION["showunit"] == 1 ? "checked" : "") . " />Einheit\n";
   echo "        <input type=checkbox name=bg value=bg " . ($_SESSION["bg"] == 1 ? "checked" : "") . " />Rahmen\n";
   echo "        <input type=checkbox name=aleft value=aleft " . ($_SESSION["aleft"] == 1 ? "checked" : "") . " />Linksbündig &nbsp;\n";
   echo "        X:<input type=\"text\" name=\"posX\" id=\"coordx\" value=\"$xpos\" size=\"2\" style=\"\">&nbsp;\n";
   echo "        Y:<input type=\"text\" name=\"posY\" id=\"coordy\" value=\"$ypos\" size=\"2\" style=\"\">&nbsp;\n";
   echo "        <input type=radio name=showtext value=Value " . ($_SESSION["showtext"] == 0 ? "checked" : "") . " />Wert\n";
   echo "        <input type=radio name=showtext value=Text " . ($_SESSION["showtext"] == 1 ? "checked" : "") . " />Text\n";

   echo "        <select id=\"col\" class=" . $_SESSION["color"] . " onChange=\"colorSelect('col');\" name=\"color\">\n";
   echo "          <option class=black value='black' "  . ($_SESSION["color"] == "black" ? "SELECTED" : "") . ">black</option>\n";
   echo "          <option class=white value=white "  . ($_SESSION["color"] == "white" ? "SELECTED" : "") . ">white</option>\n";
   echo "          <option class=yellow value=yellow "  . ($_SESSION["color"] == "yellow" ? "SELECTED" : "") . ">yellow</option>\n";
   echo "          <option class=silver value=silver "  . ($_SESSION["color"] == "silver" ? "SELECTED" : "") . ">silver</option>\n";
   echo "          <option class=red value=red "  . ($_SESSION["color"] == "red" ? "SELECTED" : "") . ">red</option>\n";
   echo "          <option class=orange value=orange "  . ($_SESSION["color"] == "orange" ? "SELECTED" : "") . ">orange</option>\n";
   echo "          <option class=navy value=navy "  . ($_SESSION["color"] == "navy" ? "SELECTED" : "") . ">navy</option>\n";
   echo "          <option class=blue value=blue "  . ($_SESSION["color"] == "blue" ? "SELECTED" : "") . ">blue</option>\n";
   echo "          <option class=aqua value=aqua "  . ($_SESSION["color"] == "aqua" ? "SELECTED" : "") . ">aqua</option>\n";
   echo "          <option class=teal value=teal "  . ($_SESSION["color"] == "teal" ? "SELECTED" : "") . ">teal</option>\n";
   echo "          <option class=green value=green "  . ($_SESSION["color"] == "green" ? "SELECTED" : "") . ">green</option>\n";
   echo "          <option class=lime value=lime "  . ($_SESSION["color"] == "lime" ? "SELECTED" : "") . ">lime</option>\n";
   echo "          <option class=purple value=purple "  . ($_SESSION["color"] == "purple" ? "SELECTED" : "") . ">purple</option>\n";
   echo "          <option class=maroon value=maroon "  . ($_SESSION["color"] == "maroon" ? "SELECTED" : "") . ">maroon</option>\n";
   echo "          <option class=olive value=olive "  . ($_SESSION["color"] == "olive" ? "SELECTED" : "") . ">olive</option>\n";
   echo "        </select>\n";
   echo "        <input type=text size=1 name=fontsize value=" . $_SESSION["fontsize"] . " />px\n";
   echo "        <button class=button4 type=submit name=cfg value=Store2><b>&nbsp; &#10004; &nbsp;</b></button> \n";
   echo "      </div>\n";
   echo "      <div style=\"font-size:50%;\">&nbsp;</div>\n";
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
   elseif ($cfg == "Store2")
   {
      store("A", htmlspecialchars($_POST["posX"]), htmlspecialchars($_POST["posY"]));
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
      store("D", 0, 0);
      nextConf(1);
   }
   
   if ($action == "click")
   {
      $mouseX = htmlspecialchars($_POST['mouse_x']);
      $mouseY = htmlspecialchars($_POST['mouse_y']);

      if ($_SESSION["cur"] >= 0)
      {
         // check numrows

         $result = mysql_query($selectAllSmartConf);
         $_SESSION["num"] = mysql_numrows($result);  // update ... who knows :o
         store("A", $mouseX, $mouseY);
			   nextConf(1);
			}
   }
}
echo "        <div class=\"smartImage\" style=\"position:absolute; left:" . ($jpegLeft) . "px; top:" . ($jpegTop) . "px; z-index:2;\">\n";
echo "          <input type=\"image\" id=\"schemaJPG\" src=\"$schemaImg\" value=\"click\" name=\"mouse\" alt=\"Schema to configure\" style=\"cursor:crosshair;\" onmousemove=\"displayCoords('coordx','coordy',event);\"></input>\n";
echo "        </div>\n";

echo "      </form>\n";

// -------------------------
// show schema and values

include("smartcore.php");

// ------------------
// config form

echo "        </div>\n";
echo "        <div  style=\"position:absolute; left:" . $jpegLeft . "px; top:" . ($jpegTop + $imgSize[1]+5) . "px; width:" . ($body_width-$jpegLeft-24) . "px; z-index:2;\">\n";
echo "          <form action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " method=post>\n"; 
echo "            <br/>\n";

// ------------------------
// setup items ...

seperator("Grund-Einstellungen", 0, 1);
schemaItem(1, "Schema", $_SESSION['schemasmart'], "schemasmart");
echo "          <button class=\"button3\" style=\"position:absolute; right:15px;\" type=submit name=action value=store>Einstellung Speichern</button>\n";
echo "        </div>\n         </form>\n         <br/><br/>\n       </div>\n";

include("footer.php");

//***************************************************************************
// Next Conf
//***************************************************************************

function nextConf($dir)
{
   global $selectAllSmartConf, $started;
   $_SESSION["cur"] += $dir;
   
   syslog(LOG_DEBUG, "p4: smart-cfg select " .  $_SESSION["cur"]);
   if ($_SESSION["cur"] >= $_SESSION["num"])
   {
      syslog(LOG_DEBUG, "p4: smart-cfg done");
      $_SESSION["started"] = 0; 
      $started = 0; 
      return;
   }
   
   // get last time stamp
   
   $result = mysql_query("select max(time), DATE_FORMAT(max(time),'%d. %M %Y   %H:%i') as maxPretty from samples;");
   $row = mysql_fetch_array($result, MYSQL_ASSOC);
   $max = $row['max(time)'];
   
   // select conf item

   $result = mysql_query($selectAllSmartConf);
   $_SESSION["num"] = mysql_numrows($result);
   $_SESSION["addr"] = mysql_result($result, $_SESSION["cur"], "f.address");
   $_SESSION["type"] = mysql_result($result, $_SESSION["cur"], "f.type");
   $_SESSION["bg"] = mysql_result($result, $_SESSION["cur"], "c.bg");
   $_SESSION["aleft"] = mysql_result($result, $_SESSION["cur"], "c.aleft");
   $_SESSION["color"] = mysql_result($result, $_SESSION["cur"], "c.color");
   $_SESSION["fontsize"] = mysql_result($result, $_SESSION["cur"], "c.fontsize");
   $_SESSION["showunit"] = mysql_result($result, $_SESSION["cur"], "c.showunit");
   $_SESSION["showtext"] = mysql_result($result, $_SESSION["cur"], "c.showtext");

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

$wert = $title . " | Wert: " . $value . $unit . " - Text: " . $text;
seperator($wert, 0, 1, 0, 0);
   
}

//***************************************************************************
// Store
//***************************************************************************

function store($state, $xpos, $ypos)
{
   $showUnit = 0;
   $showText = 0;

   $bg = (isset($_POST["bg"])) ? 1 : 0;
   $aleft = (isset($_POST["aleft"])) ? 1 : 0;
   $showUnit = (isset($_POST["unit"])) ? 1 : 0;
   $color = htmlspecialchars($_POST["color"]);
   $fontsize = htmlspecialchars($_POST["fontsize"]);
   
   if (htmlspecialchars($_POST["showtext"]) == "Text")
      $showText = 1;

   if ($_SESSION["cur"] < $_SESSION["num"] && $_SESSION["addr"] >= 0)
  {
      syslog(LOG_DEBUG, "p4: smart-cfg store position: " . $xpos . "/" . $ypos . " with unit: " . $showUnit . " for Adr:" . $_SESSION["addr"]);
      
      if ($state == "D")
         mysql_query("update smartconfig set state = '" . $state . "'" .
                     " where address = '" . $_SESSION["addr"] . "' and type = '" .
                     $_SESSION["type"] . "'");
      else
         mysql_query("update smartconfig set xpos = '" . $xpos . 
                     "', ypos = '" . $ypos . "', state = '" . $state . 
                     "', showunit = '" . $showUnit . "', showtext = '" . $showText . 
                     "', bg = " . $bg . ", fontsize = '" . $fontsize . 
                     "', aleft = '" . $aleft . "', color = '" . $color . 
                     "' where address = '" . $_SESSION["addr"] . "' and type = '" .
                     $_SESSION["type"] . "'");
   }
}
	

?>
