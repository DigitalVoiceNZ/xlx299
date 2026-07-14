<div class="container">
<div class="row">
  <h2>Statistics</h2>
</div>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
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
            <div class="mb-3">
                <label for="module">Module:</label>
                <select id="module" class="form-select"
                        hx-get="/pgs/stats-data.php" hx-target="#data-container"
                        hx-trigger="change" name="module"
                        hx-vals='js:{"timescale":document.getElementById("timescale").value, "module":event.target.value}'>
                    <option selected value="*">All Modules</option>
                    <?php
                    for ($i = ord('A'); $i <= ord('Z'); $i++) {
                        $letter = chr($i);
                        $shortName = $PageOptions['ShortNames'][$letter] ?? '';
                        if ($shortName !== '') {
                            echo "<option value=\"$letter\">$letter: $shortName</option>";
                        }
                    }
                    ?>
                    </select>
            </div>
        </div>
    </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <div id="data-container" style="transition: opacity 0.5s ease-in-out;">
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
<script src="https://unpkg.com/htmx.org@1.9.12"
        integrity="sha384-ujb1lZYygJmzgSwoxRggbCHcjc0rB2XoQrxeTUQyRjrOnlCoYta87iKBWq3EsdM2"
        crossorigin="anonymous"></script>
<script>
<?php
  echo "  let mNames = " . json_encode($PageOptions['ShortNames']) . ";\n";
?>
  clearTimeout(PageRefresh);
  graphModules();

  function graphModules() {
    if (typeof Chart === 'undefined') {
        if (!window.chartRetryCount) {
            window.chartRetryCount = 0;
        }
        if (window.chartRetryCount < 50) {
            window.chartRetryCount++;
            // retry drawing after a short delay if chart.js is still loading
            setTimeout(graphModules, 100);
        } else {
            console.error('Chart.js failed to load.');
        }
        return;
    }
    window.chartRetryCount = 0;

    let canvas = document.getElementById('mgraph');
    if (!canvas) {
        return;
    }

    // if the canvas has no width yet, wait for browser layout to finalize
    if (canvas.offsetWidth === 0) {
        if (!window.canvasRetryCount) {
            window.canvasRetryCount = 0;
        }
        if (window.canvasRetryCount < 10) {
            window.canvasRetryCount++;
            setTimeout(graphModules, 50);
            return;
        }
    }
    window.canvasRetryCount = 0;

    let table = document.getElementById('modules');
    if (!table) {
        return;
    }
    let tbody = table.querySelector('tbody');
    if (!tbody) {
        return;
    }

    let labels = [];
    let data = [];
    for (let i = 0; i < tbody.rows.length; i++) { 
        let cellText = tbody.rows[i].cells[0].textContent.trim();
        let m = cellText.split(':')[0].trim();
        let n = (mNames[m] && typeof mNames[m] === 'string') ? mNames[m].trim() : '';
        if (n && !cellText.includes(':')) {
            tbody.rows[i].cells[0].textContent = `${m}: ${n}`;
        }
        labels.push(m);
        data.push(parseInt(tbody.rows[i].cells[1].textContent, 10));
    }
    // destroy previous instance to clean up resize observers and event listeners
    if (window.myModuleChart) {
        window.myModuleChart.destroy();
    }
    window.myModuleChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Seconds',
                data: data
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            indexAxis: 'x'
        }
    });
  }

  htmx.on('htmx:beforeRequest', function(event) {
    let target = event.detail.target;
    if (target) {
        target.style.opacity = 0;
    }
  });

  htmx.on('htmx:afterSwap', function(event) {
    let target = event.detail.target;
    if (target) {
        target.style.opacity = 1;
    }
    // defer execution to the next event loop tick to allow layout calculations
    setTimeout(graphModules, 0);
  });
</script>
