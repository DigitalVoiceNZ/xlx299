<?php

require_once("reflectorlist.php");
$Reflectors = GetReflectorList();

?>


<div class="container">
<table class="table table-striped-custom table-hover">
   <tr class="table-center">  
      <th>#</th>
      <th>Reflector</th>
      <th>Country</th>
      <th>Service</th>
      <th>Comment</th>
   </tr>
<?php

$i = 1;
foreach ($Reflectors as $NAME => $reflector) {
   $COUNTRY       = $reflector["country"];
   $LASTCONTACT   = $reflector["lastcontact"];
   $COMMENT       = $reflector["comment"];
   $DASHBOARDURL  = $reflector["dashboardurl"];
   
   echo '
 <tr class="table-center">
   <td>'.($i++).'</td>
   <td><a href="'.$DASHBOARDURL.'" target="_blank" class="listinglink" title="Visit the Dashboard of&nbsp;'.$NAME.'">'.$NAME.'</a></td>
   <td>'.$COUNTRY.'</td>
   <td><img src="./img/'; if ($LASTCONTACT<(time()-1800)) { echo 'down'; } ELSE { echo 'up'; } echo '.png" class="table-status" alt=""></td>
   <td>'.$COMMENT.'</td>
 </tr>';
}

?>
</table>
</div>
   
