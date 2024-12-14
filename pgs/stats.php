<?php
function QRZ($c) {
	return preg_replace('/((\d[A-Z]\d{1,3}[A-Z]{1,4})|([A-Z]{1,2}\d{1,3}[A-Z]{1,4}))/',
		"<a target=\"_blank\" rel=\"noreferrer\" href=\"https://qrz.com/db/$1\">$1</a>",
                 $c);
}

function fetchdata($period, $module) {
    $dbPath = "/usr/local/src/activity/pb_data/data.db";
    $db = new PDO("sqlite:$dbPath");

    $sql = "SELECT call, sum(tsoff-ts) AS use FROM activity
            WHERE created > '2023-08-05'
              AND tsoff > 0
            GROUP BY module
            ORDER BY use DESC
            LIMIT 20;";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    $r = "";
    foreach ($results as $row) {
        print_r($row);
        $r += "<tr><td>" . QRZ($row['call']) . "</td><td>" . $row['use'] . "</td></tr>";
    }
    //print_r($r);
    return $r;
}
?>

<div class="container">
<div class="row">
  <h2>Statistics</h2>
</div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="timescale">Timescale:</label>
                <select id="timescale" class="form-select"
                        hx-get="/pgs/stats-data.php" hx-target="#data-container"
                        hx-trigger="change" name="timescale"
                        hx-vals='js:{"timescale":event.target.value, "module":document.getElementById("module").value}'>
                    <option value="1">1 Day</option>
                    <option value="7">7 Days</option>
                    <option selected value="30">30 Days</option>
                    <option value="90">90 Days</option>
                    <option value="180">180 Days</option>
                    <option value="365">1 Year</option>
                    <option value="730">2 Years</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="module">Module:</label>
                <select id="module" class="form-select"
                        hx-get="/pgs/stats-data.php" hx-target="#data-container"
                        hx-trigger="change" name="module"
                        hx-vals='js:{"timescale":document.getElementById("timescale").value, "module":event.target.value}'>
                    <option selected value="*">All Modules</option>
                    <?php
                    for ($i = ord('A'); $i <= ord('Z'); $i++) {
                        $letter = chr($i);
                        echo "<option value=\"$letter\">Module $letter</option>";
                    }
                    ?>
                    </select>
            </div>
        </div>
    </div>
<div class="row">
  <div id="data-container">
<?php
$_GET['timescale'] = 30;
$_GET['module'] = '*';
require 'stats-data.php';
  ?>
  </div>
</div>
  <table>
  </table>
</div>
</div>
<script>
  clearTimeout(PageRefresh);
</script>
<script src="https://unpkg.com/htmx.org@1.9.11/dist/htmx.js"
        integrity="sha384-l9bYT9SL4CAW0Hl7pAOpfRc18mys1b0wK4U8UtGnWOxPVbVMgrOdB+jyz/WY8Jue"
        crossorigin="anonymous"></script>

