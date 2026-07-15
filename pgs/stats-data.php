<?php

define("CACHETTL", 3600);
define("DBFILE", "/usr/local/src/activity/pb_data/data.db");

function fetchAll(SQLite3Result $result): array {
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_NUM)) {
        $rows[] = $row;
    }
    return $rows;
}

function humanDuration(int|float|null $seconds): string {
    if (is_null($seconds) || $seconds <= 0) {
        return '0 sec';
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    $parts = [];

    if ($hours > 0) {
        $parts[] = $hours . ' hr';
    }

    if ($hours + $minutes > 0) {
        $parts[] = $minutes . ' min';
    }

    $parts[] = $remainingSeconds . ' sec';

    return implode(' ', $parts);
}

// function getData(key) - display data based on key
function getData(string $key): void {
    // enforce default values
    $timescale = isset($_GET['timescale']) ? (int)$_GET['timescale'] : 30;
    $module = $_GET['module'] ?? '*';


    $apcuKey = "{$key}-{$timescale}-{$module}";
    $rows = apcu_fetch($apcuKey);

    if ($rows === false) {
        $now = round(microtime(true) * 1000);
        $tscutoff = $now - ($timescale * 86400 * 1000);
            
        $moduleClause = "";
        if (preg_match('/^[A-Z]$/', $module)) {
            $moduleClause = " AND module = :module ";
        }
        
        $db = new SQLite3(DBFILE);

        switch ($key) {
            case 'totals':
                $query = $db->prepare('
                    SELECT 
                        COALESCE(ROUND(SUM((tsoff - ts) / 1000.0)), 0) AS total_activity_seconds,
                        COUNT(DISTINCT call) AS unique_call_count
                    FROM 
                        activity 
                    WHERE 
                        tsoff > 0 AND
                        ts > :cutoff '
                        . $moduleClause
                );
                break;

            case 'activity':
                $query = $db->prepare('
                    SELECT
                        call,
                        ROUND(SUM(tsoff - ts)/1000) AS total_activity_time
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        +call
                    ORDER BY
                        total_activity_time DESC
                    LIMIT
                        15
                ');
                break;
            case 'modules':
                $query = $db->prepare('
                    SELECT
                        module,
                        ROUND(SUM(tsoff - ts)/1000) AS total_activity_time
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        module
                    ORDER BY
                        total_activity_time DESC
                ');
                break;
            case 'dayofweek':
                $query = $db->prepare('
                    SELECT
                        strftime(\'%w\', ts/1000, \'unixepoch\', \'localtime\') AS day_of_week,
                        COALESCE(ROUND(SUM(tsoff - ts)/1000), 0) AS total_activity_time
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        day_of_week
                    ORDER BY
                        day_of_week ASC
                ');
                break;
            case 'hour':
                $query = $db->prepare('
                    SELECT
                        strftime(\'%H\', ts/1000, \'unixepoch\', \'localtime\') AS hour_of_day,
                        COALESCE(ROUND(SUM(tsoff - ts)/1000), 0) AS total_activity_time
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        hour_of_day
                    ORDER BY
                        hour_of_day ASC
                ');
                break;
            case 'via':
                $query = $db->prepare('
                    SELECT
                        via,
                        COALESCE(ROUND(SUM(tsoff - ts)/1000), 0) AS total_activity_time
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        via
                    ORDER BY
                        total_activity_time DESC
                    LIMIT
                        15
                ');
                break;
            case 'heatmap':
                $query = $db->prepare('
                    SELECT
                        strftime(\'%w\', ts/1000, \'unixepoch\', \'localtime\') AS day_of_week,
                        strftime(\'%H\', ts/1000, \'unixepoch\', \'localtime\') AS hour_of_day,
                        COALESCE(ROUND(SUM(tsoff - ts)/1000), 0) AS total_activity_time
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        day_of_week, hour_of_day
                ');
                break;
            case 'txlen':
                $query = $db->prepare('
                    SELECT
                        CASE 
                            WHEN (tsoff - ts) < 2000 THEN \'0\'
                            WHEN (tsoff - ts) BETWEEN 2000 AND 5000 THEN \'1\'
                            WHEN (tsoff - ts) BETWEEN 5001 AND 15000 THEN \'2\'
                            WHEN (tsoff - ts) BETWEEN 15001 AND 60000 THEN \'3\'
                            ELSE \'4\'
                        END AS bracket_val,
                        COUNT(*) AS tx_count
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        bracket_val
                ');
                break;
            case 'userdiv':
                $query = $db->prepare('
                    SELECT
                        strftime(\'%Y-%m-%d\', ts/1000, \'unixepoch\', \'localtime\') AS calendar_day,
                        COUNT(DISTINCT call) AS unique_users
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > 0 '
                    . $moduleClause .
                    'GROUP BY
                        calendar_day
                    ORDER BY
                        calendar_day ASC
                ');
                break;
            case 'kerchunks':
                $query = $db->prepare('
                    SELECT
                        call,
                        COUNT(*) AS kerchunk_count
                    FROM
                        activity
                    WHERE
                        ts > :cutoff
                        AND tsoff > ts
                        AND (tsoff - ts) < 1500 '
                    . $moduleClause .
                    'GROUP BY
                        +call
                    ORDER BY
                        kerchunk_count DESC
                    LIMIT
                        15
                ');
                break;
        }
        $query->bindValue(':cutoff', $tscutoff, SQLITE3_FLOAT);
        if ($moduleClause !== "") {
            $query->bindValue(':module', $module, SQLITE3_TEXT);
        }

        $result = $query->execute();
        $rows = fetchAll($result);
        if ($key === 'dayofweek') {
            $temp = [];
            for ($i = 0; $i < 7; $i++) {
                $temp[(string)$i] = 0;
            }
            foreach ($rows as $row) {
                if ($row[0] !== null) {
                    $temp[(string)$row[0]] = (int)$row[1];
                }
            }
            $rows = [];
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($temp as $d => $val) {
                $rows[] = [$dayNames[(int)$d], $val];
            }
        } elseif ($key === 'hour') {
            $temp = [];
            for ($i = 0; $i < 24; $i++) {
                $h = sprintf('%02d', $i);
                $temp[$h] = 0;
            }
            foreach ($rows as $row) {
                if ($row[0] !== null) {
                    $h = sprintf('%02d', (int)$row[0]);
                    $temp[$h] = (int)$row[1];
                }
            }
            $rows = [];
            foreach ($temp as $h => $val) {
                $rows[] = ["{$h}:00", $val];
            }
        } elseif ($key === 'heatmap') {
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            // Fill 7x24 grid to ensure we return all data points for bubble chart
            $grid = [];
            for ($d = 0; $d < 7; $d++) {
                for ($h = 0; $h < 24; $h++) {
                    $grid["$d-$h"] = 0;
                }
            }
            foreach ($rows as $row) {
                if ($row[0] !== null && $row[1] !== null) {
                    $grid["{$row[0]}-" . (int)$row[1]] = (int)$row[2];
                }
            }
            $chartData = [];
            foreach ($grid as $keyStr => $val) {
                list($d, $h) = explode('-', $keyStr);
                $chartData[] = ['x' => (int)$d, 'y' => (int)$h, 'v' => $val];
            }
            
            // Prepare top 10 busiest slots for table display
            $topSlots = [];
            foreach ($rows as $row) {
                if ($row[0] !== null && $row[1] !== null) {
                    $topSlots[] = [
                        $dayNames[(int)$row[0]] . ' at ' . sprintf('%02d:00', (int)$row[1]),
                        (int)$row[2]
                    ];
                }
            }
            usort($topSlots, function($a, $b) {
                return $b[1] - $a[1];
            });
            $rows = [
                'chart' => $chartData,
                'table' => array_slice($topSlots, 0, 10)
            ];
        } elseif ($key === 'txlen') {
            $brackets = [
                '0' => ['0-2s (Kerchunks)', 0],
                '1' => ['2-5s', 0],
                '2' => ['5-15s', 0],
                '3' => ['15s-1m', 0],
                '4' => ['>1m (Long TX)', 0]
            ];
            foreach ($rows as $row) {
                if ($row[0] !== null) {
                    $brackets[(string)$row[0]][1] = (int)$row[1];
                }
            }
            $rows = array_values($brackets);
        }
        apcu_store($apcuKey, $rows, 3600);
        $db->close();
    }
    if ($key === 'totals') {
        $row = $rows[0] ?? [0, 0];
        echo "<p>Total transmission time: " . humanDuration($row[0]) . "<br>";
        echo "Different callsigns heard: " . $row[1] . "</p>";
        return;
    }

    $section = match ($key) {
        'activity' => 'Activity (by call)',
        'modules' => 'Activity (by module)',
        'dayofweek' => 'Activity (by day of the week)',
        'hour' => 'Activity (by hour)',
        'via' => 'Activity (by gateway)',
        'heatmap' => 'Activity Heatmap (day of week vs hour)',
        'txlen' => 'Transmission Length',
        'userdiv' => 'Active Users over Time',
        'kerchunks' => 'Kerchunks',
        default => ''
    };

    $head = match ($key) {
        'activity' => ['Call', 'Tx (sec)'],
        'modules' => ['Module', 'Tx (sec)'],
        'dayofweek' => ['Day', 'Tx (sec)'],
        'hour' => ['Hour', 'Tx (sec)'],
        'via' => ['Gateway', 'Tx (sec)'],
        'heatmap' => ['Busiest Time', 'Tx (sec)'],
        'txlen' => ['Bracket', 'Transmissions'],
        'userdiv' => ['Date', 'Active Users'],
        'kerchunks' => ['Call', 'Kerchunks'],
        default => []
    };

    // fetch and process results
    $tableRows = $rows;
    $chartData = [];
    if ($key === 'heatmap') {
        $tableRows = $rows['table'] ?? [];
        $chartData = $rows['chart'] ?? [];
    }

    echo '<div class="row">';
    echo "<h4 style='margin-top: 2em;'>$section</h4>\n";
    echo '</div>';
    echo '<div class="row"><div class="col-md-4">';
    echo '<div style="max-height: 350px; overflow-y: auto;">';
    echo '<table id="' . $key . '" class="table table-striped table-sm">';
    echo '<thead>';
    foreach ($head as $item) {
        echo "<th>" . $item . "</th>";
    }
    echo '</thead><tbody>';
    foreach ($tableRows as $row) {
        if ($key !== 'dayofweek' && $key !== 'hour' && $key !== 'txlen' && $key !== 'heatmap') {
            if (round($row[1]) == 0) {
                continue;
            }
        }
        $k = htmlspecialchars(trim($row[0]));
        echo "<tr><td>$k</td><td>" . $row[1] . "</td></tr>\n";
    }
    echo "</tbody></table>";
    echo "</div>";
    echo "<p></p>";
    echo "</div>";
    if ($key == 'modules') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="mgraph"></canvas>';
        echo '</div>';
    } elseif ($key == 'dayofweek') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="dwgraph"></canvas>';
        echo '</div>';
    } elseif ($key == 'hour') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="hgraph"></canvas>';
        echo '</div>';
    } elseif ($key == 'via') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="viagraph"></canvas>';
        echo '</div>';
    } elseif ($key == 'heatmap') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="hmapgraph" data-chart="' . htmlspecialchars(json_encode($chartData)) . '"></canvas>';
        echo '</div>';
    } elseif ($key == 'txlen') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="txlengraph"></canvas>';
        echo '</div>';
    } elseif ($key == 'userdiv') {
        echo '<div class="col-md-6" style="position: relative;">';
        echo '<canvas id="userdivgraph"></canvas>';
        echo '</div>';
    }
    echo "</div>";
}

$timescale = isset($_GET['timescale']) ? (int)$_GET['timescale'] : 30;
getData('totals');
getData('activity');
if ($_GET['module'] == "*") {
    getData('modules');
}
if ($timescale >= 7) {
    getData('dayofweek');
    getData('heatmap');
} else {
    getData('hour');
}
getData('via');
if ($timescale >= 7) {
    getData('userdiv');
}
getData('txlen');
getData('kerchunks');

?>
