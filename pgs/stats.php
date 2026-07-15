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

    let canvases = ['mgraph', 'dwgraph', 'hgraph', 'viagraph', 'hmapgraph', 'txlengraph', 'userdivgraph']
        .map(id => document.getElementById(id))
        .filter(el => el !== null);
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
    graphVia();
    graphHeatmap();
    graphTxLen();
    graphUserDiv();
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

  function graphVia() {
    let canvas = document.getElementById('viagraph');
    if (!canvas) {
        if (window.myViaChart) {
            window.myViaChart.destroy();
            window.myViaChart = null;
        }
        return;
    }
    let table = document.getElementById('via');
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
    if (window.myViaChart) {
        window.myViaChart.destroy();
    }
    window.myViaChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Seconds',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
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

  function graphHeatmap() {
    let canvas = document.getElementById('hmapgraph');
    if (!canvas) {
        if (window.myHeatmapChart) {
            window.myHeatmapChart.destroy();
            window.myHeatmapChart = null;
        }
        return;
    }
    
    let rawData = [];
    try {
        rawData = JSON.parse(canvas.dataset.chart || '[]');
    } catch (e) {
        console.error(e);
        return;
    }

    let maxVal = Math.max(...rawData.map(d => d.v), 1);
    let bubbleData = rawData.map(item => ({
        x: item.x,
        y: item.y,
        r: maxVal > 0 ? (item.v / maxVal) * 20 + 3 : 3
    }));

    if (window.myHeatmapChart) {
        window.myHeatmapChart.destroy();
    }
    
    let days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    window.myHeatmapChart = new Chart(canvas, {
        type: 'bubble',
        data: {
            datasets: [{
                label: 'Activity level',
                data: bubbleData,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let rawPoint = rawData[context.dataIndex];
                            let dName = days[rawPoint.x];
                            let hourStr = String(rawPoint.y).padStart(2, '0') + ':00';
                            let secs = rawPoint.v;
                            return `${dName} at ${hourStr}: ${secs} sec`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'linear',
                    min: -1,
                    max: 7,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return days[value] || '';
                        }
                    }
                },
                y: {
                    type: 'linear',
                    min: -2,
                    max: 25,
                    ticks: {
                        stepSize: 2,
                        callback: function(value) {
                            if (value >= 0 && value <= 23 && Number.isInteger(value)) {
                                return String(value).padStart(2, '0') + ':00';
                            }
                            return '';
                        }
                    }
                }
            }
        }
    });
  }

  function graphTxLen() {
    let canvas = document.getElementById('txlengraph');
    if (!canvas) {
        if (window.myTxLenChart) {
            window.myTxLenChart.destroy();
            window.myTxLenChart = null;
        }
        return;
    }
    let table = document.getElementById('txlen');
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
    if (window.myTxLenChart) {
        window.myTxLenChart.destroy();
    }
    window.myTxLenChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Transmissions',
                data: data,
                backgroundColor: 'rgba(255, 206, 86, 0.6)',
                borderColor: 'rgba(255, 206, 86, 1)',
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

  function graphUserDiv() {
    let canvas = document.getElementById('userdivgraph');
    if (!canvas) {
        if (window.myUserDivChart) {
            window.myUserDivChart.destroy();
            window.myUserDivChart = null;
        }
        return;
    }
    let table = document.getElementById('userdiv');
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
    if (window.myUserDivChart) {
        window.myUserDivChart.destroy();
    }
    window.myUserDivChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Unique Callsigns',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
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
