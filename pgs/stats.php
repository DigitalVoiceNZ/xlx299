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
  drawCharts();

  function drawCharts() {
    if (typeof Chart === 'undefined') {
        if (!window.chartRetryCount) {
            window.chartRetryCount = 0;
        }
        if (window.chartRetryCount < 50) {
            window.chartRetryCount++;
            setTimeout(drawCharts, 100);
        } else {
            console.error('Chart.js failed to load.');
        }
        return;
    }
    window.chartRetryCount = 0;

    let canvases = ['mgraph', 'dwgraph', 'hgraph'].map(id => document.getElementById(id)).filter(el => el !== null);
    if (canvases.length > 0 && canvases[0].offsetWidth === 0) {
        if (!window.canvasRetryCount) {
            window.canvasRetryCount = 0;
        }
        if (window.canvasRetryCount < 10) {
            window.canvasRetryCount++;
            setTimeout(drawCharts, 50);
            return;
        }
    }
    window.canvasRetryCount = 0;

    graphModules();
    graphDayOfWeek();
    graphHour();
  }

  function graphModules() {
    let canvas = document.getElementById('mgraph');
    if (!canvas) {
        if (window.myModuleChart) {
            window.myModuleChart.destroy();
            window.myModuleChart = null;
        }
        return;
    }
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

  function graphDayOfWeek() {
    let canvas = document.getElementById('dwgraph');
    if (!canvas) {
        if (window.myDayOfWeekChart) {
            window.myDayOfWeekChart.destroy();
            window.myDayOfWeekChart = null;
        }
        return;
    }
    let table = document.getElementById('dayofweek');
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
        labels.push(tbody.rows[i].cells[0].textContent.trim());
        data.push(parseInt(tbody.rows[i].cells[1].textContent, 10));
    }
    if (window.myDayOfWeekChart) {
        window.myDayOfWeekChart.destroy();
    }
    window.myDayOfWeekChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Seconds',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
  }

  function graphHour() {
    let canvas = document.getElementById('hgraph');
    if (!canvas) {
        if (window.myHourChart) {
            window.myHourChart.destroy();
            window.myHourChart = null;
        }
        return;
    }
    let table = document.getElementById('hour');
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
        labels.push(tbody.rows[i].cells[0].textContent.trim());
        data.push(parseInt(tbody.rows[i].cells[1].textContent, 10));
    }
    if (window.myHourChart) {
        window.myHourChart.destroy();
    }
    window.myHourChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Seconds',
                data: data,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
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
    setTimeout(drawCharts, 0);
  });
</script>
