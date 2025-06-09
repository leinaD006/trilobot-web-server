function executeScript(scriptPath, scriptName) {
    const buttonId = "btn-" + pathToId(scriptPath);
    console.log("Executing script:", scriptPath, "Button ID:", buttonId);
    const button = document.getElementById(buttonId);
    const outputArea = document.getElementById("output-area");
    const statusArea = document.getElementById("status-area");

    // Disable button and show loading state
    button.classList.add("loading");
    button.innerHTML = '<span class="loading-spinner"></span>Executing...';
    button.disabled = true;

    // Show status
    statusArea.innerHTML = '<div class="status info">Executing: ' + scriptName + "</div>";

    // Clear previous output
    outputArea.value = "";

    // Create FormData for the request
    const formData = new FormData();
    formData.append("script", scriptPath);

    // Execute script via AJAX
    fetch("execute.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                statusArea.innerHTML = '<div class="status success">Script executed successfully!</div>';
                outputArea.value = data.output || "Script completed with no output.";
            } else {
                statusArea.innerHTML = '<div class="status error">Error: ' + data.error + "</div>";
                outputArea.value = data.output || "No output captured.";
            }
        })
        .catch((error) => {
            statusArea.innerHTML = '<div class="status error">Request failed: ' + error.message + "</div>";
            outputArea.value = "Failed to execute script.";
        })
        .finally(() => {
            // Re-enable button
            button.classList.remove("loading");
            button.innerHTML = scriptName;
            button.disabled = false;
        });
}

function clearOutput() {
    document.getElementById("output-area").value = "";
    document.getElementById("status-area").innerHTML = "";
}

function pathToId(str) {
    // Replace slashes with dashes and remove file extension
    return str.replace(/\//g, "-").replace(/\..+$/, "");
}
