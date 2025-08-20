<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cron Job Monitor</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .status-SUCCESS {
            color: #28a745; /* Bootstrap success green */
            font-weight: bold;
        }
        .status-FAILED {
            color: #dc3545; /* Bootstrap danger red */
            font-weight: bold;
        }
        .output-toggle-btn {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }
        .output-content {
            display: none;
            margin-top: 10px;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: .25rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">

        <h1 class="mb-4 text-center">Simple Cron Job Manager</h1>

        <?php
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $alertClass = ($_GET['status'] === 'success') ? 'alert-success' : 'alert-danger';
            echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert" id="flashMessageAlert">';
            echo htmlspecialchars($_GET['message']);
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
';
            echo '<span aria-hidden="true">&times;</span>';
            echo '</button>';
            echo '</div>';
        }
        ?>

        <div class="card mb-4">
            <div class="card-header">
                Create New URL Cron Job
            </div>
            <div class="card-body">
                <form action="create_cron.php" method="POST">
                    <div class="form-group">
                        <label for="jobName">Job Name</label>
                        <input type="text" class="form-control" id="jobName" name="job_name" placeholder="e.g., Check Website Status" required>
                    </div>
                    <div class="form-group">
                        <label for="url">URL to Access</label>
                        <input type="url" class="form-control" id="url" name="url" placeholder="e.g., https://example.com/api/heartbeat" required>
                    </div>
                    <div class="form-group">
                        <label>Cron Schedule</label>
                        <div class="form-row">
                            <div class="col">
                                <input type="text" class="form-control" name="minute" placeholder="Minute (0-59 or *)" value="*" required>
                                <small class="form-text text-muted">Minute</small>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="hour" placeholder="Hour (0-23 or *)" value="*" required>
                                <small class="form-text text-muted">Hour</small>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="day_of_month" placeholder="Day of Month (1-31 or *)" value="*" required>
                                <small class="form-text text-muted">Day of Month</small>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="month" placeholder="Month (1-12 or *)" value="*" required>
                                <small class="form-text text-muted">Month</small>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="day_of_week" placeholder="Day of Week (0-7 or *)" value="*" required>
                                <small class="form-text text-muted">Day of Week</small>
                            </div>
                        </div>
                        <small class="form-text text-muted mt-2">Use `*` for "every", or specific numbers/ranges. 0 and 7 are Sunday for Day of Week.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Cron Job</button>
                </form>
            </div>
        </div>

        <h2 class="mb-3">Defined Cron Jobs</h2>
        <div class="table-responsive mb-5">
            <table class="table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Job Name</th>
                        <th>URL</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $definitionsFile = __DIR__ . '/cron_definitions.json';
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

                    if (empty($definitions)) {
                        echo '<tr><td colspan="6" class="text-center">No cron jobs defined yet.</td></tr>';
                    } else {
                        foreach ($definitions as $job) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($job['name'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($job['url'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($job['schedule_minute'] ?? '*') . ' ' . 
                                 htmlspecialchars($job['schedule_hour'] ?? '*') . ' ' . 
                                 htmlspecialchars($job['schedule_day_of_month'] ?? '*') . ' ' . 
                                 htmlspecialchars($job['schedule_month'] ?? '*') . ' ' . 
                                 htmlspecialchars($job['schedule_day_of_week'] ?? '*') . '</td>';
                            echo '<td><span class="badge badge-' . (($job['status'] ?? 'active') === 'active' ? 'success' : 'secondary') . '">' . htmlspecialchars(ucfirst($job['status'] ?? 'active')) . '</span></td>';
                            echo '<td>' . htmlspecialchars($job['created_at'] ?? 'N/A') . '</td>';
                            echo '<td>';
                            echo '<form action="delete_cron.php" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this cron job?\');">';
                            echo '<input type="hidden" name="job_id" value="' . htmlspecialchars($job['id']) . '">';
                            echo '<button type="submit" class="btn btn-danger btn-sm mr-1">Delete</button>';
                            echo '</form>';

                            if (($job['status'] ?? 'active') === 'active') {
                                echo '<form action="toggle_cron.php" method="POST" class="d-inline">';
                                echo '<input type="hidden" name="job_id" value="' . htmlspecialchars($job['id']) . '">';
                                echo '<input type="hidden" name="action" value="stop">';
                                echo '<button type="submit" class="btn btn-warning btn-sm mr-1">Stop</button>';
                                echo '</form>';
                            } else {
                                echo '<form action="toggle_cron.php" method="POST" class="d-inline">';
                                echo '<input type="hidden" name="job_id" value="' . htmlspecialchars($job['id']) . '">';
                                echo '<input type="hidden" name="action" value="start">';
                                echo '<button type="submit" class="btn btn-success btn-sm mr-1">Start</button>';
                                echo '</form>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <h2 class="mb-3">Cron Job Execution Logs</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Job Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration (s)</th>
                        <th>Status</th>
                        <th>Exit Code</th>
                        <th>Output</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logFile = __DIR__ . '/cron_logs.json';
                    $logs = [];

                    if (file_exists($logFile)) {
                        $currentContent = file_get_contents($logFile);
                        if ($currentContent !== false && !empty($currentContent)) {
                            $decodedLogs = json_decode($currentContent, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedLogs)) {
                                // Sort by timestamp descending (newest first)
                                usort($decodedLogs, function($a, $b) {
                                    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
                                });
                                $logs = $decodedLogs;
                            }
                        }
                    }

                    if (empty($logs)) {
                        echo '<tr><td colspan="7" class="text-center">No cron job logs found yet.</td></tr>';
                    } else {
                        foreach ($logs as $index => $log) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($log['job_name'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($log['start_time'] ?? 'N/A') . '</td>
';
                            echo '<td>' . htmlspecialchars($log['end_time'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($log['duration_seconds'] ?? 'N/A') . '</td>';
                            echo '<td class="status-' . htmlspecialchars($log['status'] ?? 'N/A') . '">' . htmlspecialchars($log['status'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($log['exit_code'] ?? 'N/A') . '</td>';
                            echo '<td>';
                            if (!empty($log['output'])) {
                                echo '<span class="output-toggle-btn" data-toggle="collapse" data-target="#output-' . $index . '" aria-expanded="false" aria-controls="output-' . $index . '">Show Output</span>';
                                echo '<div class="collapse output-content" id="output-' . $index . '"><pre>' . htmlspecialchars($log['output']) . '</pre></div>';
                            } else {
                                echo 'No output';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <p class="text-center">get in touch  <br><code>abysalim007@gmail.com</code></p>
    </div>


    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-7ymO4nPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script>
        // JavaScript to clear URL parameters after Bootstrap alert is closed
        $("#flashMessageAlert").on("closed.bs.alert", function () {
            var url = new URL(window.location.href);
            url.searchParams.delete("status");
            url.searchParams.delete("message");
            window.history.replaceState({}, document.title, url.toString());
        });
    </script>
</body>
</html>