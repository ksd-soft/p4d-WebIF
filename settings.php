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

$action = "";

if (isset($_POST["action"]))
   $action = htmlspecialchars($_POST["action"]);

// -------------------------
// establish db connection

mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
mysql_select_db($mysqldb) or die("<br/>DB error");
mysql_query("set names 'utf8'");
mysql_query("SET lc_time_names = 'de_DE'");

// -----------------------
//

if ($action == "init")
{
   requestAction("initvaluefacts", 20, 0, "", $resonse);
   echo "<br/><div class=\"info\"><b><center>Initialisierung abgeschlossen</center></b></div><br/><br/>";
}

else if ($action == "store")
{
    foreach ($_POST['usrtitle'] as $key => $value) 
    {
       $value = htmlspecialchars($value);
       $addr = htmlspecialchars($_POST["addr"][$key]);
       list ($addr, $type) = explode(":", $addr);

       $addr = mysql_real_escape_string($addr);
       $type = mysql_real_escape_string($type);

       $sql = "UPDATE valuefacts set usrtitle = '$value' where address = '$addr' and type = '$type'";
       mysql_query($sql) 
          or die("<br/>Error" . mysql_error());
    }
      
   if (isset($_POST["selected"]))
   {
      $sql = "UPDATE valuefacts SET state = 'D'";
      $result = mysql_query($sql) 
         or die("<br/>Error" . mysql_error());
         
      foreach ($_POST['selected'] as $key => $value) 
      {
         $value = htmlspecialchars($value);
         list ($addr, $type) = explode(":", $value);

         $addr = mysql_real_escape_string($addr);
         $type = mysql_real_escape_string($type);

         $sql = "UPDATE valuefacts set state = 'A' where address = '$addr' and type = '$type'";

         mysql_query($sql) 
            or die("<br/>Error" . mysql_error());

         mysql_query("update valuefacts set state = 'A' where type = 'UD'")
            or die("<br/>Error" . mysql_error());
      }

      requestAction("update-schemacfg", 2, 0, "", $resonse);

      echo "<br/><div class=\"info\"><b><center>Einstellungen gespeichert</center></b></div><br/><br/>";
   }
}

echo "      <form action=" . htmlspecialchars($_SERVER["PHP_SELF"]) . " method=post>\n";
showButtons();
showTable("UD", "Allgemein");
showTable("VA", "Messwerte");
showTable("DI", "Digitale Eingänge");
showTable("DO", "Digitale Ausgänge");
showTable("AO", "Analoge Ausgänge");
showTable("W1", "One Wire Sensoren");
echo "      </form>\n";

mysql_close();

include("footer.php");

//***************************************************************************
// Show Buttons
//***************************************************************************

function showButtons()
{
   echo "        <div>\n";
   echo "          <br/>\n";
   echo "          <button class=\"button3\" type=submit name=action value=init onclick=\"return confirmSubmit('Stammdaten der Messwerte initialisieren')\">Init</button>\n";
   echo "          <button class=\"button3\" type=submit name=action value=store onclick=\"return confirmSubmit('Einstellungen speichern?')\">Speichern</button>\n";
   echo "        </div>\n";
}

//***************************************************************************
// Show Table
//***************************************************************************

function showTable($type, $tableTitle)
{
   global $debug;

   $i = 0;

   echo "        <br/>\n";
   echo "        <table class=\"tableTitle\" cellspacing=0 rules=rows>\n";
   echo "          <tr>\n";
   echo "            <td><center><b>$tableTitle</b></center></td>\n";
   echo "          </tr>\n";
   echo "        </table>\n";
   echo "        <br/>\n";

   echo "        <table  class=\"tableLight\" border=1 cellspacing=0 rules=rows>
          <tr class=\"tableHead1\">\n";
   

   echo "            <td width=\"43%\"> Name </td>\n";
   echo "            <td> Bezeichnung </td>\n";
   echo "            <td> Einheit </td>\n";
   echo "            <td> Aufzeichnen </td>\n";
   echo "            <td> ID / Typ </td>\n";
   echo "          </tr>\n";

   $result = mysql_query("select * from valuefacts where type = '$type'")
      or die("<br/>Error" . mysql_error());

   $num = mysql_numrows($result);
   
   while ($i < $num) 
   {
      $address = mysql_result($result, $i, "address");
      $type    = mysql_result($result, $i, "type");
      $title   = mysql_result($result, $i, "title");
      $unit    = mysql_result($result, $i, "unit");
      $state   = mysql_result($result, $i, "state");
      $usrtitle= mysql_result($result, $i, "usrtitle");
      $txtaddr = sprintf("0x%04x", $address);

      if ($state == "A") 
         $checked = " checked";
      else 
         $checked = "";
      
      if ($i % 2)
         echo "         <tr class=\"tableLight\">";
      else
         echo "         <tr class=\"tableDark\">";


      echo "            <td> $title </td>\n";
      echo "            <td><input class=\"inputSettings\" name=\"usrtitle[]\" type=\"text\" value=\"$usrtitle\"></input></td>\n";
      echo "            <td> $unit </td>\n";
      
      if ($type != "UD")
         echo "            <td> <input type=\"checkbox\" name=\"selected[]\" value=\"$address:$type\"$checked></input></td>\n";
      else
         echo "            <td>[x]</td>\n";
      echo "            <td><input type=\"hidden\" name=\"addr[]\" value=\"$address:$type\"></input> $address / $type </td>\n";
      echo "          </tr>\n";

      $i++;
   }

   echo "        </table>\n";
}

?>
