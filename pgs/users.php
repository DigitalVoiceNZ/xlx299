<?php
require_once("reflectorlist.php");
$Reflectors = GetReflectorList();

/**
 * polyfill str_contins for old php
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}

/**
 * array_filter predicate to filter PNUT users in our rooms
 *
 * @param object $v PNUT whois object.
 *
 * @return boolean if in our room and not a transcoder
 */
function inThisXRF($v) {
    global $pnutrooms;
    return in_array($v->room, $pnutrooms) && ($v->device != 'TRANSCODER');
}

/**
 * Check PNUT cache for call $c in room $room
 *
 * @param string $c call.
 * @param string $room PNUT room name.
 *
 * @return boolean
 */
function inCache($c, $room) {
    $cache = apcu_fetch('PNUTCACHE');
    foreach ($cache as $ce) {
        if (($ce->Call == $c) && ($ce->room == $room)) {
            return True;
        }
    }
    return False;
}

$pnut = apcu_fetch('PNUTCACHE', $fetched);
if (!$fetched) {
    $pnut = [];
}
if (!$fetched || !apcu_exists('PNUTCACHEVALID')) {
    if (apcu_add('PNUTAPILOCK', time(), PNUTLIMIT)) {
        // we have the right to update the cache
        $json = file_get_contents(PEANUTAPI . "whois.json");
        if (!$json) {
            // API read error, don't hit API for a while
            apcu_store('PNUTAPILOCK', time(), 15*60);
        } else {
            $pnut = array_filter(json_decode($json), "inThisXRF");
            apcu_store('PNUTCACHE', $pnut, 60*60);
            apcu_store('PNUTCACHEVALID', time(), PNUTREFRESH);
        }
    }
}

if (!isset($_SESSION['FilterCallSign'])) {
    $_SESSION['FilterCallSign'] = null;
}

if (!isset($_SESSION['FilterModule'])) {
    $_SESSION['FilterModule'] = null;
}

if (isset($_POST['do'])) {
    if ($_POST['do'] == 'SetFilter') {

        if (isset($_POST['txtSetCallsignFilter'])) {
            $_POST['txtSetCallsignFilter'] = trim($_POST['txtSetCallsignFilter']);
            if ($_POST['txtSetCallsignFilter'] == "") {
                $_SESSION['FilterCallSign'] = null;
            }
            else {
                $_SESSION['FilterCallSign'] = $_POST['txtSetCallsignFilter'];
                if (strpos($_SESSION['FilterCallSign'], "*") === false) {
                    $_SESSION['FilterCallSign'] = "*".$_SESSION['FilterCallSign']."*";
                }
            }

        }

        if (isset($_POST['txtSetModuleFilter'])) {
            $_POST['txtSetModuleFilter'] = trim($_POST['txtSetModuleFilter']);
            if ($_POST['txtSetModuleFilter'] == "") {
                $_SESSION['FilterModule'] = null;
            }
            else {
                $_SESSION['FilterModule'] = $_POST['txtSetModuleFilter'];
            }

        }
    }
}

if (isset($_GET['do'])) {
    if ($_GET['do'] == "resetfilter") {
        $_SESSION['FilterModule'] = null;
        $_SESSION['FilterCallSign'] = null;
    }
}

?>

<div class="container recentactivity">
    <div class="row">
        <h4>Recent Activity</h4>
    </div>
    <div class="row">
        <?php
        for ($i=0; $i<26; $i++) {
            $m = chr(ord('A')+$i);
            echo '<div id="mod-'.$m.'" class="card" style="display:none;">';
            echo '<div class="card-body" style="padding: 0.5rem;">';
            echo '<div class="card-title"><h5>'.$PageOptions['ReflectorName'].'-'.$m.'</h5><p class="shortname">';
            if (array_key_exists($m, $PageOptions['ShortNames'])) {
                echo $PageOptions['ShortNames'][$m];
            }
            echo '</p></div>';
            echo '<ul class="act-calls" id="act-'.$m.'"></ul>';
            echo '</div>';
            echo '</div>';
        } ?>
    </div>
</div>

<?php
	echo <<<EOD
<nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-fixed-top">
</nav>
<div class="container">
  <div id="connections" class="row">
EOD;

