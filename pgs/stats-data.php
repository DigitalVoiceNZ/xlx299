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
        'kerchunks' => 'Kerchunks',
        default => ''
    };

    $head = match ($key) {
        'activity' => ['Call', 'Tx (sec)'],
        'modules' => ['Module', 'Tx (sec)'],
        'dayofweek' => ['Day', 'Tx (sec)'],
        'hour' => ['Hour', 'Tx (sec)'],
        'kerchunks' => ['Call', 'Kerchunks'],
        default => []
    };

    // fetch and process results
    echo '<div class="row">';
    echo "<h4>$section</h4>\n";
    echo '</div>';
    echo '<div class="row"><div class="col-md-4">';
    echo '<table id="' . $key . '" class="table table-striped table-sm">';
    echo '<thead>';
    foreach ($head as $item) {
        echo "<th>" . $item . "</th>";
    }
    echo '</thead><tbody>';
    foreach ($rows as $row) {
        if ($key !== 'dayofweek' && $key !== 'hour') {
            if (round($row[1]) == 0) {
                continue;
            }
        }
        $k = htmlspecialchars(trim($row[0]));
        echo "<tr><td>$k</td><td>" . $row[1] . "</td></tr>\n";
    }
    echo "</tbody></table>";
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
}
getData('hour');
getData('kerchunks');

?>
