<div class="row">
  <div class="col-md-6">
<?php
if (($handle = fopen(dirname(__FILE__) . "/modules.csv", "r")) !== FALSE) {
?>
    <h2>Modules</h2>
    <table class="table table-striped">
      <tr>
        <th style="white-space: nowrap">Module</th>
        <th style="white-space: nowrap">Details</th>
      </tr>
<?php
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $data = array_map('trim', $data);
        switch(count($data)) {
        case 0:
            continue;
        case 1:
            $data[1] = '';
            break;
        case 2:
            break;
        default:
            $data[1] = implode(', ', array_slice($data, 1));
            break;
        }
        if ((substr($data[0], 0, 1) == '#') || (substr($data[0], 0, 1) == ';')) {
            continue;
        }
        echo "<tr>";
        echo "  <td>" . $data[0] . "</td>";
        echo "  <td>" . $data[1] . "</td>";
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