$Modules = $Reflector->GetModules();
// make sure XLX connections are represented
for ($j=0;$j<$Reflector->PeerCount();$j++) {
   foreach (str_split($Reflector->Peers[$j]->GetLinkedModule()) as $m) {
      if (!in_array($m, $Modules)) {
         array_push($Modules, $m);
      }
   }
}
sort($Modules, SORT_STRING);
for ($i=0;$i<count($Modules);$i++) {
   
   echo '<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 py-2">';
   echo '  <div style="height: 150px;">';
   if (isset($PageOptions['ModuleIcons'][$Modules[$i]]))
   {
      $desc = "";
      if (isset($PageOptions['ModuleNames'][$Modules[$i]]))
        $desc = $PageOptions['ModuleNames'][$Modules[$i]];
        $iconfile = $PageOptions['ModuleIcons'][$Modules[$i]];
        $iconclass = (strpos($iconfile, '-130') !== false) ? 'ModuleIconW' : 'ModuleIcon';
      echo '<b>'.$PageOptions['ReflectorName'].'-'.$Modules[$i].'</b><br><img class="'.$iconclass.'" alt="icon" src="'.$PageOptions['ModuleIcons'][$Modules[$i]].'"><br><b>'.$desc.'</b>';
   }
   else if (isset($PageOptions['ModuleNames'][$Modules[$i]])) 
   {
      echo $Modules[$i].'<br>'.$PageOptions['ModuleNames'][$Modules[$i]];
   }
   else {
      echo $Modules[$i];
   }
   echo '  </div>'; 
   $Users = $Reflector->GetNodesInModulesByID($Modules[$i]);
   echo '<table class="table table-sm table-hover">';

   // insert the XLX reflectors
   for ($j=0;$j<$Reflector->PeerCount();$j++) {
       if (str_contains($Reflector->Peers[$j]->GetLinkedModule(), $Modules[$i])) {
           echo '<tr><td>';
           $Name = $Reflector->Peers[$j]->GetCallsign();
           if (isset($Reflectors[$Name])) {
               echo '<a href="'.$Reflectors[$Name]['dashboardurl'].'" ';
               echo 'data-toggle="tooltip" title="XLX&#013;LH: ';
               echo date("Y-m-d H:i", $Reflector->Peers[$j]->GetLastHeardTime()).'&#013;';
               echo 'Cx: '.date("Y-m-d H:i", $Reflector->Peers[$j]->GetConnectTime()).'">';
           }
           echo $Name.'-'.$Modules[$i];
           if (isset($Reflectors[$Name])) {
               echo '</a>';
           }
           echo '</td></tr>';
       }
   }

   $UserCheckedArray = array();
   
   for ($j=0;$j<count($Users);$j++) {
       [$Displayname, $protocol] = $Reflector->GetCallsignSuffixAndProtocolByID($Users[$j]);
       $protocol = str_replace('DMRMmdvm', 'DMR', $protocol);
       [$lh, $cx] = $Reflector->GetLastHeardAndConnectionTimes($Users[$j]);
      echo '
            <tr>
               <td><a href="http://www.aprs.fi/'.$Displayname.'" class="pl" target="_blank" data-toggle="tooltip" title="'
               .$protocol.'&#013;LH: '.date("Y-m-d H:i", $lh).'&#013;Cx: '.date("Y-m-d H:i", $cx).'">'.$Displayname.'</a></td>
            </tr>';
      $UserCheckedArray[] = $Users[$j];
   }
   // add Peanut users on this module
   $thismodule = array_key_exists($Modules[$i], $pnutrooms) ? $pnutrooms[$Modules[$i]] : '---';
   foreach ($pnut as $pu) {
       if ($pu->room == $thismodule) {
           $call = $Displayname = $pu->Call;
           switch (strtolower($pu->device)) {
              case 'android':
                  $Displayname .= '&nbsp;<i class="material-icons">android</i>';
                  break;
              case 'windows':
                  $Displayname .= '&nbsp;<i class="material-icons small">laptop_windows</i>';
                  break;
	      default:
                  $Displayname .= '-P';
          }
          echo '
                <tr>
                   <td><a href="https://aprs.fi/'.$call.'" class="pl" target="_blank" data-toggle="tooltip" title="Peanut">'.$Displayname.'</a> </td>
                </tr>';
      }
   }
   echo '</table></div>';
}

echo <<<EOD
  </div>
</div>
EOD;
?>
<script src="./js/pocketbase.umd.js"></script>
<script src="./js/activity-user.js"></script>
