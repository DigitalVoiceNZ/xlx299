<?php

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

<div class="container xusers">
  <div class="row">
     <div class="table-responsive">
        <table class="table table-sm table-striped-custom table-hover">
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
  	 <thead>
               <tr class="table-center">   
                   <th scope="col">#</th>
                   <th scope="col">Flag</th>
                   <th scope="col">Callsign</th>
                   <th scope="col">Suffix</th>
                   <th scope="col">DPRS</th>
                   <th scope="col">Via / Peer</th>
                   <th scope="col">Last heard</th>
                   <th scope="col">Module</th>
  	      </tr>
          </thead>
  <?php
  
  $Reflector->LoadFlags();
  for ($i=0;$i<$Reflector->StationCount();$i++) {
      // if PNUT user and more recent than cache and not cached
      if (($Reflector->Stations[$i]->GetSuffix() == 'PNUT')
          && array_key_exists($Reflector->Stations[$i]->GetModule(), $pnutrooms)
          && (time() - $Reflector->Stations[$i]->GetLastHeardTime() < PNUTREFRESH)
          && (!inCache($Reflector->Stations[$i]->GetCallsignOnly(), $pnutrooms[$Reflector->Stations[$i]->GetModule()]))
          && !apcu_exists('PNUTAPILOCK')) {
          apcu_delete('PNUTCACHEVALID');
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
              echo '<img src="./img/tx.gif" alt="transmitting" style="margin-top:3px;" height="20">';
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
     <td><a href="http://www.aprs.fi/' . $Reflector->Stations[$i]->GetCallsignOnly() . '" class="pl" target="_blank"><i class="material-icons md-48">satellite</i></a></td>
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
  </div>
</div>
<?php
	echo <<<EOD
<nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-fixed-top">
  <div>
    <p>&nbsp;</p>
  </div>
</nav>
<div class="container">
  <div class="row">
EOD;

$Modules = $Reflector->GetModules();
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

   $UserCheckedArray = array();
   
   for ($j=0;$j<count($Users);$j++) {
       [$Displayname, $protocol] = $Reflector->GetCallsignSuffixAndProtocolByID($Users[$j]);
       $protocol = str_replace('DMRMmdvm', 'DMR', $protocol);
      echo '
            <tr>
               <td><a href="http://www.aprs.fi/'.$Displayname.'" class="pl" target="_blank">'.$Displayname.'</a> <sup>'.$protocol.'</sup> </td>
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
   echo '</table></div>';
}

echo <<<EOD
  </div>
</div>
EOD;
