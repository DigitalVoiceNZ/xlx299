<?php

require_once("reflectorlist.php");
$Reflectors = GetReflectorList();
?>
<div class="container">
<div class="row">
  <div class="col">
<?php
if (($handle = fopen(dirname(__FILE__) . "/modules.csv", "r")) !== FALSE) {
?>
    <h2>Modules</h2>
    <table class="table table-striped">
      <tr>
        <th style="white-space: nowrap;">Module<br><span style="font-size:smaller">D-Star, DMR, YSF</span></th>
        <th style="white-space: nowrap;">DMR</th>
        <th style="white-space: nowrap;">Peanut<br>Room</th>
        <th style="white-space: nowrap;">Links</th>
        <th style="white-space: nowrap;">Details</th>
        <th>Activity</th>
      </tr>
<?php
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $data = array_map('trim', $data);
        // skip blank lines
        if (count($data) == 0 || strlen($data[0] ?? '') == 0) {
            continue;
        }
        // five fields: module, talkgroup, peanut room, links, description
        // (extra commas belong to the description)
        if (count($data) > 5) {
            $data[4] = implode(', ', array_slice($data, 4));
        }
        $data = array_pad(array_slice($data, 0, 5), 5, '');
        if ((substr($data[0], 0, 1) == '#') || (substr($data[0], 0, 1) == ';')) {
            continue;
        }
        $modname = str_replace('*', '&nbsp;<img src="/img/peanut-32x32.png" height=20 width=20 alt="Peanut enabled">', $data[0]);
        if (stripos($data[1], "tgif") === 0) {
            $tg = preg_replace('/[ a-z]*(\d+)/i', '<a href="https://prime.tgif.network/tgprofile.php?id=$1">$0</a>', $data[1]);
        } elseif (stripos($data[1], "f") === 0) {
            $tg = '<a href="https://FreeDMR.DVNZ.nz/">' . $data[1] . '</a>';
        } elseif (stripos($data[1], "z") === 0) {
            $tg = '<a href="http://trbo.arec.info:42420/CallWatch">' . $data[1] . '</a>';
        } else {
            $tg = preg_replace('/[ a-z]*(\d+)/i', '<a href="https://brandmeister.network/?page=lh&DestinationID=$1">$0</a>', $data[1]);
        }
        $links = explode('<br>', $data[3]);
        for ($i = 0; $i < count($links); $i++) {
            $l = trim($links[$i]);
            if (substr($l, 0, 3) == 'XLX') {
                $xlx = substr($l, 0, 6);
                if (array_key_exists($xlx, $Reflectors)) {
                    $links[$i] = '<a href="' . $Reflectors[$xlx]['dashboardurl'] . '">' . $l . '</a>';
                }
            }
        }
        $links = implode('<br>', $links);
        echo "<tr id='mod-{$modname[0]}'>";
        echo '  <th style="white-space: nowrap;">XLX299-' . $modname . "</th>";
        echo "  <td>" . $tg . "</td>";
        echo "  <td>" . $data[2] . "</td>";
        echo "  <td>" . $links . "</td>";
        echo "  <td>" . $data[4] . "</td>";
        echo "  <td><ul class='act-calls' id='act-{$modname[0]}'></ul></td>";
        echo "</tr>";
    }
    fclose($handle);
    echo "</table>";
}
?>
  </div>
</div>
</div>
<script src="./js/pocketbase.umd.js"></script>
<script src="./js/activity.js"></script>

