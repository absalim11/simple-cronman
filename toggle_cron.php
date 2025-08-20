<?php

$definitionsFile = __DIR__ . '/cron_definitions.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobIdToToggle = $_POST['job_id'] ?? '';
    $action = $_POST['action'] ?? ''; // 'stop' or 'start'

    if (empty($jobIdToToggle) || !in_array($action, ['stop', 'start'])) {
        header('Location: index.php?status=error&message=Invalid request for toggle action.');
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
    $jobToToggle = null;
    $updatedDefinitions = [];

    foreach ($definitions as $job) {
        if ($job['id'] === $jobIdToToggle) {
            $jobFound = true;
            $jobToToggle = $job;
            // Update status in definition
            $job['status'] = ($action === 'stop') ? 'inactive' : 'active';
        }
        $updatedDefinitions[] = $job;
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

    // --- Modify system crontab ---
    $currentCrontab = shell_exec('crontab -l');
    if ($currentCrontab === null) {
        $currentCrontab = '';
    }

    $lines = explode("\n", $currentCrontab);
    $newCrontabLines = [];
    $crontabModified = false;

    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        $originalEntry = trim($jobToToggle['full_cron_entry']);
        $commentedEntry = '# ' . $originalEntry;

        if ($action === 'stop') {
            // If it's the active entry, comment it out
            if ($trimmedLine === $originalEntry) {
                $newCrontabLines[] = $commentedEntry;
                $crontabModified = true;
            } else {
                $newCrontabLines[] = $line;
            }
        } elseif ($action === 'start') {
            // If it's the commented out entry, uncomment it
            if ($trimmedLine === $commentedEntry) {
                $newCrontabLines[] = $originalEntry;
                $crontabModified = true;
            } else {
                $newCrontabLines[] = $line;
            }
        } else {
            $newCrontabLines[] = $line; // Should not happen due to validation
        }
    }

    $newCrontab = implode("\n", $newCrontabLines);

    // --- DEBUGGING: Print the crontab content being written ---
    error_log("Attempting to write crontab (toggle) with content:\n" . $newCrontab);

    // Use exec for better error capture
    $command = 'echo ' . escapeshellarg($newCrontab) . ' | crontab - 2>&1'; // Redirect stderr to stdout
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    $execOutput = implode("\n", $output);
    error_log("Crontab write (toggle) command output (returnVar: $returnVar):\n" . $execOutput);

    if ($returnVar !== 0) { // If command failed
        // Basic rollback: try to revert status in definitions if crontab update failed
        foreach ($updatedDefinitions as &$job) {
            if ($job['id'] === $jobIdToToggle) {
                $job['status'] = ($action === 'stop') ? 'active' : 'inactive'; // Revert status
                break;
            }
        }
        file_put_contents($definitionsFile, json_encode($updatedDefinitions, JSON_PRETTY_PRINT));
        header('Location: index.php?status=error&message=Failed to modify cron job in system crontab. Error: ' . urlencode($execOutput));
        exit;
    } else {
        $message = ($action === 'stop') ? 'Cron job stopped successfully!' : 'Cron job started successfully!';
        header('Location: index.php?status=success&message=' . urlencode($message));
        exit;
    }

} else {
    header('Location: index.php');
    exit;
}

?>