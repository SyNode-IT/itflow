<?php
require_once "inc_all_admin.php";

// Initialisation des variables
$alert = '';
$matrix_server_url = $matrix_api_key = $matrix_room_id = '';

// Fonction pour mettre à jour un paramètre
function updateSetting($mysqli, $setting, $value) {
    $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE setting = ?");
    $stmt->bind_param("ss", $value, $setting);
    $stmt->execute();
    $stmt->close();
}

// Traitement de la mise à jour des paramètres Matrix
if (isset($_POST['edit_matrix_settings'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $matrix_server_url = filter_input(INPUT_POST, 'matrix_server_url', FILTER_SANITIZE_URL);
    $matrix_api_key = filter_input(INPUT_POST, 'matrix_api_key', FILTER_SANITIZE_STRING);
    $matrix_room_id = filter_input(INPUT_POST, 'matrix_room_id', FILTER_SANITIZE_STRING);

    updateSetting($mysqli, 'matrix_server_url', $matrix_server_url);
    updateSetting($mysqli, 'matrix_api_key', $matrix_api_key);
    updateSetting($mysqli, 'matrix_room_id', $matrix_room_id);

    $alert = "Matrix settings have been updated successfully.";
}

// Récupération des paramètres actuels
$stmt = $mysqli->prepare("SELECT setting, value FROM settings WHERE setting IN ('matrix_server_url', 'matrix_api_key', 'matrix_room_id')");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    ${$row['setting']} = $row['value'];
}
$stmt->close();

// Fonction pour envoyer une notification de test via Matrix
function send_test_matrix_notification($server_url, $api_key, $room_id, $message) {
    $url = $server_url . '/_matrix/client/r0/rooms/' . urlencode($room_id) . '/send/m.room.message';
    
    $data = [
        "msgtype" => "m.text",
        "body" => $message,
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $api_key,
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        error_log("Failed to send Matrix notification: " . error_get_last()['message']);
        return false;
    }

    return true;
}

// Traitement de la demande de test de notification
if (isset($_POST['test_matrix_notification'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }

    $test_message = "Ceci est un test de notification Matrix.";
    $test_result = send_test_matrix_notification($matrix_server_url, $matrix_api_key, $matrix_room_id, $test_message);
    $alert = $test_result ? "Test notification sent successfully!" : "Failed to send test notification.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres de Notification Matrix</title>
    <!-- Inclure vos fichiers CSS ici -->
</head>
<body>
    <div class="card card-dark">
        <div class="card-header py-3">
            <h3 class="card-title"><i class="fas fa-fw fa-bell mr-2"></i>Paramètres de Notification Matrix <small>(Pour l'envoi de notifications)</small></h3>
        </div>
        <div class="card-body">
            <form action="admin_settings_matrix.php" method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="matrix_server_url">URL du serveur Matrix</label>
                    <input type="url" class="form-control" id="matrix_server_url" name="matrix_server_url" value="<?php echo htmlspecialchars($matrix_server_url); ?>" required>
                </div>

                <div class="form-group">
                    <label for="matrix_api_key">API Key</label>
                    <input type="text" class="form-control" id="matrix_api_key" name="matrix_api_key" value="<?php echo htmlspecialchars($matrix_api_key); ?>" required>
                </div>

                <div class="form-group">
                    <label for="matrix_room_id">ID de la salle Matrix</label>
                    <input type="text" class="form-control" id="matrix_room_id" name="matrix_room_id" value="<?php echo htmlspecialchars($matrix_room_id); ?>" required>
                </div>

                <button type="submit" name="edit_matrix_settings" class="btn btn-primary">Sauvegarder les paramètres</button>
                <button type="submit" name="test_matrix_notification" class="btn btn-secondary">Tester l'envoi de notification</button>
            </form>
        </div>
    </div>

    <?php if ($alert): ?>
        <div class="alert alert-info mt-3">
            <?php echo htmlspecialchars($alert); ?>
        </div>
    <?php endif; ?>

    <!-- Inclure vos fichiers JavaScript ici -->
</body>
</html>

<?php
require_once "footer.php";
?>
