<?php
// require_once "inc_all_admin.php";

if (!empty($_POST)) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['alert_type'] = "error";
        $_SESSION['alert_message'] = "CSRF Error Detected!";
        header("Location: admin_settings_matrix.php");
        exit();
    }
}

// Update Matrix Settings
if (isset($_POST['edit_matrix_settings'])) {
    $matrix_server_url = sanitizeInput($_POST['matrix_server_url']);
    $matrix_api_key = sanitizeInput($_POST['matrix_api_key']);
    $matrix_room_id = sanitizeInput($_POST['matrix_room_id']);

    $sql = mysqli_prepare($mysqli, "UPDATE settings SET value = ? WHERE setting = ?");
    mysqli_stmt_bind_param($sql, 'ss', $matrix_server_url, $param_setting);
    $param_setting = 'matrix_server_url';
    mysqli_stmt_execute($sql);

    mysqli_stmt_bind_param($sql, 'ss', $matrix_api_key, $param_setting);
    $param_setting = 'matrix_api_key';
    mysqli_stmt_execute($sql);

    mysqli_stmt_bind_param($sql, 'ss', $matrix_room_id, $param_setting);
    $param_setting = 'matrix_room_id';
    mysqli_stmt_execute($sql);

    mysqli_stmt_close($sql);

    $_SESSION['alert_message'] = "Matrix settings have been updated successfully";
    $_SESSION['alert_type'] = "success";

    header("Location: admin_settings_matrix.php");
    exit();
}

// Send Test Notification
if (isset($_POST['test_matrix_notification'])) {
    $matrix_server_url = get_setting($mysqli, 'matrix_server_url');
    $matrix_api_key = get_setting($mysqli, 'matrix_api_key');
    $matrix_room_id = get_setting($mysqli, 'matrix_room_id');

    $test_message = "This is a Matrix notification test.";
    $test_result = send_test_matrix_notification($matrix_server_url, $matrix_api_key, $matrix_room_id, $test_message);

    if ($test_result) {
        $_SESSION['alert_message'] = "Test notification sent successfully!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert_message'] = "Failed to send test notification.";
        $_SESSION['alert_type'] = "error";
    }

    header("Location: admin_settings_matrix.php");
    exit();
}

// Fetch current Matrix settings
$matrix_server_url = get_setting($mysqli, 'matrix_server_url');
$matrix_api_key = get_setting($mysqli, 'matrix_api_key');
$matrix_room_id = get_setting($mysqli, 'matrix_room_id');

// Fetch recent notifications (for demonstration purposes)
$sql = $mysqli->query("SELECT * FROM matrix_notifications ORDER BY id DESC LIMIT 10");
$notifications = $sql->fetch_all(MYSQLI_ASSOC);

function send_test_matrix_notification($server_url, $api_key, $room_id, $message) {
    $url = $server_url . '/_matrix/client/r0/rooms/' . urlencode($room_id) . '/send/m.room.message?access_token=' . urlencode($api_key);

    $data = [
        "msgtype" => "m.text",
        "body" => $message,
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        error_log("Failed to send Matrix notification: " . error_get_last()['message']);
        return false;
    }

    // Log the sent notification (for demonstration purposes)
    global $mysqli;
    $sql = mysqli_prepare($mysqli, "INSERT INTO matrix_notifications (message, sent_at) VALUES (?, NOW())");
    mysqli_stmt_bind_param($sql, 's', $message);
    mysqli_stmt_execute($sql);
    mysqli_stmt_close($sql);

    return true;
}

?>

<div class="card">
    <div class="card-header">
        <h1 class="h3 mb-0"><i class="fas fa-cog mr-2"></i>Matrix Notification Settings</h1>
    </div>
    <div class="card-body">
        <form action="admin_settings_matrix.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="matrix_server_url">Matrix Server URL</label>
                <input type="url" class="form-control" id="matrix_server_url" name="matrix_server_url" value="<?php echo htmlspecialchars($matrix_server_url); ?>" required>
            </div>

            <div class="form-group">
                <label for="matrix_api_key">API Key</label>
                <input type="text" class="form-control" id="matrix_api_key" name="matrix_api_key" value="<?php echo htmlspecialchars($matrix_api_key); ?>" required>
            </div>

            <div class="form-group">
                <label for="matrix_room_id">Matrix Room ID</label>
                <input type="text" class="form-control" id="matrix_room_id" name="matrix_room_id" value="<?php echo htmlspecialchars($matrix_room_id); ?>" required>
            </div>

            <button type="submit" name="edit_matrix_settings" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Save Settings</button>
        </form>

        <hr>

        <h3>Test Notification</h3>
        <form action="admin_settings_matrix.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" name="test_matrix_notification" class="btn btn-secondary"><i class="fas fa-paper-plane mr-2"></i>Send Test Notification</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="h4 mb-0"><i class="fas fa-history mr-2"></i>Recent Notifications</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Message</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($notifications)) {
                        echo "<tr><td colspan='3' class='text-center'>No notifications found</td></tr>";
                    } else {
                        foreach ($notifications as $notification) {
                            echo "<tr>";
                            echo "<td>{$notification['id']}</td>";
                            echo "<td>{$notification['message']}</td>";
                            echo "<td>{$notification['sent_at']}</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once "footer.php";
