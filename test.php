<?php
$rekap = [];
for ($i = 1; $i <= 31; $i++) {
    $dateStr = sprintf('%04d-%02d-%02d', 2026, '05', $i);
    $rekap[$dateStr] = [];
}
$pagedData = array_slice($rekap, 0, 10, true);
echo 'Count: '.count($pagedData).PHP_EOL;
foreach ($pagedData as $k => $v) {
    echo $k.' ';
}
echo PHP_EOL;
