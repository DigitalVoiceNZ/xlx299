<?php
function grid($g) {
	/*
    return preg_replace('/([A-Z]{2}\d{2}([A-Za-z]{2})?)/',
		        "<a target=\"_blank\" rel=\"noreferrer\" href=\"http://www.levinecentral.com/ham/grid_square.php?Grid=$0\">$0</a>",
                        $g);
	 */
    return preg_replace('/([A-Z]{2}\d{2}([A-Za-z]{2})?)/',
		        "<a target=\"_blank\" rel=\"noreferrer\" href=\"https://www.karhukoti.com/maidenhead-grid-square-locator/?grid=$0#demo\">$0</a>",
                        $g);
}

function QRZ($c) {
	return preg_replace('/((\d[A-Z]\d{1,3}[A-Z]{1,4})|([A-Z]{1,2}\d{1,3}[A-Z]{1,4}))/',
		"<a target=\"_blank\" rel=\"noreferrer\" href=\"https://qrz.com/db/$1\">$1</a>",
                 $c);
}
?>

<div class="row">
  <div class="col-md-6">
<?php
if (($handle = fopen(dirname(__FILE__) . "/reflectors.csv", "r")) !== FALSE) {
?>
    <h2>Other Reflectors</h2>
    <table class="table table-striped" style="max-width: 30em">
      <tr>
        <th style="white-space: nowrap">Reflector</th>
        <th style="white-space: nowrap">Website</th>
        <th style="white-space: nowrap">Hosted by</th>
      </tr>
<?php
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	// ignore lines with fewer than 4 fields
        if (count($data) >= 4) {
	    $data = array_map('trim', $data);
	    echo "<tr>";
	    echo "  <td>" . $data[0] . "</td>";
	    echo '  <td><a href="' . $data[1] . '">' . $data[2] . '</a></td>';
	    echo '  <td style="white-space: nowrap">' . QRZ($data[3]) . "</td>";
	    echo "</tr>";
        }
    }
    fclose($handle);
    echo "</table>";
}
?>
  </div>
  <div class="col-md-6">
<?php
if (($handle = fopen(dirname(__FILE__) . "/hotspots-repeaters.csv", "r")) !== FALSE) {
?>
    <h2>Hotspots and Repeaters</h2>
    <table class="table table-striped" style="max-width: 40em; margin-bottom: 2em">
      <tr>
        <th style="white-space: nowrap">Hotspot</th>
        <th style="white-space: nowrap">Website</th>
        <th style="white-space: nowrap">Hosted by</th>
        <th style="white-space: nowrap">Location</th>
      </tr>
<?php
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	// ignore lines with fewer than 4 fields
	$fcount = count($data);
        if ($fcount >= 4) {
	    $data = array_map('trim', $data);
	    echo '<tr>';
	    echo '  <td>' . $data[0] . '</td>';
	    echo '  <td><a href="' . $data[1] . '">' . $data[2] . '</a></td>';
	    echo '  <td style="white-space: nowrap">' . QRZ($data[3]) . "</td>";
	    echo '  <td style="white-space: nowrap">';
	    if ($fcount >= 5) {
		echo grid($data[4]);
	    }
	    echo '  </td>';
	    echo '</tr>';
        }
    }
    echo "</table>";
    fclose($handle);
}
?>
  </div>
</div>

<script>
  clearTimeout(PageRefresh);
</script>

