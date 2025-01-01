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
                        echo "<option value=\"$letter\">$letter: {$PageOptions['ShortNames'][$letter]}</option>";
                    }
                    ?>
                    </select>
            </div>
        </div>
    </div>
  <div id="data-container" style="transition: opacity 0.5s ease-in-out;">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
$_GET['timescale'] = 30;
$_GET['module'] = '*';
require 'stats-data.php';
  ?>
  </div>
  <table>
  </table>
</div>
</div>
<script src="https://unpkg.com/htmx.org@1.9.11/dist/htmx.js"
        integrity="sha384-l9bYT9SL4CAW0Hl7pAOpfRc18mys1b0wK4U8UtGnWOxPVbVMgrOdB+jyz/WY8Jue"
        crossorigin="anonymous"></script>
<script>
  clearTimeout(PageRefresh);
  htmx.on('htmx:beforeRequest', function(event) {
    document.getElementById(event.detail.target.id).style.opacity = 0; 
  });

  htmx.on('htmx:afterSwap', function(event) {
    document.getElementById(event.detail.target.id).style.opacity = 1;
  });
</script>
