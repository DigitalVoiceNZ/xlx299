<?php

define("THISXRF", "XRF299");
define("PEANUTAPI", "http://peanut.pa7lim.nl/api/");
// APCu PNUT CACHE
define("PNUTLIMIT", 10);        // minimum seconds between API fetches
define("PNUTREFRESH", 170);     // normal seconds between API fetches
define("PNUTBACKOFF", 15*60);   // seconds if API read error

$pnutrooms = [
    "A" => "XRF925A",
    "B" => "XRF299B",
    "E" => "XRF299E",
    "G" => "TGF969",
    "J" => "XRF299J",
    "R" => "XRF299R",
];

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
    error_log('Peanut cache missing');
}
if (!$fetched || !apcu_exists('PNUTCACHEVALID')) {
    if (apcu_add('PNUTAPILOCK', time(), PNUTLIMIT)) {
        // we have the right to update the cache
        error_log('Peanut API hit');
        $json = file_get_contents(PEANUTAPI . "whois.json");
        if (!$json) {
            // API read error, don't hit API for a while
            apcu_store('PNUTAPILOCK', time(), 15*60);
            error_log("Peanut API read error");
        } else {
            $pnut = array_filter(json_decode($json), "inThisXRF");
            apcu_store('PNUTCACHE', $pnut, 60*60);
            apcu_store('PNUTCACHEVALID', time(), PNUTREFRESH);
        }
    } else {
        error_log('Peanut API locked, using old data');
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

<div class="row">
   <div class="col-md-9">
      <table class="table table-striped-custom table-hover">
<?php
if ($PageOptions['UserPage']['ShowFilter']) {
  echo '
 <tr>
   <th colspan="8">
      <table width="100%" border="0">
         <tr>
            <td align="left">
               <form name="frmFilterCallSign" method="post" action="./index.php">
                  <input type="hidden" name="do" value="SetFilter" />
                  <input type="text" class="FilterField" value="'.$_SESSION['FilterCallSign'].'" name="txtSetCallsignFilter" placeholder="Callsign" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                  <input type="submit" value="Apply" class="FilterSubmit" />
               </form>
            </td>';
              if (($_SESSION['FilterModule'] != null) || ($_SESSION['FilterCallSign'] != null)) {
                  echo '
         <td><a href="./index.php?do=resetfilter" class="smalllink">Disable filters</a></td>';
              }
              echo '            
            <td align="right" style="padding-right:3px;">
               <form name="frmFilterModule" method="post" action="./index.php">
                  <input type="hidden" name="do" value="SetFilter" />
                  <input type="text" class="FilterField" value="'.$_SESSION['FilterModule'].'" name="txtSetModuleFilter" placeholder="Module" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                  <input type="submit" value="Apply" class="FilterSubmit" />
               </form>
            </td>
      </table>
   </th>
</tr>';
}
?>
         <tr class="table-center">   
            <th>#</th>
            <th>Flag</th>
            <th>Callsign</th>
            <th>Suffix</th>
            <th>DPRS</th>
            <th>Via / Peer</th>
            <th>Last heard</th>
            <th>Module</th>
         </tr>
<?php

$Reflector->LoadFlags();
for ($i=0;$i<$Reflector->StationCount();$i++) {
    // if PNUT user and more recent than cache and not cached
    if (($Reflector->Stations[$i]->GetSuffix() == 'PNUT')
        && (time() - $Reflector->Stations[$i]->GetLastHeardTime() < PNUTREFRESH)
        && (!inCache($Reflector->Stations[$i]->GetCallsignOnly(), $pnutrooms[$Reflector->Stations[$i]->GetModule()]))) {
        apcu_delete('PNUTCACHEVALID');
        error_log("PNUT cache invalidated: " . $Reflector->Stations[$i]->GetCallsignOnly() . " on " . $Reflector->Stations[$i]->GetModule() . ' at ' . $Reflector->Stations[$i]->GetLastHeardTime());
    }
    $ShowThisStation = true;
    if ($PageOptions['UserPage']['ShowFilter']) {
        $CS = true;
        if ($_SESSION['FilterCallSign'] != null) {
            if (!fnmatch($_SESSION['FilterCallSign'], $Reflector->Stations[$i]->GetCallSign(), FNM_CASEFOLD)) {
                $CS = false;
            }
        }
        $MO = true;
        if ($_SESSION['FilterModule'] != null) {
            if (trim(strtolower($_SESSION['FilterModule'])) != strtolower($Reflector->Stations[$i]->GetModule())) {
                $MO = false;
            }
        }

        $ShowThisStation = ($CS && $MO);
    }

    if ($ShowThisStation) {

        echo '
      <tr class="table-center">
       <td>';
        if ($i == 0 && $Reflector->Stations[$i]->GetLastHeardTime() > (time() - 60)) {
            echo '<img src="./img/tx.gif" style="margin-top:3px;" height="20"/>';
        } else {
            echo($i + 1);
        }


        echo '</td>
        <td>';

        list ($Flag, $Name) = $Reflector->GetFlag($Reflector->Stations[$i]->GetCallSign());
        if (file_exists("./img/flags/" . $Flag . ".png")) {
            echo '<a href="#" class="tip"><img src="./img/flags/' . $Flag . '.png" class="table-flag" alt="' . $Name . '"><span>' . $Name . '</span></a>';
        }
        echo '</td>
   <td><a href="https://www.qrz.com/db/' . $Reflector->Stations[$i]->GetCallsignOnly() . '" class="pl" target="_blank">' . $Reflector->Stations[$i]->GetCallsignOnly() . '</a></td>
   <td>' . $Reflector->Stations[$i]->GetSuffix() . '</td>
   <td><a href="http://www.aprs.fi/' . $Reflector->Stations[$i]->GetCallsignOnly() . '" class="pl" target="_blank"><img src="./img/sat.png" alt=""></a></td>
   <td>' . $Reflector->Stations[$i]->GetVia();
        if ($Reflector->Stations[$i]->GetPeer() != $Reflector->GetReflectorName()) {
            echo ' / ' . $Reflector->Stations[$i]->GetPeer();
        }
        echo '</td>
   <td>' . @date("d.m.Y H:i", $Reflector->Stations[$i]->GetLastHeardTime()) . '</td>
   <td>' . $Reflector->Stations[$i]->GetModule() . '</td>
 </tr>';
    }
    if ($i == $PageOptions['LastHeardPage']['LimitTo']) {
        $i = $Reflector->StationCount() + 1;
    }
}

?> 
 
      </table>
   </div>
   <div class="col-md-3">
      <table class="table table-striped-custom table-hover moduleusers">
         <?php 

$Modules = $Reflector->GetModules();
sort($Modules, SORT_STRING);
echo '<tr>';
for ($i=0;$i<count($Modules);$i++) {
   
   if (isset($PageOptions['ModuleIcons'][$Modules[$i]]))
   {
      $desc = "";
      if (isset($PageOptions['ModuleNames'][$Modules[$i]]))
        $desc = '('.$PageOptions['ModuleNames'][$Modules[$i]].')';
    
      echo '<th>'.$Modules[$i].'<br/>'.$desc.'<br/><img class="ModuleIcon" src="'.$PageOptions['ModuleIcons'][$Modules[$i]].'"></th>';
   }
   else if (isset($PageOptions['ModuleNames'][$Modules[$i]])) 
   {
      echo '<th>'.$Modules[$i].'</br>'.$PageOptions['ModuleNames'][$Modules[$i]].'</th>';
       //if (trim($PageOptions['ModuleNames'][$Modules[$i]]) != "") {
       //    echo '<br />';
       //}
      //echo .'</th>';
   }
   else {
   echo '
  
      <th>'.$Modules[$i].'</th>';
   }
}

echo '</tr><tr>';

$GlobalPositions = array();

for ($i=0;$i<count($Modules);$i++) {
    
   $Users = $Reflector->GetNodesInModulesByID($Modules[$i]);
   echo '<td><table class="table table-hover">';

   $UserCheckedArray = array();
   
   for ($j=0;$j<count($Users);$j++) {
       $Displayname = $Reflector->GetCallsignAndSuffixByID($Users[$j]);
      echo '
            <tr>
               <td><a href="https://aprs.fi/'.$Displayname.'" class="pl" target="_blank">'.$Displayname.'</a> </td>
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
                   <td><a href="https://aprs.fi/'.$call.'" class="pl" target="_blank">'.$Displayname.'</a> </td>
                </tr>';
      }
   }
   echo '</table></td>';
}

echo '</tr>';

?>
      </table>
   </div>
</div>
