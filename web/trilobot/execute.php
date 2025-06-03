<?php
// execute.php - Backend script to execute Python scripts

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$pythonBasePath = '/var/www/python/scripts';
$pythonExecutable = '/var/www/python/venv/bin/python'; // Adjust path as needed
$maxExecutionTime = 120; // Maximum execution time in seconds
$maxOutputSize = 1024 * 1024; // Maximum output size (1MB)

function sendJsonResponse($success, $output = '', $error = '')
{
    echo json_encode([
        'success' => $success,
        'output' => $output,
        'error' => $error,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function sanitizeOutput($output)
{
    // Remove any potentially harmful content and limit size
    $output = strip_tags($output);
    if (strlen($output) > $GLOBALS['maxOutputSize']) {
        $output = substr($output, 0, $GLOBALS['maxOutputSize']) . "\n\n[Output truncated - exceeded maximum size]";
    }
    return $output;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, '', 'Only POST requests are allowed');
}

// Check if script parameter is provided
if (!isset($_POST['script']) || empty($_POST['script'])) {
    sendJsonResponse(false, '', 'No script specified');
}

$requestedScript = $_POST['script'];

// Security: Validate the script path
if (strpos($requestedScript, '..') !== false || strpos($requestedScript, '/') === 0) {
    sendJsonResponse(false, '', 'Invalid script path');
}

// Build full script path
$fullScriptPath = $pythonBasePath . '/' . $requestedScript;

// Verify the script exists and is within the allowed directory
if (!file_exists($fullScriptPath)) {
    sendJsonResponse(false, '', 'Script not found: ' . htmlspecialchars($requestedScript));
}

if (!is_file($fullScriptPath)) {
    sendJsonResponse(false, '', 'Path is not a file: ' . htmlspecialchars($requestedScript));
}

// Verify it's a Python file
if (pathinfo($fullScriptPath, PATHINFO_EXTENSION) !== 'py') {
    sendJsonResponse(false, '', 'Not a Python file: ' . htmlspecialchars($requestedScript));
}

// Security: Ensure the resolved path is still within the python directory
$realScriptPath = realpath($fullScriptPath);
$realBasePath = realpath($pythonBasePath);

if ($realScriptPath === false || $realBasePath === false || strpos($realScriptPath, $realBasePath) !== 0) {
    sendJsonResponse(false, '', 'Script path is outside allowed directory');
}

// Check if Python executable exists
if (!file_exists($pythonExecutable)) {
    sendJsonResponse(false, '', 'Python executable not found at: ' . $pythonExecutable);
}

try {
    // Set execution time limit
    set_time_limit($maxExecutionTime + 5);

    // Change to the script's directory
    $scriptDir = dirname($realScriptPath);
    $scriptName = basename($realScriptPath);

    // Build the command
    $command = sprintf(
        'cd %s && timeout %d %s %s 2>&1',
        escapeshellarg($scriptDir),
        $maxExecutionTime,
        escapeshellarg($pythonExecutable),
        escapeshellarg($scriptName)
    );

    // Log the execution (optional - remove in production if not needed)
    $logMessage = date('Y-m-d H:i:s') . " - Executing: " . $requestedScript . " from IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    error_log($logMessage, 3, '/var/log/python_runner.log');

    // Execute the command
    $output = '';
    $returnCode = 0;

    $startTime = microtime(true);
    exec($command, $outputLines, $returnCode);
    $executionTime = microtime(true) - $startTime;

    $output = implode("\n", $outputLines);

    // Add execution info
    $output .= "\n\n--- Execution Info ---\n";
    $output .= "Execution Time: " . number_format($executionTime, 3) . " seconds\n";
    $output .= "Return Code: " . $returnCode . "\n";
    $output .= "Script: " . $requestedScript . "\n";

    // Sanitize output
    $output = sanitizeOutput($output);

    if ($returnCode === 0) {
        sendJsonResponse(true, $output, '');
    } else if ($returnCode === 124) {
        // Timeout error
        sendJsonResponse(false, $output, 'Script execution timed out after ' . $maxExecutionTime . ' seconds');
    } else {
        sendJsonResponse(false, $output, 'Script exited with error code: ' . $returnCode);
    }

} catch (Exception $e) {
    sendJsonResponse(false, '', 'Execution error: ' . $e->getMessage());
} catch (Error $e) {
    sendJsonResponse(false, '', 'Fatal error: ' . $e->getMessage());
}
?>