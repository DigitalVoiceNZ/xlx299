<?php

define("CACHETTL", 3600);
define("DBFILE", "/usr/local/src/activity/pb_data/data.db");

function fetchAll($result) {
    $rows = array();
    while ($row = $result->fetchArray(SQLITE3_NUM)) {
        $rows[] = $row;
    }
    return $rows;
}

function humanDuration($seconds): string {
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
function getData($key) {
    $timescale = $_GET['timescale'];
    $module = $_GET['module'];
    $moduleClause = "";
    if (preg_match('/^[A-Z]$/', $module)) {
        $moduleClause = " AND module = '$module'";
    }
    
    $now = round(microtime(true) * 1000);
    $tscutoff = $now - $timescale * 24 * 60 * 60 * 1000;
    
    $db = new SQLite3(DBFILE);

    switch ($key) {
        case 'totals':
            $apcuKey = "totals-$timescale-$module";
            $rows = apcu_fetch($apcuKey);
            if ($rows === false) {
                $query = $db->prepare('
                    SELECT 
                        ROUND(SUM((tsoff - ts) / 1000.0)) AS total_activity_seconds,
                        COUNT(DISTINCT call) AS unique_call_count
                    FROM 
                        activity 
                    WHERE 
                        tsoff > 0 AND
                        ts > :cutoff '
                        . $moduleClause
                );
                $query->bindValue(':cutoff', $tscutoff, SQLITE3_FLOAT);
                $result = $query->execute();
                $rows = fetchAll($result);
                apcu_store($apcuKey, $rows, 3600);
            }
            $row = $rows[0];
            echo "<p>Total transmission time: " . humanDuration($row[0]) . "<br>";
            echo "Different callsigns heard: " . $row[1] . "</p>";
            return;

        case 'activity':
            $apcuKey = "activity-$timescale-$module";
            $section = "Activity (by call)";
            $head = array('Call', 'Tx (sec)');
            $query = $db->prepare('
                SELECT
                    call,
                    ROUND(SUM(tsoff - ts)/1000) AS total_activity_time
                FROM
                    activity
                WHERE
                    tsoff > 0
                    AND ts > :cutoff '
                . $moduleClause .
                'GROUP BY
                    call
                ORDER BY
                    total_activity_time DESC
                LIMIT
                    15
            ');
            break;
        case 'modules':
            $apcuKey = "modules-$timescale-$module";
            $section = "Activity (by module)";
            $head = array('Module', 'Tx (sec)');
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
        case 'kerchunks':
            $apcuKey = "kerchunks-$timescale-$module";
            $section = "Kerchunks";
            $head = array('Call', 'Kerchunks');
            $query = $db->prepare('
                SELECT
                    call,
                    COUNT(*) AS kerchunk_count
                FROM
                    activity
                WHERE
                    ts > :cutoff
                    AND tsoff > 0
                    AND (tsoff - ts) < 1500 '
                . $moduleClause .
                'GROUP BY
                    call
                ORDER BY
                    kerchunk_count DESC
                LIMIT
                    15
            ');
            break;
    }
    $query->bindValue(':cutoff', $tscutoff, SQLITE3_FLOAT);
    $rows = apcu_fetch($apcuKey);
    if ($rows == false) {
        $result = $query->execute();
        $rows = fetchAll($result);
        apcu_store($apcuKey, $rows, 3600);
    }
    // Fetch and process results
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
        if (round($row[1]) == 0) {
            continue;
        }
        $k = htmlspecialchars(trim($row[0]));
        echo "<tr><td>$k</td><td>" . $row[1] . "</td></tr>\n";
    }
    echo "</tbody></table>";
    echo "<p></p>";
    echo "</div>";
    if ($key == 'modules') {
        echo '<div class="col-md-6">';
        echo '<canvas id="mgraph"></canvas>';
        echo '</div>';
    }
    echo "</div>";
    $db->close();
}

getData('totals');
getData('activity');
if ($_GET['module'] == "*") {
    getData('modules');
}
getData('kerchunks');

?>
