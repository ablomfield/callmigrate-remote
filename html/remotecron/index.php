<?php
$f = fopen('/opt/callmigrate/cronwatch/cron.now', 'w');
fwrite($f, time());
fclose($f);
?>