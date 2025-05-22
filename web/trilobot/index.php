<?php

function flashUnderlights()
{
    // Path to the Python executable
    $python = "/var/www/python/venv/bin/python";
    // Path to the script
    $script_path = "/var/www/python/scripts";

    // Command to execute the Python script
    $command = "$python $script_path/examples/flash_underlights.py";

    // Execute the command and capture the output
    $output = shell_exec($command);

    // Return the output
    return $output;
}

# Button to trigger the flashUnderlights function
if ($_POST['flash_underlights']) {
    return flashUnderlights();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trilobot Control</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .output {
            margin-top: 20px;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Trilobot Control</h1>
        <div class="text-center">
            <button id="flash-underlights">Flash Underlights</button>
        </div>
        <div class="output">
        </div>
    </div>
    <script>
        $('#flash-underlights').click(function () {
            $.post('', { flash_underlights: true }, function (data) {
                $('.output').html(data);
            });
        });
    </script>
</body>

</html>