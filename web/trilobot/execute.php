<?php
// execute.php - Backend script to execute Python scripts

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$pythonBasePath = '/var/www/python/scripts';
$pythonExecutable = '/var/www/python/venv/bin/python';
$processesDir = '/var/www/python/processes'; // Directory to store process info
$maxOutputSize = 1024 * 1024; // Maximum output size (1MB)

// Create process directory if it doesn't exist
if (!is_dir($processesDir)) {
    mkdir($processesDir, 0777, true);
}

function sendJsonResponse($success, $output = '', $error = '', $processId = null, $status = null)
{
    echo json_encode([
        'success' => $success,
        'output' => $output,
        'error' => $error,
        'processId' => $processId,
        'status' => $status,
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

// Handle different actions
$action = $_REQUEST['action'] ?? 'start';

switch ($action) {
    case 'start':
        startScript();
        break;
    case 'status':
        getScriptStatus();
        break;
    case 'stop':
        stopScript();
        break;
    default:
        sendJsonResponse(false, '', 'Invalid action');
}

function startScript()
{
    global $pythonBasePath, $pythonExecutable, $processesDir;

    // Only allow POST requests for starting scripts
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, '', 'Only POST requests are allowed for starting scripts');
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
        // Generate a unique process ID
        $processId = uniqid('py_');
        $outputFile = $processesDir . '/' . $processId . '.out';
        $pidFile = $processesDir . '/' . $processId . '.pid';
        $infoFile = $processesDir . '/' . $processId . '.info';

        // Change to the script's directory
        $scriptDir = dirname($realScriptPath);
        $scriptName = basename($realScriptPath);

        // Store process info
        $processInfo = [
            'script' => $requestedScript,
            'scriptName' => pathinfo($requestedScript, PATHINFO_FILENAME),
            'startTime' => date('Y-m-d H:i:s'),
            'status' => 'running'
        ];
        file_put_contents($infoFile, json_encode($processInfo));

        // Build the command to run in background
        $command = sprintf(
            'cd %s && %s -u %s > %s 2>&1 & echo $! > %s',
            escapeshellarg($scriptDir),
            escapeshellarg($pythonExecutable),
            escapeshellarg($scriptName),
            escapeshellarg($outputFile),
            escapeshellarg($pidFile)
        );

        // Log the execution
        $logMessage = date('Y-m-d H:i:s') . " - Starting: " . $requestedScript . " as PID " . $processId . " from IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($logMessage, 3, '/var/log/python_runner.log');

        // Execute the command
        exec($command);

        // Wait a moment to ensure the process started
        usleep(300000); // 300ms

        if (file_exists($pidFile)) {
            sendJsonResponse(true, 'Script started in background', '', $processId, 'running');
        } else {
            sendJsonResponse(false, '', 'Failed to start script process', $processId);
        }

    } catch (Exception $e) {
        sendJsonResponse(false, '', 'Execution error: ' . $e->getMessage());
    } catch (Error $e) {
        sendJsonResponse(false, '', 'Fatal error: ' . $e->getMessage());
    }
}

function getScriptStatus()
{
    global $processesDir;

    // Check if process ID is provided
    if (!isset($_REQUEST['processId']) || empty($_REQUEST['processId'])) {
        sendJsonResponse(false, '', 'No process ID specified');
    }

    $processId = $_REQUEST['processId'];

    // Validate process ID format for security
    if (!preg_match('/^py_[a-f0-9]+$/', $processId)) {
        sendJsonResponse(false, '', 'Invalid process ID format');
    }

    $outputFile = $processesDir . '/' . $processId . '.out';
    $pidFile = $processesDir . '/' . $processId . '.pid';
    $infoFile = $processesDir . '/' . $processId . '.info';

    if (!file_exists($infoFile)) {
        sendJsonResponse(false, '', 'Process not found', $processId);
    }

    $processInfo = json_decode(file_get_contents($infoFile), true);
    $pid = trim(file_get_contents($pidFile));

    // Check if the process is still running
    $isRunning = false;
    if (file_exists("/proc/$pid")) {
        $isRunning = true;
    } else {
        // Process is no longer running, update status if needed
        if ($processInfo['status'] === 'running') {
            $processInfo['status'] = 'completed';
            $processInfo['endTime'] = date('Y-m-d H:i:s');
            file_put_contents($infoFile, json_encode($processInfo));
        }
    }

    // Get the current output
    $output = '';
    if (file_exists($outputFile)) {
        $output = file_get_contents($outputFile);
        $output = sanitizeOutput($output);
    }

    sendJsonResponse(true, $output, '', $processId, $processInfo['status']);
}

function stopScript()
{
    global $processesDir;

    // Check if process ID is provided
    if (!isset($_REQUEST['processId']) || empty($_REQUEST['processId'])) {
        sendJsonResponse(false, '', 'No process ID specified');
    }

    $processId = $_REQUEST['processId'];

    // Validate process ID format for security
    if (!preg_match('/^py_[a-f0-9]+$/', $processId)) {
        sendJsonResponse(false, '', 'Invalid process ID format');
    }

    $pidFile = $processesDir . '/' . $processId . '.pid';
    $infoFile = $processesDir . '/' . $processId . '.info';

    if (!file_exists($pidFile) || !file_exists($infoFile)) {
        sendJsonResponse(false, '', 'Process not found', $processId);
    }

    $pid = trim(file_get_contents($pidFile));
    $processInfo = json_decode(file_get_contents($infoFile), true);

    // Kill the process and its children
    exec("pkill -P $pid 2>/dev/null"); // Kill child processes first
    exec("kill $pid 2>/dev/null");     // Kill the main process

    // Update process info
    $processInfo['status'] = 'stopped';
    $processInfo['endTime'] = date('Y-m-d H:i:s');
    file_put_contents($infoFile, json_encode($processInfo));

    // Log the termination
    $logMessage = date('Y-m-d H:i:s') . " - Stopped: " . $processInfo['script'] . " (PID " . $pid . ") from IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    error_log($logMessage, 3, '/var/log/python_runner.log');

    sendJsonResponse(true, 'Script stopped', '', $processId, 'stopped');
}
?>