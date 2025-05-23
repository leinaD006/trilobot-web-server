<?php
// index.php - Main interface for Python script runner

function scanPythonDirectory($dir, $baseDir = '/var/www/python/scripts')
{
    $scripts = [];

    if (!is_dir($dir)) {
        return $scripts;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'py') {
            $relativePath = str_replace($baseDir . '/', '', $file->getPathname());
            $scripts[] = [
                'name' => $file->getBasename('.py'),
                'path' => $relativePath,
                'fullPath' => $file->getPathname(),
                'directory' => dirname($relativePath)
            ];
        }
    }

    // Sort by directory then by name
    usort($scripts, function ($a, $b) {
        $dirCompare = strcmp($a['directory'], $b['directory']);
        return $dirCompare !== 0 ? $dirCompare : strcmp($a['name'], $b['name']);
    });

    return $scripts;
}

$pythonDir = '/var/www/python';
$scripts = scanPythonDirectory($pythonDir);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Script Runner</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            min-height: 600px;
        }

        .scripts-panel {
            padding: 30px;
            border-right: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .output-panel {
            padding: 30px;
            background: #ffffff;
        }

        .panel-title {
            font-size: 1.4em;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }

        .script-group {
            margin-bottom: 25px;
        }

        .group-title {
            font-weight: bold;
            color: #34495e;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #ecf0f1;
            border-radius: 5px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .script-button {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin: 8px 0;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95em;
            transition: all 0.3s ease;
            text-align: left;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .script-button:hover {
            background: linear-gradient(135deg, #2980b9, #1abc9c);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .script-button:active {
            transform: translateY(0);
        }

        .script-button.loading {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            cursor: not-allowed;
        }

        .output-area {
            width: 100%;
            min-height: 400px;
            padding: 20px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            background: #2c3e50;
            color: #ecf0f1;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            white-space: pre-wrap;
        }

        .status {
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
        }

        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .clear-output {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            transition: background 0.3s ease;
        }

        .clear-output:hover {
            background: #c0392b;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }

            .scripts-panel {
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🐍 Python Script Runner</h1>
            <p>Execute Python scripts from your Raspberry Pi</p>
        </div>

        <div class="content">
            <div class="scripts-panel">
                <h2 class="panel-title">Available Scripts</h2>

                <?php if (empty($scripts)): ?>
                    <div class="status info">
                        No Python scripts found in /var/www/python
                    </div>
                <?php else: ?>
                    <?php
                    $currentDir = '';
                    foreach ($scripts as $script):
                        if ($script['directory'] !== $currentDir):
                            if ($currentDir !== '')
                                echo '</div>';
                            $currentDir = $script['directory'];
                            echo '<div class="script-group">';
                            echo '<div class="group-title">' . ($currentDir === '.' ? 'Root Directory' : htmlspecialchars($currentDir)) . '</div>';
                        endif;
                        ?>
                        <button class="script-button"
                            onclick="executeScript('<?php echo htmlspecialchars($script['path']); ?>', '<?php echo htmlspecialchars($script['name']); ?>')"
                            id="btn-<?php echo md5($script['path']); ?>">
                            <?php echo htmlspecialchars($script['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="output-panel">
            <h2 class="panel-title">Script Output</h2>
            <button class="clear-output" onclick="clearOutput()">Clear Output</button>
            <div id="status-area"></div>
            <textarea id="output-area" class="output-area" readonly
                placeholder="Script output will appear here..."></textarea>
        </div>
    </div>
    </div>

    <script>
        function executeScript(scriptPath, scriptName) {
            const buttonId = 'btn-' + md5(scriptPath);
            const button = document.getElementById(buttonId);
            const outputArea = document.getElementById('output-area');
            const statusArea = document.getElementById('status-area');

            // Disable button and show loading state
            button.classList.add('loading');
            button.innerHTML = '<span class="loading-spinner"></span>Executing...';
            button.disabled = true;

            // Show status
            statusArea.innerHTML = '<div class="status info">Executing: ' + scriptName + '</div>';

            // Clear previous output
            outputArea.value = '';

            // Create FormData for the request
            const formData = new FormData();
            formData.append('script', scriptPath);

            // Execute script via AJAX
            fetch('execute.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusArea.innerHTML = '<div class="status success">Script executed successfully!</div>';
                        outputArea.value = data.output || 'Script completed with no output.';
                    } else {
                        statusArea.innerHTML = '<div class="status error">Error: ' + data.error + '</div>';
                        outputArea.value = data.output || 'No output captured.';
                    }
                })
                .catch(error => {
                    statusArea.innerHTML = '<div class="status error">Request failed: ' + error.message + '</div>';
                    outputArea.value = 'Failed to execute script.';
                })
                .finally(() => {
                    // Re-enable button
                    button.classList.remove('loading');
                    button.innerHTML = scriptName;
                    button.disabled = false;
                });
        }

        function clearOutput() {
            document.getElementById('output-area').value = '';
            document.getElementById('status-area').innerHTML = '';
        }

        // Simple MD5 hash function for button IDs
        function md5(str) {
            // Simple hash function - in production, you might want a proper MD5 implementation
            let hash = 0;
            if (str.length === 0) return hash.toString();
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            return Math.abs(hash).toString();
        }
    </script>
</body>

</html>