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