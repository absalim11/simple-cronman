<?php

// --- DEBUGGING: Initial script execution test ---
file_put_contents(__DIR__ . '/create_cron_debug.log', 'Script started at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
// --- END DEBUGGING ---

// Path to your monitor_cron.php script
$monitorScriptPath = __DIR__ . '/monitor_cron.php';
$definitionsFile = __DIR__ . '/cron_definitions.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobName = $_POST['job_name'] ?? '';
    $url = $_POST['url'] ?? '';

    // Get separate cron schedule components
    $minute = $_POST['minute'] ?? '*';
    $hour = $_POST['hour'] ?? '*';
    $dayOfMonth = $_POST['day_of_month'] ?? '*';
    $month = $_POST['month'] ?? '*';
    $dayOfWeek = $_POST['day_of_week'] ?? '*';

    // Combine them into a single cron schedule string
    $cronSchedule = sprintf('%s %s %s %s %s', $minute, $hour, $dayOfMonth, $month, $dayOfWeek);

    // Basic validation
    if (empty($jobName) || empty($url) || empty($cronSchedule)) {
        header('Location: index.php?status=error&message=All fields are required.');
        exit;
    }

    // Validate URL format (simple check)
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        header('Location: index.php?status=error&message=Invalid URL format.');
        exit;
    }

    // Construct the command that monitor_cron.php will run
    // We use curl to fetch the URL, and redirect output to /dev/null
    $commandToRun = "curl -s '" . escapeshellarg($url) . "' > /dev/null";

    // Ensure the PHP executable path is correct (e.g., /usr/bin/php)
    $phpExecutable = '/usr/bin/php'; // Adjust if your PHP executable is elsewhere

    // Construct the full cron entry that will be added to crontab
    $fullCronEntry = sprintf(
        "%s %s %s %s %s %s %s",
        $cronSchedule,
        $phpExecutable,
        escapeshellarg($monitorScriptPath),
        escapeshellarg($jobName),
        escapeshellarg($commandToRun),
        // Redirect stdout and stderr of the monitor script itself to a log file
        // This is separate from the commandToRun's output
        ">>",
        escapeshellarg(__DIR__ . '/cron_monitor_log.txt'),
        "2>&1"
    );

    // --- Store job definition ---
    $definitions = [];
    if (file_exists($definitionsFile)) {
        $currentContent = file_get_contents($definitionsFile);
        if ($currentContent !== false && !empty($currentContent)) {
            $decodedDefs = json_decode($currentContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDefs)) {
                $definitions = $decodedDefs;
            }
        }
    }

    // Generate a unique ID (simple timestamp + random for uniqueness)
    $jobId = uniqid('cron_', true);

    $newJobDefinition = [
        'id' => $jobId,
        'name' => $jobName,
        'url' => $url,
        'schedule_minute' => $minute,
        'schedule_hour' => $hour,
        'schedule_day_of_month' => $dayOfMonth,
        'schedule_month' => $month,
        'schedule_day_of_week' => $dayOfWeek,
        'full_cron_entry' => $fullCronEntry, // Store the exact string added to crontab
        'status' => 'active', // Initial status
        'created_at' => date('Y-m-d H:i:s')
    ];

    $definitions[] = $newJobDefinition;

    if (file_put_contents($definitionsFile, json_encode($definitions, JSON_PRETTY_PRINT)) === false) {
        header('Location: index.php?status=error&message=Failed to save cron job definition.');
        exit;
    }

    // --- Add to system crontab ---
    $currentCrontab = shell_exec('crontab -l');
    if ($currentCrontab === null) {
        $currentCrontab = '';
    }

    // Add new entry
    $newCrontab = $currentCrontab . "\n" . $fullCronEntry . "\n";

    // --- DEBUGGING: Print the crontab content being written ---
    // This will appear in your web server's error log if display_errors is off
    // or directly in browser if display_errors is on.
    error_log("Attempting to write crontab with content:\n" . $newCrontab);

    // Use exec for better error capture
    $command = 'echo ' . escapeshellarg($newCrontab) . ' | crontab - 2>&1'; // Redirect stderr to stdout
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    $execOutput = implode("\n", $output);
    error_log("Crontab write command output (returnVar: $returnVar):\n" . $execOutput);

    if ($returnVar !== 0) { // If command failed
        // If crontab update fails, try to remove from definitions to keep consistent
        $definitions = array_filter($definitions, function($def) use ($jobId) {
            return $def['id'] !== $jobId;
        });
        file_put_contents($definitionsFile, json_encode($definitions, JSON_PRETTY_PRINT));

        header('Location: index.php?status=error&message=Failed to add cron job to system crontab. Error: ' . urlencode($execOutput));
        exit;
    } else {
        header('Location: index.php?status=success&message=Cron job added successfully!');
        exit;
    }

} else {
    header('Location: index.php');
    exit;
}

?>