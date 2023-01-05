<div class="container">
<div class="row">
  <div class="col">
<?php
if (($handle = fopen(dirname(__FILE__) . "/modules.csv", "r")) !== FALSE) {
?>
    <h2>Modules</h2>
    <table class="table table-striped">
      <tr>
        <th style="white-space: nowrap; font-size: smaller">Module</th>
        <th style="white-space: nowrap; font-size: smaller">Talkgroup</th>
        <th style="white-space: nowrap; font-size: smaller">M17</th>
        <th style="white-space: nowrap; font-size: smaller">Peanut<br>Room</th>
        <th style="white-space: nowrap; font-size: smaller">Details</th>
      </tr>
<?php
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $data = array_map('trim', $data);
        switch(count($data)) {
        case 0:
            continue 2;
        case 1:
            if (strlen($data[0]) == 0) {
                continue 2;
            }
            $data[1] = '';
        case 2:
            $data[2] = '';
        case 3:
            $data[3] = '';
            break;
        case 4:
            $data[4] = '';
            break;
        case 5:
            break;
        default:
            $data[4] = implode(', ', array_slice($data, 4));
            break;
        }
        if ((substr($data[0], 0, 1) == '#') || (substr($data[0], 0, 1) == ';')) {
            continue;
        }
        $modname = str_replace('*', '&nbsp;<img src="/img/peanut-32x32.png" height=20 width=20 alt="Peanut enabled">', $data[0]);
	if (stripos($data[1], "tgif") === 0) {
		$tg = preg_replace('/[ a-z]*(\d+)/i', '<a href="https://prime.tgif.network/tgprofile.php?id=$1">$0</a>', $data[1]);
    } elseif (stripos($data[1], "f") === 0) {
        $tg = '<a href="https://FreeDMR.DVNZ.nz/">' . $data[1] . '</a>';
	} else {
		$tg = preg_replace('/[ a-z]*(\d+)/i', '<a href="https://brandmeister.network/?page=lh&DestinationID=$1">$0</a>', $data[1]);
	}
        echo "<tr>";
        echo '  <th style="white-space: nowrap;">XLX299-' . $modname . "</th>";
        echo "  <td>" . $tg . "</td>";
        echo "  <td>" . $data[2] . "</td>";
        echo "  <td>" . $data[3] . "</td>";
        echo "  <td>" . $data[4] . "</td>";
        echo "</tr>";
    }
    fclose($handle);
    echo "</table>";
}
?>
  </div>
</div>
</div>
<script>
  clearTimeout(PageRefresh);
</script>

