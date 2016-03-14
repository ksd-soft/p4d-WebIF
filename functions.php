<?php

if(!function_exists("functions_once")) 
{

function functions_once()  {  }

// ---------------------------------------------------------------------------
// Check Browser (Mobile)
// ---------------------------------------------------------------------------

function checkMobile()
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
	return 1;
}

// ---------------------------------------------------------------------------
// Login
// ---------------------------------------------------------------------------

function haveLogin()
{
   if (isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'])
      return true;

   return false;
}

function checkLogin($user, $passwd)
{
   $md5 = md5($passwd);

   if (requestAction("check-login", 5, 0, "$user:$md5", $resonse) == 0)
      return true;

   return false;
}

// ---------------------------------------------------------------------------
// Date Picker
// ---------------------------------------------------------------------------

function datePicker($title, $name, $year, $day, $month)
{
   $startyear = date("Y")-10;
   $endyear=date("Y")+1; 
   
   $months = array('','Januar','Februar','März','April','Mai',
                   'Juni','Juli','August', 'September','Oktober','November','Dezember');

   $html = $title . ": ";

   // day

   $html .= "  <select name=\"" . $name . "day\">\n";

   for ($i = 1; $i <= 31; $i++)
   {
      $sel = $i == $day  ? "SELECTED" : "";
      $html .= "     <option value='$i' " . $sel . ">$i</option>\n";
   }

   $html .= "  </select>\n";
   
   // month

   $html .= "  <select name=\"" . $name . "month\">\n";
   
   for ($i = 1; $i <= 12; $i++)
   {
      $sel = $i == $month ? "SELECTED" : "";
      $html .= "     <option value='$i' " . $sel . ">$months[$i]</option>\n";
   }

   $html.="  </select>\n";

   // year

   $html .= "  <select name=\"" . $name . "year\">\n";
   
   for ($i = $startyear; $i <= $endyear; $i++)
   {
      $sel = $i == $year  ? "SELECTED" : "";
      $html .= "     <option value='$i' " . $sel . ">$i</option>\n";
   }

   $html .= "  </select>\n";

   return $html;
}

// ---------------------------------------------------------------------------
// Check/Create Folder
// ---------------------------------------------------------------------------

function chkDir($path, $rights = 0777)
{ 
   if (!(is_dir($path) OR is_file($path) OR is_link($path)))
      return mkdir($path, $rights);
   else
      return true; 
}

// ---------------------------------------------------------------------------
// Request Action
// ---------------------------------------------------------------------------

function requestAction($cmd, $timeout, $address, $data, &$response)
{
   $timeout = time() + $timeout;
   $response = "";

   $address = mysql_real_escape_string($address);
   $data = mysql_real_escape_string($data);
   $cmd = mysql_real_escape_string($cmd);

   syslog(LOG_DEBUG, "p4: requesting ". $cmd . " with " . $address . ", '" . $data . "'");

   mysql_query("insert into jobs set requestat = now(), state = 'P', command = '$cmd', address = '$address', data = '$data'")
      or die("Error" . mysql_error());
   $id = mysql_insert_id();

   while (time() < $timeout)
   {
      usleep(10000);

      $result = mysql_query("select * from jobs where id = $id and state = 'D'")
         or die("Error" . mysql_error());

      if (mysql_numrows($result))
      {
         $buffer = mysql_result($result, 0, "result");
         list($state, $response) = explode(":", $buffer, 2);

         if ($state == "fail")
            return -2;

         return 0;
      }
   }

   syslog(LOG_DEBUG, "p4: timeout on " . $cmd);

   return -1;
}

// ---------------------------------------------------------------------------
// Seperator
// ---------------------------------------------------------------------------

function seperator($title, $top = 0, $level = 1, $width = 0, $center = 1)
{
   if ($level == 1)
      $class = "seperatorTitle1";
   else 
      $class = "seperatorTitle2";
   
   $style = ($top <> 0 || $width <> 0) ? "style=\"" : "";
   $top = ($top <> 0) ?  "position:absolute; top:".$top."px;" : "";
   $width = ($width <> 0) ?  " width:".$width."px;" : "";
   
   echo "        <div class=\"$class\"" . $style . $top . $width . (($style) ? "\"" : "") . ">\n";
   echo "          " . (($center) ? "<center>$title</center>\n" : "$title\n");
   echo "        </div><br/>\n";
}

// ---------------------------------------------------------------------------
// Write Config Item
// ---------------------------------------------------------------------------

function writeConfigItem($name, $value)
{
   if (requestAction("write-config", 3, 0, "$name:$value", $res) != 0)
   {
      echo " <br/>failed to write config item $name\n";
      return -1;
   }

   return 0;
}

// ---------------------------------------------------------------------------
// Read Config Item
// ---------------------------------------------------------------------------

function readConfigItem($name, &$value)
{
   if (requestAction("read-config", 3, 0, "$name", $value) != 0)
   {
      echo " <br/>failed to read config item $name\n";
      return -1;
   }

   return 0;
}

}
// ---------------------------------------------------------------------------
// Schema Selection
// ---------------------------------------------------------------------------

