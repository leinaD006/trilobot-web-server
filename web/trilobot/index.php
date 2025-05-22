<?php
// Debug information
echo "Current user: " . exec('whoami') . "\n";
echo "Groups: " . exec('groups') . "\n";
echo "Python path exists: " . (file_exists('/var/www/python/venv/bin/python') ? 'Yes' : 'No') . "\n";
echo "Script path exists: " . (file_exists('/var/www/python/scripts/examples/flash_underlights.py') ? 'Yes' : 'No') . "\n";

// Set environment
putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');

// Try different approaches
echo "\n=== Approach 1: Direct execution ===\n";
$command1 = '/var/www/python/venv/bin/python /var/www/python/scripts/examples/flash_underlights.py 2>&1';
$output1 = shell_exec($command1);
echo "Output: " . ($output1 ?: 'No output') . "\n";

echo "\n=== Approach 2: With sudo ===\n";
$command2 = 'sudo /var/www/python/venv/bin/python /var/www/python/scripts/examples/flash_underlights.py 2>&1';
$output2 = shell_exec($command2);
echo "Output: " . ($output2 ?: 'No output') . "\n";

echo "\n=== Approach 3: Check Python script directly ===\n";
$command3 = '/var/www/python/venv/bin/python -c "import RPi.GPIO as GPIO; print(\'GPIO import successful\')" 2>&1';
$output3 = shell_exec($command3);
echo "GPIO test: " . ($output3 ?: 'No output') . "\n";
?>