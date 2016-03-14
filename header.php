<?php

session_start();

include("config.php");
include("functions.php");

// ----------------
// Configuration

 if (!isset($_SESSION['initialized']) || !$_SESSION['initialized'])
 {
  $_SESSION['initialized'] = true;
   
   // -------------------------
   // establish db connection
   
   mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
   mysql_select_db($mysqldb) or die("<br/>DB error");
   mysql_query("set names 'utf8'");
   mysql_query("SET lc_time_names = 'de_DE'");
   
   // ------------------
   // get configuration
   
   readConfigItem("chartStart", $_SESSION['chartStart']);
   readConfigItem("chartDiv", $_SESSION['chartDiv']);
   readConfigItem("smartHome", $_SESSION['smartHome']);
   readConfigItem("chartXLines", $_SESSION['chartXLines']);
   readConfigItem("chart1", $_SESSION['chart1']);
   readConfigItem("chart2", $_SESSION['chart2']);
   
   // Chart 3+4

   readConfigItem("chart34", $_SESSION['chart34']);
   readConfigItem("chart3", $_SESSION['chart3']);
   readConfigItem("chart4", $_SESSION['chart4']);

   readConfigItem("user", $_SESSION['user']);
   readConfigItem("passwd", $_SESSION['passwd']);
   
   readConfigItem("mail", $_SESSION['mail']);
   readConfigItem("htmlMail", $_SESSION['htmlMail']);
   readConfigItem("stateMailTo", $_SESSION['stateMailTo']);
   readConfigItem("stateMailStates", $_SESSION['stateMailStates']);
   readConfigItem("errorMailTo", $_SESSION['errorMailTo']);
   readConfigItem("mailScript", $_SESSION['mailScript']);
   
   readConfigItem("tsync", $_SESSION['tsync']);
   readConfigItem("maxTimeLeak", $_SESSION['maxTimeLeak']);
   readConfigItem("heatingType", $_SESSION['heatingType']);
   readConfigItem("stateAni", $_SESSION['stateAni']);
   readConfigItem("schemaRange", $_SESSION['schemaRange']);
   readConfigItem("schema", $_SESSION['schema']);
   readConfigItem("schemasmart", $_SESSION['schemasmart']);
   readConfigItem("viewport", $_SESSION['viewport']);
   readConfigItem("schemaBez", $_SESSION['schemaBez']);
   readConfigItem("valuesBG", $_SESSION['valuesBG']);
   readConfigItem("pumpON",  $_SESSION['pumpON']);
   readConfigItem("pumpOFF", $_SESSION['pumpOFF']);
   readConfigItem("ventON",  $_SESSION['ventON']);
   readConfigItem("ventOFF", $_SESSION['ventOFF']);
   readConfigItem("pumpsVA", $_SESSION['pumpsVA']);
   readConfigItem("pumpsDO", $_SESSION['pumpsDO']);
   readConfigItem("pumpsAO", $_SESSION['pumpsAO']);
   readConfigItem("webUrl",  $_SESSION['webUrl']);
   
   // ------------------
   // check for defaults
   
   if ($_SESSION['chartStart'] == "")
      $_SESSION['chartStart'] = "5";

   if ($_SESSION['chartDiv'] == "")
      $_SESSION['chartDiv'] = "25";

   if ($_SESSION['smartHome'] == "")
      $_SESSION['smartHome'] = "smart.php";

   if ($_SESSION['chartXLines'] == "")
      $_SESSION['chartXLines'] = "0";
   else 
      $_SESSION['chartXLines'] = "1";

   if ($_SESSION['chart1'] == "")
      $_SESSION['chart1'] = "0,1,113,18";
   
   if ($_SESSION['chart2'] == "")
      $_SESSION['chart2'] = "118,225,21,25,4,8";
   
   if ($_SESSION['chart34'] == "")
      $_SESSION['chart34'] = "0";
	  
   if ($_SESSION['heatingType'] == "")
      $_SESSION['heatingType'] = "p4";

   if ($_SESSION['schema'] == "")
      $_SESSION['schema'] = "p4-2hk-puffer";

   if ($_SESSION['schemasmart'] == "")
      $_SESSION['schemasmart'] = "smart5";

   if ($_SESSION['viewport'] == "")
      $_SESSION['viewport'] = "800";

   if ($_SESSION['pumpON'] == "")
      $_SESSION['pumpON'] = "img/icon/pump-on.gif";

   if ($_SESSION['pumpOFF'] == "")
      $_SESSION['pumpOFF'] = "img/icon/pump-off.gif";

   if ($_SESSION['ventON'] == "")
      $_SESSION['ventON'] = "img/icon/vent-on.gif";

   if ($_SESSION['ventOFF'] == "")
      $_SESSION['ventOFF'] = "aus";

   if ($_SESSION['pumpsVA'] == "")
      $_SESSION['pumpsVA'] = "(15),140,141,142,143,144,145,146,147,148,149,150,151,152,200,201,240,241,242";

   if ($_SESSION['pumpsDO'] == "")
      $_SESSION['pumpsDO'] = "0,1,25,26,31,32,37,38,43,44,49,50,55,56,61,62,67,68";

   if ($_SESSION['pumpsAO'] == "")
      $_SESSION['pumpsAO'] = "3,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22";

   mysql_close();
}

