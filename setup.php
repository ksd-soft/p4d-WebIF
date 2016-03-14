<?php

// -------------------------
// chaeck login

if (haveLogin())
{
   echo "      <div>\n";
   echo "        <a class=\"button2\" href=\"basecfg.php\">Allgemein</a>\n";
   echo "        <a class=\"button2\" href=\"alertcfg.php\">Alarme</a>\n";
   echo "        <a class=\"button2\" href=\"settings.php\">Aufzeichnung</a>\n";
   echo "        <a class=\"button2\" href=\"schemacfg.php\">Schema-Konfig</a>\n";
   echo "        <a class=\"button2\" href=\"smartcfg.php\">Smart-Konfig</a>\n";
   echo "      </div>\n";
}

?>
