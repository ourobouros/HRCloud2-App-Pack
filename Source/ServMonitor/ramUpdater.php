<?php 

// / This file will retrieve information regarding the server's memory performance and statistics.
$ramCacheFile = 'Cache/ramCACHE.php';
$ramIncludeFile = 'Cache/ramINCLUDE.php';
@chmod($ramCacheFile, 0755);
@chown($ramCacheFile, 'www-data');
@chgrp($ramCacheFile, 'www-data');

// / This file will reurn the servers current RAM usage percentage.
$exec_free = explode("\n", trim(shell_exec('free')));
$get_mem = preg_split("/[\s]+/", $exec_free[1]);
$ram = round($get_mem[2]/$get_mem[1]*100, 0);

// / The following will reurn the servers current RAM usage in gigabytes (GB).
$exec_free1 = explode("\n", trim(shell_exec('free')));
$get_mem1 = preg_split("/[\s]+/", $exec_free1[1]);
$mem = number_format(round($get_mem1[2]/1024/1024, 2), 2) . ' GB  of ' . number_format(round($get_mem1[1]/1024/1024, 2), 2).' GB';
$WRITEramIncludeFile = @file_put_contents($ramIncludeFile, '<?php $ram = \''.$ram.'\'; $mem = \''.$mem.'\'; ?>');
?>


