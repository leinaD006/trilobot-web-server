<?php

$command = 'ls /var/www/html/';
exec($command, $out, $status);

print_r($out);
echo '<br>' . $status . '<br>';


$command = '/usr/bin/python /var/www/html/trilobot/make_noise.py';
exec($command, $out, $status);

print_r($out);
echo '<br>' . $status .'<br>';
?>
