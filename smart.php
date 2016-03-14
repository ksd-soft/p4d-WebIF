<?php

  include("header.php");

  printHeader(10,1);
  include("config.php");

  $jpegTop  = 0;
  $jpegLeft = 0;

  // -------------------------
  // establish db connection

  mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
  mysql_select_db($mysqldb);
  mysql_query("set names 'utf8'");
  mysql_query("SET lc_time_names = 'de_DE'");

  // -------------------------
  // show image

  $schemaImg = "img/schema/schema-" . $_SESSION["schemasmart"] . ".png";

  echo "      <div class=\"smartImage\" style=\"position:absolute; left:" . $jpegLeft . "px; top:" . ($jpegTop) . "px; z-index:3;\">\n";
  echo "        <p><img src=\"$schemaImg\"></p>\n";
  echo "      </div>\n";

  include("smartcore.php");

  include("footer.php");

?>
