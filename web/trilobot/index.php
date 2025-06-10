<?php
// index.php - Main interface for Python script runner

function scanPythonDirectory($dir, $baseDir = '/var/www/python/scripts')
{
    $scripts = [];

    if (!is_dir($dir)) {
        return $scripts;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $dir,
            RecursiveDirectoryIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        // Handle both regular files and symlinks
        if (($file->isFile() || $file->isLink()) && $file->getExtension() === 'py') {
            $realPath = $file->isLink() ? readlink($file->getPathname()) : $file->getRealPath();
            // If symlink path is relative, make it absolute
            if ($file->isLink() && !str_starts_with($realPath, '/')) {
                $realPath = dirname($file->getPathname()) . '/' . $realPath;
            }
            $relativePath = str_replace($baseDir . '/', '', $file->getPathname());
            $scripts[] = [
                'name' => $file->getBasename('.py'),
                'path' => $relativePath,
                'fullPath' => $realPath,
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

$pythonDir = '/var/www/python/scripts';
$scripts = scanPythonDirectory($pythonDir);

// Make sure processes directory exists
$processesDir = '/var/www/python/processes';
if (!is_dir($processesDir)) {
    mkdir($processesDir, 0755, true);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Script Runner</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Mars Rover Control Panel</h1>
            <p>Execute Python scripts loaded on your rover</p>
        </div>

        <div class="content">
            <div class="scripts-panel">
                <h2 class="panel-title">Available Scripts</h2>

                <?php if (empty($scripts)): ?>
                    <div class="status info">
                        No Python scripts found in /var/www/python/scripts
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
                            id="btn-<?php echo str_replace('/', '-', explode(".", $script['path'])[0], ); ?>">
                            <?php echo htmlspecialchars($script['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="output-panel">
            <h2 class="panel-title">Script Outputs</h2>
            <div class="output-controls">
                <button class="clear-output" onclick="clearAllOutputs()">Clear All Outputs</button>
            </div>
            <div id="outputs-container" class="outputs-container">
                <!-- Process outputs will be dynamically added here -->
            </div>
        </div>
    </div>

</body>

</html>