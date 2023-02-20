<?php ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
<head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="generator" content="SVXLink" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Architects+Daughter&family=Fredoka+One&family=Tourney&family=Oswald&display=swap" rel="stylesheet">
<link rel="shortcut icon" href="/svxlnk/images/favicon.ico" sizes="16x16 32x32" type="image/png">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<?php echo ("<title>Dashboard FM-Funknetz</title>"); ?>

<style>
table {background-color: #F1F1F1; }
th, td { border: 1px solid black; }
tr:nth-child(even) { background-color: #D6EEEE; }
button {
  background-color: orange;
  border-radius: 15px;
  padding: 5px 15px;
  text-align: center;
  font-size: 16px;
  font-family: monospace;
  font-weight: normal;
  font-style: normal;
}
</style>

</head>

<body style="background-color: #e1e1e1;font: 16pt monospace;">
<script type="text/javascript">
   setTimeout(() => { document.location.reload(); }, 1200000);
</script>

<?php

// V1.1
// (C) 2023 by Heiko Amft, DL1BZ
// requires PHP > 7.0.0 and php-curl and php-xml

// check if PHP > 7.0.0, older versions not usable
if ( !version_compare(phpversion(), '7.0.0', '>=')) { die("ERROR: PHP version ".PHP_VERSION." too old.\n"); }

// add statistics (1) or not (0)
define("STATISTICS", 1);

// define URL SVXReflector for status pull
$url="https://status.thueringen.link";

if ($url!="") 
   {
      //  Initiate curl
      $ch = curl_init();
      // Will return the response, if false it print the response
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // fix SSL verification
      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
      // Set the url
      curl_setopt($ch, CURLOPT_URL,$url);
      // set error or failure options
      curl_setopt($ch,CURLOPT_FAILONERROR, true);
      // Execute
      $result=curl_exec($ch);
      // check if connect to URL has errors
      if (curl_errno($ch))
         {
            // output an error message or reason
            echo 'Request Error:'.curl_error($ch);
            // exit the script
            exit();
         }

      // Closing
      curl_close($ch);
      // explode json to an assoziative array
      $nodes = json_decode($result, true); 
   }
 else 
   {
      // clear array if any error
      $nodes="";
      // exit the script
      die("ERROR: Problem with Array/JSON source - exit...\n");
   }

      // define new array
      $repeater=[];

      // init counter
      $counter = 0;

      // Change the line below to your timezone!
      date_default_timezone_set('Europe/Berlin');
      $date = date('d.m.Y H:i', time());

      if (isset($_REQUEST['select']))
         {
            $auswahl = $_REQUEST['select'];
         }
      else
         {
            $auswahl = "ALL";
         }

      switch($auswahl)
         {

            case 'HS':
            $suchmuster='/^[D][A|C-L|N|Q-Z][0-9][A-Z]*/m';
            $topic = "<H2>FM-Funknetz - Hotspots (nur DL): ";
            break;

            case 'NODES':
            $suchmuster='/^[0-9|A-S|U-W|Y][A-Z][0-9][A-Z]*/m';
            $topic = "<H2>FM-Funknetz - Nodes (ohne Bridges): ";
            break;

            case 'DL-RPTR':
            $suchmuster='/^[D][B|M|O|P][0][A-Z][A-Z]*/m';
            $topic = "<H2>FM-Funknetz - Repeater (nur DL): ";
            break;

            default:
            // $suchmuster='/^[0-9|A-S|Y][A-Z][0-9][A-Z]*/m';
            $suchmuster='/^[0-9|A-Z]*/m';
            $topic = "<H2>FM-Funknetz - Nodes (inkl. Bridges): ";
            break;
         }

      // Filter DB0/DM0/DO0/DP0 with RegEx
      // $suchmuster='/[D][B|M|O|P][0][A-Z][A-Z]*/m';

      // loop through array, callsigns as key
      foreach ($nodes['nodes'] as $key =>$value)
         {
               if (preg_match($suchmuster, strtoupper($key)))
               {
                  //  $counter++;
                  // add nodeLocation to new array
                  // check if value exists and is not empty
                  if (isset($nodes['nodes'][$key]['nodeLocation']) && ($nodes['nodes'][$key]['nodeLocation'] != ""))
                     {
                        $repeater['repeater'][$key]['nodeLocation'] = $nodes['nodes'][$key]['nodeLocation'];
                     }
                  // check if using SM0SVX original info.json
                  elseif (isset($nodes['nodes'][$key]['qth'][0]['name']) && ($nodes['nodes'][$key]['qth'][0]['name'] != ""))
                     {
                        $repeater['repeater'][$key]['nodeLocation'] = $nodes['nodes'][$key]['qth'][0]['name'];
                     }
                  else
                     {
                        // otherwise we define a NULL string for prevent PHP errors undefined index
                        $repeater['repeater'][$key]['nodeLocation'] = "<i><font color=\"red\">Angabe fehlt</font></i>";
                     }
                  // add DefaultTG to new array
                  // check if value exists and Default TG > 0
                  if (isset($nodes['nodes'][$key]['DefaultTG']) && (intval($nodes['nodes'][$key]['DefaultTG']) > 0))
                     {
                        $repeater['repeater'][$key]['DefaultTG'] = intval($nodes['nodes'][$key]['DefaultTG']);
                     }
                  else
                     {
                        // otherwise we define a NULL string for prevent PHP errors undefined index
                        $repeater['repeater'][$key]['DefaultTG'] = "<i>keine</i>";
                     }
                  if (isset($nodes['nodes'][$key]['TXFREQ']))
                     {
                        $repeater['repeater'][$key]['TXFREQ'] = $nodes['nodes'][$key]['TXFREQ'];
                     }
                  else
                     {
                        $repeater['repeater'][$key]['TXFREQ'] = "<i><font color=\"red\">Angabe fehlt</font></i>";
                     }
                  if (isset($nodes['nodes'][$key]['CTCSS']) && ($nodes['nodes'][$key]['CTCSS'] != "0"))
                     {
                        $repeater['repeater'][$key]['CTCSS'] = $nodes['nodes'][$key]['CTCSS'];
                     }
                  else
                     {
                        $repeater['repeater'][$key]['CTCSS'] = NULL;
                     }
                  $counter++;
               }
         }

// correct counter value
$counter=$counter-1;

function _show($data,$direction)
   {
      if ($direction == "c")
         {
            $_line="<TD><CENTER>".$data."</CENTER></TD>";
         }
      else
         {
            $_line="<TD>".$data."</TD>";
         }
      return ($_line);
   }

// echo "<H2>FM-Funknetz - &Ouml;ffentliche Repeater online: ".$counter."<BR>Stand: ".$date." Uhr</H2>";
// echo "<H2>FM-Funknetz - Nodes (ohne Bridges): ".$counter."<BR>Stand: ".$date." Uhr</H2>";

echo $topic.$counter."<BR>Stand: ".$date." Uhr</H2>";

echo "<form>";
echo "<button type=\"submit\" name=\"select\" value=\"ALL\">Alles anzeigen</button>";
echo "<button type=\"submit\" name=\"select\" value=\"NODES\">Alle Nodes (ohne Bridges) anzeigen</button>";
echo "<button type=\"submit\" name=\"select\" value=\"DL-RPTR\">Alle Repeater (DB0/DM0/DO0/DP0) anzeigen</button>";
echo "<button type=\"submit\" name=\"select\" value=\"HS\">Alle Hotspots (nur DL) anzeigen</button>";
echo "</form>";

echo "<p>";

echo "<table style=\"width:1380px\">";

// echo "<th style=\"width:10%\">Nr.</th>";
echo "<th style=\"width:10%\">Node</th>";
echo "<th style=\"width:40%\">Location</th>";
echo "<th style=\"width:20%\">TX Frequenz (MHz)</th>";
echo "<th style=\"width:20%\">CTCSS</th>";
echo "<th style=\"width:10%\">Default TG</th>";

foreach ($repeater['repeater'] as $key =>$value)
   {
      echo "<TR>"._show($key,l),_show($repeater['repeater'][$key]['nodeLocation'],l),_show($repeater['repeater'][$key]['TXFREQ'],c),_show($repeater['repeater'][$key]['CTCSS'],c),_show($repeater['repeater'][$key]['DefaultTG'],c)."</TR>";
   }

echo "</table>";

// end of script
?>

</body>
</html>
