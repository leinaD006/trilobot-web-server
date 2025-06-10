// Global object to store references to running processes
let activeProcesses = {};
const refreshInterval = 1000; // Poll for updates every 1 second

function executeScript(scriptPath, scriptName) {
    const buttonId = "btn-" + pathToId(scriptPath);
    console.log("Executing script:", scriptPath, "Button ID:", buttonId);
    const button = document.getElementById(buttonId);

    // Create a new process entry in the output panel
    const processId = createProcessOutput(scriptName, scriptPath);

    // Disable button and show loading state with cancel option
    button.classList.add("loading");
    button.innerHTML = `
        <span class="loading-spinner"></span>
        <span>${scriptName}</span>
        <button class="cancel-button" onclick="event.stopPropagation(); stopScript('${processId}', '${buttonId}')">Cancel</button>
    `;
    button.disabled = true;

    // Create FormData for the request
    const formData = new FormData();
    formData.append("script", scriptPath);
    formData.append("action", "start");

    // Execute script via AJAX
    fetch("execute.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Store process information
                activeProcesses[processId] = {
                    id: data.processId,
                    scriptName: scriptName,
                    scriptPath: scriptPath,
                    status: data.status || "running",
                    buttonId: buttonId,
                    outputId: processId,
                };

                // Update process output area with initial information
                updateProcessOutput(processId, "Script started in background. Process ID: " + data.processId, "running");

                // Start polling for updates
                activeProcesses[processId].interval = setInterval(() => {
                    pollProcessStatus(data.processId, processId);
                }, refreshInterval);
            } else {
                updateProcessOutput(processId, "Error: " + data.error, "error");
                resetScriptButton(buttonId, scriptName);
            }
        })
        .catch((error) => {
            updateProcessOutput(processId, "Request failed: " + error.message, "error");
            resetScriptButton(buttonId, scriptName);
        });
}

function pollProcessStatus(processId, outputId) {
    if (!processId || !outputId || !activeProcesses[outputId]) {
        return;
    }

    fetch(`execute.php?action=status&processId=${processId}`)
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                console.log("Polling status for process:", processId, "Output ID:", outputId, "Data:", data);
                updateProcessOutput(outputId, data.output, data.status);

                // If process is no longer running, stop polling and reset button
                if (data.status === "completed" || data.status === "stopped") {
                    clearInterval(activeProcesses[outputId].interval);
                    resetScriptButton(activeProcesses[outputId].buttonId, activeProcesses[outputId].scriptName);
                    activeProcesses[outputId].status = data.status;
                }
            } else {
                updateProcessOutput(outputId, "Error checking status: " + data.error, "error");
                clearInterval(activeProcesses[outputId].interval);
                resetScriptButton(activeProcesses[outputId].buttonId, activeProcesses[outputId].scriptName);
            }
        })
        .catch((error) => {
            updateProcessOutput(outputId, "Request failed: " + error.message, "error");
            clearInterval(activeProcesses[outputId].interval);
            resetScriptButton(activeProcesses[outputId].buttonId, activeProcesses[outputId].scriptName);
        });
}

function stopScript(outputId, buttonId) {
    if (!activeProcesses[outputId]) {
        return;
    }

    const processId = activeProcesses[outputId].id;
    updateProcessOutput(outputId, "Stopping script...", "stopping");

    fetch(`execute.php?action=stop&processId=${processId}`)
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                updateProcessOutput(outputId, data.output + "\n\n[Script was manually stopped]", "stopped");
                clearInterval(activeProcesses[outputId].interval);
                activeProcesses[outputId].status = "stopped";
            } else {
                updateProcessOutput(outputId, "Error stopping script: " + data.error, "error");
            }
            resetScriptButton(buttonId, activeProcesses[outputId].scriptName);
        })
        .catch((error) => {
            updateProcessOutput(outputId, "Failed to stop script: " + error.message, "error");
            resetScriptButton(buttonId, activeProcesses[outputId].scriptName);
        });
}

function resetScriptButton(buttonId, scriptName) {
    const button = document.getElementById(buttonId);
    if (button) {
        button.classList.remove("loading");
        button.innerHTML = scriptName;
        button.disabled = false;
    }
}

function createProcessOutput(scriptName, scriptPath) {
    // Generate a unique ID for this process output
    const outputId = "process-" + Date.now();
    const outputContainer = document.getElementById("outputs-container");

    // Create process output panel
    const outputPanel = document.createElement("div");
    outputPanel.className = "process-output";
    outputPanel.id = outputId;
    outputPanel.innerHTML = `
        <div class="process-header">
            <h3 class="process-title">${scriptName}</h3>
            <div class="process-status" data-status="starting">Starting...</div>
            <button class="process-close" onclick="closeProcessOutput('${outputId}')">×</button>
        </div>
        <textarea class="output-area" readonly></textarea>
    `;

    // Add to the container
    outputContainer.prepend(outputPanel);

    // Scroll to the new output
    // outputPanel.scrollIntoView({ behavior: "smooth" });

    return outputId;
}

function updateProcessOutput(outputId, content, status) {
    const outputPanel = document.getElementById(outputId);
    if (!outputPanel) return;

    // Update the content
    const textarea = outputPanel.querySelector(".output-area");
    textarea.value = content || "";

    // Update status indicator
    const statusElement = outputPanel.querySelector(".process-status");
    if (statusElement && status) {
        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusElement.dataset.status = status;
    }

    // Auto-scroll to bottom
    textarea.scrollTop = textarea.scrollHeight;
}

function closeProcessOutput(outputId) {
    // If process is still running, confirm before closing
    if (activeProcesses[outputId] && activeProcesses[outputId].status === "running") {
        if (!confirm("This script is still running. Close the output anyway?")) {
            return;
        }
        // We don't stop the script, just hide the output
    }

    // Remove the output panel
    const outputPanel = document.getElementById(outputId);
    if (outputPanel) {
        outputPanel.remove();
    }

    // Clear the interval if it exists
    if (activeProcesses[outputId] && activeProcesses[outputId].interval) {
        clearInterval(activeProcesses[outputId].interval);
    }
}

function clearAllOutputs() {
    // Ask for confirmation if any processes are running
    const runningProcesses = Object.values(activeProcesses).filter((p) => p.status === "running").length;
    if (runningProcesses > 0) {
        if (!confirm(`There are ${runningProcesses} scripts still running. Close all outputs anyway?`)) {
            return;
        }
    }

    // Clear all outputs
    document.getElementById("outputs-container").innerHTML = "";

    // Clear all intervals
    Object.values(activeProcesses).forEach((process) => {
        if (process.interval) {
            clearInterval(process.interval);
        }
    });
}

function pathToId(str) {
    // Replace slashes with dashes and remove file extension
    return str.replace(/\//g, "-").replace(/\..+$/, "");
}