function printHeader($refresh = 0, $smart = 0)
{
  global $body_width; 		
  if ($smart <> 1)  { $bClass = "style=\"width:" . $body_width . "px;\""; $dClass = "content"; } else { $bClass = "class=\"smartBody\""; $dClass = "smartContent"; }
   // ----------------
   // HTML Head
   
   //<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
   
   echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"de\" lang=\"de\">
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">\n";
   if ($smart == 1) { echo"    <meta name=\"viewport\" content=\"width=" . $_SESSION['viewport'] . "\">\n"; }
   $stylesheet = (checkMobile() == 1)? "stylesheet.css" : "stylesheet.css";
 
   if ($refresh)
      echo "    <meta http-equiv=\"refresh\" content=\"$refresh\">\n";
   
   echo "    <meta name=\"author\" content=\"Jörg Wendel, Stefan Döring\">
    <meta name=\"copyright\" content=\"Jörg Wendel\">
    <LINK REL=\"SHORTCUT ICON\" HREF=\"" . $_SESSION['heatingType'] . ".ico\">
    <link href='https://fonts.googleapis.com/css?family=Nunito:700' rel='stylesheet' type='text/css'>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"$stylesheet\">
    <script type=\"text/JavaScript\" src=\"jfunctions.js\"></script> 
    <title>Fröling  " . $_SESSION['heatingType'] . "</title>
  </head>
  <body $bClass>\n";
  if ($smart <> 1) 
  {
   echo "    <a class=\"button1\" href=\"main.php\">Aktuell</a>
    <a class=\"button1\" href=\"chart.php\">Charts 1+2</a>\n";

   if ($_SESSION['chart34'] == "1")
      echo "<a class=\"button1\" href=\"chart2.php\">Charts 3+4</a>\n";

   echo "    <a class=\"button1\" href=\"schema.php\">Schema</a>
    <a class=\"button1\" href=\"menu.php\">Menü</a>
    <a class=\"button1\" href=\"error.php\">Fehler</a>\n";

   if (haveLogin())
   {
      echo "    <a class=\"button1\" href=\"basecfg.php\">Setup</a>\n";
      echo "    <a class=\"button1\" href=\"logout.php\">Logout</a>\n";
   }
   else
      echo "    <a class=\"button1\" href=\"login.php\">Login</a>\n";
  }
  else
  {
   $link = ($_SERVER['SCRIPT_NAME'] == "/smartmain.php") ? "smart.php" : "smartmain.php";
   $title = ($_SERVER['SCRIPT_NAME'] == "/smartmain.php") ? "zum&nbsp;Schema" : "zur&nbsp;Tabelle";
   // <a class=\"smartButton1\" style=\"position:absolute; left:1px; top:-1px; z-index:8;\" href=\"main.php\">Home</a>
   echo "    <a class=\"smartButton1\" style=\"position:absolute; left:1px; top:-1px; z-index:8;\" href=\"$link\">$title</a>\n";
  }  
//   echo $stylesheet;
   echo "    <div class=\"$dClass\">\n";
}

?>
