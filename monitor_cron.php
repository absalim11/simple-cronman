<?php

// Configuration
$logFile = __DIR__ . '/cron_logs.json';

// Get job name and command from arguments
if ($argc < 2) {
    echo "Usage: php monitor_cron.php <job_name> <command_to_run>\n";
    exit(1);
}

$jobName = $argv[1];
$commandToRun = implode(' ', array_slice($argv, 2));

if (empty($commandToRun)) {
    echo "Error: No command to run provided.\n";
    exit(1);
}

$startTime = microtime(true);
$output = [];
$returnVar = 0;

// Execute the command
exec($commandToRun . ' 2>&1', $output, $returnVar);

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2); // in seconds

$status = ($returnVar === 0) ? 'SUCCESS' : 'FAILED';
$fullOutput = implode("\n", $output);

$logEntry = [
    'job_name' => $jobName,
    'start_time' => date('Y-m-d H:i:s', $startTime),
    'end_time' => date('Y-m-d H:i:s', $endTime),
    'duration_seconds' => $duration,
    'status' => $status,
    'exit_code' => $returnVar,
    'output' => $fullOutput,
    'timestamp' => time() // Unix timestamp for easy sorting
];

// Read existing logs
$logs = [];
if (file_exists($logFile)) {
    $currentContent = file_get_contents($logFile);
    if ($currentContent !== false && !empty($currentContent)) {
        $decodedLogs = json_decode($currentContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedLogs)) {
            $logs = $decodedLogs;
        }
    }
}

// Add new entry to the beginning (newest first)
array_unshift($logs, $logEntry);

// Keep only the last 100 entries to prevent file from growing too large
$logs = array_slice($logs, 0, 100);

// Write logs back to file
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));

echo "Cron job '$jobName' finished with status: $status\n";
echo "Output:\n$fullOutput\n";

?>