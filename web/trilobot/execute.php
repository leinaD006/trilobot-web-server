<?php
// execute.php - Backend script to execute Python scripts

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$pythonBasePath = '/var/www/python/scripts';
$pythonExecutable = '/var/www/python/venv/bin/python'; // Adjust path as needed
$maxExecutionTime = 0; // Maximum execution time in seconds
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

function managePids($action, $scriptPath = null, $pid = null)
{
    global $pidFile;

    $pids = [];
    if (file_exists($pidFile)) {
        $pids = json_decode(file_get_contents($pidFile), true) ?: [];
    }

    switch ($action) {
        case 'add':
            $pids[$scriptPath] = $pid;
            break;
        case 'remove':
            unset($pids[$scriptPath]);
            break;
        case 'get':
            return isset($pids[$scriptPath]) ? $pids[$scriptPath] : null;
        case 'cleanup':
            // Remove dead processes
            foreach ($pids as $script => $processPid) {
                if (!isProcessRunning($processPid)) {
                    unset($pids[$script]);
                }
            }
            break;
    }

    file_put_contents($pidFile, json_encode($pids));
    return $pids;
}

function isProcessRunning($pid)
{
    return file_exists("/proc/$pid");
}

function killProcess($pid)
{
    if (isProcessRunning($pid)) {
        exec("kill -TERM $pid 2>/dev/null");
        sleep(1);
        if (isProcessRunning($pid)) {
            exec("kill -KILL $pid 2>/dev/null");
        }
        return true;
    }
    return false;
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
    // Clean up any dead processes first
    managePids('cleanup');

    // Check if script is already running
    $existingPid = managePids('get', $requestedScript);
    if ($existingPid && isProcessRunning($existingPid)) {
        sendJsonResponse(false, '', 'Script is already running. Cancel it first before starting again.');
    }

    // No execution time limit for long-running scripts
    set_time_limit(0);

    // Change to the script's directory
    $scriptDir = dirname($realScriptPath);
    $scriptName = basename($realScriptPath);

    // Build the command without timeout
    $command = sprintf(
        'cd %s && %s %s 2>&1 & echo $!',
        escapeshellarg($scriptDir),
        escapeshellarg($pythonExecutable),
        escapeshellarg($scriptName)
    );

    // Log the execution (optional - remove in production if not needed)
    $logMessage = date('Y-m-d H:i:s') . " - Executing: " . $requestedScript . " from IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    error_log($logMessage, 3, '/var/log/python_runner.log');

    // Start the process in background and get PID
    $pidOutput = shell_exec($command);
    $pid = trim($pidOutput);

    if (!$pid || !is_numeric($pid)) {
        sendJsonResponse(false, '', 'Failed to start script process');
    }

    // Store the PID
    managePids('add', $requestedScript, $pid);

    // Wait a moment to check if process started successfully
    usleep(500000); // 0.5 seconds

    if (!isProcessRunning($pid)) {
        managePids('remove', $requestedScript);
        sendJsonResponse(false, '', 'Script process failed to start or exited immediately');
    }

    // For long-running scripts, we return immediately with success
    $output = "Script started successfully in background.\n";
    $output .= "Process ID: " . $pid . "\n";
    $output .= "Script: " . $requestedScript . "\n";
    $output .= "Use the Cancel button to stop the script.\n\n";
    $output .= "Note: Output from long-running scripts is not captured in real-time.\n";
    $output .= "Check your script's own logging for detailed output.";


    // Sanitize output
    $output = sanitizeOutput($output);

    sendJsonResponse(true, $output, '');

    // if ($returnCode === 0) {
    //     sendJsonResponse(true, $output, '');
    // } else if ($returnCode === 124) {
    //     // Timeout error
    //     sendJsonResponse(false, $output, 'Script execution timed out after ' . $maxExecutionTime . ' seconds');
    // } else {
    //     sendJsonResponse(false, $output, 'Script exited with error code: ' . $returnCode);
    // }

} catch (Exception $e) {
    sendJsonResponse(false, '', 'Execution error: ' . $e->getMessage());
} catch (Error $e) {
    sendJsonResponse(false, '', 'Fatal error: ' . $e->getMessage());
}
?>