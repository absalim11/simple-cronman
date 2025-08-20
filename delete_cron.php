<?php

$definitionsFile = __DIR__ . '/cron_definitions.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobIdToDelete = $_POST['job_id'] ?? '';

    if (empty($jobIdToDelete)) {
        header('Location: index.php?status=error&message=Job ID not provided.');
        exit;
    }

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

    $jobFound = false;
    $jobToDelete = null;
    $updatedDefinitions = [];

    foreach ($definitions as $job) {
        if ($job['id'] === $jobIdToDelete) {
            $jobFound = true;
            $jobToDelete = $job;
        } else {
            $updatedDefinitions[] = $job;
        }
    }

    if (!$jobFound) {
        header('Location: index.php?status=error&message=Cron job not found in definitions.');
        exit;
    }

    // Update definitions file
    if (file_put_contents($definitionsFile, json_encode($updatedDefinitions, JSON_PRETTY_PRINT)) === false) {
        header('Location: index.php?status=error&message=Failed to update cron job definitions file.');
        exit;
    }

    // --- Remove from system crontab ---
    $currentCrontab = shell_exec('crontab -l');
    if ($currentCrontab === null) {
        $currentCrontab = '';
    }

    $lines = explode("\n", $currentCrontab);
    $newCrontabLines = [];
    $cronEntryRemoved = false;

    foreach ($lines as $line) {
        // Check if the line matches the full cron entry of the job to delete
        // We need to be careful here, exact match is crucial.
        // Also consider commented out lines if they were stopped.
        if (trim($line) === trim($jobToDelete['full_cron_entry']) || trim($line) === trim('# ' . $jobToDelete['full_cron_entry'])) {
            $cronEntryRemoved = true;
        } else {
            $newCrontabLines[] = $line;
        }
    }

    $newCrontab = implode("\n", $newCrontabLines);

    // --- DEBUGGING: Print the crontab content being written ---
    error_log("Attempting to write crontab (delete) with content:\n" . $newCrontab);

    // Use exec for better error capture
    $command = 'echo ' . escapeshellarg($newCrontab) . ' | crontab - 2>&1'; // Redirect stderr to stdout
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    $execOutput = implode("\n", $output);
    error_log("Crontab write (delete) command output (returnVar: $returnVar):\n" . $execOutput);

    if ($returnVar !== 0) { // If command failed
        // Basic rollback: try to add the job back to definitions if crontab update failed
        $updatedDefinitions[] = $jobToDelete;
        file_put_contents($definitionsFile, json_encode($updatedDefinitions, JSON_PRETTY_PRINT));
        header('Location: index.php?status=error&message=Failed to remove cron job from system crontab. Error: ' . urlencode($execOutput));
        exit;
    } else {
        header('Location: index.php?status=success&message=Cron job deleted successfully!');
        exit;
    }

} else {
    header('Location: index.php');
    exit;
}

?>