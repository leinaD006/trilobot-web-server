<?php

$python = "/var/www/python/venv/bin/python";
$script_path = "/var/www/python/scripts";

echo "$python $script_path/examples/flash_underlights.py\n";

$output = shell_exec("$python $script_path/examples/flash_underlights.py");
echo $output;
?>