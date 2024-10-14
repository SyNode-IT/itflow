<?php

// Vérifier si l'administrateur met à jour les paramètres Matrix
if (isset($_POST['edit_matrix_settings'])) {

    validateCSRFToken($_POST['csrf_token']);

    $matrix_server_url = sanitizeInput($_POST['matrix_server_url']);
    $matrix_api_key = sanitizeInput($_POST['matrix_api_key']);
    $matrix_room_id = sanitizeInput($_POST['matrix_room_id']);

    mysqli_query($mysqli, "UPDATE settings SET value='$matrix_server_url' WHERE setting='matrix_server_url'");
    mysqli_query($mysqli, "UPDATE settings SET value='$matrix_api_key' WHERE setting='matrix_api_key'");
    mysqli_query($mysqli, "UPDATE settings SET value='$matrix_room_id' WHERE setting='matrix_room_id'");

    $alert = "Matrix settings have been updated successfully.";
}

// Récupérer les paramètres actuels
$q = mysqli_query($mysqli, "SELECT * FROM settings WHERE setting IN ('matrix_server_url', 'matrix_api_key', 'matrix_room_id')");
while ($row = mysqli_fetch_array($q)) {
    if ($row['setting'] == 'matrix_server_url') {
        $matrix_server_url = $row['value'];
    } elseif ($row['setting'] == 'matrix_api_key') {
        $matrix_api_key = $row['value'];
    } elseif ($row['setting'] == 'matrix_room_id') {
        $matrix_room_id = $row['value'];
    }
}

// Vérifier si l'administrateur demande un test de notification
if (isset($_POST['test_matrix_notification'])) {
    validateCSRFToken($_POST['csrf_token']);
    $test_message = "Ceci est un test de notification Matrix.";
    $test_result = send_test_matrix_notification($matrix_server_url, $matrix_api_key, $matrix_room_id, $test_message);
    $alert = $test_result ? "Test notification sent successfully!" : "Failed to send test notification.";
}

/**
 * Fonction pour envoyer une notification de test via Matrix
 */
function send_test_matrix_notification($server_url, $api_key, $room_id, $message) {
    $url = $server_url . '/_matrix/client/r0/rooms/' . urlencode($room_id) . '/send/m.room.message?access_token=' . $api_key;

    $data = [
        "msgtype" => "m.text",
        "body" => $message,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code == 200;
}

?>
