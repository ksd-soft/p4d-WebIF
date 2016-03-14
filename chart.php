<?php
   
include("header.php");

include("pChart/class/pData.class.php");
include("pChart/class/pDraw.class.php");
include("pChart/class/pImage.class.php");

printHeader();

  // -------------------------
  // establish db connection

  mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
  mysql_select_db($mysqldb);
  mysql_query("set names 'utf8'");
  mysql_query("SET lc_time_names = 'de_DE'");

  // ----------------
  // init
  // $type = "(VA VA VA DO)";
  $day   = isset($_GET['sday'])   ? $_GET['sday']   : (int)date("d");
  $month = isset($_GET['smonth']) ? $_GET['smonth'] : (int)date("m");
  $year  = isset($_GET['syear'])  ? $_GET['syear']  : (int)date("Y");
  $range = isset($_GET['range'])  ? $_GET['range']  : 1;

  echo "  <br/>\n";
  echo "  <div id=\"aSelectChart\">\n";
  echo "    <form name='navigation' method='get'>\n";
  echo "      <center>Zeitraum der Charts 1+2</center>\n";
  echo datePicker("Start", "s", $year, $day, $month);

  echo "      <select name=\"range\">\n";
  echo "        <option value='1' "  . ($range == 1  ? "SELECTED" : "") . ">Tag</option>\n";
  echo "        <option value='7' "  . ($range == 7  ? "SELECTED" : "") . ">Woche</option>\n";
  echo "        <option value='31' " . ($range == 31 ? "SELECTED" : "") . ">Monat</option>\n";
  echo "      </select>\n";

  echo "      <input type=submit value=\"Go\">\n";

  echo "    </form>\n";
  echo "  </div>\n";

  $day   = isset($_GET['sday'])   ? $_GET['sday']   : (int)date("d",time()-86400*$_SESSION['chartStart']);
  $month = isset($_GET['smonth']) ? $_GET['smonth'] : (int)date("m",time()-86400*$_SESSION['chartStart']);
  $year  = isset($_GET['syear'])  ? $_GET['syear']  : (int)date("Y",time()-86400*$_SESSION['chartStart']);
  $range = isset($_GET['range'])  ? $_GET['range']  : $_SESSION['chartStart']+1;

  $from = date_create_from_format('!Y-m-d', $year.'-'.$month.'-'.$day)->getTimestamp();
  $type = isset($type) ? "&type=".$type : "";
  
  echo "  <br/><br/><br/>\n";
  echo "  <div class=\"chart\">\n";
  $condition = "address in (" . $_SESSION['chart1'] . ")";
  echo "    <img src='detail.php?width=900&height=500&from=" . $from . "&range=" . $range . "&condition=" . $condition . "&chartXLines=" . $_SESSION['chartXLines'] . "&chartDiv=" . $_SESSION['chartDiv'] . "'></img>\n";
  echo "  </div>\n";

  echo "  <br/>\n";

  echo "  <div class=\"chart\">\n";
  $condition = "address in (" . $_SESSION['chart2']. ")";
  echo "    <img src='detail.php?width=900&height=500&from=" . $from . "&range=" . $range . "&condition=" . $condition . "&chartXLines=" . $_SESSION['chartXLines'] . "&chartDiv=" . $_SESSION['chartDiv'] . "'></img>\n";
  echo "  </div>\n";

include("footer.php");
?>
