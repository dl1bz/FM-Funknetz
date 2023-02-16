<?php

// V1.0
// (C) 2023 by Heiko Amft, DL1BZ
// requires PHP > 7.0.0 and php-curl and php-xml

// check if PHP > 7.0.0, older versions not usable
if ( !version_compare(phpversion(), '7.0.0', '>=')) { die("ERROR: PHP version ".PHP_VERSION." too old.\n"); }

// add statistics (1) or not (0)
define("STATISTICS", 0);

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

// if all ok we start
if ($nodes!="")
   {
      // define new array
      $repeater=[];
      // init counter
      $counter = 0;
      // loop through array, callsigns as key
      foreach ($nodes['nodes'] as $key =>$value)
         {
            // Filter DB0/DM0/DO0/DP0 with RegEx
            if (preg_match('/[D][B|M|O|P][0][A-Z][A-Z]*/m' , strtoupper($key)))
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
                        $repeater['repeater'][$key]['nodeLocation'] = NULL;
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
                        $repeater['repeater'][$key]['DefaultTG'] = NULL;
                     }
                  $counter++;
               }
         }

// correct counter value
$counter=$counter-1;

// write additional statistics to array
if (STATISTICS)
   {
      $repeater['statistics']['count_repeaters'] = $counter;
   }

// define MIME as json content and cache prevention
header('Content-type: application/json; charset=utf-8');
header("Last-Modified: ".gmdate("d.M.Y H:i:s")." GMT");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Connection: close');
// generate new json output
print(json_encode($repeater,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));

   }
// end of script
?>
