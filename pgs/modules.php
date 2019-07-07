<div class="row">
  <div class="col-md-6">
<?php
if (($handle = fopen(dirname(__FILE__) . "/modules.csv", "r")) !== FALSE) {
?>
    <h2>Modules</h2>
    <table class="table table-striped">
      <tr>
        <th style="white-space: nowrap; font-size: smaller">Mod</th>
        <th style="white-space: nowrap; font-size: smaller">Brandmeister<br>Talkgroup</th>
        <th style="white-space: nowrap; font-size: smaller">Peanut<br>Room</th>
        <th style="white-space: nowrap; font-size: smaller">Details</th>
      </tr>
<?php
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $data = array_map('trim', $data);
        switch(count($data)) {
        case 0:
            continue;
        case 3:
            $data[3] = '';
        case 2:
            $data[2] = '';
        case 1:
            $data[1] = '';
            break;
        case 4:
            break;
        default:
            $data[3] = implode(', ', array_slice($data, 3));
            break;
        }
        if ((substr($data[0], 0, 1) == '#') || (substr($data[0], 0, 1) == ';')) {
            continue;
        }
        $modname = str_replace('*', '&nbsp;<img src="/img/peanut-32x32.png" height=20 width=20 alt="Peanut enabled">', $data[0]);
        $tg = preg_replace('/[ a-z]*(\d+)/i', '<a href="https://hose.brandmeister.network/group/$1/">$0</a>', $data[1]);
        echo "<tr>";
        echo "  <th>" . $modname . "</th>";
        echo "  <td>" . $tg . "</td>";
        echo "  <td>" . $data[2] . "</td>";
        echo "  <td>" . $data[3] . "</td>";
        echo "</tr>";
    }
    fclose($handle);
    echo "</table>";
}
?>
  </div>
</div>

<script>
  clearTimeout(PageRefresh);
</script>