function schemaItem($new, $title, $schema, $kind)
{
   global $schema_path, $schema_pattern;
   $actual = "schema-$schema.png";
   
   $end = htmTags($new);
   echo "          $title:\n";
   echo "          <select class=checkbox name=\"$kind\">\n";

   $path  = $schema_path . $schema_pattern;
   
   foreach (glob($path) as $filename) 
   {
      $filename = basename($filename);

      $sel = ($actual == $filename) ? "SELECTED" : "";
      $tp  = substr(strstr($filename, ".", true), 7);
      echo "            <option value='$tp' " . $sel . ">$tp</option>\n";
   }
   
   echo "          </select>\n";
   echo $end;     
}

// ---------------------------------------------------------------------------
// Text Config items
// ---------------------------------------------------------------------------

function configStrItem($new, $title, $name, $value, $comment = "", $width = 200, $ro = "'", $ispwd = false)
{
   $end = htmTags($new);
   echo "          $title:\n";

   if ($ispwd)
   {
      echo "          <input class=\"inputEdit\" style=\"width:" . $width . "px\" type=\"password\" name=\"passwd1\" value=\"$value\"></input>\n";
      echo "          &nbsp;&nbsp;&nbsp;wiederholen:&nbsp;\n";
      echo "          <input class=\"inputEdit\" style=\"width:" . $width . "px\" type=\"password\" name=\"passwd2\" value=\"$value\"></input>\n";
   }
   else
      echo "          <input class=\"inputEdit\" style='width:" . $width . "px$ro type=\"text\" name=$name value=\"$value\"></input>\n";

   if ($comment != "")
      echo "          <span class=\"inputComment\">&nbsp;($comment)</span>\n";

   echo $end;     
}

// ---------------------------------------------------------------------------
// Checkbox Config items
// ---------------------------------------------------------------------------

function configBoolItem($new, $title, $name, $value, $comment = "", $ro = "")
{
   $end = htmTags($new);
   echo "          $title:\n";
   echo "          <input type=checkbox name=$name$ro" . ($value ? " checked" : "") . "></input>\n";

   if ($comment != "")
      echo "          <span class=\"inputComment\"> &nbsp;($comment)</span>\n";

   echo $end;     

}

// ---------------------------------------------------------------------------
// Option Config Item
// ---------------------------------------------------------------------------

function configOptionItem($new, $title, $name, $value, $options, $comment = "", $ro = "")
{
   $end = htmTags($new);
   echo "          $title:\n";
   echo "          <select class=checkbox name=$name$ro>\n";

   foreach (explode(" ", $options) as $option)
   {
      $opt = explode(",", $option);
      $sel = ($value == $opt[1]) ? "SELECTED" : "";
      echo "            <option value='$opt[1]' " . $sel . ">$opt[0]</option>\n";
   }
   
   echo "          </select>\n";
   if ($comment != "")
      echo "          <span class=\"inputComment\"> &nbsp;($comment)</span>\n";

   echo $end;     
}

// ---------------------------------------------------------------------------
// Set Start and End Div-Tags
// ---------------------------------------------------------------------------

function htmTags($new)
{
  switch ($new) { 
   	case 1: echo "        <div class=\"input\">\n"; $end = ""; break;
   	case 2: echo "        </div><br/>\n        <div class=\"input\">\n"; $end = ""; break;
   	case 3: echo "        <div class=\"input\">\n" ; $end = "        </div><br/>\n"; break;
   	case 4: echo "          &nbsp;|&nbsp;\n" ; $end = "        </div><br/>\n"; break;
   	case 5: echo "          &nbsp;|&nbsp;\n"; $end = ""; break;
   	case 6: echo "        </div><br/>\n        <div class=\"input\">\n" ; $end = "        </div><br/>\n"; break;
   	case 7: echo "          <br /><br />\n"; $end = ""; break;
  }
  return $end;
}

?>
