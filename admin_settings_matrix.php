<?php
require_once "inc_all_admin.php";
require_once "admin_settings_matrix_functions.php"; // Inclure la logique backend
?>

<div class="card card-dark">
    <div class="card-header py-3">
        <h3 class="card-title"><i class="fas fa-fw fa-bell mr-2"></i>Paramètres de Notification Matrix <small>(Pour l'envoi de notifications)</small></h3>
    </div>
    <div class="card-body">
        <form action="admin_settings_matrix.php" method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label for="matrix_server_url">URL du serveur Matrix</label>
                <input type="text" class="form-control" id="matrix_server_url" name="matrix_server_url" value="<?php echo htmlspecialchars($matrix_server_url); ?>" required>
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

<?php if (isset($alert)): ?>
    <div class="alert alert-info mt-3">
        <?php echo $alert; ?>
    </div>
<?php endif; ?>
