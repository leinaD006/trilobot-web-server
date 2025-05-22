<?php

$python = "/var/www/html/python/venv/bin/python";
$script_path = "/var/www/html/python/scripts";

$output = shell_exec("$python $script_path/examples/flash_underlights.py");
echo $output;
?>