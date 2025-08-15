<?php
$lines = file('src/logs/app.log');
echo "Last 3 log entries:\n";
foreach (array_slice($lines, -3) as $line) {
    echo $line;
}
